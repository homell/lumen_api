<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/key', 'AuthController@generatekey');
$router->post('/login', 'AuthController@login');
$router->post('/register', 'AuthController@register');

// Router dan Endpoint pada Lumen API untuk Mahasiswa
$router->post('loginmhs', 'AuthUserController@loginmahasiswa');
$router->get('jadwalmhs', 'JadwalController@jadwalmahasiswa');
$router->post('absensi', 'AbsenController@absensi');
$router->post('izin', 'IzinController@izin'); //--
$router->post('suratizin', 'IzinController@suratizin');
$router->post('deleteizin', 'IzinController@deleteizin');
$router->get('pilihmatkul', 'PilihmatkulController@pilihmatkul');
$router->get('histori', 'HistoriController@histori');
$router->get('filtermatkul', 'FiltermatkulController@filtermatkul');
$router->post('mhstoken', 'StoretokenmhsController@index');
$router->post('mhstokenpost', 'StoretokenmhsController@MhsToken_post');

// Router dan Endpoint pada Lumen API untuk Dosen
$router->post('logindsn', 'AuthUserController@logindosen');
$router->get('jadwaldsn', 'JadwalController@jadwaldosen');
$router->get('matakuliah', 'UbahjadwalController@matakuliah');
$router->put('pertemuan', 'UbahjadwalController@pertemuan');
$router->get('historidsn', 'HistoridosenController@historidosen');
$router->put('ubahkehadiran', 'HistoridosenController@ubahkehadiran_mhs');
$router->get('historimatkul', 'HistoridosenController@matkul');
$router->get('historikelas', 'HistoridosenController@kelas');
$router->get('historiminggu', 'HistoridosenController@minggu');
$router->get('izindosen', 'IzindosenController@index');
$router->post('deleteizindsn', 'IzindosenController@deleteizin');
$router->put('changestatus', 'PdfviewController@change_status');
$router->post('dsntoken', 'StoretokendsnController@index');

