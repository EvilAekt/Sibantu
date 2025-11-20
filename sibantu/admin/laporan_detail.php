<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID tidak valid');
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$laporan = $result->fetch_assoc();
$stmt->close();

if (!$laporan) {
    die('Laporan tidak ditemukan');
}

$status_color = '#64748b'; // default
if (strtolower($laporan['status']) === 'baru') {
    $status_color = '#3b82f6';
} elseif (strtolower($laporan['status']) === 'diproses') {
    $status_color = '#f59e0b';
} elseif (strtolower($laporan['status']) === 'selesai') {
    $status_color = '#22c55e';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan #<?= $laporan['id'] ?> - SiBantu Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #0f172a;
            --card: #1e293b;
            --border: #334155;
            --text: #f1f5f9;
            --muted: #94a3b8;
            --primary: #3b82f6;
            --success: #22c55e;
            --warning: #f59e0b;
            --glow: rgba(59, 130, 246, 0.4);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', sans-serif;
            line-height: 1.7;
            padding: 2rem 1rem;
        }

        .container { max-width: 1000px; margin: 0 auto; }
        .card {
            background: var(--card);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 60px rgba(0,0,0,0.6);
            border: 1px solid var(--border);
            animation: cardGlow 4s infinite alternate;
        }

        @keyframes cardGlow {
            0% { box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 30px var(--glow); }
            100% { box-shadow: 0 25px 60px rgba(0,0,0,0.6), 0 0 50px var(--glow); }
        }

        .header {
            background: linear-gradient(135deg, #1e3a8a, #3b82f6);
            padding: 2.5rem;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%; left: -50%;
            width: 200%; height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shine 8s infinite linear;
        }

        @keyframes shine {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(50%, 50%) rotate(360deg); }
        }

        .header h1 { font-size: 2.8rem; margin-bottom: 0.5rem; text-shadow: 0 4px 15px rgba(0,0,0,0.5); }
        .header .badge {
            display: inline-block;
            padding: 0.8rem 2rem;
            border-radius: 50px;
            font-size: 1.1rem;
            font-weight: bold;
            background: <?= $status_color ?>;
            color: white;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.4);
        }

        .content { padding: 3rem; }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .info-item {
            background: rgba(255,255,255,0.05);
            padding: 1.8rem;
            border-radius: 16px;
            border: 1px solid var(--border);
            transition: all 0.3s;
        }

        .info-item:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 30px rgba(59, 130, 246, 0.2);
        }

        .info-item i {
            font-size: 1.8rem;
            color: var(--primary);
            margin-bottom: 1rem;
            display: block;
        }

        .info-item h3 { color: var(--text); margin-bottom: 0.8rem; font-size: 1.2rem; }
        .info-item p { color: var(--muted); font-size: 1.1rem; }

        .foto-section { text-align: center; margin: 3rem 0; }
        .foto-preview {
            max-width: 100%;
            max-height: 500px;
            border-radius: 20px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.7);
            border: 3px solid var(--border);
        }

        .no-foto { font-size: 4rem; color: var(--muted); margin: 2rem 0; }

        .btn-group { text-align: center; margin-top: 3rem; }
        .btn {
            padding: 1rem 2.5rem;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            margin: 0 0.8rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-back { background: #475569; color: white; }
        .btn-back:hover { background: #64748b; transform: translateY(-5px); }

        .btn-edit { background: var(--primary); color: white; }
        .btn-edit:hover { background: #2563eb; transform: translateY(-5px); box-shadow: 0 15px 30px rgba(59, 130, 246, 0.4); }

        .btn-delete { background: #ef4444; color: white; }
        .btn-delete:hover { background: #dc2626; transform: translateY(-5px); box-shadow: 0 15px 30px rgba(239, 68, 68, 0.4); }

        @media (max-width: 768px) {
            .header h1 { font-size: 2rem; }
            .header .badge { font-size: 1rem; padding: 0.6rem 1.5rem; }
            .content { padding: 2rem 1.5rem; }
            .btn { display: block; width: 100%; margin: 0.8rem 0; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <div class="header">
                <h1>Laporan Detail</h1>
                <div class="badge">
                    <?= htmlspecialchars($laporan['status']) ?>
                </div>
            </div>

            <div class="content">
                <div class="info-grid">
                    <div class="info-item">
                        <i class="fas fa-user"></i>
                        <h3>Nama Pelapor</h3>
                        <p><?= htmlspecialchars($laporan['nama_pelapor']) ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <h3>Lokasi</h3>
                        <p><?= htmlspecialchars($laporan['lokasi']) ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-tag"></i>
                        <h3>Jenis Bantuan</h3>
                        <p><?= htmlspecialchars($laporan['jenis_bantuan']) ?></p>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <h3>Tanggal Laporan</h3>
                        <p><?= date('d F Y, H:i', strtotime($laporan['tanggal'])) ?></p>
                    </div>
                </div>

                <div class="info-item" style="margin-top: 2rem; padding: 2rem; background: rgba(255,255,255,0.03);">
                    <i class="fas fa-align-left"></i>
                    <h3>Deskripsi Kejadian</h3>
                    <p style="margin-top: 1rem; font-size: 1.1rem; line-height: 1.8;">
                        <?= nl2br(htmlspecialchars($laporan['deskripsi'])) ?>
                    </p>
                </div>

                <div class="foto-section">
                    <?php if ($laporan['foto']): ?>
                        <img src="../uploads/<?= htmlspecialchars($laporan['foto']) ?>" alt="Foto Bukti" class="foto-preview">
                    <?php else: ?>
                        <div class="no-foto">
                            <i class="fas fa-image"></i>
                            <p>Tidak ada foto bukti</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="btn-group">
                    <a href="laporan.php" class="btn btn-back">Kembali ke Daftar</a>
                    <a href="laporan_edit.php?id=<?= $laporan['id'] ?>" class="btn btn-edit">Edit Laporan</a>
                    <a href="hapus_laporan.php?id=<?= $laporan['id'] ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin menghapus laporan ini?')">Hapus Laporan</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>