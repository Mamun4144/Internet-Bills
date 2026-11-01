<?php
require_once "includes/init.php";
$pageTitle = "Deleted Records";
include "includes/header.php";
?>
<body>
    <?php include "includes/sidebar.php"; ?>

    <div class="main-content">
        <div class="header">
            <button class="menu-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
            <h2>Welcome, <?= htmlspecialchars($_SESSION['user']); ?></h2>
            <button class="refresh-btn" onclick="loadDeletedRecords()"><i class="fas fa-sync-alt"></i> Refresh</button>
            <div class="header-logo">
                <img src="images/logo.jpg" alt="Sundarban Courier" title="Sundarban Courier Service (Pvt.) Ltd.">
                <div class="logo-name">Sundarban<br>Courier Service</div>
            </div>
        </div>

        <div class="content">
            <div id="messageContainer"></div>

            <div class="section active">
                <h2><i class="fas fa-trash-restore"></i> Deleted Records</h2>
                <p style="color:#666; margin-bottom:20px;">
                    <i class="fas fa-info-circle"></i> These records are soft deleted and hidden from normal lists. You can restore them anytime.
                </p>

                <div class="table-container">
                    <table id="deletedRecordsTable">
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Name / Invoice</th>
                            <th>Details</th>
                            <th>Amount</th>
                            <th>Deleted At</th>
                            <th>Actions</th>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <?php include "includes/footer.php"; ?>

    <script src="js/dashboard.js"></script>
    <script>
        window.onload = function() {
            loadData();
            loadDeletedRecords();
        };
    </script>
</body>
</html>
