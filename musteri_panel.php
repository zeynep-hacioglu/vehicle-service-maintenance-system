<?php

//Oturum kontrolu yapilir

session_start();
require_once 'db.php'; //ortak veritabani baglantisini dosyaya ekliyoruz

//Kullanici giris yapmamis ise engelle
if(!isset($_SESSION['kullanici_id'])|| $_SESSION['rol'] !== 'Müşteri'){
    header('Location: login.php');
    exit;
}

$musteriId=$_SESSION['kullanici_id']; //Giriis yapmis olan kullanicinin id'sini aldik
$mesaj=[];
$basari='';

//Kayitli arac silme bolumu
if(isset($_GET['arac_sil'])){
    $silId=(int)$_GET['arac_sil'];
    $kontrol=$db->prepare("SELECT id FROM ARAC WHERE id=? AND musteri_id=?");
    $kontrol->execute([$silId, $musteriId]);
    if($kontrol->fetch()){
        $db->prepare("DELETE FROM ARAC WHERE id=?")->execute([$silId]);
        $basari='Kayıtlı araç başarı ile silindi.';
    }
}

//Randevu silme bolumu (sadece onay bekleyen randevular silinebilir)
if(isset($_GET['randevu_sil'])){
    $silId=(int)$_GET['randevu_sil'];
    $kontrol=$db->prepare("SELECT id FROM RANDEVU WHERE id=? AND musteri_id=? AND durum='Onay Bekliyor.'");
    $kontrol->execute([$silId, $musteriId]);
    if($kontrol->fetch()){
        $db->prepare("DELETE FROM RANDEVU WHERE id=?")->execute([$silId]);
        $basari='Randevu başarı ile silindi.';
    }
}

//Kayitli arac duzenleme bolumu
$duzenlenenArac=null; //Duzenlenecek olan araci tutacak olan degisken

if(isset($_GET['arac_duzenle'])){
    $duzenleId=(int)($_GET['arac_duzenle']);
    $sorgu= $db->prepare("SELECT * FROM ARAC WHERE id=? AND musteri_id=?");
    $sorgu->execute([$duzenleId, $musteriId]);
    $duzenlenenArac=$sorgu->fetch(); //Formu doldurmak icin arac verisni sil
}
if($_SERVER["REQUEST_METHOD"]==="POST" && isset($_POST['arac_guncelle'])){
    $aracId=(int)($_POST['arac_id']);
    $plaka=strtoupper(trim($_POST['plaka'] ?? '')); //Plakayi buyuk harfe cevirmek icin strtoupper kullandik
    $marka=trim($_POST['marka'] ?? '');
    $model=trim($_POST['model'] ?? '');
    $yil=(int)($_POST['yil'] ?? '');
   
    //Girilen verilerin dogrulama kismi
    if (empty($plaka)) 
        $mesaj[]='Plaka alanının doldurulması zorunludur.';
    if (!preg_match('/^[0-9]{2}[A-Z]{1,3}[0-9]{2,4}$/', str_replace(' ', '', $plaka))) //Plaka formatini kontrol ediyoruz. 
        $mesaj[]='Geçerli bir plaka giriniz. (Örn: 16ZHS016)';

    if (empty($marka))
        $mesaj[]='Marka alanının doldurulması zorunludur.';

    if (empty($model))
        $mesaj[]='Model alanının doldurulması zorunludur.';

    if ($yil<1900 || $yil>(int)date('Y') +1)
        $mesaj[]='Geçerli bir yıl giriniz.';

    //Plaka kontrolu yaptik
    if(empty($mesaj)){
        $kontrol=$db->prepare("SELECT id FROM ARAC WHERE plaka=? AND id !=?");
        $kontrol->execute([$plaka, $aracId]);
        if($kontrol->fetch()){
            $mesaj[]="Girdiğiniz ($plaka) plaka başka bir araçta kayıtlıdır.";
        }
    }

    //Hata yoksa kayit olustur
    if(empty($mesaj)){
        $db->prepare("UPDATE ARAC SET plaka=?, marka=?, model=?, yil=? WHERE id=? AND musteri_id=?")
            ->execute([$plaka, $marka, $model, $yil, $aracId, $musteriId]);
        $basari= 'Araç bilgileri güncellendi.';
        $duzenlenenArac=null; //Duzenleme formunu kapat
    } else {
        $duzenlenenArac=['id'=>$aracId, 'plaka'=>$plaka, 'marka'=>$marka, 'model'=>$model, 'yil'=>$yil]; //hata olusursa formu tekrar gondermek icin
    }
}

//randevu duzenleme bolumu,
$duzenlenenRandevu=null; //Duzenlenecek olan randevuyu tutacak

if(isset($_GET['randevu_duzenle'])){
    $duzenleId=(int)$_GET['randevu_duzenle'];
    $sorgu=$db->prepare("SELECT r.*, a.plaka, a.marka, a.model FROM RANDEVU AS r JOIN ARAC AS a ON r.arac_id=a.id WHERE r.id=? AND r.musteri_id=? AND r.durum='Onay Bekliyor.'");
    $sorgu->execute([$duzenleId, $musteriId]);
    $duzenlenenRandevu=$sorgu->fetch();
}

if($_SERVER["REQUEST_METHOD"]==="POST" && isset ($_POST['randevu_guncelle'])){
    $randevuId=(int)$_POST['randevu_id'];
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

    //Tarihin gecmiste olup olmadigini kontrol ediyoruz
    if(!empty($randevu_tarih) && strtotime($randevu_tarih) < strtotime(date('Y-m-d'))){
        $mesaj[]='Geçmiş bir tarih seçemezsiniz.';
    }

    //Ayni gun icinde cakisan randevu var mi?
    if(empty($mesaj)){
        $sorgu=$db->prepare("SELECT id FROM RANDEVU WHERE arac_id=? AND randevu_tarih=? AND durum !='Reddedildi.' AND id !=?");
        $sorgu->execute([$aracId, $randevu_tarih, $randevuId]);
        if($sorgu->fetch()){
             $mesaj[]='Seçilen tarihte bu araç için zaten aktif bir randevu var.';
        }
    }

    //Sisteme kaydetme
    if(empty($mesaj)){
        $db->prepare("UPDATE RANDEVU SET arac_id=?, randevu_tarih=?, saat=?, notlar=? WHERE id=? AND musteri_id=? AND durum='Onay Bekliyor.'") //sadece onay bekleyen randevular duzenlenip iptal edilebilir
        ->execute([$aracId, $randevu_tarih, $saat, $notlar, $randevuId, $musteriId]);
        $basari='Randevunuz güncellendi.';
    } else {
        $duzenlenenRandevu=['id'=>$randevuId, 'arac_id'=>$aracId, 'tarih'=>$randevu_tarih, 'saat'=>$saat, 'notlar'=>$notlar]; //hata olusursa formu tekrar gondermek icin
    }
}

//Musteriye ait araclari ssitemden cek
$sorguArac=$db->prepare("SELECT * FROM ARAC WHERE musteri_id =? ORDER BY olusturma_tarih DESC");
$sorguArac->execute([$musteriId]);
$araclar=$sorguArac->fetchAll();

//Musterinin olusturdugu son 5 randevuyu cekecegiz
$sorguRandevu=$db->prepare("SELECT r.*, a.plaka, a.marka, a.model FROM RANDEVU AS r JOIN ARAC AS a ON r.arac_id=a.id WHERE r.musteri_id=? ORDER BY r.randevu_tarih DESC, r.saat DESC LIMIT 5");
$sorguRandevu->execute([$musteriId]);
$randevular=$sorguRandevu->fetchAll();

$pageTitle= 'Müşteri Paneli';
include 'includes/header.php'; //ortak kullanilabilen header dosyasini ekledik
?>


<!-- Musteri paneli ana sayfasinda kullanicinin kayitli arac ve olusturulan randevularini goruntuleyecegiz. -->
<!-- Musteri karsılama bolumu -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Hoş geldiniz, <strong><?php echo htmlspecialchars($_SESSION['ad_soyad']); ?>!</strong></h3>
    <div>
        <a href="arac_ekle.php" class="btn btn-primary btn-sm me-2"> Yeni Araç Ekle</a>
        <a href="randevu_al.php" class="btn btn-success btn-sm"> Yeni Randevu Oluştur</a>
    </div>
</div>

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

<!--Arac duzenleme bolumu -->
<?php if($duzenlenenArac): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">Araç Düzenle</div>
        <div class="card-body">
            <form method="POST" action="musteri_panel.php">
                <input type="hidden" name="arac_id" value="<?= $duzenlenenArac['id'] ?>">

                <div class="row g-3">
                    <div class="mb-3">
                        <label class="form-label">Plaka *</label> 
                        <input type="text" class="form-control text-uppercase" name="plaka" placeholder="16ZHS016" value="<?= htmlspecialchars($duzenlenenArac['plaka'] ?? '') ?>" maxlength="15" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marka *</label>
                        <input type="text" class="form-control" name="marka" placeholder="Örn: BMW, Volkswagen..." value="<?= htmlspecialchars($duzenlenenArac['marka'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Model *</label>
                        <input type="text" class="form-control" name="model" placeholder="Örn: i serisi, Golf..." value="<?= htmlspecialchars($duzenlenenArac['model'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Yıl *</label>
                        <input type="number" class="form-control" name="yil" min="1900" max="<?= date('Y') + 1 ?>" placeholder="Örn: 2018" value="<?= htmlspecialchars($duzenlenenArac['yil'] ?? '') ?>" required>
                    </div>

                    <div class="mt-3 d-flex gap-2">
                        <button type="submit" name="arac_guncelle" class="btn btn-warning w-100">Kaydet</button>
                        <a href="musteri_panel.php" class="btn btn-outline-secondary w-100">İptal</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<!-- Randevu duzenleme bolumu -->
<?php if($duzenlenenRandevu): ?>
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">Randevu Düzenle</div>
        <div class="card-body">
            <form method="POST" action="musteri_panel.php">
                <input type="hidden" name="randevu_id" value="<?= $duzenlenenRandevu['id'] ?>">

                <div class="row g-3">
                    <div class="mb-3">
                        <label class="form-label">Araç *</label> <!--* koyarak bu alanin doldurulmasinin zorunlu oldugunu belirtiyoruz. -->
                        <select name="arac_id" class="form-select" required>
                            <option value="">--Araç seçin --</option>
                            <?php foreach($araclar as $arac): ?>
                                <option value="<?= $arac['id'] ?>"
                                    <?= ($duzenlenenRandevu['arac_id'] == ($arac['id'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($arac['plaka']. ' - ' .$arac['marka']. ' ' .$arac['model']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Tarih secimi -->
                    <div class="mb-3">
                        <label class="form-label">Tarih *</label>
                        <input type="date" class="form-control" name="randevu_tarih" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($duzenlenenRandevu['randevu_tarih'] ?? '') ?>" required>
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
                                        $selected = ($saatStr == $duzenlenenRandevu['saat']) ? 'selected' : ''; //Saat onceden secilmisse secilen saat isaretlenir
                                        echo "<option value='$saatStr' $selected>$saatStr</option>"; //opsiyon olarak saat ve dakika secenekleri eklenir
                                    }
                                } 
                            ?>
                        </select>
                    </div>

                    <!-- Notlar bolumu -->
                    <div class="col-md-12">
                        <label class="form-label">Notlar <span class="text-muted">(İsteğe bağlı)</span></label>
                        <textarea name="notlar" class="form-control" rows="3" placeholder="Randevu ile ilgili eklemek istediğiniz bilgileri buraya yazınız."><?= htmlspecialchars($duzenlenenRandevu['notlar'] ?? '') ?></textarea>
                    </div>

                    <div class="mt-3 d-grid gap-2"> <!-- Butonlari alt alta ve tam genislikte yapmak icin d-grid ve gap-2 siniflarini kullandik -->
                        <button type="submit" name="randevu_guncelle" class="btn btn-warning w-100">Kaydet</button>
                        <a href="musteri_panel.php" class="btn btn-outline-secondary w-100">İptal</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>


<!-- Musteri Kayitli Arac Listesi(Araçlarım)  -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white"> Araçlarım </div>
    <div class="card-body p-0">
        <?php if(empty($araclar)): ?>
            <p class="text-muted"> Henüz araç eklemediniz. <a href="arac_ekle.php"> Araç ekle.</a></p>
        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Plaka</th>
                            <th>Marka</th>
                            <th>Model</th>
                            <th>Yıl</th>
                            <th>Randevu</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach($araclar as $arac): ?>
                            <tr>
                                <td><strong><?=  htmlspecialchars($arac['plaka']) ?></strong></td>
                                <td><?= htmlspecialchars($arac['marka']) ?></td>
                                <td><?= htmlspecialchars($arac['model']) ?></td>
                                <td><?= htmlspecialchars($arac['yil']) ?></td>
                                <td> 
                                    <a href="randevu_al.php?arac_id= <?=  $arac['id'] ?>" class="btn btn-sm btn-outline-primary"> Randevu Oluştur</a>
                                    <a href="musteri_panel.php?arac_duzenle= <?=  $arac['id'] ?>" class="btn btn-sm btn-outline-warning"> Düzenle</a> <!--Duzenleme bolumu acilir -->
                                    <a href="musteri_panel.php?arac_sil= <?=  $arac['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                    onclick="return confirm('<?= htmlspecialchars($arac['plaka']) ?> plakalı aracı silmek istediğinize emin misiniz?')"> Sil</a> <!--Silme onay penceresi -->
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Musterinin olusturdugu randevular(Randevularım) -->
 <div class="card mb-4">
    <div class="card-header bg-dark text-white"> Randevularım </div>
    <div class="card-body p-0">
        <?php if(empty($randevular)): ?>
            <p class="text-muted"> Henüz randevu oluşturmadınız. <a href="randevu_al.php"> Randevu olustur.</a></p>
        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Araç</th>
                            <th>Tarih</th>
                            <th>Saat</th>
                            <th>Durum</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($randevular as $randevu): ?>
                            <?php
                                $durumClass = '';
                                switch ($randevu['durum']) {
                                    case 'Onay Bekliyor':
                                        $durumClass = 'bg-warning text-dark';
                                        break;
                                    case 'Onaylandı':
                                        $durumClass = 'bg-success';
                                        break;
                                    case 'Reddedildi':
                                        $durumClass = 'bg-danger';
                                        break;
                                    case 'Tamamlandi':
                                        $durumClass = 'bg-secondary';
                                    default:
                                        $durumClass = 'bg-secondary';
                                }
                                $duzenleme=($randevu['durum']=== 'Onay Bekliyor.');
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($randevu['plaka']). ' ' .$randevu['marka'] ?></td>
                                    <td><?= htmlspecialchars($randevu['randevu_tarih']) ?></td>
                                    <td><?= htmlspecialchars($randevu['saat']) ?></td>
                                    <td><span class="badge <?php echo $durumClass; ?>"><?php echo htmlspecialchars($randevu['durum']); ?></span></td>
                                    <td>
                                        <?php if($duzenleme): ?>
                                            <a href="musteri_panel.php?randevu_duzenle= <?=  $randevu['id'] ?>" class="btn btn-sm btn-outline-warning"> Düzenle</a> <!--Duzenleme bolumu acilir -->
                                            <a href="musteri_panel.php?randevu_sil= <?=  $randevu['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                            onclick="return confirm('Bu randevuyu iptal etmek istediğinize emin misiniz?')"> Sil</a> <!--Silme onay penceresi -->
                                        <?php else: ?>
                                            <span class="text-muted small">Değiştirilemez</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                        <?php endforeach; ?>      
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?> <!--Ortak olarak kullanilabilecek footeri ekledik -->
