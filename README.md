# vehicle-service-maintenance-system
##Araç Bakım ve Servis Sistemi
Bu proje, bir araç servis istasyonuna müşteriler tarafından sistem üzerinden araç randevu taleplerinin oluşturulabileceği, servis personeli tarafından randevuların onaylanabileceği ve servis bakımı sonrası yapılan işlemlerin sisteme girilip müşteriler tarafından görülebilecek şekilde geliştirilmiş olan web tabanlı bir otomasyon sistemidir.
---
##Proje Ekibi ve Görev Dağılımı
Projemiz, çakışmaları önlemek adına sayfa ve modül bazlı olarak iki gruba ayrılarak geliştirilmiştir.
**1. Geliştirici->Zeynep Hacıoğlu: Müşteri Modülleri (Kullanıcı Kayıt-Giriş-Çıkış İşlemleri, Araç Ekleme-Randevu Oluşturma Talepleri ve Müşteri Panel sayfalarını oluşturmuştur.)
**2. Geliştirici->Ayşe Şevval Çokaslan: Personel Modülleri (Araç Arama İşlemleri, Randevu Onay/Ret İşlemleri, Bakım Formu Girişi, Fatura Raporlama ve Personel Panel sayfalarını oluşturmuştur.)
---
##Proje Tanıtım Videosu
Yapmış olduğumuz otomasyon sisteminin hem müşteri hem de personel paneli akışlarını, veritabanı ilişkilerini ve canlı sunucu testlerini içeren proje tanıtım videosuna aşağıdaki bağlantıdan erişebilirsiniz:
**
---
##Uygulama Ekran Görüntüleri
###1. Müşteri Paneli
###2. Personel Yönetim Paneli
---
##Kullanılan Diller ve Teknolojiler
Bu projede, hem frontend hem de backend bölümleri sıfırdan oluşturularak yapılmıştır.
###Frontend Teknolojileri
**Bootstrap5: Formlar, tablolar, kartlar ve butonlar gibi bölümler Bootstrap bileşenleri ile şık bir görünüm yakalamıştır. Bootstrap kütüphanesinde bulunan esnek ızgara sistemi ile otomasyonun mobil, tablet ve masaüstü cihazlara otomatik uyum sağlaması sağlanmıştır.
**HTML: Web sayfalarının iskelet yapısını kurmak için kullanılmıştır.
**JavaScript: Tarayıcı tabanlı onaylama bölümleri için ve Bootstrap'in mobil uyumlu bazı menü ve listeleri için Bootstrap JS kütüphanesi projeye entegre edilmiştir. 
<br>
###Backend ve Veritabanı Teknolojileri
**Yalın PHP: Projenin tüm backend iş mantığı PHP kullanılarak kodlanmıştır. Dinamik sayfa yönetimleri, formlardan gelen veri filtrelemeleri, oturum kontrolleri ve veritabanı sorguları PHP ile oluşturulmuştur.
**MySQL: Veritabanı yönetim sistemi olarak kullanılmıştır.
**PDO Sürücüsü: PHP ile MySQL veritabanı arasında köprü kurmak amacıyla kullanılmıştır. 
---
##Teknik Özellikler
**Şifre Güvenliği: Kullanıcı sisteme kayıt olurken girdiği şifre BCRYPT algoritmasını tetikleyen password_hash() fonksiyonu ile 60 karakterli güçlü bir kriptografik özet oluşturarak veritabanına kaydedilir. Giriş esnasında ise kullanıcının girdiği şifre ile veritabanındaki hash'lanmiş şifre password_verify() fonksiyonu ile karşılaştırılarak güvenli bir giriş sağlanır.  
**Oturum Yönetimi ve Session Güvenliği: Giriş başarılı olduğunda sunucu taraflı bir oturum olan session_start() fonksiyonu başlatılarak oturum dosyası açılır ve her panel sayfasının başında oturum kontrolü yapılarak giriş yapmamış ziyaretçilerin sisteme erişmesi engelleniyor.
**SQL Injection Koruması: Tüm sorgularda PDO'nın hazırlanmış sorgular(Prepared Statements) özelliği kullanılmıştır.
---
