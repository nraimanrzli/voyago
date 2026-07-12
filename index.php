<?php
session_start();

// 1. Sambungan ke Database (PDO)
require_once 'toyyibpay_config.php';

// Semakan proses Logout sekiranya dipanggil dari fail ini
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header("Location: index.php?logout=success");
    exit;
}

// 2. Semakan Sesi Aktif (Auto-Redirect)
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    if ($_SESSION['user_role'] === 'Admin') {
        header("Location: admindashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'Traveller/User') {
        header("Location: dashboard.php");
        exit;
    } elseif ($_SESSION['user_role'] === 'Local Homestay Owner') {
        header("Location: localhomestay.php");
        exit;
    }
}

$alert_script = "";

// Check for logout message from URL parameter
if (isset($_GET['logout']) && $_GET['logout'] === 'success') {
    $alert_script = "Swal.fire({
        title: 'Logged Out!',
        text: 'You have been safely disconnected from your session.',
        icon: 'success',
        confirmButtonColor: '#0e3a20'
    });";
}

// 3. Logik Pengendalian Borang (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // --- PROSES LOGIN ---
    if (isset($_POST['action_login'])) {
        $email = trim($_POST['login_email']);
        $password = trim($_POST['login_password']);
        
        if (empty($email) || empty($password)) {
            $alert_script = "Swal.fire('Error!', 'Please fill in all login fields.', 'error');";
        } else {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user_data = $stmt->fetch();
            
            if ($user_data && password_verify($password, $user_data['password'])) {
                $_SESSION['user_id'] = $user_data['id'];
                $_SESSION['user_fullname'] = $user_data['fullname'];
                $_SESSION['user_role'] = $user_data['role'];
                
                // Determine target dashboard path
                $redirect_url = 'dashboard.php';
                if ($user_data['role'] === 'Admin') {
                    $redirect_url = 'admindashboard.php';
                } elseif ($user_data['role'] === 'Local Homestay Owner') {
                    $redirect_url = 'localhomestay.php';
                }
                
                // Render sweetalert sequence instead of instant header drop
                $alert_script = "Swal.fire({
                    title: 'Welcome Back, " . addslashes($_SESSION['user_fullname']) . "!',
                    text: 'Authentication successful. Synchronizing your workspace dashboards...',
                    icon: 'success',
                    showConfirmButton: false,
                    timer: 2000,
                    timerProgressBar: true
                }).then(() => {
                    window.location.href = '$redirect_url';
                });";
            } else {
                $alert_script = "Swal.fire('Failed!', 'Email or password is incorrect.', 'error');";
            }
        }
    }
    
    // --- PROSES PENDAFTARAN (REGISTER) ---
    if (isset($_POST['action_register'])) {
        $fullname = trim($_POST['reg_fullname']);
        $email    = trim($_POST['reg_email']);
        $phone    = trim($_POST['reg_phone']);
        $address  = trim($_POST['reg_address']);
        $password = $_POST['reg_password'];
        $confirm_password = $_POST['reg_confirm_password'];
        $role     = $_POST['reg_role'];
        
        // Validasi Backend
        if (empty($fullname) || empty($email) || empty($phone) || empty($address) || empty($password) || empty($confirm_password) || empty($role)) {
            $alert_script = "Swal.fire('Error!', 'All registration fields are required.', 'error');";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $alert_script = "Swal.fire('Error!', 'The email format is invalid.', 'error');";
        } elseif (strlen($password) < 8) {
            $alert_script = "Swal.fire('Error!', 'Password must be at least 8 characters long.', 'error');";
        } elseif ($password !== $confirm_password) {
            $alert_script = "Swal.fire('Error!', 'Password and confirm password do not match.', 'error');";
        } elseif ($role === 'Admin') {
            $alert_script = "Swal.fire('Forbidden!', 'Admin account registration is not allowed.', 'error');";
        } else {
            // Semak Emel Pendua
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                $alert_script = "Swal.fire('Error!', 'This email is already registered in the system.', 'warning');";
            } else {
                // Simpan ke database dengan password_hash
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                $insert_stmt = $pdo->prepare("INSERT INTO users (fullname, email, phone, address, password, role) VALUES (?, ?, ?, ?, ?, ?)");
                
                if ($insert_stmt->execute([$fullname, $email, $phone, $address, $hashed_password, $role])) {
                    $alert_script = "Swal.fire({
                        title: 'Success!',
                        text: 'Registration successful! Please log in.',
                        icon: 'success'
                    }).then(() => {
                        window.location.href = 'index.php';
                    });";
                } else {
                    $alert_script = "Swal.fire('Error!', 'Failed to save data. Please try again.', 'error');";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($system_name) ?> - Premium Travel Planner</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <div class="leaf-container">
        <div class="leaf"></div>
        <div class="leaf"></div>
        <div class="leaf"></div>
        <div class="leaf"></div>
    </div>

    <div class="main-wrapper">
        
        <section class="left-section">
            
            <div class="glass-card active" id="loginCard">
                <div class="card-header">
                    <h2 class="brand-logo"><?= htmlspecialchars($system_name) ?><span>.</span></h2>
                    <p class="welcome-text">Welcome back! Please log in to your account.</p>
                </div>
                
                <form action="index.php" method="POST" id="formLogin">
                    <div class="input-group">
                        <label for="login_email"><i class="ri-mail-line"></i> Email Address</label>
                        <input type="email" id="login_email" name="login_email" placeholder="example@email.com" required>
                    </div>
                    
                    <div class="input-group password-wrapper">
                        <label for="login_password"><i class="ri-lock-line"></i> Password</label>
                        <input type="password" id="login_password" name="login_password" placeholder="Enter your password here" required>
                        <i class="ri-eye-off-line toggle-password" onclick="togglePasswordVisibility('login_password', this)"></i>
                    </div>
                    
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember">
                            <span>Remember Me</span>
                        </label>
                        <a href="#" class="forgot-pass-link" onclick="Swal.fire('Info', 'Please contact the administrator for password reset at this time.', 'info')">Forgot Password?</a>
                    </div>
                    
                    <button type="submit" name="action_login" class="btn-gradient" id="btnLogin">
                        <span class="btn-text">Log In</span>
                        <div class="spinner hidden"></div>
                    </button>
                </form>
                
                <div class="card-footer">
                    <p>First time here? <a href="#" id="linkToRegister">Register Now</a></p>
                </div>
            </div>

            <div class="glass-card" id="registerCard">
                <div class="card-header">
                    <h2 class="brand-logo">Voy<span>ago</span></h2>
                    <p class="welcome-text">Create your new account with the <?= htmlspecialchars($system_name) ?> community.</p>
                </div>
                
                <form action="index.php" method="POST" id="formRegister">
                    <div class="input-grid">
                        <div class="input-group">
                            <label for="reg_fullname"><i class="ri-user-line"></i> Full Name</label>
                            <input type="text" id="reg_fullname" name="reg_fullname" placeholder="Your full name" required>
                        </div>
                        <div class="input-group">
                            <label for="reg_email"><i class="ri-mail-line"></i> Email Address</label>
                            <input type="email" id="reg_email" name="reg_email" placeholder="example@email.com" required>
                        </div>
                    </div>

                    <div class="input-grid">
                        <div class="input-group">
                            <label for="reg_phone"><i class="ri-phone-line"></i> Phone Number</label>
                            <input type="text" id="reg_phone" name="reg_phone" placeholder="Example: 0123456789" required>
                        </div>
                        <div class="input-group">
                            <label for="reg_role"><i class="ri-user-shared-line"></i> User Role</label>
                            <div class="select-wrapper">
                                <select id="reg_role" name="reg_role" required>
                                    <option value="" disabled selected>Select Your Role</option>
                                    <option value="Traveller/User">Traveller / User</option>
                                    <option value="Local Homestay Owner">Local Homestay Owner</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="reg_address"><i class="ri-map-pin-line"></i> Home Address</label>
                        <input type="text" id="reg_address" name="reg_address" placeholder="Your permanent home address" required>
                    </div>

                    <div class="input-grid">
                        <div class="input-group password-wrapper">
                            <label for="reg_password"><i class="ri-lock-line"></i> Password</label>
                            <input type="password" id="reg_password" name="reg_password" placeholder="Min 8 Characters" required>
                            <i class="ri-eye-off-line toggle-password" onclick="togglePasswordVisibility('reg_password', this)"></i>
                            <div class="strength-meter"><div class="strength-bar" id="strengthBar"></div></div>
                        </div>
                        <div class="input-group password-wrapper">
                            <label for="reg_confirm_password"><i class="ri-shield-check-line"></i> Confirm Password</label>
                            <input type="password" id="reg_confirm_password" name="reg_confirm_password" placeholder="Re-enter password" required>
                            <i class="ri-eye-off-line toggle-password" onclick="togglePasswordVisibility('reg_confirm_password', this)"></i>
                        </div>
                    </div>

                    <button type="submit" name="action_register" class="btn-gradient" id="btnRegister">
                        <span class="btn-text">Register Account</span>
                        <div class="spinner hidden"></div>
                    </button>
                </form>
                
                <div class="card-footer">
                    <p>Already have an account? <a href="#" id="linkToLogin">Log In</a></p>
                </div>
            </div>

        </section>

        <section class="right-section">
            <div class="marketing-content">
                <h1 class="headline">Explore Malaysia<br>With Voy<span>ago.</span></h1>
                <p class="subtitle">Plan your next journey, discover hidden gems, and book amazing homestays all across the country seamlessly.</p>
                
                <div class="social-icons">
                    <a href="https://facebook.com" target="_blank" aria-label="Facebook"><i class="ri-facebook-circle-fill"></i></a>
                    <a href="https://instagram.com" target="_blank" aria-label="Instagram"><i class="ri-instagram-line"></i></a>
                    <a href="https://tiktok.com" target="_blank" aria-label="TikTok"><i class="ri-tiktok-fill"></i></a>
                    <a href="https://<?= htmlspecialchars($system_name) ?>.com" target="_blank" aria-label="Website"><i class="ri-global-line"></i></a>
                </div>
            </div>

            <div class="ads-card-glass">
                <div class="ads-badge"><?= htmlspecialchars($system_name) ?> Space</div>
                <h3>Own a Homestay?</h3>
                <p>Promote your homestay to thousands of active travelers. Register today as a Local Homestay Owner and start receiving direct bookings through <?= htmlspecialchars($system_name) ?>.</p>
                <button type="button" class="btn-ads-join" id="btnAdsJoin">Join Now <i class="ri-arrow-right-line"></i></button>
            </div>
        </section>

    </div>

    <script src="js/index.js"></script>
    <script>
        // Trigger PHP Alert Engine
        <?php echo $alert_script; ?>
    </script>
</body>
</html>
