<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsenController extends Controller
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

    public function absensi(Request $request) {
		$nim 	   	= $request->post('nim');
		$data_scan 	= $request->post('dataqr');
		
		date_default_timezone_set('Asia/Jakarta');
		
		$tanggal	=  date("Y-m-d"); 
		// $jam		=  '16:00:00';   // date("H:i:s");

		$data_jadwal = DB::table('kuliah')
					->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					->join('datamahasiswa', 'datamahasiswa.kelas', 'kelas.nomor')
					->join('ruang', 'ruang.nomor', 'kuliah.ruang')
					->join('matakuliah', 'matakuliah.nomor', 'kuliah.matakuliah')
					->select('kuliah.nomor as jadwal_id', 'datamahasiswa.nomor as mahasiswa_id')
					->where('datamahasiswa.nrp', $nim)
					->where('matakuliah.kode', $data_scan)
					->get();
		// return $data_jadwal;
		
		foreach ($data_jadwal as $value) {
		if ( $data_jadwal->isNotEmpty()) {

			// $this->db->where('nim', $nim);
			// $this->db->where('jadwal_id', (int)$value->jadwal_id);	
			// $data_absen = $this->db->get('tb_absen')->result();
			$data_absen = DB::table('absensi_mahasiswa')
						->join('datamahasiswa', 'datamahasiswa.nomor', 'absensi_mahasiswa.mahasiswa')
						->where('datamahasiswa.nrp', $nim)
						->where('absensi_mahasiswa.kuliah', (int)$value->jadwal_id)->get();

			if ( $data_absen->isNotEmpty()) {	
				return response()->json(['status'=>403, 'message'=>'Anda Sudah Absen'], 403);
			} else {

				$status = 'Hadir';
				$data = array(
					"mahasiswa" => $value->mahasiswa_id,
					"kuliah" 	=> (int)$value->jadwal_id,
					"tanggal"	=> $tanggal,
					// "jam"		=> $jam,
					"status"	=> $status,
				);

				// $this->db->insert('tb_absen', $data);
				// $last_id = $this->db->insert_id();
				$last_id = DB::table('absensi_mahasiswa')->insertGetId($data)->get();
//===========================================================================================================//

				// $dsnNip = $this->db->query('SELECT tb_jadwal.dosen_id, tb_dosen.username FROM `tb_jadwal` JOIN tb_dosen ON tb_jadwal.dosen_id = tb_dosen.dsn_id WHERE jdwl_id = '.$value->jadwal_id.'')->result();
				$dsnNip = DB::table('kuliah')->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
							->select('kuliah.dosen as dosen_id', 'pegawai.nip as username')
							->where('kuliah.nomor', $value->jadwal_id)->get();
				$token = $request->DsnToken_post($dsnNip[0]->username);
				$this->notif($token);
				return response()->json([
					'status'	=> 200,
					'message'	=> 'Absensi berhasil',
					'result'	=> $data,
					'id'		=> $last_id
				], 200);
			}
		} else {
			return response()->json(['status'=>500, 'message'=>'Absensi Gagal'], 500);
		}
	}
	}
	

	public function DsnToken_post($nip)
	{
		include 'firebase/dbconfig.php';

		$ref = "tokens/dosen/".$nip;
		$getToken = $database->getReference($ref)
    						 ->getValue();

		return $getToken['token'];
	}
	public function notif(Request $request, $ids) {
		 define( 'API_ACCESS_KEY', 'mykey');
		 $nim = $request->post('nim');
	     $msg = array
	          (
	            'body'  => $nim.' Berhasil Melakukan Presensi',
	            'title' => 'Presensi Diterima',

	          );
	    $fields = array
	            (
	                'registration_ids'        => [$ids], 
	                'notification'  => $msg
	            );

	    $headers = array
	            (
	                'Authorization: key=AAAAiMCqAc4:APA91bFbp43J1ivSpRJuYTBOK7wkOcKb60Q-9qE1CPmYOfZZ5QNDyWs035p5Nsnt1PNDdymMJIdEqMLkO-Zl1fBggTgM2YyaQ0PBGdQKDuJs0elp8W_BryrTJKfdXEKVpXcMeDV5wgyc',
	                'Content-Type: application/json'
	            );
	        $ch = curl_init();
	        curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
	        curl_setopt( $ch,CURLOPT_POST, true );
	        curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
	        curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
	        curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
	        curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );
	        $result = curl_exec($ch );
	        // echo $result;
	        $err = curl_error($ch);

	    // if ($err) {
	    //   echo "cURL Error #:" . $err;
	    // } else {
	    //   print_r($result) ;
	    // }
	  }
}
