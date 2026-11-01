<?php
// Session শুরু করা — লগইন না করলে unauthorized access block করা
session_start();
if(!isset($_SESSION['user'])) die(json_encode(['success'=>false,'message'=>'Unauthorized']));

include "db.php";

function getMatchingBillCriteria($conn, $id) {
    $id = intval($id);
    $stmt = $conn->prepare("SELECT company_name, bill_month, bill_year FROM invoices WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $stmt->close();
        return null;
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return [
        'company_name' => $row['company_name'],
        'bill_month' => $row['bill_month'],
        'bill_year' => $row['bill_year']
    ];
}

function updateMatchingBillsStatus($conn, $id, $completeStatus) {
    $criteria = getMatchingBillCriteria($conn, $id);
    if (!$criteria) {
        return 0;
    }

    if ($completeStatus === 1) {
        $stmt = $conn->prepare("UPDATE invoices SET complete_status = 1, complete_date = NOW() WHERE company_name = ? AND bill_month = ? AND bill_year = ? AND hold_status = 0 AND complete_status = 0");
        $stmt->bind_param("ssi", $criteria['company_name'], $criteria['bill_month'], $criteria['bill_year']);
    } else {
        $stmt = $conn->prepare("UPDATE invoices SET complete_status = 0, complete_date = NULL WHERE company_name = ? AND bill_month = ? AND bill_year = ?");
        $stmt->bind_param("ssi", $criteria['company_name'], $criteria['bill_month'], $criteria['bill_year']);
    }

    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    return $affected;
}

function updateSelectedMatchingBillsStatus($conn, $ids, $completeStatus) {
    $ids = array_values(array_filter(array_map('intval', (array)$ids), fn($id) => $id > 0));
    if (empty($ids)) {
        return 0;
    }

    $seen = [];
    $count = 0;

    foreach ($ids as $id) {
        $criteria = getMatchingBillCriteria($conn, $id);
        if (!$criteria) {
            continue;
        }

        $key = $criteria['company_name'] . '|' . $criteria['bill_month'] . '|' . $criteria['bill_year'];
        if (isset($seen[$key])) {
            continue;
        }
        $seen[$key] = true;

        if ($completeStatus === 1) {
            $stmt = $conn->prepare("UPDATE invoices SET complete_status = 1, complete_date = NOW() WHERE company_name = ? AND bill_month = ? AND bill_year = ? AND hold_status = 0 AND complete_status = 0");
            $stmt->bind_param("ssi", $criteria['company_name'], $criteria['bill_month'], $criteria['bill_year']);
        } else {
            $stmt = $conn->prepare("UPDATE invoices SET complete_status = 0, complete_date = NULL WHERE company_name = ? AND bill_month = ? AND bill_year = ?");
            $stmt->bind_param("ssi", $criteria['company_name'], $criteria['bill_month'], $criteria['bill_year']);
        }

        $stmt->execute();
        $count += $stmt->affected_rows;
        $stmt->close();
    }

    return $count;
}

$action = $_GET['action'] ?? '';

// dashboard statistics AJAX এ পাঠানো — মোট বিল, মোট amount, এই মাসের কোম্পানি, গত মাসের কোম্পানি, internet bills count, SMS bills count, hold bills (last 3 months), company status (has_bill, has_hold), recent 10 bills
if($action == 'get_data'){
    // invoices টেবিল থেকে মোট বিল সংখ্যা, মোট amount এবং মোট SMS count বের করা
    $stats = $conn->query("SELECT
        COUNT(*) as bills,
        COALESCE(SUM(amount),0) as total,
        COALESCE(SUM(sms_count),0) as sms
        FROM invoices WHERE deleted_at IS NULL");

    if($stats) {
        $statsData = $stats->fetch_assoc();

        // Get current month and year
        $currentMonth = date('F'); // e.g., "January"
        $currentYear = date('Y');  // e.g., "2025"

        // এই মাসে কতটি আলাদা কোম্পানির bill_month অনুযায়ী বিল আছে তা গণনা করা
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT company_name) as count FROM invoices WHERE bill_month = ? AND bill_year = ?");
        $stmt->bind_param("ss", $currentMonth, $currentYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $companiesThisMonth = 0;
        if($result) {
            $row = $result->fetch_assoc();
            $companiesThisMonth = $row['count'];
        }
        $stmt->close();

        // Get unique companies count for previous month (based on bill_month)
        // গত মাসের তারিখ বের করা এবং বছর পরিবর্তনও handle করা
        $previousMonthDate = strtotime('-1 month');
        $previousMonth = date('F', $previousMonthDate); // e.g., "December"
        $previousYear = date('Y', $previousMonthDate);  // Handle year change

        // গত মাসে কতটি আলাদা কোম্পানির bill_month অনুযায়ী বিল আছে তা গণনা করা
        $stmt = $conn->prepare("SELECT COUNT(DISTINCT company_name) as count FROM invoices WHERE bill_month = ? AND bill_year = ?");
        $stmt->bind_param("ss", $previousMonth, $previousYear);
        $stmt->execute();
        $result = $stmt->get_result();
        $companiesPreviousMonth = 0;
        if($result) {
            $row = $result->fetch_assoc();
            $companiesPreviousMonth = $row['count'];
        }
        $stmt->close();

        // Get entries count for current calendar month (based on created_at)
        // এই calendar মাসের প্রথম ও শেষ দিন নির্ধারণ করা (created_at অনুযায়ী)
        $firstDayOfMonth = date('Y-m-01 00:00:00');
        $lastDayOfMonth = date('Y-m-t 23:59:59');

        // এই মাসে created_at অনুযায়ী মোট কতটি entry হয়েছে তা গণনা করা
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND created_at >= ? AND created_at <= ?");
        $stmt->bind_param("ss", $firstDayOfMonth, $lastDayOfMonth);
        $stmt->execute();
        $result = $stmt->get_result();
        $thisMonthEntry = 0;
        if($result) {
            $row = $result->fetch_assoc();
            $thisMonthEntry = $row['count'];
        }
        $stmt->close();

        // Count Internet bills (bills without SMS or sms_count = 0 or NULL)
        // Internet বিল গণনা — যেসব বিলে SMS নেই অর্থাৎ sms_count NULL বা 0
        $internetBills = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND (sms_count IS NULL OR sms_count = 0)");
        $internetBillsCount = 0;
        if($internetBills) {
            $row = $internetBills->fetch_assoc();
            $internetBillsCount = $row['count'];
        }

        // Count SMS bills (bills with SMS, sms_count > 0)
        // SMS বিল গণনা — যেসব বিলে sms_count শূন্যের বেশি
        $smsBills = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND sms_count IS NOT NULL AND sms_count > 0");
        $smsBillsCount = 0;
        if($smsBills) {
            $row = $smsBills->fetch_assoc();
            $smsBillsCount = $row['count'];
        }

        // Get all companies and check their bill status for current month
        // প্রতিটি কোম্পানির জন্য এই মাসে বিল আছে কিনা এবং hold আছে কিনা তা যাচাই করা
        $companyStatus = [];
        $companiesResult = $conn->query("SELECT company_name FROM companies ORDER BY company_name");
        if($companiesResult) {
            while($company = $companiesResult->fetch_assoc()) {
                $companyName = $company['company_name'];

                // Check if this company has ANY bill entry in current calendar month (by created_at)
                // এই কোম্পানির এই মাসে (created_at অনুযায়ী) কোনো বিল আছে কিনা তা দেখা
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND company_name = ? AND created_at >= ? AND created_at <= ?");
                $stmt->bind_param("sss", $companyName, $firstDayOfMonth, $lastDayOfMonth);
                $stmt->execute();
                $billResult = $stmt->get_result();
                $hasBill = false;
                if($billResult) {
                    $billRow = $billResult->fetch_assoc();
                    $hasBill = $billRow['count'] > 0;
                }
                $stmt->close();

                // Check if this company has any HOLD bills in current month
                // এই কোম্পানির এই মাসে কোনো hold বিল আছে কিনা তা দেখা
                $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND company_name = ? AND created_at >= ? AND created_at <= ? AND hold_status = 1");
                $stmt->bind_param("sss", $companyName, $firstDayOfMonth, $lastDayOfMonth);
                $stmt->execute();
                $holdResult = $stmt->get_result();
                $hasHold = false;
                if($holdResult) {
                    $holdRow = $holdResult->fetch_assoc();
                    $hasHold = $holdRow['count'] > 0;
                }
                $stmt->close();

                // কোম্পানির bill ও hold status array তে যোগ করা
                $companyStatus[] = [
                    'company_name' => $companyName,
                    'has_bill' => $hasBill,
                    'has_hold' => $hasHold
                ];
            }
        }

        // Get hold bills count for last 3 months (by created_at/entry date)
        // গত ৩ মাসের মধ্যে কতটি বিল hold অবস্থায় আছে তা গণনা করা
        $threeMonthsAgo = date('Y-m-d 00:00:00', strtotime('-3 months'));
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND hold_status = 1 AND created_at >= ?");
        $stmt->bind_param("s", $threeMonthsAgo);
        $stmt->execute();
        $result = $stmt->get_result();
        $holdBillsLast3Months = 0;
        if($result) {
            $row = $result->fetch_assoc();
            $holdBillsLast3Months = $row['count'];
        }
        $stmt->close();

        // সর্বশেষ ১০টি বিল বের করা (id অনুযায়ী নামানো ক্রমে)
        $recent = $conn->query("SELECT * FROM invoices WHERE deleted_at IS NULL ORDER BY id DESC LIMIT 10");
        $recentData = [];
        if($recent) {
            while($row = $recent->fetch_assoc()) $recentData[] = $row;
        }

        // মোট complete বিল গণনা
        $completeBillsRow = $conn->query("SELECT COUNT(*) as count FROM invoices WHERE deleted_at IS NULL AND complete_status=1");
        $completeBillsCount = 0;
        if($completeBillsRow) {
            $row = $completeBillsRow->fetch_assoc();
            $completeBillsCount = $row['count'];
        }

        // সব statistics JSON আকারে AJAX এ পাঠানো
        echo json_encode([
            'success' => true,
            'bills' => $statsData['bills'],
            'total' => number_format($statsData['total'],2),
            'sms' => number_format($statsData['sms']),
            'companies_this_month' => $companiesThisMonth,
            'companies_previous_month' => $companiesPreviousMonth,
            'this_month_entry' => $thisMonthEntry,
            'internet_bills' => $internetBillsCount,
            'sms_bills' => $smsBillsCount,
            'hold_bills_last_3_months' => $holdBillsLast3Months,
            'complete_bills_count' => $completeBillsCount,
            'company_status' => $companyStatus,
            'recent' => $recentData
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Error fetching data']);
    }
}

// নির্দিষ্ট কোম্পানির সব বিল (pending + hold + complete) AJAX এ পাঠানো
elseif($action == 'get_company_bills'){
    $company = isset($_GET['company']) ? $conn->real_escape_string($_GET['company']) : '';
    if(!$company){
        echo json_encode(['success'=>false,'message'=>'Company name required']);
    } else {
        $bills = $conn->query("SELECT *,
            CASE WHEN complete_status=1 THEN 'complete' WHEN hold_status=1 THEN 'hold' ELSE 'pending' END as bill_status
            FROM invoices WHERE company_name='$company' ORDER BY id DESC");
        $billsData = [];
        if($bills) while($row = $bills->fetch_assoc()) $billsData[] = $row;
        echo json_encode(['success'=>true,'bills'=>$billsData]);
    }
}

// pending bills (complete_status=0 AND hold_status=0) AJAX এ পাঠানো, year/company filters সহ
elseif($action == 'get_bills'){
    // Only show bills that are NOT complete and NOT on hold (active/pending bills only)
    // শুধুমাত্র pending বিল দেখানো — যেগুলো complete নয় এবং hold নয়
    $bills = $conn->query("SELECT * FROM invoices WHERE deleted_at IS NULL AND complete_status=0 AND hold_status=0 ORDER BY id DESC");
    $billsData = [];
    if($bills) {
        while($row = $bills->fetch_assoc()) $billsData[] = $row;
    }

    // Get years and companies from active bills only
    // শুধুমাত্র active pending বিল থেকে বছর এবং কোম্পানির list বের করা (filter এর জন্য)
    $years = $conn->query("SELECT DISTINCT bill_year FROM invoices WHERE deleted_at IS NULL AND complete_status=0 AND hold_status=0 ORDER BY bill_year DESC");
    $companies = $conn->query("SELECT DISTINCT company_name FROM invoices WHERE deleted_at IS NULL AND complete_status=0 AND hold_status=0 ORDER BY company_name");

    $yearsData = [];
    if($years) {
        while($row=$years->fetch_assoc()) $yearsData[] = $row['bill_year'];
    }

    $companiesData = [];
    if($companies) {
        while($row=$companies->fetch_assoc()) $companiesData[] = $row['company_name'];
    }

    // pending বিল এবং filter data JSON আকারে পাঠানো
    echo json_encode([
        'success' => true,
        'bills' => $billsData,
        'filters' => [
            'years' => $yearsData,
            'companies' => $companiesData
        ]
    ]);
}

// Company Management Actions (BEFORE save_invoice)
// সব কোম্পানির list AJAX এ পাঠানো
elseif($action == 'get_companies'){
    // companies টেবিল থেকে নাম অনুযায়ী সাজানো সব কোম্পানি বের করা
    $result = $conn->query("SELECT * FROM companies WHERE deleted_at IS NULL ORDER BY company_name");
    $companies = [];
    if($result) {
        while($row = $result->fetch_assoc()) {
            $companies[] = $row;
        }
    }
    echo json_encode(['success'=>true, 'companies'=>$companies]);
}

// নতুন কোম্পানি যোগ করা, duplicate check সহ
elseif($action == 'add_company' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // POST থেকে কোম্পানির নাম নেওয়া এবং whitespace সরানো
    $company = trim($_POST['company_name'] ?? '');

    // কোম্পানির নাম খালি থাকলে error দেওয়া
    if(empty($company)){
        echo json_encode(['success'=>false,'message'=>'Company name is required']);
        exit;
    }

    // একই নামের কোম্পানি আগে থেকে আছে কিনা duplicate check করা
    $stmt = $conn->prepare("SELECT id FROM companies WHERE deleted_at IS NULL AND company_name=?");
    $stmt->bind_param("s", $company);
    $stmt->execute();
    $result = $stmt->get_result();

    // যদি duplicate পাওয়া যায় তাহলে error message দিয়ে বের হয়ে যাওয়া
    if($result->num_rows > 0){
        echo json_encode(['success'=>false,'message'=>'Company already exists']);
        exit;
    }

    // নতুন কোম্পানি companies টেবিলে insert করা
    $stmt = $conn->prepare("INSERT INTO companies (company_name) VALUES (?)");
    $stmt->bind_param("s", $company);

    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'Company added successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Error adding company']);
    }
    $stmt->close();
}

// নির্দিষ্ট company-র details AJAX এ পাঠানো (id দিয়ে খোঁজা)
elseif($action == 'get_company' && isset($_GET['id'])){
    // GET থেকে id নিয়ে integer এ রূপান্তর করা (SQL injection প্রতিরোধ)
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM companies WHERE deleted_at IS NULL AND id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // কোম্পানি পাওয়া গেলে details পাঠানো, না পেলে error
    if($result->num_rows > 0){
        echo json_encode(['success'=>true,'company'=>$result->fetch_assoc()]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Company not found']);
    }
    $stmt->close();
}

elseif($action == 'get_deleted_records'){
    $deletedInvoices = [];
    $invQuery = $conn->query("SELECT * FROM invoices WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
    if($invQuery){
        while($row = $invQuery->fetch_assoc()) $deletedInvoices[] = ['type'=>'invoice', 'record'=>$row];
    }

    $deletedCompanies = [];
    $companyQuery = $conn->query("SELECT * FROM companies WHERE deleted_at IS NOT NULL ORDER BY deleted_at DESC");
    if($companyQuery){
        while($row = $companyQuery->fetch_assoc()) $deletedCompanies[] = ['type'=>'company', 'record'=>$row];
    }

    $records = array_merge($deletedInvoices, $deletedCompanies);
    usort($records, function($a, $b){
        $ad = $a['record']['deleted_at'] ?? '0000-00-00 00:00:00';
        $bd = $b['record']['deleted_at'] ?? '0000-00-00 00:00:00';
        return strcmp($bd, $ad);
    });

    echo json_encode(['success'=>true, 'records'=>$records]);
}

elseif($action == 'restore_deleted_invoice' && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE invoices SET deleted_at = NULL WHERE id=? AND deleted_at IS NOT NULL");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Invoice restored successfully']);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Failed to restore invoice']);
    }
    $stmt->close();
}

elseif($action == 'restore_deleted_company' && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("UPDATE companies SET deleted_at = NULL WHERE id=? AND deleted_at IS NOT NULL");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Company restored successfully']);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Failed to restore company']);
    }
    $stmt->close();
}

// কোম্পানির নাম update করা এবং invoices টেবিলেও নাম update করা
elseif($action == 'update_company' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // POST থেকে id এবং নতুন কোম্পানির নাম নেওয়া
    $id = intval($_POST['id']);
    $company = trim($_POST['company_name'] ?? '');

    // নতুন নাম খালি থাকলে error দেওয়া
    if(empty($company)){
        echo json_encode(['success'=>false,'message'=>'Company name is required']);
        exit;
    }

    // Check if company name exists for different ID
    // অন্য কোনো কোম্পানির একই নাম আছে কিনা duplicate check করা (নিজের id বাদ দিয়ে)
    $stmt = $conn->prepare("SELECT id FROM companies WHERE deleted_at IS NULL AND company_name=? AND id!=?");
    $stmt->bind_param("si", $company, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // duplicate থাকলে error দেওয়া
    if($result->num_rows > 0){
        echo json_encode(['success'=>false,'message'=>'Company name already exists']);
        exit;
    }

    // Get old company name first
    // invoices update করার জন্য আগের কোম্পানির নাম বের করা
    $stmt = $conn->prepare("SELECT company_name FROM companies WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $oldNameResult = $stmt->get_result();
    $oldName = '';
    if($oldNameResult->num_rows > 0){
        $oldRow = $oldNameResult->fetch_assoc();
        $oldName = $oldRow['company_name'];
    }
    $stmt->close();

    // Update company name
    // companies টেবিলে কোম্পানির নাম update করা
    $stmt = $conn->prepare("UPDATE companies SET company_name=? WHERE id=?");
    $stmt->bind_param("si", $company, $id);

    if($stmt->execute()){
        // Also update the company name in all invoices
        // invoices টেবিলেও পুরনো নামের জায়গায় নতুন নাম update করা (সামঞ্জস্য রক্ষার জন্য)
        if(!empty($oldName)){
            $updateInvoices = $conn->prepare("UPDATE invoices SET company_name=? WHERE company_name=?");
            $updateInvoices->bind_param("ss", $company, $oldName);
            $updateInvoices->execute();
            $updateInvoices->close();
        }

        echo json_encode(['success'=>true,'message'=>'Company updated successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Error updating company']);
    }
    $stmt->close();
}

// কোম্পানি মুছে ফেলা (id দিয়ে)
elseif($action == 'delete_company' && isset($_GET['id'])){
    // GET থেকে id নিয়ে integer এ রূপান্তর করা
    $id = intval($_GET['id']);
    // companies টেবিল থেকে নির্দিষ্ট কোম্পানি delete করা
    $stmt = $conn->prepare("UPDATE companies SET deleted_at = NOW() WHERE id=? AND deleted_at IS NULL");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Company deleted successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Delete failed']);
    }
    $stmt->close();
}

// Invoice Management Actions
// নতুন বিল সংরক্ষণ — validation এবং duplicate invoice_no check সহ
elseif($action == 'save_invoice' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // POST থেকে সব প্রয়োজনীয় field এর মান নেওয়া
    $company = trim($_POST['company_name'] ?? '');
    $invoice = trim($_POST['invoice_no'] ?? '');
    $month = $_POST['bill_month'] ?? '';
    $year = $_POST['bill_year'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    // SMS count না থাকলে বা খালি থাকলে 0 ধরা
    $sms = isset($_POST['sms_count']) && $_POST['sms_count'] !== '' ? intval($_POST['sms_count']) : 0;

    // যেকোনো required field খালি বা amount শূন্য হলে validation error দেওয়া
    if(empty($company) || empty($invoice) || empty($month) || empty($year) || $amount <= 0){
        echo json_encode(['success'=>false,'message'=>'Please fill all required fields']);
        exit;
    }

    // একই company + month + year এর বিল আগে থেকে আছে কিনা duplicate check করা
    $yearInt = intval($year);
    $dupStmt = $conn->prepare("SELECT id FROM invoices WHERE deleted_at IS NULL AND company_name=? AND bill_month=? AND bill_year=?");
    $dupStmt->bind_param("ssi", $company, $month, $yearInt);
    $dupStmt->execute();
    $dupStmt->store_result();
    $isDuplicate = $dupStmt->num_rows > 0;
    $dupStmt->close();

    // duplicate company+month+year থাকলে warning দিয়ে বের হয়ে যাওয়া
    if($isDuplicate){
        echo json_encode(['success'=>false,'duplicate'=>true,'message'=>"⚠️ এই কোম্পানির ($company) $month $year মাসের বিল আগেই এন্ট্রি করা আছে!"]);
        exit;
    }

    // একই invoice number আগে থেকে আছে কিনা duplicate check করা
    $invStmt = $conn->prepare("SELECT id FROM invoices WHERE invoice_no=?");
    $invStmt->bind_param("s", $invoice);
    $invStmt->execute();
    $invStmt->store_result();
    $isInvDuplicate = $invStmt->num_rows > 0;
    $invStmt->close();

    // duplicate invoice number থাকলে error দিয়ে বের হয়ে যাওয়া
    if($isInvDuplicate){
        echo json_encode(['success'=>false,'message'=>'Invoice number already exists']);
        exit;
    }

    // invoices টেবিলে নতুন বিল insert করা
    $stmt = $conn->prepare("INSERT INTO invoices (company_name, invoice_no, bill_month, bill_year, amount, sms_count)
            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssidi", $company, $invoice, $month, $year, $amount, $sms);

    if($stmt->execute()){
        echo json_encode(['success'=>true,'message'=>'Bill saved successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Error saving bill: '.$conn->error]);
    }
    $stmt->close();
}

// নির্দিষ্ট বিলের details AJAX এ পাঠানো (id দিয়ে খোঁজা)
elseif($action == 'get_invoice' && isset($_GET['id'])){
    // GET থেকে id নিয়ে integer এ রূপান্তর করা
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM invoices WHERE deleted_at IS NULL AND id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // বিল পাওয়া গেলে details পাঠানো, না পেলে error
    if($result->num_rows > 0){
        echo json_encode(['success'=>true,'invoice'=>$result->fetch_assoc()]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Invoice not found']);
    }
    $stmt->close();
}

// বিল update করা — validation এবং duplicate invoice_no check সহ
elseif($action == 'update_invoice' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // POST থেকে id এবং সব updated field এর মান নেওয়া
    $id = intval($_POST['id']);
    $company = trim($_POST['company_name'] ?? '');
    $invoice = trim($_POST['invoice_no'] ?? '');
    $month = $_POST['bill_month'] ?? '';
    $year = $_POST['bill_year'] ?? '';
    $amount = floatval($_POST['amount'] ?? 0);
    // SMS count না থাকলে বা খালি থাকলে 0 ধরা
    $sms = isset($_POST['sms_count']) && $_POST['sms_count'] !== '' ? intval($_POST['sms_count']) : 0;

    // required field খালি বা amount শূন্য হলে validation error দেওয়া
    if(empty($company) || empty($invoice) || empty($month) || empty($year) || $amount <= 0){
        echo json_encode(['success'=>false,'message'=>'Please fill all required fields']);
        exit;
    }

    // Check if invoice number exists for different ID
    // অন্য কোনো বিলে একই invoice number আছে কিনা duplicate check করা (নিজের id বাদ দিয়ে)
    $stmt = $conn->prepare("SELECT id FROM invoices WHERE invoice_no=? AND id!=?");
    $stmt->bind_param("si", $invoice, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    // duplicate থাকলে error দেওয়া
    if($result->num_rows > 0){
        echo json_encode(['success'=>false,'message'=>'Invoice number already exists']);
        exit;
    }

    // invoices টেবিলে নির্দিষ্ট বিলের সব field update করা
    $stmt = $conn->prepare("UPDATE invoices SET
            company_name=?,
            invoice_no=?,
            bill_month=?,
            bill_year=?,
            amount=?,
            sms_count=?
            WHERE id=?");
    $stmt->bind_param("sssidii", $company, $invoice, $month, $year, $amount, $sms, $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Bill updated successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Update failed: '.$conn->error]);
    }
    $stmt->close();
}

// বিল মুছে ফেলা (id দিয়ে)
elseif($action == 'delete_invoice' && isset($_GET['id'])){
    // GET থেকে id নিয়ে integer এ রূপান্তর করা
    $id = intval($_GET['id']);
    // invoices টেবিল থেকে নির্দিষ্ট বিল delete করা
    $stmt = $conn->prepare("UPDATE invoices SET deleted_at = NOW() WHERE id=? AND deleted_at IS NULL");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Bill deleted successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Delete failed: '.$conn->error]);
    }
    $stmt->close();
}

// Hold/Unhold Bill Actions
// বিল hold করা — কারণ (hold_description) সহ hold_status=1 সেট করা
elseif($action == 'hold_invoice' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // POST থেকে বিলের id এবং hold করার কারণ নেওয়া
    $id = intval($_POST['id']);
    $description = trim($_POST['hold_description'] ?? '');

    // hold করার কারণ না দিলে error দেওয়া
    if(empty($description)){
        echo json_encode(['success'=>false,'message'=>'Please provide a reason for holding this bill']);
        exit;
    }

    // invoices টেবিলে hold_status=1, hold_description এবং hold_date=NOW() সেট করা
    $stmt = $conn->prepare("UPDATE invoices SET hold_status=1, hold_description=?, hold_date=NOW() WHERE id=?");
    $stmt->bind_param("si", $description, $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Bill placed on hold successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to hold bill: '.$conn->error]);
    }
    $stmt->close();
}

// hold থেকে মুক্ত করা — hold_status=0 এবং hold_description NULL করা
elseif($action == 'unhold_invoice' && isset($_GET['id'])){
    // GET থেকে id নিয়ে integer এ রূপান্তর করা
    $id = intval($_GET['id']);
    // invoices টেবিলে hold_status=0, hold_description ও hold_date NULL সেট করে hold মুক্ত করা
    $stmt = $conn->prepare("UPDATE invoices SET hold_status=0, hold_description=NULL, hold_date=NULL WHERE id=?");
    $stmt->bind_param("i", $id);

    if($stmt->execute()){
        echo json_encode(['success'=>true, 'message'=>'Bill released from hold successfully']);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to release bill: '.$conn->error]);
    }
    $stmt->close();
}

// সব hold বিলের list AJAX এ পাঠানো (hold_status=1)
elseif($action == 'get_hold_bills'){
    // invoices টেবিল থেকে hold অবস্থায় থাকা সব বিল বের করা
    $bills = $conn->query("SELECT * FROM invoices WHERE deleted_at IS NULL AND hold_status=1 ORDER BY id DESC");
    $billsData = [];
    if($bills) {
        while($row = $bills->fetch_assoc()) $billsData[] = $row;
    }

    echo json_encode([
        'success' => true,
        'bills' => $billsData
    ]);
}

// সব complete বিলের list AJAX এ পাঠানো (complete_status=1), year/company filters সহ
elseif($action == 'get_complete_bills'){
    // invoices টেবিল থেকে complete অবস্থায় থাকা সব বিল বের করা
    $bills = $conn->query("SELECT * FROM invoices WHERE deleted_at IS NULL AND complete_status=1 ORDER BY id DESC");
    $billsData = [];
    if($bills) {
        while($row = $bills->fetch_assoc()) $billsData[] = $row;
    }

    // complete বিল থেকে বছর এবং কোম্পানির list বের করা (filter এর জন্য)
    $years = $conn->query("SELECT DISTINCT bill_year FROM invoices WHERE deleted_at IS NULL AND complete_status=1 ORDER BY bill_year DESC");
    $companies = $conn->query("SELECT DISTINCT company_name FROM invoices WHERE deleted_at IS NULL AND complete_status=1 ORDER BY company_name");

    // created_at থেকে entry মাস-বছর বের করা (যে মাসে software-এ entry হয়েছে)
    $entryMonths = $conn->query("SELECT DISTINCT DATE_FORMAT(created_at, '%M %Y') as entry_label, DATE_FORMAT(created_at, '%Y-%m') as entry_value FROM invoices WHERE deleted_at IS NULL AND complete_status=1 ORDER BY entry_value DESC");

    $yearsData = [];
    if($years) {
        while($row=$years->fetch_assoc()) $yearsData[] = $row['bill_year'];
    }

    $companiesData = [];
    if($companies) {
        while($row=$companies->fetch_assoc()) $companiesData[] = $row['company_name'];
    }

    $entryMonthsData = [];
    if($entryMonths) {
        while($row=$entryMonths->fetch_assoc()) $entryMonthsData[] = ['label' => $row['entry_label'], 'value' => $row['entry_value']];
    }

    // complete বিল এবং filter data JSON আকারে পাঠানো
    echo json_encode([
        'success' => true,
        'bills' => $billsData,
        'filters' => [
            'years' => $yearsData,
            'companies' => $companiesData,
            'entry_months' => $entryMonthsData
        ]
    ]);
}

// বিল complete করা — hold check সহ (hold থাকলে complete করা যাবে না)
elseif($action == 'mark_complete' && isset($_GET['id'])){
    $id = intval($_GET['id']);

    $checkStmt = $conn->prepare("SELECT hold_status FROM invoices WHERE id=?");
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $result = $checkStmt->get_result();

    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        if($row['hold_status'] == 1){
            echo json_encode(['success'=>false,'message'=>'Cannot mark as complete. Bill is on hold!']);
            exit;
        }
    }
    $checkStmt->close();

    $affected = updateMatchingBillsStatus($conn, $id, 1);

    if($affected >= 0){
        echo json_encode([
            'success' => true,
            'message' => "Marked {$affected} bill(s) as complete successfully",
            'count' => $affected
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to mark as complete: '.$conn->error]);
    }
}

// complete বিল আবার pending-এ নেওয়া — complete_status=0 সেট করা
elseif($action == 'mark_incomplete' && isset($_GET['id'])){
    $id = intval($_GET['id']);
    $affected = updateMatchingBillsStatus($conn, $id, 0);

    if($affected >= 0){
        echo json_encode([
            'success' => true,
            'message' => "Marked {$affected} bill(s) as incomplete successfully",
            'count' => $affected
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to mark as incomplete: '.$conn->error]);
    }
}

// filter অনুযায়ী সব বিল একসাথে complete করা (hold বিল বাদে)
elseif($action == 'mark_all_complete' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    // Get filter parameters
    // POST থেকে সব filter parameter নেওয়া
    $month = $_POST['month'] ?? '';
    $year = $_POST['year'] ?? '';
    $company = $_POST['company'] ?? '';
    $search = $_POST['search'] ?? '';

    // Build query to mark all non-hold bills as complete based on filters
    // hold বিল বাদে বাকি সব বিল complete করার base query তৈরি করা
    $sql = "UPDATE invoices SET complete_status=1, complete_date=NOW() WHERE hold_status=0";
    $params = [];
    $types = "";

    // মাস filter থাকলে query তে যোগ করা
    if(!empty($month)){
        $sql .= " AND bill_month=?";
        $params[] = $month;
        $types .= "s";
    }

    // বছর filter থাকলে query তে যোগ করা
    if(!empty($year)){
        $sql .= " AND bill_year=?";
        $params[] = $year;
        $types .= "s";
    }

    // কোম্পানি filter থাকলে query তে যোগ করা
    if(!empty($company)){
        $sql .= " AND company_name=?";
        $params[] = $company;
        $types .= "s";
    }

    // search term থাকলে কোম্পানির নাম, invoice নম্বর বা মাস দিয়ে খোঁজার শর্ত যোগ করা
    if(!empty($search)){
        $sql .= " AND (company_name LIKE ? OR invoice_no LIKE ? OR bill_month LIKE ?)";
        $searchTerm = "%{$search}%";
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $types .= "sss";
    }

    $stmt = $conn->prepare($sql);

    // filter parameter থাকলে prepared statement এ bind করা
    if(!empty($params)){
        $stmt->bind_param($types, ...$params);
    }

    if($stmt->execute()){
        // কতটি বিল complete হলো তা জানানো
        $affectedRows = $stmt->affected_rows;
        echo json_encode([
            'success'=>true,
            'message'=>"Successfully marked {$affectedRows} bill(s) as complete",
            'count'=>$affectedRows
        ]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed to mark bills as complete: '.$conn->error]);
    }
    $stmt->close();
}

// নির্বাচিত pending bill গুলো একটি কারণ দিয়ে একসাথে hold করা
elseif($action == 'bulk_hold' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), fn($id) => $id > 0);
    $reason = trim($_POST['hold_description'] ?? '');

    if(empty($ids)){ echo json_encode(['success'=>false,'message'=>'কোনো বিল নির্বাচন করা হয়নি']); exit; }
    if($reason === ''){ echo json_encode(['success'=>false,'message'=>'Hold reason লিখুন']); exit; }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));

    // hold_status=1, hold_description, hold_date=NOW() সেট করা
    $stmt = $conn->prepare("UPDATE invoices SET hold_status=1, hold_description=?, hold_date=NOW() WHERE id IN ($placeholders) AND complete_status=0");
    $stmt->bind_param('s' . $types, $reason, ...$ids);

    if($stmt->execute()){
        $affected = $stmt->affected_rows;
        echo json_encode(['success'=>true,'message'=>"{$affected}টি বিল সফলভাবে Hold করা হয়েছে।"]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed: '.$conn->error]);
    }
    $stmt->close();
}

// নির্বাচিত complete bill গুলো একসাথে incomplete (pending) করা
elseif($action == 'bulk_mark_incomplete' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), fn($id) => $id > 0);
    if(empty($ids)){ echo json_encode(['success'=>false,'message'=>'কোনো বিল নির্বাচন করা হয়নি']); exit; }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("UPDATE invoices SET complete_status=0, complete_date=NULL WHERE deleted_at IS NULL AND id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    if($stmt->execute()){
        $affected = $stmt->affected_rows;
        echo json_encode(['success'=>true,'message'=>"{$affected}টি বিল সফলভাবে Pending-এ নেওয়া হয়েছে।"]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed: '.$conn->error]);
    }
    $stmt->close();
}

// নির্বাচিত bill গুলো একসাথে delete করা
elseif($action == 'bulk_delete' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $ids = array_filter(array_map('intval', (array)($_POST['ids'] ?? [])), fn($id) => $id > 0);
    if(empty($ids)){ echo json_encode(['success'=>false,'message'=>'কোনো বিল নির্বাচন করা হয়নি']); exit; }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('i', count($ids));
    $stmt = $conn->prepare("UPDATE invoices SET deleted_at = NOW() WHERE id IN ($placeholders) AND deleted_at IS NULL");
    $stmt->bind_param($types, ...$ids);
    if($stmt->execute()){
        $affected = $stmt->affected_rows;
        echo json_encode(['success'=>true,'message'=>"{$affected}টি বিল সফলভাবে delete করা হয়েছে।"]);
    } else {
        echo json_encode(['success'=>false,'message'=>'Failed: '.$conn->error]);
    }
    $stmt->close();
}

// নির্বাচিত (checkbox) bill গুলো একসাথে complete করা — hold বিল skip হবে
elseif($action == 'mark_selected_complete' && $_SERVER['REQUEST_METHOD'] == 'POST'){
    $rawIds = $_POST['ids'] ?? [];
    $ids = array_map('intval', (array)$rawIds);
    $ids = array_filter($ids, fn($id) => $id > 0);

    if(empty($ids)){
        echo json_encode(['success'=>false,'message'=>'কোনো বিল নির্বাচন করা হয়নি']);
        exit;
    }

    $affected = updateSelectedMatchingBillsStatus($conn, $ids, 1);
    $skipped = count(array_unique($ids)) - $affected;
    $msg = "{$affected}টি বিল সফলভাবে complete করা হয়েছে।";
    if($skipped > 0) $msg .= " {$skipped}টি বিল skip হয়েছে (hold বা আগেই complete)।";
    echo json_encode(['success'=>true,'message'=>$msg,'affected'=>$affected]);
}

// তারিখ range অনুযায়ী report — bills, summary, company breakdown সহ
elseif($action == 'get_date_range_report'){
    // GET থেকে from_date, to_date এবং company filter নেওয়া
    $fromDate = $_GET['from_date'] ?? '';
    $toDate = $_GET['to_date'] ?? '';
    $companyFilter = $_GET['company'] ?? '';

    // from_date বা to_date না দিলে error দেওয়া
    if(empty($fromDate) || empty($toDate)){
        echo json_encode(['success'=>false,'message'=>'Please provide both from and to dates']);
        exit;
    }

    // Add time to dates for proper range query
    // date range query সঠিকভাবে কাজ করতে সময় যোগ করা (দিনের শুরু থেকে শেষ পর্যন্ত)
    $fromDateTime = $fromDate . ' 00:00:00';
    $toDateTime = $toDate . ' 23:59:59';

    // Build query based on company filter
    // কোম্পানি filter থাকলে সেটাও query তে যোগ করা
    if(!empty($companyFilter)){
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE deleted_at IS NULL AND created_at >= ? AND created_at <= ? AND company_name = ? ORDER BY created_at DESC");
        $stmt->bind_param("sss", $fromDateTime, $toDateTime, $companyFilter);
    } else {
        // কোম্পানি filter না থাকলে শুধু তারিখ range দিয়ে query করা
        $stmt = $conn->prepare("SELECT * FROM invoices WHERE deleted_at IS NULL AND created_at >= ? AND created_at <= ? ORDER BY created_at DESC");
        $stmt->bind_param("ss", $fromDateTime, $toDateTime);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    // report এর সব summary variable initialize করা
    $bills = [];
    $totalAmount = 0;
    $totalSMS = 0;
    $internetBills = 0;
    $smsBills = 0;
    $holdBills = 0;
    $completeBills = 0;
    $companyBreakdown = [];

    if($result) {
        while($row = $result->fetch_assoc()) {
            $bills[] = $row;
            // মোট amount এবং SMS count যোগ করা
            $totalAmount += $row['amount'];
            $totalSMS += $row['sms_count'] ?? 0;

            // SMS নেই বা 0 হলে internet bill, অন্যথায় SMS bill গণনা করা
            if(empty($row['sms_count']) || $row['sms_count'] == 0){
                $internetBills++;
            } else {
                $smsBills++;
            }

            // hold_status অনুযায়ী hold এবং complete বিল গণনা করা
            if($row['hold_status'] == 1){
                $holdBills++;
            } else {
                $completeBills++;
            }

            // Company-wise breakdown
            // প্রতিটি কোম্পানির আলাদা বিল সংখ্যা ও মোট amount হিসাব করা
            $companyName = $row['company_name'];
            if(!isset($companyBreakdown[$companyName])){
                $companyBreakdown[$companyName] = [
                    'company' => $companyName,
                    'count' => 0,
                    'total' => 0
                ];
            }
            $companyBreakdown[$companyName]['count']++;
            $companyBreakdown[$companyName]['total'] += $row['amount'];
        }
    }
    $stmt->close();

    // Convert company breakdown to array
    // company breakdown associative array কে indexed array তে রূপান্তর করা
    $companyBreakdownArray = array_values($companyBreakdown);

    // report এর সম্পূর্ণ data — bills, summary এবং company breakdown সহ JSON আকারে পাঠানো
    echo json_encode([
        'success' => true,
        'bills' => $bills,
        'summary' => [
            'total_bills' => count($bills),
            'total_amount' => $totalAmount,
            'total_sms' => $totalSMS,
            'internet_bills' => $internetBills,
            'sms_bills' => $smsBills,
            'hold_bills' => $holdBills,
            'complete_bills' => $completeBills,
            'unique_companies' => count($companyBreakdown)
        ],
        'company_breakdown' => $companyBreakdownArray
    ]);
}


// অজানা action হলে error message দেওয়া
else {
    echo json_encode(['success'=>false,'message'=>'Invalid action']);
}
?>
