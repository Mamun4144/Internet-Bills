<?php
require_once "includes/init.php";

// ============================================================
// সব POST action এখানে — HTML output এর আগে
// success/error message session এ রেখে redirect (PRG pattern)
// এতে page reload করলে double submit হবে না
// ============================================================

// PDF upload handle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf_file'])) {
    $title       = trim($_POST['title'] ?? '');
    $company     = trim($_POST['company_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $entryMonth  = trim($_POST['entry_month'] ?? date('Y-m'));
    $file        = $_FILES['pdf_file'];

    if ($title === '') {
        $_SESSION['pdf_msg'] = ['text' => 'Title দিন।', 'type' => 'error'];
    } elseif ($file['error'] !== UPLOAD_ERR_OK) {
        $_SESSION['pdf_msg'] = ['text' => 'File upload error। আবার চেষ্টা করুন।', 'type' => 'error'];
    } elseif (strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)) !== 'pdf') {
        $_SESSION['pdf_msg'] = ['text' => 'শুধুমাত্র PDF file upload করা যাবে।', 'type' => 'error'];
    } elseif ($file['size'] > 20 * 1024 * 1024) {
        $_SESSION['pdf_msg'] = ['text' => 'File size সর্বোচ্চ 20MB হতে পারবে।', 'type' => 'error'];
    } else {
        $safeName  = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $file['name']);
        if (move_uploaded_file($file['tmp_name'], __DIR__ . '/uploads/pdfs/' . $safeName)) {
            $stmt = $conn->prepare("INSERT INTO bill_pdfs (title, company_name, description, file_name, file_size, entry_month) VALUES (?,?,?,?,?,?)");
            $stmt->bind_param('ssssis', $title, $company, $description, $safeName, $file['size'], $entryMonth);
            $_SESSION['pdf_msg'] = $stmt->execute()
                ? ['text' => 'PDF সফলভাবে upload হয়েছে।', 'type' => 'success']
                : ['text' => 'Database error: ' . $conn->error, 'type' => 'error'];
            $stmt->close();
        } else {
            $_SESSION['pdf_msg'] = ['text' => 'File save করতে ব্যর্থ। uploads/pdfs/ folder permission চেক করুন।', 'type' => 'error'];
        }
    }
    header('Location: bill_pdf.php');
    exit;
}

// edit/update handle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['do_edit'])) {
    $editId      = intval($_POST['edit_id']);
    $editTitle   = trim($_POST['edit_title'] ?? '');
    $editCompany = trim($_POST['edit_company'] ?? '');
    $editDesc    = trim($_POST['edit_description'] ?? '');
    $editMonth   = trim($_POST['edit_entry_month'] ?? '');
    $newFile     = $_FILES['edit_pdf_file'] ?? null;

    if ($editTitle === '') {
        $_SESSION['pdf_msg'] = ['text' => 'Title দিন।', 'type' => 'error'];
    } else {
        $newFileName = null;
        $newFileSize = null;

        if ($newFile && $newFile['error'] === UPLOAD_ERR_OK) {
            if (strtolower(pathinfo($newFile['name'], PATHINFO_EXTENSION)) !== 'pdf') {
                $_SESSION['pdf_msg'] = ['text' => 'শুধুমাত্র PDF file upload করা যাবে।', 'type' => 'error'];
                header('Location: bill_pdf.php');
                exit;
            }
            $oldRow = $conn->query("SELECT file_name FROM bill_pdfs WHERE id=$editId")->fetch_assoc();
            if ($oldRow) {
                $oldPath = __DIR__ . '/uploads/pdfs/' . $oldRow['file_name'];
                if (file_exists($oldPath)) unlink($oldPath);
            }
            $newFileName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $newFile['name']);
            move_uploaded_file($newFile['tmp_name'], __DIR__ . '/uploads/pdfs/' . $newFileName);
            $newFileSize = $newFile['size'];
        }

        if ($newFileName) {
            $stmt = $conn->prepare("UPDATE bill_pdfs SET title=?, company_name=?, description=?, entry_month=?, file_name=?, file_size=? WHERE id=?");
            $stmt->bind_param('sssssii', $editTitle, $editCompany, $editDesc, $editMonth, $newFileName, $newFileSize, $editId);
        } else {
            $stmt = $conn->prepare("UPDATE bill_pdfs SET title=?, company_name=?, description=?, entry_month=? WHERE id=?");
            $stmt->bind_param('ssssi', $editTitle, $editCompany, $editDesc, $editMonth, $editId);
        }

        $_SESSION['pdf_msg'] = $stmt->execute()
            ? ['text' => 'PDF সফলভাবে আপডেট হয়েছে।', 'type' => 'success']
            : ['text' => 'Update failed: ' . $conn->error, 'type' => 'error'];
        $stmt->close();
    }
    header('Location: bill_pdf.php');
    exit;
}

// session থেকে message নেওয়া এবং clear করা
$uploadMsg  = $_SESSION['pdf_msg']['text'] ?? '';
$uploadType = $_SESSION['pdf_msg']['type'] ?? '';
unset($_SESSION['pdf_msg']);

$pageTitle = "Bill PDF";
include "includes/header.php";

// delete handle
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delId   = intval($_GET['delete']);
    $row     = $conn->query("SELECT file_name FROM bill_pdfs WHERE id=$delId")->fetch_assoc();
    if ($row) {
        $fp = __DIR__ . '/uploads/pdfs/' . $row['file_name'];
        if (file_exists($fp)) unlink($fp);
        $conn->query("DELETE FROM bill_pdfs WHERE id=$delId");
    }
    header('Location: bill_pdf.php');
    exit;
}

// fetch all PDFs
$pdfs = $conn->query("SELECT * FROM bill_pdfs ORDER BY id DESC");
?>
<body>
    <?php include "includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="header">
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <div class="section active">
                <h2><i class="fas fa-file-pdf" style="color:#e74c3c"></i> Bill PDF</h2>

                <?php if ($uploadMsg): ?>
                <div class="message <?= $uploadType ?>" style="margin-bottom:16px;">
                    <i class="fas fa-<?= $uploadType==='success'?'check-circle':'exclamation-circle' ?>"></i>
                    <?= htmlspecialchars($uploadMsg) ?>
                </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="bill-form" style="max-width:700px; margin-bottom:30px;">
                    <h3 style="margin-bottom:18px; color:var(--dark);">
                        <i class="fas fa-upload"></i> নতুন PDF Upload করুন
                    </h3>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label><i class="fas fa-heading"></i> Title <span style="color:red">*</span></label>
                            <input type="text" name="title" placeholder="PDF এর শিরোনাম লিখুন" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label><i class="fas fa-building"></i> Company (Optional)</label>
                                <select name="company_name">
                                    <option value="">All Company</option>
                                    <?php
                                    $cos = $conn->query("SELECT company_name FROM companies ORDER BY company_name");
                                    while ($c = $cos->fetch_assoc())
                                        echo '<option value="' . htmlspecialchars($c['company_name']) . '">' . htmlspecialchars($c['company_name']) . '</option>';
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label><i class="fas fa-calendar-alt"></i> Entry Month <span style="color:red">*</span></label>
                                <select name="entry_month" required>
                                    <option value="">-- মাস select করুন --</option>
                                    <?php
                                    // বর্তমান বছর থেকে ১ বছর পিছনে পর্যন্ত মাস দেখানো
                                    for ($y = (int)date('Y'); $y >= (int)date('Y') - 1; $y--) {
                                        for ($m = 12; $m >= 1; $m--) {
                                            // ভবিষ্যতের মাস বাদ দেওয়া
                                            if ($y == (int)date('Y') && $m > (int)date('m')) continue;
                                            $val   = sprintf('%04d-%02d', $y, $m);
                                            $label = date('F Y', mktime(0, 0, 0, $m, 1, $y));
                                            $sel   = ($val === date('Y-m')) ? 'selected' : '';
                                            echo "<option value=\"$val\" $sel>$label</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-file-pdf"></i> PDF File <span style="color:red">*</span></label>
                            <input type="file" name="pdf_file" accept=".pdf" required
                                style="padding:10px; border:1px dashed #c3b4ff; border-radius:8px; background:#faf9ff; cursor:pointer; width:100%;">
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-sticky-note"></i> Description (Optional)</label>
                            <textarea name="description" rows="3" placeholder="সংক্ষিপ্ত বিবরণ লিখুন..."
                                style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:8px; font-family:inherit; resize:vertical;"></textarea>
                        </div>
                        <button type="submit" class="btn-save">
                            <i class="fas fa-cloud-upload-alt"></i> Upload PDF
                        </button>
                    </form>
                </div>

                <!-- PDF List -->
                <div class="recent-box">
                    <h3><i class="fas fa-list"></i> Uploaded PDFs</h3>

                    <!-- Filter Row -->
                    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap; margin-bottom:14px;">
                        <!-- Entry Month Dropdown -->
                        <div style="display:flex; align-items:center; gap:6px;">
                            <label style="font-size:13px; font-weight:600; color:var(--dark); white-space:nowrap;">
                                <i class="fas fa-calendar-alt" style="color:#4a6cf7;"></i> Entry Month:
                            </label>
                            <select id="pdfEntryMonthFilter" onchange="filterPdfTable()"
                                style="padding:7px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; background:#fff; cursor:pointer;">
                                <option value="">All Months</option>
                                <?php
                                // entry_month column থেকে distinct মাস-বছর বের করা
                                $months_res = $conn->query("SELECT DISTINCT entry_month as val, DATE_FORMAT(STR_TO_DATE(CONCAT(entry_month,'-01'),'%Y-%m-%d'),'%M %Y') as label FROM bill_pdfs WHERE entry_month IS NOT NULL ORDER BY val DESC");
                                if ($months_res) {
                                    while ($mr = $months_res->fetch_assoc()) {
                                        echo '<option value="' . $mr['val'] . '">' . $mr['label'] . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Company Filter -->
                        <div style="display:flex; align-items:center; gap:6px;">
                            <label style="font-size:13px; font-weight:600; color:var(--dark); white-space:nowrap;">
                                <i class="fas fa-building" style="color:#4a6cf7;"></i> Company:
                            </label>
                            <select id="pdfCompanyFilter" onchange="filterPdfTable()"
                                style="padding:7px 12px; border:1px solid #d1d5db; border-radius:8px; font-size:13px; background:#fff; cursor:pointer;">
                                <option value="">All Companies</option>
                                <?php
                                $cf = $conn->query("SELECT DISTINCT company_name FROM bill_pdfs WHERE company_name != '' ORDER BY company_name");
                                if ($cf) {
                                    while ($cr = $cf->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($cr['company_name']) . '">' . htmlspecialchars($cr['company_name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Clear Filter -->
                        <button onclick="clearPdfFilters()" class="btn-clear" style="font-size:13px;">
                            <i class="fas fa-times"></i> Clear
                        </button>
                        <span id="pdfFilterCount" style="font-size:12px; color:#888; margin-left:4px;"></span>
                    </div>

                    <div class="table-container">
                        <table id="pdfTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Title</th>
                                    <th>Company</th>
                                    <th>Entry Month</th>
                                    <th>Description</th>
                                    <th>Size</th>
                                    <th>Upload Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php
                            $i = 1;
                            $found = false;
                            while ($pdf = $pdfs->fetch_assoc()):
                                $found = true;
                                $sizeFmt = $pdf['file_size'] > 1048576
                                    ? round($pdf['file_size']/1048576, 2) . ' MB'
                                    : round($pdf['file_size']/1024, 1) . ' KB';
                                $date    = date('d M Y, h:i A', strtotime($pdf['uploaded_at']));
                                $entryYM = $pdf['entry_month'] ?? date('Y-m', strtotime($pdf['uploaded_at']));
                                $entryLabel = $entryYM ? date('F Y', strtotime($entryYM . '-01')) : '—';
                            ?>
                                <tr data-entry-month="<?= $entryYM ?>" data-company="<?= htmlspecialchars($pdf['company_name'] ?? '') ?>">
                                    <td><?= $i++ ?></td>
                                    <td><strong><?= htmlspecialchars($pdf['title']) ?></strong></td>
                                    <td><?= htmlspecialchars($pdf['company_name'] ?: '—') ?></td>
                                    <td style="white-space:nowrap; font-size:13px; color:#4a6cf7; font-weight:600;">
                                        <i class="fas fa-calendar-alt"></i> <?= $entryLabel ?>
                                    </td>
                                    <td style="max-width:200px; font-size:12px; color:#666;">
                                        <?= htmlspecialchars($pdf['description'] ?: '—') ?>
                                    </td>
                                    <td><?= $sizeFmt ?></td>
                                    <td style="white-space:nowrap; font-size:12px;"><?= $date ?></td>
                                    <td>
                                        <div class="actions">
                                            <!-- View / Open -->
                                            <a href="uploads/pdfs/<?= urlencode($pdf['file_name']) ?>" target="_blank"
                                                class="btn-edit" style="background:#4a6cf7; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px;"
                                                title="PDF দেখুন">
                                                <i class="fas fa-eye" style="color:#fff;"></i>
                                            </a>
                                            <!-- Download -->
                                            <a href="uploads/pdfs/<?= urlencode($pdf['file_name']) ?>" download="<?= htmlspecialchars($pdf['title']) ?>.pdf"
                                                class="btn-edit" style="background:#28a745; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px;"
                                                title="Download">
                                                <i class="fas fa-download" style="color:#fff;"></i>
                                            </a>
                                            <!-- Edit -->
                                            <button class="btn-edit" style="background:#e67e22;"
                                                onclick="openEditPdf(<?= $pdf['id'] ?>, '<?= addslashes(htmlspecialchars($pdf['title'])) ?>', '<?= addslashes(htmlspecialchars($pdf['company_name'])) ?>', '<?= addslashes(htmlspecialchars($pdf['description'])) ?>', '<?= $entryYM ?>')"
                                                title="Edit / Reupload">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <!-- Delete -->
                                            <a href="bill_pdf.php?delete=<?= $pdf['id'] ?>"
                                                onclick="return confirm('এই PDF মুছে ফেলতে চান?')"
                                                class="btn-delete" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:32px; height:32px; border-radius:6px;"
                                                title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if (!$found): ?>
                                <tr>
                                    <td colspan="8" class="no-data">
                                        <i class="fas fa-file-pdf" style="color:#e74c3c"></i><br>
                                        এখনো কোনো PDF upload করা হয়নি
                                    </td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit PDF Modal -->
    <div id="editPdfModal" class="modal">
        <div class="modal-content" style="max-width:560px;">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit PDF Details</h3>
                <span class="close" onclick="closeEditPdf()">&times;</span>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editPdfForm">
                <input type="hidden" name="edit_id" id="edit_id">
                <div class="modal-body" style="padding:20px;">
                    <div class="form-group">
                        <label><i class="fas fa-heading"></i> Title <span style="color:red">*</span></label>
                        <input type="text" name="edit_title" id="edit_title" required style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label><i class="fas fa-building"></i> Company</label>
                            <select name="edit_company" id="edit_company" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                                <option value="">All Company</option>
                                <?php
                                $cos2 = $conn->query("SELECT company_name FROM companies ORDER BY company_name");
                                while ($c2 = $cos2->fetch_assoc())
                                    echo '<option value="' . htmlspecialchars($c2['company_name']) . '">' . htmlspecialchars($c2['company_name']) . '</option>';
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label><i class="fas fa-calendar-alt"></i> Entry Month</label>
                            <select name="edit_entry_month" id="edit_entry_month" style="width:100%; padding:10px; border:1px solid #d1d5db; border-radius:8px;">
                                <option value="">-- মাস select করুন --</option>
                                <?php
                                for ($y2 = (int)date('Y'); $y2 >= (int)date('Y') - 1; $y2--) {
                                    for ($m2 = 12; $m2 >= 1; $m2--) {
                                        if ($y2 == (int)date('Y') && $m2 > (int)date('m')) continue;
                                        $val2   = sprintf('%04d-%02d', $y2, $m2);
                                        $label2 = date('F Y', mktime(0, 0, 0, $m2, 1, $y2));
                                        echo "<option value=\"$val2\">$label2</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-sticky-note"></i> Description</label>
                        <textarea name="edit_description" id="edit_description" rows="3"
                            style="width:100%; padding:12px; border:1px solid #d1d5db; border-radius:8px; font-family:inherit; resize:vertical;"></textarea>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-file-pdf"></i> Reupload PDF <span style="font-size:12px; color:#888;">(Optional — পুরনো file রাখতে চাইলে খালি রাখুন)</span></label>
                        <input type="file" name="edit_pdf_file" accept=".pdf"
                            style="padding:10px; border:1px dashed #c3b4ff; border-radius:8px; background:#faf9ff; cursor:pointer; width:100%;">
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeEditPdf()">Cancel</button>
                    <button type="submit" name="do_edit" class="btn-update" style="background:#e67e22;">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/dashboard.js"></script>
    <script>
        window.onload = function() { loadData(); updatePdfCount(); };

        function filterPdfTable() {
            const selMonth   = document.getElementById('pdfEntryMonthFilter').value;
            const selCompany = document.getElementById('pdfCompanyFilter').value;
            const rows = document.querySelectorAll('#pdfTable tbody tr[data-entry-month]');
            let visible = 0;
            rows.forEach(row => {
                const matchMonth   = !selMonth   || row.dataset.entryMonth === selMonth;
                const matchCompany = !selCompany || row.dataset.company === selCompany;
                row.style.display = matchMonth && matchCompany ? '' : 'none';
                if (matchMonth && matchCompany) visible++;
            });
            const countEl = document.getElementById('pdfFilterCount');
            if (countEl) countEl.textContent = (selMonth || selCompany) ? visible + 'টি পাওয়া গেছে' : '';
        }

        function clearPdfFilters() {
            document.getElementById('pdfEntryMonthFilter').value = '';
            document.getElementById('pdfCompanyFilter').value = '';
            filterPdfTable();
        }

        function updatePdfCount() { filterPdfTable(); }

        function openEditPdf(id, title, company, description, entryMonth) {
            document.getElementById('edit_id').value          = id;
            document.getElementById('edit_title').value       = title;
            document.getElementById('edit_description').value = description;
            // company select
            const cSel = document.getElementById('edit_company');
            for (let i = 0; i < cSel.options.length; i++) {
                cSel.options[i].selected = cSel.options[i].value === company;
            }
            // entry month select
            const mSel = document.getElementById('edit_entry_month');
            for (let i = 0; i < mSel.options.length; i++) {
                mSel.options[i].selected = mSel.options[i].value === entryMonth;
            }
            document.getElementById('editPdfModal').classList.add('active');
        }

        function closeEditPdf() {
            document.getElementById('editPdfModal').classList.remove('active');
        }
    </script>
</body>
</html>
