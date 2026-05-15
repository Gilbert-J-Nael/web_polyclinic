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
            ->paginate(10);

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.master_data.patient', $data) .
            view('admin.templates.footer');
    }

    public function index_dokter()
    {
        $data['title'] = 'Master Data Dokter';

        $data['doctors'] = DB::table('md_doctor')
            ->leftJoin(
                'md_doctor_specialization', 'md_doctor.SPECIALIZATION_ID', '=', 'md_doctor_specialization.SPECIALIZATION_ID'
            )
            ->where('md_doctor.IS_ACTIVE', 1)
            ->paginate(10);
        
        $data['specialization'] = DB::table('md_doctor_specialization')
            ->get();

        return
            view('admin.templates.header', $data) .
            view('admin.templates.sidebar') .
            view('admin.master_data.doctor', $data) .
            view('admin.templates.footer');
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

    public function create_jadwal_dokter(Request $req)
    {
        $data
    }
}
