<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoryController extends Controller
{
    /**
     * Tampilkan halaman master data riwayat kunjungan pasien.
     */
    public function index_riwayat_kunjungan(Request $request)
    {
        $data['title'] = 'Riwayat Kunjungan Pasien';

        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $search    = $request->input('search');

        $query = DB::table('tr_queue_polyclinic as tqp')
            ->leftJoin('md_patient as mp',          'tqp.PATIENT_ID',  '=', 'mp.PATIENT_ID')
            ->leftJoin('md_doctor_schedule as mds', 'tqp.SCHEDULE_ID', '=', 'mds.SCHEDULE_ID')
            ->leftJoin('md_doctor as md',           'mds.DOCTOR_ID',   '=', 'md.DOCTOR_ID')
            ->select(
                'tqp.QUEUE_ID',
                'tqp.QUEUE_NUMBER',
                'tqp.REGISTRATION_DATE',
                'tqp.REGISTRATION_TIME',
                'tqp.SYSTOLIC',
                'tqp.DIASTOLIC',
                'tqp.COMPLAINTS',
                'tqp.QUEUE_STATUS',
                'mp.PATIENT_NAME',
                'mp.NIK',
                'mp.GENDER',
                'mp.PHONE',
                'md.DOCTOR_NAME',
                'mds.DAY   as SCHEDULE_DAY',
                'mds.TIME_START',
                'mds.TIME_END'
            )
            ->where('tqp.IS_ACTIVE', 1)
            ->whereIn('tqp.QUEUE_STATUS', ['Selesai', 'Dilayani']);

        if ($startDate) {
            $query->where('tqp.REGISTRATION_DATE', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('tqp.REGISTRATION_DATE', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('mp.PATIENT_NAME', 'LIKE', "%{$search}%")
                  ->orWhere('mp.NIK', 'LIKE', "%{$search}%");
            });
        }

        $data['history']    = $query->orderByDesc('tqp.REGISTRATION_DATE')
                                    ->orderByDesc('tqp.REGISTRATION_TIME')
                                    ->get();
        $data['start_date'] = $startDate;
        $data['end_date']   = $endDate;
        $data['search']     = $search;

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.history', $data) .
            view('admin.templates.footer');
    }

    /**
     * Cetak laporan riwayat kunjungan (halaman print-friendly / buka tab baru).
     */
    public function print_riwayat_kunjungan(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $search    = $request->input('search');

        $query = DB::table('tr_queue_polyclinic as tqp')
            ->leftJoin('md_patient as mp',          'tqp.PATIENT_ID',  '=', 'mp.PATIENT_ID')
            ->leftJoin('md_doctor_schedule as mds', 'tqp.SCHEDULE_ID', '=', 'mds.SCHEDULE_ID')
            ->leftJoin('md_doctor as md',           'mds.DOCTOR_ID',   '=', 'md.DOCTOR_ID')
            ->select(
                'tqp.QUEUE_NUMBER',
                'tqp.REGISTRATION_DATE',
                'tqp.REGISTRATION_TIME',
                'tqp.SYSTOLIC',
                'tqp.DIASTOLIC',
                'tqp.COMPLAINTS',
                'tqp.QUEUE_STATUS',
                'mp.PATIENT_NAME',
                'mp.NIK',
                'mp.GENDER',
                'mp.PHONE',
                'md.DOCTOR_NAME',
                'mds.DAY   as SCHEDULE_DAY',
                'mds.TIME_START',
                'mds.TIME_END'
            )
            ->where('tqp.IS_ACTIVE', 1)
            ->whereIn('tqp.QUEUE_STATUS', ['Selesai', 'Dilayani']);

        if ($startDate) {
            $query->where('tqp.REGISTRATION_DATE', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('tqp.REGISTRATION_DATE', '<=', $endDate);
        }
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('mp.PATIENT_NAME', 'LIKE', "%{$search}%")
                  ->orWhere('mp.NIK', 'LIKE', "%{$search}%");
            });
        }

        $data['history']    = $query->orderBy('tqp.REGISTRATION_DATE')
                                    ->orderBy('tqp.REGISTRATION_TIME')
                                    ->get();
        $data['title']      = 'Laporan Riwayat Kunjungan Pasien';
        $data['start_date'] = $startDate;
        $data['end_date']   = $endDate;

        return view('admin.templates.print_history', $data);
    }
}