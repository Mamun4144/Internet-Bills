<?php
// ====================================================
// db.php — Database Connection & Table Setup (Root Level)
// কাজ: database connect করা এবং প্রয়োজনীয় table তৈরি করা
// Note: includes/db.php আলাদা class-based version, এটি simpler version
// ====================================================

// MySQL database-এ connect করা হচ্ছে
// localhost = সার্ভার, root = username, "" = password (XAMPP default), billing_system = database নাম
$conn = new mysqli("localhost", "root", "", "billing_system");

// Connection সফল না হলে error দেখিয়ে বন্ধ করা হচ্ছে
if ($conn->connect_error) {
    die("Database connection failed");
}

// ====================================================
// users টেবিল তৈরি (যদি আগে না থাকে)
// id: unique number, username: লগইন নাম (unique), password: হ্যাশ করা পাসওয়ার্ড
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE,
    password VARCHAR(255)
)");

// ====================================================
// invoices টেবিল তৈরি (যদি আগে না থাকে)
// প্রতিটি বিলের সব তথ্য এখানে সংরক্ষিত হয়
// hold_status: 1 = hold করা, 0 = normal
// complete_status: 1 = পরিশোধ হয়েছে, 0 = বাকি
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255),
    invoice_no VARCHAR(100) UNIQUE,
    bill_month VARCHAR(20),
    bill_year INT,
    amount DECIMAL(10,2),
    sms_count INT DEFAULT 0,
    hold_status TINYINT DEFAULT 0,
    hold_description TEXT,
    complete_status TINYINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
)");

// পুরনো database-এ hold ও complete column না থাকলে যোগ করা হচ্ছে
// IF NOT EXISTS — column আগে থেকে থাকলে error হবে না
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS hold_status TINYINT DEFAULT 0");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS hold_description TEXT");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS hold_date DATETIME NULL");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS complete_status TINYINT DEFAULT 0");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS complete_date DATETIME NULL");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");
$conn->query("ALTER TABLE companies ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");
$conn->query("ALTER TABLE invoices ADD COLUMN IF NOT EXISTS deleted_at DATETIME NULL");

// ====================================================
// companies টেবিল তৈরি (যদি আগে না থাকে)
// সব কোম্পানির নাম এখানে সংরক্ষিত থাকে
// ====================================================
$conn->query("CREATE TABLE IF NOT EXISTS companies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(255) UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at DATETIME NULL
)");

// bill_pdfs টেবিল তৈরি — PDF ফাইলের তথ্য সংরক্ষণের জন্য
$conn->query("CREATE TABLE IF NOT EXISTS bill_pdfs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    company_name VARCHAR(255),
    description TEXT,
    file_name VARCHAR(255) NOT NULL,
    file_size INT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// ডিফল্ট admin user তৈরি করা হচ্ছে — শুধুমাত্র যদি কোনো user না থাকে
// MD5('admin123') — পাসওয়ার্ড MD5 দিয়ে hash করা হচ্ছে
$checkUser = $conn->query("SELECT COUNT(*) as count FROM users");
$row = $checkUser->fetch_assoc();
if($row['count'] == 0){
    $conn->query("INSERT INTO users (username, password)
                  VALUES ('admin', MD5('admin123'))");
}
?>
