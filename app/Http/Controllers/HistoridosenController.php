<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HistoridosenController extends Controller
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

    public function historidosen(Request $request) {
		$username 	= $request->get('username');
		$matakuliah = $request->get('matakuliah');
		$kelas 		= $request->get('kelas');
		$minggu 	= $request->get('minggu');

		// $username = "'$username'";

		$q_matakuliah = '';
		if( isset($matakuliah) ) {
			$q_matakuliah = DB::table('matakuliah')->select('matakuliah')
						->where('matakuliah', $matakuliah);
		}

		$q_kelas = '';
		if( isset($kelas) ) {
			$q_kelas = DB::table('kelas')->select(DB::raw("concat(kelas, paralel) as kelas"))
						->where('kelas', $kelas);
		}

		// $q_minggu = '';
		// if( isset($minggu) ) {
		// 	$q_minggu = " and tb_jadwal.minggu = '$minggu' ";
			
		// }

		// $data_histori = $this->db->query('
		// 	SELECT
		// 	tb_absen.nim,
		// 	tb_absen.absen_id,
		// 	tb_jadwal.minggu as pertemuan,
		// 	tb_kelas.kelas_nama as kelas,
		// 	tb_mahasiswa.nama, concat(date_format(tb_absen.tanggal, "%d-%m-%Y"),
		// 	" ",date_format(tb_absen.jam,"%H:%i")) AS waktu,
		// 	tb_absen.status_absen AS status
		// 	FROM
		// 	tb_mahasiswa, tb_absen, tb_jadwal, tb_dosen, tb_matakuliah, tb_kelas
		// 	WHERE
		// 	tb_mahasiswa.nim 		= tb_absen.nim AND
		// 	tb_absen.jadwal_id 		= tb_jadwal.jdwl_id AND
		// 	tb_jadwal.dosen_id 		= tb_dosen.dsn_id AND
		// 	tb_matakuliah.kode_mk 	= tb_jadwal.kode_mk AND
		// 	tb_kelas.kelas_id 		= tb_jadwal.kelas_id AND

		// 	tb_dosen.username = '.$username.' '.$q_matakuliah.' '.$q_kelas.' '.$q_minggu.'
		// 	')->result();
		$data_histori = DB::table('absensi_mahasiswa')
					->join('datamahasiswa', 'datamahasiswa.nomor', 'absensi_mahasiswa.mahasiswa')
					->join('kuliah', 'kuliah.nomor', 'absensi_mahasiswa.kuliah')
					->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
					->join('matakuliah', 'matakuliah.nomor', 'kuliah.matakuliah')
					->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					->select('nrp as nim', 'absensi_mahasiswa.nomor as absen_id',
					DB::raw("concat(kelas.kelas, kelas.paralel) as kelas"), 'datamahasiswa.nama', 
					DB::raw("TO_DATE(absensi_mahasiswa.tanggal) as waktu"),
					'absensi_mahasiswa.status')
					->where('pegawai.nip', $username, [$q_matakuliah, $q_kelas])
					// ->whereIn('matakuliah.matakuliah', $q_matakuliah, $q_kelas)
					->get();
		return response()->json(['status'=> 200, 'result'=> $data_histori], 200);
		
	}

	public function ubahkehadiran_mhs(Request $request) 
	{

		$absen_id 		= $request->put('absen_id');
		$status_absen 	= $request->put('status_absen');
		$catatan		= $request->put('catatan');

		if( strlen($catatan) == null || '' ) {
			$catatan = null;
		}

		if( $status_absen == "Alfa" ) {
			$data_izin = DB::table('izin')->where('absen_id', (int)$absen_id);

			if( $data_izin->count() > 0 ) {

				// $this->db->where('absen_id', $absen_id);
				// $this->db->delete('tb_izin');
				DB::table('izin')->delete('izin')
								 ->where('absen_id', (int)$absen_id);

			}

			// $this->db->where('absen_id', $absen_id);
			// $this->db->delete('tb_absen');
			DB::table('absensi_mahasiswa')->delete('absensi_mahasiswa')
							 ->where('nomor', (int)$absen_id);
			return response()->json(['status'=> 200, 'message'=> 'Data Kehadiran Berhasil Dihapus'], 200);
		} else {
			$data_update = array(
				'status' 		=> $status_absen,
				'keterangan'	=> $catatan
			);

			// $this->db->where('absen_id', $absen_id);
			// $this->db->update('tb_absen', $data_update);
			DB::table('absensi_mahasiswa')->update($data_update)
										  ->where('nomor', (int)$absen_id);
			return response()->json(['status'=> 200, 'message' => 'Berhasil Ubah Data Kehadiran ke ' . $status_absen], 200);
		}

	}

	public function matkul(Request $request) {

		$username = $request->get('username');

		// $username = "'$username'";

		$data_matkul = DB::table('kuliah')
					  ->join('matakuliah', 'matakuliah.nomor', 'kuliah.matakuliah')
					  ->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
					  ->select('matakuliah.matakuliah')
					  ->where('pegawai.nip', $username)->distinct()->get();
		return response()->json(['status'=> 200, 'result'=> $data_matkul], 200);
	}

	public function kelas(Request $request) {

		$username = $request->get('username');

		// $username = "'$username'";

		$data_kelas = DB::table('kuliah')
					 ->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					 ->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
					 ->select(DB::raw("concat(kelas.kelas, kelas.paralel) as kelas"))
					 ->where('pegawai.nip', $username)
					 ->distinct()->get();
		return response()->json(['status'=> 200, 'result'=> $data_kelas], 200);
	}

	public function minggu(Request $request) {

		$username = $request->get('username');

		// $username = "'$username'";

		$data_minggu = DB::table('kuliah')
					->join('kelas', 'kelas.nomor', 'kuliah.kelas')
					->join('absensi_mhs_minggu', 'absensi_mhs_minggu.kelas', 'kelas.nomor')
					->join('pegawai', 'pegawai.nomor', 'kuliah.dosen')
					->select('absensi_mhs_minggu.nomor as minggu')
					->where('pegawai.nip', $username)->get();
		return response()->json(['status'=> 200, 'result'=> $data_minggu], 200);	
	}
}