<?php
//Musterinin aracı icin tarih ve saat secerek randevu almasini saglar

session_start();
require_once 'db.php'; //ortak veritabani baglantisini dosyaya ekliyoruz

//Kullanici giris yapmis ise otomatik olarak musteri paneline yönlendirir.
if(!isset($_SESSION['kullanici_id'])|| $_SESSION['rol'] !== 'Müşteri'){
    header('Location: login.php');
    exit;
}

$musteri_id=$_SESSION['kullanici_id']; //Giris yapmis olan kullanicinin id'sini aldik
$mesaj=[];
$basari="";

//Acilir pencereden araclarını secmek icin sorgu
$sorguArac=$db->prepare("SELECT id, plaka, marka, model FROM ARAC WHERE musteri_id=?");
$sorguArac->execute([$musteri_id]); 
$araclar=$sorguArac->fetchAll();

$seciliArac=(int)($_GET['arac_id'] ?? 0);

//form gönderildi mi kontrol edecegiz
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $aracId=(int)($_POST['arac_id'] ?? 0); 
    $randevu_tarih=trim($_POST['randevu_tarih'] ?? '');
    $saat=trim($_POST['saat'] ?? '');
    $notlar=trim($_POST['notlar'] ?? '');
   
    //Girilen verilerin dogrulama kismi
    if ($aracId <= 0) 
        $mesaj[]='Lütfen bir araç seçin';

    if (empty($randevu_tarih))
        $mesaj[]='Lütfen randevu tarihi seçin.';

    if (empty($saat))
        $mesaj[]='Lütfen randevu saati seçin.';

    //Secilen aracin musteriye ait olup olmadiginin kontrolu
    if($aracId > 0){
        $sorgu=$db->prepare("SELECT id FROM ARAC WHERE id=? AND musteri_id=?");
        $sorgu->execute([$aracId, $musteri_id]);
        if(!$sorgu->fetch()){
            $mesaj[]="Geçersiz araç seçimi yaptınız.";
        }
    }

    //Tarihin gecmiste olup olmadigini kontrol ediyoruz
    if(!empty($randevu_tarih) && strtotime($randevu_tarih) < strtotime(date('Y-m-d'))){
        $mesaj[]='Geçmiş bir tarih için randevu oluşturamazsınız.';
    }

    //Randevu alinan zaracin zaten o gun randevusu var mi?
    if(empty($mesaj)){
        $sorgu=$db->prepare("SELECT id FROM RANDEVU WHERE arac_id=? AND randevu_tarih=? AND durum !='İptal'");
        $sorgu->execute([$aracId, $randevu_tarih]);
        if($sorgu->fetch()){
             $mesaj[]='Seçilen tarihte bu araç için zaten aktif bir randevu var.';
        }
    }

    //Sisteme kaydetme
    if(empty($mesaj)){
        $sorgu=$db->prepare("INSERT INTO RANDEVU (musteri_id, arac_id, randevu_tarih, saat, notlar, durum) VALUES (?, ?, ?, ?, ?, 'Onay Bekliyor.')");
        $sorgu->execute([$musteri_id, $aracId, $randevu_tarih, $saat, $notlar]);
        $basari='Randevu talebiniz oluşturulmuştur. Personelimiz en kısa sürede randevunuzu onaylayacaktır.' . '<a href="musteri_panel.php">Müşteri paneline geri dön.</a>';
        
        $_POST=[]; //Formu temizliyoruz
    }
}

$pageTitle='Randevu Al';
include 'includes/header.php'; //ortak kullanilabilen header dosyasini ekledik
?>

<div class="row justify-content-center"> <!--grid sistemini başlatıp içeriği ortalamak için kullandık. -->
    <div class="col-md-6"> <!--form kutusu genişliği -->
        <div class="card shadow-sm border-0"> <!--kart görünümünü ayarladık. -->
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Randevu Talebi Oluştur</h4> <!--açıklama bölümü -->
            </div>
            
            <div class="card-body p-4">
                <?php if(empty($araclar)): ?>
                    <!--Kayitli arac yoksa randevu olusturulamaz -->
                    <div class="alert alert-warning">
                        Randevu oluşturmak için lütfen önce araç ekleyiniz.
                        <a href="arac_ekle.php" class="btn btn-warning btn-sm ms-2">Araç Ekle</a>
                    </div>
                <?php else: ?>

                    <?php if($basari): ?>
                        <div class="alert alert-success"><?= $basari ?> </div>
                    <?php endif; ?>

                    <?php if($mesaj): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach($mesaj as $m): ?>
                                    <li><?= htmlspecialchars($m) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="randevu_al.php">
                        <div class="mb-3">
                            <label class="form-label">Araç *</label> <!--* koyarak bu alanin doldurulmasinin zorunlu oldugunu belirtiyoruz. -->
                            <select name="arac_id" class="form-select" required>
                                <option value="">--Araç seçin --</option>
                                <?php foreach($araclar as $arac): ?>
                                    <option value="<?= $arac['id'] ?>"
                                        <?= ($seciliArac===$arac['id'] || ($_POST['arac_id'] ?? 0) == $arac['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($arac['plaka']. ' - ' .$arac['marka']. ' ' .$arac['model']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Tarih secimi -->
                        <div class="mb-3">
                            <label class="form-label">Tarih *</label>
                            <input type="date" class="form-control" name="randevu_tarih" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($_POST['randevu_tarih'] ?? '') ?>" required>
                        </div>
                    
                        <!-- Saat secimi -->
                        <div class="mb-3">
                            <label class="form-label">Saat *</label>
                            <select name="saat" class="form-select" required>
                                <option value="">--Saat seçin --</option>
                                <?php 
                                    for($saat=8; $saat<=17; $saat++){ //8.00 ile 17.00 arası 30 dakikada bir randevu alabilecegi bir secenek olusturuyoruz
                                        foreach(['00', '30'] as $dk){
                                            $saatStr = sprintf("%02d:%s", $saat, $dk); //saat ve dakikayi birlestirir
                                            $selected = ($saatStr == ($_POST['saat'] ?? '')) ? 'selected' : ''; //Saat onceden secilmisse secilen saat isaretlenir
                                            echo "<option value='$saatStr' $selected>$saatStr</option>"; //opsiyon olarak saat ve dakika secenekleri eklenir
                                        }
                                    } 
                                ?>
                            </select>
                        </div>

                        <!-- Notlar bolumu -->
                        <div class="col-md-12">
                            <label class="form-label">Notlar <span class="text-muted">(İsteğe bağlı)</span></label>
                            <textarea name="notlar" class="form-control" rows="3" placeholder="Randevu ile ilgili eklemek istediğiniz bilgileri buraya yazınız."><?= htmlspecialchars($_POST['notlar'] ?? '') ?></textarea>
                        </div>

                        <div class="d-grid gap-2"> <!-- Butonlari alt alta ve tam genislikte yapmak icin d-grid ve gap-2 siniflarini kullandik -->
                            <button type="submit" class="btn btn-primary w-100">Randevu Oluştur</button>
                            <a href="musteri_panel.php" class="btn btn-outline-secondary w-100">İptal</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> <!--Ortak olarak kullanilabilecek footeri ekledik -->
