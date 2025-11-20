<!-- sidebar.php -->
<aside class="sidebar">
    <div class="sidebar-header">
        <i class="fas fa-hands-helping"></i>
        <span>SiBantu Admin</span>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php" <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-chart-line"></i> Dashboard
        </a>
        <a href="laporan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'laporan.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-file-alt"></i> Laporan
        </a>
        <a href="bantuan.php" <?php echo basename($_SERVER['PHP_SELF']) == 'bantuan.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-boxes"></i> Data Bantuan
        </a>
        <a href="penyaluran.php" <?php echo basename($_SERVER['PHP_SELF']) == 'penyaluran.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-truck"></i> Penyaluran
        </a>
        <a href="users.php" <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'class="active"' : ''; ?>>
            <i class="fas fa-users"></i> Users
        </a>
        <a href="../index.php" target="_blank">
            <i class="fas fa-globe"></i> Lihat Website
        </a>
        <a href="logout.php" class="logout-link">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>