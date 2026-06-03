<!DOCTYPE html>
<html lang=tr>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Araç Servis Bakım Sistemi') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: whitesmoke;}
        .navbar-brand {font-weight: bold;}
        .card {border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,.08);}
    </style>
    <?php if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['kullanici_id'])): ?>
        <script>
            console.log("🔒 [GÜVENLİK] PHP Session (Oturum) başarıyla doğrulandı.");
            console.log("👤 Aktif Kullanıcı: <?= htmlspecialchars($_SESSION['ad_soyad'] ?? 'Bilinmeyen') ?>");
            console.log("🛡️ Kullanıcı Rolü: <?= htmlspecialchars($_SESSION['rol'] ?? 'Müşteri') ?>");
        </script>
    <?php endif; ?>
</head>
<body>
    <!--Menu olusturuyoruz -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">🔧 Araç Servis - Bakım</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <?php if(isset($_SESSION['kullanici_id'])): ?> <!--Kullanici girisi varsa gösterilecek sayfalar -->
                        <?php if($_SESSION['rol']=='Müşteri'): ?> <!--Giris yapan kisi musteri ise gosterilecekler -->
                            <li class="nav-item"><a class="nav-link" href="musteri_panel.php">Panelim</a></li>
                            <li class="nav-item"><a class="nav-link" href="arac_ekle.php">Araç Ekle</a></li>
                            <li class="nav-item"><a class="nav-link" href="randevu_al.php">Randevu Al</a></li>
                        <?php else: ?> <!--Personel ise gosterilecekler -->
                            <li class="nav-item"><a class="nav-link" href="personel_panel.php">Personel Panel</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Çıkış</a></li>
                    <?php else: ?> <!--Kullanicinin girisi yoksa gosterilecek menu -->
                            <li class="nav-item"><a class="nav-link" href="login.php">Giriş Yap</a></li>
                            <li class="nav-item"><a class="nav-link" href="kayit.php">Kayıt Ol</a></li>
                    <?php endif; ?>
                </ul>
        </div>
    </nav>
    <div class="container">
