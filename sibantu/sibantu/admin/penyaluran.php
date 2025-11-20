<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();

// Gunakan 'created_at' sebagai pengganti 'tanggal'
$sql = "
    SELECT p.*, 
           l.nama_pelapor, 
           b.nama_bantuan 
    FROM penyaluran p
    LEFT JOIN laporan l ON p.laporan_id = l.id
    LEFT JOIN bantuan b ON p.bantuan_id = b.id
    ORDER BY p.created_at DESC
";
$penyaluran = $conn->query($sql);

// Opsional: ambil data untuk form (jika nanti aktifkan form)
// $laporan_list = $conn->query("SELECT id, nama_pelapor, lokasi FROM laporan WHERE status != 'Selesai'");
// $bantuan_list = $conn->query("SELECT id, nama_bantuan FROM bantuan");

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Penyaluran - SiBantu Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-truck"></i> Penyaluran Bantuan</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['nama']; ?></strong></span>
                    <span class="badge-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </header>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Riwayat Penyaluran</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Pelapor</th>
                                <th>Bantuan</th>
                                <th>Jumlah</th>
                                <th>Lokasi</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($p = $penyaluran->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <?php
                                        if (isset($p['tanggal']) && $p['tanggal']) {
                                            echo date('d/m/Y', strtotime($p['tanggal']));
                                        } elseif (isset($p['created_at'])) {
                                            echo date('d/m/Y', strtotime($p['created_at']));
                                        } else {
                                            echo '-';
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['nama_pelapor'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($p['nama_bantuan'] ?? '-'); ?></td>
                                    <td><?php echo $p['jumlah'] ?? '-'; ?></td>
                                    <td><?php echo htmlspecialchars($p['lokasi'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($p['keterangan'] ?? '-'); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

</html>