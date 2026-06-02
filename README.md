# vehicle-service-maintenance-system
<h2>Araç Bakım ve Servis Sistemi</h2>
Bu proje, bir araç servis istasyonuna müşteriler tarafından sistem üzerinden araç randevu taleplerinin oluşturulabileceği, servis personeli tarafından randevuların onaylanabileceği ve servis bakımı sonrası yapılan işlemlerin sisteme girilip müşteriler tarafından görülebilecek şekilde geliştirilmiş olan web tabanlı bir otomasyon sistemidir.
<br>
<h2>Proje Ekibi ve Görev Dağılımı</h2>
Projemiz, çakışmaları önlemek adına sayfa ve modül bazlı olarak iki gruba ayrılarak geliştirilmiştir.<br>
<ul>1. Geliştirici->Zeynep Hacıoğlu: Müşteri Modülleri (Kullanıcı Kayıt-Giriş-Çıkış İşlemleri, Araç Ekleme-Randevu Oluşturma Talepleri ve Müşteri Panel sayfalarını oluşturmuştur.)</ul> <br>
<ul>2. Geliştirici->Ayşe Şevval Çokaslan: Personel Modülleri (Araç Arama İşlemleri, Randevu Onay/Ret İşlemleri, Bakım Formu Girişi, Fatura Raporlama ve Personel Panel sayfalarını oluşturmuştur.)</ul>
<br>
<h2>Proje Tanıtım Videosu</h2>
Yapmış olduğumuz otomasyon sisteminin hem müşteri hem de personel paneli akışlarını, veritabanı ilişkilerini ve canlı sunucu testlerini içeren proje tanıtım videosuna aşağıdaki bağlantıdan erişebilirsiniz:
<ul></ul>
<br>
<h2>Uygulama Ekran Görüntüleri</h2>
<ul>1. Müşteri Paneli</ul>
<ul>2. Personel Yönetim Paneli</ul>
<br>
<h2>Kullanılan Diller ve Teknolojiler</h2>
Bu projede, hem frontend hem de backend bölümleri sıfırdan oluşturularak yapılmıştır.
<h4>Frontend Teknolojileri</h4>
<ul>Bootstrap5: Formlar, tablolar, kartlar ve butonlar gibi bölümler Bootstrap bileşenleri ile şık bir görünüm yakalamıştır. Bootstrap kütüphanesinde bulunan esnek ızgara sistemi ile otomasyonun mobil, tablet ve masaüstü cihazlara otomatik uyum sağlaması sağlanmıştır.</ul> <br>
<ul>HTML: Web sayfalarının iskelet yapısını kurmak için kullanılmıştır.</ul> <br>
<ul>JavaScript: Tarayıcı tabanlı onaylama bölümleri için ve Bootstrap'in mobil uyumlu bazı menü ve listeleri için Bootstrap JS kütüphanesi projeye entegre edilmiştir. </ul> <br>
<br>
<h4>Backend ve Veritabanı Teknolojileri</h4>
<ul>Yalın PHP: Projenin tüm backend iş mantığı PHP kullanılarak kodlanmıştır. Dinamik sayfa yönetimleri, formlardan gelen veri filtrelemeleri, oturum kontrolleri ve veritabanı sorguları PHP ile oluşturulmuştur.</ul> <br>
<ul>MySQL: Veritabanı yönetim sistemi olarak kullanılmıştır. </ul> <br>
<ul>PDO Sürücüsü: PHP ile MySQL veritabanı arasında köprü kurmak amacıyla kullanılmıştır. </ul> <br>
<br>
<h2>Teknik Özellikler</h2>
<ul>Şifre Güvenliği: Kullanıcı sisteme kayıt olurken girdiği şifre BCRYPT algoritmasını tetikleyen password_hash() fonksiyonu ile 60 karakterli güçlü bir kriptografik özet oluşturarak veritabanına kaydedilir. Giriş esnasında ise kullanıcının girdiği şifre ile veritabanındaki hash'lanmiş şifre password_verify() fonksiyonu ile karşılaştırılarak güvenli bir giriş sağlanır.  </ul> <br>
<ul>Oturum Yönetimi ve Session Güvenliği: Giriş başarılı olduğunda sunucu taraflı bir oturum olan session_start() fonksiyonu başlatılarak oturum dosyası açılır ve her panel sayfasının başında oturum kontrolü yapılarak giriş yapmamış ziyaretçilerin sisteme erişmesi engelleniyor. </ul> <br>
<ul>SQL Injection Koruması: Tüm sorgularda PDO'nın hazırlanmış sorgular(Prepared Statements) özelliği kullanılmıştır. </ul>

