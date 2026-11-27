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
    'lab-bk/tentang-kami'           => 'tentang-kami',
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

// Helper ambil foto & URL profil dosen langsung dari Direktori Dosen
$dosen_directory = function_exists('bk_fip_load_dosen_data') ? bk_fip_load_dosen_data() : [];
$find_dosen_card = function ($query_name) use ($dosen_directory) {
    $clean_q = strtolower(trim(preg_replace('/^(prof\.|dr\.|m\.pd|s\.pd|s\.psi|m\.psi|psikolog|gr\.?|,|\s)+/i', ' ', (string) $query_name)));
    $tokens  = array_values(array_filter(explode(' ', $clean_q), function ($t) {
        $t = preg_replace('/[^a-z]/', '', $t);
        return strlen($t) >= 3 && !in_array($t, ['spd', 'mpd', 'spsi', 'mpsi', 'kons', 'prof', 'psikolog'], true);
    }));
    $matched = null;
    foreach ($dosen_directory as $d) {
        $nama = strtolower((string) ($d['nama'] ?? ''));
        if ($nama === '') continue;
        if (strpos($nama, strtolower((string) $query_name)) !== false) {
            $matched = $d;
            break;
        }
        if (!empty($tokens)) {
            $hits = 0;
            foreach ($tokens as $tk) {
                $tk_clean = preg_replace('/[^a-z]/', '', $tk);
                if ($tk_clean !== '' && strpos($nama, $tk_clean) !== false) $hits++;
            }
            if ($hits >= 1 && (count($tokens) === 1 || $hits >= 2 || strpos($nama, preg_replace('/[^a-z]/', '', $tokens[0])) !== false)) {
                $matched = $d;
                break;
            }
        }
    }
    $foto = '';
    $url  = '';
    if ($matched) {
        $raw_foto = $matched['foto_link'] ?? '';
        if (function_exists('bk_fip_resolve_dosen_foto_url')) {
            $foto = bk_fip_resolve_dosen_foto_url($raw_foto);
        } elseif ($raw_foto !== '') {
            $foto = (strpos($raw_foto, 'http') === 0 || strpos($raw_foto, '/') === 0)
                ? $raw_foto
                : trailingslashit(get_template_directory_uri()) . ltrim($raw_foto, '/');
        }
        if (!empty($matched['slug'])) {
            $url = home_url('/profil/dosen/' . $matched['slug'] . '/');
        }
    }
    $initials = '';
    $parts = preg_split('/\s+/', trim((string) $query_name));
    foreach ($parts as $pt) {
        $pt_clean = preg_replace('/[^a-zA-Z]/', '', $pt);
        if ($pt_clean !== '' && !in_array(strtolower($pt_clean), ['prof', 'dr', 'mpd', 'spd', 'spsi', 'mpsi', 'kons', 'psikolog', 'gr'], true)) {
            $initials .= strtoupper(substr($pt_clean, 0, 1));
            if (strlen($initials) >= 2) break;
        }
    }
    return [
        'nama'        => $query_name,
        'foto'        => $foto,
        'foto_posisi' => !empty($matched['foto_posisi']) ? $matched['foto_posisi'] : '50% 15%',
        'url'         => $url,
        'initials'    => $initials ?: 'BK',
    ];
};

$unit_reg = function_exists('ppb_get_unit_registry') ? ppb_get_unit_registry() : [];
$jur_reg  = $unit_reg['jurusan'] ?? [];
$kajur_nama = !empty($jur_reg['pimpinan_nama']) ? $jur_reg['pimpinan_nama'] : 'Dr. Suciani Latif, S.Pd., M.Pd';
$sekjur_nama = (!empty($jur_reg['sekretaris_nama']) && stripos($jur_reg['sekretaris_nama'], 'Sekretaris Jurusan') === false)
    ? $jur_reg['sekretaris_nama']
    : 'Akhmad Harum, S.Pd., M.Pd';
$kalab_nama = (!empty($jur_reg['lab_kepala']) && stripos($jur_reg['lab_kepala'], 'Kepala Laboratorium') === false)
    ? $jur_reg['lab_kepala']
    : 'M. Amirullah, S.Pd., M.Pd';

$lab_koordinator_struktur = [
    ['bidang' => 'ekstrakurikuler', 'label' => 'Ekstrakurikuler Non-Akademik', 'nama' => 'Maslina, S.Pd., M.Pd'],
    ['bidang' => 'ekstrakurikuler', 'label' => 'Ekstrakurikuler Akademik',     'nama' => 'Inayah Ridhayanti Qorimah, S.Pd., M.Pd., Gr.'],
    ['bidang' => 'konseling',       'label' => 'Bimbingan & Konseling',        'nama' => 'Qawiyyan Fitri, S.Pd., M.Pd'],
    ['bidang' => 'media',           'label' => 'Media & Publikasi',            'nama' => 'Dwi Endrasto Wibowo, S.Pd., M.Pd'],
    ['bidang' => 'training',        'label' => 'Training & Development',       'nama' => 'Abid Raisardhi Abdullah, S.Psi., M.Psi., Psikolog'],
    ['bidang' => 'asesmen',         'label' => 'Asesmen & Psikologi',          'nama' => 'Nurfaidah Ardis, S.Psi., M.Psi., Psikolog'],
];

$lab_peer_pembina = [
    'Sabrina Putri Azam',
    'Arbain Fajar A. Hamid',
    'Nurul Asikin, S.Pd',
];

$lab_peer_inti = [
    ['jabatan' => 'Koordinator Peer Collaborator',       'nama' => 'Wahyu Kurnia Saputra'],
    ['jabatan' => 'Wakil Koordinator Peer Collaborator', 'nama' => 'Elinana'],
    ['jabatan' => 'Sekretaris Peer Collaborator',        'nama' => 'Sri Wulandari'],
    ['jabatan' => 'Bendahara Peer Collaborator',         'nama' => 'Miftahul Jannah Marwah'],
];

$lab_peer_anggota = [
    'konseling' => [
        'Herdiananda Nurhidayah', 'Alfita', 'Nursyafiqah', 'Ananda',
        'Mardini Nurdin', 'Dyea Yuliana', 'Muh. Virgiawan Ramadhan',
        'Rezki Nur Afni', 'Dita Putri Qurani',
    ],
    'media' => [
        'Muhammad Naufal Adhim', 'Aryani Nur Annisa', 'Astrid Apandi',
        'Andi Sitti Sahari Habulana', 'Taufiq Rahman', 'Florencya Alnisa Christin',
        'Emmy Fauzia Amalia', 'Fitriani Nur', 'Nurul Izzah',
    ],
    'training' => [
        'Nurul Izzah', 'Nurwahyuni Watmun', 'Wahyudi Kadir',
        'Wahyu Alamsyah', 'Ummu Kalsum', 'Nasywa Nabilah S', 'Muhammad Arqam Jibril',
    ],
    'asesmen' => [
        'Rasyidah Katzimah', 'Andi Septiana Syaba Tina', 'Muhammad Habib Pratama',
        'Lutfiah Ramadhani', 'Muh Rafli Rafael Hasan', 'Putri Anggun Nur Farahim',
        'Meliana Pongreso', 'Isratujannah', 'Nabila Herman', 'Nur Anbiyah Ihsan',
    ],
];

$logo_url        = get_template_directory_uri() . '/assets/images/logo-lab-bk.png?v=3';
$peersupport_url = 'https://labbk8.wixsite.com/peersupport';
$ajax_url        = admin_url('admin-ajax.php');
$reg_nonce       = wp_create_nonce('ppb_lab_pendaftaran_public_nonce');

$titles_map = [
    'home'         => 'Laboratorium BK FIP UNM — Portal Resmi Terpadu',
    'tentang-kami' => 'Tentang Kami, Visi Misi & Struktur — Laboratorium BK FIP UNM',
    'inventaris'   => 'Inventaris Alat Tes & Ruang — Laboratorium BK FIP UNM',
    'kegiatan'     => 'Kegiatan & Praktikum — Laboratorium BK FIP UNM',
    'lomba'        => 'Lomba Non-Akademik — Laboratorium BK FIP UNM',
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
        <div>🕒 Senin – Jumat, 08.00 – 16.00 WITA &bull; ✉️ lab.bk@unm.ac.id &bull; 📸 @lab.bk_unm</div>
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
            <a href="<?php echo esc_url(home_url('/lab-bk/#lima-bidang')); ?>" class="<?php echo $is_bidang ? 'is-active' : ''; ?>">5 Bidang</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="<?php echo $active_view === 'inventaris' ? 'is-active' : ''; ?>">Inventaris</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/kegiatan/')); ?>" class="<?php echo ($active_view === 'kegiatan' || $active_view === 'lomba') ? 'is-active' : ''; ?>">Kegiatan</a>
            <a href="<?php echo esc_url(home_url('/lab-bk/tentang-kami/')); ?>" class="<?php echo $active_view === 'tentang-kami' ? 'is-active' : ''; ?>">Tentang Kami</a>
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

    <!-- Banner Pengantar ke Tentang Kami (Visi, Misi & Struktur Organisasi) -->
    <section class="lab-section" style="padding-top:0;padding-bottom:24px;">
        <div class="lab-wrap">
            <div style="background:linear-gradient(135deg, #0B1730 0%, #14274E 100%);color:#ffffff;border-radius:20px;padding:24px 28px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
                <div>
                    <span style="font-size:11px;font-weight:800;color:var(--lab-gold);text-transform:uppercase;letter-spacing:0.06em;">Tentang Laboratorium BK FIP UNM</span>
                    <h3 style="font-family:'Fraunces',Georgia,serif;font-size:21px;margin:4px 0 6px;color:#ffffff;">Visi, Misi &amp; Bagan Struktur Pengelola Lab BK</h3>
                    <p style="font-size:13.5px;color:rgba(255,255,255,0.82);margin:0;max-width:640px;">Kenali Kepala Lab, 6 Dosen Koordinator Bidang, Pembina, serta jajaran Peer Collaborator yang mengelola layanan Laboratorium BK.</p>
                </div>
                <a href="<?php echo esc_url(home_url('/lab-bk/tentang-kami/')); ?>" class="lab-btn-gold">Buka Tentang Kami &rarr;</a>
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

<?php elseif ($active_view === 'tentang-kami') : ?>
    <!-- ==================== TENTANG KAMI: VISI, MISI & STRUKTUR ==================== -->
    <section class="lab-hero" style="padding:40px 0 46px;">
        <div class="lab-wrap">
            <div style="font-size:12.5px;color:rgba(255,255,255,0.75);margin-bottom:10px;">
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" style="color:var(--lab-gold);text-decoration:none;font-weight:600;">Beranda Lab BK</a>
                <span> / </span>
                <span>Tentang Kami</span>
            </div>
            <span class="lab-badge">🏛️ Profil, Visi, Misi &amp; Struktur Organisasi</span>
            <h1>Tentang Laboratorium BK FIP UNM</h1>
            <p style="margin-bottom:0;">Mengenal visi, misi, bidang layanan, serta tata kelola pengelola dan Peer Collaborator Laboratorium Bimbingan &amp; Konseling Fakultas Ilmu Pendidikan UNM.</p>
        </div>
    </section>

    <!-- Visi & Misi Resmi -->
    <section class="lab-section" style="padding-bottom:16px;">
        <div class="lab-wrap">
            <div style="background:#ffffff;border:1px solid var(--lab-border);border-left:5px solid var(--lab-navy);border-radius:20px;padding:28px;margin-bottom:24px;box-shadow:0 6px 20px rgba(20,39,78,0.05);">
                <span class="lab-eyebrow">Visi Laboratorium BK FIP UNM</span>
                <p style="font-family:'Fraunces',Georgia,serif;font-size:21px;font-weight:700;color:var(--lab-navy);line-height:1.45;margin:6px 0 0;">
                    &ldquo;Menjadi Ruang Pembelajaran Praktikum Serta Pengenalan Pengalaman Lapangan bagi Mahasiswa dan Komunitas Bimbingan dan Konseling.&rdquo;
                </p>
            </div>

            <div class="lab-section-head" style="margin-bottom:16px;">
                <span class="lab-eyebrow">Misi Laboratorium BK FIP UNM</span>
                <h2 style="font-size:24px;">Tiga Pilar Misi Laboratorium</h2>
            </div>
            <div class="lab-grid-3" style="margin-bottom:12px;">
                <div class="lab-card" style="background:linear-gradient(135deg, #0B1730 0%, #14274E 100%);color:#ffffff;border:none;">
                    <div>
                        <span class="lab-pill lab-pill-gold" style="margin-bottom:12px;">Misi 01</span>
                        <p style="font-size:15px;font-weight:700;line-height:1.6;margin:0;color:#ffffff;">
                            Ruang Pembelajaran Praktikum Layanan Bimbingan dan Konseling secara Individu maupun Kelompok.
                        </p>
                    </div>
                </div>
                <div class="lab-card" style="background:linear-gradient(135deg, #14274E 0%, #1E4E79 100%);color:#ffffff;border:none;">
                    <div>
                        <span class="lab-pill lab-pill-gold" style="margin-bottom:12px;">Misi 02</span>
                        <p style="font-size:15px;font-weight:700;line-height:1.6;margin:0;color:#ffffff;">
                            Ruang Pembelajaran Praktikum Asesmen baik tes dan non tes.
                        </p>
                    </div>
                </div>
                <div class="lab-card" style="background:linear-gradient(135deg, #0D3B22 0%, #165B36 100%);color:#ffffff;border:none;">
                    <div>
                        <span class="lab-pill lab-pill-gold" style="margin-bottom:12px;">Misi 03</span>
                        <p style="font-size:15px;font-weight:700;line-height:1.6;margin:0;color:#ffffff;">
                            Ruang Pengembangan Kapasitas dan Kompetensi Pribadi, Sosial, Akademik dan Karier.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Struktur Organisasi dengan Garis Komando Jelas -->
    <?php
    $kajur_card  = $find_dosen_card($kajur_nama);
    $kalab_card  = $find_dosen_card($kalab_nama);
    $sekjur_card = $find_dosen_card($sekjur_nama);
    $render_org_avatar = function ($card, $size = 64) {
        if (!empty($card['foto'])) {
            $pos = !empty($card['foto_posisi']) ? $card['foto_posisi'] : '50% 15%';
            return '<img src="' . esc_url($card['foto']) . '" alt="' . esc_attr($card['nama']) . '" style="width:' . $size . 'px;height:' . $size . 'px;border-radius:14px;object-fit:cover;object-position:' . esc_attr($pos) . ';border:2px solid var(--lab-gold);flex-shrink:0;">';
        }
        return '<div style="width:' . $size . 'px;height:' . $size . 'px;border-radius:14px;background:var(--lab-navy);color:var(--lab-gold);font-weight:800;font-size:' . round($size * 0.34) . 'px;display:flex;align-items:center;justify-content:center;border:2px solid var(--lab-gold);flex-shrink:0;">' . esc_html($card['initials']) . '</div>';
    };
    ?>
    <section id="struktur" class="lab-section" style="padding-top:16px;">
        <div class="lab-wrap">
            <div class="lab-section-head">
                <span class="lab-eyebrow">Tata Kelola &amp; Garis Komando</span>
                <h2>Struktur Organisasi Laboratorium BK FIP UNM</h2>
                <p style="color:var(--lab-muted);margin:0;max-width:740px;">Kepala Laboratorium BK berkedudukan sejajar koordinatif dengan Ketua Jurusan, membawahi langsung Pembina &amp; Pengurus Inti Peer Collaborator serta 6 Dosen Koordinator Bidang beserta anggota mahasiswa di bawah komando masing-masing.</p>
            </div>

            <div style="background:#ffffff;border:1px solid var(--lab-border);border-radius:22px;padding:28px;box-shadow:0 8px 28px rgba(20,39,78,0.05);">
                <!-- Level 1: Ketua Jurusan SEJAJAR dengan Kepala Lab BK -->
                <div style="display:grid;grid-template-columns:1fr auto 1fr;gap:14px;align-items:center;max-width:880px;margin:0 auto;">
                    <div style="background:#F8FAFC;border:2px solid var(--lab-navy);border-radius:16px;padding:15px;display:flex;align-items:center;gap:14px;">
                        <?php echo $render_org_avatar($kajur_card, 62); ?>
                        <div>
                            <span class="lab-pill" style="margin-bottom:4px;">Ketua Jurusan PPB / BK</span>
                            <div style="font-size:15px;font-weight:800;color:var(--lab-navy);line-height:1.25;"><?php echo esc_html($kajur_card['nama']); ?></div>
                            <?php if (!empty($kajur_card['url'])) : ?>
                                <a href="<?php echo esc_url($kajur_card['url']); ?>" style="font-size:11.5px;color:var(--lab-emerald);font-weight:700;text-decoration:none;">Profil Dosen &rarr;</a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="display:flex;flex-direction:column;align-items:center;gap:4px;min-width:85px;">
                        <span style="font-size:10px;font-weight:800;color:var(--lab-navy);text-transform:uppercase;letter-spacing:0.05em;">Sejajar</span>
                        <div style="width:100%;height:0;border-top:2px dashed var(--lab-navy);"></div>
                        <span style="font-size:9.5px;color:var(--lab-muted);">Koordinasi</span>
                    </div>

                    <div style="background:linear-gradient(135deg, #0B1730 0%, #14274E 100%);color:#ffffff;border:2px solid var(--lab-gold);border-radius:16px;padding:15px;display:flex;align-items:center;gap:14px;">
                        <?php echo $render_org_avatar($kalab_card, 62); ?>
                        <div>
                            <span class="lab-pill lab-pill-gold" style="margin-bottom:4px;">Kepala Laboratorium BK</span>
                            <div style="font-size:15px;font-weight:800;color:#ffffff;line-height:1.25;"><?php echo esc_html($kalab_card['nama']); ?></div>
                            <?php if (!empty($kalab_card['url'])) : ?>
                                <a href="<?php echo esc_url($kalab_card['url']); ?>" style="font-size:11.5px;color:var(--lab-gold);font-weight:700;text-decoration:none;">Profil Dosen &rarr;</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Level 1b: Garis Turun dari Kajur ke Sekjur & dari Kepala Lab ke Bus Bar Komando -->
                <div style="max-width:880px;margin:0 auto;display:grid;grid-template-columns:1fr auto 1fr;gap:14px;">
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div style="width:2px;height:22px;background:var(--lab-navy);"></div>
                        <div style="font-size:10px;color:var(--lab-navy);line-height:1;margin-bottom:4px;">&#9660;</div>
                        <div style="background:#F8FAFC;border:1px solid var(--lab-border);border-radius:14px;padding:11px 14px;display:flex;align-items:center;gap:12px;width:100%;box-sizing:border-box;">
                            <?php echo $render_org_avatar($sekjur_card, 46); ?>
                            <div>
                                <div style="font-size:10.5px;font-weight:700;color:var(--lab-muted);text-transform:uppercase;">Sekretaris Jurusan</div>
                                <div style="font-size:13.5px;font-weight:700;color:var(--lab-navy);"><?php echo esc_html($sekjur_card['nama']); ?></div>
                            </div>
                        </div>
                    </div>
                    <div style="min-width:85px;"></div>
                    <div style="display:flex;flex-direction:column;align-items:center;justify-content:flex-start;">
                        <div style="width:3px;height:44px;background:var(--lab-navy);"></div>
                        <div style="background:var(--lab-navy);color:var(--lab-gold);font-size:11px;font-weight:800;padding:5px 14px;border-radius:99px;text-transform:uppercase;letter-spacing:0.05em;">
                            Garis Komando Kepala Lab BK &#9660;
                        </div>
                    </div>
                </div>

                <!-- Bus Bar Horizontal Pembagi Komando -->
                <div style="border-top:3px solid var(--lab-navy);margin:14px 0 22px;position:relative;"></div>

                <!-- Level 2: Kiri = Jalur Komando Pembina -> Pengurus Inti Peer Collaborator; Kanan = 6 Dosen Koordinator -> Anggota Bidang -->
                <div style="display:grid;grid-template-columns:280px 1fr;gap:22px;align-items:start;" class="lab-org-main-split">
                    <!-- Kolom Kiri: Pembina & Pengurus Inti Peer Collaborator -->
                    <div style="display:flex;flex-direction:column;align-items:center;">
                        <div style="width:100%;background:#F8FAFC;border:1.5px solid var(--lab-emerald);border-radius:16px;padding:15px;box-sizing:border-box;">
                            <div style="font-size:11px;font-weight:800;color:var(--lab-emerald);text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Pembina Peer Collaborator</div>
                            <div style="display:grid;gap:6px;">
                                <?php foreach ($lab_peer_pembina as $pm) : ?>
                                    <div style="background:#ffffff;border:1px solid var(--lab-border);border-radius:8px;padding:7px 10px;font-size:12.5px;font-weight:700;color:var(--lab-navy);">👤 <?php echo esc_html($pm); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Garis Komando Vertikal Pembina -> Pengurus Inti -->
                        <div style="width:2px;height:22px;background:var(--lab-emerald);"></div>
                        <div style="font-size:11px;color:var(--lab-emerald);line-height:1;margin-bottom:4px;">&#9660;</div>

                        <div style="width:100%;background:#FFFBEB;border:1.5px solid var(--lab-gold);border-radius:16px;padding:15px;box-sizing:border-box;">
                            <div style="font-size:11px;font-weight:800;color:#92400E;text-transform:uppercase;letter-spacing:0.05em;margin-bottom:8px;">Pengurus Inti Peer Collaborator</div>
                            <div style="display:grid;gap:7px;">
                                <?php foreach ($lab_peer_inti as $pi) : ?>
                                    <div style="background:#ffffff;border:1px solid #FDE68A;border-radius:8px;padding:8px 10px;">
                                        <div style="font-size:10px;color:#92400E;font-weight:700;"><?php echo esc_html($pi['jabatan']); ?></div>
                                        <div style="font-size:12.5px;font-weight:800;color:var(--lab-navy);"><?php echo esc_html($pi['nama']); ?></div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div style="margin-top:10px;font-size:11px;font-weight:700;color:var(--lab-muted);text-align:center;border:1px dashed #CBD5E1;border-radius:10px;padding:8px 10px;width:100%;box-sizing:border-box;">
                            &#8596; Koordinasi Operasional ke Anggota Peer Collaborator tiap Bidang
                        </div>
                    </div>

                    <!-- Kolom Kanan: Setiap Dosen Koordinator + Garis Komando Vertikal ke Anggotanya -->
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;">
                        <?php foreach ($lab_koordinator_struktur as $ks) :
                            $b_slug     = $ks['bidang'];
                            $koord_card = $find_dosen_card($ks['nama']);
                            $b_info     = $all_bidang[$b_slug] ?? ['icon' => '🏛️'];
                            $mems       = ($b_slug !== 'ekstrakurikuler') ? ($lab_peer_anggota[$b_slug] ?? []) : [];
                        ?>
                            <div style="display:flex;flex-direction:column;align-items:center;">
                                <!-- Kartu Dosen Koordinator -->
                                <a href="<?php echo esc_url(home_url('/lab-bk/bidang-' . $b_slug . '/')); ?>" style="width:100%;box-sizing:border-box;text-decoration:none;background:#F8FAFC;border:1.5px solid var(--lab-navy);border-radius:16px;padding:14px;display:flex;flex-direction:column;align-items:center;text-align:center;gap:8px;">
                                    <?php echo $render_org_avatar($koord_card, 58); ?>
                                    <div>
                                        <span class="lab-pill <?php echo $b_slug === 'ekstrakurikuler' ? 'lab-pill-gold' : ''; ?>" style="margin-bottom:5px;font-size:10px;"><?php echo esc_html($b_info['icon'] . ' ' . $ks['label']); ?></span>
                                        <div style="font-size:13.5px;font-weight:800;color:var(--lab-navy);line-height:1.3;"><?php echo esc_html($koord_card['nama']); ?></div>
                                        <div style="font-size:11px;color:var(--lab-muted);margin-top:2px;">Dosen Koordinator</div>
                                    </div>
                                </a>

                                <!-- Garis Komando Vertikal Dosen -> Peer Collaborator -->
                                <div style="width:2px;height:20px;background:var(--lab-navy);"></div>
                                <div style="font-size:11px;color:var(--lab-navy);line-height:1;margin-bottom:4px;">&#9660;</div>

                                <!-- Daftar Anggota Peer Collaborator di Bawah Komando Dosen -->
                                <?php if (!empty($mems)) : ?>
                                    <div style="width:100%;box-sizing:border-box;background:linear-gradient(180deg, #1E4E79 0%, #14274E 100%);color:#ffffff;border-radius:14px;padding:14px;">
                                        <div style="font-size:11.5px;font-weight:800;color:var(--lab-gold);margin-bottom:8px;">Anggota Peer Collaborator (<?php echo count($mems); ?>)</div>
                                        <ol style="margin:0;padding-left:16px;font-size:12px;color:rgba(255,255,255,0.92);display:grid;gap:2px;">
                                            <?php foreach ($mems as $m_name) : ?>
                                                <li><?php echo esc_html($m_name); ?></li>
                                            <?php endforeach; ?>
                                        </ol>
                                    </div>
                                <?php else : ?>
                                    <a href="<?php echo esc_url(home_url('/lab-bk/bidang-ekstrakurikuler/')); ?>" style="width:100%;box-sizing:border-box;text-decoration:none;background:#FFFBEB;border:1px solid #FDE68A;color:#92400E;border-radius:14px;padding:12px;text-align:center;font-size:12px;font-weight:700;">
                                        Membina Klub Minat Bakat &amp; Delegasi Lomba Mahasiswa &rarr;
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <style>
        @media (max-width: 900px) {
            .lab-org-main-split { grid-template-columns: 1fr !important; }
        }
    </style>

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

            <?php if (!empty($lab_peer_anggota[$active_view])) : ?>
                <div style="background:#ffffff;border:1px solid var(--lab-border);border-radius:20px;padding:24px;margin-top:36px;">
                    <span class="lab-eyebrow">Tim Pelaksana Mahasiswa</span>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:21px;color:var(--lab-navy);margin:0 0 14px;">Anggota Peer Collaborator <?php echo esc_html($cur_bidang['nama']); ?></h2>
                    <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(210px, 1fr));gap:10px;">
                        <?php foreach ($lab_peer_anggota[$active_view] as $idx_m => $m_name) : ?>
                            <div style="background:#F8FAFC;border:1px solid var(--lab-border);border-radius:10px;padding:10px 14px;font-size:13px;font-weight:700;color:var(--lab-navy);">
                                <?php echo ($idx_m + 1) . '. ' . esc_html($m_name); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

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
                <div style="font-size:12.5px;">✉️ lab.bk@unm.ac.id &bull; 📸 @lab.bk_unm</div>
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
