// ==========================================================================
// VOYAGO GLOBAL ADMIN INTERACTIVE ENGINE
// ==========================================================================

document.addEventListener("DOMContentLoaded", () => {
    const menuBtn = document.getElementById("menu-btn");
    const navLinks = document.getElementById("nav-links");
    const addGemForm = document.getElementById("add-gem-form");

    // 1. Mobile Dynamic Menu Toggle Sliding Layer
    if (menuBtn && navLinks) {
        menuBtn.addEventListener("click", () => {
            navLinks.classList.toggle("open");
            
            // Switch UI icons on menu toggle action
            const menuIcon = menuBtn.querySelector("i");
            if (menuIcon) {
                if (navLinks.classList.contains("open")) {
                    menuIcon.className = "ri-close-line";
                } else {
                    menuIcon.className = "ri-menu-line";
                }
            }
        });
    }

    // 2. Add New Attraction Submission Success Framework Handler
    if (addGemForm) {
        addGemForm.addEventListener("submit", (e) => {
            e.preventDefault();

            // Extract input node variables clearly
            const targetState = document.getElementById("gem-state").value;
            const placeName = document.getElementById("gem-name").value;

            // Simple visual response alert system
            alert(`Success! "${placeName}" was added to the database under the state of ${targetState}.`);
            
            // Reset input values back to empty status 
            addGemForm.reset();
        });
    }
});