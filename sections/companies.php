<!-- Manage Companies Section -->
<!-- কোম্পানি ব্যবস্থাপনা সেকশন - এখানে নতুন কোম্পানি যোগ করা এবং বিদ্যমান কোম্পানির তালিকা দেখা যায় -->
<div id="manageCompanies" class="section active">
    <h2><i class="fas fa-building"></i> Manage Companies</h2>

    <!-- add company form - নতুন কোম্পানি যোগ করার ফর্ম -->
    <!-- এই ফর্মের মাধ্যমে ডেটাবেজে নতুন কোম্পানির নাম সংরক্ষণ করা হয় -->
    <div class="bill-form" style="max-width: 600px;">
        <h3 style="margin-bottom: 20px; color: var(--dark);">Add New Company</h3>

        <!-- newCompanyName input - নতুন কোম্পানির নাম লেখার ইনপুট ফিল্ড -->
        <!-- ব্যবহারকারী এখানে কোম্পানির নাম টাইপ করবেন -->
        <div class="form-group">
            <label><i class="fas fa-building"></i> Company Name</label>
            <!-- newCompanyName - টেক্সট ফিল্ড যেখানে নতুন কোম্পানির নাম লেখা হয় -->
            <!-- placeholder "Enter company name" দিয়ে বোঝানো হয় কী লিখতে হবে -->
            <input type="text" id="newCompanyName" placeholder="Enter company name">
        </div>

        <!-- addCompany() button - কোম্পানি যোগ করার বাটন -->
        <!-- এই বাটনে ক্লিক করলে JavaScript এর addCompany() ফাংশন চালু হয় -->
        <!-- addCompany() ফাংশন newCompanyName ইনপুটের মান নিয়ে সার্ভারে পাঠায় এবং কোম্পানি সংরক্ষণ করে -->
        <button onclick="addCompany()" class="btn-save">
            <i class="fas fa-plus"></i> Add Company
        </button>
    </div>

    <!-- companies table - সংরক্ষিত কোম্পানির তালিকার টেবিল -->
    <!-- এই টেবিলে ডেটাবেজে থাকা সমস্ত কোম্পানির তথ্য দেখানো হয় -->
    <div class="table-container" style="margin-top: 30px;">
        <h3 style="margin-bottom: 20px;"><i class="fas fa-list"></i> Saved Companies</h3>
        <!-- companiesTable - সকল সংরক্ষিত কোম্পানির তালিকা দেখানোর মূল টেবিল -->
        <!-- JavaScript এর মাধ্যমে ডেটাবেজ থেকে কোম্পানির তথ্য এনে এই টেবিলে দেখানো হয় -->
        <table id="companiesTable">
            <tr>
                <!-- ID - কোম্পানির ডেটাবেজ আইডি নম্বর -->
                <th>ID</th>
                <!-- Company Name - কোম্পানির নাম -->
                <th>Company Name</th>
                <!-- Created - কোম্পানি কখন যোগ করা হয়েছিল তার তারিখ ও সময় -->
                <th>Created</th>
                <!-- Actions - কোম্পানির উপর কার্যক্রম (যেমন মুছে ফেলা বা সম্পাদনা) -->
                <th>Actions</th>
            </tr>
            <!-- কোম্পানির সারিগুলো JavaScript এর মাধ্যমে dynamically এখানে যুক্ত হবে -->
        </table>
    </div>
</div>
