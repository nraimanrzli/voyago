document.addEventListener("DOMContentLoaded", () => {
    const btnLaunchWizard = document.getElementById("btnLaunchWizard");
    const btnCloseWizard = document.getElementById("btnCloseWizard");
    const wizardOverlay = document.getElementById("wizardOverlay");
    const steps = document.querySelectorAll(".wizard-step-pane");
    const wizardBar = document.getElementById("wizardBar");
    const btnPrev = document.getElementById("btnWizardPrev");
    const btnNext = document.getElementById("btnWizardNext");
    const btnSubmit = document.getElementById("btnWizardSubmit");

    // Dynamic Room elements
    const pricingTypeWhole = document.getElementById("pricing_type_whole");
    const pricingTypeRoom = document.getElementById("pricing_type_room");
    const totalRoomsWrapper = document.getElementById("total_rooms_wrapper");
    const totalRoomsInput = document.getElementById("h_total_rooms");
    const roomInputsList = document.getElementById("room_inputs_list");

    let currentStep = 1;
    const totalSteps = 7;

    // 1. DYNAMIC PRICING TYPE FIELDS TOGGLE
    function toggleRoomFields() {
        if (pricingTypeRoom && pricingTypeRoom.checked) {
            totalRoomsWrapper.style.display = "block";
            const count = parseInt(totalRoomsInput.value) || 1;
            roomInputsList.innerHTML = "";
            for (let i = 1; i <= count; i++) {
                const row = document.createElement("div");
                row.className = "room-input-row";
                row.style.marginTop = "8px";
                row.innerHTML = `
                    <div style="display:flex; flex-direction:column; gap:6px; background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; border:1px solid rgba(255,255,255,0.1);">
                        <span style="font-weight:700; font-size:0.8rem; color:#f4cb66;">Room ${i} Configuration</span>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <input type="text" name="room_names[]" placeholder="Room Name (e.g. Deluxe Suite)" required style="flex:2; padding:8px; border-radius:6px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.2); color:white; min-width:180px;">
                            <select name="room_types[]" style="flex:1; padding:8px; border-radius:6px; background:rgba(14,58,32,0.9); border:1px solid rgba(255,255,255,0.2); color:white; min-width:100px;">
                                <option value="Single Room">Single Room</option>
                                <option value="Double Room">Double Room</option>
                                <option value="Family Suite">Family Suite</option>
                                <option value="Deluxe Studio">Deluxe Studio</option>
                            </select>
                        </div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap; margin-top:6px;">
                            <input type="number" name="room_prices[]" placeholder="Base Price Per Night (RM)" value="150.00" step="0.01" required style="flex:1; padding:8px; border-radius:6px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.2); color:white;">
                            <input type="number" name="room_guests[]" placeholder="Max Guests" value="2" min="1" required style="flex:1; padding:8px; border-radius:6px; background:rgba(0,0,0,0.3); border:1px solid rgba(255,255,255,0.2); color:white;">
                        </div>
                    </div>
                `;
                roomInputsList.appendChild(row);
            }
        } else {
            if (totalRoomsWrapper) totalRoomsWrapper.style.display = "none";
            roomInputsList.innerHTML = "";
        }
    }

    if (pricingTypeWhole && pricingTypeRoom) {
        pricingTypeWhole.addEventListener("change", toggleRoomFields);
        pricingTypeRoom.addEventListener("change", toggleRoomFields);
        totalRoomsInput.addEventListener("input", toggleRoomFields);
    }

    if (btnLaunchWizard) {
        btnLaunchWizard.addEventListener("click", () => {
            wizardOverlay.classList.add("open");
            currentStep = 1;
            updateWizardDisplay();
            toggleRoomFields();
        });
    }

    if (btnCloseWizard) {
        btnCloseWizard.addEventListener("click", () => {
            wizardOverlay.classList.remove("open");
        });
    }

    btnNext.addEventListener("click", () => {
        if (validateCurrentStep(currentStep)) {
            if (currentStep < totalSteps) {
                currentStep++;
                updateWizardDisplay();
            }
        }
    });

    btnPrev.addEventListener("click", () => {
        if (currentStep > 1) {
            currentStep--;
            updateWizardDisplay();
        }
    });

    function updateWizardDisplay() {
        steps.forEach(pane => {
            pane.classList.remove("active");
            if (parseInt(pane.getAttribute("data-step")) === currentStep) {
                pane.classList.add("active");
            }
        });
        const percentage = (currentStep / totalSteps) * 100;
        wizardBar.style.width = `${percentage}%`;

        if (currentStep === 1) {
            btnPrev.classList.add("hidden");
        } else {
            btnPrev.classList.remove("hidden");
        }

        if (currentStep === totalSteps) {
            btnNext.classList.add("hidden");
            btnSubmit.classList.remove("hidden");
        } else {
            btnNext.classList.remove("hidden");
            btnSubmit.classList.add("hidden");
        }
    }

    // 2. FORM & MULTI-UPLOAD IMAGE COUNTS VALIDATION
    function validateCurrentStep(step) {
        const currentPane = document.querySelector(`.wizard-step-pane[data-step="${step}"]`);
        
        // Step 3: Require minimum of 3 facilities checkboxes selected
        if (step === 3) {
            const checkedBoxes = currentPane.querySelectorAll("input[name='facilities[]']:checked");
            if (checkedBoxes.length < 3) {
                Swal.fire("Facilities Required", "Please select at least 3 facilities before proceeding.", "warning");
                return false;
            }
            return true;
        }

        // Step 4: Enforce 1 cover photo and at least 3 facility photos
        if (step === 4) {
            const coverInput = currentPane.querySelector("input[name='cover_image']");
            const facilityInput = currentPane.querySelector("input[name='facility_images[]']");
            
            if (!coverInput.files || coverInput.files.length !== 1) {
                Swal.fire("Cover Image Required", "Please upload exactly 1 Cover Image (Main Exterior Photo).", "warning");
                return false;
            }
            if (!facilityInput.files || facilityInput.files.length < 3) {
                Swal.fire("Facility Photos Required", "Please upload at least 3 Facility Photos.", "warning");
                return false;
            }
            return true;
        }

        const requiredFields = currentPane.querySelectorAll("[required]");
        let valid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                valid = false;
                field.style.borderColor = "#ef4444";
            } else {
                field.style.borderColor = "rgba(255,255,255,0.2)";
            }
        });
        
        if (!valid) {
            Swal.fire("Incomplete Fields", "Please populate all required fields marked with (*) before proceeding.", "warning");
        }
        return valid;
    }

    // Intercept "Pay RM29 Fee" buttons to open local mock payment modal directly
    // Only intercept if the link contains a homestay_id param (not report View/Download links)
    document.querySelectorAll(".btn-pay-trigger").forEach(btn => {
        btn.addEventListener("click", (e) => {
            const url = new URL(btn.href, window.location.origin);
            const homestayId = url.searchParams.get("homestay_id");
            // Only hijack payment buttons, let report links open normally
            if (!homestayId) return;
            e.preventDefault();
            const card = btn.closest(".listing-premium-card");
            const name = card ? card.querySelector("h4").innerText : "Your Property";
            openMockToyyibPay(homestayId, encodeURIComponent(name));
        });
    });
});

let activePayingId = null;

function openMockToyyibPay(id, propName) {
    activePayingId = id;
    document.getElementById("tpRefId").innerText = `#VYG-2026-00${id}`;
    document.getElementById("tpPropName").innerText = decodeURIComponent(propName);
    document.getElementById("toyyibpayOverlay").classList.add("open");
}

function closeMockToyyibPay() {
    document.getElementById("toyyibpayOverlay").classList.remove("open");
    activePayingId = null;
}

function selectMockBank(element) {
    const cards = document.querySelectorAll(".bank-card");
    cards.forEach(c => c.classList.remove("active"));
    element.classList.add("active");
}

document.getElementById("btnConfirmMockPayment").addEventListener("click", () => {
    if (activePayingId) {
        // Redirect directly to return handler to confirm paid state on localhost
        window.location.href = `toyyibpay_return.php?status_id=1&billcode=MOCKTP${activePayingId}&order_no=${activePayingId}`;
    }
});