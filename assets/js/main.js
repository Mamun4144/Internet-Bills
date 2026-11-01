// Global functions that are used across multiple pages

// screen-এ success/error message দেখানো, 5 সেকেন্ড পরে আপনাআপনি মুছে যায়
function showMessage(message, type) {
    // Create message container if it doesn't exist
    let container = document.getElementById('messageContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'messageContainer';
        document.querySelector('.content').prepend(container);
    }

    container.innerHTML = `
        <div class="message ${type}">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        </div>
    `;

    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// api দিয়ে invoice data আনা (incomplete)
function editInvoice(id) {
    fetch(`api/ajax.php?action=get_invoice&id=${id}`)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                // Open edit modal with invoice data
                // Implement edit modal functionality
            }
        })
        .catch(err => console.error('Error:', err));
}

// বিল delete করার confirm dialog, AJAX delete
function deleteInvoice(id) {
    if(confirm('Are you sure you want to delete this bill?')) {
        fetch(`api/ajax.php?action=delete_invoice&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if(data.success) {
                    // Reload current page
                    location.reload();
                }
            })
            .catch(err => {
                showMessage('Error deleting bill', 'error');
                console.error('Error:', err);
            });
    }
}

// একটি বিল complete করা
function markSingleComplete(id) {
    if(confirm('Mark this bill as complete?')) {
        fetch(`api/ajax.php?action=mark_complete&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if(data.success) {
                    location.reload();
                }
            })
            .catch(err => {
                showMessage('Error marking as complete', 'error');
                console.error('Error:', err);
            });
    }
}

// complete বিল আবার pending-এ নেওয়া
function markAsIncomplete(id) {
    if(confirm('Mark this bill as incomplete? It will be moved back to Pending Bills.')) {
        fetch(`api/ajax.php?action=mark_incomplete&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if(data.success) {
                    location.reload();
                }
            })
            .catch(err => {
                showMessage('Error marking as incomplete', 'error');
                console.error('Error:', err);
            });
    }
}

// hold modal খোলা (incomplete)
function openHoldModal(id) {
    // Implement hold modal functionality
}

// bill hold থেকে মুক্ত করা
function unholdInvoice(id) {
    if(confirm('Release this bill from hold?')) {
        fetch(`api/ajax.php?action=unhold_invoice&id=${id}`)
            .then(res => res.json())
            .then(data => {
                showMessage(data.message, data.success ? 'success' : 'error');
                if(data.success) {
                    location.reload();
                }
            })
            .catch(err => {
                showMessage('Error releasing bill', 'error');
                console.error('Error:', err);
            });
    }
}
