<?php

//E-posta ve sifre ile giris yapilmaya calisilir ve DB'de kontrol edilir

session_start();
require_once 'db.php'; //ortak veritabani baglantisini dosyaya ekliyoruz

//Kullanici giris yapmis ise role göre yönlendirme yapar.
if(isset($_SESSION['kullanici_id'])){
    header('Location: '. ($_SESSION['rol'] === 'Personel' ? 'personel_panel.php' : 'musteri_panel.php'));
    exit;
}

$mesaj='';


//form gönderildi mi kontrol edecegiz
if($_SERVER["REQUEST_METHOD"]==="POST"){

    $e_posta=trim($_POST['e_posta'] ?? '');
    $sifre=trim($_POST['sifre'] ?? '');
    
    //Kullanciyi e-posta adresi ile bulmaya calisiyoruz
    $sorgu = $db->prepare("SELECT * FROM KULLANICI WHERE e_posta=? LIMIT 1");
    $sorgu -> execute([$e_posta]);
    $kullanici = $sorgu -> fetch();

    //Kullanicinin bulunup bulunamadigini ve sifrenin dogru olup olmadıgını kontrol ediyoruz
    if ($kullanici && password_verify($sifre, $kullanici['sifre'])){
        
    //Session'u baslatip kullanici bilgilerini kaydedecegiz.
        session_regenerate_id(true); //Session fixation saldirisindan korunmak icin
        $_SESSION['kullanici_id']=$kullanici['id'];
        $_SESSION['ad_soyad']=$kullanici['ad_soyad'];
        $_SESSION['rol']=$kullanici['rol']; //Musteri veya personel

        //Role gore yonlendirme yapacagiz
        if ($kullanici['rol'] === 'Personel'){
            header('Location: personel_panel.php');
        } else {
            header('Location: musteri_panel.php');
        }
        exit;
    } else {
        $mesaj= 'E-posta veya şifre hatalı. Tekrar deneyiniz.'; //Genel hata mesajı
    }
}

$pageTitle='Giriş Yap';
include 'includes/header.php'; //ortak kullanilabilen header dosyasini ekledik
?>

<div class="login-middle-wrapper" style="
    background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('images/arkaplan_login.png');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    min-height: calc(100vh - 130px); /* Üst ve alt barların yüksekliğine göre burayı esnetiyoruz */
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 30px 15px;
    margin: 0;
">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                
                <div class="card shadow-lg border-0" style="
                    background: rgba(255, 255, 255, 0.92); 
                    backdrop-filter: blur(5px); /* Destekleyen tarayıcılarda hafif buzlu cam efekti verir */
                    border-radius: 12px;
                    overflow: hidden;
                ">
                    <div class="card-header bg-primary text-white text-center py-4 border-0">
                        <h4 class="mb-0 fw-bold">Araç Servis Sistemi</h4>
                        <small class="text-white-50">Kullanıcı Girişi</small>
                    </div>
                    
                    <div class="card-body p-4 p-sm-5">
                        <?php if($mesaj): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($mesaj) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="login.php">
                            <div class="mb-3">
                                <label for="e_posta" class="form-label fw-semibold">E-Posta Adresi</label>
                                <input type="email" class="form-control form-control-lg" name="e_posta" id="e_posta" placeholder="Örn: ali@gmail.com" value="<?= htmlspecialchars($_POST['e_posta'] ?? '') ?>" autofocus required style="font-size: 0.95rem;">
                            </div>

                            <div class="mb-4">
                                <label for="sifre" class="form-label fw-semibold">Şifre</label>
                                <input type="password" class="form-control form-control-lg" name="sifre" id="sifre" placeholder="••••••••" required style="font-size: 0.95rem;">
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" style="font-size: 1rem;">Giriş Yap</button>
                        </form>

                        <hr class="my-4 text-muted">

                        <p class="text-center mb-0 text-secondary">
                            Hesabın yok mu? <a href="kayit.php" class="text-decoration-none fw-semibold">Hemen Kayıt Ol</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> ```