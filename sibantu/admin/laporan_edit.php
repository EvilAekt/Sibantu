<?php
require_once '../config.php';

if (!isAdmin()) {
    redirect('../index.php');
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die('ID tidak valid');

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM laporan WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$laporan = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$laporan) die('Laporan tidak ditemukan');

function getStatusClass($status) {
    $s = strtolower($status);
    if ($s === 'baru') return 'status-baru';
    if ($s === 'diproses') return 'status-diproses';
    if ($s === 'selesai') return 'status-selesai';
    return 'status-baru';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Laporan #<?= $laporan['id'] ?> - SiBantu Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --bg: #141d2b;
            --card: #1a2332;
            --border: #2d3748;
            --text: #e2e8f0;
            --muted: #94a3b8;
            --primary: #3b82f6;
            --primary-light: #60a5fa;
            --success: #22c55e;
            --warning: #f59e0b;
            --glow: rgba(59, 130, 246, 0.3);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 2rem 1rem;
            line-height: 1.6;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
        }

        .card {
            background: var(--card);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: 
                0 20px 50px rgba(0,0,0,0.6),
                0 0 40px var(--glow);
            border: 1px solid var(--border);
            position: relative;
            overflow: hidden;
            transition: all 0.4s ease;
        }

        .card:hover {
            transform: translateY(-10px);
            box-shadow: 
                0 30px 70px rgba(0,0,0,0.7),
                0 0 60px rgba(59, 130, 246, 0.5),
                0 0 100px rgba(59, 130, 246, 0.2);
        }

        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, transparent, var(--primary), transparent);
            opacity: 0;
            transition: opacity 0.4s;
        }

        .card:hover::before {
            opacity: 1;
        }

        h1 {
            text-align: center;
            font-size: 2.6rem;
            margin-bottom: 2.5rem;
            color: var(--primary-light);
            font-weight: 700;
            text-shadow: 0 4px 20px rgba(59, 130, 246, 0.4);
            transition: all 0.4s;
        }

        h1:hover {
            color: white;
            text-shadow: 0 0 30px rgba(59, 130, 246, 0.8);
            transform: scale(1.05);
        }

        .form-group {
            margin-bottom: 2rem;
        }

        label {
            display: block;
            margin-bottom: 0.7rem;
            color: var(--muted);
            font-weight: 600;
            font-size: 1.05rem;
            transition: color 0.3s;
        }

        .form-group:hover label {
            color: var(--primary-light);
        }

        input, textarea, select {
            width: 100%;
            padding: 1.2rem 1.5rem;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: rgba(45, 55, 72, 0.6);
            color: white;
            font-size: 1.05rem;
            transition: all 0.4s ease;
            backdrop-filter: blur(8px);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary);
            background: rgba(45, 55, 72, 0.9);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3);
        }

        /* Hover input glow biru elegan */
        input:hover, textarea:hover, select:hover {
            border-color: var(--primary-light);
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.2);
        }

        textarea {
            height: 160px;
            resize: vertical;
        }

        .foto-preview {
            max-width: 100%;
            max-height: 400px;
            border-radius: 18px;
            margin: 1.5rem 0;
            border: 3px solid var(--border);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
            transition: all 0.5s ease;
        }

        .foto-preview:hover {
            transform: scale(1.03);
            border-color: var(--primary);
            box-shadow: 
                0 25px 60px rgba(0,0,0,0.7),
                0 0 40px rgba(59, 130, 246, 0.4);
        }

        .status-badge {
            padding: 0.7rem 1.8rem;
            border-radius: 50px;
            font-size: 0.95rem;
            font-weight: bold;
            display: inline-block;
            margin-top: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.4s ease;
            box-shadow: 0 8px 25px rgba(0,0,0,0.4);
        }

        .status-badge:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 15px 40px rgba(0,0,0,0.6);
        }

        .status-baru    { background: #3b82f6; }
        .status-diproses { background: #f59e0b; }
        .status-selesai  { background: #22c55e; }

        .btn {
            padding: 1.1rem 2.8rem;
            border: none;
            border-radius: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            margin: 0 1rem;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%; left: 50%;
            width: 0; height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-back {
            background: #475569;
            color: white;
        }

        .btn-back:hover {
            background: #64748b;
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(100, 116, 139, 0.5);
        }

        .btn-success {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }

        .btn-success:hover {
            background: #2563eb;
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(59, 130, 246, 0.6);
        }

        .btn-group {
            text-align: center;
            margin-top: 4rem;
        }

        small, a {
            color: var(--primary-light);
            transition: color 0.3s;
        }

        a:hover {
            color: white;
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .card { padding: 2rem; }
            h1 { font-size: 2.2rem; }
            .btn { 
                display: block; 
                width: 100%; 
                margin: 1rem 0; 
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <h1>Edit Laporan #<?= $laporan['id'] ?></h1>

            <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $laporan['id'] ?>">

                <div class="form-group">
                    <label>Nama Pelapor</label>
                    <input type="text" name="nama_pelapor" value="<?= htmlspecialchars($laporan['nama_pelapor']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Lokasi Kejadian</label>
                    <input type="text" name="lokasi" value="<?= htmlspecialchars($laporan['lokasi']) ?>" required>
                </div>

                <div class="form-group">
                    <label>Jenis Bantuan</label>
                    <select name="jenis_bantuan" required>
                        <option value="">-- Pilih Jenis Bantuan --</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Bencana Alam' ? 'selected' : '' ?>>Bencana Alam</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Kesehatan' ? 'selected' : '' ?>>Kesehatan</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Pendidikan' ? 'selected' : '' ?>>Pendidikan</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Ekonomi' ? 'selected' : '' ?>>Ekonomi</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Pangan' ? 'selected' : '' ?>>Pangan</option>
                        <option <?= $laporan['jenis_bantuan'] == 'Lainnya' ? 'selected' : '' ?>>Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Deskripsi Kejadian</label>
                    <textarea name="deskripsi" required><?= htmlspecialchars($laporan['deskripsi']) ?></textarea>
                </div>

                <div class="form-group">
                    <label>Status Laporan</label>
                    <select name="status" required>
                        <option value="Baru" <?= $laporan['status'] == 'Baru' ? 'selected' : '' ?>>Baru</option>
                        <option value="Diproses" <?= $laporan['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                        <option value="Selesai" <?= $laporan['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                    </select>
                    <span class="status-badge <?= getStatusClass($laporan['status']) ?>">
                        <?= $laporan['status'] ?>
                    </span>
                </div>

                <?php if ($laporan['foto']): ?>
                <div class="form-group">
                    <label>Foto Bukti Saat Ini</label>
                    <img src="../uploads/<?= htmlspecialchars($laporan['foto']) ?>" alt="Foto" class="foto-preview">
                    <p><small><a href="hapus_foto.php?id=<?= $laporan['id'] ?>" onclick="return confirm('Yakin hapus foto?')">Hapus Foto Permanen</a></small></p>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label>Ganti Foto (Opsional)</label>
                    <input type="file" name="foto" accept="image/*">
                    <small>JPG, PNG, WebP • Maksimal 5MB</small>
                </div>

                <div class="btn-group">
                    <button type="button" class="btn btn-back" onclick="history.back()">Kembali</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>