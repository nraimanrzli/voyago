document.addEventListener("DOMContentLoaded", () => {
    // DOM Elements
    const loginCard = document.getElementById("loginCard");
    const registerCard = document.getElementById("registerCard");
    const linkToRegister = document.getElementById("linkToRegister");
    const linkToLogin = document.getElementById("linkToLogin");
    const btnAdsJoin = document.getElementById("btnAdsJoin");
    
    const formLogin = document.getElementById("formLogin");
    const formRegister = document.getElementById("formRegister");
    const regPassword = document.getElementById("reg_password");
    const strengthBar = document.getElementById("strengthBar");

    // Form Switching Animations
    if (linkToRegister) {
        linkToRegister.addEventListener("click", (e) => {
            e.preventDefault();
            switchCard(loginCard, registerCard);
        });
    }

    if (linkToLogin) {
        linkToLogin.addEventListener("click", (e) => {
            e.preventDefault();
            switchCard(registerCard, loginCard);
        });
    }

    if (btnAdsJoin) {
        btnAdsJoin.addEventListener("click", () => {
            switchCard(loginCard, registerCard);
            const roleSelect = document.getElementById("reg_role");
            if (roleSelect) {
                roleSelect.value = "Local Homestay Owner";
            }
        });
    }

    function switchCard(fromCard, toCard) {
        if (!fromCard || !toCard) return;
        fromCard.style.opacity = "0";
        fromCard.style.transform = "translateY(20px)";
        setTimeout(() => {
            fromCard.classList.remove("active");
            toCard.classList.add("active");
            setTimeout(() => {
                toCard.style.opacity = "1";
                toCard.style.transform = "translateY(0)";
            }, 50);
        }, 300);
    }

    // Password strength logic
    if (regPassword && strengthBar) {
        regPassword.addEventListener("input", () => {
            const val = regPassword.value;
            let score = 0;

            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            // Render warna bar berdasarkan tahap keselamatan
            if (score === 0) {
                strengthBar.style.width = "0%";
            } else if (score === 1) {
                strengthBar.style.width = "25%";
                strengthBar.style.backgroundColor = "#ff4d4d"; // Merah
            } else if (score === 2) {
                strengthBar.style.width = "50%";
                strengthBar.style.backgroundColor = "#ffa500"; // Jingga
            } else if (score === 3) {
                strengthBar.style.width = "75%";
                strengthBar.style.backgroundColor = "#66cc66"; // Hijau Cair
            } else if (score === 4) {
                strengthBar.style.width = "100%";
                strengthBar.style.backgroundColor = "#d4af37"; // Emas (Kuat)
            }
        });
    }

    // ==========================================================================
    // ADDED: VOYAGO GLOBAL LOGOUT INTERCEPTOR & CLEANER MATRIX
    // ==========================================================================
    
    // Intercept any click on anchors that trigger a logout action across the app
    const logoutLinks = document.querySelectorAll('a[href*="action=logout"]');
    logoutLinks.forEach(link => {
        link.addEventListener("click", function(e) {
            e.preventDefault(); // Stop instant standard page jump redirection
            const logoutTargetUrl = this.getAttribute("href");

            Swal.fire({
                title: 'Confirm System Logout?',
                text: "Are you sure you want to securely disconnect from your active session?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#0e3a20', // Matching Voyago Theme Green Accent
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Sign Out',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = logoutTargetUrl;
                }
            });
        });
    });

    // URL parameter cleaner to prevent recurring SweetAlert notifications upon manual page reload
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('logout') || urlParams.has('login')) {
        setTimeout(() => {
            window.history.replaceState({}, document.title, window.location.pathname);
        }, 500);
    }
});

// Password visibility show/hide toggle function
function togglePasswordVisibility(fieldId, iconElement) {
    const targetField = document.getElementById(fieldId);
    if (!targetField) return;
    
    if (targetField.type === "password") {
        targetField.type = "text";
        iconElement.classList.remove("ri-eye-off-line");
        iconElement.classList.add("ri-eye-line");
    } else {
        targetField.type = "password";
        iconElement.classList.remove("ri-eye-line");
        iconElement.classList.add("ri-eye-off-line");
    }
}