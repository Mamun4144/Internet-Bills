<?php
// session চালু করা হচ্ছে — login করা user-এর তথ্য (যেমন username) browser বন্ধ না হওয়া পর্যন্ত মনে রাখার জন্য
session_start();

// চেক করা হচ্ছে user login করা আছে কিনা — $_SESSION['user'] না থাকলে মানে user login করেনি
if(!isset($_SESSION['user'])) {
    // login করা না থাকলে user-কে index.php (login page)-এ redirect করা হচ্ছে
    header("Location: index.php");
    // redirect-এর পর বাকি কোড যেন না চলে তাই script এখানেই বন্ধ করা হচ্ছে
    exit;
}

// database connection file (db.php) include করা হচ্ছে — এই file-এ MySQL connection থাকে
// __DIR__ মানে এই init.php যে folder-এ আছে সেই folder, তারপর এক ধাপ উপরে গিয়ে db.php লোড করা হচ্ছে
include __DIR__ . "/../db.php";

// বছরের ১২টি মাসের নাম একটি array-তে রাখা হচ্ছে — dropdown select box-এ ব্যবহার করার জন্য
$months = ['January','February','March','April','May','June','July','August','September','October','November','December'];

// বর্তমান বছর (যেমন: 2026) নেওয়া হচ্ছে — বছর select করার dropdown-এ ব্যবহার করার জন্য
$currentYear = date('Y');
