<?php
// ====================================================
// includes/config.php — Application Configuration
// কাজ: পুরো application-এর সব গুরুত্বপূর্ণ settings এক জায়গায়
// ====================================================

// ===== Database Settings =====
// Database সার্ভারের address (সাধারণত localhost)
define('DB_HOST', 'localhost');

// Database user নাম (XAMPP-এ default 'root')
define('DB_USER', 'root');

// Database পাসওয়ার্ড (XAMPP-এ default খালি)
define('DB_PASS', '');

// ব্যবহার করা database-এর নাম
define('DB_NAME', 'billing_system');

// ===== Application Settings =====
// Site-এর নাম (browser title-এ এবং বিভিন্ন জায়গায় ব্যবহার হয়)
define('SITE_NAME', 'Billing Soft');

// Site-এর পূর্ণ URL (links তৈরি করতে কাজে লাগে)
// define('SITE_URL', 'http://localhost/BillingSoft/');

// ===== Session Start =====
// PHP session চালু করা হচ্ছে — login তথ্য রাখার জন্য
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ===== Error Reporting =====
// Development-এ সব ধরনের error দেখানো হচ্ছে
// Production-এ এটি বন্ধ রাখা উচিত (error_reporting(0))
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
