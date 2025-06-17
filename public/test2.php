<?php
// Izinkan dari mana saja untuk tes ini
header("Access-Control-Allow-Origin: *");
// Tambahkan header kustom untuk bukti
header("X-Custom-Test-Header: Halo-Dari-PHP");

// Atur tipe konten dan kirim respons JSON sederhana
header("Content-Type: application/json");
echo json_encode(['status' => 'ok', 'time' => date('H:i:s')]);