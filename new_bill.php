<?php
// init.php লোড করা হচ্ছে - এটি সেশন চেক, ডেটাবেস কানেকশন এবং
// অন্যান্য প্রাথমিক সেটআপ একসাথে করে (config, functions সব এখানে লোড হয়)
require_once "includes/init.php";
// এই পেজের টাইটেল সেট করা হচ্ছে - header.php তে <title> ট্যাগে ব্যবহার হবে
$pageTitle = "New Bill";
// সাইটের হেডার অংশ (HTML শুরু, CSS লিংক, নেভিগেশন) অন্তর্ভুক্ত করা হচ্ছে
include "includes/header.php";
?>
<body>
    <!-- সাইডবার মেনু অন্তর্ভুক্ত করা হচ্ছে - বাম পাশের নেভিগেশন মেনু -->
    <?php include "includes/sidebar.php"; ?>

    <!-- মূল কন্টেন্ট এলাকা - নতুন বিল এন্ট্রি ফর্ম এখানে থাকবে -->
    <div class="main-content">
        <!-- পেজের শীর্ষ অংশ - মেনু বাটন ও স্বাগত বার্তা -->
        <div class="header">
            <!-- সাইডবার দেখানো/লুকানোর বাটন - toggleSidebar() ফাংশন কল করে -->
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <!-- সেশন থেকে লগইন করা ব্যবহারকারীর নাম দেখানো হচ্ছে, XSS প্রতিরোধে htmlspecialchars ব্যবহার -->
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>
        <!-- কন্টেন্ট এলাকা - বিল এন্ট্রি ফর্ম ও বার্তা বক্স এখানে আসবে -->
        <div class="content">
            <!-- সফলতা বা ব্যর্থতার বার্তা দেখানোর জন্য খালি ডিভ - JavaScript দিয়ে পূরণ হবে -->
            <div id="messageContainer"></div>
            <!-- নতুন বিল এন্ট্রি ফর্মের সেকশন অন্তর্ভুক্ত করা হচ্ছে -->
            <?php include "sections/bill_entry.php"; ?>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <!-- ড্যাশবোর্ডের প্রধান JavaScript ফাইল লোড করা হচ্ছে -->
    <script src="js/dashboard.js"></script>
    <script>
        // পেজ সম্পূর্ণ লোড হওয়ার পর এই ফাংশন স্বয়ংক্রিয়ভাবে চলবে
        window.onload = function() {
            // loadData() - AJAX এর মাধ্যমে ড্যাশবোর্ডের সাধারণ ডেটা (সারাংশ) লোড করার ফাংশন
            loadData();
            // loadCompaniesDropdown() - বিল এন্ট্রি ফর্মের কোম্পানি ড্রপডাউন তালিকা লোড করার ফাংশন
            // AJAX দিয়ে কোম্পানির নাম এনে <select> ফিল্ড পূরণ করে
            loadCompaniesDropdown();
        };
    </script>
</body>
</html>
