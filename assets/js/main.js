/**
 * PKBM Mandiri - Main JavaScript
 */

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('./sw.js').then((reg) => {
            console.log('PKBM Mandiri PWA Service Worker registered:', reg.scope);
        }).catch((err) => {
            console.log('PWA Service Worker registration failed:', err);
        });
    });
}

// PWA Install Prompt handling
let deferredPrompt;
window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    const pwaBtn = document.getElementById('pwa-install-btn');
    if (pwaBtn) {
        pwaBtn.classList.remove('hidden');
        pwaBtn.addEventListener('click', () => {
            pwaBtn.classList.add('hidden');
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then((choiceResult) => {
                deferredPrompt = null;
            });
        });
    }
});

// Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // Auto-dismiss Flash Alerts after 5 seconds
    const flashAlert = document.getElementById('flash-alert');
    if (flashAlert) {
        setTimeout(() => {
            flashAlert.style.transition = 'opacity 0.5s ease';
            flashAlert.style.opacity = '0';
            setTimeout(() => flashAlert.remove(), 500);
        }, 5000);
    }
});

