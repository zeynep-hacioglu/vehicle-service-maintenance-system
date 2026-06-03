<?php
session_start(); // Oturum kontrolü için başlatıyoruz

// Veritabanı bağlantı dosyamızı dahil ediyoruz
require_once 'db.php'; 

// GÜVENLİK KONTROLÜ: Sadece 'Personel' rolündeki kullanıcılar bu işlemi tetikleyebilir
if (!isset($_SESSION['kullanici_id']) || $_SESSION['rol'] !== 'Personel') {
    header("Location: login.php");
    exit();
}
if (isset($_GET['randevu_id']) && isset($_GET['islem_tipi'])) {
    
    // Gelen verileri daha rahat kullanabilmek için basit değişkenlere aktarıyoruz
    $gelen_id = $_GET['randevu_id'];
    $gelen_islem = $_GET['islem_tipi'];
    
    // Veritabanındaki ENUM sütununa yazacağımız değeri tutacak boş bir değişken oluşturuyoruz
    $yeni_durum_metni = "";
    
    if ($gelen_islem == 'onayla') {
        $yeni_durum_metni = "Onaylandi.";
    } elseif ($gelen_islem == 'reddet') {
        $yeni_durum_metni = "Reddedildi.";
    }

    // Eğer yukarıdaki şartlara uyan geçerli bir işlem tipi geldiyse veritabanını güncelleyelim
    if ($yeni_durum_metni != "") {
        try {
            $guncelleme_sorgusu = $db->prepare("
                UPDATE RANDEVU 
                SET durum = :p_durum 
                WHERE id = :p_id
            ");
            
            // Parametreleri güvenli bir şekilde sorguya bağlıyor ve çalıştırıyoruz
            $guncelleme_sorgusu->execute([
                ':p_durum' => $yeni_durum_metni,
                ':p_id'    => $gelen_id
            ]);
            
            // Güncelleme başarılı olduysa, personel paneline "basarili" koduyla geri dönüyoruz
            header("Location: personel_panel.php?durum=basarili");
            exit();

        } catch (PDOException $veritabani_hatasi) {
            // Veritabanı hatası oluşursa panele "hata" koduyla geri dönüyoruz
            header("Location: personel_panel.php?durum=hata");
            exit();
        }
    } else {
        header("Location: personel_panel.php");
        exit();
    }

} else {
    // Eğer bu sayfaya butonlara tıklamadan, direkt URL satırına adını yazarak girmeye çalışırlarsa engelliyoruz ve personel paneline geri gönderiyoruz
    header("Location: personel_panel.php");
    exit();
}
?>