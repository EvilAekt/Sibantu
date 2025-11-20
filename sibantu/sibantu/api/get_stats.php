<?php
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
    $conn = getConnection();

    // Total Laporan
    $total = $conn->query("SELECT COUNT(*) as total FROM laporan")->fetch_assoc()['total'];

    // Bantuan Tersalur (status = 'Selesai')
    $tersalur = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Selesai'")->fetch_assoc()['total'];

    // Sedang Diproses (status = 'Diproses')
    $diproses = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Diproses'")->fetch_assoc()['total'];

    echo json_encode([
        'success' => true,
        'total' => (int)$total,
        'tersalur' => (int)$tersalur,
        'diproses' => (int)$diproses
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>