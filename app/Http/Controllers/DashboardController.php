<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    // ── Blood Pressure Reference ──────────────────────────────────────
    private function getBloodPressureReference(int $age): array
    {
        if ($age <= 9)  return [95,  112, 56, 71];
        if ($age <= 18) return [112, 128, 66, 80];
        if ($age <= 59) return [90,  120, 60, 80];
        return [150, 150, 90, 90];
    }

    // ── Dynamic Score Calculator ──────────────────────────────────────
    private function calculateDynamicScore(object $q): float
    {
        $age = \Carbon\Carbon::parse($q->BIRTHDATE)->age;
        [$sysMin, $sysMax, $diasMin, $diasMax] = $this->getBloodPressureReference($age);

        $sysRef  = ($sysMin + $sysMax) / 2;
        $diasRef = ($diasMin + $diasMax) / 2;

        $devSys  = $sysRef  > 0 ? abs(($q->SYSTOLIC  - $sysRef)  / $sysRef  * 100) : 0;
        $devDias = $diasRef > 0 ? abs(($q->DIASTOLIC - $diasRef) / $diasRef * 100) : 0;

        $tensiAdjusted = min((($devSys + $devDias) / 2) / 100 * 5, 5);
        $keluhanScore  = ($q->COMPLAINT_SCORE / 10) * 5;

        $registeredAt  = \Carbon\Carbon::parse($q->REGISTRATION_DATE . ' ' . $q->REGISTRATION_TIME);
        $minutesWaited = max(0, now()->diffInMinutes($registeredAt));
        $waitingScore  = min(($minutesWaited / 30) * 5, 5);

        $kondisi = $q->SPECIAL_CONDITION_SCORE ? 1 : 0;

        return round(
    ($tensiAdjusted * 0.40) +
    ($keluhanScore  * 0.35) +
    ($waitingScore  * 0.15) +
    ($kondisi       * 0.10),
    4
);
    }

    // ── Tensi Score only (for display) ───────────────────────────────
    private function calculateTensiScore(object $q): float
    {
        $age = \Carbon\Carbon::parse($q->BIRTHDATE)->age;
        [$sysMin, $sysMax, $diasMin, $diasMax] = $this->getBloodPressureReference($age);
        $sysRef  = ($sysMin + $sysMax) / 2;
        $diasRef = ($diasMin + $diasMax) / 2;
        $devSys  = $sysRef  > 0 ? abs(($q->SYSTOLIC  - $sysRef)  / $sysRef  * 100) : 0;
        $devDias = $diasRef > 0 ? abs(($q->DIASTOLIC - $diasRef) / $diasRef * 100) : 0;
        return round(min((($devSys + $devDias) / 2) / 100 * 5, 5), 4);
    }

    // ── Waiting Time in minutes (for display) ─────────────────────────
    private function getWaitingMinutes(object $q): int
    {
        $registeredAt = \Carbon\Carbon::parse($q->REGISTRATION_DATE . ' ' . $q->REGISTRATION_TIME);
        return max(0, now()->diffInMinutes($registeredAt));
    }

    // ── Dashboard ─────────────────────────────────────────────────────
    public function index_dashboard_frontdesk()
    {
        $data['title'] = 'Dashboard Admin';

        $days = [
            0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
        ];
        $today  = $days[now()->dayOfWeek];
        $now    = now()->format('H:i:s');
        $cutoff = now()->addMinutes(30)->format('H:i:s');

        // ── Fetch all active queues ───────────────────────────────────
        $queues = DB::table('tr_queue_polyclinic as tq')
            ->leftJoin('md_patient as mp',          'tq.PATIENT_ID',  '=', 'mp.PATIENT_ID')
            ->leftJoin('md_doctor_schedule as mds', 'tq.SCHEDULE_ID', '=', 'mds.SCHEDULE_ID')
            ->leftJoin('md_doctor as md',           'mds.DOCTOR_ID',  '=', 'md.DOCTOR_ID')
            ->leftJoin('md_poly as mpoly',          'mds.POLY_ID',    '=', 'mpoly.POLY_ID')
            ->leftJoin('md_poly_room as mpr',       'mpoly.ROOM_ID',  '=', 'mpr.ROOM_ID')
            ->whereIn('tq.QUEUE_STATUS', ['Menunggu', 'Dilayani', 'Missed'])
            ->where('tq.IS_ACTIVE', 1)
            ->when($today, fn($q) => $q->where('mds.DAY', $today))
            ->select(
                'tq.QUEUE_ID', 'tq.QUEUE_NUMBER', 'tq.QUEUE_STATUS',
                'tq.COMPLAINTS', 'tq.SYSTOLIC', 'tq.DIASTOLIC',
                'tq.COMPLAINT_SCORE', 'tq.SPECIAL_CONDITION_SCORE',
                'tq.ADJUSTED_STATUS', 'tq.FIXED_QUEUE_STATUS',
                'tq.REGISTRATION_DATE', 'tq.REGISTRATION_TIME',
                'tq.CREATED_AT', 'tq.SHADOW_POSITION',
                'mp.PATIENT_NAME', 'mp.BIRTHDATE',
                'md.DOCTOR_NAME', 'mpoly.POLY_NAME', 'mpr.ROOM_NAME'
            )
            ->get();

        $serving = $queues->where('QUEUE_STATUS', 'Dilayani')->values();
        $waiting = $queues->whereIn('QUEUE_STATUS', ['Menunggu', 'Dibatalkan'])->values();

        // ── Calculate scores ──────────────────────────────────────────
        $waiting = $waiting->map(function ($q) {
            $q->dynamic_score   = $this->calculateDynamicScore($q);
            $q->tensi_score     = $this->calculateTensiScore($q);
            $q->waiting_minutes = $this->getWaitingMinutes($q);
            return $q;
        });

        $serving = $serving->map(function ($q) {
            $q->dynamic_score   = $this->calculateDynamicScore($q);
            $q->tensi_score     = $this->calculateTensiScore($q);
            $q->waiting_minutes = $this->getWaitingMinutes($q);
            return $q;
        });

        // ── Shadow: start from last saved SHADOW_POSITION order ──────
        $shadowOnly = $waiting->where('FIXED_QUEUE_STATUS', 0)
                               ->sortBy('SHADOW_POSITION')
                               ->values();

        // ── Re-sort shadow queue dinonaktifkan ────────────────────────
        // Agar rule maksimal bypass 2 posisi (yang sudah diatur saat pasien baru
        // masuk di fungsi bubbleUpNewPatient) tidak rusak / berulang kali bypass
        // setiap kali halaman dashboard di-refresh.
        $shadowSorted = $shadowOnly->values();

        // ── Detect bypasses dan update ADJUSTED_STATUS ────────────────
        foreach ($shadowSorted as $newPos => $q) {
            $oldPos = $q->SHADOW_POSITION;

            if ($oldPos !== null && $newPos > $oldPos && $q->ADJUSTED_STATUS < 2) {
                $newAdjusted = min($q->ADJUSTED_STATUS + 1, 2);
                DB::table('tr_queue_polyclinic')
                    ->where('QUEUE_ID', $q->QUEUE_ID)
                    ->update([
                        'ADJUSTED_STATUS' => $newAdjusted,
                        'SHADOW_POSITION' => $newPos,
                    ]);
                $q->ADJUSTED_STATUS = $newAdjusted;
                $q->SHADOW_POSITION = $newPos;
            } elseif ($oldPos !== $newPos) {
                DB::table('tr_queue_polyclinic')
                    ->where('QUEUE_ID', $q->QUEUE_ID)
                    ->update(['SHADOW_POSITION' => $newPos]);
                $q->SHADOW_POSITION = $newPos;
            }
        }

        // ── Fixed queue ───────────────────────────────────────────────
        $alreadyFixed = $waiting->where('FIXED_QUEUE_STATUS', 1)
                                 ->sortBy('CREATED_AT')
                                 ->values();

        // ── Fill empty Fixed slots from shadow ────────────────────────
        $emptySlots = max(0, 3 - $serving->count() - $alreadyFixed->count());

        if ($emptySlots > 0) {
            $toPromote = $shadowSorted->sortByDesc(function ($q) {
                $waitBonus     = min($q->waiting_minutes / 30, 1) * 2.0;
                $adjustedBonus = ($q->ADJUSTED_STATUS / 2) * 1.5;
                return $q->dynamic_score + $waitBonus + $adjustedBonus;
            })->take($emptySlots);

            if ($toPromote->count() > 0) {
                DB::table('tr_queue_polyclinic')
                    ->whereIn('QUEUE_ID', $toPromote->pluck('QUEUE_ID'))
                    ->update(['FIXED_QUEUE_STATUS' => 1]);

                $toPromote = $toPromote->map(function ($q) {
                    $q->FIXED_QUEUE_STATUS = 1;
                    return $q;
                });

                $promotedIds  = $toPromote->pluck('QUEUE_ID')->all();
                $alreadyFixed = $alreadyFixed->merge($toPromote)
                                             ->sortBy('CREATED_AT')->values();
                $shadowSorted = $shadowSorted->filter(
                    fn($q) => !in_array($q->QUEUE_ID, $promotedIds)
                )->values();
            }
        }

        $data['fixed_queue']           = $serving->merge($alreadyFixed)->values();
        $data['shadow_queue']          = $shadowSorted->take(7)->values();
        $data['remaining_queue_count'] = $waiting->count();

        // ── Schedules ─────────────────────────────────────────────────
        $currentSchedules = DB::table('md_doctor_schedule as mds')
            ->leftJoin('md_doctor as md',     'mds.DOCTOR_ID', '=', 'md.DOCTOR_ID')
            ->leftJoin('md_poly as mpoly',    'mds.POLY_ID',   '=', 'mpoly.POLY_ID')
            ->leftJoin('md_poly_room as mpr', 'mpoly.ROOM_ID', '=', 'mpr.ROOM_ID')
            ->where('mds.DAY', $today)
            ->whereRaw("TIME_TO_SEC(mds.TIME_START) <= TIME_TO_SEC(?)", [$now])
            ->whereRaw("TIME_TO_SEC(mds.TIME_END) >= TIME_TO_SEC(?)", [$cutoff])
            ->where('mds.IS_ACTIVE', 1)
            ->where('mpoly.IS_ACTIVE', 1)
            ->orderBy('mds.TIME_START')
            ->get();

        $data['available_schedules'] = $currentSchedules->isNotEmpty()
            ? $currentSchedules
            : DB::table('md_doctor_schedule as mds')
                ->leftJoin('md_doctor as md',     'mds.DOCTOR_ID', '=', 'md.DOCTOR_ID')
                ->leftJoin('md_poly as mpoly',    'mds.POLY_ID',   '=', 'mpoly.POLY_ID')
                ->leftJoin('md_poly_room as mpr', 'mpoly.ROOM_ID', '=', 'mpr.ROOM_ID')
                ->where('mds.DAY', $today)
                ->whereRaw("TIME_TO_SEC(mds.TIME_START) > TIME_TO_SEC(?)", [$now])
                ->where('mds.IS_ACTIVE', 1)
                ->where('mpoly.IS_ACTIVE', 1)
                ->orderBy('mds.TIME_START')
                ->limit(1)
                ->get();

        return
            view('admin.templates.header',  $data) .
            view('admin.templates.sidebar') .
            view('admin.dashboard',         $data) .
            view('admin.templates.footer');
    }

    // ── Search Patients ───────────────────────────────────────────────
    public function searchPatients(Request $request)
{
    $query = trim($request->get('q', ''));
    if (strlen($query) < 1) return response()->json([]);

    // Ambil PATIENT_ID yang sudah terdaftar di antrean aktif hari ini
    $alreadyQueued = DB::table('tr_queue_polyclinic')
        ->whereIn('QUEUE_STATUS', ['Menunggu', 'Dilayani'])
        ->where('IS_ACTIVE', 1)
        ->where('REGISTRATION_DATE', now()->toDateString())
        ->pluck('PATIENT_ID');

    return response()->json(
        DB::table('md_patient')
            ->where('PATIENT_NAME', 'like', '%' . $query . '%')
            ->where('IS_ACTIVE', 1)
            ->whereNotIn('PATIENT_ID', $alreadyQueued)  // ← exclude yang sudah antri
            ->orderBy('PATIENT_NAME')
            ->limit(10)
            ->select('PATIENT_ID', 'PATIENT_NAME', 'BIRTHDATE')
            ->get()
            ->map(fn($p) => [
                'PATIENT_ID'   => $p->PATIENT_ID,
                'PATIENT_NAME' => $p->PATIENT_NAME,
                'AGE'          => $p->BIRTHDATE
                                    ? \Carbon\Carbon::parse($p->BIRTHDATE)->age
                                    : null,
            ])
    );
}

    // ── Create Queue ──────────────────────────────────────────────────
    public function create_polyclinic_queue(Request $req)
    {
        $now = now();

        DB::table('tr_queue_polyclinic')->insert([
            'QUEUE_NUMBER'            => 'A-' . rand(100, 999),
            'PATIENT_ID'              => $req->patient_id,
            'SCHEDULE_ID'             => $req->schedule_id,
            'REGISTRATION_DATE'       => $now->toDateString(),
            'REGISTRATION_TIME'       => $now->toTimeString(),
            'SYSTOLIC'                => (int) $req->tekanan_sistolik,
            'DIASTOLIC'               => (int) $req->tekanan_diastolik,
            'COMPLAINT_SCORE'         => (int) ($req->prioritas_level ?? 0),
            'SPECIAL_CONDITION_SCORE' => $req->is_prioritas ? 1 : 0,
            'QUEUE_STATUS'            => 'Menunggu',
            'ADJUSTED_STATUS'         => 0,
            'FIXED_QUEUE_STATUS'      => 0,
            'SHADOW_POSITION'         => null,
            'COMPLAINTS'              => $req->keluhan,
            'IS_ACTIVE'               => 1,
        ]);

        $newId = DB::getPdo()->lastInsertId();
        $this->bubbleUpNewPatient($newId);

        return back()->with('success', 'Antrean berhasil dibuat');
    }

    // ── Bubble-Up Logic ───────────────────────────────────────────────
    //
    // RULES:
    //   1. Pasien baru selalu masuk di posisi paling bawah (index N-1).
    //
    //   2. PRE-SCAN sebelum loop — cari hardFloor:
    //      Scan semua pasien di atas pasien baru. Protected = ADJUSTED_STATUS >= 2
    //      ATAU waiting_minutes >= 30. Ambil index TERBESAR dari semua protected.
    //      "Index terbesar" = protected paling bawah = tembok pertama dari bawah.
    //      → minimumPosition = hardFloor + 1
    //      → Pasien baru tidak boleh ada di index <= hardFloor.
    //
    //   3. Batas naik maksimal 2 posisi dari posisi awal:
    //      → maxBubbleCeiling = (N-1) - 2
    //
    //   4. finalCeiling = MAX(minimumPosition, maxBubbleCeiling)
    //      Pasien baru berhenti jika currentPosition <= finalCeiling.
    //
    //   5. Loop bubble: naik satu per satu sampai finalCeiling atau kondisi stop:
    //      a. Target PROTECTED (guard ganda) → STOP
    //      b. score baru <= score target     → STOP
    //      c. Lolos → SWAP: TARGET saja +1 ADJUSTED_STATUS (max 2)
    //
    // TRACE Rinda (score 0.67) — queue: [Sudimoro(0), Ahmad(1,wait=30), Hondo(2), Dewi(3,adj=1), Luna(4)]
    //   Rinda masuk index 5.
    //   PRE-SCAN → Ahmad idx=1 (veteran) → hardFloor=1 → minimumPosition=2
    //   maxBubbleCeiling = 5-2 = 3 → finalCeiling = max(2,3) = 3
    //   iter 1: pos=5→4, Luna(0.17)<0.67 → SWAP, Luna adj→1, pos jadi 4
    //   iter 2: pos=4→3, Dewi(0.20)<0.67 → SWAP, Dewi adj→2, pos jadi 3
    //   pos=3 <= finalCeiling(3) → STOP
    //   Hasil: [Sudimoro, Ahmad, Hondo, Rinda, Dewi, Luna] ✓
    //
    // TRACE Mina (score 0.30) — queue: [Sudimoro(0), Ahmad(1,wait≥30), Hondo(2), Rinda(3), Dewi(4,adj=2), Luna(5,adj=1)]
    //   Mina masuk index 6.
    //   PRE-SCAN → Ahmad idx=1 (veteran), Dewi idx=4 (frozen) → hardFloor=4
    //   minimumPosition=5, maxBubbleCeiling=6-2=4 → finalCeiling=max(5,4)=5
    //   iter 1: pos=6→5, Luna(adj=1, score<0.30) → SWAP, Luna adj→2, pos jadi 5
    //   pos=5 <= finalCeiling(5) → STOP
    //   Hasil: [Sudimoro, Ahmad, Hondo, Rinda, Dewi, Mina, Luna] ✓
    //
    private function bubbleUpNewPatient(int $newQueueId): void
    {
        // ── STEP 1: Fetch shadow queue ────────────────────────────────
        // Pasien existing → urut ASC by SHADOW_POSITION.
        // Pasien baru (SHADOW_POSITION IS NULL) → paling akhir.
        $shadow = DB::table('tr_queue_polyclinic as tq')
            ->leftJoin('md_patient as mp', 'tq.PATIENT_ID', '=', 'mp.PATIENT_ID')
            ->where('tq.IS_ACTIVE', 1)
            ->where('tq.FIXED_QUEUE_STATUS', 0)
            ->where('tq.QUEUE_STATUS', 'Menunggu')
            ->select(
                'tq.QUEUE_ID', 'tq.ADJUSTED_STATUS',
                'tq.SHADOW_POSITION', 'tq.SYSTOLIC', 'tq.DIASTOLIC',
                'tq.COMPLAINT_SCORE', 'tq.SPECIAL_CONDITION_SCORE',
                'tq.REGISTRATION_DATE', 'tq.REGISTRATION_TIME',
                'mp.BIRTHDATE'
            )
            ->orderByRaw('tq.SHADOW_POSITION IS NULL ASC')
            ->orderBy('tq.SHADOW_POSITION', 'ASC')
            ->get();

        if ($shadow->isEmpty()) return;

        // ── STEP 2: Assign posisi 0,1,2,…N-1 ─────────────────────────
        $shadowArr = [];
        foreach ($shadow as $idx => $q) {
            $q->SHADOW_POSITION = $idx;
            $shadowArr[$idx]    = $q;
        }

        foreach ($shadowArr as $idx => $q) {
            DB::table('tr_queue_polyclinic')
                ->where('QUEUE_ID', $q->QUEUE_ID)
                ->update(['SHADOW_POSITION' => $idx]);
        }

        // Temukan index pasien baru
        $newIndex = null;
        foreach ($shadowArr as $i => $q) {
            if ($q->QUEUE_ID === $newQueueId) {
                $newIndex = $i;
                break;
            }
        }

        // Tidak ditemukan atau pasien pertama → tidak ada yang di-bubble
        if ($newIndex === null || $newIndex === 0) return;

        $newPatient      = $shadowArr[$newIndex];
        $newScore        = $this->calculateDynamicScore($newPatient);
        $currentPosition = $newIndex;

        // ── STEP 3: PRE-SCAN — cari hardFloor ────────────────────────
        // Scan index 0 s/d newIndex-1.
        // Cari index TERBESAR dari semua pasien protected.
        $hardFloor = -1;

        for ($i = 0; $i < $newIndex; $i++) {
            $q         = $shadowArr[$i];
            $isFrozen  = ($q->ADJUSTED_STATUS == 2);
            $isVeteran = ($this->getWaitingMinutes($q) >= 30);

            if (($isFrozen || $isVeteran) && $i > $hardFloor) {
                $hardFloor = $i;
            }
        }

        // Hitung finalCeiling: batas currentPosition agar loop berhenti
        $minimumPosition  = $hardFloor + 1;       // tidak boleh naik melewati protected
        $maxBubbleCeiling = $newIndex - 2;  // maks naik 2 posisi
        $finalCeiling     = max($minimumPosition, $maxBubbleCeiling);

        // Tidak ada ruang untuk naik
        if ($currentPosition <= $finalCeiling) {
            return;
        }

        // ── STEP 4: Loop bubble-up ────────────────────────────────────
        $swapCount = 0;
        while ($currentPosition > $finalCeiling && $swapCount < 2) {

            $targetIndex   = $currentPosition - 1;
            $targetPatient = $shadowArr[$targetIndex];

            // Guard ganda: pastikan target bukan protected
            if ($targetPatient->ADJUSTED_STATUS == 2 ||
                $this->getWaitingMinutes($targetPatient) >= 30) {
                break;
            }

            // Hanya naik jika score pasien baru lebih tinggi
            $targetScore = $this->calculateDynamicScore($targetPatient);
            if ($newScore <= $targetScore) {
                break;
            }

            // SWAP — hanya TARGET yang ADJUSTED_STATUS +1, pasien baru tidak berubah
            $newAdjusted = min($targetPatient->ADJUSTED_STATUS + 1, 2);

            DB::table('tr_queue_polyclinic')
                ->where('QUEUE_ID', $targetPatient->QUEUE_ID)
                ->update([
                    'ADJUSTED_STATUS' => $newAdjusted,
                    'SHADOW_POSITION' => $currentPosition,
                ]);

            $targetPatient->ADJUSTED_STATUS = $newAdjusted;
            $targetPatient->SHADOW_POSITION = $currentPosition;
            $shadowArr[$currentPosition]    = $targetPatient;

            $newPatient->SHADOW_POSITION = $targetIndex;
            $shadowArr[$targetIndex]     = $newPatient;
            $currentPosition             = $targetIndex;
            $swapCount++;
        }

        // ── STEP 5: Simpan posisi akhir pasien baru ───────────────────
        DB::table('tr_queue_polyclinic')
            ->where('QUEUE_ID', $newQueueId)
            ->update(['SHADOW_POSITION' => $currentPosition]);
    }

    public function queuePanggil(Request $request)
    {
        DB::table('tr_queue_polyclinic')
            ->where('QUEUE_ID', $request->queue_id)
            ->update(['QUEUE_STATUS' => 'Dilayani']);
            
        return response()->json(['success' => true]);
    }

    public function queueMissed(Request $request)
    {
        DB::table('tr_queue_polyclinic')
            ->where('QUEUE_ID', $request->queue_id)
            ->update(['QUEUE_STATUS' => 'Missed']);
            
        return response()->json(['success' => true]);
    }

    public function queueSelesai(Request $request)
    {
        DB::table('tr_queue_polyclinic')
            ->where('QUEUE_ID', $request->queue_id)
            ->update(['QUEUE_STATUS' => 'Selesai']);
            
        return response()->json(['success' => true]);
    }

    public function index_dashboard_pasien()
{
    $data['title'] = 'Antrean Pasien';
 
    $days = [
        0 => 'Minggu', 1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
        4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu'
    ];
    $today = $days[now()->dayOfWeek];
 
    // ── Fetch semua antrean aktif hari ini ────────────────────────────
    $queues = DB::table('tr_queue_polyclinic as tq')
        ->leftJoin('md_patient as mp',          'tq.PATIENT_ID',  '=', 'mp.PATIENT_ID')
        ->leftJoin('md_doctor_schedule as mds', 'tq.SCHEDULE_ID', '=', 'mds.SCHEDULE_ID')
        ->leftJoin('md_doctor as md',           'mds.DOCTOR_ID',  '=', 'md.DOCTOR_ID')
        ->leftJoin('md_poly as mpoly',          'mds.POLY_ID',    '=', 'mpoly.POLY_ID')
        ->leftJoin('md_poly_room as mpr',       'mpoly.ROOM_ID',  '=', 'mpr.ROOM_ID')
        ->whereIn('tq.QUEUE_STATUS', ['Menunggu', 'Dilayani'])
        ->where('tq.IS_ACTIVE', 1)
        ->when($today, fn($q) => $q->where('mds.DAY', $today))
        ->select(
            'tq.QUEUE_ID', 'tq.QUEUE_NUMBER', 'tq.QUEUE_STATUS',
            'tq.SYSTOLIC', 'tq.DIASTOLIC',
            'tq.COMPLAINT_SCORE', 'tq.SPECIAL_CONDITION_SCORE',
            'tq.FIXED_QUEUE_STATUS', 'tq.CREATED_AT',
            'tq.REGISTRATION_DATE', 'tq.REGISTRATION_TIME',
            'mp.PATIENT_NAME', 'mp.BIRTHDATE',
            'md.DOCTOR_NAME', 'mpoly.POLY_NAME', 'mpr.ROOM_NAME'
        )
        ->get();
 
    $serving = $queues->where('QUEUE_STATUS', 'Dilayani')->values();
    $waiting = $queues->where('QUEUE_STATUS', 'Menunggu')->values();
 
    // ── Hitung skor & waktu tunggu ────────────────────────────────────
    $serving = $serving->map(function ($q) {
        $q->waiting_minutes = $this->getWaitingMinutes($q);
        return $q;
    });
 
    $waiting = $waiting->map(function ($q) {
        $q->dynamic_score   = $this->calculateDynamicScore($q);
        $q->waiting_minutes = $this->getWaitingMinutes($q);
        return $q;
    });
 
    // ── Ambil hanya Fixed Queue (FIXED_QUEUE_STATUS = 1) ─────────────
    // Urutan: yang sedang dilayani di atas, lalu waiting fixed queue
    $fixedWaiting = $waiting->where('FIXED_QUEUE_STATUS', 1)
                             ->sortBy('CREATED_AT')
                             ->values();
 
    // Gabungkan: sedang dilayani + fixed waiting
    $data['fixed_queue']           = $serving->merge($fixedWaiting)->values();
    $data['total_waiting']         = $waiting->count(); // total semua yang masih menunggu
    $data['serving_queue']         = $serving->first();  // pasien yang sedang dilayani
 
    return
        view('patient.templates.header', $data).
        view('patient.dashboard',        $data).
        view('patient.templates.footer');
}

// ── Store Patient ──────────────────────────────────────────────────────
public function store_pasien(Request $req)
{
    $req->validate([
        'PATIENT_NAME' => 'required|string|max:255',
        'NIK'          => 'nullable|string|max:16',
        'JK'           => 'required|in:Laki-laki,Perempuan',
        'BIRTHDATE'    => 'required|date',
        'PHONE'        => 'nullable|string|max:20',
        'ADDRESS'      => 'nullable|string',
    ]);

    DB::table('md_patient')->insert([
        'PATIENT_NAME' => $req->PATIENT_NAME,
        'NIK'          => $req->NIK,
        'JK'           => $req->JK,
        'BIRTHDATE'    => $req->BIRTHDATE,
        'PHONE'        => $req->PHONE,
        'ADDRESS'      => $req->ADDRESS,
        'IS_ACTIVE'    => 1,
        'CREATED_AT'   => now(),
        'UPDATED_AT'   => now(),
    ]);

    return back()->with('success', 'Pasien berhasil ditambahkan.');
}

// ── Update Patient ─────────────────────────────────────────────────────
public function update_pasien(Request $req)
{
    $req->validate([
        'PATIENT_ID'   => 'required|integer',
        'PATIENT_NAME' => 'required|string|max:255',
        'NIK'          => 'nullable|string|max:16',
        'JK'           => 'required|in:Laki-laki,Perempuan',
        'BIRTHDATE'    => 'required|date',
        'PHONE'        => 'nullable|string|max:20',
        'ADDRESS'      => 'nullable|string',
    ]);

    DB::table('md_patient')
        ->where('PATIENT_ID', $req->PATIENT_ID)
        ->update([
            'PATIENT_NAME' => $req->PATIENT_NAME,
            'NIK'          => $req->NIK,
            'JK'           => $req->JK,
            'BIRTHDATE'    => $req->BIRTHDATE,
            'PHONE'        => $req->PHONE,
            'ADDRESS'      => $req->ADDRESS,
            'UPDATED_AT'   => now(),
        ]);

    return back()->with('success', 'Data pasien berhasil diperbarui.');
}

// ── Delete Patient (Soft Delete) ───────────────────────────────────────
public function delete_pasien(Request $req)
{
    $req->validate(['PATIENT_ID' => 'required|integer']);

    DB::table('md_patient')
        ->where('PATIENT_ID', $req->PATIENT_ID)
        ->update([
            'IS_ACTIVE'  => 0,
            'UPDATED_AT' => now(),
        ]);

    return back()->with('success', 'Pasien berhasil dinonaktifkan.');
}
}