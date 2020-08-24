<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthUserController extends Controller
{
    public function __construct()
    {

    }

    public function index() {
        $mhs = Mahasiswa::all();
        return response()->json([
            "status" => true,
            "result" => $mhs
        ], 200);
	}

	public function loginmahasiswa(Request $request) {

		$nim 		= $request->post('nim');
		$password 	= $request->post('password');
		// $imei 		= $this->post('imei');

		if ( $nim != null && $password != null ) {	

				// $this->db->where('nim', $nim);
				// $this->db->where('password', $password);
				// $data_user = $this->db->get('tb_mahasiswa');
				$data_user = DB::table('datamahasiswa')
							->where('nrp', $nim)
							->where('password', $password);
				if($data_user->count() > 0) {

					$data = $data_user->get();
					return response()->json([
						'status'=> 200, 'message'=> 'Login Berhasil', 'result'=> $data
					], 200);
				} else {
					return response()->json([
						'status'=> 500, 'message'=> 'Nim atau Password Salah'
					], 500);
				}	
			} 
		else {
			return response()->json([
				'status'=> 500, 'message'=> 'Data Tidak Boleh Kosong'
			], 500);
		}
	}

	public function logindosen(Request $request) {

		$username = $request->post('username');
		$password = $request->post('password');

		if( isset( $username ) && isset( $password ) ) {
			// $data_dosen = $this->db->get('tb_dosen');
			$data_dosen = DB::table('pegawai')
						->where('nip', $username)->where('password', $password);

			if( $data_dosen->count() > 0 ) {
				$data = $data_dosen->get();
				return response()->json([
					'status'=> 200, 'message'=> 'Login Berhasil', 'result'=> $data
				], 200);
			} else {
				return response()->json([
					'status'=> 400, 'message'=> 'Gagal Login'
				], 400);
			}
		} else {
			return response()->json([
				'status'=> 400, 'message'=> 'Data Tidak boleh Kosong'
			], 400);
		}
	}
}
    
