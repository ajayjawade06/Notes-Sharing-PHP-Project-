</div> <!-- End of container -->

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="container mx-auto px-4 py-6">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <p>&copy; <?php echo date('Y'); ?> Notes Sharing Platform</p>
                </div>
                <div>
                    <p>Developed with <i class="fas fa-heart text-red-500"></i> for Knowledge Sharing</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Mobile Menu Toggle Script -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // Toast notifications
            const toastContainer = document.querySelector('.toast-container');
            if (toastContainer) {
                setTimeout(function() {
                    const toasts = toastContainer.querySelectorAll('.toast');
                    toasts.forEach(toast => {
                        toast.classList.add('opacity-0');
                        setTimeout(() => {
                            toast.remove();
                        }, 300);
                    });
                }, 5000);
            }
        });
    </script>
    
    <!-- Custom JS -->
    <script src="<?php echo $base_url; ?>/js/script.js"></script>
</body>
</html>