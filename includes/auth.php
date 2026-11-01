<?php
// ====================================================
// includes/auth.php — Authentication Functions
// কাজ: user login চেক করার সহজ functions
// Note: includes/functions.php-এ আরো advanced version আছে
// ====================================================

// Session চালু করা হচ্ছে — $_SESSION variable access করতে হলে আগে এটা লাগে
session_start();

// ===== isLoggedIn() =====
// কাজ: user এখন logged in আছে কিনা চেক করে
// return: true (logged in) অথবা false (logged in না)
function isLoggedIn() {
    return isset($_SESSION['user']); // session-এ 'user' key থাকলে logged in
}

// ===== requireLogin() =====
// কাজ: যদি user logged in না থাকে তাহলে login page-এ পাঠিয়ে দেয়
// ব্যবহার: protected page-এর শুরুতে এই function call করতে হয়
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: ../index.php"); // এক ধাপ উপরে গিয়ে index.php-এ redirect
        exit();
    }
}

// ===== getUser() =====
// কাজ: বর্তমানে logged in user-এর নাম return করে
// return: username string অথবা null (যদি logged in না হয়)
function getUser() {
    return $_SESSION['user'] ?? null; // null coalescing — session না থাকলে null দেয়
}
?>
