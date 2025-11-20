<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();

$sql = "SELECT id, nama_pelapor, lokasi, deskripsi, jenis_bantuan, status, tanggal, created_at 
        FROM laporan 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=laporan_bantuan_' . date('Y-m-d_His') . '.csv');

$output = fopen('php://output', 'w');

fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

$headers = ['ID', 'Nama Pelapor', 'Lokasi', 'Deskripsi', 'Jenis Bantuan', 'Status', 'Tanggal', 'Dibuat'];
fputcsv($output, $headers);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, [
        $row['id'],
        $row['nama_pelapor'],
        $row['lokasi'],
        $row['deskripsi'],
        $row['jenis_bantuan'],
        $row['status'],
        date('d/m/Y', strtotime($row['tanggal'])),
        date('d/m/Y H:i', strtotime($row['created_at']))
    ]);
}

fclose($output);
$conn->close();
exit();
