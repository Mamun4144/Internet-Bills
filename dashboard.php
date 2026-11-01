<?php
// init.php লোড করা হচ্ছে - এটি সেশন চেক, ডেটাবেস কানেকশন এবং
// অন্যান্য প্রাথমিক সেটআপ একসাথে করে (config, functions সব এখানে লোড হয়)
require_once "includes/init.php";
// এই পেজের টাইটেল সেট করা হচ্ছে - header.php তে <title> ট্যাগে ব্যবহার হবে
$pageTitle = "Dashboard";
// সাইটের হেডার অংশ (HTML শুরু, CSS লিংক, নেভিগেশন) অন্তর্ভুক্ত করা হচ্ছে
include "includes/header.php";
?>
<body>
    <!-- সাইডবার মেনু অন্তর্ভুক্ত করা হচ্ছে - বাম পাশের নেভিগেশন মেনু -->
    <?php include "includes/sidebar.php"; ?>

    <!-- মূল কন্টেন্ট এলাকা - সাইডবারের পাশে প্রধান বিষয়বস্তু দেখাবে -->
    <div class="main-content">
        <!-- পেজের শীর্ষ অংশ - মেনু বাটন, স্বাগত বার্তা ও রিফ্রেশ বাটন -->
        <div class="header">
            <!-- সাইডবার দেখানো/লুকানোর বাটন - toggleSidebar() ফাংশন কল করে -->
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <!-- সেশন থেকে লগইন করা ব্যবহারকারীর নাম দেখানো হচ্ছে, XSS প্রতিরোধে htmlspecialchars ব্যবহার -->
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>
            <!-- রিফ্রেশ বাটন - ক্লিক করলে loadData() JavaScript ফাংশন চলবে -->
            <button class="refresh-btn" onclick="loadData()"><i class="fas fa-sync-alt"></i> Refresh</button>
            <!-- Dark Mode Toggle -->
            <button class="dark-mode-btn" onclick="toggleDarkMode()" id="darkModeBtn" title="Dark Mode"><i class="fas fa-moon"></i></button>

            <!-- Company Logo — ডান পাশে -->
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>
        <!-- কন্টেন্ট এলাকা - হোম সেকশন এখানে লোড হবে -->
        <div class="content">
            <!-- হোম পেজের মূল বিষয়বস্তু (সারাংশ কার্ড, সাম্প্রতিক বিল ইত্যাদি) অন্তর্ভুক্ত করা হচ্ছে -->
            <?php include "sections/home.php"; ?>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <!-- ড্যাশবোর্ডের প্রধান JavaScript ফাইল লোড করা হচ্ছে -->
    <script src="js/dashboard.js"></script>
    <script>
        // পেজ সম্পূর্ণ লোড হওয়ার পর এই ফাংশন স্বয়ংক্রিয়ভাবে চলবে
        window.onload = function() {
            // loadData() - AJAX এর মাধ্যমে ড্যাশবোর্ডের সব ডেটা লোড করার ফাংশন
            loadData();
        };
    </script>
</body>
</html>
