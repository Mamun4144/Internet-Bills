<?php
// init.php লোড করা হচ্ছে — session চেক, database connection এবং সব common variable এখানে সেট হয়
require_once "includes/init.php";

// Browser tab-এ যে title দেখাবে সেটা সেট করা হচ্ছে
$pageTitle = "Date Range Report";

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

        <!-- উপরের header bar — menu button এবং username -->
        <div class="header">
            <!-- sidebar খোলা/বন্ধ করার বাটন -->
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>

            <!-- লগইন করা user-এর নাম দেখাচ্ছে, XSS থেকে রক্ষা করতে htmlspecialchars() ব্যবহার করা হয়েছে -->
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <!-- Date Range Report মূল section শুরু -->
            <div id="dateRangeReport" class="section active">
                <h2><i class="fas fa-calendar-alt"></i> Date Range Report</h2>

                <!-- তারিখ range ও কোম্পানি সিলেক্ট করার form -->
                <div class="bill-form" style="max-width: 800px; margin-bottom: 30px;">
                    <h3 style="margin-bottom: 20px; color: var(--dark);">
                        <i class="fas fa-filter"></i> Select Date Range 100
                    </h3>

                    <!-- শুরু ও শেষ তারিখ input করার অংশ -->
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> From Date</label>
                            <!-- কোন তারিখ থেকে report দেখতে চাই -->
                            <input type="date" id="fromDate" required>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-day"></i> To Date</label>
                            <!-- কোন তারিখ পর্যন্ত report দেখতে চাই -->
                            <input type="date" id="toDate" required>
                        </div>
                    </div>

                    <!-- নির্দিষ্ট কোম্পানির report দেখার জন্য optional dropdown
                         খালি রাখলে সব কোম্পানির data দেখাবে -->
                    <div class="form-group">
                        <label><i class="fas fa-building"></i> Company (Optional)</label>
                        <select id="reportCompanyFilter">
                            <option value="">All Companies</option>
                            <!-- কোম্পানির list JavaScript দিয়ে AJAX থেকে এনে এখানে যোগ করা হয় -->
                        </select>
                    </div>

                    <!-- Quick range বাটন গুলো — এক ক্লিকে তারিখ range সেট হয়ে যায়
                         setQuickRange() function থেকে এবং শেষ তারিখ automatically সেট হয় -->
                    <div style="display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 20px;">
                        <!-- আজকের তারিখ থেকে আজকের তারিখ পর্যন্ত set করে -->
                        <button onclick="setQuickRange('today')" class="btn-quick">
                            <i class="fas fa-calendar-day"></i> Today
                        </button>
                        <!-- গতকালের তারিখ set করে -->
                        <button onclick="setQuickRange('yesterday')" class="btn-quick">
                            <i class="fas fa-calendar-minus"></i> Yesterday
                        </button>
                        <!-- গত ৭ দিনের range set করে -->
                        <button onclick="setQuickRange('last7days')" class="btn-quick">
                            <i class="fas fa-calendar-week"></i> Last 7 Days
                        </button>
                        <!-- গত ৩০ দিনের range set করে -->
                        <button onclick="setQuickRange('last30days')" class="btn-quick">
                            <i class="fas fa-calendar-alt"></i> Last 30 Days
                        </button>
                        <!-- এই মাসের ১ তারিখ থেকে আজ পর্যন্ত set করে -->
                        <button onclick="setQuickRange('thisMonth')" class="btn-quick">
                            <i class="fas fa-calendar"></i> This Month
                        </button>
                        <!-- গত মাসের পুরো range set করে -->
                        <button onclick="setQuickRange('lastMonth')" class="btn-quick">
                            <i class="fas fa-calendar-check"></i> Last Month
                        </button>
                    </div>

                    <!-- এই বাটনে ক্লিক করলে generateDateRangeReport() call হয়ে
                         নির্বাচিত তারিখ range-এর report AJAX দিয়ে load হবে -->
                    <button onclick="generateDateRangeReport()" class="btn-save">
                        <i class="fas fa-chart-bar"></i> Generate Report
                    </button>
                </div>

                <!-- Report result অংশ — প্রথমে লুকানো থাকে, report generate হলে দেখা যায় -->
                <div id="reportResults" style="display: none;">

                    <!-- Summary cards — মোট বিল, মোট amount, pending/complete/hold সংখ্যা দেখায় -->
                    <div class="stats" id="reportSummaryCards" style="margin-bottom: 30px;"></div>

                    <!-- নির্বাচিত date range-এর প্রতিটি বিলের বিস্তারিত তালিকা -->
                    <div class="recent-box" style="margin-bottom: 20px;">
                        <h3><i class="fas fa-list"></i> Detailed Bills</h3>
                        <div class="table-container">
                            <table id="reportBillsTable">
                                <tr>
                                    <th>ID</th>
                                    <th>Company</th>
                                    <th>Invoice</th>
                                    <th>Month</th>
                                    <th>Year</th>
                                    <th>Amount</th>
                                    <th>SMS</th>
                                    <th>Entry Date</th> <!-- বিলটি কখন system-এ ঢোকানো হয়েছিল -->
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- কোম্পানি ভিত্তিক breakdown — কোন কোম্পানির কত বিল ও মোট amount -->
                    <div class="recent-box">
                        <h3><i class="fas fa-building"></i> Company-wise Breakdown</h3>
                        <div class="table-container">
                            <table id="reportCompanyBreakdown">
                                <tr>
                                    <th>Company Name</th>
                                    <th>Total Bills</th>   <!-- কোম্পানির মোট বিল সংখ্যা -->
                                    <th>Total Amount</th>  <!-- কোম্পানির মোট টাকার পরিমাণ -->
                                    <th>Average Amount</th> <!-- প্রতি বিলের গড় টাকা -->
                                </tr>
                            </table>
                        </div>
                    </div>

                    <!-- report download করার বাটন গুলো -->
                    <div style="margin-top: 20px; text-align: center;">
                        <!-- report টি PDF format-এ download করার বাটন -->
                        <button onclick="exportReportToPDF()" class="btn-export" style="margin-right: 10px;">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </button>
                        <!-- report টি Excel format-এ download করার বাটন -->
                        <button onclick="exportReportToExcel()" class="btn-export">
                            <i class="fas fa-file-excel"></i> Download Excel
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <!-- সব JavaScript functions এই file-এ আছে -->
    <script src="js/dashboard.js"></script>
    <script>
        // পেজ সম্পূর্ণ লোড হলে এই code চালু হবে:
        window.onload = function() {
            loadData(); // সাধারণ dashboard data লোড করে

            // AJAX দিয়ে সব কোম্পানির list আনা হচ্ছে
            // এরপর reportCompanyFilter dropdown-এ option হিসেবে যোগ করা হচ্ছে
            fetch('ajax.php?action=get_companies')
                .then(res => res.json())
                .then(data => {
                    if(data.success) {
                        const select = document.getElementById('reportCompanyFilter');
                        data.companies.forEach(company => {
                            const option = document.createElement('option');
                            option.value = company.company_name;
                            option.textContent = company.company_name;
                            select.appendChild(option); // dropdown-এ কোম্পানি যোগ হচ্ছে
                        });
                    }
                });
        };
    </script>
</body>
</html>
