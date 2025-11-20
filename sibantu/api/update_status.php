<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';


$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['status'])) {
    jsonResponse(false, 'Data tidak lengkap');
}

$id     = (int)$input['id'];
$status = trim($input['status']);

if ($id <= 0) {
    jsonResponse(false, 'ID tidak valid');
}

$valid_status = ['Baru', 'Diproses', 'Selesai'];
if (!in_array($status, $valid_status, true)) {
    jsonResponse(false, 'Status tidak valid. Pilih: Baru, Diproses, atau Selesai');
}

$conn = getConnection();

$stmt = $conn->prepare("UPDATE laporan SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute() && $stmt->affected_rows > 0) {
    $result = $conn->query("SELECT id, status, updated_at FROM laporan WHERE id = " . (int)$id);
    $laporan = $result->fetch_assoc();

    $stmt->close();
    
    jsonResponse(true, 'Status berhasil diupdate', [
        'id'         => $laporan['id'],
        'status'     => $laporan['status'],
        'updated_at' => $laporan['updated_at'] ?? date('Y-m-d H:i:s')
    ]);
} else {
    $stmt->close();
    jsonResponse(false, 'Gagal update: Laporan tidak ditemukan atau status sama');
}