import Alpine from 'alpinejs';
import Chart from 'chart.js/auto';
import ChartDataLabels from 'chartjs-plugin-datalabels';

window.Alpine = Alpine;
window.Chart = Chart;

// =====================================================================
//  HELPER GRAFIK (Chart.js) — dipakai di halaman Statistik & Kinerja
// =====================================================================

// Plugin datalabels didaftarkan global supaya bisa menampilkan angka
// persentase langsung di grafik.
Chart.register(ChartDataLabels);

// Semua grafik di aplikasi ini ditaruh dalam <div class="relative h-..">
// bertinggi tetap, jadi tingginya diatur container (bukan aspect ratio).
// Ini yang membuat grafik tetap proporsional di layar HP maupun desktop.
Chart.defaults.responsive = true;
Chart.defaults.maintainAspectRatio = false;

const percentFormat = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 1 });
const sumOf = (data) => data.reduce((a, b) => a + (Number(b) || 0), 0);
// Persentase dari total dataset, 1 angka desimal, format Indonesia (33,3%)
const percentText = (value, total) =>
    (total > 0 ? percentFormat.format((Number(value) / total) * 100) : '0') + '%';

/**
 * Palet warna berurutan untuk N kategori. Kategori TERAKHIR selalu abu-abu
 * (dipakai untuk "Belum Diisi"). Kalau `mono` diisi, semua kategori
 * (kecuali yang terakhir) memakai 1 warna itu, cocok untuk bar chart.
 */
window.chartPalette = function (count, mono = null) {
    const base = ['#047857', '#f59e0b', '#dc2626', '#0369a1', '#7c3aed', '#db2777', '#0d9488', '#ea580c', '#65a30d'];
    const colors = [];
    for (let i = 0; i < count - 1; i++) colors.push(mono || base[i % base.length]);
    colors.push('#9ca3af');
    return colors;
};

/**
 * Bungkus teks panjang jadi beberapa baris (Chart.js menerima label berupa
 * array = multi-baris), supaya label sumbu tidak saling menimpa di layar kecil.
 */
window.wrapLabel = function (text, maxChars = 14) {
    const lines = [];
    let line = '';
    String(text).split(' ').forEach((word) => {
        if (line && (line + ' ' + word).length > maxChars) {
            lines.push(line);
            line = word;
        } else {
            line = (line + ' ' + word).trim();
        }
    });
    if (line) lines.push(line);
    return lines;
};
window.wrapLabels = (labels, maxChars) => labels.map((l) => window.wrapLabel(l, maxChars));

/**
 * PIE: label nama kategori + persen di dalam irisan (contoh: "Belum Diisi
 * 100%"), BUKAN cuma angka persen saja — supaya tidak ambigu kalau cuma 1
 * kategori yang terisi dan lingkarannya jadi penuh 1 warna. Irisan yang
 * terlalu kecil (< minShare %) tidak diberi label supaya tidak bertumpuk —
 * persennya tetap tampil lengkap di legend (lihat pieLegendWithPercent)
 * dan di tooltip.
 */
window.pieDataLabels = function (minShare = 6) {
    return {
        color: '#fff',
        font: { weight: 'bold', size: 12 },
        textStrokeColor: 'rgba(0,0,0,0.4)',
        textStrokeWidth: 3,
        clamp: true,
        textAlign: 'center',
        display: (ctx) => {
            const total = sumOf(ctx.dataset.data);
            const value = Number(ctx.dataset.data[ctx.dataIndex]) || 0;
            return total > 0 && (value / total) * 100 >= minShare;
        },
        formatter: (value, ctx) => {
            const rawLabel = ctx.chart.data.labels[ctx.dataIndex];
            const label = Array.isArray(rawLabel) ? rawLabel.join(' ') : rawLabel;
            return window.wrapLabel(`${label} ${percentText(value, sumOf(ctx.dataset.data))}`, 14);
        },
    };
};

/**
 * PIE: legend di bawah grafik, setiap item menampilkan jumlah + persen
 * ("Ya: 12 (33,3%)"). Ini menjamin SEMUA kategori punya persentase yang
 * terbaca, sekecil apa pun irisannya.
 */
window.pieLegendWithPercent = function () {
    const baseGenerate = (Chart.overrides.pie || Chart.overrides.doughnut).plugins.legend.labels.generateLabels;
    return {
        position: 'bottom',
        labels: {
            boxWidth: 14,
            padding: 12,
            font: { size: 12 },
            generateLabels(chart) {
                const items = baseGenerate.call(this, chart);
                const dataset = chart.data.datasets[0];
                const total = sumOf(dataset.data);
                items.forEach((item) => {
                    const value = Number(dataset.data[item.index]) || 0;
                    item.text = `${item.text}: ${value} (${percentText(value, total)})`;
                });
                return items;
            },
        },
    };
};

/**
 * BAR: label persen SELALU di ujung batang (di atas untuk bar vertikal, di
 * kanan untuk bar horizontal). Ruang kosong untuk label disediakan lewat
 * `grace: '20%'` pada sumbu nilai (lihat contoh pemakaian di view), jadi label
 * tidak pernah menabrak legend atau terpotong di tepi grafik.
 */
window.barDataLabels = function (options = {}) {
    return {
        anchor: 'end',
        align: (ctx) => (ctx.chart.options.indexAxis === 'y' ? 'right' : 'top'),
        color: '#1E8C86',
        font: { weight: 'bold', size: 11 },
        clamp: true,
        display: (ctx) => (Number(ctx.dataset.data[ctx.dataIndex]) || 0) > 0,
        formatter: (value, ctx) => percentText(value, sumOf(ctx.dataset.data)),
        ...options,
    };
};

/**
 * Tooltip yang menampilkan jumlah + persentase.
 * withName = true untuk pie (nama kategori ikut ditulis), false untuk bar
 * (nama kategori sudah jadi judul tooltip).
 */
window.percentTooltip = function ({ withName = false } = {}) {
    return {
        callbacks: {
            label(ctx) {
                const value = Number(ctx.raw) || 0;
                const text = `${value} (${percentText(value, sumOf(ctx.dataset.data))})`;
                if (!withName) return ` ${text}`;
                const raw = ctx.chart.data.labels[ctx.dataIndex];
                const name = Array.isArray(raw) ? raw.join(' ') : raw;
                return ` ${name}: ${text}`;
            },
        },
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
