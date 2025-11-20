<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, 'Metode tidak diizinkan');
}

try {
    $conn = getConnection();

    $nama_pelapor = sanitize($_POST['namaPelapor'] ?? '');
    $lokasi = sanitize($_POST['lokasi'] ?? '');
    $jenis_bantuan = sanitize($_POST['jenisBantuan'] ?? '');
    $deskripsi = sanitize($_POST['deskripsi'] ?? '');

    if (empty($nama_pelapor) || empty($lokasi) || empty($jenis_bantuan) || empty($deskripsi)) {
        jsonResponse(false, 'Semua field wajib diisi');
    }

    $foto_name = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = $_FILES['foto']['type'];
        $file_ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_type, $allowed_types) || !in_array($file_ext, ['jpg', 'jpeg', 'png'])) {
            jsonResponse(false, 'Format foto hanya JPG/PNG');
        }

        if ($_FILES['foto']['size'] > MAX_FILE_SIZE) {
            jsonResponse(false, 'Ukuran foto maksimal 5MB');
        }

        if (!is_dir(UPLOAD_DIR)) {
            mkdir(UPLOAD_DIR, 0777, true);
        }

        $foto_name = 'laporan_' . time() . '_' . uniqid() . '.' . $file_ext;
        $upload_path = UPLOAD_DIR . $foto_name;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $upload_path)) {
            jsonResponse(false, 'Gagal mengupload foto');
        }
    }

    $tanggal = date('Y-m-d');
    $status = 'Baru';

    $stmt = $conn->prepare("INSERT INTO laporan (nama_pelapor, lokasi, jenis_bantuan, deskripsi, status, tanggal, foto) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $nama_pelapor, $lokasi, $jenis_bantuan, $deskripsi, $status, $tanggal, $foto_name);

    if ($stmt->execute()) {
        jsonResponse(true, 'Laporan berhasil dikirim');
    } else {
        jsonResponse(false, 'Gagal menyimpan ke database: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    jsonResponse(false, 'Terjadi kesalahan sistem: ' . $e->getMessage());
}
?>