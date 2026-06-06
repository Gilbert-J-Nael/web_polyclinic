<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index_pasien()
    {
        $data['title'] = 'Master Data Pasien';

        $data['patients'] = DB::table('md_patient')
            ->where('IS_ACTIVE', 1)
            ->get();

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.master_data.patient', $data) .
            view('admin.templates.footer');
    }

public function store_pasien(Request $req)
{
    $req->validate([
        'PATIENT_NAME' => 'required|string|max:255',
        'NIK'          => 'nullable|string|max:16',
        'GENDER'           => 'required|in:Male,Female',
        'BIRTHDATE'    => 'required|date',
        'PHONE'        => 'nullable|string|max:20',
        'ADDRESS'      => 'nullable|string',
    ]);

    DB::table('md_patient')->insert([
        'PATIENT_NAME' => $req->PATIENT_NAME,
        'NIK'          => $req->NIK,
        'GENDER'           => $req->GENDER,
        'BIRTHDATE'    => $req->BIRTHDATE,
        'PHONE'        => $req->PHONE,
        'ADDRESS'      => $req->ADDRESS,
        'IS_ACTIVE'    => 1,
        'CREATED_AT'   => now(),
        'UPDATED_AT'   => now(),
    ]);

    return back()->with('success', 'Pasien berhasil ditambahkan.');
}

public function update_pasien(Request $req)
{
    $req->validate([
        'PATIENT_ID'   => 'required|integer',
        'PATIENT_NAME' => 'required|string|max:255',
        'NIK'          => 'nullable|string|max:16',
        'GENDER'       => 'required|in:Male,Female',
        'BIRTHDATE'    => 'required|date',
        'PHONE'        => 'nullable|string|max:20',
        'ADDRESS'      => 'nullable|string',
    ]);

    DB::table('md_patient')
        ->where('PATIENT_ID', $req->PATIENT_ID)
        ->update([
            'PATIENT_NAME' => $req->PATIENT_NAME,
            'NIK'          => $req->NIK,
            'GENDER'       => $req->GENDER,
            'BIRTHDATE'    => $req->BIRTHDATE,
            'PHONE'        => $req->PHONE,
            'ADDRESS'      => $req->ADDRESS,
            'UPDATED_AT'   => now(),
        ]);

    return back()->with('success', 'Data pasien berhasil diperbarui.');
}

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

    public function index_dokter()
    {
        $data['title'] = 'Master Data Dokter';

        $data['doctors'] = DB::table('md_doctor')
            ->leftJoin('md_doctor_specialization', 'md_doctor.SPECIALIZATION_ID', '=', 'md_doctor_specialization.SPECIALIZATION_ID')
            ->where('md_doctor.IS_ACTIVE', 1)
            ->select('md_doctor.*', 'md_doctor_specialization.SPECIALIZATION')
            ->get();
        
        $data['specialization'] = DB::table('md_doctor_specialization')->get();

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.master_data.doctor', $data) .
            view('admin.templates.footer');
    }

    private function GenerateDoctorID($specializationId)
{
    // Ambil nama spesialisasi
    $specialization = DB::table('md_doctor_specialization')
        ->where('SPECIALIZATION_ID', $specializationId)
        ->value('SPECIALIZATION');

    // Ambil kata terakhir → "Dokter Spesialis Alergi" → "Alergi"
    $words    = explode(' ', trim($specialization));
    $lastWord = preg_replace('/[^a-zA-Z]/', '', end($words));

    // Ambil 3 huruf pertama dari kata terakhir → "Ale"
    $prefix = strtoupper(substr($lastWord, 0, 3)); // → "ALE"

    // Hitung dokter dengan prefix yang sama → SPE-ALE-xxx
    $count = DB::table('md_doctor')
        ->where('DOCTOR_ID', 'LIKE', "SPE-{$prefix}-%")
        ->count();

    $nomor = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

    return "SPE-{$prefix}-{$nomor}";
}

    public function store_dokter(Request $req)
{
    $req->validate([
        'DOCTOR_NAME'       => 'required|string|max:200',
        'SPECIALIZATION_ID' => 'required|integer|exists:md_doctor_specialization,SPECIALIZATION_ID',
        'DOCTOR_PHONE'      => 'required|string|max:20',
        'DOCTOR_ADDRESS'    => 'required|string',
    ]);

    $doctorId = $this->GenerateDoctorID($req->SPECIALIZATION_ID);

    DB::table('md_doctor')->insert([
        'DOCTOR_ID'         => $doctorId,
        'DOCTOR_NAME'       => $req->DOCTOR_NAME,
        'SPECIALIZATION_ID' => $req->SPECIALIZATION_ID,
        'DOCTOR_PHONE'      => $req->DOCTOR_PHONE,
        'DOCTOR_ADDRESS'    => $req->DOCTOR_ADDRESS,
        'IS_ACTIVE'         => 1,
        'CREATED_AT'        => now(),
        'UPDATED_AT'        => now(),
    ]);

    return back()->with('success', "Dokter berhasil ditambahkan. (ID: {$doctorId})");
}

public function update_dokter(Request $req)
{
    $req->validate([
        'DOCTOR_ID'         => 'required|string|max:20',
        'DOCTOR_NAME'       => 'required|string|max:200',
        'SPECIALIZATION_ID' => 'required|integer|exists:md_doctor_specialization,SPECIALIZATION_ID',
        'DOCTOR_PHONE'      => 'required|string|max:20',
        'DOCTOR_ADDRESS'    => 'required|string',
    ]);

    // Cek apakah spesialisasi berubah
    $oldDoctor = DB::table('md_doctor')
        ->where('DOCTOR_ID', $req->DOCTOR_ID)
        ->first();

    $updateData = [
        'DOCTOR_NAME'       => $req->DOCTOR_NAME,
        'SPECIALIZATION_ID' => $req->SPECIALIZATION_ID,
        'DOCTOR_PHONE'      => $req->DOCTOR_PHONE,
        'DOCTOR_ADDRESS'    => $req->DOCTOR_ADDRESS,
        'UPDATED_AT'        => now(),
    ];

    // Jika spesialisasi berubah → generate DOCTOR_ID baru
    if ($oldDoctor->SPECIALIZATION_ID != $req->SPECIALIZATION_ID) {
        $newDoctorId = $this->GenerateDoctorID($req->SPECIALIZATION_ID);
        $updateData['DOCTOR_ID'] = $newDoctorId;
    }

    DB::table('md_doctor')
        ->where('DOCTOR_ID', $req->DOCTOR_ID)
        ->update($updateData);

    $finalId = $updateData['DOCTOR_ID'] ?? $req->DOCTOR_ID;

    return back()->with('success', 'Data dokter berhasil diperbarui.');
}

public function delete_dokter(Request $req)
{
    $req->validate(['DOCTOR_ID' => 'required|string']);

    DB::table('md_doctor')
        ->where('DOCTOR_ID', $req->DOCTOR_ID)
        ->update([
            'IS_ACTIVE'  => 0,
            'UPDATED_AT' => now(),
        ]);

    return back()->with('success', 'Dokter berhasil dinonaktifkan.');
}


    public function index_jadwal_dokter()
    {
        $data['title'] = 'Master Data Jadwal Dokter';

        $data['doctors'] = DB::table('md_doctor as md')
            ->leftJoin('md_doctor_specialization as mds', 'md.SPECIALIZATION_ID', '=', 'mds.SPECIALIZATION_ID')
            ->select('md.*', 'mds.SPECIALIZATION')
            ->where('md.IS_ACTIVE', 1)
            ->get();
        
        $data['polys'] = DB::table('md_poly as mp')
            ->leftJoin('md_poly_room as mpr', 'mp.ROOM_ID', '=', 'mpr.ROOM_ID')
            ->select(
                'mp.POLY_ID', 'mp.POLY_NAME', 
                'mpr.ROOM_NAME'
            )
            ->where('mp.IS_ACTIVE', 1)
            ->get();
        
        $data['schedules'] = DB::table('md_doctor_schedule as mds')
            ->leftJoin('md_doctor as md', 'mds.DOCTOR_ID', '=', 'md.DOCTOR_ID')
            ->leftJoin('md_poly as mp', 'mds.POLY_ID', '=', 'mp.POLY_ID')
            ->select('mds.*', 'md.DOCTOR_NAME')
            ->where('mds.IS_ACTIVE', 1)
            ->paginate(10);
        
        $days = DB::select("SHOW COLUMNS FROM md_doctor_schedule WHERE Field = 'DAY'");
        preg_match("/^enum\((.*)\)$/", $days[0]->Type, $matches);
        $enum = [];
            foreach (explode(',', $matches[1]) as $value) {
                $enum[] = trim($value, "'");
            }
        $data['days'] = $enum;

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.master_data.doctor_schedule', $data) .
            view('admin.templates.footer');
    }

    public function store_jadwal_dokter(Request $req)
{
    $req->validate([
        'DOCTOR_ID'  => 'required|string|exists:md_doctor,DOCTOR_ID',
        'POLY_ID'    => 'required|integer|exists:md_poly,POLY_ID',
        'DAY'        => 'required|string',
        'TIME_START' => 'required',
        'TIME_END'   => 'required',
        'MAX_SLOT'   => 'required|integer|min:1',
    ]);

    DB::table('md_doctor_schedule')->insert([
        'DOCTOR_ID'  => $req->DOCTOR_ID,
        'POLY_ID'    => $req->POLY_ID,
        'DAY'        => $req->DAY,
        'TIME_START' => $req->TIME_START,
        'TIME_END'   => $req->TIME_END,
        'MAX_SLOT'   => $req->MAX_SLOT,
        'IS_ACTIVE'  => 1,
        'CREATED_AT' => now(),
        'UPDATED_AT' => now(),
    ]);

    return redirect(url('/master-jadwal-dokter'))
        ->with('success', 'Jadwal dokter berhasil ditambahkan.');
}

public function update_jadwal_dokter(Request $req)
{
    $req->validate([
        'SCHEDULE_ID' => 'required|integer',
        'DOCTOR_ID'   => 'required|string|exists:md_doctor,DOCTOR_ID',
        'POLY_ID'     => 'required|integer|exists:md_poly,POLY_ID',
        'DAY'         => 'required|string',
        'TIME_START'  => 'required',
        'TIME_END'    => 'required',
        'MAX_SLOT'    => 'required|integer|min:1',
    ]);

    DB::table('md_doctor_schedule')
        ->where('SCHEDULE_ID', $req->SCHEDULE_ID)
        ->update([
            'DOCTOR_ID'  => $req->DOCTOR_ID,
            'POLY_ID'    => $req->POLY_ID,
            'DAY'        => $req->DAY,
            'TIME_START' => $req->TIME_START,
            'TIME_END'   => $req->TIME_END,
            'MAX_SLOT'   => $req->MAX_SLOT,
            'UPDATED_AT' => now(),
        ]);

    return redirect(url('/master-jadwal-dokter'))
        ->with('success', 'Jadwal dokter berhasil diperbarui.');
}

public function delete_jadwal_dokter(Request $req)
{
    $req->validate(['SCHEDULE_ID' => 'required|integer']);

    DB::table('md_doctor_schedule')
        ->where('SCHEDULE_ID', $req->SCHEDULE_ID)
        ->update([
            'IS_ACTIVE'  => 0,
            'UPDATED_AT' => now(),
        ]);

    return redirect(url('/master-jadwal-dokter'))
        ->with('success', 'Jadwal dokter berhasil dinonaktifkan.');
}
}
