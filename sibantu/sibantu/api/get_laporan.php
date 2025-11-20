<?php
// api/get_laporan.php - API untuk mendapatkan data laporan (AJAX)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

$conn = getConnection();

// Query untuk mengambil semua laporan
$sql = "SELECT id, nama_pelapor, lokasi, deskripsi, jenis_bantuan, foto, status, tanggal, created_at 
        FROM laporan 
        ORDER BY created_at DESC";

$result = $conn->query($sql);

$laporan = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $laporan[] = [
            'id' => $row['id'],
            'namaPelapor' => $row['nama_pelapor'],
            'lokasi' => $row['lokasi'],
            'deskripsi' => $row['deskripsi'],
            'jenisBantuan' => $row['jenis_bantuan'],
            'foto' => $row['foto'],
            'status' => $row['status'],
            'tanggal' => $row['tanggal'],
            'created_at' => $row['created_at']
        ];
    }
}

$conn->close();

echo json_encode($laporan);
?>  