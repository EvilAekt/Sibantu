<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method tidak diizinkan');
}

$nama_pelapor   = sanitize($_POST['namaPelapor'] ?? '');
$lokasi         = sanitize($_POST['lokasi'] ?? '');
$jenis_bantuan  = sanitize($_POST['jenisBantuan'] ?? '');
$deskripsi      = sanitize($_POST['deskripsi'] ?? '');

if (empty($nama_pelapor) || empty($lokasi) || empty($jenis_bantuan) || empty($deskripsi)) {
    jsonResponse(false, 'Semua field wajib diisi');
}

// Upload foto (opsional)
$foto_name = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $upload = uploadFoto($_FILES['foto']);
    
    if (!$upload['success']) {
        jsonResponse(false, $upload['message']);
    }
    
    $foto_name = $upload['filename'];
}

// Tanggal sekarang
$tanggal = date('Y-m-d H:i:s');

$conn = getConnection();

// Prepared statement (100% aman dari SQL Injection)
$stmt = $conn->prepare("
    INSERT INTO laporan 
    (nama_pelapor, lokasi, jenis_bantuan, deskripsi, foto, status, tanggal) 
    VALUES (?, ?, ?, ?, ?, 'Baru', ?)
");

$stmt->bind_param("ssssss", $nama_pelapor, $lokasi, $jenis_bantuan, $deskripsi, $foto_name, $tanggal);

if ($stmt->execute()) {
    $laporan_id = $stmt->insert_id;
    $stmt->close();

    // Ambil statistik terbaru (untuk update hero real-time)
    $stats = $conn->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Diproses' THEN 1 ELSE 0 END) as diproses,
            SUM(CASE WHEN status = 'Selesai' THEN 1 ELSE 0 END) as selesai
        FROM laporan
    ")->fetch_assoc();

    jsonResponse(true, 'Laporan berhasil dikirim!', [
        'id' => $laporan_id,
        'stats' => [
            'total'    => (int)$stats['total'],
            'diproses' => (int)$stats['diproses'],
            'selesai'  => (int)$stats['selesai']
        ]
    ]);
} else {
    $stmt->close();
    jsonResponse(false, 'Gagal menyimpan laporan ke database');
}
?>