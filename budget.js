// js/budget.js

// Fungsi membuka tingkap terapung modal
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) {
        modal.style.display = 'flex';
        modal.querySelector('.modal-glass-content').style.transform = 'scale(1)';
    }
}

// Fungsi menutup tingkap terapung modal
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if(modal) {
        modal.style.display = 'none';
    }
}

// 4. FEATURE INTEGRASI: SETTLEMENT KLIK UNTUK SELESAI (SPLITWISE STYLE)
function triggerSettlement(name, amount) {
    Swal.fire({
        title: 'Confirm Settlement?',
        text: `Do you want to confirm that ${name} has repaid you RM${amount}.00 in cash or transfer?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2ecc71',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, Mark as Settled!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire(
                'Settled Successfully!',
                'The shared debt with ${name} has now been fully settled!',
                'success'
            ).then(() => {
                // Di sini anda boleh buat ajax call jika mahu kemaskini DB tripmates, 
                // buat masa sekarang ia melakukan simulasi frontend yang sangat responsif.
                location.reload();
            });
        }
    });
}

// Membaca query string URL untuk memaparkan toast notifikasi kejayaan simpanan data
document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    if(params.get('success') === 'expense') {
        Swal.fire('Expense Added!', 'The new expense was recorded and added to your budget.', 'success');
    } else if(params.get('success') === 'doc') {
        Swal.fire('Document Uploaded!', 'Your ticket or booking document was saved successfully.', 'success');
    } else if(params.get('success') === 'memory') {
        Swal.fire('Memory Logged!', 'Your travel memory journal entry was saved successfully!', 'success');
    }
});