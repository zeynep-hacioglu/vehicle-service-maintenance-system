<?php
session_start(); 
require_once 'db.php'; 
include 'includes/header.php'; 

// GÜVENLİK KONTROLÜ: Sadece 'Personel' giriş yaptıysa bu formu görebilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'Personel') {
    header("Location: login.php");
    exit();
}

$aktif_personel_id = $_SESSION['kullanici_id'];
$hata_mesaji = "";
$basarili_mesaji = "";
$arac_bilgisi = null;

// Hangi araca bakım yapılacağını URL'deki 'arac_id' parametresinden anlıyoruz
if (!isset($_GET['arac_id'])) {
    // Eğer araç ID'si gönderilmediyse formu açamayız, arama sayfasına geri gönderiyoruz
    header("Location: arac_ara.php");
    exit();
}

$gelen_arac_id = (int)$_GET['arac_id'];

try {
    // Formun üstünde aracın kime ait olduğunu ve modelini göstermek için araç bilgilerini çekiyoruz
    $arac_bul_sorgu = $db->prepare("
        SELECT a.id, a.plaka, a.marka, a.model, k.ad_soyad ,a.musteri_id
        FROM ARAC a
        JOIN KULLANICI k ON a.musteri_id = k.id
        WHERE a.id = ?
    ");
    $arac_bul_sorgu->execute([$gelen_arac_id]);
    $arac_bilgisi = $arac_bul_sorgu->fetch();

    // Eğer veritabanında böyle bir araç ID'si yoksa yine sayfadan çıkarıyoruz
    if (!$arac_bilgisi) {
        header("Location: arac_ara.php");
        exit();
    }

} catch (PDOException $veritabani_hatasi) {
    $hata_mesaji = "Araç bilgileri getirilirken hata oluştu: " . $veritabani_hatasi->getMessage();
}


//Personel formu doldurup "Kaydet" butonuna bastığında (POST edildiğinde) çalışacak kısım
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Form elemanlarından gelen verileri temizleyerek değişkenlere alıyoruz
    $yapilan_islem = trim($_POST['yapilan_islem']);
    $degisen_parca = trim($_POST['degisen_parca']);
    $toplam_ucret  = trim($_POST['toplam_ucret']);
    $bakim_notu    = trim($_POST['bakim_notu']);
    $islem_tarihi  = date('Y-m-d'); // Bakımın yapıldığı bugünün tarihi

    // Basit bir kontrol: Zorunlu alanlar boş mu?
    if (empty($yapilan_islem) || empty($toplam_ucret)) {
        $hata_mesaji = "Lütfen yapılan işlem ve toplam ücret alanlarını boş bırakmayınız.";
    } else {
        try {
            // ÖĞRENCİ ÇÖZÜMÜ: Bu araca ait aktif bir randevu var mı diye bakıyoruz
            $randevu_bul = $db->prepare("SELECT id FROM RANDEVU WHERE arac_id = ? ORDER BY id DESC LIMIT 1");
            $randevu_bul->execute([$gelen_arac_id]);
            $randevu_satir = $randevu_bul->fetch();

            // Eğer veritabanında bu araca ait bir randevu bulunduysa onun ID'sini alıyoruz
            if ($randevu_satir) {
                $randevu_id = $randevu_satir['id'];
            } else {
                // Eğer müşteri dükkana randevusuz, çat kapı geldiyse ve SQL hata vermesin istiyorsak:
                // Önce RANDEVU tablosuna bu araç için 'Tamamlandi.' durumunda hayali bir servis randevusu açabiliriz
                $hayali_randevu = $db->prepare("INSERT INTO RANDEVU (randevu_tarih, saat, durum, musteri_id, arac_id, notlar) VALUES (?, ?, 'Tamamlandi.', ?, ?, 'Randevusuz Doğrudan Giriş')");
                $hayali_randevu->execute([date('Y-m-d H:i:s'), date('H:i:s'), $arac_bilgisi['musteri_id'], $gelen_arac_id]);
                $randevu_id = $db->lastInsertId();
            }
            $kayit_sorgusu = $db->prepare("
                INSERT INTO BAKIM_KAYIT (yapilan_islem, degisen_parca, toplam_ucret, islem_tarih, notlar, arac_id, randevu_id, personel_id)
                VALUES (:islem, :parca, :ucret, :tarih, :notlar, :arac, :randevu, :personel)
            ");
            $kayit_sorgusu->execute([
                ':islem'    => $yapilan_islem,
                ':parca'    => !empty($degisen_parca) ? $degisen_parca : 'Değişen parça yok',
                ':ucret'    => (float)$toplam_ucret,
                ':tarih'    => $islem_tarihi,
                ':notlar'   => $bakim_notu, 
                ':arac'     => $gelen_arac_id,
                ':randevu'  => $randevu_id, 
                ':personel' => $aktif_personel_id
            ]);

            // En son eklenen bakım kaydının ID'sini alıyoruz
            $son_eklenen_bakim_id = $db->lastInsertId();

            // Eğer bu işlem bir randevu üzerinden yapıldıysa, o randevunun durumunu da 'Tamamlandi.' olarak güncelleyelim
            $randevu_guncelle = $db->prepare("UPDATE RANDEVU SET durum = 'Tamamlandi.' WHERE id = ?");
            $randevu_guncelle->execute([$randevu_id]);

            header("Location: bakim_detay.php?bakim_id=" . $son_eklenen_bakim_id);
            exit();

        } catch (PDOException $veritabani_hatasi) {
            $hata_mesaji = "Bakım kaydı eklenirken hata oluştu: " . $veritabani_hatasi->getMessage();
        }
    }
}
?>

<div class="container mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2>Yeni Servis & Bakım Formu Aç</h2>
            <?php if ($arac_bilgisi): ?>
                <div class="alert alert-info">
                    <strong>Müşteri:</strong> <?php echo htmlspecialchars($arac_bilgisi['ad_soyad']); ?> | 
                    <strong>Araç:</strong> <?php echo htmlspecialchars($arac_bilgisi['marka'] . " " . $arac_bilgisi['model']); ?> | 
                    <strong>Plaka:</strong> <span class="badge bg-danger"><?php echo htmlspecialchars($arac_bilgisi['plaka']); ?></span>
                </div>
            <?php endif; ?>
            <hr>
        </div>
    </div>

    <?php if ($hata_mesaji != ""): ?>
        <div class="alert alert-danger"><?php echo $hata_mesaji; ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0"> Yapılan İşlemleri Giriş Ekranı</h5>
        </div>
        <div class="card-body">
            
            <form method="POST" action="bakim_formu.php?arac_id=<?php echo $gelen_arac_id; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Yapılan Servis İşlemleri *</label>
                    <textarea name="yapilan_islem" class="form-control" rows="4" 
                              placeholder="Araçta yapılan tamiratları, motor yağı değişimi, balata kontrolü gibi detayları buraya yazınız..." required></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Değişen Yedek Parçalar</label>
                    <input type="text" name="degisen_parca" class="form-control" 
                           placeholder="Örn: Ön fren balataları, Yağ filtresi (Yoksa boş bırakın)">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Toplam Servis Ücreti (TL) *</label>
                    <div class="input-group">
                        <span class="input-group-text">₺</span>
                        <input type="number" step="0.01" name="toplam_ucret" class="form-control" 
                               placeholder="Örn: 2450.00" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Servis Özel Notu</label>
                    <textarea name="bakim_notu" class="form-control" rows="2" 
                              placeholder="Müşteriye iletilmesini istediğiniz bir sonraki bakım zamanı vb. ek notlar..."></textarea>
                </div>

                <div class="row pt-2">
                    <div class="col-md-6 mb-2">
                        <button type="submit" class="btn btn-success w-100">Bakım Kaydını Bitir ve Fatura Çıkar</button>
                    </div>
                    <div class="col-md-6">
                        <a href="arac_ara.php" class="btn btn-outline-secondary w-100">İptal Et Geri Dön</a>
                    </div>
                </div>

            </form>

        </div>
    </div>
</div>

<?php 
// Ortak footer alanını dahil ediyoruz
include 'includes/footer.php'; 
?>