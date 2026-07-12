// js/booking.js

document.addEventListener("DOMContentLoaded", function() {
    fetchWishlist();
});

let currentHomestayData = null;

// 1. ASYNCHRONOUS AVAILABILITY MATRIX CHECK
function checkAvailability() {
    const checkInDate = document.getElementById('widget-in').value;
    const checkOutDate = document.getElementById('widget-out').value;
    const homestayId = document.getElementById('form-homestay-id').value;
    const alertBox = document.getElementById('availability-status-alert');
    const confirmBtn = document.getElementById('confirm-booking-btn');
    const roomSelectWrapper = document.getElementById('room-selection-wrapper');
    const roomSelect = document.getElementById('widget-room-select');

    if (!checkInDate || !checkOutDate || !homestayId) {
        return;
    }

    const d1 = new Date(checkInDate);
    const d2 = new Date(checkOutDate);
    
    // Date checks
    const today = new Date();
    today.setHours(0,0,0,0);
    
    if (d1 < today) {
        alertBox.style.display = "block";
        alertBox.style.background = "rgba(239, 68, 68, 0.15)";
        alertBox.style.borderLeft = "4px solid #ef4444";
        alertBox.style.color = "#ef4444";
        alertBox.innerHTML = "<i class='ri-error-warning-line'></i> Check-in date cannot be in the past.";
        confirmBtn.disabled = true;
        confirmBtn.style.opacity = "0.5";
        return;
    }

    if (d2 <= d1) {
        alertBox.style.display = "block";
        alertBox.style.background = "rgba(239, 68, 68, 0.15)";
        alertBox.style.borderLeft = "4px solid #ef4444";
        alertBox.style.color = "#ef4444";
        alertBox.innerHTML = "<i class='ri-error-warning-line'></i> Check-out date must be later than check-in date.";
        confirmBtn.disabled = true;
        confirmBtn.style.opacity = "0.5";
        return;
    }

    // Call availability check API
    fetch(`booking.php?action_check_availability=1&homestay_id=${homestayId}&check_in=${checkInDate}&check_out=${checkOutDate}`)
    .then(res => res.json())
    .then(data => {
        alertBox.style.display = "block";
        const pricingLabel = document.getElementById('pricingTypeLabel');
        if (pricingLabel) {
            pricingLabel.innerText = `Pricing model: ${data.pricing_type || 'Whole House'}.`;
        }
        
        if (data.status && data.status === 'error') {
            alertBox.style.background = "rgba(239, 68, 68, 0.15)";
            alertBox.style.borderLeft = "4px solid #ef4444";
            alertBox.style.color = "#ef4444";
            alertBox.innerHTML = `<i class='ri-error-warning-line'></i> ${data.message}`;
            confirmBtn.disabled = true;
            confirmBtn.style.opacity = "0.5";
            roomSelectWrapper.style.display = "none";
            roomSelect.removeAttribute("required");
            roomSelect.innerHTML = "";
            calculateLivePrice();
            return;
        }

        if (data.pricing_type === 'Whole House') {
            roomSelectWrapper.style.display = "none";
            roomSelect.removeAttribute("required");
            roomSelect.innerHTML = "";
            
            if (data.available) {
                alertBox.style.background = "rgba(16, 185, 129, 0.15)";
                alertBox.style.borderLeft = "4px solid #10b981";
                alertBox.style.color = "#10b981";
                alertBox.innerHTML = "<i class='ri-checkbox-circle-line'></i> Perfect! The homestay is vacant on these dates.";
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = "1";

            } else if (data.blocked) {
                alertBox.style.background = "rgba(249, 115, 22, 0.15)";
                alertBox.style.borderLeft = "4px solid #f97316";
                alertBox.style.color = "#f97316";
                alertBox.innerHTML = "<i class='ri-calendar-close-line'></i> Host Unavailable! The host has blocked these dates. Please choose different dates.";
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.5";
            } else {
                alertBox.style.background = "rgba(239, 68, 68, 0.15)";
                alertBox.style.borderLeft = "4px solid #ef4444";
                alertBox.style.color = "#ef4444";
                alertBox.innerHTML = "<i class='ri-close-circle-line'></i> Booked! The whole house is occupied on these dates.";
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.5";
            }
        } else {
            // Per Room matrix
            if (data.available && data.rooms && data.rooms.length > 0) {
                roomSelectWrapper.style.display = "block";
                roomSelect.setAttribute("required", "required");
                
                roomSelect.innerHTML = "";
                data.rooms.forEach(room => {
                    const opt = document.createElement("option");
                    opt.value = room.id;
                    opt.text = `${room.name} (RM ${room.price.toFixed(2)}/night)`;
                    opt.setAttribute("data-price", room.price);
                    roomSelect.appendChild(opt);
                });

                alertBox.style.background = "rgba(16, 185, 129, 0.15)";
                alertBox.style.borderLeft = "4px solid #10b981";
                alertBox.style.color = "#10b981";
                alertBox.innerHTML = `<i class='ri-checkbox-circle-line'></i> Vacancy found! ${data.rooms.length} room(s) available.`;
                confirmBtn.disabled = false;
                confirmBtn.style.opacity = "1";

            } else if (data.blocked) {
                roomSelectWrapper.style.display = "none";
                roomSelect.removeAttribute("required");
                roomSelect.innerHTML = "";

                alertBox.style.background = "rgba(249, 115, 22, 0.15)";
                alertBox.style.borderLeft = "4px solid #f97316";
                alertBox.style.color = "#f97316";
                alertBox.innerHTML = "<i class='ri-calendar-close-line'></i> Host Unavailable! The host has blocked these dates. Please choose different dates.";
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.5";
            } else if (data.reason === 'whole_house_blocked') {
                roomSelectWrapper.style.display = "none";
                roomSelect.removeAttribute("required");
                roomSelect.innerHTML = "";

                alertBox.style.background = "rgba(239, 68, 68, 0.15)";
                alertBox.style.borderLeft = "4px solid #ef4444";
                alertBox.style.color = "#ef4444";
                alertBox.innerHTML = "<i class='ri-close-circle-line'></i> A whole-house booking exists during these dates, so no individual room variants are available.";
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.5";
            } else {
                roomSelectWrapper.style.display = "none";
                roomSelect.removeAttribute("required");
                roomSelect.innerHTML = "";
                
                const noInventoryMessage = data.rooms && data.rooms.length === 0
                    ? "No room variants are configured for this listing. Please contact the host or try another homestay."
                    : "<i class='ri-close-circle-line'></i> Completely Booked! No rooms are vacant during this timeframe.";

                alertBox.style.background = "rgba(239, 68, 68, 0.15)";
                alertBox.style.borderLeft = "4px solid #ef4444";
                alertBox.style.color = "#ef4444";
                alertBox.innerHTML = data.rooms && data.rooms.length === 0 ? noInventoryMessage : noInventoryMessage;
                confirmBtn.disabled = true;
                confirmBtn.style.opacity = "0.5";
            }
        }
        calculateLivePrice();
    })
    .catch(err => {
        console.error("Availability query failed:", err);
    });
}

// 2. DYNAMIC LIVE PRICE CALCULATOR WITH ROOM SELECT INTEGRATION
function calculateLivePrice() {
    const checkInDate = document.getElementById('widget-in').value;
    const checkOutDate = document.getElementById('widget-out').value;
    const basePricePerNight = parseFloat(document.getElementById('form-price-per-night').value);
    const roomSelect = document.getElementById('widget-room-select');
    
    let pricePerNight = basePricePerNight;
    
    // Intercept selected room modifier price if Per Room option is visible
    if (roomSelect && roomSelect.style.display !== "none" && roomSelect.options.length > 0 && roomSelect.selectedIndex !== -1) {
        const selectedOpt = roomSelect.options[roomSelect.selectedIndex];
        const roomPrice = parseFloat(selectedOpt.getAttribute("data-price"));
        if (!isNaN(roomPrice)) {
            pricePerNight = roomPrice;
        }
    }

    if (checkInDate && checkOutDate && pricePerNight) {
        const d1 = new Date(checkInDate);
        const d2 = new Date(checkOutDate);
        
        const timeDiff = d2.getTime() - d1.getTime();
        const days = Math.ceil(timeDiff / (1000 * 3600 * 24));

        if (days > 0) {
            const baseTotal = pricePerNight * days;
            const serviceFee = baseTotal * 0.10; // 10% Voyago fee
            const grandTotal = baseTotal + serviceFee;

            document.getElementById('invoice-days-calc').innerText = `RM${pricePerNight.toFixed(2)} x ${days} nights`;
            document.getElementById('invoice-base-price').innerText = `RM${baseTotal.toFixed(2)}`;
            document.getElementById('invoice-service-fee').innerText = `RM${serviceFee.toFixed(2)}`;
            document.getElementById('invoice-grand-total').innerText = `RM${grandTotal.toFixed(2)}`;
            
            document.getElementById('form-total-amount-cents').value = Math.round(grandTotal * 100);

        } else {
            resetInvoice(pricePerNight);
        }
    } else if (pricePerNight) {
        resetInvoice(pricePerNight);
    }
}

function resetInvoice(pricePerNight = 0) {
    const formattedPrice = parseFloat(pricePerNight).toFixed(2);
    document.getElementById('invoice-days-calc').innerText = `RM${formattedPrice} x 0 nights`;
    document.getElementById('invoice-base-price').innerText = "RM0.00";
    document.getElementById('invoice-service-fee').innerText = "RM0.00";
    document.getElementById('invoice-grand-total').innerText = "RM0.00";
}

// 3. TOGGLE WISHLIST MODULAR SYSTEM (AJAX)
function toggleWishlist(homestayId, element) {
    fetch('wishlist_handler.php?action=toggle', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ homestay_id: homestayId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'added') {
            element.classList.add('active');
            element.innerHTML = `<i class="ri-heart-fill"></i>`;
        } else if (data.status === 'removed') {
            element.classList.remove('active');
            element.innerHTML = `<i class="ri-heart-line"></i>`;
        }
        fetchWishlist();
    });
}

// FETCH & RENDER WISHLIST SIDEBAR PANELS
function fetchWishlist() {
    fetch('wishlist_handler.php?action=fetch')
    .then(response => response.json())
    .then(res => {
        const container = document.getElementById('wishlist-items-container');
        const countSpan = document.getElementById('wishlist-count');
        
        if (res.status === 'success') {
            countSpan.innerText = res.data.length;
            if (res.data.length > 0) {
                container.innerHTML = '';
                res.data.forEach(item => {
                    const img = item.image_url ? item.image_url : 'images/default_place.jpg';
                    container.innerHTML += `
                        <div class="wishlist-item-row" style="display:flex; gap:10px; margin-bottom:10px; align-items:center;">
                            <img src="${img}" alt="Wishlist Item" style="width:50px; height:50px; object-fit:cover; border-radius:6px;" onerror="this.src='images/default_place.jpg';">
                            <div>
                                <h5 style="color:white; font-size:0.8rem; margin:0;">${item.name}</h5>
                                <small style="color:#f4cb66; font-weight:bold; font-size:0.75rem;">RM${parseFloat(item.price_per_night).toFixed(2)}</small>
                            </div>
                        </div>
                    `;
                });
            } else {
                container.innerHTML = '<p class="empty-text" style="color:rgba(255,255,255,0.4); font-size:0.8rem; text-align:center; margin:0;">No wishlist items saved.</p>';
            }
        }
    });
}

// 4. BOOKING MODAL CONTROL PANEL
function openBookingModal(homestayData) {
    currentHomestayData = homestayData;
    
    document.getElementById('modal-title').innerText = homestayData.name;
    document.getElementById('modal-location').innerHTML = `<i class="ri-map-pin-line"></i> ${homestayData.district}, ${homestayData.state}`;
    document.getElementById('modal-desc').innerText = homestayData.description || 'No additional description provided for this premium stay.';
    
    // Set hidden keys
    document.getElementById('form-homestay-id').value = homestayData.id;
    const pricingTypeInput = document.getElementById('form-pricing-type');
    if (pricingTypeInput) {
        pricingTypeInput.value = homestayData.pricing_type || 'Whole House';
    }
    document.getElementById('form-price-per-night').value = homestayData.price_per_night;
    
    // Build modal gallery images
    const galleryThumbs = document.getElementById('gallery-thumbs');
    const galleryMainImg = document.getElementById('gallery-main-img');
    const galleryImages = [];
    if (homestayData.cover_image) {
        galleryImages.push(homestayData.cover_image);
    }
    if (homestayData.facility_images) {
        try {
            const decoded = JSON.parse(homestayData.facility_images);
            if (Array.isArray(decoded)) {
                decoded.forEach(img => {
                    if (img && typeof img === 'string') {
                        galleryImages.push(img);
                    }
                });
            }
        } catch (e) {
            // fallback if facility_images stored as comma-separated list
            galleryImages.push(...String(homestayData.facility_images).split(',').map(i => i.trim()).filter(Boolean));
        }
    }
    if (galleryImages.length === 0) {
        galleryImages.push('images/default_place.jpg');
    }
    galleryMainImg.src = galleryImages[0];
    if (galleryThumbs) {
        galleryThumbs.innerHTML = '';
        galleryImages.forEach((img, idx) => {
            const thumb = document.createElement('img');
            thumb.src = img;
            thumb.alt = 'Gallery thumb';
            thumb.style.width = '64px';
            thumb.style.height = '64px';
            thumb.style.objectFit = 'cover';
            thumb.style.borderRadius = '12px';
            thumb.style.cursor = 'pointer';
            thumb.style.border = idx === 0 ? '2px solid #f4cb66' : '2px solid transparent';
            thumb.style.boxShadow = '0 4px 12px rgba(0,0,0,0.25)';
            thumb.addEventListener('click', function() {
                galleryMainImg.src = img;
                galleryThumbs.querySelectorAll('img').forEach((el, i) => {
                    el.style.border = i === idx ? '2px solid #f4cb66' : '2px solid transparent';
                });
            });
            galleryThumbs.appendChild(thumb);
        });
    }

    // Reset status fields
    document.getElementById('widget-in').value = "";
    document.getElementById('widget-out').value = "";
    document.getElementById('room-selection-wrapper').style.display = "none";
    document.getElementById('widget-room-select').innerHTML = "";
    document.getElementById('availability-status-alert').style.display = "none";

    const pricingLabel = document.getElementById('pricingTypeLabel');
    if (pricingLabel) {
        pricingLabel.innerText = homestayData.pricing_type === 'Per Room'
            ? 'This stay uses Per Room pricing. Choose your check-in and check-out dates to select an available room variant.'
            : 'This stay uses Whole House pricing. Choose your dates to check availability.';
    }

    const confirmBtn = document.getElementById('confirm-booking-btn');
    confirmBtn.disabled = false;
    confirmBtn.style.opacity = "1";

    // Render Facilities
    const facContainer = document.getElementById('modal-facilities');
    facContainer.innerHTML = '';
    
    let facs = [];
    try {
        facs = JSON.parse(homestayData.facilities);
    } catch(e) {
        facs = homestayData.facilities ? homestayData.facilities.split(',') : [];
    }

    if (Array.isArray(facs) && facs.length > 0) {
        facs.forEach(f => {
            facContainer.innerHTML += `<span class="fac-tag" style="background:rgba(255,255,255,0.1); border:1px solid rgba(255,255,255,0.15); color:white; font-size:0.75rem; padding:4px 10px; border-radius:30px; margin-right:6px; display:inline-block; margin-bottom:6px;"><i class="ri-checkbox-circle-fill" style="color:#f4cb66; margin-right:4px;"></i> ${f.trim()}</span>`;
        });
    } else {
        facContainer.innerHTML = '<span style="font-size:0.8rem; color:rgba(255,255,255,0.5);">Standard Home Amenities Included</span>';
    }

    resetInvoice(homestayData.price_per_night);
    document.getElementById('bookingModal').style.display = 'flex';
}

function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
}