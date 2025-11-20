<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Internal server error.'];
$conn = null; // Inisialisasi koneksi

try {
    $conn = getConnection();

    // 1. Total Laporan
    // Gunakan IFNULL untuk memastikan nilai default 0
    $total_result = $conn->query("SELECT IFNULL(COUNT(*), 0) as total FROM laporan");
    $total = $total_result->fetch_assoc()['total'];

    // 2. Bantuan Tersalur (status = 'Selesai')
    $tersalur_result = $conn->query("SELECT IFNULL(COUNT(*), 0) as total FROM laporan WHERE status = 'Selesai'");
    $tersalur = $tersalur_result->fetch_assoc()['total'];

    // 3. Sedang Diproses (status = 'Diproses')
    $diproses_result = $conn->query("SELECT IFNULL(COUNT(*), 0) as total FROM laporan WHERE status = 'Diproses'");
    $diproses = $diproses_result->fetch_assoc()['total'];
    
    // Siapkan response sukses
    $response = [
        'success' => true,
        'total' => (int)$total,
        'selesai' => (int)$tersalur, // Mengubah kunci 'tersalur' menjadi 'selesai' agar konsisten dengan JS/tampilan
        'diproses' => (int)$diproses
    ];

} catch (Exception $e) {
    // Tangani error, kirim pesan kegagalan
    $response = ['success' => false, 'message' => 'Gagal mengambil data statistik: ' . $e->getMessage()];
    
} finally {
    // Pastikan koneksi ditutup, terlepas dari sukses atau gagal
    if ($conn) {
        $conn->close();
    }
}

// Kirim response JSON
echo json_encode($response, JSON_UNESCAPED_UNICODE);
?>