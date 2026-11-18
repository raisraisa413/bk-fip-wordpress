<?php

/**

 * BK FIP UNM Theme Functions

 */



if (!defined('ABSPATH')) exit;

/**
 * --------------------------------------------------------------------------
 * WP-STAGING DEV ENVIRONMENT HANDLER (/yamato)
 * --------------------------------------------------------------------------
 * Ensures all generated URLs (home_url, site_url, page content links)
 * retain the '/yamato' prefix when running on staging so links never leak
 * to the production main domain (bk-fip.unm.ac.id/).
 */
if (!function_exists('ppb_is_yamato_staging')) {
    function ppb_is_yamato_staging() {
        static $is_staging = null;
        if ($is_staging !== null) return $is_staging;

        $host   = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
        $req    = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $script = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';

        // Detect if running inside WP-Staging (/yamato)
        if (
            strpos($req, '/yamato') !== false ||
            strpos($script, '/yamato') !== false ||
            strpos(__DIR__, 'yamato') !== false ||
            (defined('WPSTG_DEV') && WPSTG_DEV) ||
            (defined('WP_ENVIRONMENT_TYPE') && WP_ENVIRONMENT_TYPE === 'staging')
        ) {
            $is_staging = true;
        } else {
            $is_staging = false;
        }
        return $is_staging;
    }
}

if (!function_exists('ppb_filter_yamato_url')) {
    function ppb_filter_yamato_url($url) {
        if (empty($url) || !ppb_is_yamato_staging()) {
            return $url;
        }

        // External protocols, anchors, mailto, tel, javascript
        if (strpos($url, '#') === 0 || strpos($url, 'mailto:') === 0 || strpos($url, 'tel:') === 0 || strpos($url, 'javascript:') === 0) {
            return $url;
        }

        // Protocol-relative external URLs
        if (strpos($url, '//') === 0 && strpos($url, '//bk-fip.unm.ac.id') === false) {
            return $url;
        }

        $parsed = parse_url($url);
        $host = isset($parsed['host']) ? $parsed['host'] : '';

        // Root-relative URL starting with /
        if (empty($host) && strpos($url, '/') === 0) {
            if (strpos($url, '/yamato/') === 0 || $url === '/yamato') {
                return $url;
            }
            return '/yamato' . ($url === '/' ? '/' : $url);
        }

        // Full URL with domain
        $current_host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'bk-fip.unm.ac.id';
        if ($host === 'bk-fip.unm.ac.id' || $host === $current_host || empty($host)) {
            $path = isset($parsed['path']) ? $parsed['path'] : '/';
            if (strpos($path, '/yamato/') === 0 || $path === '/yamato') {
                return $url;
            }
            $new_path = '/yamato' . ('/' . ltrim($path, '/'));
            $scheme = isset($parsed['scheme']) ? $parsed['scheme'] . '://' : '//';
            $port = isset($parsed['port']) ? ':' . $parsed['port'] : '';
            $query = isset($parsed['query']) ? '?' . $parsed['query'] : '';
            $fragment = isset($parsed['fragment']) ? '#' . $parsed['fragment'] : '';
            return $scheme . $host . $port . $new_path . $query . $fragment;
        }

        return $url;
    }
}

// 1. Force home and siteurl options to include /yamato on staging
add_filter('pre_option_home', function($val) {
    if (ppb_is_yamato_staging()) {
        return 'https://bk-fip.unm.ac.id/yamato';
    }
    return $val;
}, 99);

add_filter('pre_option_siteurl', function($val) {
    if (ppb_is_yamato_staging()) {
        return 'https://bk-fip.unm.ac.id/yamato';
    }
    return $val;
}, 99);

// 2. Attach filters to WordPress core URL functions
add_filter('home_url', function($url, $path = '', $orig_scheme = null, $blog_id = null) {
    return ppb_filter_yamato_url($url);
}, 99, 4);

add_filter('site_url', function($url, $path = '', $scheme = null, $blog_id = null) {
    return ppb_filter_yamato_url($url);
}, 99, 4);

// 3. Prevent wp_redirect from escaping /yamato
add_filter('wp_redirect', function($location, $status = 302) {
    if (ppb_is_yamato_staging()) {
        return ppb_filter_yamato_url($location);
    }
    return $location;
}, 99, 2);

// Filter custom navigation menus from WP Admin (Appearance > Menus)
add_filter('wp_get_nav_menu_items', function($items) {
    if (empty($items) || !is_array($items) || !ppb_is_yamato_staging()) {
        return $items;
    }
    foreach ($items as &$item) {
        if (!empty($item->url)) {
            $item->url = ppb_filter_yamato_url($item->url);
        }
    }
    unset($item);
    return $items;
}, 99);

add_filter('nav_menu_link_attributes', function($atts, $item = null, $args = null, $depth = 0) {
    if (ppb_is_yamato_staging() && !empty($atts['href'])) {
        $atts['href'] = ppb_filter_yamato_url($atts['href']);
    }
    return $atts;
}, 99, 4);

add_filter('page_link', function($url, $post_id = 0, $sample = false) {
    return ppb_filter_yamato_url($url);
}, 99, 3);

add_filter('post_link', function($url, $post = null, $leavename = false) {
    return ppb_filter_yamato_url($url);
}, 99, 3);

add_filter('post_type_link', function($url, $post = null, $leavename = false, $sample = false) {
    return ppb_filter_yamato_url($url);
}, 99, 4);

add_filter('the_content', function($content) {
    if (empty($content) || !ppb_is_yamato_staging()) {
        return $content;
    }

    // 1. Rewrite root-relative links: href="/path" -> href="/yamato/path"
    $content = preg_replace_callback('/href=(["\'])(\/[^"\']*)\1/i', function($matches) {
        $quote = $matches[1];
        $href = $matches[2];
        if (strpos($href, '//') === 0 || strpos($href, '/yamato/') === 0 || $href === '/yamato') {
            return 'href=' . $quote . $href . $quote;
        }
        return 'href=' . $quote . '/yamato' . ($href === '/' ? '/' : $href) . $quote;
    }, $content);

    // 2. Rewrite full domain links: href="https://bk-fip.unm.ac.id/path" -> href="https://bk-fip.unm.ac.id/yamato/path"
    $content = preg_replace_callback('/href=(["\'])(https?:\/\/bk-fip\.unm\.ac\.id)(\/[^"\']*)\1/i', function($matches) {
        $quote = $matches[1];
        $domain = $matches[2];
        $path = $matches[3];
        if (strpos($path, '/yamato/') === 0 || $path === '/yamato') {
            return 'href=' . $quote . $domain . $path . $quote;
        }
        return 'href=' . $quote . $domain . '/yamato' . ($path === '/' ? '/' : $path) . $quote;
    }, $content);

    return $content;
}, 99);

// Staging init safeguards: unconditionally block canonical redirects and ensure permalinks & .htaccess
add_action('template_redirect', function() {
    if (ppb_is_yamato_staging() || strpos($_SERVER['REQUEST_URI'], '/yamato') !== false) {
        remove_action('template_redirect', 'redirect_canonical');
    }
}, 1);

add_filter('redirect_canonical', function($redirect_url, $requested_url) {
    if (
        ppb_is_yamato_staging() ||
        strpos($_SERVER['REQUEST_URI'], '/yamato') !== false ||
        (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'bk-fip.unm.ac.id') !== false)
    ) {
        return false;
    }
    return $redirect_url;
}, 1, 2);

add_action('init', function() {
    if (!ppb_is_yamato_staging() && strpos($_SERVER['REQUEST_URI'], '/yamato') === false) return;

    // Auto-create staging .htaccess in multiple potential paths
    $potential_paths = array();
    $wp_root = dirname(dirname(dirname(__DIR__)));
    $potential_paths[] = $wp_root . '/.htaccess';
    $potential_paths[] = $wp_root . '/yamato/.htaccess';
    if (defined('ABSPATH')) {
        $potential_paths[] = rtrim(ABSPATH, '/') . '/.htaccess';
        $potential_paths[] = rtrim(ABSPATH, '/') . '/yamato/.htaccess';
    }
    if (isset($_SERVER['DOCUMENT_ROOT'])) {
        $potential_paths[] = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/yamato/.htaccess';
    }

    $rules = "# BEGIN WordPress Staging /yamato\n" .
             "<IfModule mod_rewrite.c>\n" .
             "RewriteEngine On\n" .
             "RewriteBase /yamato/\n" .
             "RewriteRule ^index\\.php$ - [L]\n" .
             "RewriteCond %{REQUEST_FILENAME} !-f\n" .
             "RewriteCond %{REQUEST_FILENAME} !-d\n" .
             "RewriteRule . /yamato/index.php [L]\n" .
             "</IfModule>\n" .
             "# END WordPress Staging /yamato\n";

    $written_any = false;
    foreach ($potential_paths as $p) {
        $dir = dirname($p);
        if (is_dir($dir) && is_writable($dir)) {
            if (!file_exists($p) || strpos(@file_get_contents($p), 'RewriteBase /yamato/') === false) {
                if (@file_put_contents($p, $rules) !== false) {
                    $written_any = true;
                }
            } else {
                $written_any = true;
            }
        }
    }

    // Auto-activate post-name permalinks if currently plain
    if (get_option('permalink_structure') === '') {
        global $wp_rewrite;
        if ($wp_rewrite) {
            $wp_rewrite->set_permalink_structure('/%postname%/');
            $wp_rewrite->flush_rules(true);
        }
    }
}, 1);

// Admin 1-Click Fixer Handler for Staging /yamato
add_action('admin_init', function() {
    if (isset($_GET['fix_yamato_htaccess']) && current_user_can('manage_options')) {
        $rules = "# BEGIN WordPress Staging /yamato\n" .
                 "<IfModule mod_rewrite.c>\n" .
                 "RewriteEngine On\n" .
                 "RewriteBase /yamato/\n" .
                 "RewriteRule ^index\\.php$ - [L]\n" .
                 "RewriteCond %{REQUEST_FILENAME} !-f\n" .
                 "RewriteCond %{REQUEST_FILENAME} !-d\n" .
                 "RewriteRule . /yamato/index.php [L]\n" .
                 "</IfModule>\n" .
                 "# END WordPress Staging /yamato\n";
        $wp_root = dirname(dirname(dirname(__DIR__)));
        $paths = [
            $wp_root . '/.htaccess',
            $wp_root . '/yamato/.htaccess',
            (defined('ABSPATH') ? rtrim(ABSPATH, '/') . '/.htaccess' : ''),
            (defined('ABSPATH') ? rtrim(ABSPATH, '/') . '/yamato/.htaccess' : ''),
            (isset($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/yamato/.htaccess' : '')
        ];
        $success = false;
        foreach ($paths as $p) {
            if (!empty($p) && is_dir(dirname($p)) && is_writable(dirname($p))) {
                if (@file_put_contents($p, $rules) !== false) {
                    $success = true;
                }
            }
        }
        update_option('permalink_structure', '/%postname%/');
        flush_rewrite_rules(true);
        wp_safe_redirect(admin_url('options-permalink.php?yamato_fixed=' . ($success ? '1' : '0')));
        exit;
    }
});

// Auto-patch and intercept links in WP-Admin as well
add_action('admin_footer', function() {
    if (!ppb_is_yamato_staging()) return;
    ?>
    <script>
    (function() {
        function fixYamatoUrl(url) {
            if (!url) return url;
            if (url.indexOf('#') === 0 || url.indexOf('mailto:') === 0 || url.indexOf('javascript:') === 0) return url;
            if (url.indexOf('://bk-fip.unm.ac.id') !== -1) {
                try {
                    var u = new URL(url);
                    if (u.pathname.indexOf('/yamato/') !== 0 && u.pathname !== '/yamato') {
                        u.pathname = '/yamato' + (u.pathname === '/' ? '/' : u.pathname);
                        return u.toString();
                    }
                } catch(e) {}
            }
            return url;
        }
        function patchAdminLinks() {
            var links = document.querySelectorAll('a[href]');
            for (var i = 0; i < links.length; i++) {
                var raw = links[i].getAttribute('href');
                var fixed = fixYamatoUrl(raw);
                if (fixed !== raw) links[i].setAttribute('href', fixed);
            }
        }
        patchAdminLinks();
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (!a) return;
            var href = a.getAttribute('href');
            var fixed = fixYamatoUrl(href);
            if (fixed !== href) {
                e.preventDefault();
                e.stopPropagation();
                window.location.href = fixed;
            }
        }, true);
    })();
    </script>
    <?php
});

// Force theme's own header and footer over any lingering Elementor Header Footer Builder templates
add_filter('enable_hfe_render_header', '__return_false', 99999);
add_filter('enable_hfe_render_footer', '__return_false', 99999);
add_action('wp', function() {
    if (class_exists('Header_Footer_Elementor')) {
        remove_action('get_header', 'hfe_render_header');
        remove_action('get_footer', 'hfe_render_footer');
    }
}, 1);


// Safe-include PPB Berita Module if not loaded by plugin
if (!function_exists('ppb_render_berita_grid_swiss')) {
    $berita_inc = get_template_directory() . '/inc/ppb-admin-berita.php';
    if (file_exists($berita_inc)) {
        require_once $berita_inc;
    }
}

// Safe-include PPB Spotlight Admin Module if not loaded by plugin
if (!function_exists('ppb_get_spotlight_slides')) {
    $spotlight_inc = get_template_directory() . '/inc/ppb-admin-spotlight.php';
    if (file_exists($spotlight_inc)) {
        require_once $spotlight_inc;
    }
}

// PPB Dynamic Mega Menu Module & WP-Admin Manager
$mega_menu_inc = get_template_directory() . '/inc/ppb-mega-menu.php';
if (file_exists($mega_menu_inc)) {
    require_once $mega_menu_inc;
}

// =============================================================================
// EDITORIAL CALM v3.0 — ASSET HELPERS, HOMEPAGE CONFIG & COMMAND SEARCH INDEX
// =============================================================================

if (!function_exists('bk_resolve_theme_asset_url')) {
    /**
     * Resolve theme-relative asset paths (assets/img/x.jpg), site-relative paths
     * (/wp-content/..., /yamato/...) and absolute URLs into a usable URL.
     */
    function bk_resolve_theme_asset_url($path) {
        $path = trim((string) $path);
        if ($path === '') {
            return '';
        }
        if (preg_match('#^(https?:)?//#i', $path) || strpos($path, 'data:image/') === 0) {
            return $path;
        }
        if (strpos($path, 'assets/') === 0) {
            return get_template_directory_uri() . '/' . $path;
        }
        if (strpos($path, '/') === 0) {
            $home_path = (string) wp_parse_url(home_url('/'), PHP_URL_PATH);
            $home_path = rtrim($home_path, '/');
            if ($home_path !== '' && strpos($path, $home_path . '/') === 0) {
                return site_url($path === $home_path ? '/' : substr($path, strlen($home_path)));
            }
            return home_url($path);
        }
        return get_template_directory_uri() . '/' . ltrim($path, '/');
    }
}

if (!function_exists('bk_sanitize_asset_or_url')) {
    /**
     * Sanitize a value that may be a theme asset path, a site-relative path or an
     * absolute URL. Unlike esc_url_raw(), relative paths are preserved.
     * Google Drive share links are converted to direct thumbnail URLs.
     */
    function bk_sanitize_asset_or_url($val) {
        $val = trim(wp_strip_all_tags((string) $val));
        if ($val === '') {
            return '';
        }
        // Google Drive helper: /file/d/{id}/view or open?id={id}
        if (strpos($val, 'drive.google.com') !== false) {
            if (preg_match('#/d/([a-zA-Z0-9_-]{10,})#', $val, $m) || preg_match('#[?&]id=([a-zA-Z0-9_-]{10,})#', $val, $m)) {
                return 'https://drive.google.com/thumbnail?id=' . $m[1] . '&sz=w1200';
            }
        }
        if (preg_match('#^(https?:)?//#i', $val)) {
            return esc_url_raw($val);
        }
        // Relative path: allow only safe characters and block traversal / schemes.
        if (strpos($val, '..') !== false || preg_match('#^[a-z0-9+.-]+:#i', $val)) {
            return '';
        }
        $clean = preg_replace('#[^A-Za-z0-9/_\-.%~]#', '', $val);
        return $clean;
    }
}

if (!function_exists('bk_fip_get_homepage_defaults')) {
    /**
     * Default content for all Editorial Calm homepage sections (editable via WP-Admin).
     */
    function bk_fip_get_homepage_defaults() {
        return array(
            'top_strip_badge' => 'BK-CONNECT',
            'top_strip_text'  => 'Ekosistem digital layanan akademik & konseling Jurusan PPB FIP UNM',
            'portal_auth_url' => 'https://bk-connect-fipunm.vercel.app/auth',
            'sipadu_url'      => 'https://bk-connect-fipunm.vercel.app/sipadu',
            'stats' => array(
                array('code' => '01 // MUTU',      'badge' => 'LAMDIK', 'number' => 'Unggul',       'desc' => 'Terakreditasi Unggul S1 & S2. Baik Sekali S3'),
                array('code' => '02 // SEJARAH',   'badge' => 'EST.',   'number' => '1969',         'desc' => '57 tahun melahirkan konselor & pendidik Nusantara'),
                array('code' => '03 // SDM DOSEN', 'badge' => 'AKTIF',  'number' => '25 Dosen',     'desc' => '4 Guru Besar · Doktor · 421 Publikasi Ilmiah'),
                array('code' => '04 // JENJANG',   'badge' => 'LINIER', 'number' => 'S1 · S2 · S3', 'desc' => 'Kurikulum OBE 2025 terintegrasi SDGs & MBKM'),
            ),
            'kajur_nama'    => 'Dr. Suciani Latif, M.Pd.',
            'kajur_jabatan' => 'KETUA JURUSAN PPB FIP UNM',
            'kajur_quote'   => 'Pendidikan konseling di FIP UNM dirancang untuk melahirkan sarjana, magister, dan doktor yang berintegritas moral, peka terhadap keragaman sosio-kultural, serta adaptif memanfaatkan inovasi psikoedukasi dan telekonseling.',
            'degrees' => array(
                's1' => array(
                    'code' => 'S1', 'tab_label' => 'S1 Sarjana',
                    'kkni' => 'KKNI Level 6 · Akreditasi Unggul LAMDIK',
                    'title' => 'S1 Bimbingan dan Konseling',
                    'summary' => 'Mempersiapkan calon konselor sekolah dan praktisi bimbingan yang menguasai asesmen psikologis, mikrokonseling, serta desain program BK komprehensif berbasis teknologi dan empati budaya.',
                    'gelar' => 'Sarjana Pendidikan (S.Pd.)', 'beban' => '144–146 SKS · 8 Semester',
                    'kurikulum' => 'OBE 2025 Terintegrasi MBKM',
                    'sk' => 'SK LAMDIK No. 742/SK/LAMDIK/Ak/S/VIII/2023 (s.d. 2028)',
                    'kompetensi' => "Mikrokonseling & Teknik Terapi Kognitif-Perilaku (CBT / SFBT)\nAsesmen Psikologi Tes & Non-Tes (IST, RMIB, AUM, DCM, IKMS)\nManajemen Program BK Komprehensif, Karir & Konseling Digital",
                    'url' => '/akademik/kurikulum/',
                ),
                's2' => array(
                    'code' => 'S2', 'tab_label' => 'S2 Magister',
                    'kkni' => 'KKNI Level 8 · Akreditasi Unggul LAMDIK',
                    'title' => 'S2 Bimbingan dan Konseling',
                    'summary' => 'Mengembangkan penguasaan metodologi riset lanjut, rekayasa model intervensi psikososial, konseling lintas budaya, serta kepemimpinan akademik dan profesi konseling pada jenjang pascasarjana.',
                    'gelar' => 'Magister Pendidikan (M.Pd.)', 'beban' => '38–42 SKS · 4 Semester',
                    'kurikulum' => 'Riset Terapan & Supervisi Klinis',
                    'sk' => 'SK LAMDIK No. 412/SK/LAMDIK/Ak/M/V/2024',
                    'kompetensi' => "Metodologi Penelitian Kuantitatif, Kualitatif & R&D BK\nPengembangan Modul & Instrumen Psikometri Terapan\nKonseling Krisis, Rehabilitasi & Trauma Multi-Kultur",
                    'url' => '/akademik/kurikulum/',
                ),
                's3' => array(
                    'code' => 'S3', 'tab_label' => 'S3 Doktoral',
                    'kkni' => 'KKNI Level 9 · Terakreditasi Baik Sekali LAMDIK',
                    'title' => 'S3 Ilmu Pendidikan (Konsentrasi BK)',
                    'summary' => 'Mencetak filosof keilmuan, akademisi perguruan tinggi, dan perumus kebijakan nasional yang melahirkan kebaruan teori (novelty) intervensi konseling berakar kearifan budaya Nusantara.',
                    'gelar' => 'Doktor (Dr.)', 'beban' => '42–48 SKS · 6 Semester',
                    'kurikulum' => 'Disertasi & Kebaruan Teori Indigenos',
                    'sk' => 'SK LAMDIK No. 189/SK/LAMDIK/Ak/D/X/2023',
                    'kompetensi' => "Filsafat Keilmuan & Epistemologi Konseling Kontemporer\nKonstruksi Model Konseling Indigenos & Kearifan Lokal\nPublikasi Bereputasi Internasional (Scopus/WoS) & Kebijakan",
                    'url' => '/akademik/kurikulum/',
                ),
            ),
            'video_chapters' => array(
                array(
                    'num' => '01', 'duration' => '03:45', 'badge' => 'Bagian 1 · Visi & Sejarah 1969',
                    'title' => '57 Tahun Mendidik Calon Konselor Nusantara',
                    'desc' => 'Dari Bimbingan Penyuluhan (1969) menjadi prodi terakreditasi Unggul LAMDIK untuk jenjang Sarjana dan Magister di Fakultas Ilmu Pendidikan UNM.',
                    'date' => 'Profil Jurusan · Kampus Tidung', 'thumb' => 'assets/img/hero-campus.jpg', 'video_url' => '',
                ),
                array(
                    'num' => '02', 'duration' => '04:12', 'badge' => 'Bagian 2 · Lab BK Gunungsari',
                    'title' => '4 Bilik Mikrokonseling One-Way Mirror & Asesmen Psikologi',
                    'desc' => 'Tur fasilitas laboratorium psikologi kedap suara, instrumen IST/CFIT/DAT, studio cybercounseling, dan workstation Rasch/Jamovi.',
                    'date' => 'Fasilitas Praktikum · Kampus Gunungsari', 'thumb' => 'assets/img/lab-studio.jpg', 'video_url' => '',
                ),
                array(
                    'num' => '03', 'duration' => '02:58', 'badge' => 'Bagian 3 · Kurikulum OBE 2025',
                    'title' => '144 SKS Terintegrasi SDGs, MBKM & Ekosistem BK-Connect',
                    'desc' => "Implementasi Case Method, Team-Based Project, penelitian berbasis kearifan budaya Siri' na Pacce, serta praktik lapangan di 35+ sekolah mitra.",
                    'date' => 'Akademik & Inovasi · 2025', 'thumb' => 'assets/img/hero-campus.jpg', 'video_url' => '',
                ),
            ),
            'portal_cards' => array(
                array('code' => 'P-01', 'title' => 'Kurikulum OBE 2025', 'desc' => '144 SKS, RPS 16 pertemuan, CPL & indikator SDGs.', 'url' => '/akademik/kurikulum/', 'icon' => 'layers'),
                array('code' => 'P-02', 'title' => 'Persuratan F-01..F-06', 'desc' => 'Template DOCX resmi pengajuan judul, bimbingan & sidang.', 'url' => '/layanan/akademik/', 'icon' => 'file-text'),
                array('code' => 'P-03', 'title' => 'Laboratorium BK', 'desc' => '4 bilik one-way mirror, alat tes IST/CFIT & studio.', 'url' => '/laboratorium/', 'icon' => 'flask-conical'),
                array('code' => 'P-04', 'title' => 'E-Library & Modul', 'desc' => 'Modul praktikum, buku ajar & repositori artikel.', 'url' => '/e-library/', 'icon' => 'library'),
                array('code' => 'P-05', 'title' => 'Direktori 25 Dosen', 'desc' => 'Guru Besar, Doktor, kepakaran & profil SINTA.', 'url' => '/profil/dosen/', 'icon' => 'users'),
                array('code' => 'P-06', 'title' => 'Akreditasi Unggul', 'desc' => 'SK LAMDIK S1–S3 & eviden 9 Kriteria mutu.', 'url' => '/akreditasi/', 'icon' => 'award'),
            ),
            'admisi_title' => 'Penerimaan Mahasiswa Baru 2026',
            'admisi_desc'  => 'Jalur SNBP, SNBT, dan Mandiri UNM untuk S1 Bimbingan dan Konseling — serta Pascasarjana S2 & S3.',
            'admisi_btn'   => 'Info Admisi UNM',
            'admisi_url'   => 'https://penerimaan.unm.ac.id',
            'footer_address'    => 'Gedung Jurusan PPB, Fakultas Ilmu Pendidikan UNM, Kampus Tidung Jl. Tamalate I, Makassar, Sulawesi Selatan',
            'footer_akreditasi' => 'S1 & S2 Terakreditasi Unggul LAMDIK · S3 Baik Sekali',
            'footer_copyright'  => '© ' . gmdate('Y') . ' Jurusan Psikologi Pendidikan dan Bimbingan · FIP Universitas Negeri Makassar',
        );
    }
}

if (!function_exists('bk_fip_get_homepage_config')) {
    /**
     * Merged homepage config (WP options over defaults). Large arrays are stored with autoload = false.
     */
    function bk_fip_get_homepage_config() {
        $d = bk_fip_get_homepage_defaults();
        $cfg = array(
            'top_strip_badge'   => get_option('bk_top_strip_badge', $d['top_strip_badge']),
            'top_strip_text'    => get_option('bk_top_strip_text', $d['top_strip_text']),
            'portal_auth_url'   => get_option('bk_portal_auth_url', $d['portal_auth_url']),
            'sipadu_url'        => get_option('bk_sipadu_url', $d['sipadu_url']),
            'kajur_nama'        => get_option('bk_kajur_nama', $d['kajur_nama']),
            'kajur_jabatan'     => get_option('bk_kajur_jabatan', $d['kajur_jabatan']),
            'kajur_quote'       => get_option('bk_kajur_quote', $d['kajur_quote']),
            'admisi_title'      => get_option('bk_admisi_title', $d['admisi_title']),
            'admisi_desc'       => get_option('bk_admisi_desc', $d['admisi_desc']),
            'admisi_btn'        => get_option('bk_admisi_btn', $d['admisi_btn']),
            'admisi_url'        => get_option('bk_admisi_url', $d['admisi_url']),
            'footer_address'    => get_option('bk_footer_address', $d['footer_address']),
            'footer_akreditasi' => get_option('bk_footer_akreditasi', $d['footer_akreditasi']),
            'footer_copyright'  => get_option('bk_footer_copyright', $d['footer_copyright']),
        );
        $cfg['stats'] = array(
            array(
                'code'   => $d['stats'][0]['code'],
                'badge'  => $d['stats'][0]['badge'],
                'number' => get_option('bk_stat_akreditasi_val', $d['stats'][0]['number']),
                'desc'   => get_option('bk_stat_akreditasi_desc', $d['stats'][0]['desc']),
            ),
            array(
                'code'   => $d['stats'][1]['code'],
                'badge'  => $d['stats'][1]['badge'],
                'number' => get_option('bk_stat_tradisi_val', $d['stats'][1]['number']),
                'desc'   => get_option('bk_stat_tradisi_desc', $d['stats'][1]['desc']),
            ),
            array(
                'code'   => $d['stats'][2]['code'],
                'badge'  => $d['stats'][2]['badge'],
                'number' => get_option('bk_stat_dosen_val', $d['stats'][2]['number']),
                'desc'   => get_option('bk_stat_dosen_desc', $d['stats'][2]['desc']),
            ),
            array(
                'code'   => $d['stats'][3]['code'],
                'badge'  => $d['stats'][3]['badge'],
                'number' => get_option('bk_stat_riset_val', $d['stats'][3]['number']),
                'desc'   => get_option('bk_stat_riset_desc', $d['stats'][3]['desc']),
            ),
        );
        $cfg['degrees'] = array();
        foreach (array('s1', 's2', 's3') as $k) {
            $saved = get_option('bk_degree_' . $k, array());
            $cfg['degrees'][$k] = is_array($saved) ? array_merge($d['degrees'][$k], array_filter($saved, 'strlen')) : $d['degrees'][$k];
        }
        $vc = get_option('bk_video_chapters', array());
        $cfg['video_chapters'] = (is_array($vc) && count($vc) === 3) ? $vc : $d['video_chapters'];
        $pc = get_option('bk_portal_cards', array());
        $cfg['portal_cards'] = (is_array($pc) && count($pc) === 6) ? $pc : $d['portal_cards'];
        return $cfg;
    }
}

if (!function_exists('bk_fip_get_search_index')) {
    /**
     * Build the client-side Ctrl+K command search index (window.BK_SEARCH_INDEX).
     */
    function bk_fip_get_search_index() {
        $cached = get_transient('bk_search_index_v3');
        if (is_array($cached)) {
            return $cached;
        }
        $idx = array();
        $add = function ($type, $title, $sub, $url) use (&$idx) {
            $idx[] = array('type' => $type, 'title' => wp_strip_all_tags($title), 'sub' => wp_strip_all_tags($sub), 'url' => $url);
        };

        // 1. Core pages
        $pages = array(
            array('Profil Jurusan & Visi Keilmuan', 'Visi, misi & nilai CARA\'DE', '/profil/'),
            array('Direktori Dosen', '25 dosen, Guru Besar & Doktor', '/profil/dosen/'),
            array('Akreditasi Unggul LAMDIK', 'SK S1–S3 & 9 Kriteria', '/akreditasi/'),
            array('Kurikulum OBE 2025', '144 SKS, RPS & SDGs', '/akademik/kurikulum/'),
            array('Jadwal Seminar & Ujian', 'Proposal, hasil & skripsi', '/akademik/jadwal-ujian/'),
            array('Riset & Jurnal JKBK', 'Hibah PNBP, PkM & publikasi', '/mis-pnbp/'),
            array('Layanan Akademik & Persuratan', 'Template F-01..F-06 & SOP', '/layanan/akademik/'),
            array('Laboratorium BK', 'Bilik mikrokonseling & alat tes', '/laboratorium/'),
            array('E-Library & Repositori', 'Modul, buku ajar & artikel', '/e-library/'),
            array('Berita & Pengumuman', 'Warta jurusan terbaru', '/berita/'),
        );
        foreach ($pages as $p) {
            $add('Halaman', $p[0], $p[1], home_url($p[2]));
        }

        // 2. Lecturers
        $dosen = function_exists('bk_fip_load_dosen_data') ? bk_fip_load_dosen_data() : array();
        if (is_array($dosen)) {
            foreach ($dosen as $ds) {
                if (empty($ds['nama']) || empty($ds['slug'])) continue;
                $kp = !empty($ds['kepakaran']) && is_array($ds['kepakaran']) ? implode(', ', array_slice($ds['kepakaran'], 0, 2)) : ($ds['unit'] ?? '');
                $add('Dosen', $ds['nama'], $kp, home_url('/profil/dosen/' . $ds['slug'] . '/'));
            }
        }

        // 3. E-Library items
        if (function_exists('ppb_get_elibrary_items')) {
            foreach ((array) ppb_get_elibrary_items() as $it) {
                if (empty($it['title'])) continue;
                $add('E-Library', $it['title'], trim(($it['code'] ?? '') . ' · ' . ($it['author'] ?? ''), ' ·'), home_url('/e-library/') . '#' . rawurlencode($it['code'] ?? ''));
            }
        }

        // 4. Persuratan templates
        $forms = array(
            'F-01' => 'Pengajuan Judul Skripsi', 'F-02' => 'Kartu Bimbingan Skripsi', 'F-03' => 'Permohonan Seminar Proposal',
            'F-04' => 'Permohonan Seminar Hasil', 'F-05' => 'Permohonan Ujian Skripsi', 'F-06' => 'Surat Izin Penelitian',
        );
        foreach ($forms as $code => $t) {
            $add('Formulir', $code . ' · ' . $t, 'Template DOCX resmi', home_url('/layanan/akademik/'));
        }

        // 5. Lab BK
        $add('Lab BK', 'Peminjaman Alat Tes Psikologi', 'IST, CFIT, RMIB, SDS', home_url('/laboratorium/'));
        $add('Lab BK', 'Reservasi Bilik Mikrokonseling', '4 bilik one-way mirror', home_url('/laboratorium/'));
        $add('Layanan', 'Portal SIPADU Daring', 'Pengajuan surat & SK bimbingan', get_option('bk_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu'));

        // 6. Recent posts
        $recent = get_posts(array('numberposts' => 12, 'post_status' => 'publish', 'post_type' => array('post', 'berita'), 'suppress_filters' => false));
        foreach ($recent as $rp) {
            $add('Berita', get_the_title($rp), get_the_date('d M Y', $rp), get_permalink($rp));
        }

        set_transient('bk_search_index_v3', $idx, 6 * HOUR_IN_SECONDS);
        return $idx;
    }

    // Invalidate the search index when content changes.
    $bk_flush_search_index = function () { delete_transient('bk_search_index_v3'); };
    add_action('save_post', $bk_flush_search_index);
    add_action('update_option_ppb_elibrary_items', $bk_flush_search_index);
    add_action('update_option_bk_sipadu_url', $bk_flush_search_index);
}

function bk_fip_theme_setup() {

    add_theme_support('title-tag');

    add_theme_support('post-thumbnails');

    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));

    add_theme_support('responsive-embeds');



    register_nav_menus(array(

        'primary-menu' => __('Menu Utama (Navbar Mega Menu)', 'bk-fip'),

        'footer-menu'  => __('Menu Footer', 'bk-fip'),

    ));

}

add_action('after_setup_theme', 'bk_fip_theme_setup');



function bk_fip_enqueue_scripts() {

    // Google Fonts: Plus Jakarta Sans (Sans), Fraunces (Editorial Display Serif) & IBM Plex Mono

    wp_enqueue_style(

        'bk-fip-fonts',

        'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap',

        array(),

        null

    );



    // Tailwind CSS via CDN

    wp_enqueue_script('tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false);

    wp_add_inline_script('tailwind-cdn', "

        tailwind.config = {

            corePlugins: {

                preflight: false,

            },

            theme: {

                extend: {

                    fontFamily: {

                        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],

                        display: ['Fraunces', 'Georgia', 'serif'],

                        serif: ['Fraunces', 'Georgia', 'serif'],

                        mono: ['IBM Plex Mono', 'monospace'],

                    },

                    colors: {

                        prodi: {

                            dark: '#0D3B22',

                            main: '#185333',

                            light: '#237346',

                            lighter: '#E8F0EA',

                            gold: '#F5B82E',

                            goldHover: '#E5A93B',

                        },

                        surface: {

                            warm: '#FAF8F5',

                            sage: '#E8F0EA',

                        }

                    },

                    boxShadow: {

                        'soft': '0 1px 2px rgba(21, 40, 10, 0.04), 0 8px 24px -12px rgba(21, 40, 10, 0.12)',

                        'soft-lg': '0 2px 4px rgba(21, 40, 10, 0.05), 0 24px 48px -20px rgba(21, 40, 10, 0.22)',

                    }

                }

            }

        }

    ");



    // Lucide Icons

    wp_enqueue_script('lucide-icons', 'https://unpkg.com/lucide@latest', array(), null, true);

    wp_add_inline_script('lucide-icons', 'document.addEventListener("DOMContentLoaded", function() { if (window.lucide) { lucide.createIcons(); } });');

}

add_action('wp_enqueue_scripts', 'bk_fip_enqueue_scripts', 10);

function bk_fip_enqueue_theme_stylesheet() {
    $style_path = get_stylesheet_directory() . '/style.css';
    $style_ver  = file_exists($style_path) ? (string) filemtime($style_path) : wp_get_theme()->get('Version');
    wp_enqueue_style('bk-fip-style', get_stylesheet_uri(), array('bk-fip-fonts'), $style_ver);
}

add_action('wp_enqueue_scripts', 'bk_fip_enqueue_theme_stylesheet', 30);



/**

 * --------------------------------------------------------------------------

 * 1. CUSTOM POST TYPE: DIREKTORI DOSEN & STAF

 * --------------------------------------------------------------------------

 */

function bk_fip_register_cpt_dosen() {

    $labels = array(

        'name'               => 'Dosen & Tendik',

        'singular_name'      => 'Dosen',

        'menu_name'          => 'Direktori Dosen',

        'add_new'            => 'Tambah Dosen',

        'add_new_item'       => 'Tambah Dosen Baru',

        'edit_item'          => 'Edit Data Dosen',

        'new_item'           => 'Dosen Baru',

        'view_item'          => 'Lihat Profil Dosen',

        'search_items'       => 'Cari Dosen',

        'not_found'          => 'Tidak ada data dosen',

        'not_found_in_trash' => 'Tidak ada dosen di tempat sampah',

    );



    $args = array(

        'labels'              => $labels,

        'public'              => true,

        'has_archive'         => true,

        'publicly_queryable'  => true,

        'show_ui'             => true,

        'show_in_menu'        => false,

        'menu_icon'           => 'dashicons-welcome-learn-status',

        'supports'            => array('title', 'thumbnail'),

        'rewrite'             => array('slug' => 'profil/dosen'),

        'show_in_rest'        => true,

    );



    register_post_type('dosen', $args);

}

add_action('init', 'bk_fip_register_cpt_dosen');



/**

 * Formulir Meta Box Data Dosen di Dashboard Admin

 */

function bk_fip_add_dosen_metabox() {

    add_meta_box(

        'bk_dosen_metabox',

        'Informasi Kepakaran & Akademik Dosen',

        'bk_fip_render_dosen_metabox',

        'dosen',

        'normal',

        'high'

    );

}

add_action('add_meta_boxes', 'bk_fip_add_dosen_metabox');



function bk_fip_render_dosen_metabox($post) {

    wp_nonce_field('bk_dosen_nonce_action', 'bk_dosen_nonce');



    $nip         = get_post_meta($post->ID, '_dosen_nip', true);

    $tier        = get_post_meta($post->ID, '_dosen_tier', true) ?: 'dosen';

    $jabatan     = get_post_meta($post->ID, '_dosen_jabatan', true);

    $kepakaran   = get_post_meta($post->ID, '_dosen_kepakaran', true);

    $publikasi   = get_post_meta($post->ID, '_dosen_publikasi', true) ?: '0';

    $sinta_url   = get_post_meta($post->ID, '_dosen_sinta_url', true);

    ?>

    <div style="display:grid; grid-template-columns: 1fr 1fr; gap: 15px; padding: 10px 0;">

        <div>

            <label style="display:block; font-weight:600; margin-bottom: 5px;">NIP / NIDN:</label>

            <input type="text" name="dosen_nip" value="<?php echo esc_attr($nip); ?>" style="width:100%;" placeholder="Contoh: 196805121993032001">

        </div>

        <div>

            <label style="display:block; font-weight:600; margin-bottom: 5px;">Tingkatan / Tier Dosen:</label>

            <select name="dosen_tier" style="width:100%;">

                <option value="prof" <?php selected($tier, 'prof'); ?>>Guru Besar / Prof (Badge Emas)</option>

                <option value="doktor" <?php selected($tier, 'doktor'); ?>>Doktor (Dr.) (Badge Hijau)</option>

                <option value="dosen" <?php selected($tier, 'dosen'); ?>>Dosen PPB (Badge Standar)</option>

            </select>

        </div>

        <div>

            <label style="display:block; font-weight:600; margin-bottom: 5px;">Jabatan Fungsional / Peran:</label>

            <input type="text" name="dosen_jabatan" value="<?php echo esc_attr($jabatan); ?>" style="width:100%;" placeholder="Contoh: Guru Besar Konseling Perkembangan">

        </div>

        <div>

            <label style="display:block; font-weight:600; margin-bottom: 5px;">Jumlah Karya Publikasi Ilmiah:</label>

            <input type="number" name="dosen_publikasi" value="<?php echo esc_attr($publikasi); ?>" style="width:100%;" placeholder="Contoh: 18">

        </div>

        <div style="grid-column: span 2;">

            <label style="display:block; font-weight:600; margin-bottom: 5px;">Bidang Kepakaran (Pisahkan dengan koma):</label>

            <input type="text" name="dosen_kepakaran" value="<?php echo esc_attr($kepakaran); ?>" style="width:100%;" placeholder="Contoh: Konseling Sebaya, Psikometri BK, Manajemen Layanan">

            <small style="color:#666;">Kata kunci ini akan tampil sebagai tag/chip di bawah kartu dosen pada beranda.</small>

        </div>

        <div style="grid-column: span 2;">

            <label style="display:block; font-weight:600; margin-bottom: 5px;">Tautan Profil SINTA / Google Scholar:</label>

            <input type="url" name="dosen_sinta_url" value="<?php echo esc_attr($sinta_url); ?>" style="width:100%;" placeholder="https://sinta.kemdiktisaintek.go.id/authors/profile/...">

        </div>

    </div>

    <?php
    $foto_link = get_post_meta($post->ID, '_dosen_foto_link', true);
    $foto_prev = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($foto_link) : $foto_link;
    wp_enqueue_media();
    ?>
    <div style="display:grid; grid-template-columns: 96px 1fr; gap: 16px; align-items:center; margin-top: 12px; padding: 14px; background:#FAF8F5; border:1px solid #E6E2DA; border-radius:10px;">
        <div style="width:96px;height:120px;border-radius:10px;overflow:hidden;background:#0D3B22;border:2px solid #F5B82E;">
            <img id="bk-dosen-foto-preview" src="<?php echo esc_url($foto_prev); ?>" alt="" style="width:100%;height:100%;object-fit:cover;object-position:50% 18%;<?php echo $foto_prev ? '' : 'display:none;'; ?>">
        </div>
        <div>
            <label style="display:block; font-weight:600; margin-bottom: 5px;">Foto Potret Dosen (URL / Media Library / assets/img/...):</label>
            <div style="display:flex; gap:8px;">
                <input type="text" id="bk-dosen-foto-link" name="dosen_foto_link" value="<?php echo esc_attr($foto_link); ?>" style="flex:1;" placeholder="https://... atau assets/img/dosen-nama.jpg">
                <button type="button" class="button" id="bk-dosen-foto-pick">Pilih dari Media</button>
                <button type="button" class="button-link-delete" id="bk-dosen-foto-clear">Hapus</button>
            </div>
            <small style="color:#666;">Potret rasio 4:5 (mis. 480×600px) dengan wajah di sepertiga atas. Tampil di kartu direktori (54px, crop 50% 22%) dan dossier profil (96×120px, crop 50% 18%). Jika kosong/gagal dimuat, inisial nama ditampilkan otomatis. Featured Image tetap dipakai sebagai cadangan.</small>
        </div>
    </div>
    <script>
    (function(){
        var input = document.getElementById('bk-dosen-foto-link'), prev = document.getElementById('bk-dosen-foto-preview');
        var base = <?php echo wp_json_encode(trailingslashit(get_template_directory_uri())); ?>;
        function upd(v){ v = (v||'').trim(); if (v.indexOf('assets/') === 0) v = base + v; if (v) { prev.src = v; prev.style.display = 'block'; } else { prev.style.display = 'none'; } }
        input.addEventListener('input', function(){ upd(input.value); });
        document.getElementById('bk-dosen-foto-clear').addEventListener('click', function(){ input.value = ''; upd(''); });
        document.getElementById('bk-dosen-foto-pick').addEventListener('click', function(e){
            e.preventDefault();
            if (typeof wp === 'undefined' || !wp.media) return;
            var frame = wp.media({ title: 'Pilih Foto Dosen', button: { text: 'Gunakan Foto Ini' }, library: { type: 'image' }, multiple: false });
            frame.on('select', function(){ var att = frame.state().get('selection').first().toJSON(); input.value = att.url; upd(att.url); });
            frame.open();
        });
    })();
    </script>

    <?php

}



function bk_fip_save_dosen_metabox($post_id) {

    if (!isset($_POST['bk_dosen_nonce']) || !wp_verify_nonce($_POST['bk_dosen_nonce'], 'bk_dosen_nonce_action')) {

        return;

    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (!current_user_can('edit_post', $post_id)) return;



    $fields = array('nip', 'tier', 'jabatan', 'kepakaran', 'publikasi', 'sinta_url');

    foreach ($fields as $f) {

        if (isset($_POST['dosen_' . $f])) {

            update_post_meta($post_id, '_dosen_' . $f, sanitize_text_field($_POST['dosen_' . $f]));

        }

    }

    if (isset($_POST['dosen_foto_link'])) {
        $foto = function_exists('bk_sanitize_asset_or_url') ? bk_sanitize_asset_or_url(wp_unslash($_POST['dosen_foto_link'])) : esc_url_raw(wp_unslash($_POST['dosen_foto_link']));
        update_post_meta($post_id, '_dosen_foto_link', $foto);
    }

}

add_action('save_post_dosen', 'bk_fip_save_dosen_metabox');





/**

 * --------------------------------------------------------------------------

 * 2. MENU ADMIN: PENGATURAN BERANDA (TEKS & KONTEN LANDING PAGE)

 * --------------------------------------------------------------------------

 */

// Settings Menu kini terpusat di dalam 🏛️ Portal PPB FIP
function bk_fip_register_settings_menu() {}



function bk_fip_render_settings_page() {
    if (!current_user_can('manage_options')) return;

    $defaults = function_exists('bk_fip_get_homepage_defaults') ? bk_fip_get_homepage_defaults() : array();

    // ---------------------------------------------------------------------
    // RESET ke default (opsi besar dihapus, nilai bawaan kembali dipakai)
    // ---------------------------------------------------------------------
    if (isset($_POST['bk_reset_settings']) && check_admin_referer('bk_settings_verify')) {
        $reset_section = sanitize_key($_POST['bk_reset_section'] ?? 'all');
        $groups = array(
            'degrees' => array('bk_degree_s1', 'bk_degree_s2', 'bk_degree_s3'),
            'video'   => array('bk_video_chapters'),
            'portal'  => array('bk_portal_cards', 'bk_admisi_title', 'bk_admisi_desc', 'bk_admisi_btn', 'bk_admisi_url'),
            'footer'  => array('bk_footer_address', 'bk_footer_akreditasi', 'bk_footer_copyright'),
        );
        $to_delete = ($reset_section === 'all') ? call_user_func_array('array_merge', array_values($groups)) : ($groups[$reset_section] ?? array());
        foreach ($to_delete as $opt) {
            delete_option($opt);
        }
        delete_transient('bk_search_index_v3');
        echo '<div class="notice notice-warning is-dismissible"><p>Bagian pengaturan berhasil dikembalikan ke nilai bawaan.</p></div>';
    }

    // ---------------------------------------------------------------------
    // SIMPAN
    // ---------------------------------------------------------------------
    if (isset($_POST['bk_save_settings']) && check_admin_referer('bk_settings_verify')) {
        $text_options = array(
            'bk_hero_badge', 'bk_hero_h1_part1', 'bk_hero_h1_highlight', 'bk_hero_h1_part2',
            'bk_stat_akreditasi_val', 'bk_stat_tradisi_val', 'bk_stat_dosen_val', 'bk_stat_riset_val',
            'bk_kajur_nama', 'bk_kajur_jabatan', 'bk_top_strip_badge', 'bk_top_strip_text',
            'bk_admisi_title', 'bk_admisi_btn', 'bk_footer_akreditasi', 'bk_footer_copyright',
        );
        foreach ($text_options as $opt) {
            if (isset($_POST[$opt])) {
                update_option($opt, sanitize_text_field(wp_unslash($_POST[$opt])));
            }
        }
        $textarea_options = array(
            'bk_hero_desc', 'bk_stat_akreditasi_desc', 'bk_stat_tradisi_desc', 'bk_stat_dosen_desc',
            'bk_stat_riset_desc', 'bk_kajur_quote', 'bk_admisi_desc', 'bk_footer_address',
        );
        foreach ($textarea_options as $opt) {
            if (isset($_POST[$opt])) {
                update_option($opt, sanitize_textarea_field(wp_unslash($_POST[$opt])));
            }
        }
        $url_options = array('bk_portal_auth_url', 'bk_sipadu_url', 'bk_admisi_url');
        foreach ($url_options as $opt) {
            if (isset($_POST[$opt])) {
                update_option($opt, esc_url_raw(wp_unslash($_POST[$opt])));
            }
        }

        // Jenjang S1–S3 (array, autoload = false)
        $degree_fields = array('tab_label', 'kkni', 'title', 'summary', 'gelar', 'beban', 'kurikulum', 'sk', 'kompetensi', 'url');
        foreach (array('s1', 's2', 's3') as $k) {
            if (empty($_POST['bk_degree'][$k]) || !is_array($_POST['bk_degree'][$k])) continue;
            $raw = wp_unslash($_POST['bk_degree'][$k]);
            $clean = array('code' => strtoupper($k));
            foreach ($degree_fields as $f) {
                $v = $raw[$f] ?? '';
                if ($f === 'summary' || $f === 'kompetensi') {
                    $clean[$f] = sanitize_textarea_field($v);
                } elseif ($f === 'url') {
                    $clean[$f] = bk_sanitize_asset_or_url($v);
                } else {
                    $clean[$f] = sanitize_text_field($v);
                }
            }
            update_option('bk_degree_' . $k, $clean, false);
        }

        // Video chapters (3 item)
        if (!empty($_POST['bk_video']) && is_array($_POST['bk_video'])) {
            $chapters = array();
            foreach (array_slice(wp_unslash($_POST['bk_video']), 0, 3) as $i => $c) {
                $chapters[] = array(
                    'num'       => sprintf('%02d', $i + 1),
                    'duration'  => sanitize_text_field($c['duration'] ?? ''),
                    'badge'     => sanitize_text_field($c['badge'] ?? ''),
                    'title'     => sanitize_text_field($c['title'] ?? ''),
                    'desc'      => sanitize_textarea_field($c['desc'] ?? ''),
                    'date'      => sanitize_text_field($c['date'] ?? ''),
                    'thumb'     => bk_sanitize_asset_or_url($c['thumb'] ?? ''),
                    'video_url' => function_exists('ppb_clean_document_url') ? ppb_clean_document_url($c['video_url'] ?? '') : esc_url_raw($c['video_url'] ?? ''),
                );
            }
            if (count($chapters) === 3) {
                update_option('bk_video_chapters', $chapters, false);
            }
        }

        // Portal cards (6 item)
        if (!empty($_POST['bk_portal']) && is_array($_POST['bk_portal'])) {
            $cards = array();
            foreach (array_slice(wp_unslash($_POST['bk_portal']), 0, 6) as $i => $c) {
                $cards[] = array(
                    'code'  => sanitize_text_field($c['code'] ?? sprintf('P-%02d', $i + 1)),
                    'title' => sanitize_text_field($c['title'] ?? ''),
                    'desc'  => sanitize_textarea_field($c['desc'] ?? ''),
                    'url'   => bk_sanitize_asset_or_url($c['url'] ?? ''),
                    'icon'  => sanitize_key($c['icon'] ?? 'arrow-right'),
                );
            }
            if (count($cards) === 6) {
                update_option('bk_portal_cards', $cards, false);
            }
        }

        delete_transient('bk_search_index_v3');
        echo '<div class="notice notice-success is-dismissible"><p>Pengaturan beranda berhasil disimpan!</p></div>';
    }

    // ---------------------------------------------------------------------
    // Nilai saat ini
    // ---------------------------------------------------------------------
    $cfg = bk_fip_get_homepage_config();

    $hero_badge = get_option('bk_hero_badge', 'Program Studi S1 · Terakreditasi Unggul (LAMDIK)');
    $h1_p1 = get_option('bk_hero_h1_part1', 'Tempat di mana integritas bertumbuh menjadi');
    $h1_hl = get_option('bk_hero_h1_highlight', 'keunggulan');
    $h1_p2 = get_option('bk_hero_h1_part2', 'dan setiap calon konselor menemukan panggilannya.');
    $hero_desc = get_option('bk_hero_desc', 'Program Studi Bimbingan dan Konseling, Jurusan Psikologi Pendidikan dan Bimbingan, FIP Universitas Negeri Makassar menyelenggarakan pendidikan akademik adaptif OBE yang memadukan keluhuran humanistik, riset saintifik berbasis bukti, dan kepekaan sosial-budaya Nusantara.');

    $stats = array(
        array('key' => 'akreditasi', 'label' => '01 // MUTU (Akreditasi)', 'val' => get_option('bk_stat_akreditasi_val', 'Unggul'), 'desc' => get_option('bk_stat_akreditasi_desc', 'Sertifikasi mutu tertinggi Lembaga Akreditasi Mandiri Kependidikan (LAMDIK).')),
        array('key' => 'tradisi', 'label' => '02 // TRADISI (Sejak)', 'val' => get_option('bk_stat_tradisi_val', '1969'), 'desc' => get_option('bk_stat_tradisi_desc', '55+ tahun mendidik calon konselor, guru pembimbing, dan pendidik bangsa.')),
        array('key' => 'dosen', 'label' => '03 // PENGAJAR (Dosen)', 'val' => get_option('bk_stat_dosen_val', '25+ Dosen'), 'desc' => get_option('bk_stat_dosen_desc', 'Guru Besar (Prof.), Doktor (Dr.), dan Magister berkualifikasi pendidik profesional.')),
        array('key' => 'riset', 'label' => '04 // JENJANG / RISET', 'val' => get_option('bk_stat_riset_val', '500+'), 'desc' => get_option('bk_stat_riset_desc', 'Karya ilmiah terindeks SINTA/Scopus, buku ajar, dan sertifikasi hak cipta HKI.')),
    );

    $kajur_nama = get_option('bk_kajur_nama', 'Dr. Suciani Latif, M.Pd.');
    $kajur_jabatan = get_option('bk_kajur_jabatan', 'Ketua Jurusan PPB FIP UNM');
    $kajur_quote = get_option('bk_kajur_quote', 'Pendidikan konseling di UNM dirancang untuk melahirkan sarjana yang berintegritas moral, peka terhadap keragaman sosio-kultural, serta mahir memanfaatkan teknologi psikoedukasi dan telekonseling untuk menjawab dinamika kesehatan mental era modern.');

    $card = 'background:#fff; border:1px solid #E6E2DA; border-radius:12px; padding:20px 22px; margin-bottom:22px; box-shadow:0 1px 3px rgba(0,0,0,0.04);';
    $h2   = 'font-size:17px; color:#0D3B22; border-bottom:2px solid #E8F0EA; padding-bottom:10px; margin:0 0 14px; display:flex; align-items:center; justify-content:space-between; gap:12px;';
    $lbl  = 'display:block; font-weight:600; font-size:12px; color:#374151; margin:8px 0 4px;';
    $mono = 'font-family:"IBM Plex Mono",monospace; font-size:11px; color:#92400E; letter-spacing:0.06em;';

    $reset_btn = function ($section, $label = 'Reset bagian ini') {
        return '<button type="submit" name="bk_reset_settings" value="1" class="button button-link-delete" onclick="this.form.bk_reset_section.value=\'' . esc_attr($section) . '\';return confirm(\'Kembalikan bagian ini ke nilai bawaan?\');" style="font-size:12px;">' . esc_html($label) . '</button>';
    };
    wp_enqueue_media();
    ?>
    <div class="wrap" style="max-width: 1040px;">
        <h1 style="display:flex; align-items:center; gap:10px; margin-bottom:8px;">
            <span class="dashicons dashicons-admin-appearance" style="font-size:30px; width:30px; height:30px; color:#0D3B22;"></span>
            Pengaturan Beranda &amp; Situs (Editorial Calm v3)
        </h1>
        <p style="color:#555; margin:0 0 18px;">
            Ubah seluruh konten beranda — strip atas, angka reputasi, tab jenjang S1–S3, sambutan Kajur, video profil, 6 kartu portal, strip admisi, dan footer — tanpa menyentuh kode.
            Slide <strong>Spotlight</strong> diatur di menu <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-spotlight-beranda')); ?>">Spotlight Beranda</a>, menu navigasi 4 pilar di <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-mega-menu-manager')); ?>">Mega Menu</a>.
        </p>

        <div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:18px;">
            <?php foreach (array('#bk-s-strip' => '1. Strip & Portal', '#bk-s-ledger' => '2. Angka Reputasi', '#bk-s-degree' => '3. Jenjang S1–S3', '#bk-s-kajur' => '4. Sambutan Kajur', '#bk-s-video' => '5. Video Profil', '#bk-s-portal' => '6. Kartu Portal & Admisi', '#bk-s-footer' => '7. Footer', '#bk-s-legacy' => '8. Teks Intro') as $href => $t) : ?>
                <a href="<?php echo esc_attr($href); ?>" class="button" style="border-radius:999px;"><?php echo esc_html($t); ?></a>
            <?php endforeach; ?>
        </div>

        <form method="post" action="">
            <?php wp_nonce_field('bk_settings_verify'); ?>
            <input type="hidden" name="bk_reset_section" value="all">

            <!-- 1. STRIP ATAS & PORTAL -->
            <div id="bk-s-strip" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>1. Strip Atas BK-Connect &amp; Tautan Portal</span></h2>
                <div style="display:grid; grid-template-columns: 200px 1fr; gap:12px;">
                    <div><label style="<?php echo esc_attr($lbl); ?>">Badge Strip</label><input name="bk_top_strip_badge" type="text" value="<?php echo esc_attr($cfg['top_strip_badge']); ?>" style="width:100%;"></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">Teks Strip (baris tipis paling atas header)</label><input name="bk_top_strip_text" type="text" value="<?php echo esc_attr($cfg['top_strip_text']); ?>" style="width:100%;"></div>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div><label style="<?php echo esc_attr($lbl); ?>">URL Masuk Portal BK-Connect</label><input name="bk_portal_auth_url" type="url" value="<?php echo esc_attr($cfg['portal_auth_url']); ?>" style="width:100%;"><p class="description">Dipakai tautan "Masuk" pada strip atas &amp; footer.</p></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">URL Portal SIPADU Daring</label><input name="bk_sipadu_url" type="url" value="<?php echo esc_attr($cfg['sipadu_url']); ?>" style="width:100%;"><p class="description">Tampil di mega menu <em>Layanan &amp; Warta → Portal, Advokasi &amp; Mutu</em> dan footer (bukan tombol header).</p></div>
                </div>
            </div>

            <!-- 2. LEDGER -->
            <div id="bk-s-ledger" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>2. Angka Reputasi (Ledger 4 Kolom di bawah Spotlight)</span></h2>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:18px;">
                    <?php foreach ($stats as $st) : ?>
                        <div style="background:#FAF8F5; border:1px solid #E6E2DA; border-radius:10px; padding:12px 14px;">
                            <div style="<?php echo esc_attr($mono); ?>"><?php echo esc_html($st['label']); ?></div>
                            <input name="bk_stat_<?php echo esc_attr($st['key']); ?>_val" type="text" value="<?php echo esc_attr($st['val']); ?>" style="width:100%; font-weight:700; margin:6px 0;">
                            <textarea name="bk_stat_<?php echo esc_attr($st['key']); ?>_desc" rows="2" style="width:100%;"><?php echo esc_textarea($st['desc']); ?></textarea>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 3. JENJANG -->
            <div id="bk-s-degree" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>3. Tab Jenjang Studi (S1 Sarjana · S2 Magister · S3 Doktoral)</span><?php echo $reset_btn('degrees'); ?></h2>
                <?php foreach ($cfg['degrees'] as $k => $dg) : ?>
                    <details <?php echo $k === 's1' ? 'open' : ''; ?> style="border:1px solid #D4E3D8; border-radius:10px; padding:10px 14px; margin-bottom:10px; background:#F2F6F3;">
                        <summary style="cursor:pointer; font-weight:700; color:#0D3B22;"><?php echo esc_html(strtoupper($k) . ' — ' . $dg['title']); ?></summary>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-top:8px;">
                            <div><label style="<?php echo esc_attr($lbl); ?>">Label Tab</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][tab_label]" value="<?php echo esc_attr($dg['tab_label']); ?>" style="width:100%;"></div>
                            <div><label style="<?php echo esc_attr($lbl); ?>">Kicker (KKNI · Akreditasi)</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][kkni]" value="<?php echo esc_attr($dg['kkni']); ?>" style="width:100%;"></div>
                            <div style="grid-column:span 2;"><label style="<?php echo esc_attr($lbl); ?>">Judul Program</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][title]" value="<?php echo esc_attr($dg['title']); ?>" style="width:100%;"></div>
                            <div style="grid-column:span 2;"><label style="<?php echo esc_attr($lbl); ?>">Ringkasan</label><textarea name="bk_degree[<?php echo esc_attr($k); ?>][summary]" rows="3" style="width:100%;"><?php echo esc_textarea($dg['summary']); ?></textarea></div>
                            <div><label style="<?php echo esc_attr($lbl); ?>">Gelar</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][gelar]" value="<?php echo esc_attr($dg['gelar']); ?>" style="width:100%;"></div>
                            <div><label style="<?php echo esc_attr($lbl); ?>">Beban Studi</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][beban]" value="<?php echo esc_attr($dg['beban']); ?>" style="width:100%;"></div>
                            <div><label style="<?php echo esc_attr($lbl); ?>">Kurikulum</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][kurikulum]" value="<?php echo esc_attr($dg['kurikulum']); ?>" style="width:100%;"></div>
                            <div><label style="<?php echo esc_attr($lbl); ?>">SK Akreditasi</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][sk]" value="<?php echo esc_attr($dg['sk']); ?>" style="width:100%;"></div>
                            <div style="grid-column:span 2;"><label style="<?php echo esc_attr($lbl); ?>">Kompetensi Utama (1 per baris, maks. 3)</label><textarea name="bk_degree[<?php echo esc_attr($k); ?>][kompetensi]" rows="3" style="width:100%;"><?php echo esc_textarea($dg['kompetensi']); ?></textarea></div>
                            <div style="grid-column:span 2;"><label style="<?php echo esc_attr($lbl); ?>">Tautan Tombol "Lihat Kurikulum"</label><input type="text" name="bk_degree[<?php echo esc_attr($k); ?>][url]" value="<?php echo esc_attr($dg['url']); ?>" style="width:100%;"></div>
                        </div>
                    </details>
                <?php endforeach; ?>
            </div>

            <!-- 4. KAJUR -->
            <div id="bk-s-kajur" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>4. Sambutan &amp; Kutipan Ketua Jurusan (Pull-Quote)</span></h2>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div><label style="<?php echo esc_attr($lbl); ?>">Nama Ketua Jurusan</label><input name="bk_kajur_nama" type="text" value="<?php echo esc_attr($kajur_nama); ?>" style="width:100%;"></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">Jabatan</label><input name="bk_kajur_jabatan" type="text" value="<?php echo esc_attr($kajur_jabatan); ?>" style="width:100%;"></div>
                </div>
                <label style="<?php echo esc_attr($lbl); ?>">Teks Kutipan</label>
                <textarea name="bk_kajur_quote" rows="4" style="width:100%;"><?php echo esc_textarea($kajur_quote); ?></textarea>
            </div>

            <!-- 5. VIDEO -->
            <div id="bk-s-video" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>5. Video Profil — 3 Bab (Band Hijau Tua &amp; Modal)</span><?php echo $reset_btn('video'); ?></h2>
                <p class="description" style="margin-top:0;">URL video mendukung YouTube / Google Drive / Vimeo / file MP4. Thumbnail mendukung unggah Media Library maupun link Google Drive.</p>
                <?php foreach ($cfg['video_chapters'] as $i => $vc) : $n = 'bk_video[' . $i . ']'; ?>
                    <div style="display:grid; grid-template-columns: 120px 1fr; gap:14px; border-top:1px dashed #E6E2DA; padding-top:12px; margin-top:12px;">
                        <div>
                            <div style="<?php echo esc_attr($mono); ?>">BAB <?php echo esc_html(sprintf('%02d', $i + 1)); ?></div>
                            <img class="bk-media-prev" src="<?php echo esc_url(function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($vc['thumb'] ?? '') : bk_resolve_theme_asset_url($vc['thumb'] ?? '')); ?>" alt="" style="width:120px;height:72px;object-fit:cover;border-radius:8px;margin-top:6px;background:#E8F0EA;">
                        </div>
                        <div style="display:grid; grid-template-columns: 1fr 120px; gap:8px;">
                            <input type="text" name="<?php echo esc_attr($n); ?>[title]" value="<?php echo esc_attr($vc['title'] ?? ''); ?>" placeholder="Judul bab" style="width:100%; font-weight:600;">
                            <input type="text" name="<?php echo esc_attr($n); ?>[duration]" value="<?php echo esc_attr($vc['duration'] ?? ''); ?>" placeholder="03:45">
                            <input type="text" name="<?php echo esc_attr($n); ?>[badge]" value="<?php echo esc_attr($vc['badge'] ?? ''); ?>" placeholder="Bagian 1 · ..." style="width:100%;">
                            <input type="text" name="<?php echo esc_attr($n); ?>[date]" value="<?php echo esc_attr($vc['date'] ?? ''); ?>" placeholder="Keterangan">
                            <textarea name="<?php echo esc_attr($n); ?>[desc]" rows="2" style="grid-column:span 2; width:100%;" placeholder="Deskripsi"><?php echo esc_textarea($vc['desc'] ?? ''); ?></textarea>
                            <div style="grid-column:span 2; display:flex; gap:6px; flex-wrap:wrap;">
                                <input type="text" class="bk-media-input" name="<?php echo esc_attr($n); ?>[thumb]" value="<?php echo esc_attr($vc['thumb'] ?? ''); ?>" placeholder="Thumbnail: Link Google Drive, URL Media Library, atau assets/img/..." style="flex:1;min-width:220px;">
                                <button type="button" class="button bk-media-pick">📷 Unggah / Pilih Gambar</button>
                            </div>
                            <div style="grid-column:span 2; display:flex; gap:6px; flex-wrap:wrap;">
                                <input type="text" class="bk-video-input" name="<?php echo esc_attr($n); ?>[video_url]" value="<?php echo esc_attr($vc['video_url'] ?? ''); ?>" placeholder="URL Video (YouTube / Google Drive / Vimeo / Upload MP4)" style="flex:1;min-width:220px;">
                                <button type="button" class="button bk-video-media-pick">🎬 Unggah / Pilih Video</button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- 6. PORTAL CARDS & ADMISI -->
            <div id="bk-s-portal" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>6. Grid 6 Kartu Portal (3×2) &amp; Strip Admisi</span><?php echo $reset_btn('portal'); ?></h2>
                <p class="description" style="margin-top:0;">Ikon memakai nama <a href="https://lucide.dev/icons/" target="_blank" rel="noopener">Lucide</a> (mis. <code>layers</code>, <code>file-text</code>, <code>flask-conical</code>, <code>library</code>, <code>users</code>, <code>award</code>).</p>
                <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:12px;">
                    <?php foreach ($cfg['portal_cards'] as $i => $pc) : $n = 'bk_portal[' . $i . ']'; ?>
                        <div style="background:#0D3B22; border-radius:10px; padding:12px; border-bottom:4px solid #F5B82E;">
                            <div style="display:flex; gap:6px; margin-bottom:6px;">
                                <input type="text" name="<?php echo esc_attr($n); ?>[code]" value="<?php echo esc_attr($pc['code'] ?? ''); ?>" style="width:64px; font-family:monospace;">
                                <input type="text" name="<?php echo esc_attr($n); ?>[icon]" value="<?php echo esc_attr($pc['icon'] ?? ''); ?>" placeholder="ikon" style="flex:1;">
                            </div>
                            <input type="text" name="<?php echo esc_attr($n); ?>[title]" value="<?php echo esc_attr($pc['title'] ?? ''); ?>" placeholder="Judul" style="width:100%; font-weight:700; margin-bottom:6px;">
                            <textarea name="<?php echo esc_attr($n); ?>[desc]" rows="2" style="width:100%; margin-bottom:6px;"><?php echo esc_textarea($pc['desc'] ?? ''); ?></textarea>
                            <input type="text" name="<?php echo esc_attr($n); ?>[url]" value="<?php echo esc_attr($pc['url'] ?? ''); ?>" placeholder="/halaman/ atau https://" style="width:100%;">
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px; margin-top:16px; padding-top:14px; border-top:1px dashed #E6E2DA;">
                    <div><label style="<?php echo esc_attr($lbl); ?>">Judul Strip Admisi</label><input name="bk_admisi_title" type="text" value="<?php echo esc_attr($cfg['admisi_title']); ?>" style="width:100%;"></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">Teks Tombol</label><input name="bk_admisi_btn" type="text" value="<?php echo esc_attr($cfg['admisi_btn']); ?>" style="width:100%;"></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">Deskripsi</label><textarea name="bk_admisi_desc" rows="2" style="width:100%;"><?php echo esc_textarea($cfg['admisi_desc']); ?></textarea></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">URL Tombol</label><input name="bk_admisi_url" type="url" value="<?php echo esc_attr($cfg['admisi_url']); ?>" style="width:100%;"></div>
                </div>
            </div>

            <!-- 7. FOOTER -->
            <div id="bk-s-footer" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>7. Footer Situs</span><?php echo $reset_btn('footer'); ?></h2>
                <label style="<?php echo esc_attr($lbl); ?>">Alamat Kampus</label>
                <textarea name="bk_footer_address" rows="2" style="width:100%;"><?php echo esc_textarea($cfg['footer_address']); ?></textarea>
                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:12px;">
                    <div><label style="<?php echo esc_attr($lbl); ?>">Baris Akreditasi</label><input name="bk_footer_akreditasi" type="text" value="<?php echo esc_attr($cfg['footer_akreditasi']); ?>" style="width:100%;"></div>
                    <div><label style="<?php echo esc_attr($lbl); ?>">Teks Hak Cipta</label><input name="bk_footer_copyright" type="text" value="<?php echo esc_attr($cfg['footer_copyright']); ?>" style="width:100%;"></div>
                </div>
            </div>

            <!-- 8. LEGACY HERO TEXT -->
            <div id="bk-s-legacy" style="<?php echo esc_attr($card); ?>">
                <h2 style="<?php echo esc_attr($h2); ?>"><span>8. Teks Intro Beranda (cadangan bila Spotlight kosong)</span></h2>
                <label style="<?php echo esc_attr($lbl); ?>">Badge</label>
                <input name="bk_hero_badge" type="text" value="<?php echo esc_attr($hero_badge); ?>" style="width:100%;">
                <label style="<?php echo esc_attr($lbl); ?>">Judul (awal · kata sorot emas · penutup)</label>
                <input name="bk_hero_h1_part1" type="text" value="<?php echo esc_attr($h1_p1); ?>" style="width:100%; margin-bottom:6px;">
                <input name="bk_hero_h1_highlight" type="text" value="<?php echo esc_attr($h1_hl); ?>" style="width:100%; margin-bottom:6px; font-style:italic;">
                <input name="bk_hero_h1_part2" type="text" value="<?php echo esc_attr($h1_p2); ?>" style="width:100%;">
                <label style="<?php echo esc_attr($lbl); ?>">Paragraf</label>
                <textarea name="bk_hero_desc" rows="3" style="width:100%;"><?php echo esc_textarea($hero_desc); ?></textarea>
            </div>

            <div style="position:sticky; bottom:0; background:rgba(250,248,245,0.96); border-top:1px solid #E6E2DA; padding:12px 0; display:flex; gap:12px; align-items:center; z-index:5;">
                <input type="submit" name="bk_save_settings" class="button button-primary" value="Simpan Semua Perubahan" style="background:#0D3B22; border-color:#0D3B22; padding:6px 24px; font-weight:700; border-radius:8px;">
                <button type="submit" name="bk_reset_settings" value="1" class="button" onclick="this.form.bk_reset_section.value='all';return confirm('Kembalikan Jenjang, Video, Kartu Portal, Admisi & Footer ke nilai bawaan?');">Reset Semua ke Default</button>
                <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button">Lihat Beranda ↗</a>
            </div>
        </form>
    </div>
    <script>
    (function(){
        var base = <?php echo wp_json_encode(trailingslashit(get_template_directory_uri())); ?>;
        function resolvePrev(v) {
            v = (v || '').trim();
            if (!v) return '';
            var gm = v.match(/drive\.google\.com\/(?:file\/d\/|thumbnail\?id=|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]{10,})/i);
            if (gm) return 'https://drive.google.com/thumbnail?id=' + gm[1] + '&sz=w1200';
            return v.indexOf('assets/') === 0 ? base + v : v;
        }
        document.querySelectorAll('.bk-media-pick').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                if (typeof wp === 'undefined' || !wp.media) {
                    alert('Media Library WordPress belum termuat. Muat ulang halaman atau tempel link Google Drive secara langsung.');
                    return;
                }
                var wrap = btn.closest('div[style*="grid-template-columns: 120px"]');
                var input = btn.parentNode.querySelector('.bk-media-input');
                var frame = wp.media({ title: 'Unggah atau Pilih Gambar', button: { text: 'Gunakan Gambar Ini' }, library: { type: 'image' }, multiple: false });
                frame.on('select', function(){
                    var url = frame.state().get('selection').first().toJSON().url;
                    input.value = url;
                    var prev = wrap ? wrap.querySelector('.bk-media-prev') : null;
                    if (prev) prev.src = url;
                });
                frame.open();
            });
        });
        document.querySelectorAll('.bk-video-media-pick').forEach(function(btn){
            btn.addEventListener('click', function(e){
                e.preventDefault();
                if (typeof wp === 'undefined' || !wp.media) {
                    alert('Media Library WordPress belum termuat. Muat ulang halaman atau tempel link video secara langsung.');
                    return;
                }
                var input = btn.parentNode.querySelector('.bk-video-input');
                var frame = wp.media({ title: 'Unggah atau Pilih Video Profil', button: { text: 'Gunakan Video Ini' }, library: { type: 'video' }, multiple: false });
                frame.on('select', function(){
                    var url = frame.state().get('selection').first().toJSON().url;
                    if (input) input.value = url;
                });
                frame.open();
            });
        });
        document.querySelectorAll('.bk-media-input').forEach(function(inp){
            ['change', 'input'].forEach(function(ev){
                inp.addEventListener(ev, function(){
                    var wrap = inp.closest('div[style*="grid-template-columns: 120px"]');
                    var prev = wrap ? wrap.querySelector('.bk-media-prev') : null;
                    if (prev) prev.src = resolvePrev(inp.value);
                });
            });
        });
    })();
    </script>
    <?php
}
add_action('admin_enqueue_scripts', function($hook_suffix = '') {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if ($page === 'bk-pengaturan-beranda' || strpos((string) $hook_suffix, 'bk-pengaturan-beranda') !== false) {
        wp_enqueue_media();
    }
});



// =============================================================================


// SUBNAV & PRODI LAYOUT CLEANUP FILTER

// =============================================================================

add_filter('the_content', function($content) {

    if (strpos($content, 'bk-subnav-sticky-wrap') !== false) {

        // Auto-inject E-Library into Layanan subnav sticky wrap if not yet present
        if (strpos($content, '/layanan/akademik/') !== false && strpos($content, '/e-library/') === false) {
            $elib_subnav_item = '<li style="flex-shrink:0"><a href="/e-library/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Plus Jakarta Sans\',sans-serif;text-decoration:none;background:transparent;color:#4B5563;font-weight:500">E-Library &amp; Modul</a></li>';
            $content = str_replace('<li style="flex-shrink:0"><a href="/layanan/akademik/"', $elib_subnav_item . '<li style="flex-shrink:0"><a href="/layanan/akademik/"', $content);
        }

        // Auto-inject E-Library card into Layanan catalog cards grid
        if (strpos($content, 'Katalog Layanan &amp; Fasilitas Mahasiswa') !== false && strpos($content, 'Buka E-Library') === false) {
            $elib_card = '<!-- Card E-Library -->
                <div style="background:#ffffff;border:1px solid #E5E7EB;border-radius:24px;padding:28px;display:flex;flex-direction:column;justify-content:space-between">
                    <div>
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                            <span style="display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:14px;background:#E8F0EA;color:#0D3B22;font-size:20px">📚</span>
                            <span style="font-size:10px;font-weight:700;text-transform:uppercase;background:#E8F0EA;color:#0D3B22;padding:4px 10px;border-radius:9999px">Perpustakaan Digital</span>
                        </div>
                        <h3 style="font-family:\'Fraunces\',serif;font-size:19px;font-weight:700;color:#0D3B22;margin:0 0 8px">E-Library &amp; Repositori Modul</h3>
                        <p style="font-size:13px;color:#4B5563;line-height:1.6;margin:0 0 20px">
                            Akses modul praktikum konseling, diktat pembelajaran mata kuliah, buku panduan skripsi/PPL, dan repositori artikel karya ilmiah dosen serta mahasiswa.
                        </p>
                    </div>
                    <div>
                        <a href="/e-library/" style="display:inline-flex;align-items:center;gap:6px;padding:10px 18px;border-radius:12px;background:#0D3B22;color:#F5B82E;font-size:12.5px;font-weight:700;text-decoration:none">Buka E-Library &rarr;</a>
                    </div>
                </div>';
            $content = str_replace('<!-- Card 1 -->', $elib_card . "\n                <!-- Card 1 -->", $content);
            $content = str_replace('6 Portal', '7 Portal', $content);
        }

        $content = preg_replace_callback('/<div class="bk-subnav-sticky-wrap"[^>]*>.*?<\/div>\s*<\/div>/s', function($matches) {

            $cleaned = str_replace(array('<br />', '<br>', '<br/>'), '', $matches[0]);

            $cleaned = preg_replace('/<p>\s*(<li[^>]*>.*?<\/li>)\s*<\/p>/s', '$1', $cleaned);

            return $cleaned;

        }, $content);

    }

    return $content;

}, 99);



// =============================================================================

// =============================================================================

// AUTO-SYNC PRODI PAGES & CONTENT DIRECTLY INTO DATABASE (ROBUST V6.0)

// =============================================================================

function ppb_auto_sync_pages_content() {

    global $wpdb;



    $force = !empty($_GET['sync_ppb_content']) || !empty($_GET['refresh_pages']);

    $synced_version = get_option('ppb_content_synced_version');



    if ($synced_version === '21.6' && !$force) {
        return;
    }



    $json_file = get_template_directory() . '/pages-data.json';

    if (!file_exists($json_file)) {

        return;

    }



    $pages_data = json_decode(file_get_contents($json_file), true);

    if (!is_array($pages_data)) {

        return;

    }



    $slug_to_id = array();

    $now = current_time('mysql');

    $now_gmt = current_time('mysql', 1);



    // Pass 1: Parent Pages (parent_slug is empty)

    foreach ($pages_data as $p) {

        if (empty($p['parent_slug'])) {

            $existing_id = $wpdb->get_var($wpdb->prepare(

                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = 0 AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                $p['slug']

            ));



            if (!$existing_id) {

                $existing_id = $wpdb->get_var($wpdb->prepare(

                    "SELECT ID FROM $wpdb->posts WHERE (post_name = %s OR post_title = %s) AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                    $p['slug'],

                    $p['title']

                ));

            }



            if ($existing_id) {

                $wpdb->update(

                    $wpdb->posts,

                    array(

                        'post_title'     => $p['title'],

                        'post_name'      => $p['slug'],

                        'post_content'   => str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $p['content']),

                        'post_status'    => 'publish',

                        'post_parent'    => 0,

                        'menu_order'     => (int)$p['menu_order'],

                        'post_modified'  => $now,

                        'post_modified_gmt' => $now_gmt,

                    ),

                    array('ID' => $existing_id)

                );

                clean_post_cache($existing_id);

                $slug_to_id[$p['slug']] = $existing_id;

            } else {

                $wpdb->insert(

                    $wpdb->posts,

                    array(

                        'post_title'     => $p['title'],

                        'post_name'      => $p['slug'],

                        'post_content'   => str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $p['content']),

                        'post_status'    => 'publish',

                        'post_type'      => 'page',

                        'post_parent'    => 0,

                        'menu_order'     => (int)$p['menu_order'],

                        'comment_status' => 'closed',

                        'ping_status'    => 'closed',

                        'post_date'      => $now,

                        'post_date_gmt'  => $now_gmt,

                        'post_modified'  => $now,

                        'post_modified_gmt' => $now_gmt,

                    )

                );

                $new_id = $wpdb->insert_id;

                if ($new_id) {

                    clean_post_cache($new_id);

                    $slug_to_id[$p['slug']] = $new_id;

                }

            }

        }

    }



    // Pass 2: Child Pages (parent_slug is not empty)

    foreach ($pages_data as $p) {

        if (!empty($p['parent_slug'])) {

            $parent_id = isset($slug_to_id[$p['parent_slug']]) ? $slug_to_id[$p['parent_slug']] : 0;

            if (!$parent_id) {

                $parent_id = $wpdb->get_var($wpdb->prepare(

                    "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = 0 AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                    $p['parent_slug']

                ));

                if ($parent_id) {

                    $slug_to_id[$p['parent_slug']] = $parent_id;

                }

            }



            $is_dosen = ($p['slug'] === 'dosen');

            $existing_id = 0;



            if ($is_dosen) {

                // Prioritize exact 'dosen' slug so buku-dosen / kepakaran-dosen are never matched accidentally
                if ($parent_id) {
                    $existing_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM $wpdb->posts WHERE post_name = 'dosen' AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                        $parent_id
                    ));
                }
                if (!$existing_id) {
                    $existing_id = $wpdb->get_var(
                        "SELECT ID FROM $wpdb->posts WHERE post_name = 'dosen' AND post_type = 'page' AND post_status != 'trash' LIMIT 1"
                    );
                }
                if (!$existing_id && $parent_id) {
                    $existing_id = $wpdb->get_var($wpdb->prepare(
                        "SELECT ID FROM $wpdb->posts WHERE post_name LIKE 'dosen-%%' AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                        $parent_id
                    ));
                }

            } else {

                if ($parent_id) {

                    $existing_id = $wpdb->get_var($wpdb->prepare(

                        "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                        $p['slug'],

                        $parent_id

                    ));

                }

                if (!$existing_id) {

                    $existing_id = $wpdb->get_var($wpdb->prepare(

                        "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                        $p['slug']

                    ));

                }

            }



            if ($existing_id) {

                $wpdb->update(

                    $wpdb->posts,

                    array(

                        'post_title'     => $p['title'],

                        'post_name'      => $p['slug'],

                        'post_content'   => str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $p['content']),

                        'post_status'    => 'publish',

                        'post_parent'    => (int)$parent_id,

                        'menu_order'     => (int)$p['menu_order'],

                        'post_modified'  => $now,

                        'post_modified_gmt' => $now_gmt,

                    ),

                    array('ID' => $existing_id)

                );

                clean_post_cache($existing_id);

            } else {

                $wpdb->insert(

                    $wpdb->posts,

                    array(

                        'post_title'     => $p['title'],

                        'post_name'      => $p['slug'],

                        'post_content'   => str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $p['content']),

                        'post_status'    => 'publish',

                        'post_type'      => 'page',

                        'post_parent'    => (int)$parent_id,

                        'menu_order'     => (int)$p['menu_order'],

                        'comment_status' => 'closed',

                        'ping_status'    => 'closed',

                        'post_date'      => $now,

                        'post_date_gmt'  => $now_gmt,

                        'post_modified'  => $now,

                        'post_modified_gmt' => $now_gmt,

                    )

                );

                $new_id = $wpdb->insert_id;

                if ($new_id) {

                    clean_post_cache($new_id);

                }

            }

        }

    }



    // Set Front Page to 'beranda'

    $beranda_id = $wpdb->get_var($wpdb->prepare(

        "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = 0 AND post_type = 'page' AND post_status = 'publish' LIMIT 1",

        'beranda'

    ));

    if ($beranda_id) {
        update_option('show_on_front', 'page');
        update_option('page_on_front', $beranda_id);
        update_post_meta($beranda_id, '_wp_page_template', 'default');
    }



    flush_rewrite_rules(false);

    
    // AUTO-POPULATE CPT DOSEN POSTS (Ensure WP database has all 25 dosen posts)
    $all_dosen_data = function_exists('bk_fip_load_dosen_data') ? bk_fip_load_dosen_data() : array();
    if (!empty($all_dosen_data)) {
        if (is_array($all_dosen_data)) {
            $now_time = current_time('mysql');
            $now_gmt  = current_time('mysql', 1);
            foreach ($all_dosen_data as $d) {
                $d_slug = $d['slug'];
                $existing_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'dosen' LIMIT 1", $d_slug));
                if (!$existing_id) {
                    $wpdb->insert(
                        $wpdb->posts,
                        array(
                            'post_title'     => $d['nama_lengkap'],
                            'post_name'      => $d_slug,
                            'post_content'   => $d['bio'] ?? '',
                            'post_status'    => 'publish',
                            'post_type'      => 'dosen',
                            'comment_status' => 'closed',
                            'ping_status'    => 'closed',
                            'post_date'      => $now_time,
                            'post_date_gmt'  => $now_gmt,
                            'post_modified'  => $now_time,
                            'post_modified_gmt' => $now_gmt,
                        )
                    );
                    $existing_id = $wpdb->insert_id;
                } else {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_title'   => $d['nama_lengkap'],
                            'post_status'  => 'publish',
                            'post_content' => $d['bio'] ?? '',
                        ),
                        array('ID' => $existing_id)
                    );
                }
                if ($existing_id) {
                    update_post_meta($existing_id, '_dosen_nip', $d['nip'] ?? '');
                    update_post_meta($existing_id, '_dosen_jabatan', $d['jabatan_fungsional'] ?? '');
                    update_post_meta($existing_id, '_dosen_kepakaran', implode(', ', $d['kepakaran'] ?? array()));
                    update_post_meta($existing_id, '_dosen_publikasi', $d['stat']['publikasi'] ?? 0);
                    update_post_meta($existing_id, '_dosen_sinta_url', $d['sinta_url'] ?? '');
                    if (!empty($d['foto_link'])) {
                        $clean_cpt_foto = function_exists('bk_sanitize_asset_or_url') ? bk_sanitize_asset_or_url($d['foto_link']) : $d['foto_link'];
                        update_post_meta($existing_id, '_dosen_foto_link', $clean_cpt_foto);
                        update_post_meta($existing_id, '_ppb_dosen_foto_link', $clean_cpt_foto);
                    }
                    clean_post_cache($existing_id);
                }
            }
        }
    }

    update_option('ppb_content_synced_version', '21.6');



    if ($force || !is_admin()) {

        add_action('admin_notices', function() {

            echo '<div class="notice notice-success is-dismissible" style="padding:14px;border-left:4px solid #0D3B22;background:#f0fff4;"><h3 style="margin:0 0 5px 0;color:#0D3B22;">&#10003; Sinkronisasi Halaman &amp; Konten V8.0 Selesai (Full-Width & Navigation Immunity Fixed) (Rekam Jejak Dosen & Supabase Sync)!</h3><p style="margin:0;font-size:13.5px;color:#2d3748;">Seluruh 26 halaman program studi PPB FIP UNM (termasuk Dosen, Berita, &amp; Kurikulum) telah berhasil diperbarui dan diseragamkan 100% Full-Width sesuai Local WP.</p></div>';

        });

    }

}

add_action('init', 'ppb_auto_sync_pages_content');
add_action('admin_init', 'ppb_auto_sync_pages_content');

add_action('after_switch_theme', 'ppb_auto_sync_pages_content');



// Bulletproof URL routing: ensure /profil/dosen/{slug}, /profil/dosen/, /dosen/, /e-library/, /mis-pnbp/#anchors,
// and all 26+ pages always resolve cleanly, while preventing any 404 from rendering Warta & Berita.
add_filter('template_include', function($template) {
    if (!is_admin()) {
        $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $path = trim(parse_url($req_uri, PHP_URL_PATH), '/');

        // Normalize WP-Staging /yamato subfolder prefix
        if (strpos($path, 'yamato/') === 0) {
            $path = substr($path, 7);
        } elseif ($path === 'yamato') {
            $path = '';
        }

        // 1. FRONT PAGE / HOME
        if (is_front_page() || empty($path)) {
            $front_tpl = get_template_directory() . '/front-page.php';
            if (file_exists($front_tpl)) {
                return $front_tpl;
            }
        }

        // 2. BULLETPROOF BERITA PAGE ROUTING - ONLY when path is 'berita' or page template
        if ($path === 'berita' || is_page('berita') || is_page_template('page-berita.php')) {
            $berita_tpl = get_template_directory() . '/page-berita.php';
            if (file_exists($berita_tpl)) {
                return $berita_tpl;
            }
        }

        // 3. E-LIBRARY REPOSITORY ROUTING (/e-library/, /elibrary/, /layanan/e-library/, /layanan/elibrary/)
        if ($path === 'e-library' || $path === 'elibrary' || $path === 'layanan/e-library' || $path === 'layanan/elibrary') {
            global $wp_query;
            $wp_query->is_404 = false;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_post_type_archive = false;
            status_header(200);

            $elib_tpl = get_template_directory() . '/page-elibrary.php';
            if (file_exists($elib_tpl)) {
                return $elib_tpl;
            }
        }

        // 4. MIS PNBP SUB-TAB REDIRECTIONS (Safely redirect to anchor tabs on /mis-pnbp/)
        if ($path === 'mis-pnbp/penelitian' || $path === 'mis-pnbp/penelitian/') {
            wp_safe_redirect(home_url('/mis-pnbp/#penelitian'), 301);
            exit;
        }
        if ($path === 'mis-pnbp/pkm' || $path === 'mis-pnbp/pkm/') {
            wp_safe_redirect(home_url('/mis-pnbp/#pkm'), 301);
            exit;
        }

        // 5. SINGLE DOSEN PROFILE ROUTING (/profil/dosen/{slug}, /dosen/{slug}, or ?dosen={slug})
        $dosen_slug = '';
        if (isset($_GET['dosen']) && !empty($_GET['dosen'])) {
            $dosen_slug = sanitize_title($_GET['dosen']);
        } else {
            $parts = explode('/', $path);
            if (count($parts) >= 3 && $parts[0] === 'profil' && $parts[1] === 'dosen' && !empty($parts[2])) {
                $dosen_slug = sanitize_title($parts[2]);
            } elseif (count($parts) === 2 && $parts[0] === 'dosen' && !empty($parts[1])) {
                $dosen_slug = sanitize_title($parts[1]);
            }
        }

        if (!empty($dosen_slug)) {
            $json_file = get_template_directory() . '/dosen-data.json';
            $all_dosen = get_option('bk_dosen_data_cache', array());
            if ((empty($all_dosen) || !is_array($all_dosen)) && file_exists($json_file)) {
                $all_dosen = json_decode(file_get_contents($json_file), true);
            } elseif (file_exists($json_file)) {
                $json_dosen = json_decode(file_get_contents($json_file), true);
                if (is_array($json_dosen) && count($json_dosen) > count($all_dosen)) {
                    $all_dosen = $json_dosen;
                }
            }

            if (is_array($all_dosen)) {
                foreach ($all_dosen as $d) {
                    if ($d['slug'] === $dosen_slug || (string)$d['id'] === $dosen_slug) {
                        global $current_dosen, $wp_query;
                        $current_dosen = $d;
                        $wp_query->is_404 = false;
                        $wp_query->is_page = false;
                        $wp_query->is_single = true;
                        $wp_query->is_singular = true;
                        $wp_query->is_archive = false;
                        $wp_query->is_post_type_archive = false;
                        $wp_query->is_home = false;
                        status_header(200);

                        $single_template = get_template_directory() . '/single-dosen.php';
                        if (file_exists($single_template)) {
                            return $single_template;
                        }
                    }
                }
            }
        }

        // 6. MAIN DOSEN ROSTER DIRECTORY ROUTING (/profil/dosen, /dosen)
        if ($path === 'profil/dosen' || $path === 'dosen') {
            global $wpdb, $post, $wp_query;

            $GLOBALS['bk_has_dosen_roster'] = true;

            $profil_parent_id = (int)$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'profil' AND post_parent = 0 AND post_type = 'page' AND post_status != 'trash' LIMIT 1");
            $dosen_id = 0;
            if ($profil_parent_id > 0) {
                $dosen_id = (int)$wpdb->get_var($wpdb->prepare(
                    "SELECT ID FROM $wpdb->posts WHERE post_name = 'dosen' AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                    $profil_parent_id
                ));
            }
            if (!$dosen_id) {
                $dosen_id = (int)$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'dosen' AND post_type = 'page' AND post_status = 'publish' LIMIT 1");
            }
            if (!$dosen_id) {
                $dosen_id = (int)$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name LIKE 'dosen-%' AND post_type = 'page' AND post_status != 'trash' LIMIT 1");
            }

            $dosen_page_entry = null;
            $json_file = get_template_directory() . '/pages-data.json';
            if (file_exists($json_file)) {
                $pages_data = json_decode(file_get_contents($json_file), true);
                if (is_array($pages_data)) {
                    foreach ($pages_data as $p) {
                        if (($p['slug'] ?? '') === 'dosen') {
                            $dosen_page_entry = $p;
                            break;
                        }
                    }
                }
            }
            $clean_dosen_content = $dosen_page_entry
                ? str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', (string)$dosen_page_entry['content'])
                : '';

            // If not found in DB at all, auto-create it immediately from pages-data.json
            if (!$dosen_id && $dosen_page_entry) {
                $now = current_time('mysql');
                $wpdb->insert(
                    $wpdb->posts,
                    array(
                        'post_title'     => $dosen_page_entry['title'],
                        'post_name'      => 'dosen',
                        'post_content'   => $clean_dosen_content,
                        'post_status'    => 'publish',
                        'post_type'      => 'page',
                        'post_parent'    => $profil_parent_id,
                        'comment_status' => 'closed',
                        'ping_status'    => 'closed',
                        'post_date'      => $now,
                        'post_date_gmt'  => current_time('mysql', 1),
                        'post_modified'  => $now,
                        'post_modified_gmt' => current_time('mysql', 1),
                    )
                );
                $dosen_id = (int)$wpdb->insert_id;
                if ($dosen_id) {
                    clean_post_cache($dosen_id);
                }
            } elseif ($dosen_id && $clean_dosen_content !== '') {
                $db_content = (string)$wpdb->get_var($wpdb->prepare("SELECT post_content FROM $wpdb->posts WHERE ID = %d", $dosen_id));
                $needs_repair = (strpos($db_content, 'bk-dosen-card-name') === false)
                    || (strpos($db_content, 'bk-dosen-dossier-data') !== false)
                    || (strlen($db_content) < (strlen($clean_dosen_content) - 150))
                    || !empty($_GET['refresh_pages'])
                    || !empty($_GET['refresh_dosen'])
                    || !empty($_GET['sync_ppb_content']);
                if ($needs_repair) {
                    $wpdb->update(
                        $wpdb->posts,
                        array(
                            'post_content'      => $clean_dosen_content,
                            'post_status'       => 'publish',
                            'post_modified'     => current_time('mysql'),
                            'post_modified_gmt' => current_time('mysql', 1),
                        ),
                        array('ID' => $dosen_id)
                    );
                    clean_post_cache($dosen_id);
                }
            }

            if ($dosen_id) {
                $post = get_post($dosen_id);
                if ($post && $clean_dosen_content !== '' && (
                    strpos((string)$post->post_content, 'bk-dosen-card-name') === false
                    || strpos((string)$post->post_content, 'bk-dosen-dossier-data') !== false
                    || strlen((string)$post->post_content) < (strlen($clean_dosen_content) - 150)
                )) {
                    $post->post_content = $clean_dosen_content;
                }
                setup_postdata($post);
                $wp_query->post = $post;
                $wp_query->posts = array($post);
                $wp_query->post_count = 1;
                $wp_query->current_post = -1;
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_single = false;
                $wp_query->is_singular = true;
                $wp_query->is_archive = false;
                $wp_query->is_post_type_archive = false;
                $wp_query->is_home = false;
                $wp_query->queried_object = $post;
                $wp_query->queried_object_id = $dosen_id;
                status_header(200);

                $page_template = get_template_directory() . '/page.php';
                if (file_exists($page_template)) {
                    return $page_template;
                }
            }
        }

        // 7. UNIVERSAL INTELLIGENT ROUTING FOR ALL PRODI PAGES
        if (!empty($path)) {
            $json_file = get_template_directory() . '/pages-data.json';
            if (file_exists($json_file)) {
                $pages_data = json_decode(file_get_contents($json_file), true);
                if (is_array($pages_data)) {
                    $parts = explode('/', trim($path, '/'));
                    $slug_target = end($parts);
                    $parent_target = (count($parts) > 1) ? $parts[count($parts) - 2] : '';

                    $matched_page = null;

                    // Pass A: Exact full slug match (e.g. "kemahasiswaan/statistik" vs "akademik/statistik")
                    foreach ($pages_data as $p) {
                        $full_slug = !empty($p['parent_slug']) ? $p['parent_slug'] . '/' . $p['slug'] : $p['slug'];
                        if ($full_slug === $path) {
                            $matched_page = $p;
                            break;
                        }
                    }

                    // Pass B: Target slug with parent disambiguation
                    if (!$matched_page) {
                        foreach ($pages_data as $p) {
                            if ($p['slug'] === $slug_target) {
                                if (!empty($parent_target) && !empty($p['parent_slug']) && $p['parent_slug'] === $parent_target) {
                                    $matched_page = $p;
                                    break;
                                } elseif (empty($parent_target) && empty($p['parent_slug'])) {
                                    $matched_page = $p;
                                    break;
                                } elseif (!$matched_page) {
                                    $matched_page = $p; // soft fallback
                                }
                            }
                        }
                    }

                    if ($matched_page) {
                        global $wpdb, $post, $wp_query;

                        $parent_id = 0;
                        if (!empty($matched_page['parent_slug'])) {
                            $parent_id = (int)$wpdb->get_var($wpdb->prepare(
                                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = 0 AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                                $matched_page['parent_slug']
                            ));
                        }

                        $post_id = 0;
                        if ($parent_id > 0) {
                            $post_id = (int)$wpdb->get_var($wpdb->prepare(
                                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                                $matched_page['slug'],
                                $parent_id
                            ));
                        }
                        if (!$post_id) {
                            $post_id = (int)$wpdb->get_var($wpdb->prepare(
                                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'page' AND post_status != 'trash' LIMIT 1",
                                $matched_page['slug']
                            ));
                        }

                        // Auto-create page if not present in DB
                        $clean_matched_content = str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $matched_page['content']);
                        if (!$post_id) {
                            $now = current_time('mysql');
                            $wpdb->insert($wpdb->posts, array(
                                'post_title'     => $matched_page['title'],
                                'post_name'      => $matched_page['slug'],
                                'post_content'   => $clean_matched_content,
                                'post_status'    => 'publish',
                                'post_type'      => 'page',
                                'post_parent'    => $parent_id,
                                'comment_status' => 'closed',
                                'ping_status'    => 'closed',
                                'post_date'      => $now,
                                'post_date_gmt'  => current_time('mysql', 1),
                                'post_modified'  => $now,
                                'post_modified_gmt' => current_time('mysql', 1)
                            ));
                            $post_id = $wpdb->insert_id;
                            clean_post_cache($post_id);
                        } else {
                            // Failsafe auto-repair: if DB content is corrupt, truncated, or lacks key sections
                            $db_content = $wpdb->get_var($wpdb->prepare("SELECT post_content FROM $wpdb->posts WHERE ID = %d", $post_id));
                            $expected_len = strlen($clean_matched_content);
                            $actual_len = strlen($db_content);
                            $has_legacy_subnav = (strpos($clean_matched_content, 'bk-page-head') !== false && (strpos($db_content, 'bk-page-head') === false || strpos($db_content, 'bk-subnav-sticky-wrap') !== false));
                            $needs_db_repair = ($actual_len < ($expected_len - 150))
                                             || $has_legacy_subnav
                                             || ($matched_page['slug'] === 'struktur' && strpos($db_content, 'Bagan Kepemimpinan') === false)
                                             || ($matched_page['slug'] === 'roadmap' && strpos($db_content, '3 Pilar Kepakaran') === false)
                                             || (strpos($db_content, 'bk-page-wrapper') === false)
                                             || !empty($_GET['refresh_pages'])
                                             || !empty($_GET['sync_ppb_content']);

                            if ($needs_db_repair) {
                                $wpdb->update($wpdb->posts, array(
                                    'post_content'      => $clean_matched_content,
                                    'post_status'       => 'publish',
                                    'post_modified'     => current_time('mysql'),
                                    'post_modified_gmt' => current_time('mysql', 1)
                                ), array('ID' => $post_id));
                                clean_post_cache($post_id);
                            }
                        }

                        if ($post_id) {
                            $post = get_post($post_id);
                            // Immediate in-memory failsafe: guarantee current render is 100% pristine
                            $post_needs_failsafe = $post && (
                                strlen($post->post_content) < (strlen($clean_matched_content) - 150)
                                || strpos($post->post_content, 'bk-page-wrapper') === false
                                || (strpos($clean_matched_content, 'bk-page-head') !== false && (strpos($post->post_content, 'bk-page-head') === false || strpos($post->post_content, 'bk-subnav-sticky-wrap') !== false))
                                || ($matched_page['slug'] === 'struktur' && strpos($post->post_content, 'Bagan Kepemimpinan') === false)
                            );
                            if ($post_needs_failsafe) {
                                $post->post_content = $clean_matched_content;
                            }
                            setup_postdata($post);
                            $wp_query->posts = array($post);
                            $wp_query->post_count = 1;
                            $wp_query->is_404 = false;
                            $wp_query->is_page = true;
                            $wp_query->is_singular = true;
                            $wp_query->is_home = false;
                            $wp_query->is_archive = false;
                            $wp_query->is_post_type_archive = false;
                            $wp_query->queried_object = $post;
                            $wp_query->queried_object_id = $post_id;
                            status_header(200);

                            $page_template = get_template_directory() . '/page.php';
                            if (file_exists($page_template)) {
                                return $page_template;
                            }
                        }
                    }
                }
            }

            // Fallback: Check if any published page matches $slug_target directly in wp_posts
            global $wpdb, $post, $wp_query;
            $slug_parts = explode('/', trim($path, '/'));
            $fallback_target = end($slug_parts);
            $db_page_id = (int)$wpdb->get_var($wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_name = %s AND post_type = 'page' AND post_status = 'publish' LIMIT 1",
                $fallback_target
            ));
            if ($db_page_id) {
                $post = get_post($db_page_id);
                setup_postdata($post);
                $wp_query->posts = array($post);
                $wp_query->post_count = 1;
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
                $wp_query->is_singular = true;
                $wp_query->is_home = false;
                $wp_query->is_archive = false;
                $wp_query->is_post_type_archive = false;
                $wp_query->queried_object = $post;
                $wp_query->queried_object_id = $db_page_id;
                status_header(200);

                $page_template = get_template_directory() . '/page.php';
                if (file_exists($page_template)) {
                    return $page_template;
                }
            }
        }

        // 8. FAILSAFE: Intercept 404 or rogue index.php fallback (Never display Warta & Berita on broken URLs!)
        global $wp_query;
        $is_rogue_index = (basename($template) === 'index.php' && !is_home() && !is_archive() && !is_category() && !is_tag() && !is_search());
        if ($wp_query->is_404 || $is_rogue_index) {
            $wp_query->is_404 = true;
            status_header(404);
            $tpl_404 = get_template_directory() . '/404.php';
            if (file_exists($tpl_404)) {
                return $tpl_404;
            }
        }
    }
    return $template;
});

// Set document title for Single Dosen Profile & Dosen Roster Directory
add_filter('document_title_parts', function($title_parts) {
    global $current_dosen;
    if (!empty($current_dosen) && is_array($current_dosen) && !empty($current_dosen['nama_lengkap'])) {
        $title_parts['title'] = $current_dosen['nama_lengkap'] . ' - Profil & Rekam Jejak Dosen';
    } elseif (function_exists('bk_fip_is_dosen_roster_page') && bk_fip_is_dosen_roster_page()) {
        $title_parts['title'] = 'Direktori Dosen & Kepakaran';
    }
    return $title_parts;
}, 99);

/**
 * --------------------------------------------------------------------------
 * SINTA KEMDIKTISAINTEK DIRECT INTEGRATION & HYBRID SYNC ENGINE
 * --------------------------------------------------------------------------
 */
function bk_fip_get_sinta_sync_token() {
    $token = get_option('bk_sinta_sync_token', '');
    if (empty($token)) {
        $token = wp_generate_password(40, false, false);
        update_option('bk_sinta_sync_token', $token, false);
    }
    return $token;
}

function bk_fip_extract_sinta_id($dosen) {
    $sid = trim((string)($dosen['sinta_id'] ?? ''));
    if ($sid !== '' && preg_match('/^\d+$/', $sid)) {
        return $sid;
    }
    $surl = trim((string)($dosen['sinta_url'] ?? ''));
    if ($surl !== '' && preg_match('/\/authors\/profile\/(\d+)/', $surl, $m)) {
        return $m[1];
    }
    return '';
}

function bk_fip_load_dosen_data() {
    $list = get_option('bk_dosen_data_cache', array());
    if (!is_array($list) || empty($list)) {
        $json_file = get_template_directory() . '/dosen-data.json';
        $list = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();
    }
    return is_array($list) ? $list : array();
}

function bk_fip_save_dosen_data($dosen_list) {
    $json_path = get_template_directory() . '/dosen-data.json';
    file_put_contents($json_path, json_encode($dosen_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    update_option('bk_dosen_data_cache', $dosen_list, false);
    update_option('bk_dosen_last_synced', current_time('mysql'));
    if (function_exists('bk_fip_rebuild_dosen_roster_html')) {
        bk_fip_rebuild_dosen_roster_html($dosen_list);
    }
}

function bk_fip_sinta_http_get($url) {
    $resp = wp_remote_get($url, array(
        'timeout' => 20,
        'headers' => array(
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/125.0.0.0 Safari/537.36',
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ),
    ));
    if (is_wp_error($resp) || wp_remote_retrieve_response_code($resp) !== 200) {
        return false;
    }
    return wp_remote_retrieve_body($resp);
}

function bk_fip_parse_sinta_int($str) {
    $cleaned = preg_replace('/[^\d]/', '', (string)$str);
    return $cleaned !== '' ? (int)$cleaned : 0;
}

function bk_fip_sinta_dom($html) {
    if (empty($html) || !class_exists('DOMDocument')) {
        return null;
    }
    libxml_use_internal_errors(true);
    $dom = new DOMDocument();
    $dom->loadHTML('<?xml encoding="UTF-8">' . $html);
    libxml_clear_errors();
    return new DOMXPath($dom);
}

function bk_fip_parse_sinta_profile_metrics($html) {
    $metrics = array(
        'sinta_score_overall' => 0,
        'sinta_score_3yr'     => 0,
        'scopus_articles'     => 0,
        'scopus_citations'    => 0,
        'scopus_h_index'      => 0,
        'gs_articles'         => 0,
        'gs_citations'        => 0,
        'gs_h_index'          => 0,
    );
    $xpath = bk_fip_sinta_dom($html);
    if (!$xpath) return $metrics;

    $pr_nums = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' pr-num ')]");
    foreach ($pr_nums as $num_node) {
        $val = bk_fip_parse_sinta_int($num_node->textContent);
        $sib = $num_node->nextSibling;
        while ($sib && $sib->nodeType !== XML_ELEMENT_NODE) {
            $sib = $sib->nextSibling;
        }
        if ($sib && strpos(' ' . $sib->getAttribute('class') . ' ', ' pr-txt ') !== false) {
            $label = strtolower(trim($sib->textContent));
            if (strpos($label, 'sinta score overall') !== false) {
                $metrics['sinta_score_overall'] = $val;
            } elseif (strpos($label, 'sinta score 3yr') !== false) {
                $metrics['sinta_score_3yr'] = $val;
            }
        }
    }

    $rows = $xpath->query("//table[contains(concat(' ', normalize-space(@class), ' '), ' stat-table ')]//tr");
    foreach ($rows as $row) {
        $cols = $xpath->query("./th|./td", $row);
        if ($cols->length < 3) continue;
        $label = strtolower(trim($cols->item(0)->textContent));
        if ($label === 'article') {
            $metrics['scopus_articles'] = bk_fip_parse_sinta_int($cols->item(1)->textContent);
            $metrics['gs_articles']     = bk_fip_parse_sinta_int($cols->item(2)->textContent);
        } elseif ($label === 'citation') {
            $metrics['scopus_citations'] = bk_fip_parse_sinta_int($cols->item(1)->textContent);
            $metrics['gs_citations']     = bk_fip_parse_sinta_int($cols->item(2)->textContent);
        } elseif ($label === 'h-index') {
            $metrics['scopus_h_index'] = bk_fip_parse_sinta_int($cols->item(1)->textContent);
            $metrics['gs_h_index']     = bk_fip_parse_sinta_int($cols->item(2)->textContent);
        }
    }
    return $metrics;
}

function bk_fip_is_first_author_by_name($authors_str, $author_name) {
    if (empty($authors_str) || empty($author_name)) return false;
    $base = explode(',', (string)$author_name)[0];
    $base = preg_replace('/\b(prof|dr|dra|drs|ir|h|hj)\b\.?/i', ' ', $base);
    preg_match_all('/[a-zA-Z]{2,}/', $base, $m);
    $tokens = array_map('strtolower', $m[0] ?? array());
    if (empty($tokens)) return false;

    if (strpos($authors_str, ';') !== false) {
        $first_author = strtolower(trim(explode(';', $authors_str, 2)[0]));
    } elseif (strpos($authors_str, ',') !== false) {
        $first_author = strtolower(trim(explode(',', $authors_str, 2)[0]));
    } else {
        return true; // Single author
    }

    $last_name = end($tokens);
    if ($last_name && preg_match('/\b' . preg_quote($last_name, '/') . '\b/', $first_author)) {
        return true;
    }
    if (count($tokens) > 1 && strlen($tokens[0]) >= 4 && preg_match('/\b' . preg_quote($tokens[0], '/') . '\b/', $first_author)) {
        return true;
    }
    return false;
}

function bk_fip_parse_sinta_publications($html, $source_type, $author_name = '') {
    $items = array();
    $xpath = bk_fip_sinta_dom($html);
    if (!$xpath) return $items;

    $cards = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-list-item ')]");
    foreach ($cards as $card) {
        $title_a = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-title ')]//a", $card)->item(0);
        if (!$title_a) continue;
        $judul = trim(preg_replace('/\s+/', ' ', $title_a->textContent));
        if ($judul === '') continue;

        $url = trim($title_a->getAttribute('href'));
        if ($url === '#!') $url = '';

        $pub_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-pub ')]", $card)->item(0);
        $media = $pub_el ? trim(preg_replace('/\s+/', ' ', $pub_el->textContent)) : '';

        $year_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-year ')]", $card)->item(0);
        $tahun = 0;
        if ($year_el && preg_match('/\b(19\d{2}|20\d{2})\b/', $year_el->textContent, $ym)) {
            $tahun = (int)$ym[1];
        }

        $quart_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-quartile ')]", $card)->item(0);
        $quart_txt = $quart_el ? trim(preg_replace('/\s+/', ' ', $quart_el->textContent)) : '';

        $author_order = null;
        $penulis = '';
        $meta_links = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-meta ')]//a", $card);
        foreach ($meta_links as $ma) {
            $mt = trim(preg_replace('/\s+/', ' ', $ma->textContent));
            $lower = strtolower($mt);
            $cls = ' ' . $ma->getAttribute('class') . ' ';
            if (strpos($lower, 'author order') === 0) {
                if (preg_match('/:\s*(\d+)\b/', $mt, $om)) {
                    $author_order = (int)$om[1];
                }
            } elseif (strpos($lower, 'creator :') === 0 || strpos($lower, 'authors :') === 0) {
                $parts = explode(':', $mt, 2);
                $penulis = isset($parts[1]) ? trim($parts[1]) : '';
            } elseif (
                $source_type === 'GARUDA'
                && $penulis === ''
                && strpos($cls, ' ar-pub ') === false
                && strpos($cls, ' ar-year ') === false
                && strpos($cls, ' ar-quartile ') === false
                && strpos($lower, 'doi:') !== 0
                && strpos($lower, 'accred') !== 0
                && (strpos($mt, ';') !== false || strpos($mt, ',') !== false)
            ) {
                $penulis = $mt;
            }
        }

        if ($author_order !== null && $author_order > 0) {
            $peran = ($author_order === 1) ? 'Penulis Utama' : 'Anggota';
        } elseif (bk_fip_is_first_author_by_name($penulis, $author_name)) {
            $peran = 'Penulis Utama';
        } else {
            $peran = 'Anggota';
        }

        if ($source_type === 'Scopus') {
            $akreditasi = preg_match('/\b(Q[1-4]|no-Q)\b/i', $quart_txt, $qm) ? $qm[1] : 'Scopus';
        } elseif ($source_type === 'GARUDA') {
            $akreditasi = preg_match('/\b(SINTA\s*[1-6])\b/i', $quart_txt, $sm) ? strtoupper($sm[1]) : 'GARUDA';
        } else {
            $akreditasi = 'Scholar';
        }

        $items[] = array(
            'judul'      => $judul,
            'tahun'      => $tahun,
            'media'      => $media,
            'akreditasi' => $akreditasi,
            'peran'      => $peran,
            'url'        => $url,
            'sumber'     => $source_type,
            'penulis'    => $penulis,
        );
    }
    return $items;
}

function bk_fip_parse_sinta_researches_or_services($html, $is_service = false, $author_name = '') {
    $items = array();
    $xpath = bk_fip_sinta_dom($html);
    if (!$xpath) return $items;

    $first_name = strtolower(trim(explode(',', (string)$author_name)[0]));
    $cards = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-list-item ')]");
    foreach ($cards as $card) {
        $title_a = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-title ')]//a", $card)->item(0);
        if (!$title_a) continue;
        $judul = trim(preg_replace('/\s+/', ' ', $title_a->textContent));
        if ($judul === '') continue;

        $pub_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-pub ')]", $card)->item(0);
        $sumber_dana = $pub_el ? trim(preg_replace('/\s+/', ' ', $pub_el->textContent)) : ($is_service ? 'PNBP UNM' : 'KEMENDIKBUDRISTEK');

        $year_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-year ')]", $card)->item(0);
        $tahun = 0;
        if ($year_el && preg_match('/\b(19\d{2}|20\d{2})\b/', $year_el->textContent, $ym)) {
            $tahun = (int)$ym[1];
        }

        $leader = '';
        $meta_links = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-meta ')]//a", $card);
        foreach ($meta_links as $ma) {
            $mt = trim(preg_replace('/\s+/', ' ', $ma->textContent));
            if (stripos($mt, 'leader :') === 0) {
                $parts = explode(':', $mt, 2);
                $leader = isset($parts[1]) ? trim($parts[1]) : '';
                break;
            }
        }

        $is_leader = ($first_name !== '' && $leader !== '' && strpos(strtolower($leader), $first_name) !== false);
        if ($is_service) {
            $peran = $is_leader ? 'Ketua Pelaksana' : ($leader !== '' ? 'Ketua: ' . $leader : 'Anggota');
            $items[] = array(
                'judul'       => $judul,
                'tahun'       => $tahun,
                'peran'       => $peran,
                'sumber_dana' => $sumber_dana,
                'lokasi'      => '',
            );
        } else {
            $peran = $is_leader ? 'Ketua Peneliti' : ($leader !== '' ? 'Ketua: ' . $leader : 'Anggota');
            $items[] = array(
                'judul'       => $judul,
                'tahun'       => $tahun,
                'peran'       => $peran,
                'sumber_dana' => $sumber_dana,
                'lama_tahun'  => 1,
            );
        }
    }
    return $items;
}

function bk_fip_parse_sinta_books($html) {
    $items = array();
    $xpath = bk_fip_sinta_dom($html);
    if (!$xpath) return $items;

    $cards = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-list-item ')]");
    foreach ($cards as $card) {
        $title_a = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-title ')]//a", $card)->item(0);
        if (!$title_a) continue;
        $judul = trim(preg_replace('/\s+/', ' ', $title_a->textContent));
        if ($judul === '') continue;

        $pub_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-pub ')]", $card)->item(0);
        $penerbit = $pub_el ? trim(preg_replace('/\s+/', ' ', $pub_el->textContent)) : '';

        $year_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-year ')]", $card)->item(0);
        $tahun = 0;
        if ($year_el && preg_match('/\b(19\d{2}|20\d{2})\b/', $year_el->textContent, $ym)) {
            $tahun = (int)$ym[1];
        }

        $quart_el = $xpath->query(".//*[contains(concat(' ', normalize-space(@class), ' '), ' ar-quartile ')]", $card)->item(0);
        $isbn_txt = $quart_el ? trim(preg_replace('/\s+/', ' ', $quart_el->textContent)) : '';
        $isbn = trim(preg_replace('/^ISBN\s*:\s*/i', '', $isbn_txt));

        $items[] = array(
            'judul'    => $judul,
            'tahun'    => $tahun,
            'penerbit' => $penerbit,
            'isbn'     => $isbn,
        );
    }
    return $items;
}

function bk_fip_fetch_sinta_author($sinta_id, $author_name = '') {
    $sinta_id = preg_replace('/[^\d]/', '', (string)$sinta_id);
    if ($sinta_id === '') {
        return new WP_Error('invalid_sinta_id', 'SINTA ID tidak valid.');
    }

    $base = 'https://sinta.kemdiktisaintek.go.id/authors/profile/' . $sinta_id;
    $profile_html = bk_fip_sinta_http_get($base);
    if ($profile_html === false) {
        return new WP_Error('sinta_unreachable', 'Gagal menghubungi SINTA untuk ID ' . $sinta_id);
    }

    $metrics = bk_fip_parse_sinta_profile_metrics($profile_html);

    $seen_titles = array();
    $publikasi = array();
    foreach (array('scopus' => 'Scopus', 'garuda' => 'GARUDA', 'googlescholar' => 'Google Scholar') as $view => $label) {
        $vh = bk_fip_sinta_http_get($base . '/?view=' . $view);
        if ($vh !== false) {
            foreach (bk_fip_parse_sinta_publications($vh, $label, $author_name) as $pub) {
                $norm = preg_replace('/[^a-z0-9]+/', '', strtolower($pub['judul']));
                if ($norm !== '' && !isset($seen_titles[$norm])) {
                    $seen_titles[$norm] = true;
                    $publikasi[] = $pub;
                }
            }
        }
    }
    usort($publikasi, function($a, $b) {
        return ((int)($b['tahun'] ?? 0)) <=> ((int)($a['tahun'] ?? 0));
    });

    $res_html = bk_fip_sinta_http_get($base . '/?view=researches');
    $penelitian = ($res_html !== false) ? bk_fip_parse_sinta_researches_or_services($res_html, false, $author_name) : array();

    $srv_html = bk_fip_sinta_http_get($base . '/?view=services');
    $pengabdian = ($srv_html !== false) ? bk_fip_parse_sinta_researches_or_services($srv_html, true, $author_name) : array();

    $book_html = bk_fip_sinta_http_get($base . '/?view=books');
    $buku = ($book_html !== false) ? bk_fip_parse_sinta_books($book_html) : array();

    return array(
        'sinta_id'      => $sinta_id,
        'sinta_metrics' => $metrics,
        'publikasi'     => $publikasi,
        'penelitian'    => $penelitian,
        'pengabdian'    => $pengabdian,
        'buku'          => $buku,
    );
}

function bk_fip_apply_sinta_updates($updates) {
    if (!is_array($updates) || empty($updates)) {
        return 0;
    }

    $dosen_list = bk_fip_load_dosen_data();
    if (empty($dosen_list)) {
        return 0;
    }

    $by_slug = array();
    $by_sinta = array();
    foreach ($updates as $u) {
        if (!empty($u['slug'])) {
            $by_slug[sanitize_title($u['slug'])] = $u;
        }
        if (!empty($u['sinta_id'])) {
            $by_sinta[trim((string)$u['sinta_id'])] = $u;
        }
    }

    $updated_count = 0;
    $now = current_time('mysql');

    foreach ($dosen_list as &$d) {
        $slug = sanitize_title($d['slug'] ?? '');
        $sid  = bk_fip_extract_sinta_id($d);

        $match = null;
        if ($slug !== '' && isset($by_slug[$slug])) {
            $match = $by_slug[$slug];
        } elseif ($sid !== '' && isset($by_sinta[$sid])) {
            $match = $by_sinta[$sid];
        }

        if (!$match) {
            continue;
        }

        if ($sid !== '' && empty($d['sinta_id'])) {
            $d['sinta_id'] = $sid;
        }
        if (empty($d['sinta_url']) && !empty($d['sinta_id'])) {
            $d['sinta_url'] = 'https://sinta.kemdiktisaintek.go.id/authors/profile/' . $d['sinta_id'];
        }

        if (isset($match['sinta_metrics']) && is_array($match['sinta_metrics'])) {
            $d['sinta_metrics'] = $match['sinta_metrics'];
        }
        if (isset($match['publikasi']) && is_array($match['publikasi'])) {
            $d['publikasi'] = $match['publikasi'];
        }
        if (isset($match['penelitian']) && is_array($match['penelitian'])) {
            $d['penelitian'] = $match['penelitian'];
        }
        if (isset($match['pengabdian']) && is_array($match['pengabdian'])) {
            $d['pengabdian'] = $match['pengabdian'];
        }
        if (isset($match['buku']) && is_array($match['buku'])) {
            $d['buku'] = $match['buku'];
        }

        $d['stat'] = array(
            'publikasi'  => count($d['publikasi'] ?? array()),
            'penelitian' => count($d['penelitian'] ?? array()),
            'pengabdian' => count($d['pengabdian'] ?? array()),
            'buku'       => count($d['buku'] ?? array()),
        );
        $d['sinta_last_synced'] = $now;
        $updated_count++;
    }
    unset($d);

    if ($updated_count > 0) {
        bk_fip_save_dosen_data($dosen_list);
    }

    return $updated_count;
}

function bk_fip_verify_sinta_rest_token($request) {
    $auth = $request->get_header('authorization');
    if (empty($auth) || stripos($auth, 'Bearer ') !== 0) {
        return new WP_Error('unauthorized', 'Missing Bearer token.', array('status' => 401));
    }
    $provided = trim(substr($auth, 7));
    $expected = bk_fip_get_sinta_sync_token();
    if (!hash_equals($expected, $provided)) {
        return new WP_Error('forbidden', 'Invalid SINTA sync token.', array('status' => 403));
    }
    return true;
}

add_action('rest_api_init', function() {
    register_rest_route('bk-fip/v1', '/dosen-sinta-ids', array(
        'methods'             => 'GET',
        'permission_callback' => 'bk_fip_verify_sinta_rest_token',
        'callback'            => function() {
            $dosen_list = bk_fip_load_dosen_data();
            $out = array();
            foreach ($dosen_list as $d) {
                $sid = bk_fip_extract_sinta_id($d);
                if ($sid !== '') {
                    $out[] = array(
                        'slug'     => $d['slug'] ?? '',
                        'nama'     => $d['nama_lengkap'] ?? ($d['nama'] ?? ''),
                        'sinta_id' => $sid,
                    );
                }
            }
            return rest_ensure_response(array(
                'count' => count($out),
                'dosen' => $out,
            ));
        },
    ));

    register_rest_route('bk-fip/v1', '/sync-sinta', array(
        'methods'             => 'POST',
        'permission_callback' => 'bk_fip_verify_sinta_rest_token',
        'callback'            => function($request) {
            $body = $request->get_json_params();
            $updates = isset($body['updates']) && is_array($body['updates']) ? $body['updates'] : array();
            if (empty($updates)) {
                return new WP_Error('empty_payload', 'No updates provided.', array('status' => 400));
            }
            $updated = bk_fip_apply_sinta_updates($updates);
            return rest_ensure_response(array(
                'success' => true,
                'updated' => $updated,
            ));
        },
    ));
});

add_action('wp_ajax_bk_sync_single_sinta', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Akses ditolak.'));
    }
    check_ajax_referer('bk_sinta_ajax_nonce', 'nonce');

    $slug = sanitize_title($_POST['slug'] ?? '');
    if ($slug === '') {
        wp_send_json_error(array('message' => 'Slug dosen tidak valid.'));
    }

    $dosen_list = bk_fip_load_dosen_data();
    $target = null;
    foreach ($dosen_list as $d) {
        if (($d['slug'] ?? '') === $slug) {
            $target = $d;
            break;
        }
    }
    if (!$target) {
        wp_send_json_error(array('message' => 'Data dosen tidak ditemukan.'));
    }

    $sid = bk_fip_extract_sinta_id($target);
    if ($sid === '') {
        wp_send_json_error(array('message' => 'Dosen ini belum memiliki SINTA ID / SINTA URL.'));
    }

    $nama = $target['nama_lengkap'] ?? ($target['nama'] ?? '');
    $scraped = bk_fip_fetch_sinta_author($sid, $nama);
    if (is_wp_error($scraped)) {
        wp_send_json_error(array('message' => $scraped->get_error_message()));
    }

    $scraped['slug'] = $slug;
    $updated = bk_fip_apply_sinta_updates(array($scraped));

    wp_send_json_success(array(
        'slug'       => $slug,
        'sinta_id'   => $sid,
        'updated'    => $updated,
        'publikasi'  => count($scraped['publikasi']),
        'penelitian' => count($scraped['penelitian']),
        'pengabdian' => count($scraped['pengabdian']),
        'buku'       => count($scraped['buku']),
        'metrics'    => $scraped['sinta_metrics'],
    ));
});

add_action('wp_ajax_bk_regenerate_sinta_token', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Akses ditolak.'));
    }
    check_ajax_referer('bk_sinta_ajax_nonce', 'nonce');
    $token = wp_generate_password(40, false, false);
    update_option('bk_sinta_sync_token', $token, false);
    wp_send_json_success(array('token' => $token));
});

// Direktori Dosen kini terpusat di dalam 🏛️ Portal PPB FIP
function bk_fip_register_direktori_dosen_menu() {}

// Clean admin sidebar: Pastikan TIDAK ADA menu liar/ganda di luar Portal PPB FIP
add_action('admin_menu', function() {
    remove_menu_page('edit.php'); // Berita sudah dipusatkan di dalam Portal PPB FIP
    remove_menu_page('bk-pengaturan-beranda');
    remove_menu_page('ppb-spotlight-beranda');
    remove_menu_page('bk-direktori-dosen');
    remove_menu_page('edit.php?post_type=dosen');
    remove_menu_page('edit.php?post_type=ppb_dosen');
    remove_menu_page('edit.php?post_type=ppb_dokumen');
    remove_menu_page('edit.php?post_type=ppb_jadwal');
    remove_menu_page('edit.php?post_type=ppb_prestasi');
    remove_menu_page('edit.php?post_type=ppb_karya');
    remove_menu_page('edit.php?post_type=ppb_template_dok');
    remove_menu_page('edit.php?post_type=ppb_inventaris_lab');
    remove_menu_page('edit.php?post_type=ppb_alur_sop');
}, 9999);

/**
 * Calculate hierarchy rank for academic sorting
 */
/**
 * Calculate hierarchy rank for academic sorting
 */
function bk_fip_get_dosen_hierarchy_rank($slug, $nama, $jabatan) {
    $n = strtolower($nama);
    $j = strtolower($jabatan);
    if ($slug === 'abdul-saman') return 1; // Dekan FIP UNM
    if ($slug === 'suciani-latif') return 2; // Ketua Jurusan PPB
    if ($slug === 'sahril-buchori') return 3; // Sekretaris Jurusan PPB
    if (strpos($n, 'prof.') !== false || strpos($j, 'guru besar') !== false) return 10;
    if (strpos($n, 'dr.') !== false || strpos($j, 'doktor') !== false) return 20;
    if (strpos($j, 'lektor kepala') !== false) return 30;
    return 40;
}

/**
 * Helper: determine academic tier metadata for a lecturer
 */
function bk_fip_get_dosen_tier_meta($nama, $jabatan) {
    $n = strtolower((string)$nama);
    $j = strtolower((string)$jabatan);
    if (strpos($n, 'prof.') !== false || strpos($j, 'guru besar') !== false) {
        return array('prof', 'Guru Besar', 'bk-tag-gold');
    } elseif (strpos($n, 'dr.') !== false || strpos($j, 'doktor') !== false) {
        return array('dr', 'Doktor', 'bk-tag-sage');
    }
    return array('dosen', 'Dosen PPB', '');
}

/**
 * Helper: compute 2-letter academic initials without titles
 */
function bk_fip_get_dosen_initials($name) {
    $parts = explode(',', (string)$name);
    $clean = $parts[0];
    $prefixes = array('prof', 'dr', 'dra', 'drs', 'ir', 'h', 'hj');
    $words = preg_split('/[\s\.]+/', $clean);
    $filtered = array();
    if (is_array($words)) {
        foreach ($words as $w) {
            $w = trim($w);
            if ($w !== '' && !in_array(strtolower($w), $prefixes, true)) {
                $filtered[] = $w;
            }
        }
    }
    if (count($filtered) >= 2) {
        return strtoupper(substr($filtered[0], 0, 1) . substr($filtered[1], 0, 1));
    } elseif (count($filtered) === 1) {
        return strtoupper(substr($filtered[0], 0, 2));
    }
    return 'BK';
}

/**
 * Helper: resolve lecturer photo URL cleanly
 */
function bk_fip_resolve_dosen_foto_url($raw_foto) {
    $raw_foto = trim((string)$raw_foto);
    if ($raw_foto === '') {
        return '';
    }
    if (function_exists('bk_resolve_theme_asset_url')) {
        $resolved = bk_resolve_theme_asset_url($raw_foto);
        return (strpos($resolved, '/') === 0 || preg_match('#^https?://#i', $resolved))
            ? $resolved
            : ('/wp-content/themes/bk-fip-wordpress/' . ltrim($resolved, '/'));
    }
    return (strpos($raw_foto, 'assets/') === 0)
        ? ('/wp-content/themes/bk-fip-wordpress/' . $raw_foto)
        : $raw_foto;
}

/**
 * Helper: build compact dossier map entry for a lecturer
 */
function bk_fip_build_dosen_dossier_entry($d) {
    $nama = trim((string)($d['nama_lengkap'] ?? ($d['nama'] ?? '')));
    $slug = trim((string)($d['slug'] ?? ''));
    if ($slug === '' || $nama === '') {
        return null;
    }

    $nip = trim((string)($d['nip'] ?? ''));
    $nidn = trim((string)($d['nidn'] ?? ''));
    $jabatan = trim((string)($d['jabatan_fungsional'] ?? 'Dosen Bimbingan dan Konseling'));

    $struktural = trim((string)($d['jabatan_struktural'] ?? ''));
    if ($slug === 'abdul-saman' && $struktural === '') $struktural = 'Dekan FIP UNM';
    if ($slug === 'suciani-latif' && $struktural === '') $struktural = 'Ketua Jurusan PPB';
    if ($slug === 'sahril-buchori' && $struktural === '') $struktural = 'Sekretaris Jurusan PPB';

    $unit = trim((string)($d['unit'] ?? 'S1 Bimbingan & Konseling'));
    $sinta_url = trim((string)($d['sinta_url'] ?? ''));
    $scholar_url = trim((string)($d['scholar_url'] ?? ''));
    $scopus_url = trim((string)($d['scopus_url'] ?? ''));

    $stat = is_array($d['stat'] ?? null) ? $d['stat'] : array();
    $pubs_raw = is_array($d['publikasi'] ?? null) ? $d['publikasi'] : array();
    $buku_raw = is_array($d['buku'] ?? null) ? $d['buku'] : array();
    $pen_raw  = is_array($d['penelitian'] ?? null) ? $d['penelitian'] : array();
    $pkm_raw  = is_array($d['pengabdian'] ?? null) ? $d['pengabdian'] : array();
    $mk_raw   = is_array($d['mata_kuliah'] ?? null) ? $d['mata_kuliah'] : array();

    $pub_cnt  = (int)($stat['publikasi'] ?? count($pubs_raw));
    $buku_cnt = (int)($stat['buku'] ?? count($buku_raw));
    $pen_cnt  = (int)($stat['penelitian'] ?? count($pen_raw));
    $pkm_cnt  = (int)($stat['pengabdian'] ?? count($pkm_raw));

    $kepakaran = is_array($d['kepakaran'] ?? null) ? $d['kepakaran'] : array();
    if (empty($kepakaran) && !empty($d['bidang_keahlian'])) {
        $splits = preg_split('/[,;\n]+/', (string)$d['bidang_keahlian']);
        if (is_array($splits)) {
            foreach ($splits as $sp) {
                $sp = trim($sp);
                if ($sp !== '') $kepakaran[] = $sp;
            }
        }
    }
    if (empty($kepakaran)) {
        $kepakaran = array('Bimbingan & Konseling', 'Psikologi Pendidikan');
    }

    list($tier_key, $badge_label) = bk_fip_get_dosen_tier_meta($nama, $jabatan);
    $init = bk_fip_get_dosen_initials($nama);
    $foto_url = bk_fip_resolve_dosen_foto_url($d['foto_link'] ?? '');

    $mk_compact = array();
    foreach (array_slice($mk_raw, 0, 16) as $mk) {
        if (!is_array($mk)) continue;
        $mk_meta = trim((string)(isset($mk['sks']) ? $mk['sks'] . ' SKS' : '')) . (!empty($mk['jenjang']) ? ' · ' . trim((string)$mk['jenjang']) : '');
        if (!empty($mk['semester']) || !empty($mk['sem'])) {
            $sem_v = (int) ($mk['semester'] ?? ($mk['sem'] ?? 0));
            if ($sem_v > 0) $mk_meta .= ' · Sem ' . $sem_v;
        }
        $mk_compact[] = array(
            trim((string)($mk['kode'] ?? '')),
            trim((string)($mk['nama'] ?? '')),
            $mk_meta,
            function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['rps_url'] ?? '') : trim((string)($mk['rps_url'] ?? '')),
            function_exists('ppb_clean_document_url') ? ppb_clean_document_url($mk['sap_url'] ?? '') : trim((string)($mk['sap_url'] ?? '')),
        );
    }

    $pub_compact = array();
    foreach (array_slice($pubs_raw, 0, 10) as $pub) {
        if (!is_array($pub)) continue;
        $pub_compact[] = array(
            trim((string)($pub['judul'] ?? '')),
            trim((string)($pub['tahun'] ?? '')),
            trim((string)($pub['media'] ?? ($pub['sumber'] ?? ''))),
            trim((string)($pub['akreditasi'] ?? ($pub['peran'] ?? ''))),
            trim((string)($pub['url'] ?? '')),
        );
    }

    $buku_compact = array();
    foreach (array_slice($buku_raw, 0, 10) as $bk) {
        if (!is_array($bk)) continue;
        $isbn_val = trim((string)($bk['isbn'] ?? ''));
        $buku_compact[] = array(
            trim((string)($bk['judul'] ?? '')),
            trim((string)($bk['tahun'] ?? '')),
            trim((string)($bk['penerbit'] ?? '')),
            ($isbn_val !== '' && $isbn_val !== '-') ? $isbn_val : '',
        );
    }

    $ris_compact = array();
    foreach (array_slice($pen_raw, 0, 10) as $ris) {
        if (!is_array($ris)) continue;
        $ris_compact[] = array(
            trim((string)($ris['judul'] ?? '')),
            trim((string)($ris['tahun'] ?? '')),
            trim((string)($ris['peran'] ?? '')),
            trim((string)($ris['sumber_dana'] ?? '')),
        );
    }

    $bio_plain = trim(wp_strip_all_tags((string)($d['bio'] ?? '')));
    if (strlen($bio_plain) > 280) {
        $bio_plain = substr($bio_plain, 0, 277) . '...';
    }

    return array(
        's'    => $slug,
        'n'    => $nama,
        'i'    => $init,
        't'    => $tier_key,
        'b'    => $badge_label,
        'jf'   => $jabatan,
        'js'   => $struktural,
        'u'    => $unit,
        'nip'  => $nip,
        'nidn' => $nidn,
        'r'    => trim((string)($d['ruang_kerja'] ?? '')),
        'bio'  => $bio_plain,
        'f'    => $foto_url,
        'kp'   => array_values(array_slice($kepakaran, 0, 6)),
        'si'   => $sinta_url,
        'sc'   => $scholar_url,
        'cp'   => $scopus_url,
        'st'   => array($pub_cnt, $buku_cnt, $pen_cnt, $pkm_cnt),
        'mk'   => $mk_compact,
        'pub'  => $pub_compact,
        'bk'   => $buku_compact,
        'ris'  => $ris_compact,
    );
}

/**
 * Helper: load compact dossier map indexed by lecturer slug
 */
function bk_fip_get_dosen_dossier_map($dosen_list = null) {
    if (!is_array($dosen_list)) {
        $cached_list = get_option('bk_dosen_data_cache', array());
        $json_file = get_template_directory() . '/dosen-data.json';
        $json_list = file_exists($json_file) ? json_decode((string)file_get_contents($json_file), true) : array();
        if (!empty($cached_list) && is_array($cached_list) && (empty($json_list) || count($cached_list) >= count($json_list))) {
            $dosen_list = $cached_list;
        } elseif (!empty($json_list) && is_array($json_list)) {
            $dosen_list = $json_list;
        } else {
            $dosen_list = is_array($cached_list) ? $cached_list : array();
        }
    }
    $dossier_map = array();
    if (!is_array($dosen_list)) {
        return $dossier_map;
    }
    foreach ($dosen_list as $d) {
        if (!is_array($d)) continue;
        $entry = bk_fip_build_dosen_dossier_entry($d);
        if (is_array($entry) && !empty($entry['s'])) {
            $dossier_map[$entry['s']] = $entry;
        }
    }
    return $dossier_map;
}

/**
 * Build pure HTML for the public /profil/dosen/ page from ordered lecturer list
 * Interactive dossier drawer and JS controller are rendered in wp_footer
 * so WordPress KSES/wptexturize never strips or corrupts them.
 */
function bk_fip_build_dosen_roster_html($dosen_list) {
    if (empty($dosen_list) || !is_array($dosen_list)) return '';

    $count_total = count($dosen_list);
    $tier_counts = array('prof' => 0, 'dr' => 0, 'dosen' => 0);
    $cards_html = '';

    foreach ($dosen_list as $d) {
        $nama = trim((string)($d['nama_lengkap'] ?? ($d['nama'] ?? '')));
        $nip = trim((string)($d['nip'] ?? ''));
        $jabatan = trim((string)($d['jabatan_fungsional'] ?? 'Dosen Bimbingan dan Konseling'));
        $slug = trim((string)($d['slug'] ?? ''));
        if ($slug === '') continue;

        $struktural = trim((string)($d['jabatan_struktural'] ?? ''));
        if ($slug === 'abdul-saman' && $struktural === '') $struktural = 'Dekan FIP UNM';
        if ($slug === 'suciani-latif' && $struktural === '') $struktural = 'Ketua Jurusan PPB';
        if ($slug === 'sahril-buchori' && $struktural === '') $struktural = 'Sekretaris Jurusan PPB';

        $profile_url = '/profil/dosen/' . $slug . '/';
        $sinta_url = trim((string)($d['sinta_url'] ?? ''));

        $stat = is_array($d['stat'] ?? null) ? $d['stat'] : array();
        $pubs_raw = is_array($d['publikasi'] ?? null) ? $d['publikasi'] : array();
        $buku_raw = is_array($d['buku'] ?? null) ? $d['buku'] : array();
        $pen_raw  = is_array($d['penelitian'] ?? null) ? $d['penelitian'] : array();

        $pub_cnt  = (int)($stat['publikasi'] ?? count($pubs_raw));
        $buku_cnt = (int)($stat['buku'] ?? count($buku_raw));
        $pen_cnt  = (int)($stat['penelitian'] ?? count($pen_raw));

        $kepakaran = is_array($d['kepakaran'] ?? null) ? $d['kepakaran'] : array();
        if (empty($kepakaran) && !empty($d['bidang_keahlian'])) {
            $splits = preg_split('/[,;\n]+/', (string)$d['bidang_keahlian']);
            if (is_array($splits)) {
                foreach ($splits as $sp) {
                    $sp = trim($sp);
                    if ($sp !== '') $kepakaran[] = $sp;
                }
            }
        }
        if (empty($kepakaran)) {
            $kepakaran = array('Bimbingan & Konseling', 'Psikologi Pendidikan');
        }

        list($tier_key, $badge_label, $badge_cls) = bk_fip_get_dosen_tier_meta($nama, $jabatan);
        if (isset($tier_counts[$tier_key])) {
            $tier_counts[$tier_key]++;
        }
        $is_prof = ($tier_key === 'prof');
        $init = bk_fip_get_dosen_initials($nama);
        $foto_url = bk_fip_resolve_dosen_foto_url($d['foto_link'] ?? '');

        $role_line = $struktural !== '' ? ($struktural . ' · ' . $jabatan) : $jabatan;
        $tags_html = '';
        foreach (array_slice($kepakaran, 0, 3) as $t) {
            $tags_html .= '<span class="bk-tag">' . esc_html($t) . '</span>';
        }

        $mk_search = array();
        if (!empty($d['mata_kuliah']) && is_array($d['mata_kuliah'])) {
            foreach ($d['mata_kuliah'] as $mk_item) {
                if (!is_array($mk_item)) continue;
                if (!empty($mk_item['kode'])) $mk_search[] = (string) $mk_item['kode'];
                if (!empty($mk_item['nama'])) $mk_search[] = (string) $mk_item['nama'];
            }
        }
        $search_text = esc_attr(strtolower($nama . ' ' . $nip . ' ' . $jabatan . ' ' . $struktural . ' ' . $badge_label . ' ' . implode(' ', $kepakaran) . ' ' . implode(' ', $mk_search)));

        if ($foto_url !== '') {
            $av_cls = 'bk-avatar' . ($is_prof ? ' is-prof' : '') . ' has-photo';
            $avatar_html = '<div class="' . $av_cls . '" data-fb-init="' . esc_attr($init) . '"><img src="' . esc_url($foto_url) . '" alt="' . esc_attr($nama) . '" loading="lazy"></div>';
        } else {
            $av_cls = 'bk-avatar' . ($is_prof ? ' is-prof' : '');
            $avatar_html = '<div class="' . $av_cls . '">' . esc_html($init) . '</div>';
        }

        $tag_classes = trim('bk-tag ' . $badge_cls);
        $sinta_quick = $sinta_url !== ''
            ? '<span style="color:var(--border-strong);">&bull;</span><a href="' . esc_url($sinta_url) . '" target="_blank" rel="noopener noreferrer" style="color:var(--forest);font-weight:700;font-size:11px;text-decoration:none;display:inline-flex;align-items:center;gap:3px;">SINTA &#8599;</a>'
            : '';

        $cards_html .= '<article class="bk-dosen-card-item" data-slug="' . esc_attr($slug) . '" data-tier="' . esc_attr($tier_key) . '" data-text="' . $search_text . '">
            <div class="bk-dosen-card-top">
                <div class="bk-dosen-card-head">
                    <div class="bk-dosen-identity">
                        ' . $avatar_html . '
                        <div style="min-width:0;">
                            <h3 class="bk-dosen-card-name" style="margin-bottom:2px;min-height:0;"><a href="' . esc_url($profile_url) . '" data-open-dossier="' . esc_attr($slug) . '">' . esc_html($nama) . '</a></h3>
                            <div class="bk-dosen-meta-sub">' . esc_html($role_line) . '</div>
                            ' . ($nip !== '' ? '<div class="bk-fm" style="font-size:11px;color:var(--ink-muted);margin-top:2px;">NIP. ' . esc_html($nip) . '</div>' : '') . '
                        </div>
                    </div>
                    <span class="' . esc_attr($tag_classes) . '" style="flex-shrink:0;">' . esc_html($badge_label) . '</span>
                </div>
                <div class="bk-dosen-tags" style="margin-top:12px;">' . $tags_html . '</div>
            </div>
            <div class="bk-dosen-card-bottom">
                <div class="dosen-stats-row">
                    <span>Publikasi: <strong>' . $pub_cnt . '</strong></span>
                    <span style="color:var(--border-strong);">&bull;</span>
                    <span>Buku: <strong>' . $buku_cnt . '</strong></span>
                    <span style="color:var(--border-strong);">&bull;</span>
                    <span>Riset: <strong>' . $pen_cnt . '</strong></span>
                    ' . $sinta_quick . '
                </div>
                <div class="bk-dosen-actions">
                    <button type="button" class="bk-btn bk-btn-primary bk-btn-sm" style="flex:1;" data-open-dossier="' . esc_attr($slug) . '" data-dosen-url="' . esc_url($profile_url) . '">Lihat Dossier &rarr;</button>
                    <a href="' . esc_url($profile_url) . '" class="bk-btn bk-btn-secondary bk-btn-sm" title="Buka halaman profil ' . esc_attr($nama) . '">Profil Lengkap</a>
                </div>
            </div>
        </article>' . "\n";
    }

    $full_html = '<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background:#FAF8F5;color:#1F2937;font-family:\'Plus Jakarta Sans\',sans-serif;line-height:1.6;">
    <header class="bk-page-head">
        <div class="bk-container">
            <div class="bk-crumb">
                <a href="/">Beranda</a>
                <span>/</span>
                <a href="/profil/">Profil &amp; Mutu</a>
                <span>/</span>
                <span>Dosen &amp; Tenaga Pengajar</span>
            </div>
            <h1>Dosen &amp; Tenaga Pengajar</h1>
            <p class="bk-page-lead">Telusuri profil akademik, publikasi ilmiah SINTA/Scopus, penelitian &amp; hibah, pengabdian masyarakat, dan rekam jejak kepakaran para akademisi Jurusan PPB FIP UNM.</p>
        </div>
    </header>

    <section class="bk-band">
        <div class="bk-container">
            <nav class="bk-tabs" aria-label="Sub-menu Profil &amp; Mutu">
                <a href="/profil/visi-keilmuan/" class="bk-tab-btn">Visi &amp; Keilmuan</a>
                <a href="/profil/sejarah/" class="bk-tab-btn">Sejarah &amp; Pimpinan</a>
                <a href="/profil/dosen/" class="bk-tab-btn is-active" aria-current="page">Dosen &amp; Kepakaran</a>
                <a href="/akreditasi/" class="bk-tab-btn">Akreditasi LAMDIK</a>
                <a href="/profil/kerjasama-tridharma/" class="bk-tab-btn">Kemitraan &amp; Sarana</a>
            </nav>
            <div style="margin:-10px 0 28px;display:flex;flex-wrap:wrap;align-items:center;gap:12px;">
                <div class="bk-seg-control" role="navigation" aria-label="Bagian terkait">
                    <a href="/profil/dosen/" class="bk-seg-btn is-active" aria-current="page">Direktori ' . $count_total . ' Dosen</a>
                    <a href="/profil/buku-dosen/" class="bk-seg-btn">Koleksi Buku Dosen</a>
                    <a href="/profil/kepakaran-dosen/" class="bk-seg-btn">Peta Kepakaran &amp; SINTA</a>
                </div>
            </div>

            <div class="bk-ugm-head" style="margin-bottom:20px;">
                <div class="bk-ugm-title-block">
                    <div class="bk-kicker">Sumber Daya Manusia</div>
                    <h2 class="bk-section-h2">Direktori ' . $count_total . ' Dosen &amp; Kepakaran</h2>
                </div>
                <div class="bk-sec-rule" aria-hidden="true"></div>
                <div style="font-size:12.5px;color:var(--ink-muted);">Klik Dossier untuk membuka rekam jejak SINTA, matkul &amp; buku</div>
            </div>

            <div class="bk-toolbar" style="margin-bottom:16px;">
                <input type="search" id="bk-dosen-search" class="bk-inp" placeholder="Cari nama dosen, NIP, atau topik riset..." aria-label="Cari nama dosen, NIP, atau topik riset">
                <div class="bk-filter-pills" id="bk-dosen-tier-pills" role="tablist" aria-label="Filter Jenjang Akademik Dosen">
                    <button type="button" class="bk-pill is-active" data-dtier="all" role="tab" aria-selected="true">Semua (' . $count_total . ')</button>
                    <button type="button" class="bk-pill" data-dtier="prof" role="tab" aria-selected="false">Guru Besar (' . (int)$tier_counts['prof'] . ')</button>
                    <button type="button" class="bk-pill" data-dtier="dr" role="tab" aria-selected="false">Doktor (' . (int)$tier_counts['dr'] . ')</button>
                    <button type="button" class="bk-pill" data-dtier="dosen" role="tab" aria-selected="false">Dosen (' . (int)$tier_counts['dosen'] . ')</button>
                </div>
            </div>

            <div id="bk-dosen-count" style="font-size:13px;color:var(--ink-muted);margin:0 0 18px;">
                Menampilkan seluruh <strong style="color:var(--forest);">' . $count_total . '</strong> dosen &amp; tenaga pengajar Jurusan PPB
            </div>

            <div id="bk-dosen-grid" class="bk-dosen-grid-container">
' . $cards_html . '
            </div>

            <div class="bk-empty" id="bk-dosen-empty" style="display:none;margin-top:20px;">
                <p style="margin:0 0 12px;">Tidak ada dosen yang sesuai dengan filter atau pencarian Anda.</p>
                <button type="button" class="bk-btn bk-btn-secondary bk-btn-sm" id="bk-dosen-reset">Reset Filter &amp; Pencarian</button>
            </div>

            <div class="bk-card bk-card-sage" style="flex-direction:row;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:18px;margin-top:36px;">
                <div style="max-width:640px;">
                    <div class="bk-kicker">Riset &amp; Pengembangan Keilmuan</div>
                    <h3 class="bk-fd" style="font-size:21px;color:var(--forest);margin:0 0 6px;">Ingin Mengetahui Arah Riset &amp; Pengabdian Dosen?</h3>
                    <p style="font-size:13.5px;color:var(--ink-2);margin:0;">Pelajari Roadmap Penelitian 3 Pilar Keilmuan, Lembar Pengesahan Senat, dan Research Groups Program Studi.</p>
                </div>
                <a href="/profil/roadmap/" class="bk-btn bk-btn-gold">Buka Roadmap Riset &amp; PKM &rarr;</a>
            </div>
        </div>
    </section>
</div>';

    return $full_html;
}

/**
 * Determine whether current public request is the Dosen Roster Directory page
 */
function bk_fip_is_dosen_roster_page() {
    if (is_admin()) {
        return false;
    }
    if (!empty($GLOBALS['current_dosen']) || !empty($_GET['dosen'])) {
        return false;
    }
    if (!empty($GLOBALS['bk_has_dosen_roster'])) {
        return true;
    }
    $req_uri = isset($_SERVER['REQUEST_URI']) ? (string)$_SERVER['REQUEST_URI'] : '';
    $path = parse_url($req_uri, PHP_URL_PATH) ?: '';
    $home_path = parse_url(home_url(), PHP_URL_PATH) ?: '';
    if (!empty($home_path) && $home_path !== '/' && strpos($path, $home_path) === 0) {
        $path = substr($path, strlen($home_path));
    }
    $path = trim($path, '/');
    if (strpos($path, 'yamato/') === 0) {
        $path = substr($path, 7);
    } elseif ($path === 'yamato') {
        $path = '';
    }
    $path = trim($path, '/');
    if ($path === 'profil/dosen' || $path === 'dosen') {
        return true;
    }
    if (function_exists('is_page') && is_page('dosen') && $path !== 'profil/buku-dosen' && $path !== 'profil/kepakaran-dosen') {
        return true;
    }
    return false;
}

/**
 * Render slide-over Dossier Drawer and interactive JS directly in wp_footer
 * to guarantee full functionality on live WordPress without KSES/wptexturize interference.
 */
function bk_fip_render_dosen_dossier_footer() {
    if (!bk_fip_is_dosen_roster_page()) {
        return;
    }

    $dossier_map = bk_fip_get_dosen_dossier_map();
    $dossier_json = str_replace('</', '<\/', wp_json_encode($dossier_map, JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    if (!$dossier_json) {
        $dossier_json = '{}';
    }
    $total_all = count($dossier_map);
    ?>
<div class="bk-backdrop" id="bk-dsn-backdrop" data-bk-footer-overlay="1"></div>
<aside class="bk-drawer" id="bk-dsn-drawer" data-bk-footer-overlay="1" aria-label="Dossier Akademik Dosen" aria-hidden="true">
    <div class="bk-drawer-head">
        <div>
            <div class="bk-kicker" id="bk-dsn-drw-kicker">DOSSIER DOSEN PPB</div>
            <h2 class="bk-fd" style="font-size:20px;margin:4px 0 0;line-height:1.25;" id="bk-dsn-drw-title">Profil Dosen</h2>
        </div>
        <button type="button" class="bk-icon-close" id="bk-dsn-drw-close" aria-label="Tutup Dossier">&#10005;</button>
    </div>
    <div class="bk-drawer-body" id="bk-dsn-drw-body"></div>
</aside>
<script id="bk-dosen-dossier-controller">
window.BK_DOSEN_DOSSIER = <?php echo $dossier_json; ?>;
(function(){
    var DATA = (window.BK_DOSEN_DOSSIER && typeof window.BK_DOSEN_DOSSIER === "object") ? window.BK_DOSEN_DOSSIER : {};
    var legacyJsonEl = document.getElementById("bk-dosen-dossier-data");
    if (legacyJsonEl) {
        if (!Object.keys(DATA).length) {
            try { DATA = JSON.parse(legacyJsonEl.textContent || "{}") || {}; } catch (e) {}
        }
        if (legacyJsonEl.parentNode) legacyJsonEl.parentNode.removeChild(legacyJsonEl);
    }
    document.querySelectorAll("#bk-dsn-backdrop:not([data-bk-footer-overlay]), #bk-dsn-drawer:not([data-bk-footer-overlay])").forEach(function(staleEl) {
        if (staleEl && staleEl.parentNode) staleEl.parentNode.removeChild(staleEl);
    });

    var bdp = document.querySelector('#bk-dsn-backdrop[data-bk-footer-overlay="1"]') || document.getElementById("bk-dsn-backdrop");
    var drw = document.querySelector('#bk-dsn-drawer[data-bk-footer-overlay="1"]') || document.getElementById("bk-dsn-drawer");
    var drwK = document.getElementById("bk-dsn-drw-kicker");
    var drwT = document.getElementById("bk-dsn-drw-title");
    var drwB = document.getElementById("bk-dsn-drw-body");
    var drwX = document.getElementById("bk-dsn-drw-close");

    var searchIn = document.getElementById("bk-dosen-search");
    var pillsWrap = document.getElementById("bk-dosen-tier-pills");
    var gridEl = document.getElementById("bk-dosen-grid");
    var countEl = document.getElementById("bk-dosen-count");
    var emptyEl = document.getElementById("bk-dosen-empty");
    var resetBtn = document.getElementById("bk-dosen-reset");
    var activeTier = "all";
    var totalAll = gridEl ? gridEl.querySelectorAll(".bk-dosen-card-item").length : <?php echo (int)$total_all; ?>;

    var bp = window.location.pathname.indexOf("/yamato/") === 0 ? "/yamato" : "";
    function routeUrl(p) { return (bp && p && p.indexOf("/") === 0 && p.indexOf(bp + "/") !== 0) ? (bp + p) : p; }
    function assetUrl(u) { return (bp && u && u.indexOf("/wp-content/") === 0 && u.indexOf(bp + "/") !== 0) ? (bp + u) : u; }

    function initAvatars() {
        var grid = document.getElementById("bk-dosen-grid");
        if (!grid) return;
        grid.querySelectorAll(".bk-avatar img").forEach(function(im) {
            var s = im.getAttribute("src") || "";
            if (bp && s.indexOf("/wp-content/") === 0) im.src = assetUrl(s);
            if (!im.dataset.bkErrBound) {
                im.dataset.bkErrBound = "1";
                im.addEventListener("error", function() {
                    var p = this.parentElement;
                    if (p) {
                        p.classList.remove("has-photo");
                        p.textContent = p.getAttribute("data-fb-init") || "BK";
                    }
                });
            }
            if (im.complete && im.naturalWidth === 0) {
                var p = im.parentElement;
                if (p) {
                    p.classList.remove("has-photo");
                    p.textContent = p.getAttribute("data-fb-init") || "BK";
                }
            }
        });
    }

    function esc(s) {
        return String(s == null ? "" : s)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;");
    }

    function applyFilters() {
        var q = searchIn ? searchIn.value.toLowerCase().trim() : "";
        var grid = document.getElementById("bk-dosen-grid");
        var cards = grid ? grid.querySelectorAll(".bk-dosen-card-item") : [];
        var vis = 0;
        for (var i = 0; i < cards.length; i++) {
            var c = cards[i];
            var t = c.getAttribute("data-tier") || "";
            var txt = (c.getAttribute("data-text") || "").toLowerCase();
            var okTier = (activeTier === "all" || t === activeTier);
            var okQ = (!q || txt.indexOf(q) !== -1);
            if (okTier && okQ) {
                c.style.display = "flex";
                vis++;
            } else {
                c.style.display = "none";
            }
        }
        if (emptyEl) emptyEl.style.display = vis === 0 ? "block" : "none";
        if (grid) grid.style.display = vis === 0 ? "none" : "grid";
        if (countEl) {
            if (q || activeTier !== "all") {
                countEl.innerHTML = "Menampilkan <strong style=\"color:var(--forest);\">" + vis + "</strong> dari " + totalAll + " dosen" + (q ? " untuk pencarian &ldquo;" + esc(q) + "&rdquo;" : "");
            } else {
                countEl.innerHTML = "Menampilkan seluruh <strong style=\"color:var(--forest);\">" + totalAll + "</strong> dosen &amp; tenaga pengajar Jurusan PPB";
            }
        }
    }

    function sealHtml(d) {
        return "<div class=\"bk-academic-seal\"><div class=\"bk-seal-emblem\">" + esc(d.i || "BK") + "</div><div class=\"bk-seal-label\">PROFIL AKADEMISI<strong>PPB · FIP UNM</strong></div></div>";
    }

    function closeDrawer() {
        if (!drw || !bdp) return;
        bdp.classList.remove("is-open");
        drw.classList.remove("is-open");
        drw.setAttribute("aria-hidden", "true");
    }

    function openDossier(slug, triggerEl) {
        var d = slug ? DATA[slug] : null;
        if (!d || !drw || !bdp || !drwB) {
            var fallbackUrl = triggerEl ? (triggerEl.getAttribute("href") || triggerEl.getAttribute("data-dosen-url") || (slug ? ("/profil/dosen/" + slug + "/") : "")) : (slug ? ("/profil/dosen/" + slug + "/") : "");
            if (fallbackUrl) {
                window.location.href = routeUrl(fallbackUrl);
            }
            return;
        }
        var activeTab = "info";
        var fullUrl = routeUrl("/profil/dosen/" + d.s + "/");
        var hasPhoto = Boolean(d.f);
        var heroMedia = hasPhoto
            ? "<div class=\"bk-drawer-portrait\"><img src=\"" + esc(assetUrl(d.f)) + "\" alt=\"" + esc(d.n) + "\"></div>"
            : sealHtml(d);
        var idsParts = [];
        if (d.nip) idsParts.push("NIP. " + esc(d.nip));
        if (d.nidn) idsParts.push("NIDN. " + esc(d.nidn));
        if (d.r) idsParts.push(esc(d.r));
        var heroIds = idsParts.length ? idsParts.join(" · ") : "Dosen Tetap Prodi BK FIP UNM";
        var badges = "<span class=\"bk-badge-hero " + (d.t === "prof" ? "is-gold" : "") + "\">" + esc(d.b) + "</span>" +
            (d.js ? "<span class=\"bk-badge-hero is-struct\">" + esc(d.js) + "</span>" : "") +
            "<span class=\"bk-badge-hero\">" + esc(d.u || "Prodi BK FIP UNM") + "</span>";
        var heroActions = "<div style=\"display:flex;gap:8px;flex-wrap:wrap;\">" +
            (d.si ? "<a href=\"" + esc(d.si) + "\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bk-btn bk-btn-gold bk-btn-sm\">Profil SINTA &#8599;</a>" : "") +
            "<a href=\"" + esc(fullUrl) + "\" class=\"bk-btn bk-btn-glass bk-btn-sm\">Halaman Lengkap &rarr;</a>" +
            "</div>";
        var heroHtml = "<div class=\"bk-drawer-dosen-hero\">" + heroMedia + "<div class=\"bk-drawer-hero-meta\"><div class=\"bk-drawer-badges\">" + badges + "</div><h3 class=\"bk-drawer-hero-name\">" + esc(d.n) + "</h3><p class=\"bk-drawer-hero-ids\">" + heroIds + "</p>" + heroActions + "</div></div>";
        var st = d.st || [0, 0, 0, 0];
        var kpiHtml = "<div class=\"bk-drawer-kpi-grid\">" +
            "<div class=\"bk-drawer-kpi-box\"><strong>" + Number(st[0] || 0) + "</strong><span>Publikasi</span></div>" +
            "<div class=\"bk-drawer-kpi-box\"><strong>" + Number(st[1] || 0) + "</strong><span>Buku / HKI</span></div>" +
            "<div class=\"bk-drawer-kpi-box\"><strong>" + Number(st[2] || 0) + "</strong><span>Penelitian</span></div>" +
            "</div>";

        function renderBody() {
            var pubArr = d.pub || [];
            var bkArr = d.bk || [];
            var risArr = d.ris || [];
            var tabsHtml = "<div class=\"bk-drawer-tabs\" role=\"tablist\">" +
                "<button type=\"button\" class=\"bk-drawer-tab " + (activeTab === "info" ? "is-active" : "") + "\" data-dsn-tab=\"info\">Ringkasan &amp; Matkul</button>" +
                "<button type=\"button\" class=\"bk-drawer-tab " + (activeTab === "pub" ? "is-active" : "") + "\" data-dsn-tab=\"pub\">Publikasi (" + Number(st[0] || pubArr.length) + ")</button>" +
                "<button type=\"button\" class=\"bk-drawer-tab " + (activeTab === "bk" ? "is-active" : "") + "\" data-dsn-tab=\"bk\">Buku (" + Number(st[1] || bkArr.length) + ")</button>" +
                "<button type=\"button\" class=\"bk-drawer-tab " + (activeTab === "ris" ? "is-active" : "") + "\" data-dsn-tab=\"ris\">Penelitian (" + Number(st[2] || risArr.length) + ")</button>" +
                "</div>";
            var panelHtml = "";
            if (activeTab === "info") {
                var mkArr = d.mk || [];
                panelHtml = "<ul class=\"bk-spec-list\" style=\"margin-bottom:16px;\">" +
                    "<li><span style=\"color:var(--ink-muted);\">Jabatan</span><strong style=\"text-align:right;\">" + esc(d.js ? d.js + " · " + d.jf : d.jf) + "</strong></li>" +
                    (d.nip ? "<li><span style=\"color:var(--ink-muted);\">NIP</span><span class=\"bk-fm\" style=\"font-size:12px;\">" + esc(d.nip) + "</span></li>" : "") +
                    "<li><span style=\"color:var(--ink-muted);\">Kepakaran</span><span style=\"text-align:right;\">" + esc((d.kp || []).join(", ")) + "</span></li>" +
                    "</ul>" +
                    (d.bio ? "<p style=\"font-size:13.5px;color:var(--ink-2);line-height:1.65;margin:0 0 16px;\">" + esc(d.bio) + "</p>" : "") +
                    "<div class=\"bk-kicker\" style=\"margin-bottom:8px;\">Mata Kuliah Diampu (" + mkArr.length + ")</div>" +
                    (mkArr.length
                        ? "<div class=\"bk-hairline-list\" style=\"padding:0 14px;margin-bottom:16px;\">" + mkArr.map(function(m) {
                            var linksHtml = "";
                            if (m[3] || m[4]) {
                                linksHtml = "<div style=\"display:flex;gap:6px;flex-wrap:wrap;margin-top:5px;\">" +
                                    (m[3] ? "<a href=\"" + esc(m[3]) + "\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"display:inline-flex;align-items:center;gap:3px;font-size:10.5px;font-weight:700;color:#0D3B22;background:#E8F0EA;border:1px solid #C5DACB;padding:2px 8px;border-radius:999px;text-decoration:none;\">📄 RPS &#8599;</a>" : "") +
                                    (m[4] ? "<a href=\"" + esc(m[4]) + "\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"display:inline-flex;align-items:center;gap:3px;font-size:10.5px;font-weight:700;color:#92400E;background:#FEF3C7;border:1px solid #FDE68A;padding:2px 8px;border-radius:999px;text-decoration:none;\">🗓️ SAP &#8599;</a>" : "") +
                                    "</div>";
                            }
                            return "<div class=\"bk-drawer-item\"><strong>" + esc((m[0] ? m[0] + " — " : "") + m[1]) + "</strong>" + (m[2] ? "<div class=\"bk-fm\" style=\"font-size:11.5px;color:var(--ink-muted);margin-top:2px;\">" + esc(m[2]) + "</div>" : "") + linksHtml + "</div>";
                        }).join("") + "</div>"
                        : "<div class=\"bk-empty\" style=\"padding:18px;margin-bottom:16px;\">Daftar lengkap pengampuan kelas tersedia pada halaman profil dosen.</div>") +
                    "<div style=\"display:flex;gap:8px;flex-wrap:wrap;\">" +
                    "<a href=\"" + esc(fullUrl) + "\" class=\"bk-btn bk-btn-primary bk-btn-sm\">Buka Halaman Profil Lengkap &rarr;</a>" +
                    (d.sc ? "<a href=\"" + esc(d.sc) + "\" target=\"_blank\" rel=\"noopener noreferrer\" class=\"bk-btn bk-btn-secondary bk-btn-sm\">Google Scholar &#8599;</a>" : "") +
                    "</div>";
            } else if (activeTab === "pub") {
                panelHtml = pubArr.length
                    ? "<div class=\"bk-hairline-list\" style=\"padding:0 14px;\">" + pubArr.map(function(p) {
                        var titleEl = p[4] ? "<a href=\"" + esc(p[4]) + "\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"color:var(--ink);font-weight:600;text-decoration:none;\">" + esc(p[0]) + " &#8599;</a>" : "<strong>" + esc(p[0]) + "</strong>";
                        return "<div class=\"bk-drawer-item\"><div style=\"font-size:13.5px;line-height:1.5;\">" + titleEl + "</div><div style=\"display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:6px;\">" +
                            (p[1] ? "<span class=\"bk-tag\">" + esc(p[1]) + "</span>" : "") +
                            (p[3] ? "<span class=\"bk-tag bk-tag-sage\">" + esc(p[3]) + "</span>" : "") +
                            (p[2] ? "<span style=\"font-size:12px;color:var(--ink-muted);\">" + esc(p[2]) + "</span>" : "") +
                            "</div></div>";
                    }).join("") + "</div>" +
                    (Number(st[0] || 0) > pubArr.length ? "<p style=\"font-size:12.5px;color:var(--ink-muted);margin:12px 0 0;\">Menampilkan " + pubArr.length + " dari " + Number(st[0]) + " publikasi terbaru. <a href=\"" + esc(fullUrl) + "\" style=\"color:var(--forest);font-weight:600;\">Lihat semua &rarr;</a></p>" : "")
                    : "<div class=\"bk-empty\" style=\"padding:24px;\">Rekam jejak publikasi selengkapnya tersinkronisasi melalui tautan SINTA dosen.</div>";
            } else if (activeTab === "bk") {
                panelHtml = bkArr.length
                    ? "<div class=\"bk-hairline-list\" style=\"padding:0 14px;\">" + bkArr.map(function(b) {
                        return "<div class=\"bk-drawer-item\"><strong style=\"font-size:13.5px;line-height:1.5;display:block;\">" + esc(b[0]) + "</strong><div style=\"font-size:12px;color:var(--ink-muted);margin-top:4px;\">" + esc([b[2], b[1], b[3] ? "ISBN " + b[3] : ""].filter(Boolean).join(" · ")) + "</div></div>";
                    }).join("") + "</div>" +
                    (Number(st[1] || 0) > bkArr.length ? "<p style=\"font-size:12.5px;color:var(--ink-muted);margin:12px 0 0;\">Menampilkan " + bkArr.length + " dari " + Number(st[1]) + " buku/HKI. <a href=\"" + esc(fullUrl) + "\" style=\"color:var(--forest);font-weight:600;\">Lihat semua &rarr;</a></p>" : "")
                    : "<div class=\"bk-empty\" style=\"padding:24px;\">Belum ada entri buku/monograf terdata di katalog ringkas ini.</div>";
            } else {
                panelHtml = risArr.length
                    ? "<div class=\"bk-hairline-list\" style=\"padding:0 14px;\">" + risArr.map(function(r) {
                        return "<div class=\"bk-drawer-item\"><strong style=\"font-size:13.5px;line-height:1.5;display:block;\">" + esc(r[0]) + "</strong><div style=\"display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-top:6px;\">" +
                            (r[1] ? "<span class=\"bk-tag\">" + esc(r[1]) + "</span>" : "") +
                            (r[2] ? "<span class=\"bk-tag bk-tag-sage\">" + esc(r[2]) + "</span>" : "") +
                            (r[3] ? "<span style=\"font-size:12px;color:var(--ink-muted);\">" + esc(r[3]) + "</span>" : "") +
                            "</div></div>";
                    }).join("") + "</div>" +
                    (Number(st[2] || 0) > risArr.length ? "<p style=\"font-size:12.5px;color:var(--ink-muted);margin:12px 0 0;\">Menampilkan " + risArr.length + " dari " + Number(st[2]) + " hibah riset. <a href=\"" + esc(fullUrl) + "\" style=\"color:var(--forest);font-weight:600;\">Lihat semua &rarr;</a></p>" : "")
                    : "<div class=\"bk-empty\" style=\"padding:24px;\">Data hibah penelitian tersinkronisasi melalui portal MIS PNBP UNM.</div>";
            }
            drwB.innerHTML = heroHtml + kpiHtml + tabsHtml + panelHtml;
            var portImg = drwB.querySelector(".bk-drawer-portrait img");
            if (portImg) {
                portImg.addEventListener("error", function() {
                    var box = this.closest(".bk-drawer-portrait");
                    if (box) box.outerHTML = sealHtml(d);
                });
            }
            drwB.querySelectorAll("[data-dsn-tab]").forEach(function(tb) {
                tb.addEventListener("click", function() {
                    activeTab = this.getAttribute("data-dsn-tab") || "info";
                    drwB.scrollTop = 0;
                    renderBody();
                });
            });
        }
        if (drwK) drwK.textContent = String(d.b || "DOSEN PPB").toUpperCase();
        if (drwT) drwT.textContent = d.n || "Profil Dosen";
        drwB.scrollTop = 0;
        renderBody();
        bdp.classList.add("is-open");
        drw.classList.add("is-open");
        drw.setAttribute("aria-hidden", "false");
    }

    initAvatars();

    if (searchIn) searchIn.addEventListener("input", applyFilters);
    if (pillsWrap) {
        pillsWrap.addEventListener("click", function(e) {
            var pill = e.target.closest("[data-dtier]");
            if (!pill) return;
            activeTier = pill.getAttribute("data-dtier") || "all";
            pillsWrap.querySelectorAll("[data-dtier]").forEach(function(b) {
                var act = (b === pill);
                b.classList.toggle("is-active", act);
                b.setAttribute("aria-selected", act ? "true" : "false");
            });
            applyFilters();
        });
    }
    if (resetBtn) {
        resetBtn.addEventListener("click", function() {
            activeTier = "all";
            if (searchIn) searchIn.value = "";
            if (pillsWrap) {
                pillsWrap.querySelectorAll("[data-dtier]").forEach(function(b) {
                    var act = b.getAttribute("data-dtier") === "all";
                    b.classList.toggle("is-active", act);
                    b.setAttribute("aria-selected", act ? "true" : "false");
                });
            }
            applyFilters();
        });
    }

    document.addEventListener("click", function(e) {
        var trig = e.target.closest("[data-open-dossier]");
        if (!trig) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || (typeof e.button === "number" && e.button !== 0)) {
            return;
        }
        e.preventDefault();
        openDossier(trig.getAttribute("data-open-dossier"), trig);
    });

    if (drwX) drwX.addEventListener("click", closeDrawer);
    if (bdp) bdp.addEventListener("click", closeDrawer);
    document.addEventListener("keydown", function(e) {
        if (e.key === "Escape" && drw && drw.classList.contains("is-open")) {
            closeDrawer();
        }
    });
    window.bkOpenDosenDossier = openDossier;
})();
</script>
    <?php
}
add_action('wp_footer', 'bk_fip_render_dosen_dossier_footer', 95);

/**
 * Rebuild the public /profil/dosen/ page and pages-data.json from ordered lecturer list
 */
function bk_fip_rebuild_dosen_roster_html($dosen_list) {
    global $wpdb;
    $full_html = bk_fip_build_dosen_roster_html($dosen_list);
    if ($full_html === '') return false;

    // 1. Update pages-data.json
    $pages_file = get_template_directory() . '/pages-data.json';
    if (file_exists($pages_file)) {
        $pages = json_decode(file_get_contents($pages_file), true);
        if (is_array($pages)) {
            foreach ($pages as &$p) {
                if (($p['slug'] ?? '') === 'dosen') {
                    $p['content'] = $full_html;
                    break;
                }
            }
            file_put_contents($pages_file, json_encode($pages, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
    }

    // 2. Direct Update to WordPress posts DB table if exists
    $post_id = $wpdb->get_var($wpdb->prepare("SELECT ID FROM {$wpdb->posts} WHERE post_name = %s AND post_type = 'page' LIMIT 1", 'dosen'));
    if ($post_id) {
        $wpdb->update(
            $wpdb->posts,
            array('post_content' => $full_html),
            array('ID' => $post_id)
        );
        clean_post_cache($post_id);
    }

    return true;
}

function bk_fip_render_sync_dosen_page() {
    if (!current_user_can('manage_options')) return;

    // Enqueue WordPress Media Uploader for transparent photo upload
    wp_enqueue_media();

    $notice = '';

    // Handle Edit Single Lecturer Save
    if (isset($_POST['bk_save_single_dosen']) && check_admin_referer('bk_edit_dosen_action', 'bk_edit_dosen_nonce')) {
        $edit_slug = sanitize_title($_POST['dosen_slug'] ?? '');
        $json_file = get_template_directory() . '/dosen-data.json';
        $current_dosen = bk_fip_load_dosen_data();

        $found = false;
        foreach ($current_dosen as &$cd) {
            if (($cd['slug'] ?? '') === $edit_slug) {
                $cd['nama_lengkap']       = sanitize_text_field($_POST['nama_lengkap'] ?? $cd['nama_lengkap']);
                $cd['nama_panggilan']     = sanitize_text_field($_POST['nama_panggilan'] ?? '');
                $cd['nip']                = sanitize_text_field($_POST['nip'] ?? '');
                $cd['nidn']               = sanitize_text_field($_POST['nidn'] ?? '');
                $cd['jabatan_fungsional'] = sanitize_text_field($_POST['jabatan_fungsional'] ?? $cd['jabatan_fungsional']);
                $cd['jabatan_struktural'] = sanitize_text_field($_POST['jabatan_struktural'] ?? '');
                $cd['unit']               = sanitize_text_field($_POST['unit'] ?? ($cd['unit'] ?? 'S1 Bimbingan dan Konseling'));
                $cd['foto_link']          = function_exists('bk_sanitize_asset_or_url') ? bk_sanitize_asset_or_url(wp_unslash($_POST['foto_link'] ?? '')) : esc_url_raw($_POST['foto_link'] ?? '');
                $cd['sinta_id']           = sanitize_text_field($_POST['sinta_id'] ?? '');
                $cd['sinta_url']          = esc_url_raw($_POST['sinta_url'] ?? '');
                $cd['scholar_url']        = esc_url_raw($_POST['scholar_url'] ?? '');
                $cd['scopus_url']         = esc_url_raw($_POST['scopus_url'] ?? '');
                $cd['email']              = sanitize_email($_POST['email'] ?? '');
                $cd['no_hp']              = sanitize_text_field($_POST['no_hp'] ?? '');
                $cd['ruang_kerja']        = sanitize_text_field($_POST['ruang_kerja'] ?? '');
                $cd['bio']                = sanitize_textarea_field($_POST['bio'] ?? '');

                // Parse Kepakaran Tags
                $raw_k = sanitize_text_field($_POST['kepakaran_input'] ?? '');
                $k_list = array();
                if (!empty($raw_k)) {
                    foreach (explode(',', $raw_k) as $item) {
                        $item = trim($item);
                        if ($item) $k_list[] = $item;
                    }
                }
                $cd['kepakaran'] = $k_list;
                $cd['bidang_keahlian'] = implode(', ', $k_list);

                // Parse Riwayat Pendidikan
                $raw_pend = stripslashes($_POST['pendidikan_json'] ?? '[]');
                $decoded_pend = json_decode($raw_pend, true);
                if (is_array($decoded_pend)) {
                    $clean_pend = array();
                    foreach ($decoded_pend as $item) {
                        $j = sanitize_text_field($item['jenjang'] ?? '');
                        $b = sanitize_text_field($item['bidang'] ?? '');
                        $ins = sanitize_text_field($item['institusi'] ?? '');
                        $th = sanitize_text_field($item['tahun'] ?? '');
                        if ($j || $b || $ins) {
                            $clean_pend[] = array('jenjang' => $j, 'bidang' => $b, 'institusi' => $ins, 'tahun' => $th);
                        }
                    }
                    $cd['pendidikan'] = $clean_pend;
                }

                // Parse Pengalaman Kerja & Organisasi Profesi
                $raw_peng = stripslashes($_POST['pengalaman_json'] ?? '[]');
                $decoded_peng = json_decode($raw_peng, true);
                if (is_array($decoded_peng)) {
                    $clean_peng = array();
                    foreach ($decoded_peng as $item) {
                        $pos = sanitize_text_field($item['posisi'] ?? '');
                        $ins = sanitize_text_field($item['instansi'] ?? '');
                        $per = sanitize_text_field($item['periode'] ?? '');
                        if ($pos || $ins) {
                            $clean_peng[] = array('posisi' => $pos, 'instansi' => $ins, 'periode' => $per);
                        }
                    }
                    $cd['pengalaman'] = $clean_peng;
                }

                $found = true;
                break;
            }
        }

        if ($found) {
            // Save to JSON and options cache
            @file_put_contents($json_file, json_encode($current_dosen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            update_option('bk_dosen_data_cache', $current_dosen, false);

            // Sync photo URL to CPT 'dosen' / 'ppb_dosen' postmeta so single-dosen.php always stays in sync
            $saved_foto = '';
            foreach ($current_dosen as $cd_item) {
                if (($cd_item['slug'] ?? '') === $edit_slug) {
                    $saved_foto = trim((string)($cd_item['foto_link'] ?? ''));
                    break;
                }
            }
            foreach (array('dosen', 'ppb_dosen') as $cpt_type) {
                $cpt_post = get_page_by_path($edit_slug, OBJECT, $cpt_type);
                if ($cpt_post) {
                    if ($saved_foto !== '') {
                        update_post_meta($cpt_post->ID, '_dosen_foto_link', $saved_foto);
                        update_post_meta($cpt_post->ID, '_ppb_dosen_foto_link', $saved_foto);
                    } else {
                        delete_post_meta($cpt_post->ID, '_dosen_foto_link');
                        delete_post_meta($cpt_post->ID, '_ppb_dosen_foto_link');
                    }
                    clean_post_cache($cpt_post->ID);
                }
            }

            // Rebuild public roster page
            bk_fip_rebuild_dosen_roster_html($current_dosen);

            // Auto-sync course assignments bi-directionally with Manajemen Mata Kuliah
            if (isset($_POST['mk_ids_present']) && function_exists('ppb_update_lecturer_courses')) {
                $posted_mk_ids = isset($_POST['mk_ids']) && is_array($_POST['mk_ids'])
                    ? array_map('sanitize_key', wp_unslash($_POST['mk_ids']))
                    : array();
                ppb_update_lecturer_courses($edit_slug, $posted_mk_ids);
            }

            $notice = '<div class="notice notice-success is-dismissible" style="padding:16px;border-left:4px solid #0D3B22;background:#f0fff4;border-radius:8px;margin-bottom:20px;">
                <h3 style="margin:0 0 6px 0;color:#0D3B22;">&#10003; Data Profil Dosen Berhasil Diperbarui!</h3>
                <p style="margin:0;font-size:13.5px;color:#2d3748;">
                    Perubahan data dan foto profil untuk <strong>' . esc_html($_POST['nama_lengkap'] ?? $edit_slug) . '</strong> telah disimpan ke sistem dan langsung aktif di halaman publik <a href="' . esc_url(home_url('/profil/dosen/' . $edit_slug . '/')) . '" target="_blank" style="color:#0D3B22;font-weight:700;text-decoration:underline;">/profil/dosen/' . esc_html($edit_slug) . '/ ↗</a>.
                </p>
            </div>';
        }
    }

    // SINTA sync is handled via AJAX (per-dosen / batch) and GitHub Actions REST API

    // Handle Save Custom Order Action
    if (isset($_POST['bk_save_dosen_order']) && check_admin_referer('bk_dosen_order_action', 'bk_dosen_order_nonce')) {
        $raw_orders = isset($_POST['dosen_order']) && is_array($_POST['dosen_order']) ? $_POST['dosen_order'] : array();

        $json_file = get_template_directory() . '/dosen-data.json';
        $current_dosen = bk_fip_load_dosen_data();

        if (!empty($current_dosen) && !empty($raw_orders)) {
            $order_map = array();
            foreach ($raw_orders as $slug => $val) {
                $order_map[sanitize_title($slug)] = (int)$val;
            }

            usort($current_dosen, function($a, $b) use ($order_map) {
                $slug_a = $a['slug'] ?? '';
                $slug_b = $b['slug'] ?? '';
                $order_a = isset($order_map[$slug_a]) ? $order_map[$slug_a] : 999;
                $order_b = isset($order_map[$slug_b]) ? $order_map[$slug_b] : 999;
                if ($order_a === $order_b) {
                    return strcmp($a['nama_lengkap'] ?? '', $b['nama_lengkap'] ?? '');
                }
                return ($order_a < $order_b) ? -1 : 1;
            });

            // Save to dosen-data.json
            file_put_contents($json_file, json_encode($current_dosen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            // Save persistent cache & custom order map
            update_option('bk_dosen_data_cache', $current_dosen, false);
            update_option('bk_dosen_custom_order', $order_map, false);

            // Rebuild public page and pages-data.json
            bk_fip_rebuild_dosen_roster_html($current_dosen);

            $notice = '<div class="notice notice-success is-dismissible" style="padding:16px;border-left:4px solid #0D3B22;background:#f0fff4;border-radius:8px;margin-bottom:20px;">
                <h3 style="margin:0 0 6px 0;color:#0D3B22;">&#10003; Urutan Tampilan Dosen Berhasil Disimpan &amp; Diterapkan!</h3>
                <p style="margin:0;font-size:13.5px;color:#2d3748;">
                    Urutan kustom untuk <strong>' . count($current_dosen) . ' Dosen</strong> telah disimpan ke database dan langsung aktif di halaman publik <a href="' . esc_url(home_url('/profil/dosen/')) . '" target="_blank" style="color:#0D3B22;font-weight:700;text-decoration:underline;">/profil/dosen/ ↗</a>.
                </p>
            </div>';
        }
    }

    $all_dosen = bk_fip_load_dosen_data();
    $last_synced = get_option('bk_dosen_last_synced', 'Belum pernah disinkronkan');

    $tot_pub = 0;
    $tot_pen = 0;
    $tot_peng = 0;
    $tot_buku = 0;
    if (is_array($all_dosen)) {
        foreach ($all_dosen as $d) {
            $tot_pub += (int)($d['stat']['publikasi'] ?? count($d['publikasi'] ?? array()));
            $tot_pen += (int)($d['stat']['penelitian'] ?? count($d['penelitian'] ?? array()));
            $tot_peng += (int)($d['stat']['pengabdian'] ?? count($d['pengabdian'] ?? array()));
            $tot_buku += (int)($d['stat']['buku'] ?? count($d['buku'] ?? array()));
        }
    }
    ?>
    <div class="wrap" style="max-width:1240px;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:15px;margin-top:16px;margin-bottom:8px;">
            <div>
                <h1 style="font-size:25px;font-weight:800;color:#0D3B22;margin:0 0 6px 0;letter-spacing:-0.02em;">
                    Direktori Dosen &amp; Pengaturan Profil Akademik
                </h1>
                <p style="font-size:13.5px;color:#4B5563;margin:0;">
                    Edit data profil dosen (NIP, Nama Panggilan, Kepakaran, Foto Transparan), atur urutan tampilan publik, dan sinkronkan rekam jejak langsung dari SINTA Kemdiktisaintek.
                </p>
            </div>
            <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                <?php if (function_exists('ppb_render_excel_import_admin_page')) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-excel-import&dataset=dosen')); ?>" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:800;padding:8px 16px;border-radius:8px;font-size:13px;display:inline-flex;align-items:center;gap:6px;">
                        <span>📥 Import / Export Excel &amp; JSON</span>
                    </a>
                <?php endif; ?>
                <a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>" target="_blank" class="button" style="background:#0D3B22;color:#ffffff;border:none;font-weight:700;padding:8px 18px;border-radius:8px;font-size:13px;display:inline-flex;align-items:center;gap:6px;box-shadow:0 3px 10px rgba(13,59,34,0.15);">
                    <span>Buka Halaman Publik ↗</span>
                </a>
            </div>
        </div>

        <div style="margin-top:18px;">
            <?php echo $notice; ?>
        </div>

        <!-- Stat Counter Cards -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(210px, 1fr));gap:16px;margin:20px 0 24px 0;">
            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em;">Total Dosen</div>
                <div style="font-size:32px;font-weight:800;color:#0D3B22;line-height:1.2;margin:4px 0;"><?php echo count($all_dosen); ?></div>
                <div style="font-size:12px;color:#185333;">Dosen &amp; Tenaga Pengajar Aktif</div>
            </div>

            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em;">Publikasi Ilmiah</div>
                <div style="font-size:32px;font-weight:800;color:#F5B82E;line-height:1.2;margin:4px 0;"><?php echo $tot_pub; ?></div>
                <div style="font-size:12px;color:#6B7280;">Scopus, SINTA, Garuda, Scholar</div>
            </div>

            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em;">Penelitian &amp; Hibah</div>
                <div style="font-size:32px;font-weight:800;color:#0D3B22;line-height:1.2;margin:4px 0;"><?php echo $tot_pen; ?></div>
                <div style="font-size:12px;color:#6B7280;">Hibah Dikti &amp; Institusi</div>
            </div>

            <div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:14px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
                <div style="font-size:11px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.05em;">Buku &amp; PkM</div>
                <div style="font-size:32px;font-weight:800;color:#0D3B22;line-height:1.2;margin:4px 0;"><?php echo $tot_buku + $tot_peng; ?></div>
                <div style="font-size:12px;color:#6B7280;"><?php echo $tot_buku; ?> Buku &middot; <?php echo $tot_peng; ?> PkM</div>
            </div>
        </div>

        <!-- SINTA Sync Banner Card -->
        <?php $sinta_token = bk_fip_get_sinta_sync_token(); ?>
        <div style="background:linear-gradient(135deg, #0D3B22 0%, #155532 100%);border-radius:16px;padding:22px 28px;color:#ffffff;margin-bottom:26px;box-shadow:0 4px 16px rgba(13,59,34,0.12);">
            <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;">
                <div style="max-width:650px;">
                    <h2 style="font-size:17px;font-weight:700;color:#ffffff;margin:0 0 6px 0;">
                        Sinkronisasi Langsung SINTA Kemdiktisaintek
                    </h2>
                    <p style="font-size:12.5px;line-height:1.55;color:rgba(255,255,255,0.85);margin:0 0 8px 0;">
                        Tarik skor SINTA, publikasi (Scopus, Garuda, Google Scholar), penelitian, pengabdian masyarakat, dan buku langsung dari SINTA berdasarkan SINTA ID masing-masing dosen tanpa menimpa editan manual admin.
                    </p>
                    <div style="font-size:11.5px;color:#F5B82E;">
                        &bull; Terakhir disinkronkan: <strong id="bk-last-synced-label"><?php echo esc_html($last_synced); ?></strong>
                    </div>
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;">
                    <button type="button" id="bk-batch-sinta-btn" onclick="bkStartBatchSintaSync()" class="button button-primary" style="background:#F5B82E;color:#0D3B22;border:none;font-size:13.5px;font-weight:700;padding:10px 22px;border-radius:10px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,0.2);transition:all 0.2s;">
                        ⚡ Sync Semua Dosen Sekarang
                    </button>
                    <button type="button" onclick="var el=document.getElementById('bk-gha-token-box');el.style.display=(el.style.display==='none'?'block':'none');" class="button" style="background:rgba(255,255,255,0.14);color:#ffffff;border:1px solid rgba(255,255,255,0.3);font-size:12.5px;font-weight:600;padding:9px 16px;border-radius:10px;cursor:pointer;">
                        🔑 Token GitHub Actions
                    </button>
                </div>
            </div>

            <!-- Batch Progress Bar -->
            <div id="bk-sinta-progress-wrap" style="display:none;margin-top:16px;background:rgba(0,0,0,0.22);border-radius:10px;padding:12px 14px;">
                <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px;">
                    <span id="bk-sinta-progress-text">Mempersiapkan sinkronisasi SINTA...</span>
                    <strong id="bk-sinta-progress-pct" style="color:#F5B82E;">0%</strong>
                </div>
                <div style="height:8px;background:rgba(255,255,255,0.15);border-radius:999px;overflow:hidden;">
                    <div id="bk-sinta-progress-bar" style="height:100%;width:0%;background:#F5B82E;transition:width 0.25s ease;"></div>
                </div>
            </div>

            <!-- GitHub Actions Token Box -->
            <div id="bk-gha-token-box" style="display:none;margin-top:16px;background:rgba(0,0,0,0.25);border:1px solid rgba(255,255,255,0.18);border-radius:10px;padding:14px;">
                <div style="font-size:12px;color:rgba(255,255,255,0.9);margin-bottom:8px;">
                    Simpan token berikut ke GitHub Repository Secrets (<code>BK_SINTA_SYNC_TOKEN</code>) bersama <code>WP_SITE_URL</code> (<code><?php echo esc_html(home_url()); ?></code>) untuk jadwal sync otomatis mingguan:
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:8px;align-items:center;">
                    <input type="text" id="bk-sinta-token-input" readonly value="<?php echo esc_attr($sinta_token); ?>" style="flex:1;min-width:260px;font-family:monospace;font-size:12px;background:#ffffff;color:#0D3B22;border:none;border-radius:6px;padding:6px 10px;">
                    <button type="button" onclick="navigator.clipboard.writeText(document.getElementById('bk-sinta-token-input').value);this.textContent='✓ Tersalin';setTimeout(()=>this.textContent='Salin Token',1500);" class="button" style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;border-radius:6px;">Salin Token</button>
                    <button type="button" onclick="bkRegenerateSintaToken(this)" class="button" style="background:rgba(255,255,255,0.16);color:#ffffff;border:1px solid rgba(255,255,255,0.3);border-radius:6px;">Regenerate</button>
                </div>
            </div>
        </div>

        <!-- Custom Sorting & Lecturer Management Card -->
        <form method="post" action="" id="bk-dosen-order-form">
            <?php wp_nonce_field('bk_dosen_order_action', 'bk_dosen_order_nonce'); ?>

            <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;overflow:hidden;box-shadow:0 3px 12px rgba(0,0,0,0.05);margin-bottom:30px;">
                <!-- Header Toolbar -->
                <div style="padding:20px 24px;border-bottom:1.5px solid #E5E7EB;background:#FAF8F5;">
                    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:14px;margin-bottom:14px;">
                        <div>
                            <h3 style="margin:0 0 4px 0;font-size:17px;font-weight:800;color:#0D3B22;display:flex;align-items:center;gap:8px;">
                                <span>📋 Pengaturan Urutan Tampilan &amp; Edit Profil Dosen</span>
                            </h3>
                            <p style="margin:0;font-size:12.5px;color:#4B5563;">
                                Klik tombol <strong>✏️ Edit Data</strong> pada dosen mana pun untuk mengedit NIP, nama panggilan, kepakaran, foto transparan, atau kontak. Atur urutan dengan input nomor atau panah <strong>▲/▼</strong>.
                            </p>
                        </div>
                        <div>
                            <button type="submit" name="bk_save_dosen_order" class="button button-primary" style="background:#0D3B22;color:#F5B82E;border:1.5px solid #0D3B22;font-size:13.5px;font-weight:700;padding:8px 22px;border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 3px 10px rgba(13,59,34,0.18);transition:all 0.2s;" onmouseover="this.style.background='#185333';this.style.borderColor='#185333';" onmouseout="this.style.background='#0D3B22';this.style.borderColor='#0D3B22';">
                                <span>💾 Simpan Urutan Tampilan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Preset Quick Action Buttons -->
                    <div style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;padding-top:12px;border-top:1px dashed #D4E3D8;">
                        <span style="font-size:11.5px;font-weight:700;color:#6B7280;text-transform:uppercase;letter-spacing:0.04em;">Preset Cepat 1-Klik:</span>
                        
                        <button type="button" class="button" onclick="bkApplyPresetHierarchy()" style="background:#ffffff;border:1.5px solid #0D3B22;color:#0D3B22;font-weight:700;font-size:12px;padding:4px 14px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;" onmouseover="this.style.background='#E8F0EA'" onmouseout="this.style.background='#ffffff'">
                            <span>🏛</span> Preset Hierarki Akademik (Dekan &rarr; Pimpinan &rarr; Guru Besar)
                        </button>

                        <button type="button" class="button" onclick="bkApplyPresetAlphabetical()" style="background:#ffffff;border:1.5px solid #4B5563;color:#374151;font-weight:600;font-size:12px;padding:4px 14px;border-radius:8px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.15s;" onmouseover="this.style.background='#F3F4F6'" onmouseout="this.style.background='#ffffff'">
                            <span>🔤</span> Preset Abjad Nama (A &rarr; Z)
                        </button>

                        <span id="bk-sort-feedback" style="margin-left:auto;font-size:12px;font-weight:600;color:#0D3B22;display:none;padding:4px 10px;background:#E8F0EA;border-radius:6px;"></span>
                    </div>
                </div>

                <!-- Table -->
                <div style="overflow-x:auto;">
                    <table class="wp-list-table widefat striped" id="bk-dosen-sort-table" style="border:none;">
                        <thead>
                            <tr style="background:#ffffff;">
                                <th style="width:100px;text-align:center;padding:12px 8px;font-weight:700;color:#0D3B22;">Urutan</th>
                                <th style="width:60px;text-align:center;padding:12px 6px;font-weight:700;color:#0D3B22;">Foto</th>
                                <th style="padding:12px 12px;font-weight:700;color:#0D3B22;">Nama Dosen &amp; NIP</th>
                                <th style="padding:12px 10px;font-weight:700;color:#0D3B22;">Jabatan / Status</th>
                                <th style="width:75px;text-align:center;padding:12px 8px;font-weight:700;color:#0D3B22;">Publikasi</th>
                                <th style="width:75px;text-align:center;padding:12px 8px;font-weight:700;color:#0D3B22;">Penelitian</th>
                                <th style="width:190px;text-align:center;padding:12px 10px;font-weight:700;color:#0D3B22;">Aksi Kelola</th>
                            </tr>
                        </thead>
                        <tbody id="bk-dosen-tbody">
                            <?php if (empty($all_dosen)) : ?>
                                <tr><td colspan="7" style="text-align:center;padding:40px;color:#6B7280;">Belum ada data dosen. Silakan klik tombol sinkronisasi di atas.</td></tr>
                            <?php else : 
                                $all_matkul = function_exists('ppb_get_mata_kuliah_list') ? ppb_get_mata_kuliah_list(true) : array();
                                $mk_ids_by_dosen = array();
                                if (is_array($all_matkul)) {
                                    foreach ($all_matkul as $mk_row) {
                                        $mid = sanitize_key($mk_row['id'] ?? '');
                                        if ($mid === '' || empty($mk_row['dosen_slugs']) || !is_array($mk_row['dosen_slugs'])) continue;
                                        foreach ($mk_row['dosen_slugs'] as $ds_slug) {
                                            $ds_slug = sanitize_title($ds_slug);
                                            if ($ds_slug !== '') {
                                                $mk_ids_by_dosen[$ds_slug][] = $mid;
                                            }
                                        }
                                    }
                                }
                                $no = 1;
                                $total_count = count($all_dosen);
                                foreach ($all_dosen as $d) : 
                                    $slug = $d['slug'] ?? '';
                                    $mk_ids_for_d = array_values(array_unique($mk_ids_by_dosen[sanitize_title($slug)] ?? array()));
                                    $mk_cnt_for_d = !empty($mk_ids_for_d) ? count($mk_ids_for_d) : count($d['mata_kuliah'] ?? array());
                                    $nama_lengkap = $d['nama_lengkap'] ?? ($d['nama'] ?? '');
                                    $nama_panggilan = $d['nama_panggilan'] ?? '';
                                    $jabatan = $d['jabatan_fungsional'] ?? 'Dosen BK';
                                    $jab_struktural = $d['jabatan_struktural'] ?? '';
                                    $profile_url = '/profil/dosen/' . $slug . '/';
                                    $pub_cnt = (int)($d['stat']['publikasi'] ?? count($d['publikasi'] ?? array()));
                                    $pen_cnt = (int)($d['stat']['penelitian'] ?? count($d['penelitian'] ?? array()));
                                    $buku_cnt = (int)($d['stat']['buku'] ?? count($d['buku'] ?? array()));
                                    $h_rank = bk_fip_get_dosen_hierarchy_rank($slug, $nama_lengkap, $jabatan);
                                    $foto_link = $d['foto_link'] ?? '';
                                    $kepakaran_str = implode(', ', $d['kepakaran'] ?? array());

                                    // Badge styling
                                    $badge_label = 'DOSEN PPB';
                                    $badge_bg = '#F3F4F6';
                                    $badge_col = '#374151';
                                    $badge_border = '#E5E7EB';
                                    if (strpos(strtolower($nama_lengkap), 'prof.') !== false || strpos(strtolower($jabatan), 'guru besar') !== false) {
                                        $badge_label = 'GURU BESAR';
                                        $badge_bg = '#FEF3C7';
                                        $badge_col = '#92400E';
                                        $badge_border = '#FDE68A';
                                    } elseif (strpos(strtolower($nama_lengkap), 'dr.') !== false || strpos(strtolower($jabatan), 'doktor') !== false) {
                                        $badge_label = 'DOKTOR';
                                        $badge_bg = '#E0E7FF';
                                        $badge_col = '#3730A3';
                                        $badge_border = '#C7D2FE';
                                    } elseif (strpos(strtolower($jabatan), 'lektor kepala') !== false) {
                                        $badge_label = 'LEKTOR KEPALA';
                                        $badge_bg = '#E8F0EA';
                                        $badge_col = '#0D3B22';
                                        $badge_border = '#D4E3D8';
                                    }

                                    // JSON data attribute for Edit Modal
                                    $edit_json = htmlspecialchars(json_encode(array(
                                        'slug'               => $slug,
                                        'nama_lengkap'       => $nama_lengkap,
                                        'nama_panggilan'     => $nama_panggilan,
                                        'nip'                => $d['nip'] ?? '',
                                        'nidn'               => $d['nidn'] ?? '',
                                        'jabatan_fungsional' => $jabatan,
                                        'jabatan_struktural' => $jab_struktural,
                                        'unit'               => $d['unit'] ?? 'S1 Bimbingan dan Konseling',
                                        'foto_link'          => $foto_link,
                                        'kepakaran'          => $kepakaran_str,
                                        'sinta_id'           => $d['sinta_id'] ?? '',
                                        'sinta_url'          => $d['sinta_url'] ?? '',
                                        'scholar_url'        => $d['scholar_url'] ?? '',
                                        'scopus_url'         => $d['scopus_url'] ?? '',
                                        'email'              => $d['email'] ?? '',
                                        'no_hp'              => $d['no_hp'] ?? '',
                                        'ruang_kerja'        => $d['ruang_kerja'] ?? '',
                                        'bio'                => $d['bio'] ?? '',
                                        'pendidikan'         => $d['pendidikan'] ?? array(),
                                        'pengalaman'         => $d['pengalaman'] ?? array(),
                                        'mk_ids'             => $mk_ids_for_d,
                                    )), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr id="dosen-row-<?php echo esc_attr($slug); ?>" data-slug="<?php echo esc_attr($slug); ?>" data-nama="<?php echo esc_attr($nama_lengkap); ?>" data-rank="<?php echo (int)$h_rank; ?>" style="transition:background 0.3s ease;">
                                    <td style="text-align:center;vertical-align:middle;padding:10px 6px;">
                                        <div style="display:inline-flex;align-items:center;justify-content:center;gap:3px;">
                                            <input type="number" class="bk-order-input" name="dosen_order[<?php echo esc_attr($slug); ?>]" value="<?php echo $no; ?>" min="1" max="<?php echo $total_count; ?>" style="width:48px;height:32px;text-align:center;font-weight:700;font-size:13px;border:1.5px solid #D4E3D8;border-radius:8px;color:#0D3B22;background:#FAF8F5;" onchange="bkOnNumberChange(this)">
                                            <div style="display:inline-flex;flex-direction:column;gap:2px;">
                                                <button type="button" class="button button-small" onclick="bkMoveRow(this, -1)" title="Geser ke Atas" style="padding:0 4px;height:15px;line-height:13px;font-size:8px;min-height:15px;font-weight:bold;color:#0D3B22;cursor:pointer;">▲</button>
                                                <button type="button" class="button button-small" onclick="bkMoveRow(this, 1)" title="Geser ke Bawah" style="padding:0 4px;height:15px;line-height:13px;font-size:8px;min-height:15px;font-weight:bold;color:#0D3B22;cursor:pointer;">▼</button>
                                            </div>
                                        </div>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;padding:8px 6px;">
                                        <?php if ($foto_link) : ?>
                                            <img src="<?php echo esc_url(function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($foto_link) : $foto_link); ?>" alt="<?php echo esc_attr($nama_lengkap); ?>" style="width:40px;height:40px;border-radius:10px;object-fit:cover;object-position:50% 18%;border:1.5px solid #0D3B22;display:block;margin:0 auto;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                                        <?php else : ?>
                                            <div style="width:40px;height:40px;border-radius:10px;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;margin:0 auto;">
                                                <?php echo esc_html(strtoupper(substr($slug, 0, 2))); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="vertical-align:middle;padding:10px 12px;">
                                        <div style="display:flex;align-items:center;gap:6px;">
                                            <strong style="font-size:13.5px;color:#111827;"><?php echo esc_html($nama_lengkap); ?></strong>
                                            <?php if ($nama_panggilan) : ?>
                                                <span style="font-size:11px;background:#FEF3C7;color:#92400E;padding:1px 6px;border-radius:4px;font-weight:600;">(<?php echo esc_html($nama_panggilan); ?>)</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-family:monospace;font-size:11px;color:#6B7280;margin-top:2px;">
                                            NIP. <?php echo esc_html($d['nip'] ?: '-'); ?>
                                            <?php if (!empty($d['nidn'])) : ?> &middot; NIDN. <?php echo esc_html($d['nidn']); ?><?php endif; ?>
                                        </div>
                                        <?php if (!empty($kepakaran_str)) : ?>
                                            <div style="font-size:10.5px;color:#185333;margin-top:2px;max-width:320px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                                🎯 <?php echo esc_html($kepakaran_str); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div style="margin-top:4px;">
                                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:10.5px;font-weight:700;background:#E8F0EA;color:#0D3B22;border:1px solid #C5DACB;padding:1px 7px;border-radius:999px;">📖 <?php echo (int) $mk_cnt_for_d; ?> MK Diampu</span>
                                        </div>
                                    </td>
                                    <td style="vertical-align:middle;padding:10px;">
                                        <span style="background:<?php echo $badge_bg; ?>;color:<?php echo $badge_col; ?>;border:1px solid <?php echo $badge_border; ?>;padding:3px 8px;border-radius:9999px;font-size:10.5px;font-weight:700;letter-spacing:0.03em;display:inline-block;white-space:nowrap;">
                                            <?php echo $badge_label; ?>
                                        </span>
                                        <?php if ($jab_struktural) : ?>
                                            <div style="font-size:10.5px;font-weight:600;color:#0D3B22;margin-top:3px;">
                                                🏛 <?php echo esc_html($jab_struktural); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align:center;vertical-align:middle;padding:10px;"><strong style="color:#0D3B22;font-size:13px;"><?php echo $pub_cnt; ?></strong></td>
                                    <td style="text-align:center;vertical-align:middle;padding:10px;"><strong style="color:#0D3B22;font-size:13px;"><?php echo $pen_cnt; ?></strong></td>
                                    <td style="text-align:center;vertical-align:middle;padding:10px;">
                                        <div style="display:inline-flex;gap:6px;align-items:center;">
                                            <button type="button" class="button button-small bk-sync-sinta-btn" data-slug="<?php echo esc_attr($slug); ?>" data-nama="<?php echo esc_attr($nama_lengkap); ?>" onclick="bkSyncSingleSinta('<?php echo esc_js($slug); ?>', this)" title="Tarik data terbaru dari SINTA" style="background:#E8F0EA;color:#0D3B22;border:1px solid #C5DACB;font-weight:700;border-radius:6px;padding:4px 9px;font-size:11.5px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;">
                                                <span>🔄 SINTA</span>
                                            </button>
                                            <button type="button" class="button button-small" onclick='bkOpenEditModal(<?php echo $edit_json; ?>)' style="background:#F5B82E;color:#0D3B22;border:none;font-weight:700;border-radius:6px;padding:4px 10px;font-size:11.5px;cursor:pointer;display:inline-flex;align-items:center;gap:3px;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
                                                <span>✏️ Edit</span>
                                            </button>
                                            <a href="<?php echo esc_url($profile_url); ?>" target="_blank" class="button button-small" style="background:#0D3B22;color:#ffffff;border:none;font-weight:600;border-radius:6px;padding:4px 10px;font-size:11.5px;display:inline-flex;align-items:center;gap:3px;">
                                                <span>Buka ↗</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php 
                                $no++;
                                endforeach; 
                            endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer Toolbar -->
                <div style="padding:16px 24px;border-top:1.5px solid #E5E7EB;background:#FAF8F5;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div style="font-size:12.5px;color:#4B5563;">
                        Menampilkan <strong style="color:#0D3B22;"><?php echo count($all_dosen); ?> Dosen</strong>. Klik tombol simpan untuk menerapkan urutan ke website publik.
                    </div>
                    <div>
                        <button type="submit" name="bk_save_dosen_order" class="button button-primary" style="background:#0D3B22;color:#F5B82E;border:1.5px solid #0D3B22;font-size:13.5px;font-weight:700;padding:8px 24px;border-radius:10px;cursor:pointer;display:inline-flex;align-items:center;gap:7px;box-shadow:0 3px 10px rgba(13,59,34,0.18);">
                            <span>💾 Simpan Urutan Tampilan</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- EDIT DOSEN MODAL -->
        <div id="bk-edit-modal-backdrop" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.65);z-index:99999;backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;">
            <div style="background:#ffffff;border-radius:20px;max-width:850px;width:100%;max-height:92vh;overflow-y:auto;box-shadow:0 25px 50px -12px rgba(0,0,0,0.35);font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;position:relative;">
                
                <!-- Modal Header -->
                <div style="padding:20px 26px;border-bottom:1.5px solid #E5E7EB;display:flex;align-items:center;justify-content:space-between;background:#FAF8F5;border-radius:20px 20px 0 0;position:sticky;top:0;z-index:10;">
                    <div>
                        <h2 id="modal-title" style="margin:0 0 4px 0;font-size:18px;font-weight:800;color:#0D3B22;">Edit Data Profil Dosen</h2>
                        <p style="margin:0;font-size:12.5px;color:#6B7280;">Perbarui data identitas, fokus kepakaran, kontak, dan foto profil cutout transparan.</p>
                    </div>
                    <button type="button" onclick="bkCloseEditModal()" style="background:#F3F4F6;border:none;font-size:18px;font-weight:bold;color:#4B5563;cursor:pointer;width:34px;height:34px;border-radius:50%;display:flex;align-items:center;justify-content:center;line-height:1;" title="Tutup">&times;</button>
                </div>

                <!-- Modal Body Form -->
                <form method="post" action="" id="bk-modal-form" onsubmit="return bkPrepareSubmit()" style="padding:24px 26px;">
                    <input type="hidden" name="pendidikan_json" id="edit-pendidikan-json" value="">
                    <input type="hidden" name="pengalaman_json" id="edit-pengalaman-json" value="">
                    <?php wp_nonce_field('bk_edit_dosen_action', 'bk_edit_dosen_nonce'); ?>
                    <input type="hidden" name="dosen_slug" id="edit-slug" value="">

                    <!-- Section 1: Identitas Pokok & Panggilan -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:20px;">
                        <h4 style="margin:0 0 14px 0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                            <span>👤 Identitas Dosen &amp; Panggilan</span>
                        </h4>
                        
                        <div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Nama Lengkap &amp; Gelar Akademik:</label>
                                <input type="text" name="nama_lengkap" id="edit-nama-lengkap" required style="width:100%;height:38px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 12px;font-size:13.5px;font-weight:600;">
                                <span style="font-size:11px;color:#6B7280;">Contoh: Prof. Dr. Abdul Saman, M.Si., Kons.</span>
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Nama Panggilan / Singkat:</label>
                                <input type="text" name="nama_panggilan" id="edit-nama-panggilan" style="width:100%;height:38px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 12px;font-size:13.5px;">
                                <span style="font-size:11px;color:#6B7280;">Contoh: Prof. Saman</span>
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Nomor Induk Pegawai (NIP):</label>
                                <input type="text" name="nip" id="edit-nip" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:13px;font-family:monospace;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">NIDN (Kemenristekdikti):</label>
                                <input type="text" name="nidn" id="edit-nidn" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:13px;font-family:monospace;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Jabatan Fungsional:</label>
                                <input type="text" name="jabatan_fungsional" id="edit-jabatan-fungsional" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:13px;">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Jabatan Struktural / Peran Tambahan (Opsional):</label>
                                <input type="text" name="jabatan_struktural" id="edit-jabatan-struktural" placeholder="Contoh: Dekan FIP UNM / Ketua Jurusan BK" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:13px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Unit / Program Studi:</label>
                                <input type="text" name="unit" id="edit-unit" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:13px;">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Foto Profil Transparan -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:20px;">
                        <h4 style="margin:0 0 14px 0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                            <span>🖼️ Foto Profil Portrait Transparan</span>
                        </h4>

                        <div style="display:flex;align-items:flex-start;gap:18px;">
                            <div style="width:100px;height:120px;border-radius:12px;background:#0D3B22;border:2px dashed #F5B82E;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;" id="edit-photo-preview-box">
                                <img src="" id="edit-photo-preview" style="width:100%;height:100%;object-fit:cover;object-position:50% 18%;display:none;">
                                <span id="edit-photo-none" style="font-size:11px;color:#F5B82E;font-weight:600;text-align:center;padding:6px;">Tanpa Foto</span>
                            </div>

                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">URL Foto Profil:</label>
                                <div style="display:flex;gap:8px;margin-bottom:8px;">
                                    <input type="text" name="foto_link" id="edit-foto-link" placeholder="https://... atau assets/img/nama.jpg" style="flex:1;height:38px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 12px;font-size:13px;" oninput="bkUpdatePhotoPreview(this.value)">
                                    <button type="button" class="button" onclick="bkChooseMediaPhoto()" style="background:#0D3B22;color:#ffffff;border:none;font-weight:700;border-radius:8px;padding:0 16px;height:38px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;">
                                        <span>📁 Pilih / Upload Media</span>
                                    </button>
                                </div>
                                <p style="margin:0;font-size:11.5px;line-height:1.5;color:#4B5563;">
                                    <strong>Rekomendasi Format:</strong> Gunakan foto portrait dengan latar belakang transparan (format <code>.png</code> atau <code>.webp</code>). Foto akan ditampilkan tegak berdiri dari batas atas hingga batas bawah background hijau secara mulus dan elegan.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Fokus Kepakaran & Biografi -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:20px;">
                        <h4 style="margin:0 0 14px 0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                            <span>🎯 Fokus Kepakaran &amp; Sambutan Profil</span>
                        </h4>

                        <div style="margin-bottom:14px;">
                            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Bidang Kepakaran (Pisahkan dengan koma):</label>
                            <input type="text" name="kepakaran_input" id="edit-kepakaran" placeholder="Contoh: Konseling Karir, Psikologi Kepribadian, Terapi CBT, Evaluasi Pendidikan" style="width:100%;height:38px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 12px;font-size:13px;">
                            <span style="font-size:11px;color:#6B7280;">Setiap tag dipisahkan tanda koma <code>,</code> dan akan tampil sebagai badge kepakaran pada kartu &amp; profil.</span>
                        </div>

                        <div>
                            <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Biografi Singkat / Sambutan Profil:</label>
                            <textarea name="bio" id="edit-bio" rows="4" placeholder="Tuliskan 1-2 paragraf sambutan atau deskripsi rekam jejak kepakaran dosen..." style="width:100%;border-radius:8px;border:1.5px solid #D1D5DB;padding:10px 12px;font-size:13px;line-height:1.6;font-family:inherit;"></textarea>
                        </div>
                    </div>

                    <!-- Section: Riwayat Pendidikan Tinggi -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <h4 style="margin:0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                                <span>🎓 Riwayat Pendidikan Tinggi</span>
                            </h4>
                            <button type="button" class="button" onclick="bkAddPendidikanItem('S1','','','')" style="background:#0D3B22;color:#F5B82E;border:none;font-weight:700;padding:5px 14px;border-radius:8px;font-size:12px;cursor:pointer;">
                                + Tambah Pendidikan
                            </button>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:11.5px;color:#6B7280;">Masukkan jenjang, jurusan/bidang, nama perguruan tinggi, dan tahun kelulusan.</p>
                        <div id="edit-pendidikan-list" style="display:grid;gap:8px;"></div>
                    </div>

                    <!-- Section: Pengalaman & Karir Akademik / Organisasi Profesi -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <h4 style="margin:0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                                <span>💼 Pengalaman Kerja &amp; Organisasi Profesi</span>
                            </h4>
                            <button type="button" class="button" onclick="bkAddPengalamanItem('','','')" style="background:#0D3B22;color:#F5B82E;border:none;font-weight:700;padding:5px 14px;border-radius:8px;font-size:12px;cursor:pointer;">
                                + Tambah Pengalaman
                            </button>
                        </div>
                        <p style="margin:0 0 10px 0;font-size:11.5px;color:#6B7280;">Contoh: Dekan FIP UNM, Pengurus ABKIN Sulsel, Asesor BAN-PT, Reviewer Jurnal Nasional.</p>
                        <div id="edit-pengalaman-list" style="display:grid;gap:8px;"></div>
                    </div>

                    <!-- Section 4: Repositori Akademik & Kontak -->
                    <div style="background:#F9FBFA;border:1px solid #D4E3D8;border-radius:14px;padding:18px;margin-bottom:24px;">
                        <h4 style="margin:0 0 14px 0;font-size:13px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;">
                            <span>🌐 Repositori Akademik &amp; Kontak Resmi</span>
                        </h4>

                        <div style="display:grid;grid-template-columns:160px 1fr 1fr;gap:14px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">SINTA ID (Angka):</label>
                                <input type="text" name="sinta_id" id="edit-sinta-id" placeholder="6656787" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;font-family:monospace;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">SINTA Dikti URL:</label>
                                <input type="url" name="sinta_url" id="edit-sinta-url" placeholder="https://sinta.kemdiktisaintek.go.id/authors/profile/..." style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Google Scholar URL:</label>
                                <input type="url" name="scholar_url" id="edit-scholar-url" placeholder="https://scholar.google.com/citations?user=..." style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
                            </div>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Scopus Profile URL:</label>
                                <input type="url" name="scopus_url" id="edit-scopus-url" placeholder="https://www.scopus.com/authid/..." style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">Email Institusi UNM:</label>
                                <input type="email" name="email" id="edit-email" placeholder="nama@unm.ac.id" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
                            </div>
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">WhatsApp / Ruang Kerja:</label>
                                <input type="text" name="no_hp" id="edit-nohp" placeholder="08... / Gd FIP Lt. 2" style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
                            </div>
                        </div>
                    </div>

                    <!-- Mata Kuliah Diampu (Auto-Sync dengan Manajemen Mata Kuliah) -->
                    <div style="background:#FAF8F5;border:1.5px solid #D4E3D8;border-radius:12px;padding:16px;margin-bottom:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:10px;">
                            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                <h4 style="margin:0;font-size:13px;font-weight:800;color:#0D3B22;text-transform:uppercase;letter-spacing:0.04em;">📖 Mata Kuliah yang Diampu</h4>
                                <span id="edit-mk-count" style="background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:700;padding:2px 9px;border-radius:999px;">0 dipilih</span>
                            </div>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=ppb-mata-kuliah-manager')); ?>" target="_blank" style="font-size:11.5px;font-weight:700;color:#0D3B22;text-decoration:underline;">Kelola RPS/SAP &amp; MK Baru ↗</a>
                        </div>
                        <?php if (!empty($all_matkul) && is_array($all_matkul)) : ?>
                            <input type="hidden" name="mk_ids_present" value="1">
                            <input type="text" id="edit-mk-search" oninput="bkFilterModalMk(this.value)" placeholder="Cari kode atau nama mata kuliah (mis. BK103, Konseling, S2)..." style="width:100%;height:34px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;margin-bottom:10px;background:#ffffff;">
                            <div id="edit-mk-checklist" style="max-height:210px;overflow-y:auto;background:#ffffff;border:1px solid #D1D5DB;border-radius:8px;padding:6px;display:grid;gap:4px;">
                                <?php foreach ($all_matkul as $mk_opt) :
                                    $opt_id   = sanitize_key($mk_opt['id'] ?? '');
                                    $opt_kode = (string) ($mk_opt['kode'] ?? '');
                                    $opt_nama = (string) ($mk_opt['nama'] ?? '');
                                    if ($opt_id === '' || $opt_nama === '') continue;
                                    $opt_jenj = (string) ($mk_opt['jenjang'] ?? 'S1');
                                    $opt_sks  = (int) ($mk_opt['sks'] ?? 3);
                                    $opt_sem  = (int) ($mk_opt['semester'] ?? 1);
                                    $opt_rps  = !empty($mk_opt['rps_url']);
                                    $opt_search = strtolower(trim($opt_kode . ' ' . $opt_nama . ' ' . $opt_jenj . ' sem ' . $opt_sem));
                                ?>
                                    <label class="bk-modal-mk-row" data-search="<?php echo esc_attr($opt_search); ?>" style="display:flex;align-items:center;justify-content:space-between;gap:8px;padding:6px 9px;border-radius:6px;cursor:pointer;border:1px solid transparent;transition:background 0.15s;" onmouseover="this.style.background='#F2F6F3'" onmouseout="this.style.background='transparent'">
                                        <span style="display:flex;align-items:center;gap:8px;min-width:0;">
                                            <input type="checkbox" class="bk-modal-mk-cb" name="mk_ids[]" value="<?php echo esc_attr($opt_id); ?>" onchange="bkRefreshModalMkCount()" style="margin:0;flex-shrink:0;">
                                            <span style="font-family:monospace;font-size:11px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:2px 6px;border-radius:4px;flex-shrink:0;"><?php echo esc_html($opt_kode); ?></span>
                                            <span style="font-size:12.5px;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?php echo esc_html($opt_nama); ?></span>
                                        </span>
                                        <span style="display:inline-flex;align-items:center;gap:4px;flex-shrink:0;font-size:10.5px;color:#4B5563;">
                                            <span style="background:#F3F4F6;padding:1px 6px;border-radius:4px;font-weight:600;"><?php echo esc_html($opt_jenj); ?> &middot; <?php echo $opt_sks; ?> SKS &middot; Sem <?php echo $opt_sem; ?></span>
                                            <?php if ($opt_rps) : ?><span style="background:#DCFCE7;color:#166534;padding:1px 5px;border-radius:4px;font-weight:700;">RPS</span><?php endif; ?>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                        <?php else : ?>
                            <p style="margin:0;font-size:12px;color:#6B7280;">Belum ada data mata kuliah terdaftar. Tambahkan melalui menu Manajemen Mata Kuliah.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Modal Actions -->
                    <div style="display:flex;align-items:center;justify-content:flex-end;gap:12px;padding-top:10px;border-top:1px solid #E5E7EB;">
                        <button type="button" class="button" onclick="bkCloseEditModal()" style="padding:8px 18px;border-radius:8px;font-weight:600;font-size:13px;">Batal</button>
                        <button type="submit" name="bk_save_single_dosen" class="button button-primary" style="background:#0D3B22;color:#F5B82E;border:none;font-size:13.5px;font-weight:700;padding:8px 24px;border-radius:8px;cursor:pointer;box-shadow:0 3px 10px rgba(13,59,34,0.2);">
                            💾 Simpan Perubahan Data Dosen
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Interactive Reordering & Modal Scripts -->
    <script>
    function bkHighlightRow(tr) {
        tr.style.backgroundColor = '#FEF3C7';
        setTimeout(function() {
            tr.style.backgroundColor = '';
        }, 600);
    }

    function bkReindex() {
        var tbody = document.getElementById('bk-dosen-tbody');
        if (!tbody) return;
        var rows = tbody.querySelectorAll('tr[data-slug]');
        rows.forEach(function(row, idx) {
            var input = row.querySelector('.bk-order-input');
            if (input) {
                input.value = idx + 1;
            }
        });
    }

    function bkMoveRow(btn, direction) {
        var tr = btn.closest('tr');
        if (!tr) return;
        var tbody = tr.parentNode;
        if (direction === -1) {
            var prev = tr.previousElementSibling;
            if (prev && prev.getAttribute('data-slug')) {
                tbody.insertBefore(tr, prev);
                bkHighlightRow(tr);
                bkReindex();
            }
        } else if (direction === 1) {
            var next = tr.nextElementSibling;
            if (next && next.getAttribute('data-slug')) {
                tbody.insertBefore(next, tr);
                bkHighlightRow(tr);
                bkReindex();
            }
        }
    }

    function bkShowFeedback(msg) {
        var fb = document.getElementById('bk-sort-feedback');
        if (!fb) return;
        fb.innerHTML = '&#10003; ' + msg;
        fb.style.display = 'inline-block';
        setTimeout(function() {
            fb.style.display = 'none';
        }, 4000);
    }

    function bkApplyPresetHierarchy() {
        var tbody = document.getElementById('bk-dosen-tbody');
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr[data-slug]'));

        rows.sort(function(a, b) {
            var rankA = parseInt(a.getAttribute('data-rank') || '40', 10);
            var rankB = parseInt(b.getAttribute('data-rank') || '40', 10);
            if (rankA !== rankB) {
                return rankA - rankB;
            }
            var nameA = (a.getAttribute('data-nama') || '').toLowerCase();
            var nameB = (b.getAttribute('data-nama') || '').toLowerCase();
            return nameA.localeCompare(nameB);
        });

        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        bkReindex();
        bkShowFeedback('Preset Hierarki (Dekan &rarr; Pimpinan &rarr; Guru Besar) diterapkan! Silakan klik "Simpan Urutan Tampilan".');
    }

    function bkApplyPresetAlphabetical() {
        var tbody = document.getElementById('bk-dosen-tbody');
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr[data-slug]'));

        rows.sort(function(a, b) {
            var nameA = (a.getAttribute('data-nama') || '').toLowerCase();
            var nameB = (b.getAttribute('data-nama') || '').toLowerCase();
            return nameA.localeCompare(nameB);
        });

        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        bkReindex();
        bkShowFeedback('Preset Abjad Nama (A &rarr; Z) diterapkan! Silakan klik "Simpan Urutan Tampilan".');
    }

    function bkOnNumberChange(input) {
        var tbody = document.getElementById('bk-dosen-tbody');
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr[data-slug]'));

        rows.sort(function(a, b) {
            var valA = parseInt(a.querySelector('.bk-order-input').value || '999', 10);
            var valB = parseInt(b.querySelector('.bk-order-input').value || '999', 10);
            return valA - valB;
        });

        rows.forEach(function(row) {
            tbody.appendChild(row);
        });

        bkReindex();
    }

    // Modal Edit Functions
        function bkAddPendidikanItem(jenjang, bidang, institusi, tahun) {
        jenjang = jenjang || 'S1';
        bidang = bidang || '';
        institusi = institusi || '';
        tahun = tahun || '';

        var container = document.getElementById('edit-pendidikan-list');
        if (!container) return;
        var div = document.createElement('div');
        div.className = 'pend-row';
        div.style.cssText = 'display:grid;grid-template-columns:90px 1.4fr 1.4fr 80px 32px;gap:8px;align-items:center;background:#ffffff;border:1px solid #D1D5DB;border-radius:8px;padding:8px 10px;box-shadow:0 1px 3px rgba(0,0,0,0.03);';

        var optS1 = (jenjang === 'S1' ? ' selected' : '');
        var optS2 = (jenjang === 'S2' ? ' selected' : '');
        var optS3 = (jenjang === 'S3' ? ' selected' : '');
        var optProf = (jenjang === 'Profesi' ? ' selected' : '');
        var optSp = (jenjang === 'Spesialis' ? ' selected' : '');
        var optD3 = (jenjang === 'D3' ? ' selected' : '');

        div.innerHTML = '<select class="pend-jenjang" style="height:34px;border-radius:6px;font-size:12px;font-weight:700;border:1.5px solid #D1D5DB;padding:0 6px;color:#0D3B22;">' +
            '<option value="S1"' + optS1 + '>S1</option>' +
            '<option value="S2"' + optS2 + '>S2</option>' +
            '<option value="S3"' + optS3 + '>S3</option>' +
            '<option value="Profesi"' + optProf + '>Profesi</option>' +
            '<option value="Spesialis"' + optSp + '>Spesialis</option>' +
            '<option value="D3"' + optD3 + '>D3</option>' +
        '</select>' +
        '<input type="text" class="pend-bidang" value="' + bidang.replace(/"/g, '&quot;') + '" placeholder="Jurusan / Bidang Ilmu" style="height:34px;border-radius:6px;font-size:12.5px;border:1.5px solid #D1D5DB;padding:0 8px;">' +
        '<input type="text" class="pend-institusi" value="' + institusi.replace(/"/g, '&quot;') + '" placeholder="Nama Universitas / Almamater" style="height:34px;border-radius:6px;font-size:12.5px;border:1.5px solid #D1D5DB;padding:0 8px;">' +
        '<input type="text" class="pend-tahun" value="' + tahun.replace(/"/g, '&quot;') + '" placeholder="Tahun" style="height:34px;border-radius:6px;font-size:12px;border:1.5px solid #D1D5DB;padding:0 6px;text-align:center;">' +
        '<button type="button" onclick="this.closest(\'.pend-row\').remove()" style="background:#FEE2E2;color:#991B1B;border:none;border-radius:6px;height:32px;width:32px;cursor:pointer;font-weight:bold;font-size:16px;display:flex;align-items:center;justify-content:center;line-height:1;" title="Hapus">&times;</button>';

        container.appendChild(div);
    }

    function bkAddPengalamanItem(posisi, instansi, periode) {
        posisi = posisi || '';
        instansi = instansi || '';
        periode = periode || '';

        var container = document.getElementById('edit-pengalaman-list');
        if (!container) return;
        var div = document.createElement('div');
        div.className = 'peng-row';
        div.style.cssText = 'display:grid;grid-template-columns:1.5fr 1.5fr 140px 32px;gap:8px;align-items:center;background:#ffffff;border:1px solid #D1D5DB;border-radius:8px;padding:8px 10px;box-shadow:0 1px 3px rgba(0,0,0,0.03);';

        div.innerHTML = '<input type="text" class="peng-posisi" value="' + posisi.replace(/"/g, '&quot;') + '" placeholder="Posisi / Jabatan / Peran" style="height:34px;border-radius:6px;font-size:12.5px;border:1.5px solid #D1D5DB;padding:0 8px;">' +
        '<input type="text" class="peng-instansi" value="' + instansi.replace(/"/g, '&quot;') + '" placeholder="Instansi / Lembaga / Organisasi" style="height:34px;border-radius:6px;font-size:12.5px;border:1.5px solid #D1D5DB;padding:0 8px;">' +
        '<input type="text" class="peng-periode" value="' + periode.replace(/"/g, '&quot;') + '" placeholder="Contoh: 2021 - Sekarang" style="height:34px;border-radius:6px;font-size:12px;border:1.5px solid #D1D5DB;padding:0 8px;">' +
        '<button type="button" onclick="this.closest(\'.peng-row\').remove()" style="background:#FEE2E2;color:#991B1B;border:none;border-radius:6px;height:32px;width:32px;cursor:pointer;font-weight:bold;font-size:16px;display:flex;align-items:center;justify-content:center;line-height:1;" title="Hapus">&times;</button>';

        container.appendChild(div);
    }

    function bkPrepareSubmit() {
        var pRows = document.querySelectorAll('#edit-pendidikan-list .pend-row');
        var pData = [];
        pRows.forEach(function(r) {
            var j = r.querySelector('.pend-jenjang').value;
            var b = r.querySelector('.pend-bidang').value.trim();
            var ins = r.querySelector('.pend-institusi').value.trim();
            var th = r.querySelector('.pend-tahun').value.trim();
            if (b || ins) {
                pData.push({ jenjang: j, bidang: b, institusi: ins, tahun: th });
            }
        });
        document.getElementById('edit-pendidikan-json').value = JSON.stringify(pData);

        var expRows = document.querySelectorAll('#edit-pengalaman-list .peng-row');
        var expData = [];
        expRows.forEach(function(r) {
            var pos = r.querySelector('.peng-posisi').value.trim();
            var ins = r.querySelector('.peng-instansi').value.trim();
            var per = r.querySelector('.peng-periode').value.trim();
            if (pos || ins) {
                expData.push({ posisi: pos, instansi: ins, periode: per });
            }
        });
        document.getElementById('edit-pengalaman-json').value = JSON.stringify(expData);
        return true;
    }

    function bkOpenEditModal(d) {
        if (!d) return;
        document.getElementById('edit-slug').value = d.slug || '';
        document.getElementById('edit-nama-lengkap').value = d.nama_lengkap || '';
        document.getElementById('edit-nama-panggilan').value = d.nama_panggilan || '';
        document.getElementById('edit-nip').value = d.nip || '';
        document.getElementById('edit-nidn').value = d.nidn || '';
        document.getElementById('edit-jabatan-fungsional').value = d.jabatan_fungsional || '';
        document.getElementById('edit-jabatan-struktural').value = d.jabatan_struktural || '';
        document.getElementById('edit-unit').value = d.unit || 'S1 Bimbingan dan Konseling';
        document.getElementById('edit-foto-link').value = d.foto_link || '';
        document.getElementById('edit-kepakaran').value = d.kepakaran || '';
        document.getElementById('edit-sinta-id').value = d.sinta_id || '';
        document.getElementById('edit-sinta-url').value = d.sinta_url || '';
        document.getElementById('edit-scholar-url').value = d.scholar_url || '';
        document.getElementById('edit-scopus-url').value = d.scopus_url || '';
        document.getElementById('edit-email').value = d.email || '';
        document.getElementById('edit-nohp').value = d.no_hp || '';
        document.getElementById('edit-bio').value = d.bio || '';

        bkUpdatePhotoPreview(d.foto_link || '');

        // Populate Pendidikan
        var pList = document.getElementById('edit-pendidikan-list');
        if (pList) {
            pList.innerHTML = '';
            if (d.pendidikan && Array.isArray(d.pendidikan) && d.pendidikan.length) {
                d.pendidikan.forEach(function(p) {
                    bkAddPendidikanItem(p.jenjang, p.bidang, p.institusi, p.tahun);
                });
            } else {
                bkAddPendidikanItem('S1', '', '', '');
            }
        }

        // Populate Pengalaman
        var expList = document.getElementById('edit-pengalaman-list');
        if (expList) {
            expList.innerHTML = '';
            if (d.pengalaman && Array.isArray(d.pengalaman) && d.pengalaman.length) {
                d.pengalaman.forEach(function(e) {
                    bkAddPengalamanItem(e.posisi, e.instansi, e.periode);
                });
            } else {
                bkAddPengalamanItem('', '', '');
            }
        }
        // Populate Mata Kuliah Diampu (Auto-Sync)
        var mkSearch = document.getElementById('edit-mk-search');
        if (mkSearch) {
            mkSearch.value = '';
            bkFilterModalMk('');
        }
        var mkLookup = {};
        if (d.mk_ids && Array.isArray(d.mk_ids)) {
            d.mk_ids.forEach(function(id) {
                if (id) mkLookup[String(id)] = true;
            });
        }
        var mkCheckboxes = document.querySelectorAll('.bk-modal-mk-cb');
        mkCheckboxes.forEach(function(cb) {
            cb.checked = !!mkLookup[String(cb.value)];
        });
        bkRefreshModalMkCount();

        document.getElementById('modal-title').innerText = 'Edit Data: ' + (d.nama_lengkap || d.slug);

        var modal = document.getElementById('bk-edit-modal-backdrop');
        modal.style.display = 'flex';
    }

    function bkCloseEditModal() {
        var modal = document.getElementById('bk-edit-modal-backdrop');
        modal.style.display = 'none';
    }

    function bkRefreshModalMkCount() {
        var countEl = document.getElementById('edit-mk-count');
        if (!countEl) return;
        var checked = document.querySelectorAll('.bk-modal-mk-cb:checked').length;
        countEl.textContent = checked + ' dipilih';
    }

    function bkFilterModalMk(query) {
        var q = (query || '').toLowerCase().trim();
        var rows = document.querySelectorAll('#edit-mk-checklist .bk-modal-mk-row');
        rows.forEach(function(row) {
            var hay = (row.getAttribute('data-search') || '').toLowerCase();
            if (!q || hay.indexOf(q) !== -1) {
                row.style.display = 'flex';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function bkUpdatePhotoPreview(url) {
        var img = document.getElementById('edit-photo-preview');
        var none = document.getElementById('edit-photo-none');
        if (url && url.trim().length > 5) {
            url = url.trim();
            if (url.indexOf('assets/') === 0) {
                url = <?php echo wp_json_encode(trailingslashit(get_template_directory_uri())); ?> + url;
            }
            img.src = url;
            img.style.display = 'block';
            none.style.display = 'none';
        } else {
            img.style.display = 'none';
            none.style.display = 'block';
        }
    }

    function bkChooseMediaPhoto() {
        if (typeof wp !== 'undefined' && wp.media) {
            var frame = wp.media({
                title: 'Pilih atau Upload Foto Dosen (Transparan Portrait)',
                button: { text: 'Gunakan Foto Ini' },
                multiple: false
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                document.getElementById('edit-foto-link').value = attachment.url;
                bkUpdatePhotoPreview(attachment.url);
            });
            frame.open();
        } else {
            alert('Fitur Media Library WordPress tidak tersedia.');
        }
    }

    var bkSintaNonce = '<?php echo esc_js(wp_create_nonce("bk_sinta_ajax_nonce")); ?>';

    function bkSyncSingleSinta(slug, btnEl) {
        if (!slug) return;
        var origText = btnEl ? btnEl.innerHTML : '';
        if (btnEl) {
            btnEl.disabled = true;
            btnEl.innerHTML = '<span>⏳ Sync...</span>';
        }
        var fd = new FormData();
        fd.append('action', 'bk_sync_single_sinta');
        fd.append('nonce', bkSintaNonce);
        fd.append('slug', slug);

        return fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                if (btnEl) {
                    btnEl.disabled = false;
                    if (res && res.success) {
                        btnEl.innerHTML = '<span>✓ SINTA</span>';
                        setTimeout(function() { btnEl.innerHTML = origText; }, 2200);
                    } else {
                        btnEl.innerHTML = '<span>⚠ Gagal</span>';
                        setTimeout(function() { btnEl.innerHTML = origText; }, 2200);
                    }
                }
                return res;
            })
            .catch(function(err) {
                if (btnEl) {
                    btnEl.disabled = false;
                    btnEl.innerHTML = origText;
                }
                return { success: false, data: { message: String(err) } };
            });
    }

    async function bkStartBatchSintaSync() {
        var btns = Array.prototype.slice.call(document.querySelectorAll('.bk-sync-sinta-btn'));
        if (!btns.length) return;

        var mainBtn = document.getElementById('bk-batch-sinta-btn');
        var wrap = document.getElementById('bk-sinta-progress-wrap');
        var bar = document.getElementById('bk-sinta-progress-bar');
        var txt = document.getElementById('bk-sinta-progress-text');
        var pct = document.getElementById('bk-sinta-progress-pct');

        if (mainBtn) {
            mainBtn.disabled = true;
            mainBtn.textContent = '⏳ Sedang Sinkronisasi...';
        }
        if (wrap) wrap.style.display = 'block';

        var total = btns.length;
        var ok = 0;
        var skipped = 0;

        for (var i = 0; i < total; i++) {
            var b = btns[i];
            var slug = b.getAttribute('data-slug');
            var nama = b.getAttribute('data-nama') || slug;
            var percent = Math.round(((i) / total) * 100);
            if (bar) bar.style.width = percent + '%';
            if (pct) pct.textContent = percent + '%';
            if (txt) txt.textContent = 'Menarik SINTA (' + (i + 1) + '/' + total + '): ' + nama + '...';

            var res = await bkSyncSingleSinta(slug, b);
            if (res && res.success) {
                ok++;
            } else {
                skipped++;
            }
        }

        if (bar) bar.style.width = '100%';
        if (pct) pct.textContent = '100%';
        if (txt) txt.textContent = 'Selesai! ' + ok + ' dosen berhasil disinkronkan' + (skipped ? ' (' + skipped + ' dilewati/tanpa SINTA ID).' : '.');
        if (mainBtn) {
            mainBtn.disabled = false;
            mainBtn.textContent = '⚡ Sync Semua Dosen Sekarang';
        }
        var lbl = document.getElementById('bk-last-synced-label');
        if (lbl) lbl.textContent = 'Baru saja (muat ulang halaman untuk melihat angka terbaru)';
    }

    function bkRegenerateSintaToken(btn) {
        if (!confirm('Buat ulang token? Secret BK_SINTA_SYNC_TOKEN di GitHub Actions juga harus diperbarui.')) return;
        btn.disabled = true;
        var fd = new FormData();
        fd.append('action', 'bk_regenerate_sinta_token');
        fd.append('nonce', bkSintaNonce);
        fetch(ajaxurl, { method: 'POST', body: fd, credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(res) {
                btn.disabled = false;
                if (res && res.success && res.data && res.data.token) {
                    document.getElementById('bk-sinta-token-input').value = res.data.token;
                }
            })
            .catch(function() { btn.disabled = false; });
    }
    </script>
    <?php
}

/**
 * Seamless Local Tunnel & Live Link Support (localsite.io, ngrok, loca.lt)
 * Allows testing via feline-trains.localsite.io without canonical redirects or broken URLs.
 */
function bk_get_active_tunnel_host() {
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';
    if (!empty($host) && (strpos($host, 'localsite.io') !== false || strpos($host, 'ngrok') !== false || strpos($host, 'loca.lt') !== false || strpos($host, '.ts.net') !== false || strpos($host, 'raisardhi') !== false || strpos($host, '100.') === 0 || strpos($host, ':10004') !== false)) {
        return $host;
    }
    return false;
}

add_action('init', function() {
    if (bk_get_active_tunnel_host()) {
        remove_action('template_redirect', 'redirect_canonical');
    }
}, 1);

add_filter('home_url', function($url, $path, $orig_scheme, $blog_id = null) {
    $tunnel_host = bk_get_active_tunnel_host();
    if ($tunnel_host) {
        $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $clean_path = '/' . ltrim($path, '/');
        return $proto . '://' . $tunnel_host . ($clean_path === '/' ? '' : $clean_path);
    }
    return $url;
}, 99, 4);

add_filter('site_url', function($url, $path, $scheme, $blog_id = null) {
    $tunnel_host = bk_get_active_tunnel_host();
    if ($tunnel_host) {
        $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $clean_path = '/' . ltrim($path, '/');
        return $proto . '://' . $tunnel_host . ($clean_path === '/' ? '' : $clean_path);
    }
    return $url;
}, 99, 4);

add_filter('the_content', function($content) {
    if (empty($content) || !is_string($content)) return $content;
    $tunnel_host = bk_get_active_tunnel_host();
    if ($tunnel_host) {
        $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $target_url = $proto . '://' . $tunnel_host;
    } else {
        $target_url = rtrim(home_url(), '/');
    }
    $content = str_replace('http://bk-fip.local/', $target_url . '/', $content);
    $content = str_replace('https://bk-fip.local/', $target_url . '/', $content);
    $content = str_replace('http://bk-fip.local', $target_url, $content);
    $content = str_replace('https://bk-fip.local', $target_url, $content);
    return $content;
}, 99);


function bk_rewrite_tunnel_asset_url($url) {
    if (empty($url) || !is_string($url)) return $url;
    $tunnel_host = bk_get_active_tunnel_host();
    if ($tunnel_host) {
        $proto = (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $target_url = $proto . '://' . $tunnel_host;
    } else {
        $target_url = rtrim(home_url(), '/');
    }
    $url = str_replace('http://bk-fip.local/', $target_url . '/', $url);
    $url = str_replace('https://bk-fip.local/', $target_url . '/', $url);
    $url = str_replace('http://bk-fip.local', $target_url, $url);
    $url = str_replace('https://bk-fip.local', $target_url, $url);
    return $url;
}
foreach (array('style_loader_src', 'script_loader_src', 'plugins_url', 'content_url', 'template_directory_uri', 'stylesheet_directory_uri') as $bk_hook) {
    add_filter($bk_hook, 'bk_rewrite_tunnel_asset_url', 99);
}

/**
 * PARSER SUMBER VIDEO SPOTLIGHT (YOUTUBE, GOOGLE DRIVE, VIMEO, DIRECT MP4)
 * Mendukung video eksternal streaming tanpa membebani kuota upload hosting WordPress (Bypass limit 2MB)
 */
/**
 * PARSER SUMBER GAMBAR SPOTLIGHT (GOOGLE DRIVE, DROPBOX, DIRECT CDN)
 * Mengonversi link Google Drive Viewer (file/d/ID/view) menjadi direct image CDN stream
 */
if (!function_exists('bk_extract_gdrive_id')) {
    function bk_extract_gdrive_id($url) {
        $url = trim((string) $url);
        if ($url === '') return '';
        if (preg_match('#drive\.google\.com/(?:file/d/|thumbnail\?id=|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#drive\.google\.com/[^\s]*?/d/([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
            return $m[1];
        }
        if (preg_match('#lh3\.googleusercontent\.com/d/([a-zA-Z0-9_-]{10,})#i', $url, $m)) {
            return $m[1];
        }
        return '';
    }
}

if (!function_exists('bk_parse_spotlight_image_url')) {
    function bk_parse_spotlight_image_url($url) {
        if (empty($url)) return '';
        $url = trim($url);

        // 1. Google Drive: file/d/{ID}, open?id={ID}, uc?...id={ID}, thumbnail?id={ID}, lh3.googleusercontent.com/d/{ID}
        $gd_id = bk_extract_gdrive_id($url);
        if ($gd_id !== '') {
            return 'https://drive.google.com/thumbnail?id=' . $gd_id . '&sz=w1920';
        }

        // 2. Dropbox: convert www.dropbox.com to dl.dropboxusercontent.com
        if (strpos($url, 'dropbox.com') !== false) {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
            $url = preg_replace('/\?dl=[01]$/', '', $url);
            return $url;
        }

        return function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($url) : $url;
    }
}

if (!function_exists('bk_parse_spotlight_video_source')) {
    function bk_parse_spotlight_video_source($url) {
        if (empty($url)) return false;
        $url = trim($url);

        // 1. YouTube
        if (preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([a-zA-Z0-9_-]{11})/i', $url, $m)) {
            $yt_id = $m[1];
            return [
                'type'      => 'youtube',
                'id'        => $yt_id,
                'embed_url' => 'https://www.youtube-nocookie.com/embed/' . $yt_id . '?autoplay=1&rel=0&playsinline=1&modestbranding=1'
            ];
        }

        // 2. Google Drive
        $gd_id = bk_extract_gdrive_id($url);
        if ($gd_id !== '') {
            return [
                'type'      => 'gdrive',
                'id'        => $gd_id,
                'embed_url' => 'https://drive.google.com/file/d/' . $gd_id . '/preview'
            ];
        }

        // 3. Vimeo
        if (preg_match('/vimeo\.com\/(?:video\/)?([0-9]+)/i', $url, $m)) {
            $vim_id = $m[1];
            return [
                'type'      => 'vimeo',
                'id'        => $vim_id,
                'embed_url' => 'https://player.vimeo.com/video/' . $vim_id . '?autoplay=1&byline=0&title=0'
            ];
        }

        // 4. Direct video (MP4/WebM atau link CDN/aset tema)
        $resolved = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($url) : $url;
        return [
            'type' => 'direct',
            'url'  => $resolved
        ];
    }
}

// AUTOMATIC DATABASE CLEANUP: Purge any lingering 'bk-fip.local' URLs from database on live / staging sites
add_action('init', function() {
    if (get_option('bk_local_url_cleaned_v2') !== '1') {
        global $wpdb;
        $home = rtrim(home_url(), '/');
        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'http://bk-fip.local/', '{$home}/') WHERE post_content LIKE '%bk-fip.local%'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'https://bk-fip.local/', '{$home}/') WHERE post_content LIKE '%bk-fip.local%'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'http://bk-fip.local', '{$home}') WHERE post_content LIKE '%bk-fip.local%'");
        $wpdb->query("UPDATE {$wpdb->posts} SET post_content = REPLACE(post_content, 'https://bk-fip.local', '{$home}') WHERE post_content LIKE '%bk-fip.local%'");
        update_option('bk_local_url_cleaned_v2', '1', false);
    }
}, 5);

/**
 * AUTOMATIC SITE HEALTH OPTIMIZER: Autoloaded Options & Transient Cleanup
 * Addresses WordPress Site Health critical issue: "Autoloaded options could affect performance"
 * Automatically sets autoload = 'no' on large cache options and purges expired transients.
 */
function bk_fip_run_site_health_db_optimization() {
    global $wpdb;

    $stats = array(
        'options_unloaded'   => 0,
        'transients_deleted' => 0,
        'size_before_kb'     => 0,
        'size_after_kb'      => 0,
    );

    // Hitung ukuran autoloaded options sebelum optimasi
    $size_before = $wpdb->get_var("SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload IN ('yes', 'on', '1')");
    $stats['size_before_kb'] = round(($size_before ?: 0) / 1024, 2);

    // 1. Matikan autoload ('no') untuk seluruh cache data besar di wp_options
    $large_options = array(
        'bk_dosen_data_cache',
        'bk_dosen_custom_order',
        'ppb_spotlight_slides',
        'bk_dosen_last_synced',
        'ppb_content_synced_version',
        'ppb_mega_menu_autoseeded_v1',
        'ppb_portal_dummy_seeded_v4',
        'ppb_portal_initial_seeded_v2',
        'bk_local_url_cleaned_v2',
        'bk_site_health_optimized_v1',
        'bk_site_health_optimized_v2',
        'bk_site_health_optimized_v3',
        'bk_mega_menu_spotlights',
        'bk_video_chapters',
        'bk_portal_cards',
        'bk_degree_s1',
        'bk_degree_s2',
        'bk_degree_s3',
        'ppb_elibrary_items',
        'ppb_elibrary_portal_links',
        'ppb_elibrary_submissions',
        'ppb_mega_menu_autoseeded_v3',
        'ppb_mega_menu_autoseeded_v4',
        'ppb_spotlight_defaults_v2',
        'ppb_mata_kuliah_list',
        'ppb_mata_kuliah_seeded_v1',
        'ppb_lamdik_settings',
        'ppb_link_buku_kurikulum',
        'ppb_link_sk_kurikulum',
        'ppb_link_pedoman_penilaian',
        'ppb_link_pedoman_skripsi',
        'ppb_link_rubrik_konseling',
        'ppb_link_rubrik_project',
        'ppb_link_sk_kepakaran',
        'ppb_link_moa_pendidikan',
        'ppb_link_moa_penelitian',
        'ppb_link_moa_pkm',
        'ppb_link_roadmap_pilar1_sk',
        'ppb_link_roadmap_pilar1_doc',
        'ppb_link_roadmap_pilar2_sk',
        'ppb_link_roadmap_pilar2_doc',
        'ppb_link_roadmap_pilar3_sk',
        'ppb_link_roadmap_pilar3_doc',
        'ppb_km_total_aktif',
        'ppb_km_lulus_tepat_waktu',
        'ppb_km_persen_3t',
        'ppb_km_rasio_dosen',
        'ppb_km_ipk_rata',
        'ppb_ts_response_rate',
        'ppb_ts_waktu_tunggu_3bln',
        'ppb_ts_keselarasan_tinggi',
        'ppb_ts_kepuasan_sangat_baik',
        'ppb_ts_drive_laporan_link',
        'ppb_ts_catatan_analisis',
        'ppb_layanan_wa_hotline',
        'ppb_layanan_jam_operasional',
        'ppb_layanan_lokasi_lab',
        'ppb_layanan_sop_perlindungan_link',
        'ppb_layanan_sipadu_url',
        'ppb_layanan_survei_url',
        'ppb_survei_submissions',
        'ppb_survei_settings',
        'ppb_mis_pnbp_data',
        'ppb_jurnal_bk_data',
        'ppb_maba_statistik_data',
        'ppb_mbkm_kemanusiaan_data',
        'ppb_lab_kegiatan_list',
        'ppb_lab_lomba_list',
        'ppb_sarpras_list',
    );

    foreach ($large_options as $opt_name) {
        $updated = $wpdb->query($wpdb->prepare(
            "UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name = %s AND autoload IN ('yes', 'on', '1')",
            $opt_name
        ));
        if ($updated) {
            $stats['options_unloaded'] += (int)$updated;
        }
    }

    // 2. Scan options lain yang berukuran > 150 KB dan matikan autoload jika bukan core system essential
    $heavy_options = $wpdb->get_results(
        "SELECT option_name, LENGTH(option_value) AS sz 
         FROM {$wpdb->options} 
         WHERE autoload IN ('yes', 'on', '1') 
           AND option_name NOT IN ('rewrite_rules', 'active_plugins', 'cron', 'siteurl', 'home') 
           AND LENGTH(option_value) > 153600"
    );

    if (!empty($heavy_options)) {
        foreach ($heavy_options as $ho) {
            $up = $wpdb->query($wpdb->prepare(
                "UPDATE {$wpdb->options} SET autoload = 'no' WHERE option_name = %s",
                $ho->option_name
            ));
            if ($up) {
                $stats['options_unloaded'] += (int)$up;
            }
        }
    }

    // 3. Bersihkan transient kedaluwarsa (expired transients)
    $now = time();
    $del_transients = $wpdb->query($wpdb->prepare(
        "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
         WHERE a.option_name LIKE %s
           AND a.option_name NOT LIKE %s
           AND b.option_name = CONCAT('_transient_timeout_', SUBSTRING(a.option_name, 12))
           AND b.option_value < %d",
        $wpdb->esc_like('_transient_') . '%',
        $wpdb->esc_like('_transient_timeout_') . '%',
        $now
    ));
    if ($del_transients) {
        $stats['transients_deleted'] += (int)$del_transients;
    }

    // 4. Bersihkan orphaned transient timeout rows
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND option_value < %d",
        $wpdb->esc_like('_transient_timeout_') . '%',
        $now
    ));

    // 5. Bersihkan site transients kedaluwarsa
    $del_site_transients = $wpdb->query($wpdb->prepare(
        "DELETE a, b FROM {$wpdb->options} a, {$wpdb->options} b
         WHERE a.option_name LIKE %s
           AND a.option_name NOT LIKE %s
           AND b.option_name = CONCAT('_site_transient_timeout_', SUBSTRING(a.option_name, 17))
           AND b.option_value < %d",
        $wpdb->esc_like('_site_transient_') . '%',
        $wpdb->esc_like('_site_transient_timeout_') . '%',
        $now
    ));
    if ($del_site_transients) {
        $stats['transients_deleted'] += (int)$del_site_transients;
    }

    // 6. Hitung ukuran baru setelah optimasi
    $size_after = $wpdb->get_var("SELECT SUM(LENGTH(option_value)) FROM {$wpdb->options} WHERE autoload IN ('yes', 'on', '1')");
    $stats['size_after_kb'] = round(($size_after ?: 0) / 1024, 2);

    // 7. Bersihkan duplikat plugin nonaktif (versi 1.0.0 lama)
    $plugins_cleaned = bk_fip_clean_duplicate_inactive_plugins();
    $stats['plugins_cleaned'] = $plugins_cleaned;

    // Set flag selesai
    update_option('bk_site_health_optimized_v1', '1', false);

    // Flush alloptions cache memory agar langsung terasa tanpa restart
    wp_cache_delete('alloptions', 'options');

    return $stats;
}

/**
 * AUTOMATIC INACTIVE DUPLICATE PLUGIN CLEANER
 * Safely removes older inactive duplicate versions of BK Connect (v1.0.0)
 * while preserving the active v1.2.2 Git-connected plugin.
 */
function bk_fip_clean_duplicate_inactive_plugins() {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }

    $all_plugins = get_plugins();
    $active_plugins = (array) get_option('active_plugins', array());
    $cleaned_count = 0;

    foreach ($all_plugins as $plugin_file => $plugin_data) {
        $p_name    = $plugin_data['Name'] ?? '';
        $p_version = $plugin_data['Version'] ?? '';

        // Deteksi jika namanya BK Connect Suite, versi < 1.2.0, dan TIDAK aktif
        if (
            stripos($p_name, 'BK Connect Suite') !== false &&
            version_compare($p_version, '1.2.0', '<') &&
            !in_array($plugin_file, $active_plugins)
        ) {
            $plugin_dir_rel = dirname($plugin_file);
            if ($plugin_dir_rel && $plugin_dir_rel !== '.' && $plugin_dir_rel !== '..') {
                $full_dir = WP_PLUGIN_DIR . '/' . $plugin_dir_rel;
                if (is_dir($full_dir)) {
                    // Pastikan BUKAN folder plugin yang sedang aktif
                    $is_active_dir = false;
                    foreach ($active_plugins as $ap) {
                        if (dirname($ap) === $plugin_dir_rel) {
                            $is_active_dir = true;
                            break;
                        }
                    }

                    if (!$is_active_dir) {
                        bk_fip_recursive_delete_dir($full_dir);
                        $cleaned_count++;
                    }
                }
            }
        }
    }

    if ($cleaned_count > 0) {
        wp_clean_plugins_cache();
    }

    return $cleaned_count;
}

function bk_fip_recursive_delete_dir($dir) {
    if (!is_dir($dir)) return false;
    $items = @scandir($dir);
    if (!is_array($items)) return false;
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($path)) {
            bk_fip_recursive_delete_dir($path);
        } else {
            @unlink($path);
        }
    }
    return @rmdir($dir);
}

// Otomatis jalankan pada reload admin
add_action('admin_init', function() {
    if (get_option('bk_site_health_optimized_v3') !== '1') {
        bk_fip_run_site_health_db_optimization();
        update_option('bk_site_health_optimized_v3', '1', false);
    }
}, 5);

// Handler manual tombol optimasi via WP Admin
add_action('admin_post_bk_manual_optimize_db', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Akses ditolak.');
    }
    check_admin_referer('bk_manual_optimize_db_action');
    $stats = bk_fip_run_site_health_db_optimization();
    $redirect_url = add_query_arg(array(
        'bk_optimized'     => '1',
        'opt_before'       => $stats['size_before_kb'],
        'opt_after'        => $stats['size_after_kb'],
        'opt_count'        => $stats['options_unloaded'],
        'trans_deleted'    => $stats['transients_deleted'],
        'plugins_cleaned'  => $stats['plugins_cleaned'] ?? 0,
    ), wp_get_referer() ?: admin_url('site-health.php'));
    wp_safe_redirect($redirect_url);
    exit;
});

// Admin notice jika berhasil dioptimalkan
add_action('admin_notices', function() {
    if (isset($_GET['bk_optimized']) && $_GET['bk_optimized'] === '1') {
        $before  = esc_html($_GET['opt_before'] ?? '3000');
        $after   = esc_html($_GET['opt_after'] ?? '250');
        $opts    = (int)($_GET['opt_count'] ?? 0);
        $trans   = (int)($_GET['trans_deleted'] ?? 0);
        $plugins = (int)($_GET['plugins_cleaned'] ?? 0);
        echo '<div class="notice notice-success is-dismissible" style="padding:14px 18px;border-left:4px solid #16a34a;background:#f0fdf4;">
            <h3 style="margin:0 0 6px 0;color:#166534;font-size:15px;">&#10003; Database WordPress &amp; Plugin Berhasil Dioptimalkan!</h3>
            <p style="margin:0;font-size:13.5px;color:#14532d;line-height:1.6;">
                Ukuran data autoload berhasil diturunkan dari <strong>' . $before . ' KB</strong> menjadi <strong>' . $after . ' KB</strong>.<br>
                Diperbaiki <strong>' . $opts . ' opsi data besar</strong> menjadi non-autoload, dibersihkan <strong>' . $trans . ' transients kedaluwarsa</strong>' . ($plugins > 0 ? ', dan dihapus <strong>' . $plugins . ' duplikat plugin nonaktif</strong>' : '') . '. Site Health status sekarang hijau dan performa situs jauh lebih responsif!
            </p>
        </div>';
    }
});
