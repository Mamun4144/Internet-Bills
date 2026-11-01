<!-- New Bill Entry Section -->
<!-- নতুন বিল এন্ট্রি সেকশন - এখানে নতুন বিল তৈরি ও সংরক্ষণ করা হয় -->
<div id="billEntry" class="section active">
    <h2><i class="fas fa-plus-circle"></i> New Bill Entry</h2>
    <!-- billForm - বিল এন্ট্রি ফর্ম, এই ফর্মের মাধ্যমে নতুন বিলের সমস্ত তথ্য সংগ্রহ করা হয় -->
    <form id="billForm" class="bill-form">

        <!-- company dropdown - কোম্পানি নির্বাচন ড্রপডাউন -->
        <!-- ডেটাবেজ থেকে লোড করা কোম্পানির তালিকা থেকে সঠিক কোম্পানি বেছে নেওয়া হয় -->
        <div class="form-group">
            <label><i class="fas fa-building"></i> Company Name</label>
            <!-- company select - JavaScript এর মাধ্যমে কোম্পানির তালিকা এখানে dynamically যুক্ত হয় -->
            <select name="company" id="company" required>
                <option value="">Select Company</option>
                <!-- কোম্পানির অপশনগুলো JavaScript দিয়ে এখানে লোড হবে -->
            </select>
        </div>

        <!-- invoice number - ইনভয়েস নম্বর ইনপুট ফিল্ড -->
        <!-- প্রতিটি বিলের জন্য একটি অনন্য (unique) ইনভয়েস নম্বর প্রদান করতে হবে -->
        <div class="form-group">
            <label><i class="fas fa-file-alt"></i> Invoice Number</label>
            <!-- invoice input - টেক্সট ফিল্ডে ইনভয়েস নম্বর লেখা হয়, এটি অবশ্যই পূরণ করতে হবে -->
            <input type="text" name="invoice" id="invoice" required>
        </div>

        <!-- month/year select - মাস এবং বছর নির্বাচনের জন্য পাশাপাশি দুটি ড্রপডাউন -->
        <div class="form-row">
            <!-- month dropdown - বিলের মাস নির্বাচন -->
            <div class="form-group">
                <label><i class="fas fa-calendar"></i> Month</label>
                <!-- month select - PHP এর $months array থেকে সমস্ত মাসের নাম এখানে option হিসেবে দেখানো হয় -->
                <select name="month" id="month" required>
                    <option value="">Select Month</option>
                    <?php foreach($months as $m) echo "<option>$m</option>"; ?>
                    <!-- PHP loop: $months array এর প্রতিটি মাসের নাম (January থেকে December) একটি করে <option> হিসেবে যুক্ত হয় -->
                </select>
            </div>
            <!-- year dropdown - বিলের বছর নির্বাচন -->
            <div class="form-group">
                <label><i class="fas fa-calendar-alt"></i> Year</label>
                <!-- year select - বর্তমান বছর থেকে শুরু করে পিছনে ৫ বছর পর্যন্ত অপশন দেখানো হয় -->
                <select name="year" id="year" required>
                    <option value="">Select Year</option>
                    <?php for($y=$currentYear; $y>=$currentYear-5; $y--) echo "<option>$y</option>"; ?>
                    <!-- PHP loop: $currentYear থেকে ($currentYear - 5) পর্যন্ত প্রতিটি বছর একটি করে <option> হিসেবে যুক্ত হয় -->
                    <!-- উদাহরণ: ২০২৬, ২০২৫, ২০২৪, ২০২৩, ২০২২, ২০২১ -->
                </select>
            </div>
        </div>

        <!-- amount এবং SMS ফিল্ড পাশাপাশি দুটি কলামে দেখানো হয় -->
        <div class="form-row">
            <!-- amount - বিলের পরিমাণ (টাকায়) ইনপুট ফিল্ড -->
            <div class="form-group">
                <label><i class="fas fa-money-bill-wave"></i> Amount (৳)</label>
                <!-- amount input - দশমিক সংখ্যা (যেমন ১২৩৪.৫০) গ্রহণ করে, step="0.01" মানে পয়সা পর্যন্ত গ্রহণযোগ্য -->
                <input type="number" name="amount" id="amount" step="0.01" required>
            </div>
            <!-- SMS - SMS এর সংখ্যা ইনপুট ফিল্ড (ঐচ্ছিক) -->
            <div class="form-group">
                <label><i class="fas fa-sms"></i> SMS (Optional)</label>
                <!-- sms input - SMS সংখ্যা লেখার ফিল্ড, এটি পূরণ করা বাধ্যতামূলক নয় -->
                <!-- placeholder দেখায় "Leave blank if no SMS" - SMS না থাকলে খালি রাখা যাবে -->
                <input type="number" name="sms" id="sms" placeholder="Leave blank if no SMS">
            </div>
        </div>

        <!-- saveBill() button - বিল সংরক্ষণ করার বাটন -->
        <!-- এই বাটনে ক্লিক করলে JavaScript এর saveBill() ফাংশন চালু হয় যা ফর্মের তথ্য সার্ভারে পাঠায় -->
        <button type="button" onclick="saveBill()" class="btn-save">
            <i class="fas fa-save"></i> Save Bill
        </button>
    </form>
</div>
