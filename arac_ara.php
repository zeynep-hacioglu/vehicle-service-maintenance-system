<?php

session_start(); // Oturum kontrolü için başlatıyoruz

// Veritabanı bağlantısı ve ortak header dosyamızı dahil ediyoruz
require_once 'db.php'; 
include 'includes/header.php'; 

// GÜVENLİK KONTROLÜ: Sadece 'Personel' rolündeki kullanıcılar bu sayfayı görebilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'Personel') {
    header("Location: login.php");
    exit();
}
$arama_kelimesi = "";
$arama_sonuclari = [];
$hata_mesaji = "";

// Form gönderildi mi (Post veya Get ile arama yapıldı mı) kontrol ediyoruz
if (isset($_GET['arama_terimi'])) {
    // Kullanıcının formdan girdiğini alıp temizliyoruz ve büyük harfe çeviriyoruz (Plakalar için kolaylık olsun)
    $arama_kelimesi = trim($_GET['arama_terimi']);

    if ($arama_kelimesi != "") {
        try {
            // LIKE yapısı ile arama sorgusu hazırlıyoruz.
            // Hem araç plakasına, hem araç markasına, hem de müşterinin adına göre arama yapabiliyoruz.
            $arama_sorgusu = $db->prepare("
                SELECT a.id as arac_tablo_id, a.plaka, a.marka, a.model, a.yil,
                       k.ad_soyad as musteri_ad_soyad, k.telefon as musteri_telefon
                FROM ARAC a
                JOIN KULLANICI k ON a.musteri_id = k.id
                WHERE a.plaka LIKE :aranan1
                   OR a.marka LIKE :aranan2
                   OR k.ad_soyad LIKE :aranan3
                ORDER BY a.plaka ASC
            ");

            $aranacak_deger = "%" . $arama_kelimesi . "%";
            $arama_sorgusu->execute([
              ':aranan1' => $aranacak_deger,
              ':aranan2' => $aranacak_deger,
              ':aranan3' => $aranacak_deger
            ]);

            // Bulunan tüm araç satırlarını diziye aktarıyoruz
            $arama_sonuclari = $arama_sorgusu->fetchAll();

        } catch (PDOException $veritabani_hatasi) {
            $hata_mesaji = "Arama yapılırken bir hata oluştu: " . $veritabani_hatasi->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2> Araç / Müşteri Arama Paneli</h2>
            <p class="text-muted">Sisteme kayıtlı araçları plakaya, markaya veya müşteri adına göre anlık olarak arayabilirsiniz.</p>
            <hr>
        </div>
    </div>

    <?php if ($hata_mesaji != ""): ?>
        <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
    <?php endif; ?>

    <div class="card mb-4">
        <div class="card-body bg-light">
            <form method="GET" action="arac_ara.php">
                <div class="row g-3 align-items-center">
                    <div class="col-md-9">
                        <input type="text" name="arama_terimi" class="form-control" 
                               placeholder="Örn: 16BTU16, Volkswagen veya Ali Yılmaz yazın..." 
                               value="<?php echo htmlspecialchars($arama_kelimesi); ?>" required>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">Sistemde Ara</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['arama_terimi'])): ?>
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Arama Sonuçları (<?php echo count($arama_sonuclari); ?> Kayıt Bulundu)</h5>
            </div>
            <div class="card-body">
                
                <?php if (count($arama_sonuclari) == 0): ?>
                    <div class="alert alert-warning text-center mb-0">
                        "<strong><?php echo htmlspecialchars($arama_kelimesi); ?></strong>" kriterine uygun hiçbir araç veya müşteri kaydı bulunamadı.
                    </div>
                <?php else: ?>
                    
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Plaka</th>
                                    <th>Araç Bilgisi</th>
                                    <th>Model Yılı</th>
                                    <th>Müşteri Ad Soyad</th>
                                    <th>Müşteri Telefon</th>
                                    <th class="text-center">İşlem</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($arama_sonuclari as $arac): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-danger text-uppercase fs-6">
                                                <?php echo htmlspecialchars($arac['plaka']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($arac['marka'] . " " . $arac['model']); ?></td>
                                        <td><?php echo htmlspecialchars($arac['yil']); ?></td>
                                        <td><?php echo htmlspecialchars($arac['musteri_ad_soyad']); ?></td>
                                        <td><?php echo htmlspecialchars($arac['musteri_telefon'] ?? 'Girilmemiş'); ?></td>
                                        <td class="text-center">
                                            <a href="bakim_formu.php?arac_id=<?php echo $arac['arac_tablo_id']; ?>" class="btn btn-sm btn-success">
                                                 Bakım Formu Aç
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                <?php endif; ?>
                
            </div>
        </div>
    <?php endif; ?>
</div>

<?php 
// Ortak footer alanını ekliyoruz
include 'includes/footer.php'; 
?>