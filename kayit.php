<?php
//Musteri kayit olmak icin formu doldurur daha sonra PHP ile veriler dogrulanir

session_start();
require_once 'db.php'; //ortak veritabani baglantisini dosyaya ekliyoruz

//Kullanici giris yapmis ise otomatik olarak musteri paneline yönlendirir.
if(isset($_SESSION['kullanici_id'])){
    header('Location: musteri_panel.php');
    exit;
}

$mesaj=[];
$basari="";

//form gönderildi mi kontrol edecegiz
if($_SERVER["REQUEST_METHOD"]==="POST"){
    $ad_soyad=trim($_POST['ad_soyad'] ?? ''); //trim ile gereksiz bosluklari temizledik.
    $telefon=trim($_POST['telefon'] ?? '');
    $e_posta=trim($_POST['e_posta']?? '');
    $kullanici_adi=trim($_POST['kullanici_adi'] ?? '');
    $sifre=trim($_POST['sifre'] ?? '');
    $sifre2=trim($_POST['sifre2'] ?? '');

    //Girilen verilerin dogrulama kismi
    if (empty($ad_soyad)) 
        $mesaj[]='Ad soyad alanının doldurulması zorunludur.';

    // Telefon numarasının sadece rakamlardan oluştuğunu ve uzunluğunu kontrol eder
    if (empty($telefon) || !ctype_digit($telefon) || strlen($telefon) < 10 || strlen($telefon) > 11) {
    $mesaj[] = 'Telefon numarası geçersizdir. Başında sıfır olmadan 10 hane veya sıfırla 11 hane giriniz.';
    }

    if (!filter_var($e_posta, FILTER_VALIDATE_EMAIL))
        $mesaj[]='Geçerli bir e-posta adresi giriniz.';

    if (!filter_var($kullanici_adi, FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[a-zA-Z0-9_]{5,20}$/"]])) {
        $mesaj[]='Kullanıcı adı 5-20 karakter uzunluğunda olmalı ve sadece harf, rakam ve alt çizgi içermelidir.';
    }

    if(strlen($sifre) < 6)
        $mesaj[]='Şifre en az 6 karakterden oluşmalıdır.';

    if ($sifre !== $sifre2) {
        $mesaj[]='Şifreler eşleşmiyor.';
    }

    //E-posta kontrolu yaptik
    if(empty($mesaj)){
        $sorgu=$db->prepare("SELECT id FROM KULLANICI WHERE e_posta=?");
        $sorgu->execute([$e_posta]);
        if($sorgu->fetch()){
            $mesaj[]='Girdiğiniz e-posta adresi sistemde kayıtlıdır. Lütfen farklı bir e-posta adresi deneyin.';
        }
    }

    //Kullanıci adi kontrolu yaptik
    if(empty($mesaj)){
        $sorgu=$db->prepare("SELECT id FROM KULLANICI WHERE kullanici_adi=?");
        $sorgu->execute([$kullanici_adi]);
        if($sorgu->fetch()){
            $mesaj[]='Girdiğiniz kullanıcı adı sistemde kayıtlıdır. Lütfen farklı bir kullanıcı adı deneyin.';
        }
    }

    //Hata yoksa kayit olustur
    if(empty($mesaj)){
        $hashlanmis_sifre=password_hash($sifre, PASSWORD_DEFAULT); //Gelecekte de güvenli bir sifreleme icin PAASSWORD_DEFAULT kullanıyoruz.

        $sorgu=$db->prepare("INSERT INTO KULLANICI (ad_soyad, telefon, e_posta, kullanici_adi, sifre, rol) VALUES (?, ?, ?, ?, ?, 'Müşteri')");
        $sorgu->execute([$ad_soyad, $telefon, $e_posta, $kullanici_adi, $hashlanmis_sifre]);
        $basari= 'Kayıt başarılı! <a href="login.php">Giriş yapabilirsiniz.</a>';
    }
}

$pageTitle= 'Kayıt Ol';
include 'includes/header.php'; //ortak kullanilabilen header dosyasini ekledik
?>

<div class="row justify-content-center"> <!--grid sistemini başlatıp içeriği ortalamak için kullandık. -->
    <div class="col-md-6"> <!--form kutusu genişliği -->
        <div class="card shadow-sm border-0"> <!--kart görünümünü ayarladık. -->
            <div class="card-header bg-primary text-white text-center py-3">
                <h4 class="mb-0">Müşteri Kayıt Formu</h4> <!--açıklama bölümü -->
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

                <form method="POST" action="kayit.php">
                    <div class="mb-3">
                        <label for="ad_soyad" class="form-label">Ad Soyad *</label> 
                        <input type="text" class="form-control" name="ad_soyad" placeholder="Örn: Ali Yılmaz" value="<?= htmlspecialchars($_POST['ad_soyad'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="telefon" class="form-label">Telefon *</label>
                        <input type="tel" class="form-control" name="telefon" maxlength="11" placeholder="Örn: 5516234817" value="<?= htmlspecialchars($_POST['telefon'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="e_posta" class="form-label">E-Posta *</label>
                        <input type="email" class="form-control" name="e_posta" placeholder="Örn: ali@gmail.com" value="<?= htmlspecialchars($_POST['e_posta'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="kullanici_adi" class="form-label">Kullanıcı Adı *</label>
                        <input type="text" class="form-control" name="kullanici_adi" placeholder="Örn: aliyilmaz" value="<?= htmlspecialchars($_POST['kullanici_adi'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="sifre" class="form-label">Şifre *<small class="text-muted">(En az 6 karakter)</small></label>
                        <input type="password" class="form-control" id="sifre" name="sifre" placeholder="Örn: ......." required>
                    </div>

                    <div class="mb-3">
                        <label for="sifre2" class="form-label">Şifre Tekrar *</label>
                        <input type="password" class="form-control" name="sifre2" placeholder="Örn: ......." required>
                    </div>

                        <button type="submit" class="btn btn-primary w-100">Kayıt Ol</button>
                </form>

                <p class="text-center mt-3">
                    Zaten hesabınız var mı? <a href="login.php" class="text-decoration-none">Giriş yapın.</a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> <!--Ortak olarak kullanilabilecek footeri ekledik -->