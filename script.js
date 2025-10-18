// js/script.js
document.addEventListener('DOMContentLoaded', function() {
    initializeApp();
});

function initializeApp() {
    // Initialize all components
    initBalanceUpdates();
    initTransferForm();
    initModalHandlers();
    initFormValidations();
    initAutoLogout();
    initNotifications();
}

// Balance and Account Management
function initBalanceUpdates() {
    const accountSelects = document.querySelectorAll('select[id*="account"]');
    accountSelects.forEach(select => {
        select.addEventListener('change', function() {
            updateBalanceDisplay(this);
        });
    });
}

function updateBalanceDisplay(selectElement) {
    const balanceDisplay = document.getElementById('balance-display');
    if (!balanceDisplay) return;

    const selectedOption = selectElement.options[selectElement.selectedIndex];
    if (selectedOption.value && selectedOption.dataset.balance) {
        const balance = parseFloat(selectedOption.dataset.balance);
        balanceDisplay.innerHTML = `
            <div class="balance-info">
                <i class="fas fa-wallet"></i>
                Available Balance: ₹${balance.toLocaleString('en-IN', {minimumFractionDigits: 2})}
            </div>
        `;
        balanceDisplay.classList.add('visible');
    } else {
        balanceDisplay.innerHTML = '';
        balanceDisplay.classList.remove('visible');
    }
}

// Transfer Form Handling
function initTransferForm() {
    const transferForm = document.querySelector('.transfer-form');
    if (transferForm) {
        transferForm.addEventListener('submit', handleTransferSubmit);
        
        // Real-time amount validation
        const amountInput = document.getElementById('amount');
        if (amountInput) {
            amountInput.addEventListener('input', validateAmount);
        }
    }
}

function validateAmount() {
    const amountInput = document.getElementById('amount');
    const fromAccount = document.getElementById('from_account');
    const errorDiv = document.getElementById('amount-error') || createErrorDiv('amount-error');
    
    if (!fromAccount.value) {
        showError(errorDiv, 'Please select an account first');
        return false;
    }

    const amount = parseFloat(amountInput.value);
    const selectedOption = fromAccount.options[fromAccount.selectedIndex];
    const balance = parseFloat(selectedOption.dataset.balance);

    if (amount <= 0) {
        showError(errorDiv, 'Amount must be greater than 0');
        return false;
    }

    if (amount > balance) {
        showError(errorDiv, 'Insufficient balance');
        return false;
    }

    hideError(errorDiv);
    return true;
}

function createErrorDiv(id) {
    const errorDiv = document.createElement('div');
    errorDiv.id = id;
    errorDiv.className = 'error-message';
    const amountInput = document.getElementById('amount');
    amountInput.parentNode.appendChild(errorDiv);
    return errorDiv;
}

function showError(errorDiv, message) {
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
    errorDiv.previousElementSibling.style.borderColor = '#e74c3c';
}

function hideError(errorDiv) {
    errorDiv.style.display = 'none';
    errorDiv.previousElementSibling.style.borderColor = '';
}

function handleTransferSubmit(e) {
    e.preventDefault();
    
    if (!validateTransferForm()) {
        return;
    }

    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitBtn.disabled = true;

    // Simulate API call delay
    setTimeout(() => {
        e.target.submit();
    }, 2000);
}

function validateTransferForm() {
    const requiredFields = ['from_account', 'to_account', 'to_name', 'to_bank', 'amount'];
    let isValid = true;

    requiredFields.forEach(field => {
        const element = document.getElementById(field);
        if (element && !element.value) {
            showFieldError(element, 'This field is required');
            isValid = false;
        } else if (element) {
            clearFieldError(element);
        }
    });

    if (!validateAmount()) {
        isValid = false;
    }

    return isValid;
}

function showFieldError(element, message) {
    element.style.borderColor = '#e74c3c';
    let errorDiv = element.parentNode.querySelector('.field-error');
    if (!errorDiv) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        element.parentNode.appendChild(errorDiv);
    }
    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

function clearFieldError(element) {
    element.style.borderColor = '';
    const errorDiv = element.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.style.display = 'none';
    }
}

// Tab Management for Transfer Page
function showTab(tabName) {
    // Update tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    event.target.classList.add('active');

    // Update tab content
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.remove('active');
    });
    document.getElementById(tabName + '-tab').classList.add('active');

    // Clear manual entry fields when switching to contact tab
    if (tabName === 'contact') {
        clearManualFields();
    }
}

function clearManualFields() {
    const manualFields = ['to_name', 'to_account', 'to_bank'];
    manualFields.forEach(field => {
        const element = document.getElementById(field);
        if (element) element.value = '';
    });
}

function fillContactDetails() {
    const contactSelect = document.getElementById('contact_select');
    const selectedOption = contactSelect.options[contactSelect.selectedIndex];
    
    if (selectedOption.value) {
        document.getElementById('to_name').value = selectedOption.dataset.name || '';
        document.getElementById('to_account').value = selectedOption.dataset.account || '';
        document.getElementById('to_bank').value = selectedOption.dataset.bank || '';
        
        // Clear any errors
        ['to_name', 'to_account', 'to_bank'].forEach(field => {
            const element = document.getElementById(field);
            if (element) clearFieldError(element);
        });
    }
}

// Modal Management
function initModalHandlers() {
    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // ESC key to close modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal[style*="display: block"]');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden'; // Prevent scrolling
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = ''; // Restore scrolling
    }
}

// Form Validations
function initFormValidations() {
    // Account number validation
    const accountInputs = document.querySelectorAll('input[name*="account"]');
    accountInputs.forEach(input => {
        input.addEventListener('blur', validateAccountNumber);
    });

    // Phone number validation
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('blur', validatePhoneNumber);
    });

    // Amount formatting
    const amountInputs = document.querySelectorAll('input[type="number"]');
    amountInputs.forEach(input => {
        input.addEventListener('blur', formatAmount);
    });
}

function validateAccountNumber(e) {
    const input = e.target;
    const value = input.value.trim();
    
    if (value && !/^\d{9,18}$/.test(value)) {
        showFieldError(input, 'Account number must be 9-18 digits');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function validatePhoneNumber(e) {
    const input = e.target;
    const value = input.value.trim();
    
    if (value && !/^\d{10}$/.test(value)) {
        showFieldError(input, 'Phone number must be 10 digits');
        return false;
    }
    
    clearFieldError(input);
    return true;
}

function formatAmount(e) {
    const input = e.target;
    let value = parseFloat(input.value);
    
    if (!isNaN(value) && value > 0) {
        input.value = value.toFixed(2);
    }
}

// Auto Logout for Security
function initAutoLogout() {
    let timeout;
    
    function resetTimer() {
        clearTimeout(timeout);
        timeout = setTimeout(showLogoutWarning, 15 * 60 * 1000); // 15 minutes
    }
    
    function showLogoutWarning() {
        if (confirm('Your session will expire due to inactivity. Continue session?')) {
            resetTimer();
        } else {
            window.location.href = 'logout.php';
        }
    }
    
    // Reset timer on user activity
    ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart'].forEach(event => {
        document.addEventListener(event, resetTimer);
    });
    
    resetTimer();
}

// Notification System
function initNotifications() {
    // Check for success/error messages in URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type');
    
    if (message) {
        showNotification(message, type || 'info');
    }
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
            <button class="notification-close" onclick="this.parentElement.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function getNotificationIcon(type) {
    const icons = {
        'success': 'check-circle',
        'error': 'exclamation-circle',
        'warning': 'exclamation-triangle',
        'info': 'info-circle'
    };
    return icons[type] || 'info-circle';
}

// Search and Filter Functions
function searchContacts(query) {
    const contactCards = document.querySelectorAll('.contact-card');
    contactCards.forEach(card => {
        const name = card.querySelector('h3').textContent.toLowerCase();
        const account = card.querySelector('p:nth-child(2)').textContent.toLowerCase();
        const bank = card.querySelector('p:nth-child(3)').textContent.toLowerCase();
        
        if (name.includes(query) || account.includes(query) || bank.includes(query)) {
            card.style.display = 'flex';
        } else {
            card.style.display = 'none';
        }
    });
}

function filterTransactions(filter) {
    const transactionItems = document.querySelectorAll('.transaction-item, .table-row');
    transactionItems.forEach(item => {
        if (filter === 'all') {
            item.style.display = item.classList.contains('table-header') ? 'grid' : 'flex';
        } else if (filter === 'debit') {
            const amountElement = item.querySelector('.debit');
            item.style.display = amountElement ? 'flex' : 'none';
        } else if (filter === 'credit') {
            const amountElement = item.querySelector('.credit');
            item.style.display = amountElement ? 'flex' : 'none';
        }
    });
}

// Utility Functions
function formatCurrency(amount) {
    return '₹' + parseFloat(amount).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

function formatDate(dateString) {
    const options = { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    return new Date(dateString).toLocaleDateString('en-IN', options);
}

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Export functions for global access
window.openModal = openModal;
window.closeModal = closeModal;
window.showTab = showTab;
window.fillContactDetails = fillContactDetails;
window.searchContacts = debounce(searchContacts, 300);
window.filterTransactions = filterTransactions;
window.showNotification = showNotification;