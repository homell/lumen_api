<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IzinController extends Controller
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

	public function izin(Request $request) {
		// $this->validate($request, [
		// 	'unggah_file' 	=> 'required|file|mimes:pdf|max_size:2048',
		// 	'keterangan'	=> 'required'
		// ]);

		$nim 				= $request->post('nim');
		$alasan 			= $request->post('alasan');
		$keterangan 		= $request->post('keterangan');
		// $tgl_izin 			= $request->post('tglizin');
		// $tgl_izin_sampai 	= $request->post('tglizinSampai');
		$unggah_file		= $request->file('unggah_file');
		// $name			= $unggah_file->getClientOriginalName();	
		// $extension		= $unggah_file->getClientOriginalExtension();
		// $newname		= $name . '.' .$extension;
		// $path			= $request->file('unggah_file')->storeAs('upload_izin', $newname);

		date_default_timezone_set('Asia/Jakarta');
		$tanggal 	= date("Y-m-d");
		$jam 		= date("H:i:s");

		//Location
		// $longitude 	= $request->post('longitude');
		// $latitude 	= $request->post('latitude');

		//Get Data Jadwal
		// $data_jadwal = $this->db->query('SELECT tb_jdwl_mhs.jadwal_id
		// 	from tb_jdwl_mhs, tb_jadwal, tb_mahasiswa, tb_jam			where
		// 	tb_jadwal.jdwl_id       = tb_jdwl_mhs.jadwal_id and
		// 	tb_jam.jam_id 			= tb_jadwal.jam_id and
		// 	tb_mahasiswa.nim 		= tb_jdwl_mhs.nim and
		// 	tb_mahasiswa.nim 		= "' . $nim . '" and
		// 	tanggal 				= "' . $tanggal . '" and
		// 	"' . $jam . '" between tb_jam.jam_masuk and tb_jam.jam_keluar')->result();
		$data_jadwal = DB::table('kuliah')
					  ->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					  ->join('datamahasiswa', 'datamahasiswa.kelas', 'kelas.nomor')
					  ->select('kuliah.nomor as jadwal_id', 'datamahasiswa.nomor as mahasiswa')
					  ->where('datamahasiswa.nrp', $nim)
					  ->get();

		foreach ($data_jadwal as $value) {
		if ($data_jadwal->isNotEmpty()) {
			if ($nim != NULL && $alasan != NULL && $keterangan != NULL) { //$this->upload->do_upload() == TRUE &&
				// $data = array('upload_data' => $this->upload->data());
				// $file_upload = $this->upload->data();
				$name 		= $unggah_file->getClientOriginalName();
				$path_file	= $request->file('unggah_file')->storeAs('upload_izin', $name);
				
				$data_absen = DB::table('absensi_mahasiswa')
							->join('datamahasiswa', 'datamahasiswa.nomor', 'absensi_mahasiswa.mahasiswa')
							->where('datamahasiswa.nrp', $nim)
							->where('absensi_mahasiswa.kuliah', (int)$value->jadwal_id);
				if ($data_absen->count() > 0) {
					return response()->json(['status'=> 201, 'message'=> "Anda Sudah Absen"], 201);
				} else {
					$insert_absen = array(
						'mahasiswa' 	=> $value->mahasiswa,
						'kuliah'	 	=> (int)$value->jadwal_id,
						'tanggal' 		=> $tanggal,
						// 'jam' 			=> $jam,
						'status' 		=> $alasan
					);

					$last_id = DB::table('absensi_mahasiswa')->insertGetId($insert_absen)->get();
					$insert_izin = array(
						'nrp' 			=> $nim,
						'absen_id' 		=> $last_id,
						'file_upload' 	=> $name,
						'alasan' 		=> $alasan,
						'keterangan' 	=> $keterangan,
						'tanggal' 		=> $tanggal //$tgl_izin
						// 'sampai_tanggal' => $tgl_izin_sampai,
					);
					// $this->db->insert('tb_izin', $insert_izin);
					DB::table('izin')->insert($insert_izin);

		//========================================================================================//
					$dsnNip = DB::table('kuliah')->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
							->select('kuliah.dosen as dosen_id', 'pegawai.nip as username')
							->where('kuliah.nomor', $value->jadwal_id)->get();
					$token = $request->DsnToken_post($dsnNip[0]->username);
					return response()->json(['status'=> 201, 'message'=> 'Berhasil Menambahkan Izin'], 201);
					$this->notif($token);

				}
			} else {
				return response()->json(['status'=> 400, 'message'=> 'Gagal Menambahkan Izin'], 400);
			}
		} else {
			return response()->json(['status'=> 400, 'message'=> 'Data Jadwal Tidak Ditemukan'], 400);
		}
	}
	}

	public function index(Request $request) 
	{
		$username = $request->input->get('username');

		// $data_izin = $this->db->query('
		// 	SELECT tb_izin.*, tb_mahasiswa.nama, tb_mahasiswa.nim, tb_kelas.kelas_nama as kelas, tb_jadwal.minggu, tb_matakuliah.matakuliah 
		// 	FROM `tb_izin` 
		// 	JOIN tb_absen ON tb_izin.absen_id = tb_absen.absen_id 
		// 	JOIN tb_jadwal ON tb_absen.jadwal_id = tb_jadwal.jdwl_id 
		// 	JOIN tb_dosen ON tb_jadwal.dosen_id = tb_dosen.dsn_id 
		// 	JOIN tb_mahasiswa ON tb_izin.nim = tb_mahasiswa.nim 
		// 	JOIN tb_kelas ON tb_jadwal.kelas_id = tb_kelas.kelas_id 
		// 	JOIN tb_matakuliah ON tb_jadwal.kode_mk = tb_matakuliah.kode_mk 
		// 	WHERE tb_dosen.username = '.$username.'
		// 	')->result();
		$data_izin = DB::table('izin')
					->join('absensi_mahasiswa', 'absensi_mahasiswa.nomor', 'izin.absen_id')
					->join('kuliah', 'kuliah.nomor', 'absensi_mahasiswa.kuliah')
					->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
					->join('datamahasiswa', 'datamahasiswa.nrp', 'izin.nrp')
					->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					->join('matakuliah', 'matakuliah.nomor', 'kuliah.matakuliah')
					->select('izin.*', 'datamahasiswa.nama', 'datamahasiswa.nrp as nim',
							DB::raw("concat(kelas.kelas, kelas.paralel) as kelas"),
							'matakuliah.matakuliah')
					->where('pegawai.nip', $username)->get();

		if( $data_izin->isNotEmpty() ) {
			return response()->json(['status'=> 200, 'result'=> $data_izin], 200);
		} else {
			return response()->json(['status'=> 500, 'message'=> 'Tidak Ada Mahasiswa Izin'], 500);
		}

	}

	public function deleteizin(Request $request)
	{
		$izin_id = $request->post('izin_id');
		// $absen_id = $this->db->query('SELECT absen_id FROM `tb_izin` WHERE izin_id = '.$izin_id.'')->result()[0]->absen_id;
		$absen_id = DB::table('izin')->select('absen_id')->where('izin_id', $izin_id)->get()[0]->absen_id;
		// return $absen_id;
		if( "'$absen_id'") {

			// $this->db->query('DELETE FROM tb_izin WHERE izin_id = '.$izin_id.'');
			DB::table('izin')->where('izin_id', $izin_id)->delete();
			// $this->db->query('DELETE FROM tb_absen WHERE absen_id = '.$absen_id.'');

			return response()->json(['status'=> 200, 'message'=> 'Data Berhasil Dihapus'], 200);
		} else {
			return response()->json(['status'=> 500, 'message'=> 'Data Gagal Dihapus'], 500);
		}
		print_r("'$absen_id'");
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
	            'body'  => $nim.' Mengirim Surat izin',
	            'title' => 'Surat izin Diterima ',
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

	  public function suratizin(Request $request)
	  {
		$nim = $request->post("nim");

		// $surat_izin = $this->db->query('SELECT * FROM `tb_izin` WHERE nim = '.$nim.'')->result();
		$surat_izin = DB::table('izin')->select('*')->where('nrp', $nim)->get();

		if( $surat_izin->isNotEmpty()) {
			return response()->json(['status'=> 200, 'result'=> $surat_izin], 200);
		} else {
			return response()->json(['status'=> 500, 'message'=> 'Tidak Ada Surat Izin'], 500);
		}

	  }	
}