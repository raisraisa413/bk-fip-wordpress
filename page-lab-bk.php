<?php
/**
 * Template Name: Microsite Portal Khusus Laboratorium BK
 * Description: Portal khusus Laboratorium Bimbingan dan Konseling FIP UNM dengan navigasi mandiri,
 *              5 halaman bidang interaktif, inventaris alat tes, kegiatan, lomba, skrining mandiri,
 *              dan formulir pendaftaran terpadu.
 */

if (!defined('ABSPATH')) exit;

// Deteksi rute aktif dari REQUEST_URI
$req_uri  = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$req_path = trim((string) parse_url($req_uri, PHP_URL_PATH), '/');
if (strpos($req_path, 'yamato/') === 0) {
    $req_path = substr($req_path, 7);
}

$active_view = 'home';
$route_map = [
    'lab-bk/inventaris'             => 'inventaris',
    'lab-bk/kegiatan'               => 'kegiatan',
    'lab-bk/lomba'                  => 'lomba',
    'lab-bk/bidang-konseling'       => 'konseling',
    'bidang-konseling'              => 'konseling',
    'lab-bk/bidang-asesmen'         => 'asesmen',
    'bidang-asesmen'                => 'asesmen',
    'lab-bk/bidang-training'        => 'training',
    'bidang-training'               => 'training',
    'lab-bk/bidang-media'           => 'media',
    'bidang-media'                  => 'media',
    'lab-bk/bidang-ekstrakurikuler' => 'ekstrakurikuler',
    'bidang-ekstrakurikuler'        => 'ekstrakurikuler',
];
if (isset($route_map[$req_path])) {
    $active_view = $route_map[$req_path];
}

$all_bidang    = function_exists('ppb_get_lab_bidang_data') ? ppb_get_lab_bidang_data() : [];
$is_bidang     = isset($all_bidang[$active_view]);
$cur_bidang    = $is_bidang ? $all_bidang[$active_view] : null;
$kegiatan_list = function_exists('ppb_get_lab_kegiatan_list') ? ppb_get_lab_kegiatan_list() : [];
$lomba_list    = function_exists('ppb_get_lab_lomba_list') ? ppb_get_lab_lomba_list() : [];

// Ambil daftar alat tes dari CPT ppb_inventaris_lab
$inventaris_items = get_posts([
    'post_type'      => 'ppb_inventaris_lab',
    'post_status'    => 'publish',
    'posts_per_page' => 100,
    'orderby'        => 'title',
    'order'          => 'ASC',
]);

$logo_url        = get_template_directory_uri() . '/assets/images/logo-lab-bk.png';
$peersupport_url = 'https://labbk8.wixsite.com/peersupport';
$ajax_url        = admin_url('admin-ajax.php');
$reg_nonce       = wp_create_nonce('ppb_lab_pendaftaran_public_nonce');

$titles_map = [
    'home'       => 'Laboratorium BK FIP UNM — Portal Resmi Terpadu',
    'inventaris' => 'Inventaris Alat Tes & Ruang — Laboratorium BK FIP UNM',
    'kegiatan'   => 'Kegiatan & Praktikum — Laboratorium BK FIP UNM',
    'lomba'      => 'Lomba Non-Akademik — Laboratorium BK FIP UNM',
];
$page_title = $cur_bidang
    ? ($cur_bidang['nama'] . ' — Laboratorium BK FIP UNM')
    : ($titles_map[$active_view] ?? $titles_map['home']);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($page_title); ?></title>
    <?php wp_head(); ?>
    <style>
        :root {
            --lab-navy: #14274E;
            --lab-navy-deep: #0B1730;
            --lab-gold: #F5B82E;
            --lab-gold-soft: #FEF3C7;
            --lab-emerald: #0D3B22;
            --lab-emerald-soft: #E8F0EA;
            --lab-bg: #F8FAFC;
            --lab-surface: #FFFFFF;
            --lab-ink: #0F172A;
            --lab-muted: #475569;
            --lab-border: #E2E8F0;
        }
        body.bk-lab-microsite {
            margin: 0;
            padding: 0;
            background: var(--lab-bg);
            color: var(--lab-ink);
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        .lab-wrap {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        /* Topbar & Navbar Terang agar Logo Lab BK Menyatu Tanpa Border Putih */
        .lab-topbar {
            background: var(--lab-navy-deep);
            color: rgba(255,255,255,0.88);
            font-size: 12px;
        }
        .lab-topbar-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 7px 0;
            gap: 12px;
            flex-wrap: wrap;
        }
        .lab-nav {
            position: sticky;
            top: 0;
            z-index: 900;
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(12px);
            border-bottom: 2px solid var(--lab-gold);
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.06);
        }
        .lab-nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            min-height: 70px;
        }
        .lab-brand {
            display: flex;
            align-items: center;
            gap: 11px;
            text-decoration: none;
            color: var(--lab-navy);
            flex-shrink: 0;
        }
        .lab-brand img {
            width: 44px;
            height: 44px;
            object-fit: contain;
            display: block;
            border: none;
            background: transparent;
            padding: 0;
        }
        .lab-brand-title {
            font-family: 'Fraunces', Georgia, serif;
            font-size: 17px;
            font-weight: 800;
            line-height: 1.15;
            color: var(--lab-navy);
        }
        .lab-brand-sub {
            font-size: 11px;
            color: var(--lab-emerald);
            font-weight: 700;
            letter-spacing: 0.02em;
        }
        .lab-menu {
            display: flex;
            align-items: center;
            gap: 3px;
            flex-wrap: wrap;
        }
        .lab-menu a {
            color: var(--lab-navy);
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 11px;
            border-radius: 8px;
            transition: all 0.15s ease;
        }
        .lab-menu a:hover {
            background: #F1F5F9;
            color: var(--lab-emerald);
        }
        .lab-menu a.is-active {
            background: var(--lab-navy);
            color: var(--lab-gold);
        }
        .lab-nav-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }
        .lab-btn-back {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 700;
            color: var(--lab-navy);
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            padding: 8px 12px;
            border-radius: 9px;
            text-decoration: none;
        }
        .lab-btn-back:hover {
            background: #E2E8F0;
        }
        .lab-btn-gold {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: var(--lab-navy-deep);
            background: var(--lab-gold);
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
            transition: transform 0.15s ease;
        }
        .lab-btn-gold:hover {
            transform: translateY(-1px);
        }
        .lab-btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            background: var(--lab-navy);
            border: none;
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
        }
        .lab-btn-primary:hover {
            background: var(--lab-emerald);
        }
        .lab-btn-outline {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 18px;
            border-radius: 10px;
            text-decoration: none;
            cursor: pointer;
        }
        .lab-mobile-toggle {
            display: none;
            background: var(--lab-navy);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
        }
        /* Hero */
        .lab-hero {
            background: linear-gradient(135deg, var(--lab-navy-deep) 0%, var(--lab-navy) 55%, var(--lab-emerald) 100%);
            color: #ffffff;
            padding: 52px 0 60px;
            position: relative;
        }
        .lab-hero-grid {
            display: grid;
            grid-template-columns: 1.25fr 0.9fr;
            gap: 32px;
            align-items: center;
        }
        .lab-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(245, 184, 46, 0.18);
            color: var(--lab-gold);
            border: 1px solid rgba(245, 184, 46, 0.35);
            font-size: 11.5px;
            font-weight: 700;
            padding: 5px 12px;
            border-radius: 99px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
        }
        .lab-hero h1 {
            font-family: 'Fraunces', Georgia, serif;
            font-size: clamp(28px, 4vw, 42px);
            line-height: 1.18;
            margin: 0 0 14px;
            color: #ffffff;
        }
        .lab-hero p {
            font-size: 15.5px;
            color: rgba(255,255,255,0.86);
            margin: 0 0 24px;
            max-width: 600px;
        }
        .lab-hero-card {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.16);
            border-radius: 20px;
            padding: 24px;
            backdrop-filter: blur(10px);
        }
        /* Sections & Cards */
        .lab-section {
            padding: 48px 0;
        }
        .lab-section-head {
            margin-bottom: 24px;
        }
        .lab-eyebrow {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--lab-emerald);
            display: block;
            margin-bottom: 4px;
        }
        .lab-section-head h2 {
            font-family: 'Fraunces', Georgia, serif;
            font-size: clamp(22px, 2.8vw, 30px);
            color: var(--lab-navy);
            margin: 0 0 6px;
        }
        .lab-grid-2 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }
        .lab-grid-4 {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 18px;
        }
        .lab-grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(310px, 1fr));
            gap: 20px;
        }
        .lab-card {
            background: var(--lab-surface);
            border: 1px solid var(--lab-border);
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 4px 16px rgba(15, 23, 42, 0.04);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: transform 0.16s ease, box-shadow 0.16s ease, border-color 0.16s ease;
        }
        .lab-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(20, 39, 78, 0.08);
            border-color: #CBD5E1;
        }
        .lab-pill {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 99px;
            background: var(--lab-emerald-soft);
            color: var(--lab-emerald);
        }
        .lab-pill-gold {
            background: var(--lab-gold-soft);
            color: #92400E;
        }
        .lab-filter-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 22px;
        }
        .lab-filter-btn {
            background: #ffffff;
            color: var(--lab-muted);
            border: 1px solid var(--lab-border);
            border-radius: 99px;
            padding: 7px 15px;
            font-size: 12.5px;
            font-weight: 700;
            cursor: pointer;
        }
        .lab-filter-btn.is-active {
            background: var(--lab-navy);
            color: #ffffff;
            border-color: var(--lab-navy);
        }
        .lab-skrining-box {
            background: #ffffff;
            border: 1px solid var(--lab-border);
            border-radius: 22px;
            padding: 28px;
            box-shadow: 0 8px 28px rgba(20, 39, 78, 0.06);
        }
        .lab-q-row {
            padding: 14px 0;
            border-bottom: 1px solid #F1F5F9;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }
        .lab-q-opts {
            display: flex;
            gap: 6px;
            flex-wrap: wrap;
        }
        .lab-q-opts label {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 11px;
            border: 1px solid var(--lab-border);
            border-radius: 8px;
            cursor: pointer;
            background: #F8FAFC;
        }
        .lab-footer {
            background: var(--lab-navy-deep);
            color: rgba(255,255,255,0.82);
            padding: 44px 0 28px;
            margin-top: 40px;
        }
        .lab-footer-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr 1fr;
            gap: 28px;
            margin-bottom: 28px;
        }
        .lab-footer a {
            color: rgba(255,255,255,0.82);
            text-decoration: none;
            font-size: 13px;
        }
        .lab-footer a:hover {
            color: var(--lab-gold);
        }
        @media (max-width: 1020px) {
            .lab-hero-grid, .lab-footer-grid {
                grid-template-columns: 1fr;
            }
            .lab-menu {
                display: none;
                width: 100%;
                padding: 12px 0;
                border-top: 1px solid var(--lab-border);
            }
            .lab-menu.is-open {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
            }
            .lab-nav-inner {
                flex-wrap: wrap;
                padding: 10px 0;
            }
            .lab-mobile-toggle {
                display: inline-flex;
            }
        }
    </style>
</head>
<body <?php body_class('bk-lab-microsite'); ?>>

<!-- Topbar Lab BK -->
<div class="lab-topbar">
    <div class="lab-wrap lab-topbar-inner">
        <div>🏛️ <strong>Portal Resmi Laboratorium Bimbingan &amp; Konseling</strong> &bull; FIP Universitas Negeri Makassar</div>
        <div>🕒 Senin – Jumat, 08.00 – 16.00 WITA &bull; ✉️ admin@labbk.unm.ac.id &bull; 📸 @labbk.unm</div>
    </div>
</div>

<!-- Navbar Khusus Lab BK (Latar Putih Bersih agar Logo Tanpa Border Putih) -->
<header class="lab-nav">
    <div class="lab-wrap lab-nav-inner">
        <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" class="lab-brand">
            <img src="<?php echo esc_url($logo_url); ?>" alt="Logo Resmi Laboratorium BK FIP UNM">
            <div>
                <div class="lab-brand-title">Laboratorium BK</div>
                <div class="lab-brand-sub">FIP · Universitas Negeri Makassar</div>
            </div>
        </a>

        <button type="button" class="lab-mobile-toggle" onclick="document.getElementById('labMainMenu').classList.toggle('is-open')">☰ Menu Lab</button>

        <nav id="labMainMenu" class="lab-menu" aria-label="Navigasi Laboratorium BK">
            <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" class="<?php echo $active_view === 'home' ? 'is-active' : ''; ?>">Beranda</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-konseling/')); ?>" class="<?php echo $active_view === 'konseling' ? 'is-active' : ''; ?>">Konseling</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-asesmen/')); ?>" class="<?php echo $active_view === 'asesmen' ? 'is-active' : ''; ?>">Asesmen</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-training/')); ?>" class="<?php echo $active_view === 'training' ? 'is-active' : ''; ?>">Training</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-media/')); ?>" class="<?php echo $active_view === 'media' ? 'is-active' : ''; ?>">Media</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-ekstrakurikuler/')); ?>" class="<?php echo $active_view === 'ekstrakurikuler' ? 'is-active' : ''; ?>">Ekstrakurikuler</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="<?php echo $active_view === 'inventaris' ? 'is-active' : ''; ?>">Inventaris</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/kegiatan/')); ?>" class="<?php echo $active_view === 'kegiatan' ? 'is-active' : ''; ?>">Kegiatan</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/lomba/')); ?>" class="<?php echo $active_view === 'lomba' ? 'is-active' : ''; ?>">Lomba</a>
        </nav>

        <div class="lab-nav-actions">
            <a href="<?php echo esc_url(home_url('/')); ?>" class="lab-btn-back">&larr; Web Jurusan</a>
        </div>
    </div>
</header>

<?php if ($active_view === 'home') : ?>
    <!-- ==================== BERANDA MICROSITE LAB BK ==================== -->
    <section class="lab-hero">
        <div class="lab-wrap lab-hero-grid">
            <div>
                <span class="lab-badge">✨ Portal Terpadu Laboratorium BK &bull; 100% Rahasia &amp; Etis</span>
                <h1>Ruang aman buat cerita, belajar bareng, dan tumbuh tanpa di-judge.</h1>
                <p>
                    Seluruh layanan Laboratorium Bimbingan &amp; Konseling FIP UNM kini terpusat di satu tempat:
                    pendaftaran konseling sebaya, skrining kondisi diri mandiri, inventaris &amp; peminjaman alat tes,
                    pelatihan kompetensi, produksi media, hingga wadah ekstrakurikuler non-akademik mahasiswa.
                </p>
                <div style="display:flex;flex-wrap:wrap;gap:10px;">
                    <a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-gold">💬 Daftar Konseling ↗</a>
                    <a href="#skrining-mandiri" class="lab-btn-outline">🧠 Skrining Mandiri</a>
                    <a href="#lima-bidang" class="lab-btn-outline">🏛️ 5 Bidang Lab</a>
                </div>
            </div>
            <div class="lab-hero-card">
                <div style="display:flex;align-items:center;gap:14px;margin-bottom:14px;">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo Lab BK" style="width:52px;height:52px;object-fit:contain;">
                    <div>
                        <div style="font-size:15px;font-weight:800;color:#ffffff;">Prinsip Ruang Aman Lab BK</div>
                        <div style="font-size:12px;color:var(--lab-gold);">Gedung FIP UNM, Kampus Gunungsari Makassar</div>
                    </div>
                </div>
                <p style="font-size:13px;color:rgba(255,255,255,0.86);margin:0 0 14px;">
                    Semua cerita, hasil asesmen, dan konsultasi dilindungi kode etik kerahasiaan konselor. Didampingi langsung oleh 5 Dosen Koordinator bidang dan tim asisten terlatih.
                </p>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:12px;">
                    <div style="background:rgba(255,255,255,0.08);padding:10px;border-radius:10px;">
                        <strong style="color:var(--lab-gold);display:block;">5 Bidang Aktif</strong>
                        <span>Satu Portal Terpadu</span>
                    </div>
                    <div style="background:rgba(255,255,255,0.08);padding:10px;border-radius:10px;">
                        <strong style="color:var(--lab-gold);display:block;">Senin – Jumat</strong>
                        <span>08.00 – 16.00 WITA</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Visi & Misi Lab BK -->
    <section class="lab-section" style="padding-bottom:10px;">
        <div class="lab-wrap">
            <div class="lab-grid-2">
                <div class="lab-card" style="border-left:4px solid var(--lab-navy);">
                    <div>
                        <span class="lab-eyebrow">Visi Laboratorium BK</span>
                        <h3 style="margin:0 0 8px;font-size:20px;color:var(--lab-navy);">Pusat Layanan, Inovasi &amp; Pengembangan Profesi Konselor</h3>
                        <p style="font-size:13.5px;color:var(--lab-muted);margin:0;">
                            Menjadi laboratorium bimbingan dan konseling yang unggul, humanis, etis, dan berbasis riset terapan dalam mendukung kesehatan mental sivitas akademika serta pengembangan kompetensi calon konselor profesional.
                        </p>
                    </div>
                </div>
                <div class="lab-card" style="border-left:4px solid var(--lab-gold);">
                    <div>
                        <span class="lab-eyebrow">Misi Laboratorium BK</span>
                        <ul style="margin:6px 0 0;padding-left:18px;font-size:13.5px;color:var(--lab-muted);display:grid;gap:6px;">
                            <li>Menyediakan layanan konseling sebaya, individual, dan kelompok yang aman serta rahasia.</li>
                            <li>Mengembangkan layanan asesmen psikologis dan pengelolaan bank alat tes terstandar.</li>
                            <li>Menyelenggarakan pelatihan keterampilan konseling, produksi media edukasi, dan wadah bakat non-akademik mahasiswa.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Aksi Cepat Interaktif -->
    <section class="lab-section" style="padding-bottom:12px;">
        <div class="lab-wrap">
            <div class="lab-grid-4">
                <div class="lab-card">
                    <div>
                        <span class="lab-pill">Privat &amp; Rahasia</span>
                        <h3 style="margin:10px 0 6px;font-size:17px;color:var(--lab-navy);">Curhat &amp; Konseling</h3>
                        <p style="font-size:13px;color:var(--lab-muted);margin:0 0 14px;">Jadwalkan sesi bersama konselor sebaya atau dosen pembina Lab BK.</p>
                    </div>
                    <a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-primary">Daftar Konseling ↗</a>
                </div>
                <div class="lab-card">
                    <div>
                        <span class="lab-pill">Hasil Instan</span>
                        <h3 style="margin:10px 0 6px;font-size:17px;color:var(--lab-navy);">Skrining Kondisi Diri</h3>
                        <p style="font-size:13px;color:var(--lab-muted);margin:0 0 14px;">Cek tingkat stres akademik dan kondisi emosimu langsung di halaman ini.</p>
                    </div>
                    <a href="#skrining-mandiri" class="lab-btn-primary">Mulai Skrining ↓</a>
                </div>
                <div class="lab-card">
                    <div>
                        <span class="lab-pill">Alat Tes Psikologi</span>
                        <h3 style="margin:10px 0 6px;font-size:17px;color:var(--lab-navy);">Inventaris &amp; Peminjaman</h3>
                        <p style="font-size:13px;color:var(--lab-muted);margin:0 0 14px;">Lihat katalog alat tes psikologi dan ajukan peminjaman praktikum.</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="lab-btn-primary">Buka Inventaris →</a>
                </div>
                <div class="lab-card">
                    <div>
                        <span class="lab-pill lab-pill-gold">Non-Akademik</span>
                        <h3 style="margin:10px 0 6px;font-size:17px;color:var(--lab-navy);">Klub Ekstrakurikuler</h3>
                        <p style="font-size:13px;color:var(--lab-muted);margin:0 0 14px;">Asah minat-bakat seni, public speaking, olahraga, dan info lomba mahasiswa.</p>
                    </div>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-ekstrakurikuler/')); ?>" class="lab-btn-primary">Gabung Komunitas →</a>
                </div>
            </div>
        </div>
    </section>

    <!-- 5 Pilar Bidang Lab BK -->
    <section id="lima-bidang" class="lab-section">
        <div class="lab-wrap">
            <div class="lab-section-head">
                <span class="lab-eyebrow">01 — Struktur &amp; Ranah Kerja</span>
                <h2>Lima Pilar Bidang Laboratorium BK</h2>
                <p style="color:var(--lab-muted);margin:0;max-width:680px;">Pilih salah satu bidang untuk mengakses layanan interaktif, jadwal, daftar program/klub, dan formulir pendaftaran.</p>
            </div>
            <div class="lab-grid-3">
                <?php
                $idx = 1;
                foreach ($all_bidang as $b_key => $b) :
                    $num = sprintf('%02d', $idx++);
                ?>
                    <div class="lab-card">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                                <span class="lab-pill <?php echo $b_key === 'ekstrakurikuler' ? 'lab-pill-gold' : ''; ?>"><?php echo esc_html($num . ' · Bidang Lab'); ?></span>
                                <span style="font-size:22px;"><?php echo esc_html($b['icon']); ?></span>
                            </div>
                            <h3 style="margin:0 0 6px;font-size:19px;color:var(--lab-navy);"><?php echo esc_html($b['nama']); ?></h3>
                            <p style="font-size:13.5px;color:var(--lab-muted);margin:0 0 14px;"><?php echo esc_html($b['tagline']); ?></p>
                        </div>
                        <div>
                            <div style="font-size:12px;color:var(--lab-emerald);font-weight:700;padding:10px 0;border-top:1px solid var(--lab-border);margin-bottom:10px;">
                                👤 Koordinator: <?php echo esc_html($b['koordinator']); ?>
                            </div>
                            <a href="<?php echo esc_url(home_url('/lab-bk/bidang-' . $b_key . '/')); ?>" class="lab-btn-primary" style="width:100%;box-sizing:border-box;">
                                Buka <?php echo esc_html($b['nama']); ?> &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Widget Interaktif: Skrining Kondisi Diri Mandiri -->
    <section id="skrining-mandiri" class="lab-section" style="padding-top:10px;">
        <div class="lab-wrap">
            <div class="lab-skrining-box">
                <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:flex-end;gap:12px;margin-bottom:18px;">
                    <div>
                        <span class="lab-eyebrow">02 — Asesmen Mandiri Interaktif</span>
                        <h2 style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:var(--lab-navy);margin:0;">Cek Kondisi Diri &amp; Stres Akademik</h2>
                        <p style="font-size:13.5px;color:var(--lab-muted);margin:4px 0 0;">Refleksi singkat 6 pertanyaan (bersifat privat di perangkatmu, tidak disimpan ke server).</p>
                    </div>
                    <span class="lab-pill">Hasil Instan</span>
                </div>

                <form id="labSkriningForm">
                    <?php
                    $questions = [
                        'Merasa kewalahan atau sulit fokus dengan tugas kuliah / skripsi dalam 2 minggu terakhir?',
                        'Mengalami kesulitan tidur atau cepat lelah secara emosional?',
                        'Merasa cemas berlebih saat memikirkan masa depan atau tuntutan akademik?',
                        'Sulit menemukan teman cerita yang aman saat menghadapi masalah pribadi?',
                        'Kehilangan minat pada kegiatan organisasi, hobi, atau aktivitas kampus?',
                        'Merasa membutuhkan pendampingan atau sudut pandang baru dari konselor?',
                    ];
                    foreach ($questions as $qi => $qt) :
                    ?>
                        <div class="lab-q-row">
                            <div style="flex:1 1 360px;font-size:13.5px;font-weight:600;color:var(--lab-ink);">
                                <?php echo ($qi + 1) . '. ' . esc_html($qt); ?>
                            </div>
                            <div class="lab-q-opts">
                                <label><input type="radio" name="q<?php echo $qi; ?>" value="0" required><span>Tidak Pernah</span></label>
                                <label><input type="radio" name="q<?php echo $qi; ?>" value="1"><span>Kadang</span></label>
                                <label><input type="radio" name="q<?php echo $qi; ?>" value="2"><span>Sering</span></label>
                                <label><input type="radio" name="q<?php echo $qi; ?>" value="3"><span>Hampir Selalu</span></label>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;align-items:center;">
                        <button type="submit" class="lab-btn-gold">Lihat Hasil Skrining</button>
                        <button type="button" onclick="document.getElementById('labSkriningForm').reset();document.getElementById('labSkriningResult').style.display='none';" style="background:#F1F5F9;border:1px solid #CBD5E1;padding:10px 16px;border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;">Reset Jawaban</button>
                    </div>
                </form>

                <div id="labSkriningResult" style="display:none;margin-top:20px;padding:18px 20px;border-radius:14px;border:1px solid #CBD5E1;background:#F8FAFC;"></div>
            </div>
        </div>
    </section>

    <!-- Agenda & Kegiatan Lab BK -->
    <?php if (!empty($kegiatan_list)) : ?>
    <section class="lab-section" style="padding-top:10px;">
        <div class="lab-wrap">
            <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:12px;margin-bottom:22px;">
                <div>
                    <span class="lab-eyebrow">03 — Warta &amp; Agenda Laboratorium</span>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:26px;color:var(--lab-navy);margin:0;">Kegiatan &amp; Praktikum Terdekat</h2>
                </div>
                <a href="<?php echo esc_url(home_url('/lab-bk/kegiatan/')); ?>" class="lab-btn-primary">Semua Kegiatan &rarr;</a>
            </div>
            <div class="lab-grid-3">
                <?php foreach (array_slice($kegiatan_list, 0, 3) as $kg) : ?>
                    <div class="lab-card">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;">
                                <span class="lab-pill"><?php echo esc_html($kg['status'] ?? 'Aktif'); ?></span>
                                <span style="font-size:12px;color:var(--lab-muted);"><?php echo esc_html($kg['periode'] ?? ''); ?></span>
                            </div>
                            <h3 style="margin:0 0 8px;font-size:17px;color:var(--lab-navy);"><?php echo esc_html($kg['judul'] ?? ''); ?></h3>
                            <p style="font-size:13px;color:var(--lab-muted);margin:0 0 12px;"><?php echo esc_html($kg['deskripsi'] ?? ''); ?></p>
                        </div>
                        <div style="font-size:12px;font-weight:700;color:var(--lab-emerald);">
                            👥 Sasaran: <?php echo esc_html($kg['peserta'] ?? ''); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

<?php elseif ($active_view === 'inventaris') : ?>
    <!-- ==================== INVENTARIS ALAT TES LAB BK ==================== -->
    <section class="lab-hero" style="padding:38px 0 44px;">
        <div class="lab-wrap">
            <div style="font-size:12.5px;color:rgba(255,255,255,0.75);margin-bottom:10px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Beranda Lab BK</a>
                <span> / </span>
                <span>Inventaris Alat Tes</span>
            </div>
            <span class="lab-badge">🧪 Katalog Instrumen &amp; Fasilitas Lab</span>
            <h1>Inventaris Alat Tes Psikologi &amp; Non-Tes</h1>
            <p style="margin-bottom:0;">Daftar instrumen asesmen dan perlengkapan praktikum Laboratorium BK FIP UNM. Mahasiswa dapat langsung mengajukan peminjaman alat melalui formulir di bawah.</p>
        </div>
    </section>

    <section class="lab-section">
        <div class="lab-wrap">
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:20px;">
                <input type="search" id="labInvSearch" placeholder="Cari nama alat tes atau instrumen..." style="flex:1 1 280px;max-width:420px;padding:10px 14px;border:1px solid var(--lab-border);border-radius:10px;font-size:13.5px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/bidang-asesmen/')); ?>" class="lab-btn-primary">← Kembali ke Bidang Asesmen</a>
            </div>

            <?php if (!empty($inventaris_items)) : ?>
                <div class="lab-grid-3" id="labInvGrid">
                    <?php foreach ($inventaris_items as $inv) :
                        $kode   = get_post_meta($inv->ID, 'kode_alat', true) ?: ('INV-' . $inv->ID);
                        $jumlah = get_post_meta($inv->ID, 'jumlah_unit', true) ?: 'Tersedia';
                        $kond   = get_post_meta($inv->ID, 'kondisi', true) ?: 'Baik';
                    ?>
                        <div class="lab-card lab-inv-card" data-search="<?php echo esc_attr(strtolower($inv->post_title . ' ' . $kode . ' ' . $inv->post_excerpt)); ?>">
                            <div>
                                <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;">
                                    <span class="lab-pill"><?php echo esc_html($kode); ?></span>
                                    <span class="lab-pill lab-pill-gold"><?php echo esc_html($kond); ?></span>
                                </div>
                                <h3 style="margin:0 0 8px;font-size:17px;color:var(--lab-navy);"><?php echo esc_html($inv->post_title); ?></h3>
                                <p style="font-size:13px;color:var(--lab-muted);margin:0 0 12px;">
                                    <?php echo esc_html(wp_strip_all_tags($inv->post_excerpt ?: wp_trim_words($inv->post_content, 24))); ?>
                                </p>
                            </div>
                            <div>
                                <div style="font-size:12px;color:var(--lab-emerald);font-weight:700;margin-bottom:10px;">📦 Ketersediaan: <?php echo esc_html($jumlah); ?></div>
                                <button type="button"
                                    class="lab-btn-primary lab-open-modal"
                                    data-bidang="Bidang Asesmen"
                                    data-program="Peminjaman Alat Tes: <?php echo esc_attr($inv->post_title); ?>"
                                    style="width:100%;box-sizing:border-box;">
                                    Ajukan Peminjaman Alat
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <div class="lab-card">
                    <p style="margin:0;color:var(--lab-muted);">Daftar alat tes sedang disinkronkan oleh pengelola laboratorium. Anda tetap dapat mengajukan peminjaman alat melalui formulir Bidang Asesmen.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

<?php elseif ($active_view === 'kegiatan') : ?>
    <!-- ==================== KEGIATAN & PRAKTIKUM LAB BK ==================== -->
    <section class="lab-hero" style="padding:38px 0 44px;">
        <div class="lab-wrap">
            <div style="font-size:12.5px;color:rgba(255,255,255,0.75);margin-bottom:10px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Beranda Lab BK</a>
                <span> / </span>
                <span>Kegiatan &amp; Praktikum</span>
            </div>
            <span class="lab-badge">🗓️ Agenda Resmi Laboratorium BK</span>
            <h1>Kegiatan &amp; Praktikum Mahasiswa Lab BK</h1>
            <p style="margin-bottom:0;">Jadwal praktikum, workshop, pelatihan, dan agenda aktif di lingkungan Laboratorium Bimbingan dan Konseling FIP UNM.</p>
        </div>
    </section>

    <section class="lab-section">
        <div class="lab-wrap">
            <div class="lab-grid-3">
                <?php foreach ($kegiatan_list as $kg) :
                    $dok_url = !empty($kg['dok_url']) && function_exists('ppb_clean_document_url')
                        ? ppb_clean_document_url($kg['dok_url'])
                        : trim((string) ($kg['dok_url'] ?? ''));
                ?>
                    <div class="lab-card">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;">
                                <span class="lab-pill"><?php echo esc_html($kg['status'] ?? 'Aktif'); ?></span>
                                <span style="font-size:12px;color:var(--lab-muted);"><?php echo esc_html($kg['periode'] ?? ''); ?></span>
                            </div>
                            <h3 style="margin:0 0 8px;font-size:18px;color:var(--lab-navy);"><?php echo esc_html($kg['judul'] ?? ''); ?></h3>
                            <p style="font-size:13.5px;color:var(--lab-muted);margin:0 0 12px;"><?php echo esc_html($kg['deskripsi'] ?? ''); ?></p>
                        </div>
                        <div>
                            <div style="font-size:12px;color:var(--lab-emerald);font-weight:700;margin-bottom:10px;">👥 Sasaran: <?php echo esc_html($kg['peserta'] ?? ''); ?></div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                <button type="button" class="lab-btn-primary lab-open-modal" data-bidang="Kegiatan Lab BK" data-program="<?php echo esc_attr($kg['judul'] ?? 'Kegiatan Lab'); ?>" style="flex:1;">Daftar / Ikuti</button>
                                <?php if ($dok_url !== '') : ?>
                                    <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-back">📄 Panduan ↗</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php elseif ($active_view === 'lomba') : ?>
    <!-- ==================== LOMBA NON-AKADEMIK LAB BK ==================== -->
    <section class="lab-hero" style="padding:38px 0 44px;">
        <div class="lab-wrap">
            <div style="font-size:12.5px;color:rgba(255,255,255,0.75);margin-bottom:10px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Beranda Lab BK</a>
                <span> / </span>
                <a href="<?php echo esc_url(home_url('/lab-bk/bidang-ekstrakurikuler/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Ekstrakurikuler</a>
                <span> / </span>
                <span>Info Lomba</span>
            </div>
            <span class="lab-badge">🏆 Prestasi &amp; Kompetisi Non-Akademik</span>
            <h1>Informasi Lomba Non-Akademik Mahasiswa</h1>
            <p style="margin-bottom:0;">Kompetisi kreativitas, seni, media edukasi, simulasi konseling, dan minat bakat di bawah pembinaan Bidang Ekstrakurikuler Lab BK.</p>
        </div>
    </section>

    <section class="lab-section">
        <div class="lab-wrap">
            <div class="lab-grid-3">
                <?php foreach ($lomba_list as $lb) :
                    $dok_url = !empty($lb['dok_url']) && function_exists('ppb_clean_document_url')
                        ? ppb_clean_document_url($lb['dok_url'])
                        : trim((string) ($lb['dok_url'] ?? ''));
                ?>
                    <div class="lab-card">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;">
                                <span class="lab-pill lab-pill-gold"><?php echo esc_html($lb['kategori'] ?? 'Nasional'); ?></span>
                                <span style="font-size:12px;color:var(--lab-muted);"><?php echo esc_html($lb['lingkup'] ?? ''); ?></span>
                            </div>
                            <h3 style="margin:0 0 8px;font-size:18px;color:var(--lab-navy);"><?php echo esc_html($lb['nama'] ?? ''); ?></h3>
                            <p style="font-size:13px;color:var(--lab-muted);margin:0 0 12px;">
                                <strong>Penyelenggara:</strong> <?php echo esc_html(html_entity_decode((string) ($lb['penyelenggara'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                            </p>
                        </div>
                        <div>
                            <div style="font-size:12.5px;font-weight:700;color:var(--lab-emerald);background:#F8FAFC;padding:8px 12px;border-radius:8px;border:1px solid var(--lab-border);margin-bottom:10px;">
                                🏆 <?php echo esc_html(html_entity_decode((string) ($lb['prestasi'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                            </div>
                            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                <button type="button" class="lab-btn-primary lab-open-modal" data-bidang="Bidang Ekstrakurikuler" data-program="Delegasi Lomba: <?php echo esc_attr($lb['nama'] ?? ''); ?>" style="flex:1;">Ajukan Delegasi</button>
                                <?php if ($dok_url !== '') : ?>
                                    <a href="<?php echo esc_url($dok_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-back">📄 Juknis ↗</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

<?php else : ?>
    <!-- ==================== DETAIL BIDANG LAB BK ==================== -->
    <section class="lab-hero" style="padding:40px 0 46px;">
        <div class="lab-wrap">
            <div style="font-size:12.5px;color:rgba(255,255,255,0.75);margin-bottom:10px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Beranda Lab BK</a>
                <span> / </span>
                <span><?php echo esc_html($cur_bidang['nama']); ?></span>
            </div>
            <span class="lab-badge"><?php echo esc_html($cur_bidang['icon'] . ' ' . $cur_bidang['nama']); ?></span>
            <h1><?php echo esc_html($cur_bidang['tagline']); ?></h1>
            <p style="margin-bottom:18px;"><?php echo esc_html($cur_bidang['deskripsi']); ?></p>
            <div style="display:flex;flex-wrap:wrap;gap:10px;">
                <span style="background:rgba(255,255,255,0.12);padding:8px 14px;border-radius:10px;font-size:12.5px;">
                    👤 <strong>Koordinator:</strong> <?php echo esc_html($cur_bidang['koordinator']); ?>
                </span>
                <span style="background:rgba(255,255,255,0.12);padding:8px 14px;border-radius:10px;font-size:12.5px;">
                    🕒 <?php echo esc_html($cur_bidang['jadwal']); ?>
                </span>
                <span style="background:rgba(255,255,255,0.12);padding:8px 14px;border-radius:10px;font-size:12.5px;">
                    📍 <?php echo esc_html($cur_bidang['lokasi']); ?>
                </span>
            </div>
        </div>
    </section>

    <section class="lab-section">
        <div class="lab-wrap">
            <?php
            $programs = $cur_bidang['program'] ?? [];
            $categories = [];
            foreach ($programs as $pr) {
                $cat = trim((string) ($pr['kategori'] ?? ''));
                if ($cat !== '' && !in_array($cat, $categories, true)) {
                    $categories[] = $cat;
                }
            }
            ?>
            <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:12px;margin-bottom:18px;">
                <div>
                    <span class="lab-eyebrow">Layanan &amp; Program Kerja</span>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:var(--lab-navy);margin:0;">Layanan Interaktif &amp; Wadah Kegiatan</h2>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;">
                    <?php if ($active_view === 'konseling') : ?>
                        <a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-gold">Daftar Konseling ↗</a>
                    <?php elseif ($active_view === 'asesmen') : ?>
                        <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="lab-btn-primary">Buka Katalog Alat Tes →</a>
                    <?php elseif ($active_view === 'ekstrakurikuler') : ?>
                        <a href="<?php echo esc_url(home_url('/lab-bk/lomba/')); ?>" class="lab-btn-primary">Lihat Info Lomba →</a>
                    <?php endif; ?>
                    <a href="<?php echo esc_url(home_url('/lab-bk/kegiatan/')); ?>" style="background:#ffffff;border:1px solid var(--lab-border);color:var(--lab-navy);font-size:12px;font-weight:700;padding:8px 13px;border-radius:9px;text-decoration:none;">
                        🗓️ Agenda Lab BK
                    </a>
                </div>
            </div>

            <?php if (count($categories) > 1) : ?>
                <div class="lab-filter-bar" id="labProgramFilter">
                    <button type="button" class="lab-filter-btn is-active" data-cat="all">Semua (<?php echo count($programs); ?>)</button>
                    <?php foreach ($categories as $cat) : ?>
                        <button type="button" class="lab-filter-btn" data-cat="<?php echo esc_attr(strtolower($cat)); ?>"><?php echo esc_html($cat); ?></button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="lab-grid-3" id="labProgramGrid">
                <?php foreach ($programs as $pr) :
                    $cta_url   = trim((string) ($pr['cta_url'] ?? ''));
                    $cta_label = !empty($pr['cta_label']) ? $pr['cta_label'] : 'Ajukan / Daftar';
                    $is_ext    = $cta_url !== '' && preg_match('/^https?:\/\//i', $cta_url) && strpos($cta_url, home_url()) === false;

                    // Pastikan semua rute internal tetap di dalam ekosistem /lab-bk/*
                    if ($cta_url === '/laboratorium/lomba/') $cta_url = '/lab-bk/lomba/';
                    if ($cta_url === '/laboratorium/kegiatan/') $cta_url = '/lab-bk/kegiatan/';
                    $use_modal = ($cta_url === '' || $cta_url === '/e-library/');
                    $href_final = ($cta_url !== '' && strpos($cta_url, '/') === 0) ? home_url($cta_url) : $cta_url;
                    $cat_attr  = strtolower(trim((string) ($pr['kategori'] ?? '')));
                ?>
                    <div class="lab-card lab-program-card" data-cat="<?php echo esc_attr($cat_attr); ?>">
                        <div>
                            <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;margin-bottom:10px;flex-wrap:wrap;">
                                <span class="lab-pill"><?php echo esc_html($pr['kategori'] ?? 'Layanan'); ?></span>
                                <span class="lab-pill lab-pill-gold"><?php echo esc_html($pr['status'] ?? 'Aktif'); ?></span>
                            </div>
                            <h3 style="margin:0 0 8px;font-size:18px;color:var(--lab-navy);"><?php echo esc_html($pr['judul'] ?? ''); ?></h3>
                            <p style="font-size:13.5px;color:var(--lab-muted);margin:0 0 14px;"><?php echo esc_html($pr['deskripsi'] ?? ''); ?></p>
                        </div>
                        <div>
                            <div style="font-size:12px;color:var(--lab-muted);margin-bottom:14px;">
                                <?php if (!empty($pr['sasaran'])) : ?>
                                    <div>👥 <strong>Sasaran:</strong> <?php echo esc_html($pr['sasaran']); ?></div>
                                <?php endif; ?>
                                <?php if (!empty($pr['jadwal'])) : ?>
                                    <div>🗓️ <strong>Jadwal:</strong> <?php echo esc_html($pr['jadwal']); ?></div>
                                <?php endif; ?>
                            </div>
                            <?php if (!$use_modal && $cta_url !== '') : ?>
                                <a href="<?php echo esc_url($href_final); ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener noreferrer"' : ''; ?> class="lab-btn-primary" style="width:100%;box-sizing:border-box;">
                                    <?php echo esc_html($cta_label); ?>
                                </a>
                            <?php else : ?>
                                <button type="button"
                                    class="lab-btn-primary lab-open-modal"
                                    data-bidang="<?php echo esc_attr($cur_bidang['nama']); ?>"
                                    data-program="<?php echo esc_attr($pr['judul'] ?? ''); ?>"
                                    style="width:100%;box-sizing:border-box;">
                                    <?php echo esc_html($use_modal && $cta_url === '/e-library/' ? 'Ajukan Akses Modul' : $cta_label); ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Khusus Bidang Ekstrakurikuler: Info Lomba Non-Akademik -->
            <?php if ($active_view === 'ekstrakurikuler' && !empty($lomba_list)) : ?>
                <div style="margin-top:44px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;margin-bottom:18px;">
                        <div>
                            <span class="lab-eyebrow">Kompetisi &amp; Minat Bakat</span>
                            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:var(--lab-navy);margin:0;">Lomba Non-Akademik Mahasiswa</h2>
                        </div>
                        <a href="<?php echo esc_url(home_url('/lab-bk/lomba/')); ?>" class="lab-btn-primary">Semua Info Lomba &rarr;</a>
                    </div>
                    <div class="lab-grid-3">
                        <?php foreach (array_slice($lomba_list, 0, 3) as $lb) : ?>
                            <div class="lab-card">
                                <div>
                                    <span class="lab-pill lab-pill-gold"><?php echo esc_html($lb['kategori'] ?? 'Kompetisi'); ?></span>
                                    <h3 style="margin:10px 0 6px;font-size:17px;color:var(--lab-navy);"><?php echo esc_html($lb['nama'] ?? ''); ?></h3>
                                    <p style="font-size:13px;color:var(--lab-muted);margin:0 0 10px;">Penyelenggara: <?php echo esc_html(html_entity_decode((string) ($lb['penyelenggara'] ?? ''), ENT_QUOTES, 'UTF-8')); ?></p>
                                </div>
                                <div style="font-size:12.5px;font-weight:700;color:var(--lab-emerald);background:#F8FAFC;padding:8px 12px;border-radius:8px;border:1px solid var(--lab-border);">
                                    🏆 <?php echo esc_html(html_entity_decode((string) ($lb['prestasi'] ?? ''), ENT_QUOTES, 'UTF-8')); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Alur / SOP Layanan -->
            <?php if (!empty($cur_bidang['sop_alur'])) : ?>
                <div style="background:#ffffff;border:1px solid var(--lab-border);border-radius:20px;padding:28px;margin-top:40px;">
                    <span class="lab-eyebrow">Prosedur Standar</span>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:var(--lab-navy);margin:0 0 18px;">Alur &amp; Prosedur Layanan</h2>
                    <div class="lab-grid-4">
                        <?php foreach ($cur_bidang['sop_alur'] as $sidx => $step) : ?>
                            <div style="background:#F8FAFC;border:1px solid var(--lab-border);border-radius:14px;padding:18px;">
                                <div style="width:30px;height:30px;border-radius:99px;background:var(--lab-navy);color:var(--lab-gold);font-size:13px;font-weight:800;display:flex;align-items:center;justify-content:center;margin-bottom:10px;">
                                    <?php echo (int) ($sidx + 1); ?>
                                </div>
                                <p style="font-size:13px;color:var(--lab-ink);margin:0;"><?php echo esc_html($step); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<!-- Modal Form Pendaftaran / Booking Terpadu -->
<div id="labRegModal" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(11,23,48,0.68);align-items:center;justify-content:center;padding:16px;">
    <div style="background:#ffffff;border-radius:20px;max-width:520px;width:100%;padding:28px;box-shadow:0 24px 60px rgba(0,0,0,0.28);position:relative;">
        <button type="button" id="labRegClose" aria-label="Tutup" style="position:absolute;top:14px;right:14px;background:#F1F5F9;border:none;width:34px;height:34px;border-radius:99px;font-size:18px;cursor:pointer;color:#475569;">&times;</button>
        <div style="font-size:11px;font-weight:800;color:var(--lab-navy);text-transform:uppercase;letter-spacing:0.06em;margin-bottom:4px;">Formulir Terpadu Laboratorium BK</div>
        <h3 id="labRegTitle" style="font-family:'Fraunces',Georgia,serif;font-size:21px;color:var(--lab-navy);margin:0 0 14px;">Pendaftaran Layanan</h3>

        <form id="labRegForm">
            <input type="hidden" name="action" value="ppb_submit_lab_pendaftaran">
            <input type="hidden" name="nonce" value="<?php echo esc_attr($reg_nonce); ?>">
            <input type="hidden" name="bidang" id="labRegBidang" value="">
            <input type="hidden" name="program" id="labRegProgram" value="">

            <div style="margin-bottom:10px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">Nama Lengkap *</label>
                <input type="text" name="nama" required placeholder="Masukkan nama lengkap" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;font-size:13.5px;">
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:10px;">
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">NIM / Status *</label>
                    <input type="text" name="nim" required placeholder="Contoh: 23040450..." style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;font-size:13.5px;">
                </div>
                <div>
                    <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">No. HP / Email *</label>
                    <input type="text" name="kontak" required placeholder="08xxxx / email" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;font-size:13.5px;">
                </div>
            </div>
            <?php if (!empty($inventaris_items) && ($active_view === 'asesmen' || $active_view === 'inventaris')) : ?>
                <div style="margin-bottom:10px;">
                    <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">Pilih Alat Tes (Opsional)</label>
                    <select id="labAlatSelect" style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;font-size:13.5px;">
                        <option value="">-- Pilih Instrumen / Alat Tes --</option>
                        <?php foreach ($inventaris_items as $inv) : ?>
                            <option value="<?php echo esc_attr($inv->post_title); ?>"><?php echo esc_html($inv->post_title); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            <?php endif; ?>
            <div style="margin-bottom:14px;">
                <label style="display:block;font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">Catatan / Pilihan Jadwal / Minat</label>
                <textarea name="catatan" id="labRegCatatan" rows="3" placeholder="Tuliskan jadwal yang diinginkan, tujuan penggunaan, atau minat klub..." style="width:100%;box-sizing:border-box;padding:10px 12px;border:1px solid #CBD5E1;border-radius:9px;font-size:13.5px;"></textarea>
            </div>
            <div id="labRegMsg" style="display:none;padding:10px 12px;border-radius:9px;font-size:13px;margin-bottom:12px;"></div>
            <button type="submit" id="labRegSubmit" class="lab-btn-primary" style="width:100%;padding:12px;">
                Kirim Pengajuan Sekarang
            </button>
        </form>
    </div>
</div>

<!-- Footer Khusus Lab BK -->
<footer class="lab-footer">
    <div class="lab-wrap">
        <div class="lab-footer-grid">
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <img src="<?php echo esc_url($logo_url); ?>" alt="Logo Lab BK" style="width:44px;height:44px;object-fit:contain;">
                    <div>
                        <strong style="color:#ffffff;font-size:16px;display:block;">Laboratorium BK FIP UNM</strong>
                        <span style="font-size:12px;color:var(--lab-gold);">Universitas Negeri Makassar</span>
                    </div>
                </div>
                <p style="font-size:13px;margin:0 0 10px;max-width:360px;">
                    Ruang bertumbuh, belajar bersama, layanan bimbingan konseling, asesmen psikologis, pelatihan, media, serta wadah kreativitas non-akademik mahasiswa BK.
                </p>
                <div style="font-size:12.5px;">✉️ admin@labbk.unm.ac.id &bull; 📸 @labbk.unm</div>
            </div>
            <div>
                <strong style="color:#ffffff;display:block;margin-bottom:10px;font-size:14px;">5 Bidang Laboratorium</strong>
                <div style="display:grid;gap:7px;">
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-konseling/')); ?>">01. Bidang Konseling</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-asesmen/')); ?>">02. Bidang Asesmen</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-training/')); ?>">03. Bidang Training &amp; Development</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-media/')); ?>">04. Bidang Media</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-ekstrakurikuler/')); ?>">05. Bidang Ekstrakurikuler</a>
                </div>
            </div>
            <div>
                <strong style="color:#ffffff;display:block;margin-bottom:10px;font-size:14px;">Akses Cepat Lab BK</strong>
                <div style="display:grid;gap:7px;">
                    <a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer">Pendaftaran Konseling ↗</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>">Inventaris Alat Tes</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/kegiatan/')); ?>">Kegiatan Lab BK</a>
                    <a href="<?php echo esc_url(home_url('/lab-bk/lomba/')); ?>">Lomba Non-Akademik</a>
                    <a href="<?php echo esc_url(home_url('/')); ?>" style="color:var(--lab-gold);font-weight:700;">&larr; Kembali ke Website Jurusan</a>
                </div>
            </div>
        </div>
        <div style="border-top:1px solid rgba(255,255,255,0.12);padding-top:16px;font-size:12px;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
            <span>&copy; <?php echo date('Y'); ?> Laboratorium Bimbingan &amp; Konseling FIP UNM</span>
            <span>Portal Resmi Terpadu Laboratorium BK</span>
        </div>
    </div>
</footer>

<script>
(function(){
    // 1. Skrining Mandiri Interaktif
    var sForm = document.getElementById('labSkriningForm');
    if (sForm) {
        sForm.addEventListener('submit', function(e){
            e.preventDefault();
            var total = 0;
            for (var i = 0; i < 6; i++) {
                var checked = sForm.querySelector('input[name="q' + i + '"]:checked');
                if (checked) total += parseInt(checked.value, 10);
            }
            var res = document.getElementById('labSkriningResult');
            var level = '', color = '', bg = '', desc = '', cta = '';
            if (total <= 5) {
                level = 'Kondisi Relatif Stabil';
                color = '#166534'; bg = '#DCFCE7';
                desc = 'Kamu berada dalam kondisi emosional dan akademik yang cukup terjaga. Terus pertahankan rutinitas positif atau eksplor minatmu di Bidang Ekstrakurikuler & Training Lab BK.';
            } else if (total <= 11) {
                level = 'Butuh Ruang Cerita & Refleksi';
                color = '#92400E'; bg = '#FEF3C7';
                desc = 'Ada beberapa beban pikiran atau kelelahan akademik yang mulai terasa. Berbagi cerita dengan Konselor Sebaya (Peer Support) dapat membantumu merasa lebih lega.';
                cta = '<a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-primary" style="margin-top:10px;">Daftar Konseling Sebaya ↗</a>';
            } else {
                level = 'Disarankan Pendampingan Konseling';
                color = '#991B1B'; bg = '#FEE2E2';
                desc = 'Skor menunjukkan tekanan emosional atau akademik yang cukup tinggi. Jangan dipendam sendiri — segera jadwalkan sesi privat bersama Konselor Lab BK.';
                cta = '<a href="<?php echo esc_url($peersupport_url); ?>" target="_blank" rel="noopener noreferrer" class="lab-btn-primary" style="margin-top:10px;">Jadwalkan Konseling Sekarang ↗</a>';
            }
            res.style.display = 'block';
            res.style.background = bg;
            res.style.color = color;
            res.innerHTML = '<strong style="font-size:16px;display:block;margin-bottom:4px;">Skor: ' + total + '/18 — ' + level + '</strong><p style="margin:0;font-size:13.5px;">' + desc + '</p>' + cta;
        });
    }

    // 2. Filter Program / Klub
    var fBar = document.getElementById('labProgramFilter');
    if (fBar) {
        fBar.addEventListener('click', function(e){
            var btn = e.target.closest('.lab-filter-btn');
            if (!btn) return;
            fBar.querySelectorAll('.lab-filter-btn').forEach(function(b){ b.classList.remove('is-active'); });
            btn.classList.add('is-active');
            var cat = btn.getAttribute('data-cat');
            document.querySelectorAll('.lab-program-card').forEach(function(card){
                card.style.display = (cat === 'all' || card.getAttribute('data-cat') === cat) ? 'flex' : 'none';
            });
        });
    }

    // 3. Pencarian Inventaris Alat Tes
    var invSearch = document.getElementById('labInvSearch');
    if (invSearch) {
        invSearch.addEventListener('input', function(){
            var q = (this.value || '').trim().toLowerCase();
            document.querySelectorAll('.lab-inv-card').forEach(function(card){
                var blob = card.getAttribute('data-search') || '';
                card.style.display = (!q || blob.indexOf(q) !== -1) ? 'flex' : 'none';
            });
        });
    }

    // 4. Modal Pendaftaran Terpadu (AJAX)
    var modal = document.getElementById('labRegModal');
    var form  = document.getElementById('labRegForm');
    var msg   = document.getElementById('labRegMsg');
    var ajaxUrl = <?php echo wp_json_encode($ajax_url); ?>;

    document.addEventListener('click', function(e){
        var openBtn = e.target.closest('.lab-open-modal');
        if (openBtn && modal) {
            document.getElementById('labRegTitle').textContent = openBtn.getAttribute('data-program') || 'Pendaftaran Layanan';
            document.getElementById('labRegBidang').value = openBtn.getAttribute('data-bidang') || '';
            document.getElementById('labRegProgram').value = openBtn.getAttribute('data-program') || '';
            msg.style.display = 'none';
            modal.style.display = 'flex';
            return;
        }
        if (e.target.id === 'labRegClose' || e.target === modal) {
            modal.style.display = 'none';
        }
    });

    var alatSel = document.getElementById('labAlatSelect');
    if (alatSel) {
        alatSel.addEventListener('change', function(){
            if (!this.value) return;
            var cat = document.getElementById('labRegCatatan');
            if (cat.value.indexOf(this.value) === -1) {
                cat.value = 'Alat Tes: ' + this.value + (cat.value ? '\n' + cat.value : '');
            }
        });
    }

    if (form) {
        form.addEventListener('submit', function(e){
            e.preventDefault();
            var btn = document.getElementById('labRegSubmit');
            btn.disabled = true;
            btn.textContent = 'Mengirim...';
            fetch(ajaxUrl, { method: 'POST', body: new FormData(form) })
                .then(function(r){ return r.json(); })
                .then(function(res){
                    msg.style.display = 'block';
                    if (res && res.success) {
                        msg.style.background = '#DCFCE7';
                        msg.style.color = '#166534';
                        msg.textContent = (res.data && res.data.message) ? res.data.message : 'Pengajuan berhasil dikirim!';
                        form.reset();
                    } else {
                        msg.style.background = '#FEE2E2';
                        msg.style.color = '#991B1B';
                        msg.textContent = (res && res.data && res.data.message) ? res.data.message : 'Gagal mengirim pengajuan.';
                    }
                })
                .catch(function(){
                    msg.style.display = 'block';
                    msg.style.background = '#FEE2E2';
                    msg.style.color = '#991B1B';
                    msg.textContent = 'Terjadi kesalahan jaringan. Silakan coba lagi.';
                })
                .finally(function(){
                    btn.disabled = false;
                    btn.textContent = 'Kirim Pengajuan Sekarang';
                });
        });
    }
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
