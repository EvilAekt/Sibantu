<?php
// api/update_status.php - API untuk update status laporan (AJAX Real-time)
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../config.php';

// Check if logged in (optional untuk demo)
// if (!isLoggedIn()) {
//     jsonResponse(false, 'Unauthorized', null);
// }

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['id']) || !isset($input['status'])) {
    jsonResponse(false, 'Data tidak lengkap', null);
}

$id = (int)$input['id'];
$status = $input['status'];

// Validasi status
$valid_status = ['Baru', 'Diproses', 'Selesai'];
if (!in_array($status, $valid_status)) {
    jsonResponse(false, 'Status tidak valid', null);
}

$conn = getConnection();

// Update status
$stmt = $conn->prepare("UPDATE laporan SET status = ?, updated_at = NOW() WHERE id = ?");
$stmt->bind_param("si", $status, $id);

if ($stmt->execute()) {
    // Get updated data
    $result = $conn->query("SELECT * FROM laporan WHERE id = $id");
    $laporan = $result->fetch_assoc();
    
    $stmt->close();
    $conn->close();
    
    jsonResponse(true, 'Status berhasil diupdate', [
        'id' => $laporan['id'],
        'status' => $laporan['status'],
        'updated_at' => $laporan['updated_at']
    ]);
} else {
    $stmt->close();
    $conn->close();
    jsonResponse(false, 'Gagal mengupdate status', null);
}
?>