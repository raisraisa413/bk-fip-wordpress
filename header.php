<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <!-- BK_THEME_VER: 3.0.0_EDITORIAL_CALM -->
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
    <style>
    /* ======================================================================
       EDITORIAL CALM v3.0 — NAVBAR CLEARANCE, ADMIN BAR & 1160px CONTAINERS
       ====================================================================== */
    :root {
        --max-w: 1160px;
        --util-h: 26px;
        --nav-h: 62px;
        --header-h: 88px;
        --bk-navbar-height: 88px;
        --bk-edge-pad: clamp(20px, 4vw, 40px);
        --bk-admin-bar-offset: 0px;
        --bk-top-clearance: 24px;
    }
    body.admin-bar {
        --bk-admin-bar-offset: 32px;
    }
    @media screen and (max-width: 782px) {
        body.admin-bar {
            --bk-admin-bar-offset: 46px;
        }
    }

    /* WordPress Top Admin Bar Protection */
    #wpadminbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 32px !important;
        min-height: 32px !important;
        max-height: 32px !important;
        background: #1d2327 !important;
        color: #c3c4c7 !important;
        z-index: 999999 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    @media screen and (max-width: 782px) {
        #wpadminbar {
            height: 46px !important;
            min-height: 46px !important;
            max-height: 46px !important;
        }
    }

    /* Fixed 2-Tier Forest Green Header & Flow Spacer */
    #bk-main-navbar.bk-header {
        position: fixed !important;
        top: var(--bk-admin-bar-offset, 0px) !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 99990 !important;
    }
    #bk-navbar-spacer {
        width: 100% !important;
        height: var(--bk-navbar-height, 88px) !important;
        background-color: #0D3B22 !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
    }

    /* Editorial Full-Bleed Outer + 1160px Centered Inner Container */
    .bk-wrap,
    .bk-container,
    .bk-content-wrap {
        width: 100% !important;
        max-width: var(--max-w, 1160px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-right: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        box-sizing: border-box !important;
    }
    .bk-container .bk-container,
    .bk-wrap .bk-wrap,
    .bk-wrap .bk-container,
    .bk-container .bk-wrap {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* Subpage Sticky Sub-Navigation Bar Alignment (1160px inner) */
    .bk-subnav-sticky-wrap {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-right: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        box-sizing: border-box !important;
    }
    .bk-subnav-sticky-wrap > div {
        width: 100% !important;
        max-width: var(--max-w, 1160px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }

    /* Subpage Section & Main Body Alignment (Full-bleed outer, 1160px inner) */
    .bk-page-wrapper > main,
    main.bk-subpage-main {
        width: 100% !important;
        max-width: var(--max-w, 1160px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-right: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-bottom: 80px !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:first-of-type {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .bk-page-wrapper > section:first-of-type > div:not([style*="position:absolute"]):not([style*="position: absolute"]):not(.orb-1):not(.orb-2):not(.dot-grid) {
        width: 100% !important;
        max-width: var(--max-w, 1160px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-right: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:not(:first-of-type) {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        padding-right: var(--bk-edge-pad, clamp(20px, 4vw, 40px)) !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:not(:first-of-type) > div {
        width: 100% !important;
        max-width: var(--max-w, 1160px) !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }
    .bk-content-wrap .ppb-jadwal-wrapper,
    .bk-content-wrap .ppb-prestasi-wrapper,
    .bk-content-wrap .ppb-karya-wrapper,
    .bk-content-wrap .ppb-tracer-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    </style>
</head>
<body <?php body_class('antialiased selection:bg-[#F5B82E] selection:text-[#0D3B22]'); ?> style="background:#FAF8F5;color:#111827;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;overflow-x:hidden;margin:0;padding:0;">
<?php wp_body_open(); ?>

<?php
// =============================================================================
// URL & ACTIVE STATE HELPERS
// =============================================================================
$req_uri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
$req_path = parse_url($req_uri, PHP_URL_PATH) ?: '/';
$home_path = parse_url(home_url(), PHP_URL_PATH) ?: '';
if (!empty($home_path) && strpos($req_path, $home_path) === 0) {
    $req_path = substr($req_path, strlen($home_path));
}
if (strpos($req_path, '/yamato/') === 0) {
    $req_path = substr($req_path, 7);
} elseif ($req_path === '/yamato') {
    $req_path = '/';
}
$current_path = trim($req_path, '/');
$is_home = is_front_page() || is_home() || $current_path === '' || $current_path === 'beranda';

if (!function_exists('ppb_filter_yamato_url')) {
    function ppb_filter_yamato_url($url) {
        if (empty($url) || $url === '#') return $url;
        $req = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '';
        $in_yamato = (strpos($req, '/yamato') === 0 || strpos(home_url(), '/yamato') !== false);
        if (!$in_yamato) return $url;
        if (strpos($url, '/') === 0 && strpos($url, '//') !== 0 && strpos($url, '/yamato') !== 0) {
            return '/yamato' . $url;
        }
        return $url;
    }
}

if (!function_exists('ppb_nav_is_active')) {
    function ppb_nav_is_active($url, $current_path, $is_home, $is_parent = false) {
        if (empty($url)) return false;
        $clean_url = trim((string) $url);
        if ($clean_url === '#' || strpos($clean_url, '#') === 0) return false;

        $parsed = parse_url($clean_url);
        $m_path = $parsed['path'] ?? '';
        $home_path = parse_url(home_url(), PHP_URL_PATH) ?? '';
        if (!empty($home_path) && strpos($m_path, $home_path) === 0) {
            $m_path = substr($m_path, strlen($home_path));
        }
        if (strpos($m_path, '/yamato/') === 0) {
            $m_path = substr($m_path, 7);
        } elseif ($m_path === '/yamato') {
            $m_path = '/';
        }
        $m_path = trim($m_path, '/');

        if ($is_home) {
            $clean_item_url = rtrim(preg_replace('/^https?:/', '', $clean_url), '/');
            $clean_home_url = rtrim(preg_replace('/^https?:/', '', home_url()), '/');
            return ($clean_item_url === $clean_home_url || $m_path === '' || $m_path === 'beranda');
        }
        if (empty($m_path) || $m_path === 'beranda') {
            return false;
        }
        if (!$is_parent) {
            if (function_exists('ppb_is_leaf_item_active')) {
                $matched_dummy = false;
                return ppb_is_leaf_item_active(array('url' => $clean_url, 'title' => ''), $current_path, $is_home, $matched_dummy);
            }
            return ($m_path === $current_path);
        }
        if ($m_path === $current_path || strpos($current_path, $m_path . '/') === 0) {
            return true;
        }
        if ($m_path === 'profil') {
            return (strpos($current_path, 'profil') === 0 || strpos($current_path, 'akreditasi') === 0 || in_array($current_path, array('sejarah', 'visi-misi', 'visi-keilmuan', 'struktur', 'kerjasama-tridharma', 'dosen', 'buku-dosen', 'kepakaran-dosen', 'sarana-prasarana', 'roadmap', 'dokumen', 'akreditasi', 'upm', 'sertifikat'), true) || (function_exists('is_singular') && is_singular('dosen')));
        }
        if ($m_path === 'akademik') {
            return (strpos($current_path, 'akademik') === 0 || strpos($current_path, 'mis-pnbp') === 0 || in_array($current_path, array('kurikulum', 'rps-sap', 'panduan', 'jadwal-ujian', 'statistik', 'mis-pnbp', 'jurnal-bk'), true));
        }
        if ($m_path === 'layanan') {
            return (strpos($current_path, 'layanan') === 0 || strpos($current_path, 'laboratorium') === 0 || strpos($current_path, 'lab-bk') === 0 || strpos($current_path, 'berita') === 0 || strpos($current_path, 'kemahasiswaan') === 0 || strpos($current_path, 'survei') === 0 || strpos($current_path, 'e-library') !== false || strpos($current_path, 'elibrary') !== false || (function_exists('is_singular') && (is_singular('post') || is_singular('berita'))) || (function_exists('is_category') && is_category()));
        }
        return false;
    }
}

$bk_cfg = function_exists('bk_fip_get_homepage_config') ? bk_fip_get_homepage_config() : array();
$top_strip_badge = $bk_cfg['top_strip_badge'] ?? get_option('bk_top_strip_badge', 'BK-CONNECT');
$top_strip_text  = $bk_cfg['top_strip_text'] ?? get_option('bk_top_strip_text', 'Ekosistem digital layanan akademik & konseling Jurusan PPB FIP UNM');
$portal_auth_url = $bk_cfg['portal_auth_url'] ?? get_option('bk_portal_auth_url', 'https://bk-connect-fipunm.vercel.app/auth');

// Always build the 4-pillar navigation tree (with custom WP-Admin menu support if assigned & 4-pillar, or canonical 4 pillars).
$nav_tree = function_exists('ppb_get_nav_tree') ? ppb_get_nav_tree($current_path, $is_home) : array();
// Guarantee the 4-pillar structure: if an older 9-item menu was still in DB before admin_init ran, fall back to the 4-pillar defaults.
if (empty($nav_tree) || count($nav_tree) > 5) {
    $nav_tree = function_exists('ppb_get_default_mega_menu_data') ? ppb_get_default_mega_menu_data() : array();
    foreach ($nav_tree as &$top_p) {
        $top_p['is_active'] = ppb_nav_is_active($top_p['url'] ?? '', $current_path, $is_home, true);
    }
    unset($top_p);
}
$search_index = function_exists('bk_fip_get_search_index') ? bk_fip_get_search_index() : array();
?>

<!-- ======================================================================= -->
<!-- OFFICIAL 2-TIER FOREST GREEN & GOLD HEADER + 4-PILLAR MEGA MENU         -->
<!-- ======================================================================= -->
<header id="bk-main-navbar" class="bk-header" role="banner">
    <!-- Tier 1: Top BK-Connect Utility Strip (#061D11, 26px) -->
    <div class="bk-util-bar">
        <div class="bk-wrap bk-util-inner">
            <span class="bk-util-badge"><?php echo esc_html($top_strip_badge); ?></span>
            <span class="bk-util-text"><?php echo esc_html($top_strip_text); ?></span>
            <span aria-hidden="true" style="opacity:0.35;">·</span>
            <a href="<?php echo esc_url($portal_auth_url); ?>" target="_blank" rel="noopener" class="bk-util-link">
                <span>Masuk Portal</span>
                <span aria-hidden="true">↗</span>
            </a>
        </div>
    </div>

    <!-- Tier 2: Main Forest Green Navbar (#0D3B22, 62px) -->
    <div class="bk-wrap bk-header-inner">
        <!-- Brand Identity -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="bk-brand" aria-label="Beranda Bimbingan dan Konseling FIP UNM">
            <span class="bk-brand-mark">
                <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/logo-white.png'); ?>" alt="Logo UNM">
            </span>
            <span class="bk-brand-title">
                <span class="bk-brand-name">Bimbingan &amp; Konseling</span>
                <span class="bk-brand-sub">Jurusan PPB · FIP UNM</span>
            </span>
        </a>

        <!-- Desktop 4-Pillar Navigation (Beranda | Profil & Mutu | Akademik | Layanan & Warta) -->
        <nav id="bk-desktop-nav" class="bk-nav" aria-label="Navigasi Utama">
            <?php foreach ($nav_tree as $idx => $pillar) :
                $has_mega = !empty($pillar['is_mega']) && !empty($pillar['sections']);
                $is_act   = !empty($pillar['is_active']);
                $p_url    = $pillar['url'] ?? home_url('/');
                $spot     = $pillar['spotlight'] ?? null;
            ?>
                <div class="bk-nav-item <?php echo $has_mega ? 'bk-nav-item-wrap' : ''; ?>">
                    <?php if ($has_mega) : ?>
                        <button type="button"
                                class="bk-nav-link dropdown-trigger <?php echo $is_act ? 'is-active' : ''; ?>"
                                aria-expanded="false"
                                aria-haspopup="true">
                            <span><?php echo esc_html($pillar['title']); ?></span>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>

                        <!-- Full-Width Editorial Mega Menu Panel -->
                        <div class="bk-mega-panel bk-mega-dropdown" role="region" aria-label="<?php echo esc_attr($pillar['title']); ?>">
                            <div class="bk-wrap bk-mega-grid">
                                <div class="bk-mega-cols">
                                    <?php foreach ($pillar['sections'] as $col) : ?>
                                        <div class="bk-mega-col">
                                            <div class="bk-mega-col-title"><?php echo esc_html($col['heading'] ?? ''); ?></div>
                                            <div class="bk-mega-list">
                                                <?php foreach (($col['items'] ?? array()) as $item) :
                                                    $it_active = !empty($item['is_active']);
                                                    $it_target = !empty($item['target']) ? $item['target'] : '_self';
                                                    $it_icon   = !empty($item['icon']) ? $item['icon'] : 'arrow-right';
                                                    $it_sub    = $item['subtitle'] ?? ($item['sub'] ?? '');
                                                ?>
                                                    <a href="<?php echo esc_url($item['url'] ?? '#'); ?>"
                                                       target="<?php echo esc_attr($it_target); ?>"
                                                       <?php echo $it_target === '_blank' ? 'rel="noopener"' : ''; ?>
                                                       class="bk-mega-link <?php echo $it_active ? 'is-active' : ''; ?>">
                                                        <span class="bk-mega-link-icon" aria-hidden="true">
                                                            <?php echo function_exists('ppb_get_menu_icon_svg') ? ppb_get_menu_icon_svg($it_icon, 15) : ''; ?>
                                                        </span>
                                                        <span>
                                                            <span class="bk-mega-link-title" style="display:block;"><?php echo esc_html($item['title'] ?? ''); ?></span>
                                                            <?php if (!empty($it_sub)) : ?>
                                                                <span class="bk-mega-link-sub" style="display:block;"><?php echo esc_html($it_sub); ?></span>
                                                            <?php endif; ?>
                                                        </span>
                                                    </a>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!empty($spot) && is_array($spot)) : ?>
                                    <aside class="bk-mega-spotlight-card">
                                        <div>
                                            <?php if (!empty($spot['badge'])) : ?>
                                                <span class="bk-mega-spotlight-badge"><?php echo esc_html($spot['badge']); ?></span>
                                            <?php endif; ?>
                                            <h3 class="bk-mega-spotlight-title"><?php echo esc_html($spot['title'] ?? ''); ?></h3>
                                            <p class="bk-mega-spotlight-desc"><?php echo esc_html($spot['description'] ?? ''); ?></p>
                                        </div>
                                        <a href="<?php echo esc_url($spot['url'] ?? '#'); ?>" class="bk-mega-spotlight-cta">
                                            <span><?php echo esc_html($spot['action_text'] ?? 'Selengkapnya'); ?></span>
                                            <span aria-hidden="true">&rarr;</span>
                                        </a>
                                    </aside>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else : ?>
                        <a href="<?php echo esc_url($p_url); ?>" class="bk-nav-link <?php echo $is_act ? 'is-active' : ''; ?>">
                            <span><?php echo esc_html($pillar['title']); ?></span>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </nav>

        <!-- Right Actions: Search Trigger Only (Windows Ctrl K) + Mobile Hamburger (NO standalone SIPADU button) -->
        <div class="bk-header-actions">
            <button type="button" id="bk-search-trigger" class="bk-search-trigger" aria-label="Cari halaman, dosen, modul, atau formulir (Ctrl K)">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span class="bk-search-label">Cari...</span>
                <kbd class="bk-kbd">Ctrl K</kbd>
            </button>

            <button type="button" id="mobile-nav-toggle" class="bk-mobile-toggle" aria-label="Buka menu navigasi" aria-expanded="false" aria-controls="mobile-nav-menu">
                <svg id="menu-icon-open" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                <svg id="menu-icon-close" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>
    </div>
</header>

<!-- Flow Spacer matching fixed header height -->
<div id="bk-navbar-spacer" aria-hidden="true"></div>

<!-- ======================================================================= -->
<!-- MOBILE DRAWER PANEL (4 PILLARS + ACCORDIONS + CTRL K SEARCH)            -->
<!-- ======================================================================= -->
<div id="mobile-nav-menu" class="bk-mobile-panel" aria-label="Menu Mobile">
    <button type="button" class="bk-mob-search-btn" onclick="window.bkToggleMobileNav(false);window.bkOpenCommandSearch && window.bkOpenCommandSearch();" style="margin-bottom:14px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <span style="flex:1;">Cari halaman, dosen, modul, formulir...</span>
        <kbd class="bk-kbd">Ctrl K</kbd>
    </button>

    <?php foreach ($nav_tree as $pillar) :
        $has_mega = !empty($pillar['is_mega']) && !empty($pillar['sections']);
        $spot     = $pillar['spotlight'] ?? null;
    ?>
        <?php if (!$has_mega) : ?>
            <div class="bk-mobile-acc" style="border-bottom:1px solid rgba(255,255,255,0.12);">
                <a href="<?php echo esc_url($pillar['url'] ?? home_url('/')); ?>" class="bk-mobile-link" style="padding:14px 4px;font-size:15px;font-weight:600;">
                    <span><?php echo esc_html($pillar['title']); ?></span>
                    <span aria-hidden="true" style="color:#F5B82E;">&rarr;</span>
                </a>
            </div>
        <?php else : ?>
            <div class="bk-mobile-acc">
                <button type="button" class="bk-mobile-acc-btn" onclick="this.parentElement.classList.toggle('is-open');">
                    <span><?php echo esc_html($pillar['title']); ?></span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </button>
                <div class="bk-mobile-acc-panel">
                    <?php foreach ($pillar['sections'] as $col) : ?>
                        <div class="bk-mobile-col-label"><?php echo esc_html($col['heading'] ?? ''); ?></div>
                        <?php foreach (($col['items'] ?? array()) as $item) :
                            $it_target = !empty($item['target']) ? $item['target'] : '_self';
                        ?>
                            <a href="<?php echo esc_url($item['url'] ?? '#'); ?>"
                               target="<?php echo esc_attr($it_target); ?>"
                               class="bk-mobile-link">
                                <span><?php echo esc_html($item['title'] ?? ''); ?></span>
                                <span aria-hidden="true" style="opacity:0.55;">&rsaquo;</span>
                            </a>
                        <?php endforeach; ?>
                    <?php endforeach; ?>

                    <?php if (!empty($spot['url'])) : ?>
                        <a href="<?php echo esc_url($spot['url']); ?>" class="bk-mobile-spot">
                            <span>
                                <strong><?php echo esc_html($spot['badge'] ?? 'SOROTAN'); ?></strong>
                                <?php echo esc_html($spot['title'] ?? ''); ?>
                            </span>
                            <span aria-hidden="true" style="color:#F5B82E;font-weight:700;">&rarr;</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>

<!-- ======================================================================= -->
<!-- COMMAND PALETTE SEARCH MODAL (Ctrl+K / /)                               -->
<!-- ======================================================================= -->
<div id="bk-cmd-backdrop" class="bk-backdrop" aria-hidden="true"></div>
<div id="bk-cmd-modal" class="bk-cmd-modal" role="dialog" aria-modal="true" aria-label="Pencarian Cepat">
    <div class="bk-cmd-input-wrap">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        <input type="search" id="bk-cmd-input" class="bk-cmd-input" placeholder="Ketik nama dosen, mata kuliah, modul E-Library, F-01, atau halaman..." autocomplete="off">
        <button type="button" id="bk-cmd-close" class="bk-icon-btn" aria-label="Tutup pencarian" style="width:30px;height:30px;font-size:11px;font-family:'IBM Plex Mono',monospace;">ESC</button>
    </div>
    <div id="bk-cmd-results" class="bk-cmd-results" role="listbox"></div>
</div>

<script>
window.BK_SEARCH_INDEX = <?php echo wp_json_encode($search_index); ?>;

(function() {
    // 1. Keep #bk-navbar-spacer synced with live header height (without double-counting WP html margin-top)
    function syncBkNavbarSpacer() {
        var nav = document.getElementById('bk-main-navbar');
        var spacer = document.getElementById('bk-navbar-spacer');
        if (!nav) return;
        var h = nav.offsetHeight || 88;
        document.documentElement.style.setProperty('--bk-navbar-height', h + 'px');
        document.documentElement.style.setProperty('--ppb-navbar-height', h + 'px');
        var bar = document.getElementById('wpadminbar');
        var barH = (bar && document.body && document.body.classList.contains('admin-bar')) ? (bar.offsetHeight || 0) : 0;
        if (bar && barH > 0) {
            document.documentElement.style.setProperty('--bk-admin-bar-offset', barH + 'px');
        }
        if (spacer) {
            var htmlMt = parseFloat(window.getComputedStyle(document.documentElement).marginTop) || 0;
            var extraBar = Math.max(0, barH - htmlMt);
            spacer.style.setProperty('height', (h + extraBar) + 'px', 'important');
        }
    }
    syncBkNavbarSpacer();
    document.addEventListener('DOMContentLoaded', syncBkNavbarSpacer);
    window.addEventListener('load', syncBkNavbarSpacer);
    window.addEventListener('resize', syncBkNavbarSpacer);

    // 2. Mobile drawer toggle
    window.bkToggleMobileNav = function(forceState) {
        var menu = document.getElementById('mobile-nav-menu');
        var btn = document.getElementById('mobile-nav-toggle');
        var iconOpen = document.getElementById('menu-icon-open');
        var iconClose = document.getElementById('menu-icon-close');
        if (!menu) return;
        var shouldOpen = (typeof forceState === 'boolean') ? forceState : !menu.classList.contains('is-open');
        menu.classList.toggle('is-open', shouldOpen);
        if (btn) btn.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        if (iconOpen) iconOpen.style.display = shouldOpen ? 'none' : 'block';
        if (iconClose) iconClose.style.display = shouldOpen ? 'block' : 'none';
    };
    var mobBtn = document.getElementById('mobile-nav-toggle');
    if (mobBtn) {
        mobBtn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            window.bkToggleMobileNav();
        });
    }
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 1024) window.bkToggleMobileNav(false);
    });

    // 3. Desktop Mega Menu (hover + click + Esc + outside click)
    function initDesktopMegaMenu() {
        var wraps = document.querySelectorAll('#bk-desktop-nav .bk-nav-item-wrap');
        if (!wraps.length) return;
        var hoverTimer = null;

        function closeAll() {
            wraps.forEach(function(w) {
                w.classList.remove('is-open');
                delete w.dataset.closedByClick;
                var b = w.querySelector('.dropdown-trigger');
                if (b) b.setAttribute('aria-expanded', 'false');
            });
        }
        function openWrap(w) {
            wraps.forEach(function(o) {
                if (o !== w) {
                    o.classList.remove('is-open');
                    delete o.dataset.closedByClick;
                    var ob = o.querySelector('.dropdown-trigger');
                    if (ob) ob.setAttribute('aria-expanded', 'false');
                }
            });
            delete w.dataset.closedByClick;
            w.classList.add('is-open');
            var b = w.querySelector('.dropdown-trigger');
            if (b) b.setAttribute('aria-expanded', 'true');
        }
        function closeWrap(w) {
            w.classList.remove('is-open');
            w.dataset.closedByClick = 'true';
            var b = w.querySelector('.dropdown-trigger');
            if (b) b.setAttribute('aria-expanded', 'false');
        }

        wraps.forEach(function(w) {
            var btn = w.querySelector('.dropdown-trigger');
            var panel = w.querySelector('.bk-mega-panel');
            if (!btn || !panel) return;

            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                if (w.classList.contains('is-open')) closeWrap(w);
                else openWrap(w);
            });
            w.addEventListener('mouseenter', function() {
                if (window.innerWidth < 1024) return;
                if (hoverTimer) clearTimeout(hoverTimer);
                if (w.dataset.closedByClick === 'true') return;
                hoverTimer = setTimeout(function() { openWrap(w); }, 70);
            });
            w.addEventListener('mouseleave', function(e) {
                if (window.innerWidth < 1024) return;
                delete w.dataset.closedByClick;
                if (hoverTimer) clearTimeout(hoverTimer);
                hoverTimer = setTimeout(function() {
                    if (!w.matches(':hover')) {
                        w.classList.remove('is-open');
                        btn.setAttribute('aria-expanded', 'false');
                    }
                }, 140);
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#bk-desktop-nav')) closeAll();
        });
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeAll();
        });
    }
    initDesktopMegaMenu();

    // 4. Command Palette Search (Ctrl+K / /)
    var cmdModal = document.getElementById('bk-cmd-modal');
    var cmdBack  = document.getElementById('bk-cmd-backdrop');
    var cmdInput = document.getElementById('bk-cmd-input');
    var cmdRes   = document.getElementById('bk-cmd-results');
    var cmdClose = document.getElementById('bk-cmd-close');
    var srchBtn  = document.getElementById('bk-search-trigger');

    function escHtml(s) {
        return String(s || '').replace(/[&<>"']/g, function(c) {
            return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];
        });
    }
    function renderCmdResults(q) {
        if (!cmdRes) return;
        var list = Array.isArray(window.BK_SEARCH_INDEX) ? window.BK_SEARCH_INDEX : [];
        var term = (q || '').trim().toLowerCase();
        var filtered = term ? list.filter(function(it) {
            return (it.title + ' ' + it.sub + ' ' + it.type).toLowerCase().indexOf(term) !== -1;
        }) : list.slice(0, 14);

        if (!filtered.length) {
            cmdRes.innerHTML = '<div style="padding:24px;text-align:center;color:#6B7280;font-size:13.5px;">Tidak ditemukan hasil untuk <strong>' + escHtml(q) + '</strong>.</div>';
            return;
        }
        var groups = {};
        filtered.slice(0, 24).forEach(function(it) {
            var g = it.type || 'Lainnya';
            if (!groups[g]) groups[g] = [];
            groups[g].push(it);
        });
        var html = '';
        Object.keys(groups).forEach(function(g) {
            html += '<div class="bk-cmd-group-label">' + escHtml(g) + '</div>';
            groups[g].forEach(function(it) {
                html += '<a class="bk-cmd-item" href="' + escHtml(it.url) + '">' +
                    '<div><div style="font-weight:600;font-size:13.5px;color:#111827;">' + escHtml(it.title) + '</div>' +
                    '<div style="font-size:12px;color:#6B7280;">' + escHtml(it.sub) + '</div></div>' +
                    '<span style="font-family:\'IBM Plex Mono\',monospace;font-size:11px;color:#0D3B22;">Buka &rarr;</span></a>';
            });
        });
        cmdRes.innerHTML = html;
    }
    window.bkOpenCommandSearch = function() {
        if (!cmdModal || !cmdBack) return;
        cmdModal.classList.add('is-open');
        cmdBack.classList.add('is-open');
        renderCmdResults(cmdInput ? cmdInput.value : '');
        setTimeout(function() { if (cmdInput) cmdInput.focus(); }, 30);
    };
    window.bkCloseCommandSearch = function() {
        if (!cmdModal || !cmdBack) return;
        cmdModal.classList.remove('is-open');
        cmdBack.classList.remove('is-open');
    };
    if (srchBtn) srchBtn.addEventListener('click', window.bkOpenCommandSearch);
    if (cmdClose) cmdClose.addEventListener('click', window.bkCloseCommandSearch);
    if (cmdBack) cmdBack.addEventListener('click', window.bkCloseCommandSearch);
    if (cmdInput) cmdInput.addEventListener('input', function() { renderCmdResults(cmdInput.value); });

    document.addEventListener('keydown', function(e) {
        var tag = (e.target && e.target.tagName) ? e.target.tagName.toLowerCase() : '';
        var inField = (tag === 'input' || tag === 'textarea' || tag === 'select' || (e.target && e.target.isContentEditable));
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            if (cmdModal && cmdModal.classList.contains('is-open')) window.bkCloseCommandSearch();
            else window.bkOpenCommandSearch();
        } else if (e.key === '/' && !inField) {
            e.preventDefault();
            window.bkOpenCommandSearch();
        } else if (e.key === 'Escape' && cmdModal && cmdModal.classList.contains('is-open')) {
            window.bkCloseCommandSearch();
        }
    });

    // 5. Auto-lock all navigation links to /yamato staging environment when accessed under /yamato
    (function() {
        var isYamato = (window.location.pathname.indexOf('/yamato') === 0 || window.location.href.indexOf('/yamato') !== -1);
        if (!isYamato) return;
        function fixYamatoUrl(url) {
            if (!url) return url;
            if (url.indexOf('#') === 0 || url.indexOf('mailto:') === 0 || url.indexOf('tel:') === 0 || url.indexOf('javascript:') === 0) return url;
            if (url.indexOf('//') === 0 && url.indexOf('//bk-fip.unm.ac.id') === -1) return url;
            if (url.indexOf('/') === 0) {
                if (url.indexOf('/yamato/') === 0 || url === '/yamato') return url;
                return '/yamato' + (url === '/' ? '/' : url);
            }
            if (url.indexOf('://bk-fip.unm.ac.id') !== -1) {
                try {
                    var u = new URL(url);
                    if (u.pathname.indexOf('/yamato/') !== 0 && u.pathname !== '/yamato') {
                        u.pathname = '/yamato' + (u.pathname === '/' ? '/' : u.pathname);
                        return u.toString();
                    }
                } catch (e) {}
            }
            return url;
        }
        function patchLinks() {
            var links = document.querySelectorAll('a[href]');
            for (var i = 0; i < links.length; i++) {
                var raw = links[i].getAttribute('href');
                var fixed = fixYamatoUrl(raw);
                if (fixed !== raw) links[i].setAttribute('href', fixed);
            }
        }
        patchLinks();
        document.addEventListener('DOMContentLoaded', patchLinks);
        window.addEventListener('load', patchLinks);
    })();
})();
</script>
