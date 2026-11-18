<?php
/**
 * Template Name: E-Library & Repositori Modul
 * Description: Template halaman Perpustakaan Digital Prodi BK FIP UNM (Modul Pembelajaran, Buku Ajar, Panduan Praktikum, & Artikel Ilmiah)
 *              dengan Laci Detail Sitasi APA 7th & Pengajuan Unggah Modul terintegrasi WP-Admin.
 */

if (!defined('ABSPATH')) exit;

get_header();

$elibrary_items = function_exists('ppb_get_elibrary_items') ? ppb_get_elibrary_items() : array(
    array(
        'id'        => 1,
        'kode'      => 'ELB-01',
        'category'  => 'modul',
        'cat_label' => 'Modul Praktikum',
        'authors'   => 'Tim Dosen Laboratorium BK FIP UNM',
        'year'      => '2025',
        'meta'      => '64 Hlm · PDF 3.2 MB',
        'format'    => 'PDF',
        'title'     => 'Modul Praktikum Konseling Individual Berbasis Kognitif-Perilaku (CBT)',
        'desc'      => 'Panduan terstruktur praktikum microcounseling dan penerapan cognitive restructuring bagi mahasiswa sarjana.',
        'file_url'  => '#',
    ),
    array(
        'id'        => 2,
        'kode'      => 'ELB-02',
        'category'  => 'buku',
        'cat_label' => 'Pedoman Akademik',
        'authors'   => 'Program Studi BK FIP UNM',
        'year'      => '2025',
        'meta'      => '88 Hlm · PDF 4.1 MB',
        'format'    => 'PDF',
        'title'     => 'Buku Pedoman Praktik Pengalaman Lapangan (PPL) BK Sekolah',
        'desc'      => 'Ketentuan teknis, format laporan, instrumen penilaian, dan tata tertib pelaksanaan PPL BK di SMP/SMA/SMK mitra.',
        'file_url'  => '#',
    ),
    array(
        'id'        => 3,
        'kode'      => 'ELB-03',
        'category'  => 'asesmen',
        'cat_label' => 'Instrumen Asesmen',
        'authors'   => 'Laboratorium BK FIP UNM',
        'year'      => '2024',
        'meta'      => '76 Hlm · PDF 2.8 MB',
        'format'    => 'PDF',
        'title'     => 'Modul Asesmen Psikologis Non-Tes: Angket, Sosiometri & AUM',
        'desc'      => 'Petunjuk administrasi, skoring, tabulasi, dan interpretasi profil kebutuhan konseli menggunakan AUM dan Sosiometri.',
        'file_url'  => '#',
    ),
    array(
        'id'        => 4,
        'kode'      => 'ELB-04',
        'category'  => 'artikel',
        'cat_label' => 'Artikel Ilmiah',
        'authors'   => 'Sivitas Akademika BK FIP UNM',
        'year'      => '2024',
        'meta'      => '14 Hlm · PDF 850 KB',
        'format'    => 'PDF',
        'title'     => 'Pengembangan Model Bimbingan Karir Berorientasi Industri 4.0',
        'desc'      => 'Kajian empiris efektivitas model bimbingan karir kontekstual dalam meningkatkan career readiness siswa menengah.',
        'file_url'  => '#',
    ),
    array(
        'id'        => 5,
        'kode'      => 'ELB-05',
        'category'  => 'modul',
        'cat_label' => 'Modul Pembelajaran',
        'authors'   => 'Pengampu MK Konseling Kelompok',
        'year'      => '2024',
        'meta'      => '92 Hlm · PDF 3.6 MB',
        'format'    => 'PDF',
        'title'     => 'Diktat Dinamika Konseling Kelompok & Manajemen Konflik Remaja',
        'desc'      => 'Konsep tahapan pembentukan, peralihan, kegiatan, dan pengakhiran dalam kelompok serta teknik manajemen resistensi.',
        'file_url'  => '#',
    ),
    array(
        'id'        => 6,
        'kode'      => 'ELB-06',
        'category'  => 'buku',
        'cat_label' => 'Pedoman Akademik',
        'authors'   => 'GPM Program Studi BK UNM',
        'year'      => '2025',
        'meta'      => '110 Hlm · PDF 5.2 MB',
        'format'    => 'PDF',
        'title'     => 'Panduan Penulisan Skripsi & Tugas Akhir Bimbingan dan Konseling',
        'desc'      => 'Standar format proposal, laporan hasil, artikel jurnal syarat yudisium, kode etik sitasi APA 7th, dan uji Turnitin.',
        'file_url'  => '#',
    ),
);

$portal_links = function_exists('ppb_get_elibrary_portal_links') ? ppb_get_elibrary_portal_links() : array(
    array(
        'badge' => 'OJS · SINTA 2',
        'title' => 'Jurnal JKBK UNM',
        'sub'   => 'Jurnal Kajian Bimbingan & Konseling · E-ISSN 2549-8517',
        'cta'   => 'Portal OJS JKBK ↗',
        'href'  => 'https://journal.unm.ac.id/index.php/JKBK',
    ),
    array(
        'badge' => 'OJS · SINTA 4',
        'title' => 'Jurnal PJEC UNM',
        'sub'   => 'Pinisi Journal of Education & Counseling · E-ISSN 2809-5480',
        'cta'   => 'Portal OJS PJEC ↗',
        'href'  => 'https://ojs.unm.ac.id/PJEC',
    ),
    array(
        'badge' => '172 BUKU',
        'title' => 'Katalog Buku & Karya Dosen',
        'sub'   => 'Monograf ber-ISBN & publikasi SINTA 25 dosen PPB',
        'cta'   => 'Buka Direktori Dosen →',
        'href'  => home_url('/profil/dosen/'),
    ),
);

$ajax_url     = admin_url('admin-ajax.php');
$submit_nonce = wp_create_nonce('ppb_elibrary_submit_nonce');

$normalized_elibrary_items = array();
foreach ($elibrary_items as $idx => $item) {
    $raw_url = isset($item['file_url']) ? (string) $item['file_url'] : '';
    $clean_url = function_exists('ppb_clean_document_url')
        ? ppb_clean_document_url($raw_url)
        : (($raw_url === '#') ? '' : trim($raw_url));
    $item['id']       = isset($item['id']) ? (int) $item['id'] : ($idx + 1);
    $item['kode']     = !empty($item['kode']) ? $item['kode'] : sprintf('ELB-%02d', $idx + 1);
    $item['file_url'] = $clean_url;
    $normalized_elibrary_items[] = $item;
}
$elibrary_items = $normalized_elibrary_items;
?>

<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background-color:var(--canvas, #FAF8F5);font-family:var(--font-sans, 'Plus Jakarta Sans', sans-serif);color:var(--ink, #142219);">

    <!-- =================================================================== -->
    <!-- 1. HERO HEADER: PUSAT LAYANAN, LAB BK & E-LIBRARY                   -->
    <!-- =================================================================== -->
    <header class="bk-page-head">
        <div class="bk-container">
            <div class="bk-crumb">
                <a href="<?php echo esc_url(home_url('/')); ?>">Beranda</a>
                <span>/</span>
                <a href="<?php echo esc_url(home_url('/layanan/')); ?>">Layanan &amp; Lab BK</a>
                <span>/</span>
                <span>E-Library &amp; Modul</span>
            </div>
            <h1>E-Library &amp; Repositori Modul Prodi BK</h1>
            <p class="bk-page-lead">
                Akses terbuka modul praktikum laboratorium, pedoman akademik PPL &amp; tugas akhir, instrumen asesmen psikologis non-tes, serta karya artikel ilmiah sivitas akademika Jurusan PPB / Prodi BK FIP UNM.
            </p>
        </div>
    </header>

    <!-- =================================================================== -->
    <!-- 2. MAIN WORKSPACE: SUB-TABS, PORTAL CARDS, TOOLBAR & HAIRLINE LIST  -->
    <!-- =================================================================== -->
    <section class="bk-band">
        <div class="bk-container">

            <!-- Sub-navigation Tabs -->
            <nav class="bk-tabs" aria-label="Sub-menu Layanan &amp; Laboratorium BK">
                <a href="<?php echo esc_url(home_url('/layanan/persuratan/')); ?>" class="bk-tab-btn">Persuratan &amp; SOP</a>
                <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="bk-tab-btn">Lab BK &amp; Alat Tes</a>
                <a href="<?php echo esc_url(home_url('/e-library/')); ?>" class="bk-tab-btn is-active" aria-current="page">E-Library &amp; Modul</a>
                <a href="<?php echo esc_url(home_url('/layanan/perlindungan-mahasiswa/')); ?>" class="bk-tab-btn">Perlindungan &amp; Survei</a>
            </nav>

            <!-- Top 3 External Repository & Journal Cards -->
            <div class="bk-elib-links" aria-label="Tautan Repositori &amp; Jurnal Resmi">
                <?php foreach ($portal_links as $lk) :
                    $href_raw = isset($lk['href']) ? (string) $lk['href'] : '';
                    $href     = function_exists('ppb_clean_document_url') ? ppb_clean_document_url($href_raw) : (($href_raw === '#') ? '' : trim($href_raw));
                    $is_ext   = $href !== '' && preg_match('/^https?:\/\//i', $href) && strpos($href, home_url()) === false;
                ?>
                    <a href="<?php echo esc_url($href !== '' ? $href : home_url('/e-library/')); ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="bk-elib-link-card">
                        <div>
                            <span class="bk-tag bk-tag-sage" style="margin-bottom:8px;"><?php echo esc_html($lk['badge']); ?></span>
                            <strong class="bk-fd" style="display:block;font-size:15px;color:var(--ink);margin-bottom:4px;"><?php echo esc_html($lk['title']); ?></strong>
                            <div style="font-size:12.5px;color:var(--muted);line-height:1.45;"><?php echo esc_html($lk['sub']); ?></div>
                        </div>
                        <span class="bk-elib-cta bk-elib-link-cta"><?php echo esc_html($lk['cta']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- Search Input & Filter Pills Toolbar -->
            <div class="bk-toolbar">
                <input type="search" class="bk-inp" id="bk-elib-search" placeholder="Cari kode, judul modul, penulis, PPL…" aria-label="Cari koleksi E-Library">
                <div class="bk-filter-pills" id="bk-elib-pills" role="tablist" aria-label="Filter Kategori Koleksi">
                    <button type="button" class="bk-pill is-active" data-ek="all">Semua (<?php echo count($elibrary_items); ?>)</button>
                    <button type="button" class="bk-pill" data-ek="modul">Modul</button>
                    <button type="button" class="bk-pill" data-ek="buku">Pedoman</button>
                    <button type="button" class="bk-pill" data-ek="asesmen">Asesmen</button>
                    <button type="button" class="bk-pill" data-ek="artikel">Artikel</button>
                </div>
            </div>

            <!-- Hairline List of E-Library Items (ELB-01..ELB-xx) -->
            <div class="bk-hairline-list" id="bk-elib-list">
                <?php foreach ($elibrary_items as $idx => $item) :
                    $item_id   = (int) $item['id'];
                    $kode      = (string) $item['kode'];
                    $cat       = !empty($item['category']) ? $item['category'] : 'modul';
                    $cat_label = !empty($item['cat_label']) ? $item['cat_label'] : 'Modul Praktikum';
                    $authors   = !empty($item['authors']) ? $item['authors'] : 'Tim Dosen BK FIP UNM';
                    $year      = !empty($item['year']) ? $item['year'] : '2025';
                    $meta      = !empty($item['meta']) ? $item['meta'] : '64 Hlm · PDF 3.2 MB';
                    $title     = !empty($item['title']) ? $item['title'] : 'Modul Bimbingan dan Konseling';
                    $desc      = !empty($item['desc']) ? $item['desc'] : '';
                    $file_url  = (string) ($item['file_url'] ?? '');
                    $search_blob = strtolower($kode . ' ' . $title . ' ' . $authors . ' ' . $cat_label . ' ' . $desc);
                ?>
                    <div class="bk-hairline-row bk-elib-row"
                         data-id="<?php echo esc_attr($item_id); ?>"
                         data-cat="<?php echo esc_attr($cat); ?>"
                         data-search="<?php echo esc_attr($search_blob); ?>">
                        <div class="bk-rw">
                            <div style="display:flex;align-items:center;gap:8px;margin-bottom:5px;flex-wrap:wrap;">
                                <span class="bk-tag"><?php echo esc_html($kode); ?></span>
                                <span class="bk-tag bk-tag-sage"><?php echo esc_html($cat_label); ?></span>
                                <span class="bk-fm" style="font-size:11.5px;color:var(--muted);"><?php echo esc_html($year . ' · ' . $meta); ?></span>
                            </div>
                            <strong style="display:block;font-size:16px;color:var(--ink);margin-bottom:3px;"><?php echo esc_html($title); ?></strong>
                            <div style="font-size:12.5px;color:var(--forest);font-weight:600;margin-bottom:4px;">Oleh: <?php echo esc_html($authors); ?></div>
                            <span style="font-size:13.5px;color:var(--ink-2);"><?php echo esc_html($desc); ?></span>
                        </div>
                        <div class="bk-elib-act bk-elib-actions">
                            <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-open-elib="<?php echo esc_attr($item_id); ?>">Detail</button>
                            <?php if ($file_url !== '') : ?>
                                <a href="<?php echo esc_url($file_url); ?>" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-primary bk-btn-sm">Unduh PDF &#8599;</a>
                            <?php else : ?>
                                <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-toast="Berkas PDF sedang dalam proses pengunggahan oleh Admin/GPM.">⏳ Segera Diunggah</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Empty Search State -->
            <div class="bk-empty" id="bk-elib-empty" style="display:none;">
                <p style="margin:0 0 12px;">Tidak ada modul atau dokumen yang cocok dengan pencarian Anda.</p>
                <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-elib-reset">Reset Pencarian</button>
            </div>

            <!-- Bottom Submission Banner -->
            <aside class="bk-elib-submit-banner" aria-label="Unggah Karya E-Library">
                <div>
                    <span class="bk-badge-hero gold" style="margin-bottom:8px;">Kontribusi Sivitas &amp; Dosen</span>
                    <h3>Ajukan Unggah Modul / Naskah Dosen</h3>
                    <p>Dosen, peneliti, dan mahasiswa bimbingan PPB FIP UNM dapat mendistribusikan modul praktikum, instrumen asesmen BK, atau naskah ilmiah ke repositori E-Library resmi prodi.</p>
                </div>
                <button type="button" class="bk-btn bk-btn-gold" id="bk-elib-open-submit">Ajukan Unggah Modul →</button>
            </aside>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 3. SLIDE-OVER DRAWER (DETAIL MODUL SITASI APA 7TH & FORM PENGAJUAN) -->
    <!-- =================================================================== -->
    <div class="bk-backdrop" id="bk-elib-backdrop"></div>
    <aside class="bk-drawer" id="bk-elib-drawer" aria-label="Panel Detail dan Pengajuan E-Library" aria-hidden="true">
        <div class="bk-drawer-head">
            <div>
                <div class="bk-kicker" id="bk-drw-kicker">E-LIBRARY BK</div>
                <h2 class="bk-fd" style="font-size:21px;margin:4px 0 0;line-height:1.25;" id="bk-drw-title">Detail Koleksi</h2>
            </div>
            <button type="button" class="bk-icon-close" id="bk-drw-close" aria-label="Tutup Panel">✕</button>
        </div>
        <div class="bk-drawer-body" id="bk-drw-body"></div>
    </aside>

    <!-- Toast Notification -->
    <div class="bk-toast" id="bk-elib-toast" role="status" aria-live="polite"></div>

</div>

<script>
(function(){
    var ITEMS = <?php echo wp_json_encode(array_values($elibrary_items)); ?>;
    var AJAX_URL = <?php echo wp_json_encode($ajax_url); ?>;
    var NONCE = <?php echo wp_json_encode($submit_nonce); ?>;
    var SIPADU_URL = <?php echo wp_json_encode(get_option('ppb_layanan_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu')); ?>;

    var searchIn = document.getElementById('bk-elib-search');
    var pillsWrap = document.getElementById('bk-elib-pills');
    var listWrap = document.getElementById('bk-elib-list');
    var emptyBox = document.getElementById('bk-elib-empty');
    var resetBtn = document.getElementById('bk-elib-reset');
    var openSubBtn = document.getElementById('bk-elib-open-submit');

    var bdp = document.getElementById('bk-elib-backdrop');
    var drw = document.getElementById('bk-elib-drawer');
    var drwK = document.getElementById('bk-drw-kicker');
    var drwT = document.getElementById('bk-drw-title');
    var drwB = document.getElementById('bk-drw-body');
    var drwX = document.getElementById('bk-drw-close');
    var toastEl = document.getElementById('bk-elib-toast');
    var toastT = null;
    var activeCat = 'all';

    function esc(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function showToast(msg) {
        if (!toastEl) return;
        toastEl.textContent = msg;
        toastEl.classList.add('is-shown');
        clearTimeout(toastT);
        toastT = setTimeout(function(){ toastEl.classList.remove('is-shown'); }, 2600);
    }

    function openDrawer(kicker, title, html) {
        if (!drw || !bdp) return;
        drwK.textContent = kicker;
        drwT.textContent = title;
        drwB.innerHTML = html;
        drwB.scrollTop = 0;
        bdp.classList.add('is-open');
        drw.classList.add('is-open');
        drw.setAttribute('aria-hidden', 'false');
    }

    function closeDrawer() {
        if (!drw || !bdp) return;
        bdp.classList.remove('is-open');
        drw.classList.remove('is-open');
        drw.setAttribute('aria-hidden', 'true');
    }

    function openItemDetail(id) {
        var el = null;
        for (var i = 0; i < ITEMS.length; i++) {
            if (Number(ITEMS[i].id) === Number(id)) {
                el = ITEMS[i];
                break;
            }
        }
        if (!el) return;
        var kode = el.kode || ('ELB-0' + el.id);
        var catLabel = el.cat_label || el.catLabel || 'Modul Praktikum';
        var format = el.format || 'PDF';
        var meta = el.meta || 'PDF';
        var cit = el.authors + '. (' + el.year + '). ' + el.title + ' [' + catLabel + ']. Jurusan PPB FIP Universitas Negeri Makassar.';
        var hasUrl = (el.file_url && el.file_url !== '#' && String(el.file_url).indexOf('javascript:') !== 0);
        var dlBtn = hasUrl
            ? '<a href="' + esc(el.file_url) + '" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-primary bk-btn-sm">Unduh Dokumen PDF &#8599;</a>'
            : '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-toast="Berkas PDF sedang dalam proses pengunggahan oleh Admin/GPM.">⏳ Segera Diunggah</button>';

        openDrawer('E-LIBRARY BK · ' + kode, el.title,
            '<div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:18px;">' +
                '<span class="bk-tag bk-tag-sage">' + esc(catLabel) + '</span>' +
                '<span class="bk-tag bk-tag-gold">' + esc(format) + '</span>' +
                '<span class="bk-fm" style="font-size:12px;color:var(--muted);">' + esc(kode) + '</span>' +
            '</div>' +
            '<ul class="bk-spec-list" style="margin-bottom:20px;">' +
                '<li><span style="color:var(--muted);">Penyusun / Penulis</span><strong style="text-align:right;">' + esc(el.authors) + '</strong></li>' +
                '<li><span style="color:var(--muted);">Tahun Terbit</span><span class="bk-fm">' + esc(el.year) + '</span></li>' +
                '<li><span style="color:var(--muted);">Spesifikasi Berkas</span><span style="text-align:right;">' + esc(meta) + '</span></li>' +
                '<li><span style="color:var(--muted);">Status Berkas</span><strong style="color:' + (hasUrl ? 'var(--forest)' : '#B45309') + ';">' + (hasUrl ? 'Tersedia · Siap Diunduh' : 'Menunggu Unggahan Admin/GPM') + '</strong></li>' +
            '</ul>' +
            '<div class="bk-kicker" style="margin-bottom:6px;">Abstrak &amp; Ringkasan Materi</div>' +
            '<p style="font-size:14px;color:var(--ink-2);line-height:1.7;margin:0 0 20px;">' + esc(el.desc) + '</p>' +
            '<div style="background:var(--canvas);border:1px solid var(--border);border-radius:var(--r-md);padding:14px 16px;margin-bottom:22px;">' +
                '<div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap;">' +
                    '<span class="bk-kicker" style="margin:0;">Sitasi Akademik (APA 7th)</span>' +
                    '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" data-copy-cit="' + esc(cit) + '">Salin Sitasi</button>' +
                '</div>' +
                '<p style="margin:0;font-size:13px;color:var(--ink-2);line-height:1.55;">' + esc(cit) + '</p>' +
            '</div>' +
            '<div style="display:flex;gap:10px;flex-wrap:wrap;">' +
                dlBtn +
                '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-drw-open-sub">Ajukan Unggah Modul →</button>' +
            '</div>'
        );
    }

    function openSubmitDrawer() {
        openDrawer('KONTRIBUSI REPOSITORI E-LIBRARY', 'Ajukan Unggah Modul / Naskah Dosen',
            '<p style="font-size:13.5px;color:var(--ink-2);line-height:1.65;margin:0 0 18px;">Dosen, tim pengampu mata kuliah, atau peneliti Prodi BK FIP UNM dapat mengajukan modul pembelajaran, instrumen asesmen, atau panduan praktikum untuk ditinjau Gugus Penjaminan Mutu (GPM).</p>' +
            '<form id="bk-elib-submit-form" class="bk-form-stack">' +
                '<div class="bk-form-group"><label class="bk-form-label" for="sub-jdl">Judul Modul / Pedoman / Naskah *</label><input type="text" id="sub-jdl" class="bk-inp" required placeholder="Contoh: Modul Praktikum Konseling Karier Digital"></div>' +
                '<div class="bk-form-group"><label class="bk-form-label" for="sub-kat">Kategori Koleksi *</label><select id="sub-kat" class="bk-inp"><option value="Modul Praktikum">Modul Praktikum &amp; Pembelajaran</option><option value="Pedoman Akademik">Pedoman PPL &amp; Tugas Akhir</option><option value="Instrumen Asesmen">Instrumen &amp; Asesmen BK</option><option value="Artikel Ilmiah">Artikel Ilmiah / Monograf</option></select></div>' +
                '<div class="bk-form-group"><label class="bk-form-label" for="sub-usr">Nama Pengusul &amp; NIP / NIM *</label><input type="text" id="sub-usr" class="bk-inp" required placeholder="Contoh: Dr. Suciani Latif, M.Pd. · NIP 1982..."></div>' +
                '<div class="bk-form-group"><label class="bk-form-label" for="sub-lnk">Tautan Berkas PDF (Drive / OSF / Repositori) *</label><input type="url" id="sub-lnk" class="bk-inp" required placeholder="https://drive.google.com/..."></div>' +
                '<div class="bk-form-group"><label class="bk-form-label" for="sub-cat">Ringkasan Materi &amp; Tahun Terbit (Opsional)</label><textarea id="sub-cat" class="bk-inp" style="min-height:84px;resize:vertical;" placeholder="Cantumkan sasaran mata kuliah OBE, jumlah halaman, atau catatan lisensi…"></textarea></div>' +
                '<div style="display:flex;gap:10px;flex-wrap:wrap;padding-top:6px;">' +
                    '<button type="submit" class="bk-btn bk-btn-primary bk-btn-sm" id="bk-sub-submit-btn">Kirim Pengajuan Repositori →</button>' +
                    '<a href="' + esc(SIPADU_URL) + '" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-secondary bk-btn-sm">Ajukan via SIPADU ↗</a>' +
                '</div>' +
            '</form>'
        );
    }

    function renderTicketConfirmation(tik, jdl, kat, usr, cat) {
        drwB.innerHTML =
            '<div style="background:var(--sage-soft);border:1px solid var(--border-sage);border-radius:var(--r-lg);padding:18px;margin-bottom:20px;">' +
                '<span class="bk-tag bk-tag-sage" style="margin-bottom:8px;">✓ Pengajuan Naskah Terdaftar · Tiket ' + esc(tik) + '</span>' +
                '<h3 class="bk-fd" style="font-size:19px;margin:0 0 6px;color:var(--forest);">Usulan Berhasil Dikirim ke Kurasi E-Library</h3>' +
                '<p style="margin:0;font-size:13.5px;color:var(--ink-2);line-height:1.6;">Tim Admin E-Library &amp; GPM akan meninjau kelengkapan naskah di WP-Admin sebelum dipublikasikan pada katalog repositori Prodi BK.</p>' +
            '</div>' +
            '<ul class="bk-spec-list" style="margin-bottom:22px;">' +
                '<li><span style="color:var(--muted);">Kode Tiket</span><strong class="bk-fm" style="color:var(--forest);">' + esc(tik) + '</strong></li>' +
                '<li><span style="color:var(--muted);">Judul Naskah</span><span style="text-align:right;font-weight:600;">' + esc(jdl) + '</span></li>' +
                '<li><span style="color:var(--muted);">Kategori</span><span style="text-align:right;">' + esc(kat) + '</span></li>' +
                '<li><span style="color:var(--muted);">Pengusul</span><span style="text-align:right;">' + esc(usr) + '</span></li>' +
                (cat ? '<li><span style="color:var(--muted);">Catatan</span><span style="text-align:right;">' + esc(cat) + '</span></li>' : '') +
                '<li><span style="color:var(--muted);">Status</span><strong style="color:var(--forest);">Menunggu Kurasi Admin &amp; GPM</strong></li>' +
            '</ul>' +
            '<div style="display:flex;gap:10px;flex-wrap:wrap;">' +
                '<button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-elib-another">Ajukan Naskah Lain</button>' +
                '<button type="button" class="bk-btn bk-btn-primary bk-btn-sm" id="bk-elib-done-x">Selesai &amp; Tutup</button>' +
            '</div>';
        showToast('Pengajuan naskah ' + tik + ' tercatat untuk kurasi repositori.');
    }

    function applyFilters() {
        var q = searchIn ? searchIn.value.toLowerCase().trim() : '';
        var rows = listWrap ? listWrap.querySelectorAll('.bk-elib-row') : [];
        var visible = 0;

        for (var i = 0; i < rows.length; i++) {
            var r = rows[i];
            var rCat = r.getAttribute('data-cat') || '';
            var rSearch = r.getAttribute('data-search') || '';
            var okCat = (activeCat === 'all' || rCat === activeCat);
            var okQ = (!q || rSearch.indexOf(q) !== -1);
            if (okCat && okQ) {
                r.style.display = 'flex';
                visible++;
            } else {
                r.style.display = 'none';
            }
        }

        if (listWrap) listWrap.style.display = visible > 0 ? 'block' : 'none';
        if (emptyBox) emptyBox.style.display = visible === 0 ? 'block' : 'none';
    }

    if (searchIn) {
        searchIn.addEventListener('input', applyFilters);
    }

    if (pillsWrap) {
        pillsWrap.addEventListener('click', function(e){
            var btn = e.target.closest('[data-ek]');
            if (!btn) return;
            activeCat = btn.getAttribute('data-ek') || 'all';
            var allBtns = pillsWrap.querySelectorAll('.bk-pill');
            for (var i = 0; i < allBtns.length; i++) {
                allBtns[i].classList.toggle('is-active', allBtns[i] === btn);
            }
            applyFilters();
        });
    }

    if (resetBtn) {
        resetBtn.addEventListener('click', function(){
            if (searchIn) searchIn.value = '';
            activeCat = 'all';
            if (pillsWrap) {
                var allBtns = pillsWrap.querySelectorAll('.bk-pill');
                for (var i = 0; i < allBtns.length; i++) {
                    allBtns[i].classList.toggle('is-active', allBtns[i].getAttribute('data-ek') === 'all');
                }
            }
            applyFilters();
        });
    }

    if (listWrap) {
        listWrap.addEventListener('click', function(e){
            var detBtn = e.target.closest('[data-open-elib]');
            if (detBtn) {
                openItemDetail(detBtn.getAttribute('data-open-elib'));
                return;
            }
            var tBtn = e.target.closest('[data-toast]');
            if (tBtn) {
                showToast(tBtn.getAttribute('data-toast'));
            }
        });
    }

    if (openSubBtn) {
        openSubBtn.addEventListener('click', openSubmitDrawer);
    }
    if (drwX) drwX.addEventListener('click', closeDrawer);
    if (bdp) bdp.addEventListener('click', closeDrawer);

    if (drwB) {
        drwB.addEventListener('click', function(e){
            var b = e.target.closest('button');
            if (!b) return;
            if (b.getAttribute('data-toast')) {
                showToast(b.getAttribute('data-toast'));
            } else if (b.getAttribute('data-copy-cit')) {
                var cit = b.getAttribute('data-copy-cit');
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(cit).catch(function(){});
                }
                showToast('Format sitasi APA 7th disalin.');
            } else if (b.id === 'bk-drw-open-sub' || b.id === 'bk-elib-another') {
                openSubmitDrawer();
            } else if (b.id === 'bk-elib-done-x') {
                closeDrawer();
            }
        });

        drwB.addEventListener('submit', function(e){
            var f = e.target.closest('#bk-elib-submit-form');
            if (!f) return;
            e.preventDefault();
            var jdl = (document.getElementById('sub-jdl') ? document.getElementById('sub-jdl').value : '').trim();
            var kat = (document.getElementById('sub-kat') ? document.getElementById('sub-kat').value : '').trim();
            var usr = (document.getElementById('sub-usr') ? document.getElementById('sub-usr').value : '').trim();
            var lnk = (document.getElementById('sub-lnk') ? document.getElementById('sub-lnk').value : '').trim();
            var cat = (document.getElementById('sub-cat') ? document.getElementById('sub-cat').value : '').trim();

            if (!jdl || !usr || !lnk) {
                showToast('Mohon lengkapi judul, pengusul, dan tautan naskah.');
                return;
            }

            var subBtn = document.getElementById('bk-sub-submit-btn');
            if (subBtn) { subBtn.disabled = true; subBtn.textContent = 'Mengirim ke Kurasi…'; }

            var fallbackTik = 'ELB-' + String(Date.now()).slice(-4);
            if (AJAX_URL && window.fetch) {
                var fd = new FormData();
                fd.append('action', 'ppb_submit_elibrary');
                fd.append('nonce', NONCE);
                fd.append('title', jdl);
                fd.append('category', kat);
                fd.append('author', usr);
                fd.append('file_url', lnk);
                fd.append('notes', cat);

                fetch(AJAX_URL, { method: 'POST', body: fd, credentials: 'same-origin' })
                    .then(function(r){ return r.json(); })
                    .then(function(res){
                        if (res && res.success) {
                            var tik = (res.data && res.data.ticket) ? res.data.ticket : fallbackTik;
                            renderTicketConfirmation(tik, jdl, kat, usr, cat);
                        } else {
                            if (subBtn) { subBtn.disabled = false; subBtn.textContent = 'Kirim Pengajuan Repositori →'; }
                            showToast((res && res.data && res.data.message) ? res.data.message : 'Gagal mengirim pengajuan.');
                        }
                    })
                    .catch(function(){
                        if (subBtn) { subBtn.disabled = false; subBtn.textContent = 'Kirim Pengajuan Repositori →'; }
                        showToast('Gagal terhubung ke server.');
                    });
            }
        });
    }

    document.addEventListener('keydown', function(e){
        if (e.key === 'Escape' && drw && drw.classList.contains('is-open')) {
            closeDrawer();
        }
    });
})();
</script>

<?php get_footer(); ?>
