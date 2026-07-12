<?php
// booking_history.php - Split Admin Records, Name Search, & Quick Filters
require_once 'toyyibpay_config.php';

// LEFT SIDE: Fetch regular tourist/customer booking payment history
$stmt_customer = $pdo->prepare("SELECT p.id, u.fullname as user, h.name as homestay_name, h.state, DATE(p.payment_date) as date, p.amount as budget, p.status 
                               FROM payment_history p 
                               JOIN users u ON p.user_id = u.id 
                               JOIN homestays h ON p.homestay_id = h.id 
                               ORDER BY p.id DESC");
$stmt_customer->execute();
$raw_customer_history = $stmt_customer->fetchAll(PDO::FETCH_ASSOC);

$customer_booking_history = [];
foreach ($raw_customer_history as $row) {
    $status_lbl = strtolower($row['status']) == 'paid' ? 'confirmed' : 'pending';
    $customer_booking_history[] = [
        'id' => $row['id'],
        'user' => !empty($row['user']) ? $row['user'] : 'Unknown User',
        'homestay_name' => $row['homestay_name'],
        'state' => $row['state'],
        'date' => $row['date'],
        'budget' => 'RM ' . number_format((float)$row['budget'], 2),
        'status' => $status_lbl
    ];
}

// RIGHT SIDE: Fetch homestay owners' listing registration fee activation log (RM 29.00 logs)
$stmt_owner = $pdo->prepare("SELECT h.id, u.fullname as owner_name, h.name as homestay_name, h.state, h.payment_status 
                             FROM homestays h 
                             JOIN users u ON h.user_id = u.id 
                             ORDER BY h.id DESC");
$stmt_owner->execute();
$owner_activation_history = $stmt_owner->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Admin Portal - Booking History</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
    <style>
        /* Custom Modern Split Responsive Grid Layout */
        .admin-split-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-top: 1.5rem;
        }
        @media (max-width: 1024px) {
            .admin-split-grid {
                grid-template-columns: 1fr;
            }
        }
        .header-action-row {
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            margin-bottom: 15px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
            padding-bottom: 10px;
            flex-wrap: wrap;
            gap: 10px;
        }
        .search-container-box {
            width: 100%;
            margin-bottom: 12px;
        }
        .search-container-box input {
            width: 100%;
            padding: 10px 14px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 0.88rem;
            font-family: 'DM Sans', sans-serif;
            outline: none;
            box-sizing: border-box;
        }
        .search-container-box input:focus {
            border-color: #d4af37;
        }
        
        /* Quick Filter Row Styles */
        .quick-filter-row {
            display: flex;
            gap: 8px;
            margin-bottom: 15px;
        }
        .filter-badge {
            background: #f3f4f6;
            color: #4b5563;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid #e5e7eb;
            transition: all 0.2s;
            font-family: 'DM Sans', sans-serif;
        }
        .filter-badge.active {
            background: #043927;
            color: white;
            border-color: #043927;
        }
        .filter-badge.active-blue {
            background: #2563eb;
            color: white;
            border-color: #2563eb;
        }
        
        .btn-excel-export {
            background-color: #1f804f; 
            color: white; 
            border: none; 
            padding: 8px 14px; 
            font-size: 0.8rem; 
            font-weight: 600; 
            border-radius: 6px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 6px;
            font-family: 'DM Sans', sans-serif;
            transition: background 0.2s;
        }
        .btn-excel-export:hover {
            background-color: #165c38;
        }
    </style>
</head>
<body>

    <nav>
        <div class="nav__header">
            <div class="nav__logo"><a href="admin.php" class="logo"><?= htmlspecialchars($system_name) ?><span>.</span></a></div>
            <div class="nav__menu__btn" id="menu-btn"><i class="ri-menu-line"></i></div>
        </div>
        <ul class="nav__links" id="nav-links">
            <li><a href="booking_history.php" style="color: #d4af37; font-weight: 600;">Booking History</a></li>
            <li><a href="add_attractions.php">Add Attractions</a></li>
            <li><a href="admin_commission.php">Admin Commission</a></li>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_homestay.php">Homestay Approvals</a></li>
            <li><a href="index.php?action=logout" style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: bold; text-transform: uppercase;">LOGOUT</a></li>
        </ul>
    </nav>

    <div class="admin-workspace-container">
        <header class="admin-workspace-header">
            <div class="header-titles">
                <h1>System History Logs</h1>
                <p>Monitor platform interactions. The left logs customer trips; the right audits homestay owner listing setup fees.</p>
            </div>
        </header>

        <div class="admin-split-grid">
            
            <section class="admin-section-card">
                <div class="header-action-row">
                    <h3><i class="ri-history-line" style="color: #d4af37;"></i> Customer Booking History</h3>
                    <button type="button" class="btn-excel-export" onclick="exportTableToExcel('customerBookingTable', 'Customer_Trip_Bookings')">
                        <i class="ri-file-excel-2-line"></i> Export Customer List
                    </button>
                </div>

                <div class="search-container-box">
                    <input type="text" id="searchCustomerInput" onkeyup="filterCustomerData()" placeholder="Search by Guest Name only...">
                </div>

                <div class="quick-filter-row">
                    <span class="filter-badge active" id="cust-all" onclick="setCustomerStatusFilter('all')">All</span>
                    <span class="filter-badge" id="cust-confirmed" onclick="setCustomerStatusFilter('confirmed')">Confirmed</span>
                    <span class="filter-badge" id="cust-pending" onclick="setCustomerStatusFilter('pending')">Pending</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="customerBookingTable">
                        <thead>
                            <tr>
                                <th>Trip ID</th>
                                <th>User Name</th>
                                <th>Homestay Info</th>
                                <th>Travel Date</th>
                                <th>Estimated Budget</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($customer_booking_history)): ?>
                                <tr><td colspan="6" style="text-align:center; color:#999;">No customer trip records logged.</td></tr>
                            <?php else: ?>
                                <?php foreach($customer_booking_history as $booking): ?>
                                <tr>
                                    <td>#<?php echo $booking['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($booking['user']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($booking['homestay_name']); ?> <br><small style="color: #888;"><?php echo htmlspecialchars($booking['state']); ?></small></td>
                                    <td><?php echo $booking['date']; ?></td>
                                    <td><span class="budget-text"><?php echo $booking['budget']; ?></span></td>
                                    <td><span class="status-badge <?php echo $booking['status']; ?>"><?php echo htmlspecialchars($booking['status']); ?></span></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="admin-section-card">
                <div class="header-action-row">
                    <h3><i class="ri-shield-user-line" style="color: #1f804f;"></i> Homestay Owner History</h3>
                    <button type="button" class="btn-excel-export" onclick="exportTableToExcel('ownerActivationTable', 'Homestay_Owner_Activations')" style="background-color: #3b82f6;">
                        <i class="ri-file-excel-2-line"></i> Export Owner List
                    </button>
                </div>

                <div class="search-container-box">
                    <input type="text" id="searchOwnerInput" onkeyup="filterOwnerData()" placeholder="Search by Owner Name only...">
                </div>

                <div class="quick-filter-row">
                    <span class="filter-badge active-blue" id="own-all" onclick="setOwnerStatusFilter('all')">All</span>
                    <span class="filter-badge" id="own-paid" onclick="setOwnerStatusFilter('paid')">Paid</span>
                    <span class="filter-badge" id="own-unpaid" onclick="setOwnerStatusFilter('unpaid')">Unpaid</span>
                </div>

                <div class="table-responsive">
                    <table class="data-table" id="ownerActivationTable">
                        <thead>
                            <tr>
                                <th>Owner ID</th>
                                <th>Owner Name</th>
                                <th>Homestay Name</th>
                                <th>Setup Cost</th>
                                <th>Fee Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($owner_activation_history)): ?>
                                <tr><td colspan="5" style="text-align:center; color:#999;">No registered homestay owners found.</td></tr>
                            <?php else: ?>
                                <?php foreach($owner_activation_history as $owner): ?>
                                <tr>
                                    <td>#OWN-<?php echo $owner['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($owner['owner_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($owner['homestay_name']); ?> <br><small style="color: #888;"><?php echo htmlspecialchars($owner['state']); ?></small></td>
                                    <td>RM 29.00</td>
                                    <td>
                                        <span class="status-badge <?php echo (strtolower($owner['payment_status']) === 'paid') ? 'confirmed' : 'pending'; ?>">
                                            <?php echo htmlspecialchars($owner['payment_status'] ?: 'Unpaid'); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>

    <script>
    // Global tracking states for the active status filters
    let currentCustomerStatus = 'all';
    let currentOwnerStatus = 'all';

    // ------------------ CUSTOMER LOGIC HANDLERS ------------------
    function setCustomerStatusFilter(status) {
        currentCustomerStatus = status;
        
        // Toggle badge visual states
        document.getElementById('cust-all').classList.remove('active');
        document.getElementById('cust-confirmed').classList.remove('active');
        document.getElementById('cust-pending').classList.remove('active');
        
        document.getElementById('cust-' + status).classList.add('active');
        filterCustomerData(); // Recompute matching matrix rows
    }

    function filterCustomerData() {
        let nameInput = document.getElementById("searchCustomerInput").value.toUpperCase();
        let table = document.getElementById("customerBookingTable");
        let tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let trElement = tr[i];
            let nameTd = trElement.getElementsByTagName("td")[1]; // User Name column index
            let statusTd = trElement.getElementsByTagName("td")[5]; // Status badge column index

            if (nameTd && statusTd) {
                let nameText = nameTd.textContent || nameTd.innerText;
                let statusText = statusTd.textContent || statusTd.innerText;
                
                let matchesName = nameText.toUpperCase().indexOf(nameInput) > -1;
                let matchesStatus = false;

                if (currentCustomerStatus === 'all') {
                    matchesStatus = true;
                } else if (currentCustomerStatus === 'confirmed' && statusText.toLowerCase().trim() === 'confirmed') {
                    matchesStatus = true;
                } else if (currentCustomerStatus === 'pending' && statusText.toLowerCase().trim() === 'pending') {
                    matchesStatus = true;
                }

                if (matchesName && matchesStatus) {
                    trElement.style.display = "";
                } else {
                    trElement.style.display = "none";
                }
            }
        }
    }

    // ------------------ OWNER LOGIC HANDLERS ------------------
    function setOwnerStatusFilter(status) {
        currentOwnerStatus = status;
        
        document.getElementById('own-all').classList.remove('active-blue');
        document.getElementById('own-paid').classList.remove('active-blue');
        document.getElementById('own-unpaid').classList.remove('active-blue');
        
        document.getElementById('own-' + status).classList.add('active-blue');
        filterOwnerData();
    }

    function filterOwnerData() {
        let nameInput = document.getElementById("searchOwnerInput").value.toUpperCase();
        let table = document.getElementById("ownerActivationTable");
        let tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let trElement = tr[i];
            let nameTd = trElement.getElementsByTagName("td")[1]; // Owner Name column index
            let statusTd = trElement.getElementsByTagName("td")[4]; // Fee Status column index

            if (nameTd && statusTd) {
                let nameText = nameTd.textContent || nameTd.innerText;
                let statusText = statusTd.textContent || statusTd.innerText;
                
                let matchesName = nameText.toUpperCase().indexOf(nameInput) > -1;
                let matchesStatus = false;

                if (currentOwnerStatus === 'all') {
                    matchesStatus = true;
                } else if (currentOwnerStatus === 'paid' && statusText.toLowerCase().trim() === 'paid') {
                    matchesStatus = true;
                } else if (currentOwnerStatus === 'unpaid' && (statusText.toLowerCase().trim() === 'unpaid' || statusText.toLowerCase().trim() === 'pending')) {
                    matchesStatus = true;
                }

                if (matchesName && matchesStatus) {
                    trElement.style.display = "";
                } else {
                    trElement.style.display = "none";
                }
            }
        }
    }

    // Excel Exporter Engine Function
    function exportTableToExcel(tableID, filename = '') {
        var downloadLink;
        var dataType = 'application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);
        var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
        
        filename = filename ? filename + '.xls' : 'excel_data.xls';
        downloadLink = document.createElement("a");
        document.body.appendChild(downloadLink);
        
        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff' + tableHTML], { type: dataType });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = 'data:' + dataType + ', ' + '\ufeff' + tableHTML;
            downloadLink.download = filename;
            downloadLink.click();
        }
    }
    </script>
    <script src="js/admin.js"></script>
</body>
</html>