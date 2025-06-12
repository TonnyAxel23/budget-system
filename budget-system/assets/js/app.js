document.addEventListener('DOMContentLoaded', function() {
    // Initialize datepickers
    const dateInputs = document.querySelectorAll('.datepicker');
    if (dateInputs) {
        dateInputs.forEach(input => {
            input.type = 'date';
            if (!input.value) {
                const today = new Date().toISOString().split('T')[0];
                input.value = today;
            }
        });
    }

    // Handle form submissions with confirmation
    const deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });
    });

    // Toggle mobile menu
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');
    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', function() {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Initialize tooltips
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(tooltip => {
        tooltip.addEventListener('mouseenter', showTooltip);
        tooltip.addEventListener('mouseleave', hideTooltip);
    });

    function showTooltip(e) {
        const tooltipText = this.getAttribute('data-tooltip');
        const tooltip = document.createElement('div');
        tooltip.className = 'absolute z-50 bg-gray-800 text-white text-xs rounded py-1 px-2';
        tooltip.textContent = tooltipText;
        tooltip.style.top = `${e.clientY + 10}px`;
        tooltip.style.left = `${e.clientX + 10}px`;
        tooltip.id = 'current-tooltip';
        document.body.appendChild(tooltip);
    }

    function hideTooltip() {
        const tooltip = document.getElementById('current-tooltip');
        if (tooltip) {
            tooltip.remove();
        }
    }
});