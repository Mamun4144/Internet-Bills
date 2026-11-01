<?php
// PHP সেশন শুরু করা হচ্ছে - ব্যবহারকারীর লগইন তথ্য সেশনে সংরক্ষণের জন্য
session_start();
// ডেটাবেস কানেকশন ফাইল অন্তর্ভুক্ত করা হচ্ছে ($conn ভেরিয়েবল এখান থেকে আসে)
include "db.php";

// যদি ব্যবহারকারী আগে থেকেই লগইন করা থাকে (সেশনে 'user' সেট থাকলে)
if(isset($_SESSION['user'])) {
    // তাহলে সরাসরি ড্যাশবোর্ড পেজে পাঠিয়ে দাও
    header("Location: dashboard.php");
    // স্ক্রিপ্টের বাকি অংশ আর চালাবে না
    exit();
}

// এরর মেসেজ ধরার জন্য খালি ভেরিয়েবল তৈরি করা হচ্ছে
$error = "";
// যদি লগইন ফর্ম সাবমিট করা হয়ে থাকে (POST রিকোয়েস্টে 'login' বাটন থাকলে)
if(isset($_POST['login'])) {
    // ফর্ম থেকে ইউজারনেম নেওয়া হচ্ছে
    $username = $_POST['username'];
    // ফর্ম থেকে পাসওয়ার্ড নিয়ে MD5 হ্যাশ করা হচ্ছে (ডেটাবেসে যেভাবে সংরক্ষিত)
    $password = md5($_POST['password']);

    // ডেটাবেসে ইউজারনেম ও হ্যাশড পাসওয়ার্ড দিয়ে ব্যবহারকারী খোঁজা হচ্ছে
    $result = $conn->query("SELECT * FROM users WHERE username='$username' AND password='$password'");

    // যদি ডেটাবেসে মিলে যাওয়া কোনো রো পাওয়া যায় (লগইন সফল)
    if($result->num_rows > 0) {
        // সেশনে ইউজারনেম সেট করা হচ্ছে - এতে ব্যবহারকারী লগইন অবস্থায় থাকবে
        $_SESSION['user'] = $username;
        // ড্যাশবোর্ড পেজে পাঠিয়ে দেওয়া হচ্ছে
        header("Location: dashboard.php");
        // স্ক্রিপ্ট বন্ধ করা হচ্ছে
        exit();
    } else {
        // ইউজারনেম বা পাসওয়ার্ড ভুল হলে এরর মেসেজ সেট করা হচ্ছে
        $error = "Wrong username or password!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <!-- পেজের টাইটেল সেট করা হচ্ছে - ব্রাউজার ট্যাবে দেখাবে -->
    <title>Login - Billing System</title>
    <!-- প্রধান CSS স্টাইলশিট লোড করা হচ্ছে -->
    <link rel="stylesheet" href="style.css">
    <style>
        /* body এর ব্যাকগ্রাউন্ডে গ্রেডিয়েন্ট রঙ, এবং লগইন বক্সটিকে মাঝখানে রাখা হচ্ছে */
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            font-family: Arial;
            margin: 0;
        }
        /* লগইন বক্সের স্টাইল - সাদা ব্যাকগ্রাউন্ড, গোলাকার কোণা, ছায়া */
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        /* লোগো সেকশন - মাঝখানে এবং নিচে মার্জিন */
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        /* লোগো আইকনের আকার ও রঙ */
        .logo i {
            font-size: 50px;
            color: #667eea;
            margin-bottom: 15px;
        }
        /* প্রতিটি ইনপুট ফিল্ডের মোড়কের নিচের মার্জিন */
        .input-group {
            margin-bottom: 20px;
        }
        /* ইনপুট বক্সের পূর্ণ প্রস্থ, প্যাডিং, বর্ডার ও ট্রানজিশন অ্যানিমেশন */
        .input-group input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: 0.3s;
        }
        /* ইনপুটে ক্লিক করলে বর্ডারের রঙ পরিবর্তন হবে এবং outline সরে যাবে */
        .input-group input:focus {
            border-color: #667eea;
            outline: none;
        }
        /* লগইন বাটনের স্টাইল - পূর্ণ প্রস্থ, নীল ব্যাকগ্রাউন্ড, সাদা লেখা */
        .login-btn {
            width: 100%;
            padding: 15px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }
        /* লগইন বাটনের উপর মাউস গেলে গাঢ় নীল রঙ হবে */
        .login-btn:hover {
            background: #5a67d8;
        }
        /* এরর মেসেজ বক্সের স্টাইল - হালকা লাল ব্যাকগ্রাউন্ড, গাঢ় লাল লেখা */
        .error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        /* ডেমো ক্রেডেনশিয়াল বক্সের স্টাইল - ধূসর ব্যাকগ্রাউন্ড, ছোট ফন্ট */
        .demo-creds {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 14px;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <!-- লগইন ফর্মের মূল কন্টেইনার -->
    <div class="login-container">
        <!-- লোগো ও শিরোনাম অংশ -->
        <div class="logo">
            <!-- Font Awesome থেকে ইনভয়েস আইকন -->
            <i class="fas fa-file-invoice-dollar"></i>
            <h2>Billing System</h2>
            <p style="color: #666;">Manage your invoices easily</p>
        </div>

        <!-- যদি $error ভেরিয়েবলে কোনো মেসেজ থাকে তাহলে এরর বক্স দেখাও -->
        <?php if($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- লগইন ফর্ম - POST মেথডে একই পেজে সাবমিট হবে -->
        <form method="POST">
            <!-- ইউজারনেম ইনপুট ফিল্ড - খালি রাখা যাবে না (required) -->
            <div class="input-group">
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <!-- পাসওয়ার্ড ইনপুট ফিল্ড - টাইপ করলে গোপন থাকবে (password) -->
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <!-- সাবমিট বাটন - name="login" দিয়ে PHP-তে শনাক্ত করা হয় -->
            <button type="submit" name="login" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
        </form>

        <!-- ডেমো অ্যাকাউন্টের তথ্য দেখানো হচ্ছে পরীক্ষার জন্য -->
        <!--<div class="demo-creds">-->
        <!--    <i class="fas fa-info-circle"></i>-->
        <!--    Demo: admin / admin123-->
        <!--</div>-->
    </div>

    <!-- Font Awesome আইকন লাইব্রেরি লোড করা হচ্ছে CDN থেকে -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
</body>
</html>
