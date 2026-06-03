<?php
session_start(); // Oturum kontrolünü başlatıyoruz

// Veritabanı ve ortak tasarım dosyalarını ekliyoruz
require_once 'db.php'; 
include 'includes/header.php'; 

// GÜVENLİK KONTROLÜ: Giriş yapılmadıysa detayları görmeyi engelle
if (!isset($_SESSION['kullanici_id'])) {
    header("Location: login.php");
    exit();
}

//  Hangi bakımın faturasına bakılacağını URL'deki 'bakim_id' parametresinden alıyoruz
if (!isset($_GET['bakim_id'])) {
    header("Location: arac_ara.php");
    exit();
}

$gelen_bakim_id = (int)$_GET['bakim_id'];
$bakim_detayi = null;

try {
    $fatura_sorgusu = $db->prepare("
        SELECT b.id as fatura_no, b.yapilan_islem, b.degisen_parca, b.toplam_ucret, b.islem_tarih, b.notlar as servis_notu,
               a.plaka, a.marka, a.model, a.yil,
               m.ad_soyad as musteri_ad, m.telefon as musteri_tel,
               p.ad_soyad as personel_ad
        FROM BAKIM_KAYIT b
        JOIN ARAC a ON b.arac_id = a.id
        JOIN KULLANICI m ON a.musteri_id = m.id
        JOIN KULLANICI p ON b.personel_id = p.id
        WHERE b.id = ?
    ");
    
    $fatura_sorgusu->execute([$gelen_bakim_id]);
    $bakim_detayi = $fatura_sorgusu->fetch();

    // Eğer veritabanında bu ID'ye ait bir fatura bulunamazsa sayfadan çıkar
    if (!$bakim_detayi) {
        echo "<div class='container mt-4'><div class='alert alert-danger'>Hata: Belirtilen bakım kaydı sistemde bulunamadı.</div></div>";
        include 'includes/footer.php';
        exit();
    }

} catch (PDOException $veritabani_hatasi) {
    echo "<div class='container mt-4'><div class='alert alert-danger'>Veritabanı Hatası: " . $veritabani_hatasi->getMessage() . "</div></div>";
    include 'includes/footer.php';
    exit();
}
?>

<div class="container mt-4">
    <div class="row mb-3 d-print-none">
        <div class="col-12">
            <a href="personel_panel.php" class="btn btn-sm btn-outline-dark me-2">Panele Dön</a>
            <a href="arac_ara.php" class="btn btn-sm btn-sm btn-outline-primary me-2">Yeni Araç Ara</a>
            <button onclick="window.print()" class="btn btn-sm btn-success float-end">Faturayı Yazdır / PDF Yap</button>
        </div>
    </div>

    <div class="card border-dark">
        <div class="card-header bg-dark text-white p-3 text-center">
            <h3 class="mb-0"> ARAÇ SERVİS BAKIM FATURASI</h3>
            <small>Fatura No: #<?php echo $bakim_detayi['fatura_no']; ?> | İşlem Tarihi: <?php echo $bakim_detayi['islem_tarih']; ?></small>
        </div>
        <div class="card-body p-4">
            
            <div class="row mb-4">
                <div class="col-md-6 mb-3">
                    <h5 class="border-bottom pb-2 text-primary"> Müşteri Bilgileri</h5>
                    <p class="mb-1"><strong>Adı Soyadı:</strong> <?php echo htmlspecialchars($bakim_detayi['musteri_ad']); ?></p>
                    <p class="mb-1"><strong>Telefon:</strong> <?php echo htmlspecialchars($bakim_detayi['musteri_tel'] ?? 'Girilmemiş'); ?></p>
                </div>
                
                <div class="col-md-6 mb-3">
                    <h5 class="border-bottom pb-2 text-danger">Araç Bilgileri</h5>
                    <p class="mb-1"><strong>Plaka:</strong> <span class="badge bg-danger text-uppercase"><?php echo htmlspecialchars($bakim_detayi['plaka']); ?></span></p>
                    <p class="mb-1"><strong>Marka / Model:</strong> <?php echo htmlspecialchars($bakim_detayi['marka'] . " " . $bakim_detayi['model'] . " (" . $bakim_detayi['yil'] . ")"); ?></p>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2 text-dark">Yapılan Teknik İşlemler</h5>
                    <div class="bg-light p-3 rounded" style="white-space: pre-line;">
                        <?php echo htmlspecialchars($bakim_detayi['yapilan_islem']); ?>
                    </div>
                </div>
            </div>

            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="border-bottom pb-2 text-dark">Değişen Yedek Parçalar</h5>
                    <p class="text-muted"><?php echo htmlspecialchars($bakim_detayi['degisen_parca']); ?></p>
                </div>
            </div>

            <?php if (!empty($bakim_detayi['servis_notu'])): ?>
                <div class="row mb-4">
                    <div class="col-12">
                        <h5 class="border-bottom pb-2 text-dark">Servis Notu</h5>
                        <p class="text-secondary italic"><em><?php echo htmlspecialchars($bakim_detayi['servis_notu']); ?></em></p>
                    </div>
                </div>
            <?php endif; ?>

            <div class="row mt-5 pt-3 border-top">
                <div class="col-md-6">
                    <p class="mb-1 text-muted">İşlemi Gerçekleştiren Personel:</p>
                    <strong><?php echo htmlspecialchars($bakim_detayi['personel_ad']); ?></strong>
                </div>
                <div class="col-md-6 text-md-end mt-3 mt-md-0">
                    <h5 class="text-muted mb-1">TOPLAM TUTAR</h5>
                    <h2 class="text-success fw-bold">₺<?php echo number_format($bakim_detayi['toplam_ucret'], 2, ',', '.'); ?></h2>
                </div>
            </div>

        </div>
        <div class="card-footer bg-light text-center py-2">
            <small class="text-muted">Bizi tercih ettiğiniz için teşekkür ederiz. Güvenli sürüşler dileriz!</small>
        </div>
    </div>
</div>

<?php 
// Ortak footer alanını ekliyoruz
include 'includes/footer.php'; 
?>