<?php
require_once 'config.php';

$total_laporan = 0;
$bantuan_tersalur = 0;
$sedang_diproses = 0;

try {
    $conn = getConnection();
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan");
    $total_laporan = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Selesai'");
    $bantuan_tersalur = $result->fetch_assoc()['total'];
    $result = $conn->query("SELECT COUNT(*) as total FROM laporan WHERE status = 'Diproses'");
    $sedang_diproses = $result->fetch_assoc()['total'];

    $conn->close();
} catch (Exception $e) {
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
    <script>
        window.addEventListener('load', () => {
            window.scrollTo(0, 0);
        });
    </script>
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
    <section class="laporan-section" id="lapor">
        <div class="container">
            <div class="section-header reveal">
                <h2>Laporkan Kondisi Darurat</h2>
                <p>Isi formulir di bawah ini untuk melaporkan kondisi yang membutuhkan bantuan</p>
            </div>
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
    <section class="daftar-section" id="daftar">
        <div class="container">
            <div class="section-header reveal">
                <h2>Daftar Laporan Bantuan</h2>
                <p>Pantau status laporan bantuan yang telah diajukan</p>
            </div>
            <div class="filter-container reveal">
                <div class="filter-group">
                    <label for="filterJenis">Filter Jenis:</label>
                    <select id="filterJenis">
                        <option value="Semua">Semua</option>
                        <option value="Bencana Alam">Bencana Alam</option>
                        <option value="Kesehatan">Kesehatan</option>
                        <option value="Pendidikan">Pendidikan</option>
                        <option value="Pangan">Pangan</option>
                        <option value="Ekonomi">Ekonomi</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="filterStatus">Filter Status:</label>
                    <select id="filterStatus">
                        <option value="Semua">Semua</option>
                        <option value="Baru">Baru</option>
                        <option value="Diproses">Diproses</option>
                        <option value="Selesai">Selesai</option>
                    </select>
                </div>
            </div>
            <div class="laporan-grid reveal" id="laporanGrid">
            </div>
        </div>
    </section>

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

<div id="modalPopup" class="modal" style="display:none;">
    <div class="modal-content-success">
        <span class="close-modal">×</span>
        
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h3>Laporan Berhasil Dikirim!</h3>
        <p>Terima kasih atas laporan Anda.<br>Tim kami akan segera menindaklanjuti.</p>
    </div>
</div>


<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 9999;
        left: 0; top: 0;
        width: 100%; height: 100%;
        background-color: rgba(0,0,0,0.8);
        backdrop-filter: blur(10px);
        align-items: center;
        justify-content: center;
    }

    .modal-content-success {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 3rem 2rem;
        border-radius: 24px;
        text-align: center;
        max-width: 420px;
        width: 90%;
        box-shadow: 0 20px 60px rgba(0,0,0,0.6);
        border: 2px solid rgba(34, 197, 94, 0.5);
        position: relative;
        animation: popIn 0.6s ease-out;
    }

    @keyframes popIn {
        0% { transform: scale(0.5); opacity: 0; }
        70% { transform: scale(1.05); }
        100% { transform: scale(1); opacity: 1; }
    }

    .success-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 1.5rem;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .success-icon i {
        font-size: 5rem;
        color: #10b981;
        background: radial-gradient(circle, #10b981 30%, transparent 70%);
        width: 100%;
        height: 100%;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 60px rgba(16, 185, 129, 0.8);
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0%, 100% { box-shadow: 0 0 60px rgba(16, 185, 129, 0.8); }
        50% { box-shadow: 0 0 100px rgba(16, 185, 129, 1); }
    }

    .success-icon::before {
        content: '';
        position: absolute;
        width: 160px;
        height: 160px;
        background: radial-gradient(circle, rgba(16, 185, 129, 0.3) 0%, transparent 70%);
        border-radius: 50%;
        animation: glow 3s infinite;
    }

    @keyframes glow {
        0%, 100% { transform: scale(1); opacity: 0.6; }
        50% { transform: scale(1.3); opacity: 1; }
    }

    .modal-content-success h3 {
        color: #10b981;
        font-size: 1.8rem;
        margin: 0 0 1rem;
        font-weight: 700;
    }

    .modal-content-success p {
        color: #cbd5e1;
        line-height: 1.7;
        margin: 0;
    }

    .close-modal {
        position: absolute;
        top: 1rem;
        right: 1rem;
        font-size: 2rem;
        color: #64748b;
        cursor: pointer;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }

    .close-modal:hover {
        background: #ef4444;
        color: white;
        transform: rotate(90deg);
    }
</style>    
   <script type="text/javascript" src="./js/laporan.js"></script>
</body>
</html>
</body>

</html>