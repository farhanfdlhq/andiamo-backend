<?php
// Mulai atau lanjutkan sesi
session_start();

// Set header untuk CORS agar bisa diakses dari frontend
header("Access-Control-Allow-Origin: https://andiamo.elenmorcreative.com");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Jika ada data yang di-POST, coba tampilkan data sesi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = [
        'message' => 'Ini adalah respon dari request POST.',
        'session_id_saat_ini' => session_id(),
        'data_di_sesi' => $_SESSION['test_data'] ?? 'Tidak ditemukan!',
    ];
    echo json_encode($response);
    exit;
}

// Jika ini request GET, buat data sesi baru
$_SESSION['test_data'] = 'Halo dari sesi ' . date('H:i:s');
$response = [
    'message' => 'Sesi PHP telah dibuat/diperbarui.',
    'session_id_awal' => session_id(),
    'data_yang_disimpan' => $_SESSION['test_data'],
];
echo json_encode($response);