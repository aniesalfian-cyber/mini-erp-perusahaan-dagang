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
