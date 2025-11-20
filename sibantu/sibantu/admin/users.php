<?php
require_once '../config.php';

if (!isLoggedIn()) {
    redirect('../login.php');
}

$conn = getConnection();
$users = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
$conn->close();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users - SiBantu Admin</title>
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
                <h1><i class="fas fa-users"></i> Manajemen Pengguna</h1>
                <div class="user-info">
                    <span>Selamat datang, <strong><?php echo htmlspecialchars($_SESSION['nama']); ?></strong></span>
                    <span class="badge-role"><?php echo ucfirst($_SESSION['role']); ?></span>
                </div>
            </header>

            <div class="content-card">
                <div class="card-header">
                    <h3><i class="fas fa-list"></i> Daftar Pengguna</h3>
                </div>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($user = $users->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo (int)$user['id']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nama'] ?? '-'); ?></td>
                                    <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                                    <td>
                                        <?php
                                        $role = $user['role'] ?? 'user';
                                        $roleClass = ($role === 'admin') ? 'danger' : 'info';
                                        ?>
                                        <span class="badge badge-<?php echo $roleClass; ?>">
                                            <?php echo ucfirst($role); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php
                                        $createdAt = $user['created_at'] ?? null;
                                        echo $createdAt ? date('d/m/Y', strtotime($createdAt)) : '-';
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $is_active = $user['is_active'] ?? 1; 
                                        $statusClass = $is_active ? 'success' : 'warning';
                                        $statusText = $is_active ? 'Aktif' : 'Nonaktif';
                                        ?>
                                        <span class="badge badge-<?php echo $statusClass; ?>">
                                            <?php echo $statusText; ?>
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