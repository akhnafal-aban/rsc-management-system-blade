import './bootstrap';
import { Chart, registerables } from 'chart.js';
import './notifications.js';

Chart.register(...registerables);
window.Chart = Chart;

// Sidebar functionality
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.querySelector('.sidebar');
    const toggleBtn = document.querySelector('#btn');
    const mainContent = document.querySelector('.main-content');
    
    if (sidebar && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            sidebar.classList.toggle('open');
            menuBtnChange();
            adjustMainContent();
        });
        
        function menuBtnChange() {
            const icon = toggleBtn.querySelector('svg');
            if (sidebar.classList.contains('open')) {
                // Change icon to close when sidebar is open
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />';
            } else {
                // Change icon to menu when sidebar is closed
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />';
            }
        }
        
        function adjustMainContent() {
            if (mainContent) {
                if (sidebar.classList.contains('open')) {
                    mainContent.style.marginLeft = '250px';
                } else {
                    mainContent.style.marginLeft = '78px';
                }
            }
        }

        
        // Initialize button state and main content
        menuBtnChange();
        adjustMainContent();
    }
});
