window.addEventListener('load', function () {
    console.log('Page fully loaded');
    const loadingOverlay = document.querySelector('.loading-overlay');
    if (loadingOverlay) {
        console.log('Overlay found, starting fade-out');
        loadingOverlay.style.transition = 'opacity 0.5s ease';
        loadingOverlay.style.opacity = '0';
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, 500);
    } else {
        console.error('Overlay not found!');
    }
});
