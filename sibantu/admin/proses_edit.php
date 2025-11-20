<?php
require_once '../config.php';
if (!isAdmin()) redirect('../index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') redirect('index.php');

$id = (int)$_POST['id'];
$nama = sanitize($_POST['nama_pelapor']);
$lokasi = sanitize($_POST['lokasi']);
$jenis = sanitize($_POST['jenis_bantuan']);
$deskripsi = sanitize($_POST['deskripsi']);
$status = $_POST['status'];

$foto_name = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $upload = uploadFoto($_FILES['foto']);
    if ($upload['success']) {
        $foto_name = $upload['filename'];
        // Hapus foto lama kalau ada
        $old = $conn->query("SELECT foto FROM laporan WHERE id = $id")->fetch_assoc();
        if ($old['foto']) deleteFoto($old['foto']);
    } else {
        die($upload['message']);
    }
}

$conn = getConnection();
if ($foto_name) {
    $sql = "UPDATE laporan SET nama_pelapor=?, lokasi=?, jenis_bantuan=?, deskripsi=?, status=?, foto=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $nama, $lokasi, $jenis, $deskripsi, $status, $foto_name, $id);
} else {
    $sql = "UPDATE laporan SET nama_pelapor=?, lokasi=?, jenis_bantuan=?, deskripsi=?, status=?, updated_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nama, $lokasi, $jenis, $deskripsi, $status, $id);
}

if ($stmt->execute()) {
    redirect("laporan_edit.php?id=$id&success=1");
} else {
    die("Gagal menyimpan");
}
?>