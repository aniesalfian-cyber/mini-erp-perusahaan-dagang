import './bootstrap';
import Alpine from 'alpinejs';
import Swal from 'sweetalert2';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import L from 'leaflet';

window.Alpine = Alpine;
window.Swal = Swal;
window.Chart = Chart;
window.ChartDataLabels = ChartDataLabels;
window.L = L;

// Global PWA Install State
let deferredPwaPrompt = null;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPwaPrompt = e;
    window.dispatchEvent(new CustomEvent('pwa-prompt-ready'));
});

window.addEventListener('appinstalled', () => {
    deferredPwaPrompt = null;
    window.dispatchEvent(new CustomEvent('pwa-installed'));
});

// Alpine Component for PWA Install Button
Alpine.data('pwaInstaller', () => ({
    canInstall: false,
    isInstalled: false,
    isIOS: false,
    showIOSGuide: false,
    dismissed: false,

    init() {
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
        if (isStandalone) {
            this.isInstalled = true;
            this.canInstall = false;
            return;
        }

        const userAgent = window.navigator.userAgent.toLowerCase();
        this.isIOS = /iphone|ipad|ipod/.test(userAgent) && !window.MSStream;

        if (deferredPwaPrompt) {
            this.canInstall = true;
        } else if (this.isIOS && !isStandalone) {
            this.canInstall = true;
        }

        window.addEventListener('pwa-prompt-ready', () => {
            this.canInstall = true;
        });

        window.addEventListener('pwa-installed', () => {
            this.canInstall = false;
            this.isInstalled = true;
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'success',
                    title: 'Aplikasi Berhasil Dipasang!',
                    text: 'ERP System Prima Jaya siap diakses langsung dari layar utama perangkat Anda.',
                    confirmButtonColor: '#1E3A2B',
                });
            }
        });
    },

    async installApp() {
        if (this.isIOS && !deferredPwaPrompt) {
            this.showIOSGuide = true;
            return;
        }

        if (!deferredPwaPrompt) {
            if (window.Swal) {
                window.Swal.fire({
                    icon: 'info',
                    title: 'Pemasangan Aplikasi',
                    html: 'Buka menu browser Anda (ikon titik tiga di pojok kanan atas) lalu pilih <b>"Install app"</b> atau <b>"Tambahkan ke Layar Utama"</b>.',
                    confirmButtonColor: '#1E3A2B',
                });
            }
            return;
        }

        deferredPwaPrompt.prompt();
        const { outcome } = await deferredPwaPrompt.userChoice;
        if (outcome === 'accepted') {
            this.canInstall = false;
            deferredPwaPrompt = null;
        }
    },

    dismissBanner() {
        this.dismissed = true;
    }
}));

Alpine.start();

// PWA Service Worker Registration
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js')
            .then((registration) => {
                console.log('ERP Prima Jaya ServiceWorker registered with scope:', registration.scope);
            })
            .catch((error) => {
                console.warn('ServiceWorker registration failed:', error);
            });
    });
}
