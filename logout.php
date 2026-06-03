<?php
session_start();
session_destroy(); //session verilerini ucurur
header("Location: login.php"); //giris sayfasına gonderir
exit;
?>