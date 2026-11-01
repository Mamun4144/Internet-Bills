<?php
// config.php ফাইল অন্তর্ভুক্ত করা হচ্ছে যেখানে DB_HOST, DB_USER, DB_PASS, DB_NAME constant সংজ্ঞায়িত আছে
require_once 'config.php';

// Database ক্লাস - MySQL ডেটাবেজ সংযোগ এবং সমস্ত ডেটাবেজ অপারেশন পরিচালনা করে
// এই ক্লাস mysqli ব্যবহার করে ডেটাবেজের সাথে যোগাযোগ করে
class Database {
    // $conn - ডেটাবেজ সংযোগ object সংরক্ষণের জন্য private প্রপার্টি
    // শুধুমাত্র এই ক্লাসের ভেতর থেকে এটি ব্যবহার করা যাবে
    private ?mysqli $conn = null;

    // __construct() - Database ক্লাসের constructor
    // ক্লাসের নতুন instance তৈরি হওয়ার সময় স্বয়ংক্রিয়ভাবে চালু হয়
    // connect() দিয়ে ডেটাবেজে সংযোগ করে এবং createTables() দিয়ে টেবিল তৈরি করে
    public function __construct() {
        // প্রথমে ডেটাবেজে সংযোগ স্থাপন করা হয়
        $this->connect();
        // তারপর প্রয়োজনীয় টেবিলগুলো তৈরি করা হয় (যদি আগে তৈরি না হয়ে থাকে)
        $this->createTables();
    }

    // connect() - ডেটাবেজে সংযোগ স্থাপন করার private মেথড
    // config.php থেকে DB_HOST, DB_USER, DB_PASS, DB_NAME ব্যবহার করে mysqli সংযোগ তৈরি করে
    // সংযোগ ব্যর্থ হলে error message দেখিয়ে script বন্ধ করে দেয়
    private function connect() {
        // mysqli constructor দিয়ে ডেটাবেজ সংযোগ তৈরি করা হয়
        // DB_HOST: সার্ভারের ঠিকানা, DB_USER: ব্যবহারকারীর নাম, DB_PASS: পাসওয়ার্ড, DB_NAME: ডেটাবেজের নাম
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

        // সংযোগে কোনো error হলে তা পরীক্ষা করা হয়
        if ($this->conn->connect_error) {
            // সংযোগ ব্যর্থ হলে error message দেখিয়ে script সম্পূর্ণ বন্ধ করা হয়
            die("Database connection failed: " . $this->conn->connect_error);
        }

        // character encoding utf8mb4 সেট করা হয় যাতে বাংলাসহ সব ভাষার অক্ষর সঠিকভাবে সংরক্ষণ হয়
        $this->conn->set_charset("utf8mb4");
    }

    // createTables() - প্রয়োজনীয় ডেটাবেজ টেবিলগুলো তৈরি করার private মেথড
    // "CREATE TABLE IF NOT EXISTS" ব্যবহার করে, তাই টেবিল আগে থেকে থাকলে আবার তৈরি হবে না
    // প্রথমবার অ্যাপ চালানোর সময় এটি সমস্ত টেবিল এবং default admin user তৈরি করে
    private function createTables() {
        // একটি বড় SQL string এ সমস্ত CREATE TABLE statement রাখা হয়
        $sql = "
        CREATE TABLE IF NOT EXISTS `users` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `username` VARCHAR(50) UNIQUE NOT NULL,
          `password` VARCHAR(255) NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        );
        /* users টেবিল - লগইন করা ব্যবহারকারীদের তথ্য সংরক্ষণ করে
           id: অনন্য পরিচয় নম্বর (স্বয়ংক্রিয়ভাবে বাড়ে)
           username: ব্যবহারকারীর নাম (অনন্য হতে হবে)
           password: MD5 hashed পাসওয়ার্ড
           created_at: অ্যাকাউন্ট তৈরির তারিখ ও সময় */

        CREATE TABLE IF NOT EXISTS `companies` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `company_name` VARCHAR(255) UNIQUE NOT NULL,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `deleted_at` DATETIME NULL
        );
        /* companies টেবিল - কোম্পানির তথ্য সংরক্ষণ করে
           id: অনন্য পরিচয় নম্বর (স্বয়ংক্রিয়ভাবে বাড়ে)
           company_name: কোম্পানির নাম (অনন্য হতে হবে, একই নামে দুটি কোম্পানি থাকবে না)
           created_at: কোম্পানি যোগ করার তারিখ ও সময় */

        CREATE TABLE IF NOT EXISTS `invoices` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `company_name` VARCHAR(255) NOT NULL,
          `invoice_no` VARCHAR(100) UNIQUE NOT NULL,
          `bill_month` VARCHAR(20) NOT NULL,
          `bill_year` INT NOT NULL,
          `amount` DECIMAL(10,2) NOT NULL,
          `sms_count` INT DEFAULT 0,
          `hold_status` TINYINT DEFAULT 0,
          `hold_description` TEXT,
          `complete_status` TINYINT DEFAULT 0,
          `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
          `deleted_at` DATETIME NULL
        );
        /* invoices টেবিল - সমস্ত বিলের বিস্তারিত তথ্য সংরক্ষণ করে
           id: অনন্য পরিচয় নম্বর (স্বয়ংক্রিয়ভাবে বাড়ে)
           company_name: কোম্পানির নাম
           invoice_no: ইনভয়েস নম্বর (অনন্য হতে হবে)
           bill_month: বিলের মাস (যেমন: January)
           bill_year: বিলের বছর (যেমন: 2026)
           amount: বিলের পরিমাণ (সর্বোচ্চ 10 সংখ্যা, 2 দশমিক স্থান)
           sms_count: SMS এর সংখ্যা (ডিফল্ট 0)
           hold_status: hold অবস্থা (0=না, 1=হ্যাঁ, ডিফল্ট 0)
           hold_description: hold এর কারণের বিবরণ
           complete_status: সম্পন্ন অবস্থা (0=না, 1=হ্যাঁ, ডিফল্ট 0)
           created_at: বিল তৈরির তারিখ ও সময় */

        INSERT IGNORE INTO `users` (`username`, `password`) VALUES ('admin', MD5('admin123'));
        /* default admin user insert করা হয়
           INSERT IGNORE ব্যবহার করা হয় যাতে user আগে থেকে থাকলে error না হয়
           username: 'admin', password: 'admin123' (MD5 hash হিসেবে সংরক্ষিত) */
        ";

        // multi_query() ব্যবহার করা হয় কারণ $sql তে একাধিক SQL statement আছে
        // একবারে সব query চালানো হয়
        $this->conn->multi_query($sql);
    }

    // getConnection() - raw mysqli connection object ফেরত দেওয়ার public মেথড
    // যখন সরাসরি $conn object দরকার তখন এই মেথড ব্যবহার করা হয়
    public function getConnection() {
        return $this->conn;
    }

    // query() - সাধারণ SQL query চালানোর public মেথড
    // $sql: চালানোর জন্য SQL string
    // mysqli query() মেথড ব্যবহার করে ফলাফল (result object বা true/false) ফেরত দেয়
    public function query($sql) {
        return $this->conn->query($sql);
    }

    // prepare() - SQL injection থেকে সুরক্ষিত prepared statement তৈরির public মেথড
    // $sql: ? placeholder সহ SQL string
    // User input সংবলিত query চালানোর সময় সবসময় এটি ব্যবহার করা উচিত
    public function prepare($sql) {
        return $this->conn->prepare($sql);
    }

    // escape() - string কে SQL injection থেকে নিরাপদ করার public মেথড
    // $string: escape করার string
    // real_escape_string() ব্যবহার করে বিশেষ character গুলো escape করে নিরাপদ string ফেরত দেয়
    public function escape($string) {
        return $this->conn->real_escape_string($string);
    }

    // getLastId() - সর্বশেষ INSERT করা সারির auto-increment ID ফেরত দেওয়ার public মেথড
    // নতুন রেকর্ড যোগ করার পর সেই রেকর্ডের ID জানার জন্য এটি ব্যবহার করা হয়
    public function getLastId() {
        return $this->conn->insert_id;
    }
}

// Create global database instance
// Database ক্লাসের একটি global instance তৈরি করা হয় যা সমস্ত পেজে ব্যবহার করা যাবে
$db = new Database();
// $conn - raw mysqli connection object যা সরাসরি ব্যবহারের প্রয়োজন হলে কাজে লাগে
$conn = $db->getConnection();
?>
