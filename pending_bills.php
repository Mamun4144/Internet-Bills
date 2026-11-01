<?php
// init.php লোড করা হচ্ছে — session চেক, database connection এবং সব common variable এখানে সেট হয়
require_once "includes/init.php";

// Browser tab-এ যে title দেখাবে সেটা সেট করা হচ্ছে
$pageTitle = "Pending Bills";

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

            <!-- এই বাটনে ক্লিক করলে loadBills() function call হয়ে pending bills আবার লোড হবে -->
            <button class="refresh-btn" onclick="loadBills()"><i class="fas fa-sync-alt"></i> Refresh</button>

            <!-- Company Logo — ডান পাশে -->
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <!-- success/error message এখানে JavaScript দিয়ে দেখানো হয় -->
            <div id="messageContainer"></div>

            <!-- Pending Bills মূল section শুরু -->
            <div id="checkBill" class="section active">
                <h2><i class="fas fa-list-alt"></i> Pending Bills</h2>
                <p style="color: #666; margin-bottom: 20px;">
                    <i class="fas fa-info-circle"></i> Showing only pending bills (not completed or on hold)
                </p>

                <!-- ফিল্টার অংশ — search, মাস, বছর, কোম্পানি দিয়ে বিল খোঁজা যায় -->
                <div class="filter-section">

                    <!-- text লিখে বিল search করার box -->
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Search...">
                    </div>

                    <div class="filters">
                        <!-- মাস অনুযায়ী ফিল্টার করার dropdown
                             PHP দিয়ে $months array থেকে option গুলো তৈরি হচ্ছে -->
                        <select id="monthFilter">
                            <option value="">All Months</option>
                            <?php foreach($months as $m) echo "<option>$m</option>"; ?>
                        </select>

                        <!-- বছর অনুযায়ী ফিল্টার — JavaScript দিয়ে option populate হয় -->
                        <select id="yearFilter">
                            <option value="">All Years</option>
                        </select>

                        <!-- কোম্পানি অনুযায়ী ফিল্টার — JavaScript দিয়ে option populate হয় -->
                        <select id="companyFilter">
                            <option value="">All Companies</option>
                        </select>

                        <!-- সব ফিল্টার একসাথে clear করার বাটন -->
                        <button onclick="clearFilters()" class="btn-clear">
                            <i class="fas fa-times"></i> Clear
                        </button>

                        <!-- সব pending bill একসাথে complete করে দেওয়ার বাটন -->
                        <button onclick="markAllComplete()" class="btn-complete">
                            <i class="fas fa-check-circle"></i> Mark All Complete
                        </button>

                        <!-- নির্বাচিত (checked) বিলগুলো complete করার বাটন -->
                        <button onclick="markSelectedComplete()" class="btn-complete" id="markSelectedBtn" style="display:none; background: #17a2b8;">
                            <i class="fas fa-check-double"></i> Mark Selected Complete (<span id="selectedCount">0</span>)
                        </button>

                        <!-- নির্বাচিত বিলগুলো একসাথে hold করার বাটন -->
                        <button onclick="openGroupHoldModal()" class="btn-complete" id="groupHoldBtn" style="display:none; background: #e67e22;">
                            <i class="fas fa-pause-circle"></i> Hold Selected (<span id="holdSelectedCount">0</span>)
                        </button>

                        <!-- pending bills Excel file হিসেবে download করার বাটন -->
                        <button onclick="exportToExcel()" class="btn-export">
                            <i class="fas fa-file-excel"></i> Export to Excel
                        </button>
                    </div>
                </div>

                <!-- বিলের তালিকা table আকারে এখানে দেখানো হবে
                     JavaScript-এর loadBills() function AJAX দিয়ে data এনে এই table-এ ভরে দেয় -->
                <div class="table-container">
                    <div id="allBillsContainer">
                        <table id="billsTable">
                            <tr>
                                <th style="width:36px;"><input type="checkbox" id="selectAllBills" title="সব select/deselect করুন" onchange="toggleSelectAll(this)"></th>
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

    <!-- Group Hold Modal — একাধিক বিল একসাথে hold করার popup -->
    <div id="groupHoldModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-pause-circle"></i> Group Hold — <span id="groupHoldCountLabel">0</span>টি বিল</h3>
                <span class="close" onclick="closeModal('groupHoldModal')">&times;</span>
            </div>
            <form onsubmit="submitGroupHold(event)">
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Hold Reason (সবার জন্য একই কারণ)</label>
                        <textarea id="groupHoldDescription" rows="4"
                            placeholder="Enter the reason for holding these bills..."
                            required
                            style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:8px; font-family:inherit; resize:vertical;"></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('groupHoldModal')">Cancel</button>
                    <button type="submit" class="btn-update" style="background: var(--warning);">
                        <i class="fas fa-pause"></i> Hold All Selected
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <!-- সব JavaScript functions এই file-এ আছে -->
    <script src="js/dashboard.js"></script>
    <script>
        // পেজ সম্পূর্ণ লোড হলে এই তিনটি function চালু হবে:
        window.onload = function() {
            loadData();             // সাধারণ dashboard data লোড করে
            loadBills();            // pending bills AJAX দিয়ে লোড করে table-এ দেখায়
            loadCompaniesDropdown(); // কোম্পানি filter-এর dropdown populate করে
        };
    </script>
</body>
</html>
