                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white py-6 mt-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="border-t border-gray-200 pt-6">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-gray-600 text-sm mb-4 md:mb-0">
                        &copy; <?php echo date('Y'); ?> Booking System. All rights reserved.
                    </div>
                    <div class="flex space-x-6">
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-twitter"></i>
                        </a>
                        <a href="#" class="text-gray-500 hover:text-gray-700">
                            <i class="fab fa-instagram"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');
        
        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
        
        // User menu toggle
        const userMenuButton = document.getElementById('user-menu-button');
        const userMenuDropdown = document.getElementById('user-menu-dropdown');
        
        if (userMenuButton && userMenuDropdown) {
            userMenuButton.addEventListener('click', () => {
                userMenuDropdown.classList.toggle('hidden');
            });
            
            // ปิดเมนูเมื่อคลิกที่ส่วนอื่นของเพจ
            document.addEventListener('click', (event) => {
                if (!userMenuButton.contains(event.target) && !userMenuDropdown.contains(event.target)) {
                    userMenuDropdown.classList.add('hidden');
                }
            });
        }
        
        // Initialize datetime pickers
        document.addEventListener('DOMContentLoaded', function() {
            const dateInputs = document.querySelectorAll('.date-picker');
            if (dateInputs.length > 0) {
                dateInputs.forEach(function(input) {
                    flatpickr(input, {
                        dateFormat: "Y-m-d",
                    });
                });
            }
            
            const datetimeInputs = document.querySelectorAll('.datetime-picker');
            if (datetimeInputs.length > 0) {
                datetimeInputs.forEach(function(input) {
                    flatpickr(input, {
                        enableTime: true,
                        dateFormat: "Y-m-d H:i",
                        time_24hr: true
                    });
                });
            }
        });
    </script>
</body>
</html> 