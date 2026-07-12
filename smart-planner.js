// ==================== VOYAGO SMART PLANNER INTERACTIVE ENGINE ====================

document.addEventListener("DOMContentLoaded", () => {
    const startDateInput = document.getElementById("start-date");
    const endDateInput = document.getElementById("end-date");
    const cardsContainer = document.getElementById("planner-cards-container");
    const nextStepBtn = document.getElementById("next-step-btn");
    const toast = document.getElementById("toast-notif");
    const searchInput = document.getElementById("gem-search-input");
    const categoryTabsRow = document.getElementById("category-tabs-row");
    const itinerarySec = document.getElementById("itinerary-section");

    // Dynamic Database injected from PHP
    const hiddenGems = typeof hiddenGemsDatabase !== "undefined" ? hiddenGemsDatabase : {};

    // 1. DYNAMICAL SETTING TODAY AS MIN DATE
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    const todayStr = `${yyyy}-${mm}-${dd}`;
    
    if (startDateInput) {
        startDateInput.setAttribute("min", todayStr);
    }
    if (endDateInput) {
        endDateInput.setAttribute("min", todayStr);
    }

    // Recommendation Dropdown Init
    let dropdown = document.querySelector(".recommendations-dropdown");
    if (!dropdown && searchInput) {
        dropdown = document.createElement("div");
        dropdown.className = "recommendations-dropdown";
        dropdown.style.display = "none";
        searchInput.parentNode.style.position = "relative";
        searchInput.parentNode.appendChild(dropdown);
    }

    function showRecommendations(query = "") {
        if (!dropdown) return;
        dropdown.innerHTML = "";
        
        const cleanQuery = query.toLowerCase().trim();
        let matches = [];

        if (cleanQuery === "") {
            const checkedStates = Array.from(document.querySelectorAll(".state-checkbox:checked"))
                                       .map(cb => cb.value);
            
            checkedStates.forEach(state => {
                if (hiddenGems[state]) {
                    Object.entries(hiddenGems[state].gems).forEach(([category, gemsArray]) => {
                        gemsArray.forEach(gem => {
                            matches.push({ name: gem.name, state: state });
                        });
                    });
                }
            });
        } else {
            for (const [stateName, stateDetails] of Object.entries(hiddenGems)) {
                for (const [category, gemsArray] of Object.entries(stateDetails.gems)) {
                    gemsArray.forEach(gem => {
                        if (gem.name.toLowerCase().includes(cleanQuery) || stateName.toLowerCase().includes(cleanQuery)) {
                            matches.push({ name: gem.name, state: stateName });
                        }
                    });
                }
            }
        }

        const visibleMatches = matches.slice(0, 6);

        if (visibleMatches.length === 0) {
            if (cleanQuery !== "") {
                dropdown.innerHTML = `<div style="padding:12px; font-size:0.8rem; color:rgba(255,255,255,0.4); text-align:center;">No gems found</div>`;
                dropdown.style.display = "block";
            } else {
                dropdown.style.display = "none";
            }
            return;
        }

        visibleMatches.forEach(match => {
            const row = document.createElement("div");
            row.className = "recommendation-item";
            row.innerHTML = `<span>${match.name}</span> <span class="rec-state">${match.state}</span>`;
            
            row.addEventListener("click", () => {
                if (searchInput) searchInput.value = match.name;
                dropdown.style.display = "none";
                quickSearch(match.name, match.state);
            });
            dropdown.appendChild(row);
        });

        dropdown.style.display = "block";
    }

    if (searchInput) {
        searchInput.addEventListener("focus", () => {
            plannerState.searchQuery = searchInput.value.trim();
            showRecommendations(plannerState.searchQuery);
        });
        searchInput.addEventListener("input", (e) => {
            plannerState.searchQuery = e.target.value.trim();
            showRecommendations(plannerState.searchQuery);
            renderGeneratedCards();
        });

        document.addEventListener("click", (e) => {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    }

    document.querySelectorAll(".state-checkbox").forEach(box => {
        box.addEventListener("change", () => {
            if (document.activeElement === searchInput || (dropdown && dropdown.style.display === "block")) {
                showRecommendations(searchInput.value);
            }
        });
    });

    window.quickSearch = function(gemName, forceState = null) {
        if (searchInput) searchInput.value = gemName;
        if (dropdown) dropdown.style.display = "none";

        let targetedState = forceState;
        if (!targetedState) {
            for (const [stateName, stateDetails] of Object.entries(hiddenGems)) {
                for (const [category, gemsArray] of Object.entries(stateDetails.gems)) {
                    if (gemsArray.some(g => g.name.toLowerCase() === gemName.toLowerCase())) {
                        targetedState = stateName;
                        break;
                    }
                }
            }
        }

        if (targetedState) {
            document.querySelectorAll(".state-checkbox").forEach(cb => cb.checked = false);
            const checkEl = document.querySelector(`.state-checkbox[value="${targetedState}"]`);
            if (checkEl) checkEl.checked = true;

            plannerState.selectedStates = [targetedState];
            triggerPlannerGeneration();
        }
    };

    let plannerState = {
        startDate: "",
        endDate: "",
        durationDays: 0,
        selectedStates: [],
        selectedTypes: [],
        selectedCategoryTab: "All",
        searchQuery: "",
        generatedData: []
    };

    function handleDateCalculation() {
        const startVal = startDateInput.value;
        const endVal = endDateInput.value;
        
        if (startVal && endVal) {
            const start = new Date(startVal);
            const end = new Date(endVal);
            
            if (end >= start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1; 
                
                plannerState.startDate = startVal;
                plannerState.endDate = endVal;
                plannerState.durationDays = diffDays;
                
                const radios = document.getElementsByName("duration");
                radios.forEach(r => r.checked = false); 
                
                if (diffDays === 1) {
                    const r = document.querySelector('input[name="duration"][value="1"]');
                    if (r) r.checked = true;
                } else if (diffDays === 2) {
                    const r = document.querySelector('input[name="duration"][value="2"]');
                    if (r) r.checked = true;
                } else if (diffDays === 3 || diffDays === 4) {
                    const r = document.querySelector('input[name="duration"][value="3"]');
                    if (r) r.checked = true;
                } else if (diffDays >= 5) {
                    const r = document.querySelector('input[name="duration"][value="5"]');
                    if (r) r.checked = true;
                }
                
                triggerPlannerGeneration();
            } else {
                endDateInput.value = startVal;
                handleDateCalculation();
            }
        }
    }

    if (startDateInput) startDateInput.addEventListener("change", handleDateCalculation);
    if (endDateInput) endDateInput.addEventListener("change", handleDateCalculation);

    document.querySelectorAll(".state-checkbox").forEach(cb => {
        cb.addEventListener("change", () => {
            const checkedStates = Array.from(document.querySelectorAll(".state-checkbox:checked")).map(el => el.value);
            plannerState.selectedStates = checkedStates;
            triggerPlannerGeneration();
        });
    });

    document.querySelectorAll(".type-checkbox").forEach(cb => {
        cb.addEventListener("change", () => {
            const checkedTypes = Array.from(document.querySelectorAll(".type-checkbox:checked")).map(el => el.value);
            plannerState.selectedTypes = checkedTypes;
            triggerPlannerGeneration();
        });
    });

    document.querySelectorAll('input[name="duration"]').forEach(radio => {
        radio.addEventListener("change", (e) => {
            if (!plannerState.startDate) {
                const val = parseInt(e.target.value);
                plannerState.durationDays = val === 5 ? 5 : val;
                triggerPlannerGeneration();
            }
        });
    });

    // 2. DYNAMIC CATEGORY TABS EVENT HANDLING (REACTIVE GRID RE-RENDER)
    const categoryTabs = document.querySelectorAll(".category-tab");
    categoryTabs.forEach(tab => {
        tab.addEventListener("click", (e) => {
            categoryTabs.forEach(t => t.classList.remove("active"));
            const targetTab = e.currentTarget;
            targetTab.classList.add("active");
            
            plannerState.selectedCategoryTab = targetTab.getAttribute("data-category");
            renderGeneratedCards();
        });
    });

    function triggerPlannerGeneration() {
        if (!cardsContainer) return;

        if (plannerState.selectedStates.length === 0) {
            cardsContainer.innerHTML = `
                <div class="empty-state">
                    <i class="ri-map-pin-time-line"></i>
                    <h3>Start Planning Your Journey</h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 8px;">
                        Select a travel date, state and travel preferences on the sidebar to instantly generate your smart itinerary.
                    </p>
                </div>
            `;
            plannerState.generatedData = [];
            
            if (categoryTabsRow) categoryTabsRow.style.display = "none";
            if (itinerarySec) itinerarySec.style.display = "none";
            return;
        }

        if (categoryTabsRow) categoryTabsRow.style.display = "flex";

        cardsContainer.innerHTML = `
            <div class="loading-state">
                <i class="ri-loader-4-line"></i>
                <h3>Analyzing Destinations...</h3>
                <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 8px;">Assembling perfect hidden gems matching your travel persona.</p>
            </div>
        `;

        setTimeout(() => {
            renderGeneratedCards();
        }, 350);
    }

    // 3. REACTIVE STATE & CATEGORY COMBINED FILTERING LOGIC
    function renderGeneratedCards() {
        if (!cardsContainer) return;
        cardsContainer.innerHTML = "";
        plannerState.generatedData = [];

        const selectedStates = plannerState.selectedStates;
        if (selectedStates.length === 0) return;

        // Pool places based on selected States and active Category Filter Tab
        let filteredPlaces = [];
        selectedStates.forEach(stateName => {
            const stateData = hiddenGems[stateName];
            if (!stateData) return;

            let targetCategories = [];
            if (plannerState.selectedCategoryTab === "All") {
                targetCategories = plannerState.selectedTypes.length > 0 
                    ? plannerState.selectedTypes 
                    : ["Beach", "Nature", "Adventure", "Culture", "Food"];
            } else {
                targetCategories = [plannerState.selectedCategoryTab];
            }

            targetCategories.forEach(cat => {
                if (stateData.gems[cat]) {
                    stateData.gems[cat].forEach(gem => {
                        filteredPlaces.push({ ...gem, state: stateName, category: cat });
                    });
                }
            });
        });

        const searchQuery = plannerState.searchQuery.toLowerCase().trim();
        if (searchQuery !== "") {
            filteredPlaces = filteredPlaces.filter(place => {
                return place.name.toLowerCase().includes(searchQuery)
                    || place.state.toLowerCase().includes(searchQuery)
                    || place.desc.toLowerCase().includes(searchQuery)
                    || place.category.toLowerCase().includes(searchQuery);
            });
        }

        // Render attractions as individual cards in the grid dynamically
        if (filteredPlaces.length === 0) {
            cardsContainer.innerHTML = `
                <div class="empty-state" style="grid-column: 1 / -1;">
                    <i class="ri-map-pin-2-line"></i>
                    <h3>No Attractions Found</h3>
                    <p style="font-size: 0.85rem; color: rgba(255,255,255,0.6); margin-top: 8px;">
                        No places match the selected filters. Try switching category tabs or state configurations.
                    </p>
                </div>
            `;
        } else {
            // FIX THE OVERWRITING BUG: Accumulate HTML and assign once dynamically
            let cardsHtml = "";
            filteredPlaces.forEach(place => {
                // Ensure proper folder mapping for the image path structure
                const calculatedImgPath = place.image.startsWith('images/') ? place.image : `images/${place.image}`;
                cardsHtml += `
                    <div class="place-card">
                        <div class="place-img-container">
                            <img 
                                class="place-img" 
                                src="${calculatedImgPath}" 
                                onerror="this.onerror=null; this.src='images/default_place.jpg';" 
                                alt="${place.name}" 
                                loading="lazy"
                            >
                            <span class="place-tag">${place.category}</span>
                        </div>
                        <div class="place-details">
                            <div>
                                <div class="place-header">
                                    <h4 class="place-name">${place.name}</h4>
                                    <div class="place-rating">
                                        <i class="ri-star-fill"></i> ${place.rating || '4.5'}
                                    </div>
                                </div>
                                <p class="place-desc">${place.desc}</p>
                            </div>
                            <div class="place-meta" style="margin-top: 12px;">
                                <span><i class="ri-time-line"></i> ${place.time}</span>
                                <a href="${place.maps}" target="_blank" class="map-link-btn"><i class="ri-map-pin-line"></i> Maps</a>
                            </div>
                        </div>
                    </div>
                `;
            });
            cardsContainer.innerHTML = cardsHtml;
        }

        // Render the smart itinerary layout
        renderSmartItinerary(selectedStates);
    }

    // 4. DAYS-DEPENDENT SMART ITINERARY MOCKUP GENERATOR
    function renderSmartItinerary(selectedStates) {
        if (!itinerarySec) return;

        // Gather all gems matching selected states and sidebar types
        let pooledGems = [];
        selectedStates.forEach(stateName => {
            const stateData = hiddenGems[stateName];
            if (!stateData) return;

            let targetCategories = plannerState.selectedTypes.length > 0 
                ? plannerState.selectedTypes 
                : ["Beach", "Nature", "Adventure", "Culture", "Food"];

            targetCategories.forEach(cat => {
                if (stateData.gems[cat]) {
                    stateData.gems[cat].forEach(gem => {
                        pooledGems.push({ ...gem, state: stateName, category: cat });
                    });
                }
            });
        });

        const activeDays = plannerState.durationDays || 3; 
        let generatedItineraryArray = [];
        let activityCost = 0;
        let dayHtml = "";

        if (pooledGems.length > 0) {
            // Logics depend entirely on user duration days
            if (activeDays === 1) {
                // 1 Day Template: 3 activities (Morning, Afternoon, Night)
                const template = itineraryTemplates[1];
                let activitiesHtml = "";
                
                template.forEach((act, idx) => {
                    const gem = pooledGems[idx % pooledGems.length];
                    const activityText = act.activity.replace(/\{place\d+\}/g, gem.name);
                    
                    activitiesHtml += `
                        <div class="itinerary-item">
                            <span class="itinerary-dot"></span>
                            <div><strong>${act.time}</strong> - ${activityText} (${gem.category})</div>
                        </div>
                    `;
                    generatedItineraryArray.push(`Day 1 (${act.time}): ${activityText} (${gem.category})`);
                    activityCost += act.budget;
                });

                dayHtml = `
                    <div class="itinerary-day-card">
                        <div class="itinerary-day-title">Day 1 Schedule</div>
                        <div class="itinerary-items-list">${activitiesHtml}</div>
                    </div>
                `;
            } else if (activeDays === 2) {
                // 2 Days Template: Unique Day 1 and Day 2 structures
                const template = itineraryTemplates[2];
                
                for (let d = 1; d <= 2; d++) {
                    const dayActivities = template.filter(act => act.day === d);
                    let activitiesHtml = "";
                    
                    dayActivities.forEach((act, idx) => {
                        // Spread out gems uniquely
                        const gemIdx = ((d - 1) * dayActivities.length + idx) % pooledGems.length;
                        const gem = pooledGems[gemIdx];
                        const activityText = act.activity.replace(/\{place\d+\}/g, gem.name);
                        
                        activitiesHtml += `
                            <div class="itinerary-item">
                                <span class="itinerary-dot"></span>
                                <div><strong>${act.time}</strong> - ${activityText} (${gem.category})</div>
                            </div>
                        `;
                        generatedItineraryArray.push(`Day ${d} (${act.time}): ${activityText} (${gem.category})`);
                        activityCost += act.budget;
                    });

                    dayHtml += `
                        <div class="itinerary-day-card">
                            <div class="itinerary-day-title">Day ${d} Schedule</div>
                            <div class="itinerary-items-list">${activitiesHtml}</div>
                        </div>
                    `;
                }
            } else {
                // 3 Days or more: Loop and repeat modulo templates safely without crashes
                const templateCount = Object.keys(itineraryTemplates).length;
                
                for (let d = 1; d <= activeDays; d++) {
                    // Map days back to the 3-day template pattern
                    const templateDay = ((d - 1) % 3) + 1;
                    const dayActivities = itineraryTemplates[3].filter(act => act.day === templateDay);
                    let activitiesHtml = "";
                    
                    dayActivities.forEach((act, idx) => {
                        const gemIdx = ((d - 1) * dayActivities.length + idx) % pooledGems.length;
                        const gem = pooledGems[gemIdx];
                        const activityText = act.activity.replace(/\{place\d+\}/g, gem.name);
                        
                        activitiesHtml += `
                            <div class="itinerary-item">
                                <span class="itinerary-dot"></span>
                                <div><strong>${act.time}</strong> - ${activityText} (${gem.category})</div>
                            </div>
                        `;
                        generatedItineraryArray.push(`Day ${d} (${act.time}): ${activityText} (${gem.category})`);
                        activityCost += act.budget;
                    });

                    dayHtml += `
                        <div class="itinerary-day-card">
                            <div class="itinerary-day-title">Day ${d} Schedule</div>
                            <div class="itinerary-items-list">${activitiesHtml}</div>
                        </div>
                    `;
                }
            }
        } else {
            // Fallback content when no locations matched
            for (let d = 1; d <= activeDays; d++) {
                dayHtml += `
                    <div class="itinerary-day-card">
                        <div class="itinerary-day-title">Day ${d} Schedule</div>
                        <div class="itinerary-item">
                            <span class="itinerary-dot"></span>
                            <div>Settle in and explore surrounding locations at your own leisure.</div>
                        </div>
                    </div>
                `;
                generatedItineraryArray.push(`Day ${d}: Relax and explore surrounding areas.`);
            }
            activityCost = activeDays * 35;
        }

        // Generate budget summary
        const totalAttractionsCount = Math.min(pooledGems.length, activeDays * 3) || 4;
        const transportCost = 40 * activeDays + (totalAttractionsCount * 10);
        const hotelCost = 100 * (activeDays - 1 || 1);
        const foodCost = 50 * activeDays;
        const grandTotal = transportCost + hotelCost + foodCost + activityCost;

        const budgetObj = {
            transport: transportCost,
            hotel: hotelCost,
            food: foodCost,
            activities: activityCost,
            total: grandTotal
        };

        // Cache generated data inside State
        plannerState.generatedData = [{
            state: selectedStates.join(", "),
            itinerary: generatedItineraryArray,
            budget: budgetObj
        }];

        // Render Itinerary Panel
        itinerarySec.innerHTML = `
            <div class="itinerary-header">
                <h3><i class="ri-calendar-todo-line"></i> Generated Smart Itinerary (${activeDays} Days)</h3>
                <div class="itinerary-budget-summary">
                    Est. Budget: <span style="color: var(--gold); font-weight: 800; font-size: 1.1rem;">RM${budgetObj.total}</span>
                </div>
            </div>
            
            <div class="itinerary-days-grid">
                ${dayHtml}
            </div>

            <div class="budget-box">
                <div class="budget-title"><i class="ri-wallet-3-line"></i> Estimated Budget Breakdown</div>
                <div class="budget-grid-summary">
                    <div>Transport: <strong>RM${budgetObj.transport}</strong></div>
                    <div>Hotel: <strong>RM${budgetObj.hotel}</strong></div>
                    <div>Food: <strong>RM${budgetObj.food}</strong></div>
                    <div>Activities: <strong>RM${budgetObj.activities}</strong></div>
                </div>
            </div>
        `;
        itinerarySec.style.display = "block";
    }

    // 5. NEXT STEP TRIGGER WITH PHP BACKEND VERIFICATION LAYER
    if (nextStepBtn) {
        nextStepBtn.addEventListener("click", () => {
            if (plannerState.selectedStates.length === 0) {
                Swal.fire({
                    title: 'State Required!',
                    text: 'Please select at least one State before proceeding!',
                    icon: 'warning',
                    confirmButtonColor: '#0e3a20'
                });
                return;
            }

            if (!plannerState.startDate || !plannerState.endDate) {
                Swal.fire({
                    title: 'Dates Required!',
                    text: 'Please select both your travel start and end dates.',
                    icon: 'warning',
                    confirmButtonColor: '#0e3a20'
                });
                return;
            }

            const dataToSave = {
                state: plannerState.selectedStates.join(", "),
                startDate: plannerState.startDate,
                endDate: plannerState.endDate,
                duration: plannerState.durationDays || 3,
                categories: plannerState.selectedTypes,
                itinerary: plannerState.generatedData.flatMap(d => d.itinerary),
                budget: plannerState.generatedData.reduce((acc, curr) => {
                    return {
                        transport: acc.transport + curr.budget.transport,
                        hotel: acc.hotel + curr.budget.hotel,
                        food: acc.food + curr.budget.food,
                        activities: acc.activities + curr.budget.activities,
                        total: acc.total + curr.budget.total
                    };
                }, { transport: 0, hotel: 0, food: 0, activities: 0, total: 0 })
            };

            // AJAX Validation request
            fetch('smart-planner.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    'action_validate_dates': '1',
                    'start_date': plannerState.startDate,
                    'end_date': plannerState.endDate
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'error') {
                    Swal.fire({
                        title: 'Invalid Date Selection!',
                        text: data.message,
                        icon: 'error',
                        confirmButtonColor: '#0e3a20'
                    });
                } else {
                    // Success: Save travel parameters and redirect to booking
                    localStorage.setItem("voyago_planner_data", JSON.stringify(dataToSave));

                    if (toast) {
                        toast.classList.add("show");
                        setTimeout(() => {
                            toast.classList.remove("show");
                            let firstState = (plannerState.selectedStates && plannerState.selectedStates.length > 0) ? plannerState.selectedStates[0] : "";
                            let destinationUrl = "booking.php?state=" + encodeURIComponent(firstState);
                            if (plannerState.startDate) destinationUrl += "&check_in=" + encodeURIComponent(plannerState.startDate);
                            if (plannerState.endDate) destinationUrl += "&check_out=" + encodeURIComponent(plannerState.endDate);
                            window.location.href = destinationUrl;
                        }, 1200);
                    } else {
                        let firstState = (plannerState.selectedStates && plannerState.selectedStates.length > 0) ? plannerState.selectedStates[0] : "";
                        window.location.href = "booking.php?state=" + encodeURIComponent(firstState);
                    }
                }
            })
            .catch(error => {
                console.error("Verification connection error:", error);
                
                // Fallback check on connection error
                if (plannerState.startDate < todayStr || plannerState.endDate < todayStr) {
                    Swal.fire({
                        title: 'Invalid Date Selection!',
                        text: 'Travel dates cannot be in the past.',
                        icon: 'error',
                        confirmButtonColor: '#0e3a20'
                    });
                } else {
                    localStorage.setItem("voyago_planner_data", JSON.stringify(dataToSave));
                    let firstState = (plannerState.selectedStates && plannerState.selectedStates.length > 0) ? plannerState.selectedStates[0] : "";
                    let destinationUrl = "booking.php?state=" + encodeURIComponent(firstState);
                    if (plannerState.startDate) destinationUrl += "&check_in=" + encodeURIComponent(plannerState.startDate);
                    if (plannerState.endDate) destinationUrl += "&check_out=" + encodeURIComponent(plannerState.endDate);
                    window.location.href = destinationUrl;
                }
            });
        });
    }
});