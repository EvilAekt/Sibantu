<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Method not allowed', null);
}

$nama_pelapor = sanitize($_POST['namaPelapor'] ?? '');
$lokasi = sanitize($_POST['lokasi'] ?? '');
$jenis_bantuan = sanitize($_POST['jenisBantuan'] ?? '');
$deskripsi = sanitize($_POST['deskripsi'] ?? '');

if (empty($nama_pelapor) || empty($lokasi) || empty($jenis_bantuan) || empty($deskripsi)) {
    jsonResponse(false, 'Semua field wajib diisi', null);
}

$foto_name = null;
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['foto'];

    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $allowed_exts = ['jpg', 'jpeg', 'png'];

    $file_type = $file['type'];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($file_type, $allowed_types) || !in_array($file_ext, $allowed_exts)) {
        jsonResponse(false, 'Tipe file tidak didukung. Gunakan JPG atau PNG', null);
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        jsonResponse(false, 'Ukuran file terlalu besar. Maksimal 5MB', null);
    }

    if (!file_exists(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0777, true);
    }

    $foto_name = 'laporan_' . time() . '_' . uniqid() . '.' . $file_ext;
    $upload_path = UPLOAD_DIR . $foto_name;

    if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
        jsonResponse(false, 'Gagal mengupload foto', null);
    }
}

$conn = getConnection();
$stmt = $conn->prepare("INSERT INTO laporan (nama_pelapor, lokasi, deskripsi, jenis_bantuan, foto, status, tanggal) VALUES (?, ?, ?, ?, ?, 'Baru', ?)");
$stmt->bind_param("ssssss", $nama_pelapor, $lokasi, $deskripsi, $jenis_bantuan, $foto_name, $tanggal);

if ($stmt->execute()) {
    $laporan_id = $stmt->insert_id;
    $stmt->close();
    $conn->close();

    jsonResponse(true, 'Laporan berhasil dikirim', [
        'id' => $laporan_id,
        'namaPelapor' => $nama_pelapor,
        'lokasi' => $lokasi,
        'deskripsi' => $deskripsi,
        'jenisBantuan' => $jenis_bantuan,
        'foto' => $foto_name,
        'status' => 'Baru',
        'tanggal' => $tanggal
    ]);
} else {
    $stmt->close();
    $conn->close();
    jsonResponse(false, 'Gagal menyimpan laporan', null);
}
?>