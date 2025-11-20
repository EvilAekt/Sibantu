<?php
require_once '../config.php';

if (!file_exists('../config.php')) {
    die('Error: File config.php tidak ditemukan.');
}

require_once '../config.php';

if (!function_exists('isLoggedIn')) {
    die('Error: Fungsi isLoggedIn() tidak didefinisikan.');
}

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();

$stats = [];

$result = $conn->query("SELECT COUNT(*) as total FROM laporan");
$stats['total_laporan'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Baru'");
$stats['laporan_baru'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Diproses'");
$stats['laporan_proses'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Selesai'");
$stats['laporan_selesai'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM bantuan");
$stats['total_bantuan'] = $result->fetch_assoc()['total'];

$result = $conn->query("SELECT COUNT(*) as total FROM penyaluran");
$stats['total_penyaluran'] = $result->fetch_assoc()['total'];

// ✅ URUTKAN DARI YANG TERLAMA DULU (baru di bawah) & BATAS 5
$recent_laporan = $conn->query("SELECT * FROM laporan ORDER BY created_at ASC LIMIT 5");

$jenis_stats = $conn->query("SELECT jenis_bantuan, COUNT(*) as total FROM laporan GROUP BY jenis_bantuan");

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SiBantu Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="admin-container">
        <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-chart-line"></i> Dashboard</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['nama']; ?></strong></span>
                    <span class="badge-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_laporan']; ?></h3>
                        <p>Total Laporan</p>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['laporan_baru']; ?></h3>
                        <p>Laporan Baru</p>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">
                        <i class="fas fa-spinner"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['laporan_proses']; ?></h3>
                        <p>Sedang Diproses</p>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['laporan_selesai']; ?></h3>
                        <p>Selesai</p>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-icon">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_bantuan']; ?></h3>
                        <p>Jenis Bantuan</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_penyaluran']; ?></h3>
                        <p>Total Penyaluran</p>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <h3><i class="fas fa-chart-pie"></i> Laporan Berdasarkan Jenis</h3>
                    <canvas id="jenisChart"></canvas>
                </div>

                <div class="chart-card">
                    <h3><i class="fas fa-chart-bar"></i> Status Laporan</h3>
                    <canvas id="statusChart"></canvas>
                </div>
            </div>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Laporan Terbaru</h3>
                    <a href="laporan.php" class="btn btn-sm btn-primary">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th> <!-- ✅ GANTI DARI "ID" -->
                                <th>Nama Pelapor</th>
                                <th>Lokasi</th>
                                <th>Jenis Bantuan</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($recent_laporan->num_rows > 0): ?>
                                <?php
                                // ✅ Tambahkan nomor urut
                                $no = 1;
                                ?>
                                <?php while ($row = $recent_laporan->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td> <!-- ✅ NOMOR URUT -->
                                        <td><?php echo htmlspecialchars($row['nama_pelapor']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['jenis_bantuan']); ?></span></td>
                                        <td>
                                            <?php
                                            $status_class = strtolower($row['status']);
                                            if ($status_class === 'baru') $status_class = 'warning';
                                            elseif ($status_class === 'diproses') $status_class = 'info';
                                            elseif ($status_class === 'selesai') $status_class = 'success';
                                            ?>
                                            <span class="badge badge-<?php echo $status_class; ?>"><?php echo $row['status']; ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                        <td>
                                            <a href="laporan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 2rem;">
                                        <i class="fas fa-inbox" style="font-size: 3rem; color: #9ca3af;"></i>
                                        <p style="margin-top: 1rem; color: #6b7280;">Tidak ada data laporan</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const jenisData = <?php
                            $jenis_array = [];
                            $jenis_stats->data_seek(0);
                            while ($row = $jenis_stats->fetch_assoc()) {
                                $jenis_array[$row['jenis_bantuan']] = $row['total'];
                            }
                            echo json_encode($jenis_array);
                            ?>;

        const jenisCtx = document.getElementById('jenisChart').getContext('2d');
        new Chart(jenisCtx, {
            type: 'pie',
            data: {
                labels: Object.keys(jenisData),
                datasets: [{
                    data: Object.values(jenisData),
                    backgroundColor: [
                    '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Baru', 'Diproses', 'Selesai'],
                datasets: [{
                    label: 'Jumlah Laporan',
                    data: [<?php echo $stats['laporan_baru']; ?>, <?php echo $stats['laporan_proses']; ?>, <?php echo $stats['laporan_selesai']; ?>],
                    backgroundColor: ['#f59e0b', '#3b82f6', '#10b981']
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        setInterval(() => {
            location.reload();
        }, 30000);
    </script>
</body>

</html>