import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Alpine = Alpine;
window.Chart = Chart;

// Daftarkan plugin datalabels secara global, supaya SEMUA chart yang dibuat
// lewat `new Chart(...)` otomatis bisa menampilkan label (dipakai untuk
// menampilkan persentase di setiap grafik pie/bar).
Chart.register(ChartDataLabels);

/**
 * Konfigurasi datalabels siap pakai untuk menampilkan persentase dari total
 * nilai dalam 1 dataset. Tinggal di-spread ke plugins.datalabels tiap chart:
 *   plugins: { datalabels: window.percentageDataLabels() }
 */
window.percentageDataLabels = function (options = {}) {
    return {
        color: '#fff',
        font: { weight: 'bold', size: 11 },
        textStrokeColor: 'rgba(0,0,0,0.45)',
        textStrokeWidth: 3,
        formatter: (value, ctx) => {
            const data = ctx.chart.data.datasets[ctx.datasetIndex].data;
            const total = data.reduce((a, b) => a + (Number(b) || 0), 0);
            if (!total || !value) return '';
            return (value / total * 100).toFixed(1).replace('.0', '') + '%';
        },
        ...options,
    };
};

/**
 * Komponen Alpine untuk dropdown Fakultas -> Prodi yang saling terhubung.
 * Dipakai di halaman Import Excel dan Statistik.
 *
 * Data fakultas (window.__fakultasList) diisi lewat <script> terpisah
 * di masing-masing halaman blade (lihat resources/views/mahasiswa/import.blade.php
 * dan resources/views/statistik/index.blade.php), BUKAN ditulis langsung di
 * atribut x-data, supaya tidak rawan rusak oleh tanda kutip/karakter khusus.
 */
window.fakultasPicker = function (initialFakultasId = '') {
    return {
        fakultasList: window.__fakultasList || [],
        selectedFakultasId: initialFakultasId ? String(initialFakultasId) : '',
        get prodiOptions() {
            const f = this.fakultasList.find((item) => item.id == this.selectedFakultasId);
            return f ? f.prodis : [];
        },
    };
};

Alpine.start();

/**
 * Modal konfirmasi custom bertema Flip7, menggantikan confirm() bawaan
 * browser. Dipakai di atribut onsubmit form:
 *   onsubmit="return confirmAction(this, 'Hapus data ini?')"
 * Markup modalnya ada di resources/views/components/app-layout.blade.php
 * (id="confirm-modal"), jadi otomatis tersedia di semua halaman yang
 * memakai <x-app-layout>.
 */
window.confirmAction = function (form, message) {
    const modal = document.getElementById('confirm-modal');
    const messageEl = document.getElementById('confirm-modal-message');
    if (!modal || !messageEl) {
        // Fallback kalau modalnya entah kenapa tidak ada di halaman ini
        return window.confirm(message);
    }

    messageEl.textContent = message;
    modal.classList.remove('hidden');
    window.__pendingConfirmForm = form;

    // requestAnimationFrame supaya browser sempat "melihat" state awal
    // (opacity 0, scale kecil) dulu sebelum class .modal-open ditambahkan,
    // sehingga transisi CSS-nya benar-benar terpicu (bukan langsung lompat).
    requestAnimationFrame(() => {
        requestAnimationFrame(() => modal.classList.add('modal-open'));
    });

    return false; // cegah submit langsung; submit sesungguhnya terjadi di tombol OK
};

function closeConfirmModal() {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    modal.classList.remove('modal-open');
    // Tunggu animasi keluar selesai (samakan dengan durasi transition di CSS)
    // baru benar-benar disembunyikan lewat class 'hidden'.
    setTimeout(() => modal.classList.add('hidden'), 220);
}

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('confirm-modal');
    if (!modal) return;

    document.getElementById('confirm-modal-ok')?.addEventListener('click', function () {
        const pendingForm = window.__pendingConfirmForm;
        closeConfirmModal();
        if (pendingForm) {
            pendingForm.submit();
            window.__pendingConfirmForm = null;
        }
    });

    document.getElementById('confirm-modal-cancel')?.addEventListener('click', function () {
        closeConfirmModal();
        window.__pendingConfirmForm = null;
    });
});
