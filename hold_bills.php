<?php
// init.php লোড করা হচ্ছে — session চেক, database connection এবং সব common variable এখানে সেট হয়
require_once "includes/init.php";

// Browser tab-এ যে title দেখাবে সেটা সেট করা হচ্ছে
$pageTitle = "Hold Bills";

// HTML <head> অংশ লোড করা হচ্ছে — CSS, meta tag, title এখানে আছে
include "includes/header.php";
?>
<body>
    <?php
    // বাম পাশের navigation menu (sidebar) লোড করা হচ্ছে
    include "includes/sidebar.php";
    ?>

    <!-- মূল content area শুরু -->
    <div class="main-content">

        <!-- উপরের header bar — menu button, username এবং refresh button -->
        <div class="header">
            <!-- sidebar খোলা/বন্ধ করার বাটন -->
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

            <!-- লগইন করা user-এর নাম দেখাচ্ছে, XSS থেকে রক্ষা করতে htmlspecialchars() ব্যবহার করা হয়েছে -->
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>

            <!-- এই বাটনে ক্লিক করলে loadHoldBills() call হয়ে hold bills আবার লোড হবে -->
            <button class="refresh-btn" onclick="loadHoldBills()"><i class="fas fa-sync-alt"></i> Refresh</button>
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <!-- success/error message এখানে JavaScript দিয়ে দেখানো হয় -->
            <div id="messageContainer"></div>

            <!-- Hold Bills মূল section শুরু -->
            <div id="holdBills" class="section active">
                <h2><i class="fas fa-pause-circle"></i> Hold Bills</h2>

                <!-- hold করা বিলের তালিকা table আকারে এখানে দেখানো হবে
                     JavaScript-এর loadHoldBills() function AJAX দিয়ে data এনে এই table-এ ভরে দেয়
                     অন্য bills-এর তুলনায় এখানে অতিরিক্ত "Hold Reason" column আছে -->
                <div class="table-container">
                    <div id="holdBillsContainer">
                        <table id="holdBillsTable">
                            <tr>
                                <th>ID</th>
                                <th>Company</th>
                                <th>Invoice</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Amount</th>
                                <th>SMS</th>
                                <th>Hold Reason</th> <!-- কেন বিলটি hold করা হয়েছে তার কারণ -->
                                <th>Actions</th>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
    // popup modal গুলো লোড করা হচ্ছে — bill edit, delete confirm ইত্যাদি
    include "includes/modals.php";
    ?>

    <?php include "includes/footer.php"; ?>

    <!-- সব JavaScript functions এই file-এ আছে -->
    <script src="js/dashboard.js"></script>
    <script>
        // পেজ সম্পূর্ণ লোড হলে এই তিনটি function চালু হবে:
        window.onload = function() {
            loadData();              // সাধারণ dashboard data লোড করে
            loadHoldBills();         // hold bills AJAX দিয়ে লোড করে table-এ দেখায়
            loadCompaniesDropdown(); // কোম্পানি filter-এর dropdown populate করে
        };
    </script>
</body>
</html>
