import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';

window.Alpine = Alpine;
window.Chart = Chart;

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
