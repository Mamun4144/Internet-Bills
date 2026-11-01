<?php
// init.php লোড করা হচ্ছে — session চেক, database connection এবং সব common variable এখানে সেট হয়
require_once "includes/init.php";

// Browser tab-এ যে title দেখাবে সেটা সেট করা হচ্ছে
$pageTitle = "Complete Bills";

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

            <!-- এই বাটনে ক্লিক করলে loadCompleteBills() call হয়ে complete bills আবার লোড হবে -->
            <button class="refresh-btn" onclick="loadCompleteBills()"><i class="fas fa-sync-alt"></i> Refresh</button>

            <!-- Company Logo — ডান পাশে -->
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <!-- success/error message এখানে JavaScript দিয়ে দেখানো হয় -->
            <div id="messageContainer"></div>

            <!-- Complete Bills মূল section শুরু -->
            <div id="completeBills" class="section active">
                <h2><i class="fas fa-check-circle"></i> Complete Bills</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i> Showing only completed bills (not on hold)
                </p>

                <!-- ফিল্টার অংশ — search, মাস, বছর, কোম্পানি দিয়ে complete বিল খোঁজা যায় -->
                <div class="filter-section">

                    <!-- text লিখে বিল search করার box -->
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="completeSearchInput" placeholder="Search...">
                    </div>

                    <div class="filters">
                        <!-- Bill Month অনুযায়ী ফিল্টার — বিলের মাস -->
                        <select id="completeMonthFilter">
                            <option value="">All Bill Months</option>
                            <?php foreach($months as $m) echo "<option>$m</option>"; ?>
                        </select>

                        <!-- Entry Month অনুযায়ী ফিল্টার — সফটওয়্যারে entry করা মাস (created_at থেকে) -->
                        <select id="completeEntryMonthFilter">
                            <option value="">All Entry Months</option>
                        </select>

                        <!-- বছর অনুযায়ী ফিল্টার — JavaScript দিয়ে option populate হয় -->
                        <select id="completeYearFilter">
                            <option value="">All Years</option>
                        </select>

                        <!-- কোম্পানি অনুযায়ী ফিল্টার — JavaScript দিয়ে option populate হয় -->
                        <select id="completeCompanyFilter">
                            <option value="">All Companies</option>
                        </select>

                        <!-- সব ফিল্টার একসাথে clear করার বাটন -->
                        <button onclick="clearCompleteFilters()" class="btn-clear">
                            <i class="fas fa-times"></i> Clear
                        </button>

                        <!-- complete bills Excel file হিসেবে download করার বাটন -->
                        <button onclick="exportCompleteBillsToExcel()" class="btn-export">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>

                        <!-- নির্বাচিত বিলগুলোতে bulk action — select করলেই দেখা যাবে -->
                        <div id="completeBulkActions" style="display:none; gap:6px; align-items:center; flex-wrap:wrap;">
                            <span style="font-size:13px; font-weight:600; color:#4a6cf7;">
                                <i class="fas fa-check-square"></i> Selected: <span id="completeSelectedCount">0</span>
                            </span>
                            <button onclick="bulkCompleteAction('incomplete')" class="btn-complete" style="background:#ffc107; color:#333;">
                                <i class="fas fa-undo"></i> Mark Incomplete
                            </button>
                            <button onclick="bulkCompleteAction('edit')" class="btn-edit-bulk" style="background:#4a6cf7; color:#fff; border:none; padding:7px 14px; border-radius:6px; cursor:pointer; font-size:13px;">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            <button onclick="bulkCompleteAction('delete')" class="btn-delete" style="padding:7px 14px; border-radius:6px; font-size:13px;">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        </div>
                    </div>
                </div>

                <!-- complete বিলের তালিকা table আকারে এখানে দেখানো হবে
                     JavaScript-এর loadCompleteBills() function AJAX দিয়ে data এনে এই table-এ ভরে দেয় -->
                <div class="table-container">
                    <div id="completeBillsContainer">
                        <table id="completeBillsTable">
                            <tr>
                                <th style="width:36px;"><input type="checkbox" id="selectAllComplete" title="সব select/deselect করুন" onchange="toggleSelectAllComplete(this)"></th>
                                <th>ID</th>
                                <th>Company</th>
                                <th>Invoice</th>
                                <th>Month</th>
                                <th>Year</th>
                                <th>Amount</th>
                                <th>SMS</th>
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
            loadCompleteBills();     // complete bills AJAX দিয়ে লোড করে table-এ দেখায়
            loadCompaniesDropdown(); // কোম্পানি filter-এর dropdown populate করে
        };
    </script>
</body>
</html>
