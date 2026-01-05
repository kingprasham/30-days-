/**
 * Main JavaScript
 * Customer Tracking & Billing Management System
 */

// Global namespace
const App = {
    baseUrl: '',
    csrfToken: '',

    init: function() {
        this.baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

        this.initTooltips();
        this.initConfirmButtons();
        this.initFormValidation();
    },

    // Initialize Bootstrap tooltips
    initTooltips: function() {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
    },

    // Initialize confirm buttons
    initConfirmButtons: function() {
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', function(e) {
                if (!confirm(this.dataset.confirm || 'Are you sure?')) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    },

    // Initialize form validation
    initFormValidation: function() {
        document.querySelectorAll('.needs-validation').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!form.checkValidity()) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                form.classList.add('was-validated');
            });
        });
    },

    // Show loading spinner
    showLoading: function() {
        let overlay = document.querySelector('.spinner-overlay');
        if (!overlay) {
            overlay = document.createElement('div');
            overlay.className = 'spinner-overlay';
            overlay.innerHTML = `
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            `;
            document.body.appendChild(overlay);
        }
        overlay.style.display = 'flex';
    },

    // Hide loading spinner
    hideLoading: function() {
        const overlay = document.querySelector('.spinner-overlay');
        if (overlay) {
            overlay.style.display = 'none';
        }
    },

    // Show toast notification
    showToast: function(message, type = 'success') {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }

        const toastId = 'toast-' + Date.now();
        const toast = document.createElement('div');
        toast.id = toastId;
        toast.className = `toast align-items-center text-white bg-${type} border-0`;
        toast.setAttribute('role', 'alert');
        toast.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        `;

        container.appendChild(toast);
        const bsToast = new bootstrap.Toast(toast, { autohide: true, delay: 3000 });
        bsToast.show();

        toast.addEventListener('hidden.bs.toast', () => toast.remove());
    },

    // AJAX request helper
    ajax: function(options) {
        const defaults = {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfToken
            }
        };

        options = { ...defaults, ...options };

        if (options.data && options.method !== 'GET') {
            if (!(options.data instanceof FormData)) {
                options.headers['Content-Type'] = 'application/json';
                options.body = JSON.stringify(options.data);
            } else {
                options.body = options.data;
            }
        }

        return fetch(options.url, options)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            });
    },

    // Format currency (INR)
    formatCurrency: function(amount) {
        return '₹ ' + parseFloat(amount).toLocaleString('en-IN', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    },

    // Format number
    formatNumber: function(num) {
        return parseFloat(num).toLocaleString('en-IN');
    },

    // Format date (dd/mm/yyyy)
    formatDate: function(dateStr) {
        if (!dateStr) return '-';
        const date = new Date(dateStr);
        if (isNaN(date)) return '-';

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    },

    // Parse date (dd/mm/yyyy to Date object)
    parseDate: function(dateStr) {
        if (!dateStr) return null;
        const parts = dateStr.split('/');
        if (parts.length !== 3) return null;

        const day = parseInt(parts[0], 10);
        const month = parseInt(parts[1], 10) - 1;
        const year = parseInt(parts[2], 10);

        return new Date(year, month, day);
    },

    // Debounce function
    debounce: function(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Escape HTML
    escapeHtml: function(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
};

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', () => App.init());

// Export for use in other scripts
window.App = App;
