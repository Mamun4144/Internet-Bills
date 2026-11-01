<?php
// বর্তমানে কোন PHP file চলছে তার নাম নেওয়া হচ্ছে — যেমন: 'dashboard.php', 'new_bill.php' ইত্যাদি
// basename() শুধু file-এর নাম দেয়, পুরো path নয় — এটি দিয়ে active menu item বোঝা যাবে
$currentPage = basename($_SERVER['PHP_SELF']); ?>

<!-- Sidebar-এর মূল container — id="sidebar" দিয়ে JavaScript থেকে toggle করা যাবে -->
<div class="sidebar" id="sidebar">

    <!-- Sidebar-এর উপরে logo ও application-এর নাম দেখানো হচ্ছে -->
    <div class="logo">
        <!-- Font Awesome থেকে invoice/dollar চিহ্নের icon দেখানো হচ্ছে -->
        <i class="fas fa-file-invoice-dollar"></i>
        <!-- Application-এর নাম "Billing" দেখানো হচ্ছে -->
        <h3>Billing</h3>
    </div>

    <!-- Navigation menu-র list শুরু হচ্ছে -->
    <ul>
        <!-- Dashboard menu item -->
        <li>
            <!-- dashboard.php-এ যাওয়ার link — যদি এখন dashboard.php চলছে তাহলে 'active' class যোগ হবে, নয়তো খালি থাকবে -->
            <a href="dashboard.php" class="<?= $currentPage=='dashboard.php' ? 'active' : '' ?>">
                <!-- ঘর/home icon -->
                <i class="fas fa-home"></i> Dashboard
            </a>
        </li>

        <!-- New Bill তৈরি করার menu item -->
        <li>
            <!-- new_bill.php-এ যাওয়ার link — বর্তমান page new_bill.php হলে 'active' class যোগ হবে -->
            <a href="new_bill.php" class="<?= $currentPage=='new_bill.php' ? 'active' : '' ?>">
                <!-- plus/যোগ চিহ্নের icon -->
                <i class="fas fa-plus-circle"></i> New Bill
            </a>
        </li>

        <!-- Pending (অপেক্ষমান) bill দেখার menu item -->
        <li>
            <!-- pending_bills.php-এ যাওয়ার link — বর্তমান page pending_bills.php হলে 'active' class যোগ হবে -->
            <a href="pending_bills.php" class="<?= $currentPage=='pending_bills.php' ? 'active' : '' ?>">
                <!-- list icon -->
                <i class="fas fa-list-alt"></i> Pending Bills
            </a>
        </li>

        <!-- Complete (সম্পন্ন) bill দেখার menu item -->
        <li>
            <!-- complete_bills.php-এ যাওয়ার link — বর্তমান page complete_bills.php হলে 'active' class যোগ হবে -->
            <a href="complete_bills.php" class="<?= $currentPage=='complete_bills.php' ? 'active' : '' ?>">
                <!-- check/টিক চিহ্নের icon -->
                <i class="fas fa-check-circle"></i> Complete Bills
            </a>
        </li>

        <!-- Hold (স্থগিত) bill দেখার menu item -->
        <li>
            <!-- hold_bills.php-এ যাওয়ার link — বর্তমান page hold_bills.php হলে 'active' class যোগ হবে -->
            <a href="hold_bills.php" class="<?= $currentPage=='hold_bills.php' ? 'active' : '' ?>">
                <!-- pause/বিরতি icon -->
                <i class="fas fa-pause-circle"></i> Hold Bills
            </a>
        </li>

        <!-- তারিখ অনুযায়ী report দেখার menu item -->
        <li>
            <!-- date_report.php-এ যাওয়ার link — বর্তমান page date_report.php হলে 'active' class যোগ হবে -->
            <a href="date_report.php" class="<?= $currentPage=='date_report.php' ? 'active' : '' ?>">
                <!-- calendar icon -->
                <i class="fas fa-calendar-alt"></i> Date Range Report
            </a>
        </li>

        <!-- Company পরিচালনার menu item -->
        <li>
            <!-- manage_companies.php-এ যাওয়ার link — বর্তমান page manage_companies.php হলে 'active' class যোগ হবে -->
            <a href="manage_companies.php" class="<?= $currentPage=='manage_companies.php' ? 'active' : '' ?>">
                <!-- building/অফিস icon -->
                <i class="fas fa-building"></i> Manage Companies
            </a>
        </li>

        <li>
            <a href="deleted_records.php" class="<?= $currentPage=='deleted_records.php' ? 'active' : '' ?>">
                <i class="fas fa-trash-restore"></i> Deleted Records
            </a>
        </li>

        <!-- Bill PDF আপলোড ও দেখার menu item -->
        <li>
            <a href="bill_pdf.php" class="<?= $currentPage=='bill_pdf.php' ? 'active' : '' ?>">
                <i class="fas fa-file-pdf"></i> Bill PDF
            </a>
        </li>

        <!-- Logout (বের হওয়ার) menu item — এটিতে active class logic নেই কারণ logout সবসময় আলাদা -->
        <li>
            <!-- logout.php-এ যাওয়ার link — user-কে logout করে login page-এ পাঠাবে -->
            <a href="logout.php">
                <!-- sign-out/বের হওয়ার icon -->
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </li>
    </ul>

    <!-- Sidebar-এর নিচে সংক্ষিপ্ত পরিসংখ্যান (summary) দেখানোর section -->
    <!-- id="sidebarSummary" দিয়ে JavaScript থেকে এই section update করা যাবে -->
    <div class="summary" id="sidebarSummary">

        <!-- এই মাসে মোট কতটি company-র bill আছে — JavaScript দিয়ে sidebarCompanies span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-building"></i> This Month: <span id="sidebarCompanies">0</span></p>

        <!-- গত মাসে মোট কতটি company-র bill ছিল — JavaScript দিয়ে sidebarCompaniesPrevious span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-history"></i> Previous Month: <span id="sidebarCompaniesPrevious">0</span></p>

        <!-- মোট কতটি Internet bill আছে — JavaScript দিয়ে sidebarInternetBills span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-wifi"></i> Internet: <span id="sidebarInternetBills">0</span></p>

        <!-- মোট কতটি SMS bill আছে — JavaScript দিয়ে sidebarSmsBills span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-sms"></i> SMS: <span id="sidebarSmsBills">0</span></p>

        <!-- এই মাসে কতটি entry করা হয়েছে — JavaScript দিয়ে sidebarThisMonthEntry span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-calendar-check"></i> Entry This Month: <span id="sidebarThisMonthEntry">0</span></p>

        <!-- মোট কতটি bill complete হয়েছে — JavaScript দিয়ে sidebarCompleteBills span-এ সংখ্যা বসানো হবে -->
        <p><i class="fas fa-check-circle" style="color:#6ee7b7"></i> Complete Bills: <span id="sidebarCompleteBills">0</span></p>

        <!-- শেষ ৩ মাসে কতটি bill hold-এ আছে — উপরে একটি border line দিয়ে আলাদা করা হয়েছে -->
        <!-- JavaScript দিয়ে sidebarHoldBills span-এ সংখ্যা বসানো হবে -->
        <p style="border-top: 1px solid rgba(255,255,255,0.1); padding-top: 12px; margin-top: 8px;">
            <i class="fas fa-pause-circle"></i> Last 3 Month Hold Bills: <span id="sidebarHoldBills">0</span>
        </p>
    </div>
</div>
