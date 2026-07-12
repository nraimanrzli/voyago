<?php
// add_attractions.php - Add New Places Form
require_once 'toyyibpay_config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> Admin Portal - Add Attractions</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin.css?v=<?= time() ?>">
</head>
<body>

    <nav>
        <div class="nav__header">
            <div class="nav__logo"><a href="admin.php" class="logo"><?= htmlspecialchars($system_name) ?><span>.</span></a></div>
            <div class="nav__menu__btn" id="menu-btn"><i class="ri-menu-line"></i></div>
        </div>
        <ul class="nav__links" id="nav-links">
            <li><a href="booking_history.php">Booking History</a></li>
            <li><a href="add_attractions.php" style="color: #d4af37; font-weight: 600;">Add Attractions</a></li>
            <li><a href="admin_commission.php">Admin Commission</a></li>
            <li><a href="admin.php">Dashboard</a></li>
            <li><a href="admin_homestay.php">Homestay Approvals</a></li>
            <li><a href="index.php?action=logout" style="background: #e74c3c; color: white; padding: 6px 12px; border-radius: 4px; font-size: 0.85rem;">LOGOUT</a></li>
        </ul>
    </nav>

    <div class="admin-workspace-container">
        <header class="admin-workspace-header">
            <div class="header-titles">
                <h1>Manage Places & Locations</h1>
                <p>Use this form to expand your database by adding beautiful new spots, activities, or hidden attractions across Malaysia.</p>
            </div>
        </header>

        <section class="admin-section-card">
            <div class="section-card-header">
                <h3><i class="ri-add-circle-line"></i> Add a New Destination Spot</h3>
            </div>
            <form id="add-gem-form" class="form-grid-layout">
                <div class="form-group">
                    <label>State Location</label>
                    <select id="gem-state" required>
                        <option value="Johor">Johor</option>
                        <option value="Terengganu">Terengganu</option>
                        <option value="Sabah">Sabah</option>
                        <option value="Sarawak">Sarawak</option>
                        <option value="Pulau Pinang">Pulau Pinang</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Travel Category</label>
                    <select id="gem-category" required>
                        <option value="Beach">Beach & Coastline</option>
                        <option value="Nature">Nature & Rainforests</option>
                        <option value="Adventure">Adventure Sports</option>
                        <option value="Culture">Local Culture & Heritage</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Name of the Place</label>
                    <input type="text" id="gem-name" placeholder="e.g. Rawa Island Resort" required>
                </div>
                <div class="form-group full-width-field">
                    <label>About this Destination (Short Description)</label>
                    <textarea id="gem-desc" rows="3" placeholder="Share details about what makes this place unique, local tips, or scenery instructions..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Recommended Visit Time</label>
                    <input type="text" id="gem-time" placeholder="e.g. 2 to 3 Hours" required>
                </div>
                <div class="form-group full-width-field">
                    <button type="submit" class="submit-btn-node">Save Location to Website</button>
                </div>
            </form>
        </section>

        <section class="admin-section-card">
            <div class="section-card-header">
                <h3><i class="ri-map-pin-user-line"></i> Active Regions</h3>
            </div>
            <div style="display: flex; gap: 15px; flex-wrap: wrap; margin-top: 1rem;">
                <span style="background: rgba(6,64,43,0.06); padding: 8px 14px; border-radius: 0.5rem; font-size: 0.88rem; font-weight:600; color:var(--primary-color);"><i class="ri-checkbox-blank-circle-fill" style="color:var(--accent-gold); font-size:0.6rem;"></i> Terengganu Active</span>
                <span style="background: rgba(6,64,43,0.06); padding: 8px 14px; border-radius: 0.5rem; font-size: 0.88rem; font-weight:600; color:var(--primary-color);"><i class="ri-checkbox-blank-circle-fill" style="color:var(--accent-gold); font-size:0.6rem;"></i> Sabah Active</span>
                <span style="background: rgba(6,64,43,0.06); padding: 8px 14px; border-radius: 0.5rem; font-size: 0.88rem; font-weight:600; color:var(--primary-color);"><i class="ri-checkbox-blank-circle-fill" style="color:var(--accent-gold); font-size:0.6rem;"></i> Pulau Pinang Active</span>
            </div>
        </section>
    </div>

    <script src="js/admin.js"></script>
</body>
</html>
