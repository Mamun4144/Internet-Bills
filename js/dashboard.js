// ============================================================
// toggleSidebar(): sidebar খোলা/বন্ধ করার function
// sidebar-এ 'active' class toggle করা হয়
// localStorage-এ sidebar-এর অবস্থা (খোলা/বন্ধ) save করা হয়
// ============================================================
// ============================================================
// Dark Mode toggle — body-তে dark-mode class যোগ/বাদ দেওয়া হয়
// localStorage-এ পছন্দ save থাকে যাতে reload-এও টিকে থাকে
// ============================================================
function toggleDarkMode() {
    const isDark = document.body.classList.toggle('dark-mode');
    localStorage.setItem('darkMode', isDark ? '1' : '0');
    const btn = document.getElementById('darkModeBtn');
    if (btn) btn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
}

document.addEventListener('DOMContentLoaded', function() {
    if (localStorage.getItem('darkMode') === '1') {
        document.body.classList.add('dark-mode');
        const btn = document.getElementById('darkModeBtn');
        if (btn) btn.innerHTML = '<i class="fas fa-sun"></i>';
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    // sidebar-এ 'active' class যোগ বা বাদ দেওয়া হয় (toggle)
    sidebar.classList.toggle('active');
    // sidebar খোলা আছে কিনা সেই অবস্থা localStorage-এ 'sidebarOpen' key দিয়ে save করা হয়
    localStorage.setItem('sidebarOpen', sidebar.classList.contains('active'));
}

// ============================================================
// DOMContentLoaded event: page সম্পূর্ণ load হলে এই block চলে
// localStorage থেকে sidebar-এর আগের অবস্থা পড়ে restore করা হয়
// যদি আগে sidebar খোলা ছিল, তাহলে 'active' class যোগ করে পুনরায় খোলা হয়
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // localStorage থেকে 'sidebarOpen' value পড়া হয়
    if (localStorage.getItem('sidebarOpen') === 'true') {
        // যদি আগে খোলা ছিল, sidebar-এ 'active' class যোগ করা হয়
        document.getElementById('sidebar').classList.add('active');
    }
});

// ============================================================
// showSection(): নির্দিষ্ট section দেখানো এবং বাকি সব লুকানো
// সব section থেকে 'active' class সরানো হয়
// শুধু নির্বাচিত section-এ 'active' class যোগ করা হয়
// sidebar-এর navigation link-গুলোর active state-ও update করা হয়
// ============================================================
function showSection(section) {
    // সব section থেকে 'active' class সরানো হয় (সব লুকানো)
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    // শুধু নির্বাচিত section-এ 'active' class যোগ করা হয় (দেখানো)
    document.getElementById(section).classList.add('active');
    // sidebar-এর সব link থেকে 'active' class সরানো হয়
    document.querySelectorAll('.sidebar ul li a').forEach(a => a.classList.remove('active'));
    // যে link-এ click করা হয়েছে সেটিতে 'active' class যোগ করা হয়
    event.target.classList.add('active');
}

// ============================================================
// loadData(): AJAX দিয়ে dashboard-এর সকল data load করা
// ajax.php?action=get_data endpoint থেকে data fetch করা হয়
// সফল হলে:
//   - sidebar-এর stats update করা হয়
//   - company status boxes তৈরি ও রং করা হয়
//   - company summary count (Entry/Hold/Pending) দেখানো হয়
//   - সাম্প্রতিক bills table update করা হয়
// ============================================================
function loadData() {
    fetch('ajax.php?action=get_data')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // ---- sidebar stats update করা হচ্ছে ----
                // এই মাসের কোম্পানি সংখ্যা
                document.getElementById('sidebarCompanies').textContent = data.companies_this_month;
                // আগের মাসের কোম্পানি সংখ্যা
                document.getElementById('sidebarCompaniesPrevious').textContent = data.companies_previous_month;
                // এই মাসে কতটি entry হয়েছে
                document.getElementById('sidebarThisMonthEntry').textContent = data.this_month_entry;
                // internet বিলের সংখ্যা
                document.getElementById('sidebarInternetBills').textContent = data.internet_bills;
                // SMS বিলের সংখ্যা
                document.getElementById('sidebarSmsBills').textContent = data.sms_bills;
                // শেষ ৩ মাসে hold বিলের সংখ্যা
                document.getElementById('sidebarHoldBills').textContent = data.hold_bills_last_3_months;
                // মোট complete বিলের সংখ্যা
                const sidebarCompleteBills = document.getElementById('sidebarCompleteBills');
                if (sidebarCompleteBills) sidebarCompleteBills.textContent = data.complete_bills_count || 0;

                // ---- company status boxes update করা হচ্ছে (শুধু dashboard-এ) ----
                const companyStatusBoxes = document.getElementById('companyStatusBoxes');
                // যদি element না থাকে তাহলে বের হয়ে যাওয়া
                if(!companyStatusBoxes) return;
                // আগের সব box মুছে ফেলা
                companyStatusBoxes.innerHTML = '';

                // প্রতিটি কোম্পানির জন্য একটি করে status box তৈরি করা হচ্ছে
                data.company_status.forEach(company => {
                    const box = document.createElement('div');
                    let className = 'stat-box company-box ';
                    let inlineStyle = 'padding: 8px 6px; border-radius: 6px; text-align: center; min-height: 45px; display: flex; align-items: center; justify-content: center; transition: all 0.2s ease; cursor: pointer; ';

                    // কোম্পানির বর্তমান অবস্থা অনুযায়ী রং নির্ধারণ করা হচ্ছে
                    if (company.has_hold) {
                        // hold থাকলে হলুদ রঙের border
                        className += 'company-hold';
                        inlineStyle += 'border: 3px solid #ffc107 !important; box-shadow: 0 1px 4px rgba(255, 193, 7, 0.12);';
                    } else if (company.has_bill) {
                        // বিল আছে (paid) হলে সবুজ রঙের border
                        className += 'company-paid';
                        inlineStyle += 'border: 3px solid #28a745 !important; box-shadow: 0 1px 4px rgba(40, 167, 69, 0.12);';
                    } else {
                        // বিল নেই (pending) হলে লাল রঙের border
                        className += 'company-pending';
                        inlineStyle += 'border: 3px solid #dc3545 !important; box-shadow: 0 1px 4px rgba(220, 53, 69, 0.12);';
                    }

                    box.className = className;
                    inlineStyle += 'pointer-events: auto;';
                    box.setAttribute('style', inlineStyle);

                    // অবস্থা অনুযায়ী text color নির্ধারণ করা হচ্ছে
                    const textColor = company.has_hold ? '#856404' : (company.has_bill ? '#155724' : '#721c24');
                    const cName = company.company_name;
                    box.innerHTML = `<h3 onclick="showCompanyBills('${cName.replace(/'/g, "\\'")}')" style="margin: 0; padding: 0; font-size: 11px; font-weight: 600; line-height: 1.2; color: ${textColor}; cursor:pointer; width:100%; height:100%;">${cName}</h3>`;
                    companyStatusBoxes.appendChild(box);
                });

                // ---- company summary count update করা হচ্ছে (শুধু dashboard-এ) ----
                const companySummary = document.getElementById('companySummary');
                // যদি element না থাকে তাহলে বের হয়ে যাওয়া
                if(!companySummary) return;
                // hold-এ থাকা কোম্পানির সংখ্যা গণনা
                const holdCount = data.company_status.filter(c => c.has_hold).length;
                // বিল entry হয়েছে কিন্তু hold-এ নেই এমন কোম্পানির সংখ্যা
                const paidCount = data.company_status.filter(c => c.has_bill && !c.has_hold).length;
                // এখনো বিল entry হয়নি এবং hold-এও নেই এমন কোম্পানির সংখ্যা
                const pendingCount = data.company_status.filter(c => !c.has_bill && !c.has_hold).length;

                // summary card-এ Entry, Hold, Pending count দেখানো হচ্ছে
                const isDark = document.body.classList.contains('dark-mode');
                const summaryCards = [
                    { label: 'Entry',   count: paidCount,    bg: isDark ? 'rgba(16,185,129,0.15)' : 'linear-gradient(135deg,#d4edda,#c3e6cb)', border: '#28a745', color: isDark ? '#6ee7b7' : '#155724' },
                    { label: 'Hold',    count: holdCount,    bg: isDark ? 'rgba(245,158,11,0.15)' : 'linear-gradient(135deg,#fff3cd,#ffe8a1)', border: '#ffc107', color: isDark ? '#fcd34d' : '#856404' },
                    { label: 'Pending', count: pendingCount, bg: isDark ? 'rgba(239,68,68,0.15)'  : 'linear-gradient(135deg,#f8d7da,#f5c6cb)', border: '#dc3545', color: isDark ? '#fca5a5' : '#721c24' },
                ];
                companySummary.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:6px; width:fit-content;">
                        ${summaryCards.map(c => `
                        <div style="background:${c.bg}; border-radius:6px; padding:6px 12px; border-left:3px solid ${c.border}; box-shadow:0 1px 4px rgba(0,0,0,0.08); display:flex; align-items:center; gap:6px;">
                            <div style="width:8px; height:8px; border-radius:50%; background:${c.border};"></div>
                            <span style="font-size:11px; color:${c.color}; font-weight:600;">${c.label}: <strong style="font-size:13px;">${c.count}</strong></span>
                        </div>`).join('')}
                    </div>
                `;

                // ---- সাম্প্রতিক bills table update করা হচ্ছে (শুধু dashboard-এ) ----
                const table = document.getElementById('recentBillsTable');
                // যদি element না থাকে তাহলে বের হয়ে যাওয়া
                if(!table) return;
                // header row ছাড়া বাকি সব row মুছে ফেলা
                while (table.rows.length > 1) table.deleteRow(1);

                if (data.recent.length > 0) {
                    // প্রতিটি সাম্প্রতিক bill-এর জন্য table-এ row যোগ করা হচ্ছে
                    data.recent.forEach(bill => {
                        const row = table.insertRow();
                        row.innerHTML = `
                            <td>${bill.company_name}</td>
                            <td>${bill.invoice_no}</td>
                            <td>${bill.bill_month}</td>
                            <td>${bill.bill_year}</td>
                            <td>৳${parseFloat(bill.amount).toFixed(2)}</td>
                            <td>${bill.sms_count || '-'}</td>
                        `;
                    });
                } else {
                    // কোনো bill না থাকলে "No bills found" দেখানো হচ্ছে
                    const row = table.insertRow();
                    row.innerHTML = '<td colspan="6" class="no-data"><i class="fas fa-inbox"></i><br>No bills found</td>';
                }
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// loadCompaniesDropdown(): কোম্পানির dropdown list populate করা
// ajax.php?action=get_companies থেকে কোম্পানির list AJAX-এ load করা হয়
// 'company' ও 'editCompany' উভয় dropdown-এ কোম্পানির নাম যোগ করা হয়
// optional callback function থাকলে data load শেষে সেটি call করা হয়
// ============================================================
function loadCompaniesDropdown(callback) {
    fetch('ajax.php?action=get_companies')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // 'company' ও 'editCompany' উভয় select element খোঁজা হচ্ছে
                const selects = [document.getElementById('company'), document.getElementById('editCompany')].filter(Boolean);
                selects.forEach(select => {
                    // প্রথম option ছাড়া বাকি সব option সরানো হচ্ছে
                    while (select.options.length > 1) select.remove(1);
                    // প্রতিটি কোম্পানির জন্য নতুন option যোগ করা হচ্ছে
                    data.companies.forEach(company => {
                        const option = new Option(company.company_name, company.company_name);
                        select.add(option);
                    });
                });
                // callback থাকলে সেটি execute করা হচ্ছে
                if (callback) callback();
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// saveBill(): নতুন বিল save করার function (AJAX POST)
// form থেকে company_name, invoice_no, bill_month, bill_year, amount, sms_count নেওয়া হয়
// ajax.php?action=save_invoice endpoint-এ POST request পাঠানো হয়
// সফল হলে form reset করা হয় এবং dashboard data reload করা হয়
// ============================================================
function saveBill() {
    const formData = new FormData();
    // form-এর প্রতিটি field থেকে value নিয়ে FormData-তে যোগ করা হচ্ছে
    formData.append('company_name', document.getElementById('company').value);
    formData.append('invoice_no', document.getElementById('invoice').value);
    formData.append('bill_month', document.getElementById('month').value);
    formData.append('bill_year', document.getElementById('year').value);
    formData.append('amount', document.getElementById('amount').value);
    formData.append('sms_count', document.getElementById('sms').value);

    // AJAX POST request পাঠানো হচ্ছে
    fetch('ajax.php?action=save_invoice', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // duplicate হলে warning, অন্যথায় success/error message
            const type = data.success ? 'success' : (data.duplicate ? 'warning' : 'error');
            showMessage(data.message, type);
            if (data.success) {
                // সফল হলে form reset ও dashboard reload করা হচ্ছে
                document.getElementById('billForm').reset();
                loadData();
            }
        })
        .catch(err => {
            showMessage('Error saving bill', 'error');
            console.error('Error:', err);
        });
}

// ============================================================
// loadBills(): pending bills AJAX-এ load করা
// ajax.php?action=get_bills থেকে সব pending bill fetch করা হয়
// allBillsData variable-এ সব bill সংরক্ষণ করা হয় (client-side filter-এর জন্য)
// year ও company-এর filter dropdown populate করা হয়
// displayBills() call করে table-এ সব bill দেখানো হয়
// ============================================================
let allBillsData = [];
function loadBills() {
    fetch('ajax.php?action=get_bills')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // সব bill data global variable-এ রাখা হচ্ছে (পরে filter-এ কাজে আসবে)
                allBillsData = data.bills;

                const yearFilter = document.getElementById('yearFilter');
                const companyFilter = document.getElementById('companyFilter');

                // year filter dropdown reset ও populate করা হচ্ছে
                yearFilter.innerHTML = '<option value="">All Years</option>';
                data.filters.years.forEach(year => {
                    yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                });

                // company filter dropdown reset ও populate করা হচ্ছে
                companyFilter.innerHTML = '<option value="">All Companies</option>';
                data.filters.companies.forEach(company => {
                    companyFilter.innerHTML += `<option value="${company}">${company}</option>`;
                });

                // সব bill table-এ দেখানো হচ্ছে
                displayBills(allBillsData);

                // select-all ও selected count reset করা
                const saCb = document.getElementById('selectAllBills');
                if(saCb){ saCb.checked = false; saCb.indeterminate = false; }
                const selBtn = document.getElementById('markSelectedBtn');
                if(selBtn) selBtn.style.display = 'none';
                const holdBtn = document.getElementById('groupHoldBtn');
                if(holdBtn) holdBtn.style.display = 'none';
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// displayBills(): bill data table-এ render করার helper function
// প্রাপ্ত bills array থেকে প্রতিটি bill-এর জন্য table row তৈরি করা হয়
// প্রতিটি row-তে complete, hold, edit, delete button যোগ করা হয়
// ============================================================
function displayBills(bills) {
    const table = document.getElementById('billsTable');
    // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
    while (table.rows.length > 1) table.deleteRow(1);

    if (bills.length > 0) {
        // প্রতিটি bill-এর জন্য একটি row তৈরি করা হচ্ছে
        bills.forEach(bill => {
            const row = table.insertRow();
            const isHold = bill.hold_status == 1;
            const cbDisabled = isHold ? 'disabled title="Hold বিল select করা যাবে না"' : '';
            row.innerHTML = `
                <td style="text-align:center;"><input type="checkbox" class="bill-checkbox" data-id="${bill.id}" ${cbDisabled} onchange="updateSelectedCount()"></td>
                <td>${bill.id}</td>
                <td>${bill.company_name}</td>
                <td>${bill.invoice_no}</td>
                <td>${bill.bill_month}</td>
                <td>${bill.bill_year}</td>
                <td>৳${parseFloat(bill.amount).toFixed(2)}</td>
                <td>${bill.sms_count || '-'}</td>
                <td>
                    <div class="actions">
                        <button class="btn-edit" style="background: var(--success);" onclick="markSingleComplete(${bill.id})" title="Mark as Complete"><i class="fas fa-check"></i></button>
                        <button class="btn-edit" style="background: var(--warning);" onclick="openHoldModal(${bill.id})" title="Hold Bill"><i class="fas fa-pause"></i></button>
                        <button class="btn-edit" onclick="editInvoice(${bill.id})"><i class="fas fa-edit"></i></button>
                        <button class="btn-delete" onclick="deleteInvoice(${bill.id})"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            `;
        });
    } else {
        // কোনো bill না থাকলে "No bills found" দেখানো হচ্ছে
        const row = table.insertRow();
        row.innerHTML = '<td colspan="9" class="no-data"><i class="fas fa-inbox"></i><br>No bills found</td>';
    }
}

// ============================================================
// updateSelectedCount(): চেক করা bill-এর সংখ্যা গণনা ও বাটন দেখানো/লুকানো
// ============================================================
function updateSelectedCount() {
    const checked = document.querySelectorAll('.bill-checkbox:checked');
    const count = checked.length;

    // Mark Selected Complete বাটন
    const btn = document.getElementById('markSelectedBtn');
    const countEl = document.getElementById('selectedCount');
    if (btn) {
        btn.style.display = count > 0 ? '' : 'none';
        if (countEl) countEl.textContent = count;
    }

    // Hold Selected বাটন — hold নয় এমন checked bill গণনা
    const holdableCount = Array.from(document.querySelectorAll('.bill-checkbox:checked'))
        .filter(cb => !cb.disabled).length;
    const holdBtn = document.getElementById('groupHoldBtn');
    const holdCountEl = document.getElementById('holdSelectedCount');
    if (holdBtn) {
        holdBtn.style.display = holdableCount > 0 ? '' : 'none';
        if (holdCountEl) holdCountEl.textContent = holdableCount;
    }

    // select-all checkbox state update
    const all = document.querySelectorAll('.bill-checkbox:not([disabled])');
    const selectAll = document.getElementById('selectAllBills');
    if (selectAll && all.length > 0) {
        const checkedEnabled = document.querySelectorAll('.bill-checkbox:not([disabled]):checked');
        selectAll.indeterminate = checkedEnabled.length > 0 && checkedEnabled.length < all.length;
        selectAll.checked = checkedEnabled.length === all.length;
    }
}

// ============================================================
// openGroupHoldModal(): নির্বাচিত pending bills group hold modal খোলা
// ============================================================
function openGroupHoldModal() {
    const holdable = Array.from(document.querySelectorAll('.bill-checkbox:not([disabled]):checked'));
    if (holdable.length === 0) { alert('Hold করার জন্য কোনো বিল select করা হয়নি!'); return; }
    const label = document.getElementById('groupHoldCountLabel');
    if (label) label.textContent = holdable.length;
    document.getElementById('groupHoldDescription').value = '';
    document.getElementById('groupHoldModal').classList.add('active');
}

// ============================================================
// submitGroupHold(): নির্বাচিত bills একসাথে hold করা (AJAX POST)
// ============================================================
function submitGroupHold(event) {
    event.preventDefault();
    const reason = document.getElementById('groupHoldDescription').value.trim();
    if (!reason) { alert('Hold reason লিখুন!'); return false; }

    const ids = Array.from(document.querySelectorAll('.bill-checkbox:not([disabled]):checked'))
        .map(cb => cb.dataset.id);

    const formData = new FormData();
    ids.forEach(id => formData.append('ids[]', id));
    formData.append('hold_description', reason);

    fetch('ajax.php?action=bulk_hold', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                closeModal('groupHoldModal');
                loadBills();
                loadHoldBills();
                loadData();
            }
        })
        .catch(() => showMessage('Error occurred', 'error'));

    return false;
}

// ============================================================
// toggleSelectAll(): header checkbox দিয়ে সব bill একসাথে select/deselect
// ============================================================
function toggleSelectAll(masterCb) {
    document.querySelectorAll('.bill-checkbox:not([disabled])').forEach(cb => {
        cb.checked = masterCb.checked;
    });
    updateSelectedCount();
}

// ============================================================
// markSelectedComplete(): চেক করা bill গুলো complete করা (AJAX POST)
// ============================================================
function markSelectedComplete() {
    const checked = document.querySelectorAll('.bill-checkbox:checked');
    if (checked.length === 0) {
        alert('কোনো বিল select করা হয়নি!');
        return;
    }
    const ids = Array.from(checked).map(cb => cb.dataset.id);
    if (!confirm(`আপনি কি ${ids.length}টি বিল COMPLETE করতে চান?`)) return;

    const formData = new FormData();
    ids.forEach(id => formData.append('ids[]', id));

    fetch('ajax.php?action=mark_selected_complete', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                loadBills();
                loadCompleteBills();
                loadData();
            }
        })
        .catch(err => {
            showMessage('Error marking selected bills as complete', 'error');
            console.error('Error:', err);
        });
}

// ============================================================
// filterBills() এর জন্য DOMContentLoaded event listener
// page load হলে filter input ও select element-গুলোতে
// event listener যোগ করা হয় যাতে পরিবর্তন হলে filterBills() call হয়
// ============================================================
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('searchInput');
    const monthFilter = document.getElementById('monthFilter');
    const yearFilter = document.getElementById('yearFilter');
    const companyFilter = document.getElementById('companyFilter');

    // প্রতিটি filter element-এ input ও change event-এ filterBills() যোগ করা হচ্ছে
    [searchInput, monthFilter, yearFilter, companyFilter].forEach(element => {
        if (element) {
            element.addEventListener('input', filterBills);
            element.addEventListener('change', filterBills);
        }
    });
});

// ============================================================
// filterBills(): client-side JavaScript-এ bill filter করা
// search text, month, year, company filter-এর মান পড়া হয়
// allBillsData array থেকে matching bill-গুলো খুঁজে বের করা হয়
// filtered result displayBills()-এ পাঠানো হয়
// ============================================================
function filterBills() {
    // filter input থেকে বর্তমান মান পড়া হচ্ছে
    const search = document.getElementById('searchInput').value.toLowerCase();
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    const company = document.getElementById('companyFilter').value;

    // প্রতিটি bill-কে filter criteria-র বিপরীতে পরীক্ষা করা হচ্ছে
    const filtered = allBillsData.filter(bill => {
        // search text কোম্পানির নাম বা invoice নম্বরে আছে কিনা
        const matchSearch = !search ||
            bill.company_name.toLowerCase().includes(search) ||
            bill.invoice_no.toLowerCase().includes(search);
        // নির্বাচিত month মিলছে কিনা
        const matchMonth = !month || bill.bill_month === month;
        // নির্বাচিত year মিলছে কিনা
        const matchYear = !year || bill.bill_year == year;
        // নির্বাচিত company মিলছে কিনা
        const matchCompany = !company || bill.company_name === company;
        // সব condition true হলেই bill দেখানো হবে
        return matchSearch && matchMonth && matchYear && matchCompany;
    });

    // filtered result table-এ দেখানো হচ্ছে
    displayBills(filtered);
}

// ============================================================
// clearFilters(): সব pending bill filter reset করা
// সব filter input খালি করা হয়
// সম্পূর্ণ allBillsData আবার দেখানো হয়
// ============================================================
function clearFilters() {
    // সব filter field খালি করা হচ্ছে
    document.getElementById('searchInput').value = '';
    document.getElementById('monthFilter').value = '';
    document.getElementById('yearFilter').value = '';
    document.getElementById('companyFilter').value = '';
    // filter ছাড়া সব bill আবার দেখানো হচ্ছে
    displayBills(allBillsData);
}

// ============================================================
// markAllComplete(): বর্তমান filter-এ দেখা সব bill একসাথে complete করা
// filter-এ নির্বাচিত সব non-hold bill-এর তালিকা তৈরি করা হয়
// user-কে confirm করতে বলা হয়, সম্মত হলে AJAX POST পাঠানো হয়
// সফল হলে bills, complete bills ও dashboard data reload করা হয়
// ============================================================
function markAllComplete() {
    // বর্তমান filter-এর মান পড়া হচ্ছে
    const search = document.getElementById('searchInput').value;
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    const company = document.getElementById('companyFilter').value;

    // filter অনুযায়ী affected bills বের করা হচ্ছে (hold-এ থাকা বাদে)
    const affectedBills = allBillsData.filter(bill => {
        const matchSearch = !search ||
            bill.company_name.toLowerCase().includes(search.toLowerCase()) ||
            bill.invoice_no.toLowerCase().includes(search.toLowerCase());
        const matchMonth = !month || bill.bill_month === month;
        const matchYear = !year || bill.bill_year == year;
        const matchCompany = !company || bill.company_name === company;
        // hold-এ থাকা bill বাদ দেওয়া হচ্ছে
        const notOnHold = bill.hold_status == 0;
        return matchSearch && matchMonth && matchYear && matchCompany && notOnHold;
    });

    // কোনো bill না থাকলে alert দেওয়া হচ্ছে
    if (affectedBills.length === 0) {
        alert('No bills match the current filters or all matching bills are on hold!');
        return;
    }

    // প্রযোজ্য filter-এর বর্ণনা তৈরি করা হচ্ছে (confirm message-এর জন্য)
    const filterDesc = [];
    if (month) filterDesc.push(`Month: ${month}`);
    if (year) filterDesc.push(`Year: ${year}`);
    if (company) filterDesc.push(`Company: ${company}`);
    if (search) filterDesc.push(`Search: "${search}"`);

    const filterText = filterDesc.length > 0 ? `\nFilters: ${filterDesc.join(', ')}` : '\nNo filters applied';

    // user-কে confirm করতে বলা হচ্ছে
    if (confirm(`Are you sure you want to mark ${affectedBills.length} bill(s) as COMPLETE?${filterText}\n\nNote: Bills on hold will be skipped.`)) {
        const formData = new FormData();
        // filter data AJAX-এ পাঠানো হচ্ছে
        formData.append('month', month);
        formData.append('year', year);
        formData.append('company', company);
        formData.append('search', search);

        // AJAX POST request পাঠানো হচ্ছে সব bill complete করার জন্য
        fetch('ajax.php?action=mark_all_complete', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                    loadBills();
                    loadCompleteBills();
                    loadData();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(err => {
                console.error('Error:', err);
                alert('Failed to mark bills as complete');
            });
    }
}

// ============================================================
// exportToExcel(): pending bills Excel (CSV) format-এ export করা
// বর্তমান filter অনুযায়ী bill বাছাই করা হয়
// CSV format-এ data তৈরি করে browser দিয়ে download করানো হয়
// ============================================================
function exportToExcel() {
    // বর্তমান filter-এর মান পড়া হচ্ছে
    const search = document.getElementById('searchInput').value.toLowerCase();
    const month = document.getElementById('monthFilter').value;
    const year = document.getElementById('yearFilter').value;
    const company = document.getElementById('companyFilter').value;

    // filter অনুযায়ী bill বাছাই করা হচ্ছে
    const filtered = allBillsData.filter(bill => {
        const matchSearch = !search ||
            bill.company_name.toLowerCase().includes(search) ||
            bill.invoice_no.toLowerCase().includes(search);
        const matchMonth = !month || bill.bill_month === month;
        const matchYear = !year || bill.bill_year == year;
        const matchCompany = !company || bill.company_name === company;
        return matchSearch && matchMonth && matchYear && matchCompany;
    });

    // কোনো bill না থাকলে export করা হবে না
    if (filtered.length === 0) {
        alert('No bills to export!');
        return;
    }

    // CSV header তৈরি করা হচ্ছে
    let csv = 'ID,Company Name,Invoice No,Bill Month,Bill Year,Amount,SMS Count,Hold Status,Created Date\n';
    // প্রতিটি bill-এর data CSV row-এ যোগ করা হচ্ছে
    filtered.forEach(bill => {
        const holdStatus = bill.hold_status == 1 ? 'On Hold' : 'Active';
        const sms = bill.sms_count || '0';
        const amount = parseFloat(bill.amount).toFixed(2);
        const createdDate = bill.created_at ? new Date(bill.created_at).toLocaleDateString() : '';
        // double quote escape করা হচ্ছে CSV-তে সমস্যা এড়াতে
        const companyName = '"' + bill.company_name.replace(/"/g, '""') + '"';
        const invoiceNo = '"' + bill.invoice_no.replace(/"/g, '""') + '"';
        csv += `${bill.id},${companyName},${invoiceNo},${bill.bill_month},${bill.bill_year},${amount},${sms},${holdStatus},${createdDate}\n`;
    });

    // file-এর নাম তৈরি করা হচ্ছে filter ও তারিখ দিয়ে
    let filename = 'bills_export_';
    if (month) filename += month + '_';
    if (year) filename += year + '_';
    if (company) filename += company.replace(/[^a-z0-9]/gi, '_') + '_';
    filename += new Date().toISOString().split('T')[0] + '.csv';

    // Blob তৈরি করে download link click করানো হচ্ছে
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement('a');
    link.setAttribute('href', URL.createObjectURL(blob));
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);

    alert(`Exported ${filtered.length} bills to Excel!\n\nFile: ${filename}`);
}

// ============================================================
// editInvoice() / openEditModal(): edit modal খোলা
// নির্দিষ্ট id-এর invoice data AJAX-এ load করা হয়
// সব field-এ আগের data বসানো হয়
// company dropdown load করার পর editModal দেখানো হয়
// ============================================================
function editInvoice(id) {
    // নির্দিষ্ট invoice-এর data AJAX-এ fetch করা হচ্ছে
    fetch(`ajax.php?action=get_invoice&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // edit form-এর field-গুলোতে আগের data বসানো হচ্ছে
                document.getElementById('editId').value = data.invoice.id;
                document.getElementById('editInvoiceNo').value = data.invoice.invoice_no;
                document.getElementById('editMonth').value = data.invoice.bill_month;
                document.getElementById('editYear').value = data.invoice.bill_year;
                document.getElementById('editAmount').value = data.invoice.amount;
                document.getElementById('editSms').value = data.invoice.sms_count || '';
                // company dropdown load হওয়ার পর modal দেখানো হচ্ছে
                loadCompaniesDropdown(function() {
                    document.getElementById('editCompany').value = data.invoice.company_name;
                    // editModal active করা হচ্ছে (দেখানো হচ্ছে)
                    document.getElementById('editModal').classList.add('active');
                });
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// updateInvoice(): বিল update করার function
// edit form-এর সব field থেকে data নিয়ে AJAX POST পাঠানো হয়
// সফল হলে modal বন্ধ করা হয় এবং bills ও dashboard reload করা হয়
// ============================================================
function updateInvoice(event) {
    event.preventDefault();

    const formData = new FormData();
    // edit form-এর সব field থেকে data নেওয়া হচ্ছে
    formData.append('id', document.getElementById('editId').value);
    formData.append('company_name', document.getElementById('editCompany').value);
    formData.append('invoice_no', document.getElementById('editInvoiceNo').value);
    formData.append('bill_month', document.getElementById('editMonth').value);
    formData.append('bill_year', document.getElementById('editYear').value);
    formData.append('amount', document.getElementById('editAmount').value);
    formData.append('sms_count', document.getElementById('editSms').value);

    // AJAX POST দিয়ে invoice update করা হচ্ছে
    fetch('ajax.php?action=update_invoice', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            // সফল বা ব্যর্থ message দেখানো হচ্ছে
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                // সফল হলে modal বন্ধ করা হচ্ছে এবং data reload করা হচ্ছে
                closeModal('editModal');
                loadBills();
                loadData();
            }
        })
        .catch(err => {
            showMessage('Error updating bill', 'error');
            console.error('Error:', err);
        });

    return false;
}

// ============================================================
// deleteInvoice() / deleteBill(): বিল delete করার function
// user-কে confirm করতে বলা হয়
// সম্মত হলে AJAX GET দিয়ে invoice delete করা হয়
// সফল হলে bills, dashboard ও hold bills reload করা হয়
// ============================================================
function deleteInvoice(id) {
    // user-কে নিশ্চিতকরণের জন্য জিজ্ঞেস করা হচ্ছে
    if (confirm('Are you sure you want to delete this bill?')) {
        // AJAX দিয়ে invoice delete করা হচ্ছে
        fetch(`ajax.php?action=delete_invoice&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                    loadBills();
                    loadData();
                    loadHoldBills();
                }
            })
            .catch(err => {
                showMessage('Error deleting bill', 'error');
                console.error('Error:', err);
            });
    }
}

// ============================================================
// openHoldModal(): hold modal খোলার function
// নির্দিষ্ট bill-এর id hold form-এ বসানো হয়
// hold description field খালি করা হয়
// holdModal দেখানো হয়
// ============================================================
function openHoldModal(id) {
    // hold করার জন্য bill-এর id form-এ রাখা হচ্ছে
    document.getElementById('holdId').value = id;
    // hold description field খালি করা হচ্ছে
    document.getElementById('holdDescription').value = '';
    // holdModal দেখানো হচ্ছে
    document.getElementById('holdModal').classList.add('active');
}

// ============================================================
// submitHold(): বিল hold করার function
// hold description সহ AJAX POST পাঠানো হয়
// সফল হলে modal বন্ধ করা হয় এবং bills, dashboard, hold bills reload করা হয়
// ============================================================
function submitHold(event) {
    event.preventDefault();

    const formData = new FormData();
    // hold করার জন্য id ও description নেওয়া হচ্ছে
    formData.append('id', document.getElementById('holdId').value);
    formData.append('hold_description', document.getElementById('holdDescription').value);

    // AJAX POST দিয়ে bill hold করা হচ্ছে
    fetch('ajax.php?action=hold_invoice', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                // সফল হলে modal বন্ধ ও সব data reload করা হচ্ছে
                closeModal('holdModal');
                loadBills();
                loadData();
                loadHoldBills();
            }
        })
        .catch(err => {
            showMessage('Error holding bill', 'error');
            console.error('Error:', err);
        });

    return false;
}

// ============================================================
// loadHoldBills(): hold-এ থাকা bills AJAX-এ load করা
// ajax.php?action=get_hold_bills থেকে data fetch করা হয়
// holdBillsTable-এ প্রতিটি hold bill-এর row তৈরি করা হয়
// প্রতিটি row-তে unhold, edit, delete button থাকে
// ============================================================
function loadHoldBills() {
    fetch('ajax.php?action=get_hold_bills')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const table = document.getElementById('holdBillsTable');
                // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
                while (table.rows.length > 1) table.deleteRow(1);

                if (data.bills.length > 0) {
                    // প্রতিটি hold bill-এর জন্য row তৈরি করা হচ্ছে
                    data.bills.forEach(bill => {
                        const row = table.insertRow();
                        row.innerHTML = `
                            <td>${bill.id}</td>
                            <td>${bill.company_name}</td>
                            <td>${bill.invoice_no}</td>
                            <td>${bill.bill_month}</td>
                            <td>${bill.bill_year}</td>
                            <td>৳${parseFloat(bill.amount).toFixed(2)}</td>
                            <td>${bill.sms_count || '-'}</td>
                            <td style="max-width: 300px;">
                                <div style="padding: 8px; background: #fff3cd; border-left: 3px solid var(--warning); border-radius: 4px;">
                                    <i class="fas fa-exclamation-triangle" style="color: var(--warning);"></i>
                                    ${bill.hold_description}
                                </div>
                            </td>
                            <td>
                                <div class="actions">
                                    <button class="btn-edit" style="background: var(--success);" onclick="unholdInvoice(${bill.id})" title="Release from Hold"><i class="fas fa-play"></i></button>
                                    <button class="btn-edit" onclick="editInvoice(${bill.id})"><i class="fas fa-edit"></i></button>
                                    <button class="btn-delete" onclick="deleteInvoice(${bill.id})"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        `;
                    });
                } else {
                    // কোনো hold bill না থাকলে বার্তা দেখানো হচ্ছে
                    const row = table.insertRow();
                    row.innerHTML = '<td colspan="9" class="no-data"><i class="fas fa-check-circle"></i><br>No bills on hold</td>';
                }
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// unholdInvoice() / unholdBill(): hold থেকে bill মুক্ত করা
// user-কে confirm করতে বলা হয়
// সম্মত হলে AJAX GET দিয়ে bill unhold করা হয়
// সফল হলে hold bills, pending bills ও dashboard reload করা হয়
// ============================================================
function unholdInvoice(id) {
    // user-কে নিশ্চিতকরণের জন্য জিজ্ঞেস করা হচ্ছে
    if (confirm('Release this bill from hold?')) {
        // AJAX দিয়ে bill unhold করা হচ্ছে
        fetch(`ajax.php?action=unhold_invoice&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                    loadHoldBills();
                    loadBills();
                    loadData();
                }
            })
            .catch(err => {
                showMessage('Error releasing bill', 'error');
                console.error('Error:', err);
            });
    }
}

// ============================================================
// markAsIncomplete(): complete bill-কে পুনরায় pending-এ নেওয়া
// user-কে confirm করতে বলা হয়
// সম্মত হলে AJAX দিয়ে bill incomplete করা হয়
// সফল হলে complete bills, pending bills ও dashboard reload করা হয়
// ============================================================
function markAsIncomplete(id) {
    // user-কে নিশ্চিতকরণের জন্য জিজ্ঞেস করা হচ্ছে
    if (confirm('Mark this bill as incomplete? It will be moved back to Pending Bills.')) {
        // AJAX দিয়ে bill incomplete করা হচ্ছে
        fetch(`ajax.php?action=mark_incomplete&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                    loadCompleteBills();
                    loadBills();
                    loadData();
                }
            })
            .catch(err => {
                showMessage('Error marking as incomplete', 'error');
                console.error('Error:', err);
            });
    }
}

// ============================================================
// markSingleComplete() / markComplete(): একটি বিল complete করা
// user-কে confirm করতে বলা হয়
// সম্মত হলে AJAX দিয়ে bill complete করা হয়
// সফল হলে pending bills, complete bills ও dashboard reload করা হয়
// ============================================================
function markSingleComplete(id) {
    // user-কে নিশ্চিতকরণের জন্য জিজ্ঞেস করা হচ্ছে
    if (confirm('Mark this bill as complete?')) {
        // AJAX দিয়ে bill complete করা হচ্ছে
        fetch(`ajax.php?action=mark_complete&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) {
                    // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                    loadBills();
                    loadCompleteBills();
                    loadData();
                }
            })
            .catch(err => {
                showMessage('Error marking as complete', 'error');
                console.error('Error:', err);
            });
    }
}

// ============================================================
// loadCompanies(): কোম্পানির management list load করা
// ajax.php?action=get_companies থেকে সব কোম্পানি fetch করা হয়
// companiesTable-এ প্রতিটি কোম্পানির row তৈরি করা হয়
// প্রতিটি row-তে edit ও delete button থাকে
// ============================================================
function loadCompanies() {
    fetch('ajax.php?action=get_companies')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const table = document.getElementById('companiesTable');
                // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
                while (table.rows.length > 1) table.deleteRow(1);

                if (data.companies.length > 0) {
                    // প্রতিটি কোম্পানির জন্য row তৈরি করা হচ্ছে
                    data.companies.forEach(company => {
                        const row = table.insertRow();
                        // created_at date format করা হচ্ছে
                        const date = new Date(company.created_at);
                        row.innerHTML = `
                            <td>${company.id}</td>
                            <td>${company.company_name}</td>
                            <td>${date.toLocaleDateString()}</td>
                            <td>
                                <div class="actions">
                                    <button class="btn-edit" onclick="editCompany(${company.id})" title="Edit Company"><i class="fas fa-edit"></i></button>
                                    <button class="btn-delete" onclick="deleteCompany(${company.id})" title="Delete Company"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        `;
                    });
                } else {
                    // কোনো কোম্পানি না থাকলে বার্তা দেখানো হচ্ছে
                    const row = table.insertRow();
                    row.innerHTML = '<td colspan="4" class="no-data"><i class="fas fa-building"></i><br>No companies added yet</td>';
                }
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// addCompany(): নতুন কোম্পানি যোগ করার function (AJAX POST)
// input field থেকে কোম্পানির নাম নেওয়া হয়
// validation করা হয় (খালি নাম দেওয়া যাবে না)
// ajax.php?action=add_company-এ POST পাঠানো হয়
// সফল হলে input field খালি করা হয় এবং companies ও dropdown reload করা হয়
// ============================================================
function addCompany() {
    // input field থেকে কোম্পানির নাম নেওয়া হচ্ছে (trim করা হচ্ছে)
    const companyName = document.getElementById('newCompanyName').value.trim();
    // খালি নাম দিলে alert দেওয়া হচ্ছে
    if (!companyName) {
        alert('Please enter company name');
        return;
    }

    const formData = new FormData();
    formData.append('company_name', companyName);

    // AJAX POST দিয়ে নতুন কোম্পানি যোগ করা হচ্ছে
    fetch('ajax.php?action=add_company', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            alert(data.message);
            if (data.success) {
                // সফল হলে input field খালি করা হচ্ছে
                document.getElementById('newCompanyName').value = '';
                // companies table ও dropdown reload করা হচ্ছে
                loadCompanies();
                loadCompaniesDropdown();
            }
        })
        .catch(err => {
            alert('Error adding company');
            console.error('Error:', err);
        });
}

// ============================================================
// editCompany(): কোম্পানি edit করার function
// নির্দিষ্ট id-এর কোম্পানির data AJAX-এ load করা হয়
// prompt() দিয়ে user-এর কাছে নতুন নাম চাওয়া হয়
// updateCompany(): নতুন নাম AJAX POST-এ save করা হয়
// সফল হলে companies, dropdown, bills ও dashboard reload করা হয়
// ============================================================
function editCompany(id) {
    // নির্দিষ্ট কোম্পানির data AJAX-এ fetch করা হচ্ছে
    fetch(`ajax.php?action=get_company&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // user-কে prompt দিয়ে নতুন নাম চাওয়া হচ্ছে
                const newName = prompt('Edit Company Name:', data.company.company_name);
                // নতুন নাম দেওয়া হলে update করা হচ্ছে
                if (newName && newName.trim() !== '') {
                    const formData = new FormData();
                    formData.append('id', id);
                    formData.append('company_name', newName.trim());

                    // AJAX POST দিয়ে কোম্পানির নাম update করা হচ্ছে
                    fetch('ajax.php?action=update_company', { method: 'POST', body: formData })
                        .then(res => res.json())
                        .then(updateData => {
                            alert(updateData.message);
                            if (updateData.success) {
                                // সফল হলে সব সংশ্লিষ্ট data reload করা হচ্ছে
                                loadCompanies();
                                loadCompaniesDropdown();
                                loadBills();
                                loadData();
                            }
                        })
                        .catch(err => {
                            alert('Error updating company');
                            console.error('Error:', err);
                        });
                }
            } else {
                alert('Error fetching company data');
            }
        })
        .catch(err => {
            alert('Error loading company');
            console.error('Error:', err);
        });
}

// ============================================================
// deleteCompany(): কোম্পানি delete করার function
// user-কে confirm করতে বলা হয়
// সম্মত হলে AJAX GET দিয়ে কোম্পানি delete করা হয়
// সফল হলে companies table ও dropdown reload করা হয়
// ============================================================
function deleteCompany(id) {
    // user-কে নিশ্চিতকরণের জন্য জিজ্ঞেস করা হচ্ছে
    if (confirm('Are you sure you want to delete this company?')) {
        // AJAX দিয়ে কোম্পানি delete করা হচ্ছে
        fetch(`ajax.php?action=delete_company&id=${id}`)
            .then(res => res.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    // সফল হলে companies table ও dropdown reload করা হচ্ছে
                    loadCompanies();
                    loadCompaniesDropdown();
                }
            })
            .catch(err => {
                alert('Error deleting company');
                console.error('Error:', err);
            });
    }
}

function loadDeletedRecords() {
    fetch('ajax.php?action=get_deleted_records')
        .then(res => res.json())
        .then(data => {
            const table = document.getElementById('deletedRecordsTable');
            if (!table) return;

            while (table.rows.length > 1) table.deleteRow(1);

            if (data.success && data.records && data.records.length > 0) {
                data.records.forEach(item => {
                    const record = item.record || {};
                    const type = item.type === 'company' ? 'Company' : 'Invoice';
                    const heading = item.type === 'company'
                        ? (record.company_name || 'Unnamed Company')
                        : (record.invoice_no || 'Invoice ' + record.id);
                    const detail = item.type === 'company'
                        ? 'Company record'
                        : `${record.bill_month || ''} ${record.bill_year || ''}`;
                    const amount = item.type === 'invoice'
                        ? `৳${parseFloat(record.amount || 0).toFixed(2)}`
                        : '—';
                    const row = table.insertRow();
                    row.innerHTML = `
                        <td>${record.id}</td>
                        <td>${type}</td>
                        <td>${heading}</td>
                        <td>${detail}</td>
                        <td>${amount}</td>
                        <td>${record.deleted_at ? new Date(record.deleted_at).toLocaleString() : '-'}</td>
                        <td>
                            <div class="actions">
                                <button class="btn-save" onclick="restoreDeletedRecord('${item.type}', ${record.id})" title="Restore ${type}"><i class="fas fa-trash-restore"></i></button>
                            </div>
                        </td>
                    `;
                });
            } else {
                const row = table.insertRow();
                row.innerHTML = '<td colspan="7" class="no-data"><i class="fas fa-trash"></i><br>No deleted records found</td>';
            }
        })
        .catch(err => {
            console.error('Error loading deleted records:', err);
            const table = document.getElementById('deletedRecordsTable');
            if (table) {
                while (table.rows.length > 1) table.deleteRow(1);
                const row = table.insertRow();
                row.innerHTML = '<td colspan="7" class="no-data"><i class="fas fa-exclamation-triangle"></i><br>Failed to load deleted records</td>';
            }
        });
}

function restoreDeletedRecord(type, id) {
    if (!confirm(`Restore this ${type.toLowerCase()}?`)) return;

    const action = type === 'company' ? 'restore_deleted_company' : 'restore_deleted_invoice';

    fetch(`ajax.php?action=${action}&id=${id}`)
        .then(res => res.json())
        .then(data => {
            showMessage(data.message, data.success ? 'success' : 'error');
            if (data.success) {
                loadDeletedRecords();
                if (typeof loadCompanies === 'function') loadCompanies();
                if (typeof loadData === 'function') loadData();
                if (typeof loadCompaniesDropdown === 'function') loadCompaniesDropdown();
            }
        })
        .catch(err => {
            showMessage('Error restoring record', 'error');
            console.error('Error restoring record:', err);
        });
}

// ============================================================
// closeModal(): modal বন্ধ করার function
// নির্দিষ্ট modalId-এর element থেকে 'active' class সরানো হয়
// ============================================================
function closeModal(modalId) {
    // modal থেকে 'active' class সরিয়ে লুকানো হচ্ছে
    document.getElementById(modalId).classList.remove('active');
}

// ============================================================
// showMessage(): success বা error message দেখানো
// messageContainer-এ styled message insert করা হয়
// 5 সেকেন্ড পরে message স্বয়ংক্রিয়ভাবে মুছে যায়
// ============================================================
function showMessage(message, type) {
    const container = document.getElementById('messageContainer');
    // message-এর ধরন অনুযায়ী icon ও style সহ HTML তৈরি করা হচ্ছে
    const icon = type === 'success' ? 'check-circle' : type === 'warning' ? 'exclamation-triangle' : 'exclamation-circle';
    container.innerHTML = `
        <div class="message ${type}">
            <i class="fas fa-${icon}"></i>
            ${message}
        </div>
    `;
    // 5 সেকেন্ড (5000ms) পরে message মুছে ফেলা হচ্ছে
    setTimeout(() => { container.innerHTML = ''; }, 5000);
}

// ============================================================
// loadCompleteBills(): complete bills AJAX-এ load করা
// ajax.php?action=get_complete_bills থেকে data fetch করা হয়
// completeBillsTable-এ সব complete bill দেখানো হয়
// year ও company filter dropdown populate করা হয়
// setupCompleteFilters() call করে filter event listener যোগ করা হয়
// ============================================================
function loadCompleteBills() {
    fetch('ajax.php?action=get_complete_bills')
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const table = document.getElementById('completeBillsTable');
                const yearFilter = document.getElementById('completeYearFilter');
                const companyFilter = document.getElementById('completeCompanyFilter');

                // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
                while (table.rows.length > 1) table.deleteRow(1);

                // year filter dropdown populate করা হচ্ছে
                yearFilter.innerHTML = '<option value="">All Years</option>';
                data.filters.years.forEach(year => {
                    yearFilter.innerHTML += `<option value="${year}">${year}</option>`;
                });

                // company filter dropdown populate করা হচ্ছে
                companyFilter.innerHTML = '<option value="">All Companies</option>';
                data.filters.companies.forEach(company => {
                    companyFilter.innerHTML += `<option value="${company}">${company}</option>`;
                });

                // entry month filter dropdown populate করা হচ্ছে (created_at থেকে)
                const entryMonthFilter = document.getElementById('completeEntryMonthFilter');
                if (entryMonthFilter) {
                    entryMonthFilter.innerHTML = '<option value="">All Entry Months</option>';
                    (data.filters.entry_months || []).forEach(em => {
                        entryMonthFilter.innerHTML += `<option value="${em.value}">${em.label}</option>`;
                    });
                }

                if (data.bills.length > 0) {
                    // প্রতিটি complete bill-এর জন্য row তৈরি করা হচ্ছে
                    data.bills.forEach(bill => {
                        const row = table.insertRow();
                        // created_at থেকে YYYY-MM format বের করা (entry month filter এর জন্য)
                        const entryYM = bill.created_at ? bill.created_at.substring(0, 7) : '';
                        row.setAttribute('data-entry-month', entryYM);
                        row.innerHTML = `
                            <td style="text-align:center;"><input type="checkbox" class="complete-checkbox" data-id="${bill.id}" onchange="updateCompleteSelectedCount()"></td>
                            <td>${bill.id}</td>
                            <td>${bill.company_name}</td>
                            <td>${bill.invoice_no}</td>
                            <td>${bill.bill_month}</td>
                            <td>${bill.bill_year}</td>
                            <td>৳${parseFloat(bill.amount).toFixed(2)}</td>
                            <td>${bill.sms_count || '-'}</td>
                            <td>
                                <div class="actions">
                                    <button class="btn-edit" style="background: var(--warning);" onclick="markAsIncomplete(${bill.id})" title="Mark as Incomplete"><i class="fas fa-undo"></i></button>
                                    <button class="btn-edit" onclick="editInvoice(${bill.id})"><i class="fas fa-edit"></i></button>
                                    <button class="btn-delete" onclick="deleteInvoice(${bill.id})"><i class="fas fa-trash"></i></button>
                                </div>
                            </td>
                        `;
                    });
                } else {
                    // কোনো complete bill না থাকলে বার্তা দেখানো হচ্ছে
                    const row = table.insertRow();
                    row.innerHTML = '<td colspan="9" class="no-data"><i class="fas fa-check-circle"></i><br>No complete bills found</td>';
                }

                // select-all ও bulk action panel reset করা
                const saCb = document.getElementById('selectAllComplete');
                if(saCb){ saCb.checked = false; saCb.indeterminate = false; }
                const bulkDiv = document.getElementById('completeBulkActions');
                if(bulkDiv) bulkDiv.style.display = 'none';

                // complete bills-এর filter event listener setup করা হচ্ছে
                setupCompleteFilters();
            }
        })
        .catch(err => console.error('Error:', err));
}

// ============================================================
// setupCompleteFilters() / filterCompleteBills(): complete bills filter setup
// complete bills section-এর filter input-গুলোতে event listener যোগ করা হয়
// filterTable() function সরাসরি DOM row-এর display toggle করে filter করে
// ============================================================
function setupCompleteFilters() {
    const searchInput = document.getElementById('completeSearchInput');
    const monthFilter = document.getElementById('completeMonthFilter');
    const yearFilter = document.getElementById('completeYearFilter');
    const companyFilter = document.getElementById('completeCompanyFilter');
    const entryMonthFilter = document.getElementById('completeEntryMonthFilter');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedMonth = monthFilter.value;
        const selectedYear = yearFilter.value;
        const selectedCompany = companyFilter.value;
        const selectedEntryMonth = entryMonthFilter ? entryMonthFilter.value : '';
        const table = document.getElementById('completeBillsTable');

        for (let i = 1; i < table.rows.length; i++) {
            const row = table.rows[i];
            // col 0=checkbox, 1=ID, 2=company, 3=invoice, 4=bill_month, 5=bill_year
            const company = row.cells[2]?.textContent || '';
            const invoice = row.cells[3]?.textContent || '';
            const month = row.cells[4]?.textContent || '';
            const year = row.cells[5]?.textContent || '';
            // entry month: row-এর data-entry-month attribute থেকে নেওয়া (YYYY-MM)
            const entryMonth = row.getAttribute('data-entry-month') || '';

            const matchesSearch = company.toLowerCase().includes(searchTerm) || invoice.toLowerCase().includes(searchTerm);
            const matchesMonth = !selectedMonth || month === selectedMonth;
            const matchesYear = !selectedYear || year === selectedYear;
            const matchesCompany = !selectedCompany || company === selectedCompany;
            const matchesEntryMonth = !selectedEntryMonth || entryMonth === selectedEntryMonth;

            row.style.display = matchesSearch && matchesMonth && matchesYear && matchesCompany && matchesEntryMonth ? '' : 'none';
        }
    }

    searchInput.addEventListener('input', filterTable);
    monthFilter.addEventListener('change', filterTable);
    yearFilter.addEventListener('change', filterTable);
    companyFilter.addEventListener('change', filterTable);
    if (entryMonthFilter) entryMonthFilter.addEventListener('change', filterTable);
}

// ============================================================
// updateCompleteSelectedCount(): complete bills-এ চেক করা সংখ্যা গণনা ও bulk panel দেখানো/লুকানো
// ============================================================
function updateCompleteSelectedCount() {
    const checked = document.querySelectorAll('.complete-checkbox:checked');
    const count = checked.length;
    const bulkDiv = document.getElementById('completeBulkActions');
    const countEl = document.getElementById('completeSelectedCount');
    if (bulkDiv) {
        bulkDiv.style.display = count > 0 ? 'flex' : 'none';
        if (countEl) countEl.textContent = count;
    }
    // select-all checkbox state update
    const all = document.querySelectorAll('.complete-checkbox');
    const selectAll = document.getElementById('selectAllComplete');
    if (selectAll && all.length > 0) {
        selectAll.indeterminate = count > 0 && count < all.length;
        selectAll.checked = count === all.length;
    }
}

// ============================================================
// toggleSelectAllComplete(): header checkbox দিয়ে সব complete bill select/deselect
// ============================================================
function toggleSelectAllComplete(masterCb) {
    document.querySelectorAll('.complete-checkbox').forEach(cb => {
        cb.checked = masterCb.checked;
    });
    updateCompleteSelectedCount();
}

// ============================================================
// bulkCompleteAction(): নির্বাচিত complete bills এ bulk action চালানো
// action: 'incomplete' | 'edit' | 'delete'
// ============================================================
function bulkCompleteAction(action) {
    const checked = document.querySelectorAll('.complete-checkbox:checked');
    if (checked.length === 0) { alert('কোনো বিল select করা হয়নি!'); return; }
    const ids = Array.from(checked).map(cb => cb.dataset.id);

    if (action === 'edit') {
        if (ids.length > 1) { alert('Edit-এর জন্য একটি মাত্র বিল select করুন।'); return; }
        editInvoice(ids[0]);
        return;
    }

    if (action === 'incomplete') {
        if (!confirm(`${ids.length}টি বিল Incomplete (Pending) করতে চান?`)) return;
        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        fetch('ajax.php?action=bulk_mark_incomplete', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) { loadCompleteBills(); loadBills(); loadData(); }
            })
            .catch(() => showMessage('Error occurred', 'error'));
    }

    if (action === 'delete') {
        if (!confirm(`${ids.length}টি বিল স্থায়ীভাবে DELETE করতে চান? এটি undo করা যাবে না!`)) return;
        const formData = new FormData();
        ids.forEach(id => formData.append('ids[]', id));
        fetch('ajax.php?action=bulk_delete', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if (data.success) { loadCompleteBills(); loadBills(); loadData(); }
            })
            .catch(() => showMessage('Error occurred', 'error'));
    }
}

// ============================================================
// clearCompleteFilters(): complete bills-এর সব filter reset করা
// সব filter input খালি করা হয়
// লুকানো সব row আবার দেখানো হয়
// ============================================================
function clearCompleteFilters() {
    // সব filter field খালি করা হচ্ছে
    document.getElementById('completeSearchInput').value = '';
    document.getElementById('completeMonthFilter').value = '';
    document.getElementById('completeYearFilter').value = '';
    document.getElementById('completeCompanyFilter').value = '';
    const emf = document.getElementById('completeEntryMonthFilter');
    if (emf) emf.value = '';

    const table = document.getElementById('completeBillsTable');
    // header row ছাড়া সব row-এর display পুনরুদ্ধার করা হচ্ছে
    for (let i = 1; i < table.rows.length; i++) {
        table.rows[i].style.display = '';
    }
}

// ============================================================
// exportCompleteBillsToExcel(): complete bills Excel (CSV) format-এ export করা
// completeBillsTable-এর দৃশ্যমান সব row (hidden নয়) থেকে data নেওয়া হয়
// Actions column বাদ দিয়ে CSV তৈরি করা হয়
// browser দিয়ে CSV file download করানো হয়
// ============================================================
function exportCompleteBillsToExcel() {
    const table = document.getElementById('completeBillsTable');
    let csv = [];

    // table-এর প্রতিটি দৃশ্যমান row process করা হচ্ছে
    for (let i = 0; i < table.rows.length; i++) {
        const row = table.rows[i];
        // লুকানো row বাদ দেওয়া হচ্ছে
        if (row.style.display !== 'none') {
            let rowData = [];
            // প্রথম column (checkbox) ও শেষ column (Actions) বাদ দিয়ে বাকি cell-এর text নেওয়া হচ্ছে
            for (let j = 1; j < row.cells.length - 1; j++) {
                rowData.push(row.cells[j].textContent);
            }
            csv.push(rowData.join(','));
        }
    }

    // Blob তৈরি করে download link click করানো হচ্ছে
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    // file-এর নামে আজকের তারিখ যোগ করা হচ্ছে
    a.download = 'complete_bills_' + new Date().toISOString().split('T')[0] + '.csv';
    a.click();
}

// ============================================================
// setQuickRange(): quick date range set করার function
// 'today', 'yesterday', 'last7days', 'last30days', 'thisMonth', 'lastMonth'
// এই option-গুলোর মধ্যে যেকোনো একটি select করলে
// fromDate ও toDate field-এ স্বয়ংক্রিয়ভাবে তারিখ বসানো হয়
// ============================================================
function setQuickRange(range) {
    const today = new Date();
    const fromDate = document.getElementById('fromDate');
    const toDate = document.getElementById('toDate');
    let from, to;

    // নির্বাচিত range অনুযায়ী from ও to তারিখ নির্ধারণ করা হচ্ছে
    switch (range) {
        case 'today':
            // আজকের তারিখ
            from = to = today;
            break;
        case 'yesterday':
            // গতকালের তারিখ
            from = to = new Date(today.setDate(today.getDate() - 1));
            break;
        case 'last7days':
            // শেষ ৭ দিন
            from = new Date(today.setDate(today.getDate() - 6));
            to = new Date();
            break;
        case 'last30days':
            // শেষ ৩০ দিন
            from = new Date(today.setDate(today.getDate() - 29));
            to = new Date();
            break;
        case 'thisMonth':
            // এই মাসের প্রথম থেকে শেষ দিন
            from = new Date(today.getFullYear(), today.getMonth(), 1);
            to = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            break;
        case 'lastMonth':
            // গত মাসের প্রথম থেকে শেষ দিন
            from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
            to = new Date(today.getFullYear(), today.getMonth(), 0);
            break;
    }

    // তারিখ input field-এ ISO format-এ (YYYY-MM-DD) বসানো হচ্ছে
    fromDate.value = from.toISOString().split('T')[0];
    toDate.value = to.toISOString().split('T')[0];
}

// ============================================================
// generateDateRangeReport(): date range report generate করা
// fromDate, toDate ও optional company filter নেওয়া হয়
// validation করা হয় (উভয় তারিখ থাকতে হবে, from <= to)
// AJAX GET দিয়ে report data fetch করা হয়
// displayDateRangeReport() দিয়ে result দেখানো হয়
// ============================================================
function generateDateRangeReport() {
    // filter field থেকে তারিখ ও কোম্পানির মান নেওয়া হচ্ছে
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    const company = document.getElementById('reportCompanyFilter').value;

    // উভয় তারিখ দেওয়া না হলে alert
    if (!fromDate || !toDate) {
        alert('Please select both from and to dates');
        return;
    }

    // from date, to date-এর পরে হলে alert
    if (new Date(fromDate) > new Date(toDate)) {
        alert('From date cannot be greater than to date');
        return;
    }

    // AJAX URL তৈরি করা হচ্ছে, company filter থাকলে যোগ করা হচ্ছে
    let url = `ajax.php?action=get_date_range_report&from_date=${fromDate}&to_date=${toDate}`;
    if (company) url += `&company=${encodeURIComponent(company)}`;

    // AJAX GET দিয়ে report data fetch করা হচ্ছে
    fetch(url)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                // সফল হলে report display করা হচ্ছে
                displayDateRangeReport(data, fromDate, toDate);
            } else {
                alert(data.message || 'Error generating report');
            }
        })
        .catch(err => {
            alert('Error generating report');
            console.error('Error:', err);
        });
}

// ============================================================
// displayDateRangeReport(): date range report-এর result দেখানো
// summary cards-এ মোট bills, amount, internet, SMS, hold, complete count দেখানো হয়
// reportBillsTable-এ সব bill-এর বিস্তারিত দেখানো হয়
// reportCompanyBreakdown table-এ company-wise breakdown দেখানো হয়
// smooth scroll করে report section-এ নিয়ে যাওয়া হয়
// ============================================================
function displayDateRangeReport(data, fromDate, toDate) {
    const reportResults = document.getElementById('reportResults');
    // report section দেখানো হচ্ছে
    reportResults.style.display = 'block';

    const summaryCards = document.getElementById('reportSummaryCards');
    // summary stat card-গুলো HTML-এ তৈরি করা হচ্ছে
    summaryCards.innerHTML = `
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <i class="fas fa-file-invoice"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.total_bills}</h3><p>Total Bills</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-money-bill-wave"></i>
            </div>
            <div class="stat-content"><h3>৳${parseFloat(data.summary.total_amount).toFixed(2)}</h3><p>Total Amount</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                <i class="fas fa-wifi"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.internet_bills}</h3><p>Internet Bills</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);">
                <i class="fas fa-sms"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.sms_bills}</h3><p>SMS Bills</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="fas fa-pause-circle"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.hold_bills}</h3><p>Hold Bills</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.complete_bills}</h3><p>Complete Bills</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);">
                <i class="fas fa-building"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.unique_companies}</h3><p>Companies</p></div>
        </div>
        <div class="stat-box">
            <div class="stat-icon" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="fas fa-sms"></i>
            </div>
            <div class="stat-content"><h3>${data.summary.total_sms}</h3><p>Total SMS</p></div>
        </div>
    `;

    const billsTable = document.getElementById('reportBillsTable');
    // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
    while (billsTable.rows.length > 1) billsTable.deleteRow(1);

    if (data.bills.length > 0) {
        // প্রতিটি bill-এর জন্য report table-এ row তৈরি করা হচ্ছে
        data.bills.forEach(bill => {
            const row = billsTable.insertRow();
            const date = new Date(bill.created_at);
            row.innerHTML = `
                <td>${bill.id}</td>
                <td>${bill.company_name}</td>
                <td>${bill.invoice_no}</td>
                <td>${bill.bill_month}</td>
                <td>${bill.bill_year}</td>
                <td>৳${parseFloat(bill.amount).toFixed(2)}</td>
                <td>${bill.sms_count || '-'}</td>
                <td>${date.toLocaleDateString()}</td>
            `;
        });
    } else {
        // কোনো bill না থাকলে বার্তা দেখানো হচ্ছে
        const row = billsTable.insertRow();
        row.innerHTML = '<td colspan="8" class="no-data"><i class="fas fa-inbox"></i><br>No bills found in this date range</td>';
    }

    const breakdownTable = document.getElementById('reportCompanyBreakdown');
    // header row ছাড়া বাকি সব row মুছে ফেলা হচ্ছে
    while (breakdownTable.rows.length > 1) breakdownTable.deleteRow(1);

    if (data.company_breakdown.length > 0) {
        // প্রতিটি কোম্পানির breakdown data row-এ দেখানো হচ্ছে
        data.company_breakdown.forEach(item => {
            const row = breakdownTable.insertRow();
            // প্রতি bill-এর গড় amount হিসাব করা হচ্ছে
            const average = item.total / item.count;
            row.innerHTML = `
                <td>${item.company}</td>
                <td>${item.count}</td>
                <td>৳${parseFloat(item.total).toFixed(2)}</td>
                <td>৳${parseFloat(average).toFixed(2)}</td>
            `;
        });
    } else {
        // কোনো data না থাকলে বার্তা দেখানো হচ্ছে
        const row = breakdownTable.insertRow();
        row.innerHTML = '<td colspan="4" class="no-data"><i class="fas fa-building"></i><br>No company data available</td>';
    }

    // smooth scroll করে report section-এ নিয়ে যাওয়া হচ্ছে
    reportResults.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ============================================================
// exportReportToPDF(): report PDF format-এ export করা
// বর্তমানে এই feature সম্পূর্ণ implement হয়নি
// user-কে জানানো হয় এবং print dialog খোলা হয়
// ============================================================
function exportReportToPDF() {
    // PDF feature এখনো আসেনি, তাই print করার পরামর্শ দেওয়া হচ্ছে
    alert('PDF export feature coming soon! For now, please use the Excel export or print this page.');
    // browser-এর print dialog খোলা হচ্ছে
    window.print();
}

// ============================================================
// exportReportToExcel(): date range report Excel (CSV) format-এ export করা
// fromDate ও toDate নেওয়া হয়
// reportBillsTable ও reportCompanyBreakdown উভয় table-এর data CSV-তে যোগ করা হয়
// browser দিয়ে CSV file download করানো হয়
// ============================================================
function exportReportToExcel() {
    // date range filter-এর তারিখ নেওয়া হচ্ছে
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;

    // CSV-এর শুরুতে date range header যোগ করা হচ্ছে
    let csv = [`Date Range Report: ${fromDate} to ${toDate}\n\n`];

    // reportBillsTable-এর সব row CSV-তে যোগ করা হচ্ছে
    const billsTable = document.getElementById('reportBillsTable');
    csv.push('Detailed Bills\n');
    for (let i = 0; i < billsTable.rows.length; i++) {
        const row = billsTable.rows[i];
        let rowData = [];
        for (let j = 0; j < row.cells.length; j++) rowData.push(row.cells[j].textContent);
        csv.push(rowData.join(','));
    }

    // reportCompanyBreakdown table-এর সব row CSV-তে যোগ করা হচ্ছে
    csv.push('\n\nCompany-wise Breakdown\n');
    const breakdownTable = document.getElementById('reportCompanyBreakdown');
    for (let i = 0; i < breakdownTable.rows.length; i++) {
        const row = breakdownTable.rows[i];
        let rowData = [];
        for (let j = 0; j < row.cells.length; j++) rowData.push(row.cells[j].textContent);
        csv.push(rowData.join(','));
    }

    // Blob তৈরি করে download link click করানো হচ্ছে
    const blob = new Blob([csv.join('\n')], { type: 'text/csv' });
    const a = document.createElement('a');
    a.href = window.URL.createObjectURL(blob);
    // file-এর নামে date range যোগ করা হচ্ছে
    a.download = `date_range_report_${fromDate}_to_${toDate}.csv`;
    a.click();
}

// ============================================================
// showCompanyBills(): company box click করলে সেই কোম্পানির সব বিল modal-এ দেখানো
// ============================================================
function showCompanyBills(companyName) {
    document.getElementById('companyBillsTitle').textContent = companyName;
    document.getElementById('companyBillsSummary').innerHTML = '';
    document.getElementById('companyBillsBody').innerHTML =
        '<tr><td colspan="7" class="cbm-loading"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>';
    document.getElementById('companyBillsModal').style.display = 'block';
    document.body.style.overflow = 'hidden';

    const dark = document.body.classList.contains('dark-mode');
    const statusMap = {
        complete: { label: 'Complete', icon: 'fa-check-circle', bg: dark ? 'rgba(16,185,129,0.2)' : '#d1fae5', color: dark ? '#6ee7b7' : '#065f46' },
        hold:     { label: 'Hold',     icon: 'fa-pause-circle', bg: dark ? 'rgba(245,158,11,0.2)' : '#fef3c7', color: dark ? '#fcd34d' : '#92400e' },
        pending:  { label: 'Pending',  icon: 'fa-clock',        bg: dark ? 'rgba(239,68,68,0.2)'  : '#fee2e2', color: dark ? '#fca5a5' : '#991b1b' },
    };

    fetch(`ajax.php?action=get_company_bills&company=${encodeURIComponent(companyName)}`)
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('companyBillsBody');
            if (!data.success || data.bills.length === 0) {
                tbody.innerHTML = '<tr><td colspan="10" class="cbm-loading"><i class="fas fa-inbox"></i><br>কোনো বিল পাওয়া যায়নি</td></tr>';
                return;
            }

            // Summary strip
            const counts = { complete: 0, hold: 0, pending: 0 };
            let totalAmt = 0;
            data.bills.forEach(b => { counts[b.bill_status] = (counts[b.bill_status] || 0) + 1; totalAmt += parseFloat(b.amount); });

            const summaryEl = document.getElementById('companyBillsSummary');
            const summaryBg = dark ? '#1e293b' : '#f8f9fa';
            summaryEl.style.background = summaryBg;
            summaryEl.innerHTML = `
                <span class="cbm-badge" style="background:${dark?'rgba(99,102,241,0.2)':'#ede9fe'};color:${dark?'#a5b4fc':'#4c1d95'}">
                    <i class="fas fa-file-invoice"></i> মোট ${data.bills.length} বিল
                </span>
                <span class="cbm-badge" style="background:${dark?'rgba(16,185,129,0.2)':'#d1fae5'};color:${dark?'#6ee7b7':'#065f46'}">
                    <i class="fas fa-check-circle"></i> Complete: ${counts.complete}
                </span>
                <span class="cbm-badge" style="background:${dark?'rgba(245,158,11,0.2)':'#fef3c7'};color:${dark?'#fcd34d':'#92400e'}">
                    <i class="fas fa-pause-circle"></i> Hold: ${counts.hold}
                </span>
                <span class="cbm-badge" style="background:${dark?'rgba(239,68,68,0.2)':'#fee2e2'};color:${dark?'#fca5a5':'#991b1b'}">
                    <i class="fas fa-clock"></i> Pending: ${counts.pending}
                </span>
                <span class="cbm-badge" style="background:${dark?'rgba(99,102,241,0.15)':'#e0e7ff'};color:${dark?'#818cf8':'#3730a3'}; margin-left:auto">
                    <i class="fas fa-money-bill-wave"></i> মোট: ৳${totalAmt.toFixed(2)}
                </span>`;

            // Rows
            const fmt = d => d ? new Date(d).toLocaleDateString('en-GB', {day:'2-digit', month:'short', year:'numeric'}) : '—';
            tbody.innerHTML = data.bills.map(bill => {
                const s = statusMap[bill.bill_status] || statusMap.pending;
                const statusHtml = `<span class="cbm-status" style="background:${s.bg};color:${s.color}"><i class="fas ${s.icon}"></i>${s.label}</span>`;

                const entryDateHtml = `<span style="font-size:11px;color:${dark?'#94a3b8':'#64748b'}">${fmt(bill.created_at)}</span>`;

                let holdDateHtml = '—';
                if (bill.bill_status === 'hold' && bill.hold_date) {
                    holdDateHtml = `<span style="font-size:11px;color:${dark?'#fcd34d':'#92400e'};white-space:nowrap">${fmt(bill.hold_date)}</span>`;
                }

                let completeDateHtml = '—';
                if (bill.bill_status === 'complete' && bill.complete_date) {
                    completeDateHtml = `<span style="font-size:11px;color:${dark?'#6ee7b7':'#065f46'};white-space:nowrap">${fmt(bill.complete_date)}</span>`;
                }

                return `<tr>
                    <td>${bill.id}</td>
                    <td><strong>${bill.invoice_no}</strong></td>
                    <td>${bill.bill_month}</td>
                    <td>${bill.bill_year}</td>
                    <td><strong>৳${parseFloat(bill.amount).toFixed(2)}</strong></td>
                    <td>${bill.sms_count || '—'}</td>
                    <td>${statusHtml}</td>
                    <td>${entryDateHtml}</td>
                    <td>${holdDateHtml}</td>
                    <td>${completeDateHtml}</td>
                </tr>`;
            }).join('');
        })
        .catch(() => {
            document.getElementById('companyBillsBody').innerHTML =
                '<tr><td colspan="10" class="cbm-loading" style="color:#ef4444"><i class="fas fa-exclamation-circle"></i> Error loading bills</td></tr>';
        });
}

function closeCompanyBillsModal() {
    document.getElementById('companyBillsModal').style.display = 'none';
    document.body.style.overflow = '';
}

// modal-এর বাইরে click করলে বন্ধ হবে
document.addEventListener('click', function(e) {
    const modal = document.getElementById('companyBillsModal');
    if (modal && e.target === modal) closeCompanyBillsModal();
});

