/**
 * Selftrace — Main JS
 */

// Auto-dismiss flash messages
document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('[data-auto-dismiss]');
    flashes.forEach(flash => {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            flash.style.opacity = '0';
            flash.style.transform = 'translateX(10px)';
            setTimeout(() => flash.remove(), 500);
        }, 3500);
    });

    // Page fade-in animation
    const main = document.querySelector('main > div');
    if (main) {
        main.classList.add('page-enter');
    }

    // Auto-resize textareas
    document.querySelectorAll('textarea').forEach(textarea => {
        const resize = () => {
            textarea.style.height = 'auto';
            textarea.style.height = textarea.scrollHeight + 'px';
        };
        textarea.addEventListener('input', resize);
        // Initial sizing
        if (textarea.value) resize();
    });
});
