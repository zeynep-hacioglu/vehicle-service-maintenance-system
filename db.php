<?php
// config/db.php — Okul Sunucusu Veritabanı Bağlantısı

define('DB_HOST', 'localhost'); 
define('DB_NAME', 'okulun verdiği veritabanı adı'); 
define('DB_USER', 'okulun verdiği kullanıcı adı');       
define('DB_PASS', 'okulun verdiği şifre');         
define('DB_CHARSET', 'utf8mb4');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $db = new PDO($dsn, DB_USER, DB_PASS, $options);
    $db->setAttribute(PDO::ATTR_PERSISTENT, true);
} catch (PDOException $e) {
    // Güvenlik için canlı sunucuda detaylı hata mesajı gizlenir
    die("Veritabanı bağlantı hatası oluştu."); 
}