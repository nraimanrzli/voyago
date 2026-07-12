/**
 * admin_commission.js
 * Client-side table record matching and action triggers for administration workflows.
 */

// Filter Functionality
function filterCommissionTable() {
    const input = document.getElementById("searchUser");
    const filter = input.value.toLowerCase();
    const table = document.getElementById("commissionTable");
    const tr = table.getElementsByTagName("tr");

    for (let i = 1; i < tr.length; i++) {
        let matchFound = false;
        const userCell = tr[i].getElementsByTagName("td")[1];
        const stateCell = tr[i].getElementsByTagName("td")[2];
        const statusCell = tr[i].getElementsByTagName("td")[7];

        if (userCell || stateCell || statusCell) {
            const userText = userCell.textContent || userCell.innerText;
            const stateText = stateCell.textContent || stateCell.innerText;
            const statusText = statusCell.textContent || statusCell.innerText;
            
            if (
                userText.toLowerCase().indexOf(filter) > -1 || 
                stateText.toLowerCase().indexOf(filter) > -1 ||
                statusText.toLowerCase().indexOf(filter) > -1
            ) {
                matchFound = true;
            }
        }

        if (matchFound) {
            tr[i].style.display = "";
        } else {
            if (tr[i].cells.length > 1) {
                tr[i].style.display = "none";
            }
        }
    }
}

// Action Button Hook: Generate PDF Receipt
function generatePdfReceipt(bookingId) {
    alert("Generating PDF Receipt for Booking #" + bookingId + "...\n(Your friend can link this to fpdf or dompdf later!)");
    // Frontend route ready:
    // window.open('generate_pdf.php?id=' + bookingId, '_blank');
}

// Action Button Hook: Ready Payment (ToyyibPay Trigger)
function initiateToyyibpay(bookingId, hostAmount) {
    alert("Connecting to ToyyibPay securely...\nProcessing Host Payout Distribution of RM " + hostAmount.toFixed(2) + " for Booking #" + bookingId);
    // Frontend route ready for backend connection:
    // window.location.href = 'toyyibpay_payout.php?id=' + bookingId + '&amount=' + hostAmount;
}