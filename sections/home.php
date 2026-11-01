<!-- Dashboard Home Section -->
<!-- ড্যাশবোর্ড হোম সেকশন - এটি মূল ড্যাশবোর্ড পেজ যেখানে সমস্ত সারসংক্ষেপ তথ্য দেখানো হয় -->
<div id="home" class="section active">
    <h1 class="section-title"><i class="fas fa-home"></i> Dashboard</h1>
    <div class="dashboard-month">
        <i class="fas fa-calendar-alt"></i> Month of <strong><?= date('F Y'); ?></strong>
    </div>

    <!-- Company Status Boxes -->
    <!-- কোম্পানির bill status box - প্রতিটি কোম্পানির বিলের বর্তমান অবস্থা (pending/complete/hold) কার্ড আকারে এখানে dynamically লোড হয় -->
    <div class="stats" id="companyStatusBoxes">
        <!-- Company status boxes will be loaded here dynamically -->
        <!-- কোম্পানির স্ট্যাটাস বক্সগুলো JavaScript এর মাধ্যমে এখানে স্বয়ংক্রিয়ভাবে যুক্ত হবে -->
    </div>

    <!-- Entry Summary -->
    <!-- summary count - মোট বিল, মোট পরিমাণ, pending/complete/hold বিলের সংখ্যার সারসংক্ষেপ এখানে দেখানো হয় -->
    <div class="company-summary" id="companySummary">
        <!-- Summary counts will be shown here -->
        <!-- সারসংক্ষেপ সংখ্যাগুলো JavaScript এর মাধ্যমে এখানে দেখানো হবে -->
    </div>

    <!-- Recent Bills -->
    <!-- সাম্প্রতিক বিল - সর্বশেষ এন্ট্রি করা বিলের তালিকা টেবিল আকারে এখানে দেখানো হয় -->
    <div class="recent-box">
        <h3><i class="fas fa-history"></i> Recent Bills</h3>
        <!-- recentBillsContainer - সাম্প্রতিক বিলের টেবিলের মোড়ক div -->
        <div id="recentBillsContainer">
            <!-- recentBillsTable - সাম্প্রতিক বিলের তালিকা দেখানোর মূল টেবিল -->
            <!-- এই টেবিলে কোম্পানি, ইনভয়েস নম্বর, মাস, বছর, পরিমাণ এবং SMS তথ্য দেখানো হয় -->
            <table id="recentBillsTable">
                <tr>
                    <!-- Company - কোম্পানির নাম -->
                    <th>Company</th>
                    <!-- Invoice - ইনভয়েস নম্বর -->
                    <th>Invoice</th>
                    <!-- Month - বিলের মাস -->
                    <th>Month</th>
                    <!-- Year - বিলের বছর -->
                    <th>Year</th>
                    <!-- Amount - বিলের পরিমাণ (টাকা) -->
                    <th>Amount</th>
                    <!-- SMS - SMS এর সংখ্যা (যদি থাকে) -->
                    <th>SMS</th>
                </tr>
            </table>
        </div>
    </div>
</div>

<!-- Company Bills Modal -->
<div id="companyBillsModal" class="cbm-overlay">
    <div class="cbm-box">
        <!-- Header -->
        <div class="cbm-header">
            <div class="cbm-title">
                <i class="fas fa-building"></i>
                <span id="companyBillsTitle">Company Bills</span>
            </div>
            <button class="cbm-close" onclick="closeCompanyBillsModal()" title="Close">&times;</button>
        </div>
        <!-- Summary strip (জাভাস্ক্রিপ্ট দিয়ে পূরণ হবে) -->
        <div id="companyBillsSummary" class="cbm-summary"></div>
        <!-- Table -->
        <div class="cbm-body">
            <table class="cbm-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Invoice</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Amount</th>
                        <th>SMS</th>
                        <th>Status</th>
                        <th>Entry Date</th>
                        <th>Hold Date</th>
                        <th>Complete Date</th>
                    </tr>
                </thead>
                <tbody id="companyBillsBody">
                    <tr><td colspan="10" class="cbm-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
