<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];

    $result = $conn->query("SELECT foto FROM laporan WHERE id = $id");
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if ($row['foto'] && file_exists(UPLOAD_DIR . $row['foto'])) {
            unlink(UPLOAD_DIR . $row['foto']);
        }
    }

    $conn->query("DELETE FROM laporan WHERE id = $id");
    $success = "Laporan berhasil dihapus";
}

// Get all laporan
$search = $_GET['search'] ?? '';
$filter_status = $_GET['filter_status'] ?? '';
$filter_jenis = $_GET['filter_jenis'] ?? '';

$sql = "SELECT * FROM laporan WHERE 1=1";

if (!empty($search)) {
    $search = $conn->real_escape_string($search);
    $sql .= " AND (nama_pelapor LIKE '%$search%' OR lokasi LIKE '%$search%' OR deskripsi LIKE '%$search%')";
}

if (!empty($filter_status)) {
    $filter_status = $conn->real_escape_string($filter_status);
    $sql .= " AND status = '$filter_status'";
}

if (!empty($filter_jenis)) {
    $filter_jenis = $conn->real_escape_string($filter_jenis);
    $sql .= " AND jenis_bantuan = '$filter_jenis'";
}

// ✅ URUTKAN DARI YANG TERLAMA DULU (baru di bawah)
$sql .= " ORDER BY created_at ASC";

$laporan = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - SiBantu Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="admin-container">
      <?php include 'sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1><i class="fas fa-file-alt"></i> Manajemen Laporan</h1>
                <div class="user-info">
                    <span><?php echo $_SESSION['nama']; ?></span>
                </div>
            </header>

            <?php if (isset($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Laporan</h3>
                    <button class="btn btn-primary" onclick="window.location.href='../index.html#lapor'">
                        <i class="fas fa-plus"></i> Tambah Laporan
                    </button>
                </div>

                <div style="padding: 1.5rem 2rem; border-bottom: 1px solid var(--border-color);">
                    <form method="GET" style="display: grid; grid-template-columns: 2fr 1fr 1fr auto; gap: 1rem;">
                        <div class="form-group" style="margin: 0;">
                            <input type="text" name="search" placeholder="Cari nama, lokasi, atau deskripsi..."
                                value="<?php echo htmlspecialchars($search); ?>" style="width: 100%;">
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <select name="filter_status">
                                <option value="">Semua Status</option>
                                <option value="Baru" <?php echo $filter_status === 'Baru' ? 'selected' : ''; ?>>Baru</option>
                                <option value="Diproses" <?php echo $filter_status === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                <option value="Selesai" <?php echo $filter_status === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                            </select>
                        </div>
                        <div class="form-group" style="margin: 0;">
                            <select name="filter_jenis">
                                <option value="">Semua Jenis</option>
                                <option value="Bencana Alam" <?php echo $filter_jenis === 'Bencana Alam' ? 'selected' : ''; ?>>Bencana Alam</option>
                                <option value="Kesehatan" <?php echo $filter_jenis === 'Kesehatan' ? 'selected' : ''; ?>>Kesehatan</option>
                                <option value="Pendidikan" <?php echo $filter_jenis === 'Pendidikan' ? 'selected' : ''; ?>>Pendidikan</option>
                                <option value="Pangan" <?php echo $filter_jenis === 'Pangan' ? 'selected' : ''; ?>>Pangan</option>
                                <option value="Ekonomi" <?php echo $filter_jenis === 'Ekonomi' ? 'selected' : ''; ?>>Ekonomi</option>
                                <option value="Lainnya" <?php echo $filter_jenis === 'Lainnya' ? 'selected' : ''; ?>>Lainnya</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </form>
                </div>

                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>No</th> <!-- ✅ GANTI DARI "ID" -->
                                <th>Nama Pelapor</th>
                                <th>Lokasi</th>
                                <th>Jenis</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($laporan->num_rows > 0): ?>
                                <?php
                                // ✅ Tambahkan nomor urut
                                $no = 1;
                                ?>
                                <?php while ($row = $laporan->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td> <!-- ✅ NOMOR URUT -->
                                        <td><?php echo htmlspecialchars($row['nama_pelapor']); ?></td>
                                        <td><?php echo htmlspecialchars($row['lokasi']); ?></td>
                                        <td><span class="badge badge-info"><?php echo htmlspecialchars($row['jenis_bantuan']); ?></span></td>
                                        <td>
                                            <select class="status-select" data-id="<?php echo $row['id']; ?>"
                                                style="padding: 0.4rem; border-radius: 8px; border: 2px solid #e5e7eb;">
                                                <option value="Baru" <?php echo $row['status'] === 'Baru' ? 'selected' : ''; ?>>Baru</option>
                                                <option value="Diproses" <?php echo $row['status'] === 'Diproses' ? 'selected' : ''; ?>>Diproses</option>
                                                <option value="Selesai" <?php echo $row['status'] === 'Selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            </select>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal'])); ?></td>
                                        <td>
                                            <a href="laporan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" title="Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="laporan_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Yakin ingin menghapus laporan ini?')" title="Hapus">
                                                <i class="fas fa-trash"></i>
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

    <script>
        document.querySelectorAll('.status-select').forEach(select => {
            select.addEventListener('change', function() {
                const laporanId = this.getAttribute('data-id');
                const newStatus = this.value;

                // Tampilkan loading
                this.disabled = true;

                // Kirim update via AJAX
                fetch('../api/update_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            id: laporanId,
                            status: newStatus
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Tampilkan notifikasi sukses
                            showNotification('Status berhasil diupdate!', 'success');
                            this.disabled = false;
                        } else {
                            alert('Gagal mengupdate status: ' . data.message);
                            this.disabled = false;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat mengupdate status');
                        this.disabled = false;
                    });
            });
        });

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.className = `alert alert-${type}`;
            notification.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            notification.style.position = 'fixed';
            notification.style.top = '20px';
            notification.style.right = '20px';
            notification.style.zIndex = '9999';
            notification.style.minWidth = '300px';

            document.body.appendChild(notification);

            setTimeout(() => {
                notification.remove();
            }, 3000);
        }
    </script>
</body>

</html>
<?php $conn->close(); ?>