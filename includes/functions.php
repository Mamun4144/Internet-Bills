<?php
// db.php ফাইল অন্তর্ভুক্ত করা হচ্ছে যেখানে Database ক্লাস এবং ডেটাবেজ সংযোগ সংজ্ঞায়িত আছে
require_once 'db.php';

// Functions ক্লাস - অ্যাপ্লিকেশনের সমস্ত ব্যবসায়িক লজিক এই ক্লাসে রাখা হয়েছে
// এই ক্লাসে লগইন, কোম্পানি, ইনভয়েস এবং ড্যাশবোর্ড সংক্রান্ত সমস্ত ফাংশন আছে
class Functions {
    // $db - Database ক্লাসের একটি instance সংরক্ষণ করে রাখার জন্য private প্রপার্টি
    private $db;

    // __construct() - ক্লাস তৈরি হওয়ার সময় স্বয়ংক্রিয়ভাবে চালু হয়
    // Database ক্লাসের নতুন instance তৈরি করে $db প্রপার্টিতে সংরক্ষণ করা হয়
    public function __construct() {
        $this->db = new Database();
    }

    // User Authentication - ব্যবহারকারীর পরিচয় যাচাইয়ের ফাংশনসমূহ

    // login() - ব্যবহারকারীর নাম ও পাসওয়ার্ড যাচাই করে লগইন করার ফাংশন
    // $username: ব্যবহারকারীর নাম, $password: পাসওয়ার্ড (plain text)
    // ডেটাবেজে MD5 হ্যাশ করা পাসওয়ার্ডের সাথে তুলনা করে ব্যবহারকারীর তথ্য ফেরত দেয়
    public function login($username, $password) {
        // SQL query: users টেবিলে username এবং MD5 hashed password দিয়ে ব্যবহারকারী খোঁজা হয়
        $sql = "SELECT * FROM users WHERE username = ? AND password = MD5(?)";
        // prepared statement তৈরি করা হয় SQL injection থেকে সুরক্ষার জন্য
        $stmt = $this->db->prepare($sql);
        // "ss" মানে দুটি string parameter - username এবং password বাঁধাই করা হয়
        $stmt->bind_param("ss", $username, $password);
        // query চালানো হয়
        $stmt->execute();
        // ফলাফল থেকে একটি associative array হিসেবে ডেটা ফেরত দেওয়া হয়
        return $stmt->get_result()->fetch_assoc();
    }

    // isLoggedIn() - ব্যবহারকারী লগইন করা আছে কিনা তা পরীক্ষা করার ফাংশন
    // $_SESSION['user_id'] সেট করা থাকলে true, না থাকলে false ফেরত দেয়
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    // requireLogin() - লগইন বাধ্যতামূলক করার ফাংশন
    // যদি ব্যবহারকারী লগইন না করা থাকে, তাহলে login পেজে redirect করে
    public function requireLogin() {
        // isLoggedIn() ফাংশন দিয়ে পরীক্ষা করা হয়, না থাকলে index.php তে পাঠানো হয়
        if (!$this->isLoggedIn()) {
            // লগইন না থাকলে index.php (login পেজ) এ redirect করা হয়
            header("Location: ../index.php");
            // redirect এর পর script চালানো বন্ধ করা হয়
            exit();
        }
    }

    // Company Functions - কোম্পানি সংক্রান্ত ফাংশনসমূহ

    // getCompanies() - ডেটাবেজ থেকে সমস্ত কোম্পানির তালিকা আনার ফাংশন
    // companies টেবিল থেকে নাম অনুযায়ী বর্ণানুক্রমে সাজানো তালিকা ফেরত দেয়
    public function getCompanies() {
        // companies টেবিল থেকে সমস্ত কোম্পানি কোম্পানির নাম অনুযায়ী সাজিয়ে আনা হয়
        $result = $this->db->query("SELECT * FROM companies ORDER BY company_name");
        // ফলাফল রাখার জন্য খালি array তৈরি করা হয়
        $companies = [];
        // প্রতিটি সারি পড়া হয় এবং $companies array তে যোগ করা হয়
        while ($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
        // সমস্ত কোম্পানির array ফেরত দেওয়া হয়
        return $companies;
    }

    // addCompany() - নতুন কোম্পানি ডেটাবেজে সংরক্ষণ করার ফাংশন
    // $name: নতুন কোম্পানির নাম (string)
    // সফল হলে true, ব্যর্থ হলে false ফেরত দেয়
    public function addCompany($name) {
        // companies টেবিলে নতুন কোম্পানির নাম যোগ করার SQL
        $sql = "INSERT INTO companies (company_name) VALUES (?)";
        // prepared statement তৈরি করা হয়
        $stmt = $this->db->prepare($sql);
        // "s" মানে একটি string parameter - কোম্পানির নাম বাঁধাই করা হয়
        $stmt->bind_param("s", $name);
        // query চালিয়ে ফলাফল (true/false) ফেরত দেওয়া হয়
        return $stmt->execute();
    }

    // Invoice Functions - ইনভয়েস/বিল সংক্রান্ত ফাংশনসমূহ

    // saveInvoice() - নতুন ইনভয়েস/বিল ডেটাবেজে সংরক্ষণ করার ফাংশন
    // $data: বিলের সমস্ত তথ্য সংবলিত associative array
    // সফল হলে true, ব্যর্থ হলে false ফেরত দেয়
    public function saveInvoice($data) {
        // invoices টেবিলে নতুন বিল সংরক্ষণের SQL
        // company_name, invoice_no, bill_month, bill_year, amount, sms_count ফিল্ডে ডেটা ঢোকানো হয়
        $sql = "INSERT INTO invoices (company_name, invoice_no, bill_month, bill_year, amount, sms_count)
                VALUES (?, ?, ?, ?, ?, ?)";
        // prepared statement তৈরি করা হয়
        $stmt = $this->db->prepare($sql);
        // "sssidi" মানে: s=string, s=string, s=string, i=integer, d=double/decimal, i=integer
        // $data array থেকে প্রতিটি ফিল্ডের মান বাঁধাই করা হয়
        $stmt->bind_param("sssidi",
            $data['company_name'],   // কোম্পানির নাম (string)
            $data['invoice_no'],     // ইনভয়েস নম্বর (string)
            $data['bill_month'],     // বিলের মাস (string)
            $data['bill_year'],      // বিলের বছর (integer)
            $data['amount'],         // বিলের পরিমাণ (decimal)
            $data['sms_count']       // SMS এর সংখ্যা (integer)
        );
        // query চালিয়ে ফলাফল (true/false) ফেরত দেওয়া হয়
        return $stmt->execute();
    }

    // getInvoices() - নির্দিষ্ট ধরনের ইনভয়েস/বিলের তালিকা আনার ফাংশন
    // $type: বিলের ধরন - 'pending' (বকেয়া), 'complete' (সম্পন্ন), 'hold' (আটকানো), অথবা অন্য যেকোনো (সব)
    // সর্বশেষ এন্ট্রি করা থেকে শুরু করে তালিকা ফেরত দেয় (id DESC)
    public function getInvoices($type = 'pending') {
        // মূল SQL query এর শুরু - WHERE শর্তটি $type অনুযায়ী নিচে যোগ করা হবে
        $sql = "SELECT * FROM invoices WHERE ";

        // $type এর মান অনুযায়ী WHERE শর্ত নির্ধারণ করা হয়
        switch($type) {
            // case 'pending' - বকেয়া বিল: complete_status = 0 এবং hold_status = 0
            // অর্থাৎ যে বিলগুলো সম্পন্নও হয়নি এবং hold এও নেই
            case 'pending':
                $sql .= "complete_status = 0 AND hold_status = 0";
                break;
            // case 'complete' - সম্পন্ন বিল: complete_status = 1 এবং hold_status = 0
            // অর্থাৎ যে বিলগুলো সম্পন্ন হয়েছে কিন্তু hold এ নেই
            case 'complete':
                $sql .= "complete_status = 1 AND hold_status = 0";
                break;
            // case 'hold' - আটকানো বিল: hold_status = 1
            // অর্থাৎ যে বিলগুলো hold এ আছে
            case 'hold':
                $sql .= "hold_status = 1";
                break;
            // default - অন্য যেকোনো ধরনের জন্য সমস্ত বিল দেখানো হয়
            // "1" মানে সবসময় সত্য, তাই সব সারি ফেরত আসবে
            default:
                $sql .= "1";
        }

        // সর্বশেষ এন্ট্রি থেকে পুরনো এন্ট্রির দিকে সাজানো হয় (id DESC)
        $sql .= " ORDER BY id DESC";

        // SQL query চালানো হয়
        $result = $this->db->query($sql);
        // ফলাফল রাখার জন্য খালি array তৈরি করা হয়
        $invoices = [];
        // প্রতিটি সারি পড়া হয় এবং $invoices array তে যোগ করা হয়
        while ($row = $result->fetch_assoc()) {
            $invoices[] = $row;
        }
        // সমস্ত বিলের array ফেরত দেওয়া হয়
        return $invoices;
    }

    // Dashboard Stats - ড্যাশবোর্ডের পরিসংখ্যান সংক্রান্ত ফাংশন

    // getDashboardStats() - ড্যাশবোর্ডে দেখানোর জন্য সমস্ত পরিসংখ্যান তথ্য সংগ্রহ করার ফাংশন
    // মোট বিল, মোট পরিমাণ, বকেয়া/সম্পন্ন/hold বিল এবং কোম্পানির সংখ্যা ফেরত দেয়
    public function getDashboardStats() {
        // $stats - সমস্ত পরিসংখ্যান তথ্য সংরক্ষণের জন্য খালি array
        $stats = [];

        // Total Bills - সমস্ত বিলের মোট সংখ্যা গণনা করা হয়
        // invoices টেবিলের সমস্ত সারি গণনা করা হয়
        $result = $this->db->query("SELECT COUNT(*) as total FROM invoices");
        // $stats['total_bills'] তে মোট বিলের সংখ্যা সংরক্ষণ করা হয়
        $stats['total_bills'] = $result->fetch_assoc()['total'];

        // Total Amount - সমস্ত বিলের মোট টাকার পরিমাণ যোগ করা হয়
        // COALESCE ব্যবহার করা হয় যাতে কোনো বিল না থাকলে NULL এর বদলে 0 দেখায়
        $result = $this->db->query("SELECT COALESCE(SUM(amount), 0) as total FROM invoices");
        // $stats['total_amount'] তে মোট পরিমাণ সংরক্ষণ করা হয়
        $stats['total_amount'] = $result->fetch_assoc()['total'];

        // Pending Bills - বকেয়া বিলের সংখ্যা গণনা করা হয়
        // complete_status = 0 এবং hold_status = 0 মানে বিলটি এখনো সম্পন্ন বা hold হয়নি
        $result = $this->db->query("SELECT COUNT(*) as total FROM invoices WHERE complete_status = 0 AND hold_status = 0");
        // $stats['pending_bills'] তে বকেয়া বিলের সংখ্যা সংরক্ষণ করা হয়
        $stats['pending_bills'] = $result->fetch_assoc()['total'];

        // Complete Bills - সম্পন্ন বিলের সংখ্যা গণনা করা হয়
        // complete_status = 1 মানে বিলটি সম্পন্ন হয়েছে
        $result = $this->db->query("SELECT COUNT(*) as total FROM invoices WHERE complete_status = 1");
        // $stats['complete_bills'] তে সম্পন্ন বিলের সংখ্যা সংরক্ষণ করা হয়
        $stats['complete_bills'] = $result->fetch_assoc()['total'];

        // Hold Bills - hold করা বিলের সংখ্যা গণনা করা হয়
        // hold_status = 1 মানে বিলটি hold এ আছে
        $result = $this->db->query("SELECT COUNT(*) as total FROM invoices WHERE hold_status = 1");
        // $stats['hold_bills'] তে hold বিলের সংখ্যা সংরক্ষণ করা হয়
        $stats['hold_bills'] = $result->fetch_assoc()['total'];

        // Companies Count - মোট কোম্পানির সংখ্যা গণনা করা হয়
        // companies টেবিলের সমস্ত সারি গণনা করা হয়
        $result = $this->db->query("SELECT COUNT(*) as total FROM companies");
        // $stats['companies'] তে মোট কোম্পানির সংখ্যা সংরক্ষণ করা হয়
        $stats['companies'] = $result->fetch_assoc()['total'];

        // সমস্ত পরিসংখ্যান তথ্য সংবলিত $stats array ফেরত দেওয়া হয়
        return $stats;
    }

    // Utility Functions - সহায়ক ফাংশনসমূহ

    // getMonths() - সমস্ত মাসের নামের array ফেরত দেওয়ার ফাংশন
    // bill_entry.php এ মাসের dropdown তৈরি করতে এই array ব্যবহার করা হয়
    public function getMonths() {
        return ['January','February','March','April','May','June','July','August','September','October','November','December'];
    }

    // formatCurrency() - টাকার পরিমাণকে সুন্দরভাবে ফরম্যাট করার ফাংশন
    // $amount: টাকার পরিমাণ (numeric)
    // টাকার চিহ্ন (৳) যোগ করে দুই দশমিক স্থান সহ ফরম্যাট করা string ফেরত দেয়
    // উদাহরণ: 1234.5 -> ৳1,234.50
    public function formatCurrency($amount) {
        // '৳' চিহ্ন এবং number_format() দিয়ে হাজার separator এবং দুই দশমিক স্থান সহ ফরম্যাট করা হয়
        return '৳' . number_format($amount, 2);
    }
}

// Create global functions instance
// Functions ক্লাসের একটি global instance তৈরি করা হয় যা সমস্ত পেজে ব্যবহার করা যাবে
$functions = new Functions();
?>
