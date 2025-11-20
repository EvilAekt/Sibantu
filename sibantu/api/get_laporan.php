<?php
// api/get_laporan.php - Simple, aman, dan PASTI cocok dengan JavaScript kamu

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once '../config.php'; // pastikan ini berisi fungsi getConnection() yang return mysqli

$jenis  = $_GET['jenis'] ?? '';
$status = $_GET['status'] ?? '';

try {
    $conn = getConnection();
    if (!$conn) {
        throw new Exception("Koneksi database gagal");
    }

    $sql = "SELECT 
                id,
                nama_pelapor AS namaPelapor,
                lokasi,
                deskripsi,
                jenis_bantuan AS jenisBantuan,
                foto,
                status,
                tanggal,
                created_at
            FROM laporan
            WHERE 1=1";

    $params = [];
    if ($jenis && $jenis !== 'Semua') {
        $sql .= " AND jenis_bantuan = ?";
        $params[] = $jenis;
    }
    if ($status && $status !== 'Semua') {
        $sql .= " AND status = ?";
        $params[] = $status;
    }

    $sql .= " ORDER BY created_at DESC LIMIT 100";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    if (!empty($params)) {
        $stmt->bind_param(str_repeat('s', count($params)), ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        // Pastikan tanggal tidak null
        $row['tanggal'] = $row['tanggal'] ?: date('Y-m-d'); // fallback hari ini kalau null
        $data[] = [
            'id'           => $row['id'],
            'namaPelapor'  => htmlspecialchars($row['namaPelapor'] ?? 'Anonim'),
            'lokasi'       => htmlspecialchars($row['lokasi'] ?? '-'),
            'deskripsi'    => htmlspecialchars($row['deskripsi'] ?? ''),
            'jenisBantuan' => htmlspecialchars($row['jenisBantuan'] ?? 'Umum'),
            'foto'         => $row['foto'] ?? null,
            'status'       => $row['status'] ?? 'Baru',
            'tanggal'      => $row['tanggal']
        ];
    }

    echo json_encode([
        "success" => true,
        "data"    => $data
    ], JSON_UNESCAPED_UNICODE);

    $stmt->close();
    $conn->close();

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>