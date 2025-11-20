<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();
$bantuan = $conn->query("SELECT * FROM bantuan ORDER BY created_at DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Bantuan - SiBantu Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <?php include 'sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-boxes"></i> Data Bantuan</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo $_SESSION['nama']; ?></strong></span>
                    <span class="badge-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </header>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Jenis Bantuan</h3>
                    <!-- <a href="bantuan_tambah.php" class="btn btn-sm btn-primary">+ Tambah</a> -->
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama Bantuan</th>
                                <th>Kategori</th>
                                <th>Stok</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($b = $bantuan->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $b['id']; ?></td>
                                    <td><?php echo htmlspecialchars($b['nama_bantuan']); ?></td>
                                    <td><?php echo htmlspecialchars($b['kategori']); ?></td>
                                    <td><?php echo $b['stok']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $b['status'] == 'aktif' ? 'success' : 'warning'; ?>">
                                            <?php echo $b['status'] == 'aktif' ? 'Aktif' : 'Nonaktif'; ?>
                                        </span>
                                    </td>
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