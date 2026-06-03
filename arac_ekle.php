<?php
//Arac eklemek icin aracın plakası, markası, modeli ve yilinin girildigi sistem

session_start();
require_once 'db.php'; //ortak veritabani baglantisini dosyaya ekliyoruz

//Kullanici giris yapmis ise otomatik olarak musteri paneline yönlendirir.
if(!isset($_SESSION['kullanici_id'])|| $_SESSION['rol'] !== 'Müşteri'){
    header('Location: login.php');
    exit;
}

$musteriId=$_SESSION['kullanici_id']; //Giriis yapmis olan kullanicinin id'sini aldik
$mesaj=[];
$basari="";

//form gönderildi mi kontrol edecegiz
if($_SERVER["REQUEST_METHOD"]==="POST"){
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
        $sorgu=$db->prepare("SELECT id FROM ARAC WHERE plaka=?");
        $sorgu->execute([$plaka]);
        if($sorgu->fetch()){
            $mesaj[]="Girdiğiniz ($plaka) plaka sistemde kayıtlıdır.";
        }
    }

    //Hata yoksa kayit olustur
    if(empty($mesaj)){
        $sorgu=$db->prepare("INSERT INTO ARAC (musteri_id, plaka, marka, model, yil) VALUES (?, ?, ?, ?, ?)");
        $sorgu->execute([$musteriId, $plaka, $marka, $model, $yil]);
        $basari= "($plaka) plakalı araç başarılı bir şekilde eklendi!". '<a href="musteri_panel.php"> Panele dön </a> veya yeni araç ekle.';

        $_POST=[]; //Formu temizliyoruz.
    }
}

$pageTitle='Araç Ekle';
include 'includes/header.php'; //ortak kullanilabilen header dosyasini ekledik
?>

<div class="row justify-content-center"> <!--grid sistemini başlatıp içeriği ortalamak için kullandık. -->
    <div class="col-md-6"> <!--form kutusu genişliği -->
        <div class="card shadow-sm border-0"> <!--kart görünümünü ayarladık. -->
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Araç Ekle</h4> <!--açıklama bölümü -->
            </div>
            
            <div class="card-body p-4">
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

                <form method="POST" action="arac_ekle.php">
                    <div class="mb-3">
                    <label for="plaka" class="form-label">Plaka *</label> 
                    <input type="text" 
                        class="form-control plaka-input" 
                        name="plaka" 
                        placeholder="Örn: 16ZHS016" 
                        value="<?= htmlspecialchars($_POST['plaka'] ?? '') ?>" 
                        maxlength="15" 
                        oninput="this.value = this.value.toUpperCase();" 
                        required>
                        <div class="form-text">Otomatik büyük harfe dönüştürülür.</div>
                    </div>

<style>
.plaka-input {
    text-transform: uppercase; /* Kullanıcının yazdığı harfleri büyük yapar */
}
.plaka-input::placeholder {
    text-transform: none; /* Placeholder'ın bu durumdan etkilenmesini engeller */
}
</style>

                    <div class="row">
                        <div class="mb-3">
                            <label for="marka" class="form-label">Marka *</label>
                            <input type="text" class="form-control" name="marka" placeholder="Örn: BMW, Volkswagen..." value="<?= htmlspecialchars($_POST['marka'] ?? '') ?>" required>
                        </div>
                    
                        <div class="mb-3">
                            <label for="model" class="form-label">Model *</label>
                            <input type="text" class="form-control" name="model" placeholder="Örn: i serisi, Golf..." value="<?= htmlspecialchars($_POST['model'] ?? '') ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="yil" class="form-label">Yıl *</label>
                        <input type="number" class="form-control" name="yil" min="1900" max="<?= date('Y') + 1 ?>" placeholder="Örn: 2018" value="<?= htmlspecialchars($_POST['yil'] ?? '') ?>" required>
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary w-100 mb-2">Araç Ekle</button>
                        <a href="musteri_panel.php" class="btn btn-outline-secondary w-100 mb-2">İptal</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> <!--Ortak olarak kullanilabilecek footeri ekledik -->