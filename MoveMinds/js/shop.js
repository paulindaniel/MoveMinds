// script.js
document.addEventListener('DOMContentLoaded', () => {
    // Basic interactivity: highlight active nav item
    const navItems = document.querySelectorAll('.footer-nav .nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function(event) {
            // Prevent default anchor behavior for this demo
            event.preventDefault();

            // Remove 'active' class from all items
            navItems.forEach(i => i.classList.remove('active'));

            // Add 'active' class to the clicked item
            this.classList.add('active');

            // If it's the Loja icon, ensure its text also gets "active" color if needed
            // For this design, only the icon itself changes or the whole item.
            // The provided CSS already handles .active color for the whole item.

            console.log(`Mapsd to: ${this.querySelector('span').textContent}`);
            // In a real app, you would load content or navigate here
        });
    });

    // Make buttons log to console to show they are "functional" in a basic sense
    const buttons = document.querySelectorAll('.btn-subscribe, .btn-price');
    buttons.forEach(button => {
        button.addEventListener('click', () => {
            console.log(`Button clicked: ${button.textContent.trim()}`);
            // Add further actions here, e.g., alert('Button clicked!');
        });
    });

});