<?php
require_once 'config.php';

// Inisialisasi variabel
$total_laporan = 0;
$bantuan_tersalur = 0;
$sedang_diproses = 0;

try {
    $conn = getConnection();

    // Total Laporan
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan");
    $total_laporan = $result->fetch_assoc()['total'];

    // Bantuan Tersalur (status = 'Selesai')
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Selesai'");
    $bantuan_tersalur = $result->fetch_assoc()['total'];

    // Sedang Diproses (status = 'Diproses')
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Diproses'");
    $sedang_diproses = $result->fetch_assoc()['total'];

    $conn->close();
} catch (Exception $e) {
    // Jika error, biarkan nilai default (0)
    $total_laporan = 0;
    $bantuan_tersalur = 0;
    $sedang_diproses = 0;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SiBantu - Sistem Pelaporan & Penyaluran Bantuan</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>

<body>
    <!-- Auto scroll ke atas saat refresh -->
    <script>
        window.addEventListener('load', () => {
            window.scrollTo(0, 0);
        });
    </script>

    <!-- Navigation -->
    <nav class="navbar reveal">
        <div class="container">
            <div class="nav-brand">
                <i class="fas fa-hands-helping"></i>
                <span>SiBantu</span>
            </div>
            <ul class="nav-menu" id="navMenu">
                <li><a href="#beranda" class="active">Beranda</a></li>
                <li><a href="#lapor">Laporkan</a></li>
                <li><a href="#daftar">Daftar Bantuan</a></li>
                <li><a href="#tentang">Tentang Kami</a></li>
                <li><a href="login.php" class="btn-login">Login</a></li>
            </ul>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="beranda">
        <div class="container">
            <div class="hero-content reveal">
                <h1>Bersama Membantu <span>Sesama</span></h1>
                <p>Platform pelaporan dan penyaluran bantuan untuk masyarakat yang membutuhkan. Laporkan kondisi darurat dan dapatkan bantuan dengan cepat.</p>
                <div class="hero-buttons">
                    <a href="#lapor" class="btn btn-primary">Laporkan Sekarang</a>
                    <a href="#daftar" class="btn btn-secondary">Lihat Laporan</a>
                </div>
            </div>
            <div class="hero-stats reveal">
                <div class="stat-card">
                    <i class="fas fa-file-alt"></i>
                    <h3 id="totalLaporan"><?php echo $total_laporan; ?></h3>
                    <p>Total Laporan</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <h3 id="laporanSelesai"><?php echo $bantuan_tersalur; ?></h3>
                    <p>Bantuan Tersalur</p>
                </div>
                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <h3 id="laporanProses"><?php echo $sedang_diproses; ?></h3>
                    <p>Sedang Diproses</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Form Pelaporan -->
    <section class="laporan-section" id="lapor">
        <div class="container">
            <div class="section-header reveal">
                <h2>Laporkan Kondisi Darurat</h2>
                <p>Isi formulir di bawah ini untuk melaporkan kondisi yang membutuhkan bantuan</p>
            </div>
            <!-- ✅ Form dengan ID unik dan enctype -->
            <form id="formLaporan" class="laporan-form reveal" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label for="namaPelapor"><i class="fas fa-user"></i> Nama Pelapor</label>
                        <input type="text" id="namaPelapor" name="namaPelapor" required>
                    </div>
                    <div class="form-group">
                        <label for="lokasi"><i class="fas fa-map-marker-alt"></i> Lokasi</label>
                        <input type="text" id="lokasi" name="lokasi" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="jenisBantuan"><i class="fas fa-hand-holding-heart"></i> Jenis Bantuan</label>
                    <select id="jenisBantuan" name="jenisBantuan" required>
                        <option value="">-- Pilih Jenis Bantuan --</option>
                        <option value="Bencana Alam">Bencana Alam</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Pangan">Pangan</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="deskripsi"><i class="fas fa-align-left"></i> Deskripsi Kejadian</label>
                    <textarea id="deskripsi" name="deskripsi" rows="5" required></textarea>
                </div>
                <div class="form-group">
                    <label for="foto"><i class="fas fa-camera"></i> Upload Foto Bukti</label>
                    <input type="file" id="foto" name="foto" accept="image/*">
                    <small>Format: JPG, PNG, maksimal 5MB</small>
                </div>
                <button type="submit" class="btn btn-primary btn-submit">
                    <i class="fas fa-paper-plane"></i> Kirim Laporan
                </button>
            </form>
        </div>
    </section>

    <!-- Daftar Laporan -->
    <section class="daftar-section" id="daftar">
        <div class="container">
            <div class="section-header reveal">
                <h2>Daftar Laporan Bantuan</h2>
                <p>Pantau status laporan bantuan yang telah diajukan</p>
            </div>

            <!-- Filter -->
            <div class="filter-container reveal">
                <div class="filter-group">
                    <label for="filterJenis"><i class="fas fa-filter"></i> Filter Jenis:</label>
                    <select id="filterJenis">
                        <option value="">Semua</option>
                        <option value="Bencana Alam">Bencana Alam</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Pangan">Pangan</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterStatus"><i class="fas fa-tasks"></i> Filter Status:</label>
                    <select id="filterStatus">
                        <option value="">Semua</option>
                        <option value="Baru">Baru</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>

            <!-- Card Laporan -->
            <div class="laporan-grid reveal" id="laporanGrid">
                <!-- Akan diisi dengan JavaScript -->
            </div>
        </div>
    </section>

    <!-- Tentang Kami -->
    <section class="tentang-section" id="tentang">
        <div class="container">
            <div class="tentang-content reveal">
                <div class="tentang-text">
                    <h2>Tentang SiBantu</h2>
                    <p><strong>SiBantu</strong> adalah platform digital yang menghubungkan masyarakat yang membutuhkan bantuan dengan para relawan dan organisasi sosial yang siap membantu.</p>
                    <p>Kami berkomitmen untuk mempercepat proses penyaluran bantuan kepada mereka yang membutuhkan dengan sistem yang transparan dan efisien.</p>
                    <div class="tentang-features">
                        <div class="feature-item">
                            <i class="fas fa-bolt"></i>
                            <h4>Cepat & Responsif</h4>
                            <p>Laporan langsung ditanggapi</p>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-shield-alt"></i>
                            <h4>Aman & Terpercaya</h4>
                            <p>Data terlindungi dengan baik</p>
                        </div>
                        <div class="feature-item">
                            <i class="fas fa-users"></i>
                            <h4>Kolaboratif</h4>
                            <p>Bersama untuk sesama</p>
                        </div>
                    </div>
                </div>
                <div class="tentang-image reveal">
                    <img src="https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=600" alt="Helping Hands">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer reveal">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3><i class="fas fa-hands-helping"></i> SiBantu</h3>
                    <p>Platform pelaporan dan penyaluran bantuan untuk masyarakat Indonesia.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-section">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="#beranda">Beranda</a></li>
                        <li><a href="#lapor">Laporkan</a></li>
                        <li><a href="#daftar">Daftar Bantuan</a></li>
                        <li><a href="#tentang">Tentang Kami</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Kontak</h4>
                    <ul>
                        <li><i class="fas fa-phone"></i> +62 812-3456-7890</li>
                        <li><i class="fas fa-envelope"></i> info@sibantu.id</li>
                        <li><i class="fas fa-map-marker-alt"></i> Yogyakarta, Indonesia</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 SiBantu. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Modal Popup -->
    <div id="modalPopup" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div class="modal-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h3>Laporan Berhasil Dikirim!</h3>
            <p>Terima kasih atas laporan Anda. Tim kami akan segera menindaklanjuti.</p>
        </div>
    </div>

    <script type="text/javascript" src="./js/script.js"></script>
</body>

</html>