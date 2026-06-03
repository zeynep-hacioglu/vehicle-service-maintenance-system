
-- 1. Tablo: KULLANICILAR
-- Hem musteri hem de servis personelleri rol ayrimi burada tutulacaktir.
CREATE TABLE IF NOT EXISTS KULLANICI(
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(100) NOT NULL,
    telefon VARCHAR(20) DEFAULT NULL,
    e_posta VARCHAR(100) NOT NULL UNIQUE,
    kullanici_adi VARCHAR(50) NOT NULL UNIQUE,
    sifre VARCHAR(255) NOT NULL, -- sifreyi hash fonksiyonu ile saklayacagımız için karakter sayisi en az 255 olmali
    rol ENUM('Müşteri', 'Personel') DEFAULT 'Müşteri', -- Kullanici tipi ayrimi (sadece biz yetki verirsek kisi sisteme personel olarak giris yapabilir)
    kayit_tarihi TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB; -- Tablolari birbirine baglayip; islemlerin güvenli olmasini, coklu kullanici esnasında cokmemesi icin kullanilan veritabi motoru

-- 2. Tablo: ARACLAR
-- arac_ekle.php dosyasında eklenecek aracları tutacak tablo
CREATE TABLE IF NOT EXISTS ARAC(
    id INT AUTO_INCREMENT PRIMARY KEY,
    plaka VARCHAR(20) NOT NULL UNIQUE,
    marka VARCHAR(50) NOT NULL,
    model VARCHAR(50) NOT NULL,
    olusturma_tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    yil INT NOT NULL,
    musteri_id INT NOT NULL,
    FOREIGN KEY (musteri_id) REFERENCES KULLANICI(id) ON DELETE CASCADE -- musteri silinirse aracinin da otomatik olarak silinmesi icin yapilir
) ENGINE=InnoDB;

-- 3. Tablo: RANDEVULAR
-- Musterinin olusturdugu, personelin (onay/ret) şeklinde durumunu guncelleyebilecegi tablo
CREATE TABLE IF NOT EXISTS RANDEVU(
    id INT AUTO_INCREMENT PRIMARY KEY,
    randevu_tarih DATETIME NOT NULL,
    saat TIME NOT NULL,
    notlar TEXT,
    durum ENUM('Onay Bekliyor.', 'Onaylandi.', 'Reddedildi.', 'Tamamlandi.') DEFAULT 'Onay Bekliyor.',
    olusturma_tarih TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    musteri_id INT NOT NULL,
    arac_id INT NOT NULL,
    FOREIGN KEY (musteri_id) REFERENCES KULLANICI(id) ON DELETE CASCADE,
    FOREIGN KEY (arac_id) REFERENCES ARAC(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 4. Tablo: BAKIM KAYITLARI
-- Servis girisi yapilip fatura dokumunun cikarilacagi tablo
CREATE TABLE IF NOT EXISTS BAKIM_KAYIT(
    bakim_id INT AUTO_INCREMENT PRIMARY KEY,
    yapilan_islem TEXT NOT NULL, -- yapilanlari anlatan uzun metinler icin
    degisen_parca TEXT DEFAULT NULL,
    toplam_ucret DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    islem_tarih DATE NOT NULL,
    notlar TEXT,
    olusturma TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    arac_id INT NOT NULL,
    randevu_id INT NOT NULL,
    personel_id INT NOT NULL,
    FOREIGN KEY (arac_id) REFERENCES ARAC(id) ON DELETE CASCADE,
    FOREIGN KEY (randevu_id) REFERENCES RANDEVU(id) ON DELETE CASCADE,
    FOREIGN KEY (personel_id) REFERENCES KULLANICI(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Test Verisi: 1 personel hesabı
-- Sifre: personel123 (hash: DEFAULT)

INSERT INTO KULLANICI (ad_soyad, telefon, e_posta, kullanici_adi, sifre, rol) VALUES ('Servis Yöneticisi', '5423187563', 'personel@servis.com', 'servis_yonetici', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Personel');