<?php
require_once 'config.php';

$conn = getConnection();

$nama_pelapor = 'Test User';
$lokasi = 'Sleman';
$jenis_bantuan = 'Kesehatan';
$deskripsi = 'Test deskripsi';
$tanggal = date('Y-m-d');
$status = 'Baru';
$foto = null;

$stmt = $conn->prepare("INSERT INTO laporan (nama_pelapor, lokasi, jenis_bantuan, deskripsi, status, tanggal, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sssssss", $nama_pelapor, $lokasi, $jenis_bantuan, $deskripsi, $status, $tanggal, $foto);

if ($stmt->execute()) {
    echo "BERHASIL! ID: " . $stmt->insert_id;
} else {
    echo "GAGAL: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>