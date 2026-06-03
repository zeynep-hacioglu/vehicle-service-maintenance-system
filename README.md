# vehicle-service-maintenance-system
<h2>Araç Bakım ve Servis Sistemi</h2>
Bu proje, bir araç servis istasyonuna müşteriler tarafından sistem üzerinden araç randevu taleplerinin oluşturulabileceği, servis personeli tarafından randevuların onaylanabileceği ve servis bakımı sonrası yapılan işlemlerin sisteme girilip müşteriler tarafından görülebilecek şekilde geliştirilmiş olan web tabanlı bir otomasyon sistemidir.
<br>

<h2>Proje Ekibi ve Görev Dağılımı</h2>
Projemiz, çakışmaları önlemek adına sayfa ve modül bazlı olarak iki gruba ayrılarak geliştirilmiştir.
<br><br>
<li>1. Geliştirici->Zeynep Hacıoğlu: Müşteri Modülleri (Kullanıcı Kayıt-Giriş-Çıkış İşlemleri, Araç Ekleme-Randevu Oluşturma Talepleri ve Müşteri Panel sayfalarını oluşturmuştur.)</li> <br>
<li>2. Geliştirici->Ayşe Şevval Çokaslan: Personel Modülleri (Araç Arama İşlemleri, Randevu Onay/Ret İşlemleri, Bakım Formu Girişi, Fatura Raporlama ve Personel Panel sayfalarını oluşturmuştur.)</li>
<br>

<h2>Proje Tanıtım Videosu</h2>
Yapmış olduğumuz otomasyon sisteminin hem müşteri hem de personel paneli akışlarını, veritabanı ilişkilerini ve canlı sunucu testlerini içeren proje tanıtım videosuna aşağıdaki bağlantıdan erişebilirsiniz:
<br><br>
<li></li>
<br>

<h2>Uygulama Ekran Görüntüleri</h2>
<li><b>1. Müşteri Paneli</b></li><br>
<img width="1600" height="755" alt="musteri" src="https://github.com/user-attachments/assets/58da1178-7e7b-4272-a52b-5434cf5dde90" />
<br><br>
<li><b>2. Personel Yönetim Paneli</b></li><br>
<img width="1600" height="759" alt="personel" src="https://github.com/user-attachments/assets/ba44932b-abb6-4a0e-9388-bfc7932c42f9" />
<br><br>

<h2>Kullanılan Diller ve Teknolojiler</h2>
Bu projede, hem frontend hem de backend bölümleri sıfırdan oluşturularak yapılmıştır.

<h4>Frontend Teknolojileri</h4>
<li><b>Bootstrap5:</b> Formlar, tablolar, kartlar ve butonlar gibi bölümler Bootstrap bileşenleri ile şık bir görünüm yakalamıştır. Bootstrap kütüphanesinde bulunan esnek ızgara sistemi ile otomasyonun mobil, tablet ve masaüstü cihazlara otomatik uyum sağlaması sağlanmıştır.</li> <br>
<li><b>HTML:</b> Web sayfalarının iskelet yapısını kurmak için kullanılmıştır.</li> <br>
<li><b>JavaScript:</b> Tarayıcı tabanlı onaylama bölümleri için ve Bootstrap'in mobil uyumlu bazı menü ve listeleri için Bootstrap JS kütüphanesi projeye entegre edilmiştir. </li> <br>

<h4>Backend ve Veritabanı Teknolojileri</h4>
<li><b>Yalın PHP:</b> Projenin tüm backend iş mantığı PHP kullanılarak kodlanmıştır. Dinamik sayfa yönetimleri, formlardan gelen veri filtrelemeleri, oturum kontrolleri ve veritabanı sorguları PHP ile oluşturulmuştur.</li> <br>
<li><b>MySQL:</b> Veritabanı yönetim sistemi olarak kullanılmıştır. </li> <br>
<li><b>PDO Sürücüsü:</b> PHP ile MySQL veritabanı arasında köprü kurmak amacıyla kullanılmıştır. </li> <br>

<h2>Teknik Özellikler</h2>
<li><b>Şifre Güvenliği:</b> Kullanıcı sisteme kayıt olurken girdiği şifre BCRYPT algoritmasını tetikleyen password_hash() fonksiyonu ile 60 karakterli güçlü bir kriptografik özet oluşturarak veritabanına kaydedilir. Giriş esnasında ise kullanıcının girdiği şifre ile veritabanındaki hash'lanmiş şifre password_verify() fonksiyonu ile karşılaştırılarak güvenli bir giriş sağlanır.  </li> <br>
<li><b>Oturum Yönetimi ve Session Güvenliği:</b> Giriş başarılı olduğunda sunucu taraflı bir oturum olan session_start() fonksiyonu başlatılarak oturum dosyası açılır ve her panel sayfasının başında oturum kontrolü yapılarak giriş yapmamış ziyaretçilerin sisteme erişmesi engelleniyor. </li> <br>
<li><b>SQL Injection Koruması:</b> Tüm sorgularda PDO'nın hazırlanmış sorgular(Prepared Statements) özelliği kullanılmıştır. </li>

