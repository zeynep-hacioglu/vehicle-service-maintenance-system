<?php
session_start(); // Kullanıcı oturumunu kontrol edebilmek için başlatıyoruz

// Projedeki ortak veritabanı bağlantı dosyamızı çağırıyoruz
require_once 'db.php'; 

// Sitenin üst menü ve tasarım bütünlüğü için header dosyasını ekliyoruz
include 'includes/header.php'; 

// GÜVENLİK KONTROLÜ: Sisteme giriş yapılmamışsa veya giren kişi Personel değilse login sayfasına at
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'Personel') {
    header("Location: login.php");
    exit();
}

$aktif_personel_id = $_SESSION['kullanici_id'];
$hata_mesaji = "";
$basarili_mesaji = "";

// URL'den gelen bir işlem sonucu  var mı diye bakıyoruz
if (isset($_GET['durum'])) {
    if ($_GET['durum'] == 'basarili') {
        $basarili_mesaji = "Randevu durumu başarıyla güncellendi.";
    } elseif ($_GET['durum'] == 'hata') {
        $hata_mesaji = "Randevu durumu güncellenirken bir hata oluştu.";
    }
}

// Sorgulardan önce diziyi boş olarak tanımlıyoruz ki alt tarafta count() asla patlamasın!
$onayBekleyenRandevuSayisi = 0;
$onaylananRandevuSayisi = 0;
$tamamlananRandevuSayisi = 0;
$onayBekleyenRandevuListesi = [];

try {
    // 1. İSTATİSTİK SAYAÇLARI (Şemadaki ENUM değerlerine tam uyumlu)
    $bekleyen_sorgu = $db->query("SELECT COUNT(*) as toplam FROM RANDEVU WHERE durum = 'Onay Bekliyor.'");
    if ($bekleyen_sorgu) { $onayBekleyenRandevuSayisi = $bekleyen_sorgu->fetch()['toplam']; }

    $onayli_sorgu = $db->query("SELECT COUNT(*) as toplam FROM RANDEVU WHERE durum = 'Onaylandi.'");
    if ($onayli_sorgu) { $onaylananRandevuSayisi = $onayli_sorgu->fetch()['toplam']; }

    $tamamlanan_sorgu = $db->query("SELECT COUNT(*) as toplam FROM RANDEVU WHERE durum = 'Tamamlandi.'");
    if ($tamamlanan_sorgu) { $tamamlananRandevuSayisi = $tamamlanan_sorgu->fetch()['toplam']; }

    // 2. TABLO LİSTELEME SORGUSU 
    $listeleme_sorgusu = $db->query("
        SELECT r.id as randevu_id, r.randevu_tarih, r.saat, r.notlar as randevu_notu, r.durum,
               a.plaka, a.marka, a.model,
               k.ad_soyad as musteri_ad_soyad
        FROM RANDEVU r
        JOIN ARAC a ON r.arac_id = a.id
        JOIN KULLANICI k ON r.musteri_id = k.id
        WHERE r.durum = 'Onay Bekliyor.'
        ORDER BY r.randevu_tarih ASC, r.saat ASC
    ");
    
    // Eğer sorgu başarılıysa verileri yükle, hata oluştuysa boş diziyi koru
    if ($listeleme_sorgusu) {
        $onayBekleyenRandevuListesi = $listeleme_sorgusu->fetchAll();
    }

} catch (PDOException $veritabani_hatasi) {
    $hata_mesaji = "Veritabanından veri çekilirken bir hata oluştu: " . $veritabani_hatasi->getMessage();
}
?>

<div class="container mt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-8">
            <h2>Teknik Servis Personel Paneli</h2>
            <p class="text-muted">Hoş geldiniz. Bu panelden randevu taleplerini yönetebilir ve araç servis kayıtlarını oluşturabilirsiniz.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="arac_ara.php" class="btn btn-primary btn-lg shadow-sm">Araç / Müşteri Ara</a>
        </div>
    </div>

    <?php if ($basarili_mesaji != ""): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $basarili_mesaji; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($hata_mesaji != ""): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo $hata_mesaji; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="card bg-warning text-dark h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                    <h6 class="text-uppercase fw-bold mb-2">Onay Bekleyenler</h6>
                    <h2 class="display-4 fw-bold mb-0"><?php echo (int)$onayBekleyenRandevuSayisi; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-info text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                    <h6 class="text-uppercase fw-bold mb-2">Onaylanan Randevular</h6>
                    <h2 class="display-4 fw-bold mb-0"><?php echo (int)$onaylananRandevuSayisi; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card bg-success text-white h-100">
                <div class="card-body d-flex flex-column justify-content-center align-items-center py-4">
                    <h6 class="text-uppercase fw-bold mb-2">Tamamlanan İşler</h6>
                    <h2 class="display-4 fw-bold mb-0"><?php echo (int)$tamamlananRandevuSayisi; ?></h2>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Onay Bekleyen Güncel Randevu Talepleri</h5>
            <span class="badge bg-light text-dark fw-bold">
                <?php echo is_array($onayBekleyenRandevuListesi) ? count($onayBekleyenRandevuListesi) : 0; ?> Yeni İstek
            </span>
        </div>
        <div class="card-body">
            <?php if (!is_array($onayBekleyenRandevuListesi) || count($onayBekleyenRandevuListesi) == 0): ?>
                <div class="alert alert-light text-center mb-0 py-4">
                    <p class="mb-0 text-muted fs-5">Şu anda onay bekleyen herhangi bir randevu talebi bulunmuyor.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle mb-0">
                        <thead class="table-secondary">
                            <tr>
                                <th>Tarih</th>
                                <th>Saat</th>
                                <th>Müşteri Ad Soyad</th>
                                <th>Araç Plaka</th>
                                <th>Araç Detayı</th>
                                <th>Müşteri Notu</th>
                                <th class="text-center" style="width: 180px;">İşlemler</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($onayBekleyenRandevuListesi as $tek_randevu): ?>
                                <tr>
                                    <td><strong><?php echo date("d.m.Y", strtotime($tek_randevu['randevu_tarih'])); ?></strong></td>
                                    <td><code><?php echo date("H:i", strtotime($tek_randevu['saat'])); ?></code></td>
                                    <td><?php echo htmlspecialchars($tek_randevu['musteri_ad_soyad']); ?></td>
                                    <td><span class="badge bg-danger text-uppercase fs-6"><?php echo htmlspecialchars($tek_randevu['plaka']); ?></span></td>
                                    <td><?php echo htmlspecialchars($tek_randevu['marka'] . " " . $tek_randevu['model']); ?></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars($tek_randevu['randevu_notu'] ?? '-'); ?></small></td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="randevu_guncelle.php?randevu_id=<?php echo $tek_randevu['randevu_id']; ?>&islem_tipi=onayla" class="btn btn-success fw-bold">Onayla</a>
                                            <a href="randevu_guncelle.php?randevu_id=<?php echo $tek_randevu['randevu_id']; ?>&islem_tipi=reddet" class="btn btn-danger fw-bold" onclick="return confirm('Bu randevuyu reddetmek istediğinize emin misiniz?')">Reddet</a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>