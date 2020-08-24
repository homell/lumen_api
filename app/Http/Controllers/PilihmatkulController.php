<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PilihmatkulController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function pilihmatkul(Request $request) {	
		$nim 	 = $request->get('nim');
		// $tanggal = '2018-09-05'; //date('Y-m-d'); set date manual

		$data_pilih = DB::table('kuliah')
					 ->join('matakuliah', 'matakuliah.nomor', 'kuliah.matakuliah')
					 ->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					 ->join('datamahasiswa', 'datamahasiswa.kelas', 'kelas.nomor')
					 ->select('matakuliah.matakuliah', 'kuliah.nomor as jdwl_id')
					 ->where('datamahasiswa.nrp', $nim)
					//  ->where('kuliah.tglujian', $tanggal)
					 ->get();
		return response()->json(['status'=> 200, 'result'=> $data_pilih], 200);

	}
}