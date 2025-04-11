document.addEventListener('DOMContentLoaded', function() {
    // Mobile Menu Toggle
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            if (mobileMenu.classList.contains('hidden')) {
                mobileMenu.classList.remove('hidden');
                mobileMenu.classList.add('fade-in');
                mobileMenuButton.innerHTML = '<i class="fas fa-times text-xl"></i>';
            } else {
                mobileMenu.classList.add('hidden');
                mobileMenu.classList.remove('fade-in');
                mobileMenuButton.innerHTML = '<i class="fas fa-bars text-xl"></i>';
            }
        });
    }
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Back to Top Button
    const backToTopButton = document.getElementById('backToTop');
    
    if (backToTopButton) {
        // Show/hide back to top button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
        
        // Smooth scroll to top when button is clicked
        backToTopButton.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
    
    // Toast Notifications
    const toastContainer = document.querySelector('.toast-container');
    
    if (toastContainer) {
        // Function to show toast notification
        window.showToast = function(message, type = 'success') {
            const toastId = 'toast-' + Date.now();
            const toastHTML = `
                <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
                    <div class="toast-header ${type === 'success' ? 'bg-success' : 'bg-danger'} text-white">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} me-2"></i>
                        <strong class="me-auto">${type === 'success' ? 'Success' : 'Error'}</strong>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                    <div class="toast-body">
                        ${message}
                    </div>
                </div>
            `;
            
            toastContainer.insertAdjacentHTML('beforeend', toastHTML);
            
            // Auto-dismiss toast after 5 seconds
            setTimeout(function() {
                const toast = document.getElementById(toastId);
                if (toast) {
                    toast.classList.remove('show');
                    setTimeout(function() {
                        toast.remove();
                    }, 500);
                }
            }, 5000);
            
            // Add click event to close button
            const closeButton = document.querySelector(`#${toastId} .btn-close`);
            if (closeButton) {
                closeButton.addEventListener('click', function() {
                    const toast = document.getElementById(toastId);
                    if (toast) {
                        toast.classList.remove('show');
                        setTimeout(function() {
                            toast.remove();
                        }, 500);
                    }
                });
            }
        };
        
        // Check for success/error messages in URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            showToast(decodeURIComponent(urlParams.get('success')), 'success');
        }
        if (urlParams.has('error')) {
            showToast(decodeURIComponent(urlParams.get('error')), 'error');
        }
        
        // Convert existing alerts to toasts
        const successAlerts = document.querySelectorAll('.alert-success');
        const errorAlerts = document.querySelectorAll('.alert-danger');
        
        successAlerts.forEach(alert => {
            showToast(alert.textContent.trim(), 'success');
            alert.style.display = 'none';
        });
        
        errorAlerts.forEach(alert => {
            showToast(alert.textContent.trim(), 'error');
            alert.style.display = 'none';
        });
    }
    
    // File upload preview
    const fileInput = document.querySelector('.file-input');
    const fileDropArea = document.querySelector('.file-drop-area');
    const fileMsg = document.querySelector('.file-msg');
    
    if (fileInput && fileDropArea && fileMsg) {
        // Highlight drop area when file is dragged over it
        ['dragenter', 'dragover'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, highlight, false);
        });
        
        ['dragleave', 'drop'].forEach(eventName => {
            fileDropArea.addEventListener(eventName, unhighlight, false);
        });
        
        // Handle file selection
        fileInput.addEventListener('change', function() {
            if (fileInput.files && fileInput.files[0]) {
                fileMsg.textContent = fileInput.files[0].name;
            }
        });
        
        function highlight() {
            fileDropArea.classList.add('is-active');
        }
        
        function unhighlight() {
            fileDropArea.classList.remove('is-active');
        }
    }
    
    // Password visibility toggle
    const togglePassword = document.querySelector('.toggle-password');
    const passwordField = document.querySelector('.password-field');
    
    if (togglePassword && passwordField) {
        togglePassword.addEventListener('click', function() {
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.querySelector('i').classList.toggle('fa-eye');
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });
    }
    
    // Confirm delete
    const deleteButtons = document.querySelectorAll('.delete-btn');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Search functionality
    const searchInput = document.getElementById('searchInput');
    const noteCards = document.querySelectorAll('.note-card');
    
    if (searchInput && noteCards.length > 0) {
        searchInput.addEventListener('keyup', function() {
            const searchTerm = this.value.toLowerCase();
            
            noteCards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                const description = card.querySelector('p').textContent.toLowerCase();
                
                if (title.includes(searchTerm) || description.includes(searchTerm)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Category filter
    const categoryFilters = document.querySelectorAll('.category-filter');
    
    if (categoryFilters.length > 0 && noteCards.length > 0) {
        // Define category to extension mappings
        const categoryExtensions = {
            'all': ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar'],
            'pdf': ['pdf'],
            'doc': ['doc', 'docx'],
            'ppt': ['ppt', 'pptx'],
            'txt': ['txt']
        };
        
        categoryFilters.forEach(filter => {
            filter.addEventListener('click', function(e) {
                e.preventDefault();
                
                const category = this.getAttribute('data-category');
                
                // Update styling for all filters
                categoryFilters.forEach(f => {
                    // Remove active styling
                    f.classList.remove('bg-blue-600');
                    f.classList.remove('text-white');
                    // Add inactive styling
                    f.classList.add('bg-gray-100');
                    f.classList.add('text-gray-700');
                });
                
                // Add active styling to clicked filter
                this.classList.remove('bg-gray-100');
                this.classList.remove('text-gray-700');
                this.classList.add('bg-blue-600');
                this.classList.add('text-white');
                
                if (category === 'all') {
                    noteCards.forEach(card => {
                        card.style.display = '';
                    });
                } else {
                    const validExtensions = categoryExtensions[category] || [];
                    
                    noteCards.forEach(card => {
                        const cardCategory = card.getAttribute('data-category');
                        if (validExtensions.includes(cardCategory)) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                }
            });
        });
    }
});