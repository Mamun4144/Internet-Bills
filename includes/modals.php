<!-- Edit Modal — bill সম্পাদনা করার জন্য popup window -->
<!-- id="editModal" দিয়ে JavaScript থেকে এই modal খোলা ও বন্ধ করা যাবে -->
<!-- class="modal" দিয়ে CSS-এ এটিকে একটি overlay popup হিসেবে style করা হয়েছে -->
<div id="editModal" class="modal">

    <!-- Modal-এর ভেতরের content box -->
    <div class="modal-content">

        <!-- Modal-এর উপরের header অংশ — title ও close button এখানে থাকে -->
        <div class="modal-header">
            <!-- Modal-এর শিরোনাম — edit icon সহ "Edit Bill" দেখানো হচ্ছে -->
            <h3><i class="fas fa-edit"></i> Edit Bill</h3>
            <!-- × চিহ্নে click করলে closeModal() function ডাকা হবে এবং editModal বন্ধ হয়ে যাবে -->
            <span class="close" onclick="closeModal('editModal')">&times;</span>
        </div>

        <!-- Modal-এর মূল body অংশ — form এখানে থাকে -->
        <div class="modal-body">

            <!-- Edit form — submit হলে updateInvoice() function ডাকা হবে, return false করলে page reload হবে না -->
            <form id="editForm" onsubmit="return updateInvoice(event)">

                <!-- Hidden input — bill-এর ID রাখা হচ্ছে, user দেখতে পাবে না কিন্তু submit হলে server-এ যাবে -->
                <!-- কোন bill edit করা হচ্ছে তা চেনার জন্য এই ID ব্যবহার হয় -->
                <input type="hidden" id="editId">

                <!-- Company নির্বাচন করার field -->
                <div class="form-group">
                    <label>Company</label>
                    <!-- Dropdown থেকে company বেছে নেওয়া যাবে — required মানে এটি ফাঁকা রাখা যাবে না -->
                    <!-- JavaScript দিয়ে এই dropdown-এ company-র list লোড করা হবে -->
                    <select id="editCompany" required>
                        <option value="">Select Company</option>
                    </select>
                </div>

                <!-- Invoice নম্বর লেখার field -->
                <div class="form-group">
                    <label>Invoice No</label>
                    <!-- Invoice নম্বর টাইপ করার text box — required মানে এটি ফাঁকা রাখা যাবে না -->
                    <input type="text" id="editInvoiceNo" required>
                </div>

                <!-- মাস ও বছর পাশাপাশি দুটি field একসাথে দেখানোর জন্য form-row wrapper -->
                <div class="form-row">

                    <!-- মাস নির্বাচন করার field -->
                    <div class="form-group">
                        <label>Month</label>
                        <!-- Dropdown থেকে মাস বেছে নেওয়া যাবে — required মানে এটি ফাঁকা রাখা যাবে না -->
                        <select id="editMonth" required>
                            <option value="">Select Month</option>
                            <!-- init.php-এ তৈরি $months array loop করে প্রতিটি মাস option হিসেবে দেখানো হচ্ছে -->
                            <!-- যেমন: January, February, ... December -->
                            <?php foreach($months as $m) echo "<option>$m</option>"; ?>
                        </select>
                    </div>

                    <!-- বছর নির্বাচন করার field -->
                    <div class="form-group">
                        <label>Year</label>
                        <!-- Dropdown থেকে বছর বেছে নেওয়া যাবে — required মানে এটি ফাঁকা রাখা যাবে না -->
                        <select id="editYear" required>
                            <option value="">Select Year</option>
                            <!-- বর্তমান বছর ($currentYear) থেকে শুরু করে ৫ বছর পেছনে পর্যন্ত option দেখানো হচ্ছে -->
                            <!-- যেমন: 2026, 2025, 2024, 2023, 2022, 2021 -->
                            <?php for($y=$currentYear; $y>=$currentYear-5; $y--) echo "<option>$y</option>"; ?>
                        </select>
                    </div>
                </div>

                <!-- Amount ও SMS পাশাপাশি দুটি field একসাথে দেখানোর জন্য form-row wrapper -->
                <div class="form-row">

                    <!-- টাকার পরিমাণ লেখার field -->
                    <div class="form-group">
                        <label>Amount (৳)</label>
                        <!-- সংখ্যা টাইপ করার box — step="0.01" মানে দশমিক সংখ্যাও লেখা যাবে, required মানে ফাঁকা রাখা যাবে না -->
                        <input type="number" id="editAmount" step="0.01" required>
                    </div>

                    <!-- SMS bill-এর পরিমাণ লেখার field (ঐচ্ছিক) -->
                    <div class="form-group">
                        <label>SMS (Optional)</label>
                        <!-- SMS bill না থাকলে এই field ফাঁকা রাখা যাবে — required নেই তাই বাধ্যতামূলক নয় -->
                        <input type="number" id="editSms" placeholder="Leave blank if no SMS">
                    </div>
                </div>

                <!-- Form-এর নিচে Cancel ও Update button -->
                <div class="form-actions">
                    <!-- Cancel button — click করলে modal বন্ধ হয়ে যাবে, কোনো পরিবর্তন save হবে না -->
                    <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                    <!-- Update button — click করলে form submit হবে এবং updateInvoice() function চলবে -->
                    <button type="submit" class="btn-update"><i class="fas fa-check"></i> Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Hold Modal — bill স্থগিত (hold) করার জন্য popup window -->
<!-- id="holdModal" দিয়ে JavaScript থেকে এই modal খোলা ও বন্ধ করা যাবে -->
<!-- class="modal" দিয়ে CSS-এ এটিকে একটি overlay popup হিসেবে style করা হয়েছে -->
<div id="holdModal" class="modal">

    <!-- Modal-এর ভেতরের content box -->
    <div class="modal-content">

        <!-- Modal-এর উপরের header অংশ — title ও close button এখানে থাকে -->
        <div class="modal-header">
            <!-- Modal-এর শিরোনাম — pause icon সহ "Hold Bill" দেখানো হচ্ছে -->
            <h3><i class="fas fa-pause-circle"></i> Hold Bill</h3>
            <!-- × চিহ্নে click করলে closeModal() function ডাকা হবে এবং holdModal বন্ধ হয়ে যাবে -->
            <span class="close" onclick="closeModal('holdModal')">&times;</span>
        </div>

        <!-- Modal-এর মূল body অংশ — form এখানে থাকে -->
        <div class="modal-body">

            <!-- Hold form — submit হলে submitHold() function ডাকা হবে, return false করলে page reload হবে না -->
            <form id="holdForm" onsubmit="return submitHold(event)">

                <!-- Hidden input — hold করা bill-এর ID রাখা হচ্ছে, user দেখতে পাবে না কিন্তু submit হলে server-এ যাবে -->
                <!-- কোন bill hold করা হচ্ছে তা চেনার জন্য এই ID ব্যবহার হয় -->
                <input type="hidden" id="holdId">

                <!-- Hold করার কারণ লেখার field -->
                <div class="form-group">
                    <!-- Label-এ comment icon সহ "Reason for Holding" দেখানো হচ্ছে -->
                    <label><i class="fas fa-comment"></i> Reason for Holding</label>
                    <!-- Textarea — একাধিক লাইনে কারণ লেখা যাবে, rows="4" মানে ৪ লাইন উঁচু দেখাবে -->
                    <!-- required মানে এটি ফাঁকা রাখা যাবে না — কারণ না লিখলে submit হবে না -->
                    <!-- resize: vertical মানে user শুধু উপর-নিচে বড় করতে পারবে -->
                    <textarea id="holdDescription" rows="4" placeholder="Enter the reason why you are holding this bill..." required
                        style="width: 100%; padding: 12px; border: 1px solid #d1d5db; border-radius: 8px; font-family: inherit; resize: vertical;">
                    </textarea>
                </div>

                <!-- Form-এর নিচে Cancel ও Hold Bill button -->
                <div class="form-actions">
                    <!-- Cancel button — click করলে modal বন্ধ হয়ে যাবে, কোনো পরিবর্তন save হবে না -->
                    <button type="button" class="btn-cancel" onclick="closeModal('holdModal')">Cancel</button>
                    <!-- Hold Bill button — warning রঙে দেখাবে (হলুদ/কমলা), click করলে submitHold() চলবে -->
                    <button type="submit" class="btn-update" style="background: var(--warning);">
                        <i class="fas fa-pause"></i> Hold Bill
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
