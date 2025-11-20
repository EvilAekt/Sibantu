<?php

declare(strict_types=1);

if (basename($_SERVER['SCRIPT_FILENAME']) === 'config.php') {
    http_response_code(403);
    exit('Akses langsung ke file ini tidak diizinkan.');
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_bantuan');
define('DB_CHARSET', 'utf8mb4');

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$dir = dirname($_SERVER['SCRIPT_NAME']);
$dir = $dir === '/' ? '' : rtrim($dir, '/\\');

define('BASE_URL', $protocol . '://' . $host . $dir . '/');
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); 
define('ALLOWED_TYPES', [
    'image/jpeg',
    'image/jpg',
    'image/png',
    'image/gif',
    'image/webp'
]);

date_default_timezone_set('Asia/Jakarta');
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => $protocol === 'https',  
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

function getConnection(): mysqli {
    static $conn = null;
    
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        
        if ($conn->connect_error) {
            error_log("DB Connection Failed: " . $conn->connect_error);
            http_response_code(503);
            exit("Sistem sedang maintenance. Silakan coba lagi nanti.");
        }
        
        $conn->set_charset(DB_CHARSET);
        $conn->options(MYSQLI_OPT_INT_AND_FLOAT_NATIVE, true);
    }
    
    return $conn;
}

function sanitize(string $data): string {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function escape(string $data): string {
    return getConnection()->real_escape_string(trim($data));
}

function isLoggedIn(): bool {
    return !empty($_SESSION['user_id'] ?? null);
}

function isAdmin(): bool {
    return isLoggedIn() && ($_SESSION['role'] ?? '') === 'admin';
}

function redirect(string $url): never {
    $location = BASE_URL . ltrim($url, '/');
    
    if (!headers_sent()) {
        header("Location: $location", true, 302);
        exit;
    }
    
    echo "<script>window.location.href = '" . addslashes($location) . "';</script>";
    exit;
}

function jsonResponse(bool $success, string $message = '', array $data = []): never {
    header('Content-Type: application/json; charset=utf-8');
    
    $response = [
        'success'   => $success,
        'message'   => $message,
        'timestamp' => date('c')
    ];
    
    if (!empty($data)) {
        if (isset($data['stats'])) {
            $response['stats'] = $data['stats'];
            unset($data['stats']);
        }
        if (!empty($data)) {
            $response['data'] = $data;
        }
    }
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    exit;
}

function uploadFoto(array $file): array {
 
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['success' => true, 'filename' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'Gagal mengunggah file.'];
    }

    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'Ukuran file maksimal 5MB!'];
    }

    $mime = mime_content_type($file['tmp_name']);
    if ($mime === false || !in_array($mime, ALLOWED_TYPES, true)) {
        return ['success' => false, 'message' => 'Format file tidak didukung. Gunakan JPG, PNG, GIF, atau WebP.'];
    }

    if (!is_dir(UPLOAD_DIR)) {
        if (!mkdir(UPLOAD_DIR, 0755, true) && !is_dir(UPLOAD_DIR)) {
            return ['success' => false, 'message' => 'Gagal membuat folder upload.'];
        }
    }

    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $ext = empty($ext) ? 'jpg' : strtolower($ext);
    $filename = 'laporan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $filepath = UPLOAD_DIR . $filename;

    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        @chmod($filepath, 0644);
        return ['success' => true, 'filename' => $filename];
    }

    return ['success' => false, 'message' => 'Gagal menyimpan file ke server.'];
}

function deleteFoto(string $filename): bool {
    if (empty($filename)) return true;
    $path = UPLOAD_DIR . $filename;
    return file_exists($path) ? @unlink($path) : true;
}


define('ACCESS_ALLOWED', true);