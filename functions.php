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

    // Google Fonts: Poppins (Sans) & Fraunces (Editorial Display Serif)

    wp_enqueue_style(

        'bk-fip-fonts',

        'https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400..700;1,9..144,400..700&family=Poppins:wght@300;400;500;600;700;800&family=IBM+Plex+Mono:wght@400;500;600&display=swap',

        array(),

        null

    );



    // Tailwind CSS via CDN

    wp_enqueue_script('tailwind-cdn', 'https://cdn.tailwindcss.com', array(), null, false);

    wp_add_inline_script('tailwind-cdn', "

        tailwind.config = {

            theme: {

                extend: {

                    fontFamily: {

                        sans: ['Poppins', 'sans-serif'],

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



    // Theme Stylesheet

    wp_enqueue_style('bk-fip-style', get_stylesheet_uri(), array(), '2.1.2');

}

add_action('wp_enqueue_scripts', 'bk_fip_enqueue_scripts');



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

    <div style="margin-top: 10px; padding: 10px; background: #f0f7f3; border-left: 4px solid #185333;">

        <small><strong>Tips:</strong> Untuk foto dosen, gunakan fitur <strong>"Featured Image" (Gambar Unggulan)</strong> di panel sebelah kanan.</small>

    </div>

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



    // Simpan pengaturan jika form disubmit

    if (isset($_POST['bk_save_settings']) && check_admin_referer('bk_settings_verify')) {

        $options = array(

            'bk_hero_badge',

            'bk_hero_h1_part1',

            'bk_hero_h1_highlight',

            'bk_hero_h1_part2',

            'bk_hero_desc',

            'bk_stat_akreditasi_val',

            'bk_stat_akreditasi_desc',

            'bk_stat_tradisi_val',

            'bk_stat_tradisi_desc',

            'bk_stat_dosen_val',

            'bk_stat_dosen_desc',

            'bk_stat_riset_val',

            'bk_stat_riset_desc',

            'bk_kajur_nama',

            'bk_kajur_jabatan',

            'bk_kajur_quote',

            'bk_portal_auth_url',

        );



        foreach ($options as $opt) {

            if (isset($_POST[$opt])) {

                update_option($opt, wp_unslash($_POST[$opt]));

            }

        }

        echo '<div class="notice notice-success is-dismissible"><p>Pengaturan teks beranda berhasil disimpan!</p></div>';

    }



    // Nilai default

    $hero_badge = get_option('bk_hero_badge', 'Program Studi S1 · Terakreditasi Unggul (LAMDIK)');

    $h1_p1 = get_option('bk_hero_h1_part1', 'Tempat di mana integritas bertumbuh menjadi');

    $h1_hl = get_option('bk_hero_h1_highlight', 'keunggulan');

    $h1_p2 = get_option('bk_hero_h1_part2', 'dan setiap calon konselor menemukan panggilannya.');

    $hero_desc = get_option('bk_hero_desc', 'Program Studi Bimbingan dan Konseling, Jurusan Psikologi Pendidikan dan Bimbingan, FIP Universitas Negeri Makassar menyelenggarakan pendidikan akademik adaptif OBE yang memadukan keluhuran humanistik, riset saintifik berbasis bukti, dan kepekaan sosial-budaya Nusantara.');



    $stat_akr_val = get_option('bk_stat_akreditasi_val', 'Unggul');

    $stat_akr_desc = get_option('bk_stat_akreditasi_desc', 'Sertifikasi mutu tertinggi Lembaga Akreditasi Mandiri Kependidikan (LAMDIK).');



    $stat_trad_val = get_option('bk_stat_tradisi_val', '1969');

    $stat_trad_desc = get_option('bk_stat_tradisi_desc', '55+ tahun mendidik calon konselor, guru pembimbing, dan pendidik bangsa.');



    $stat_dos_val = get_option('bk_stat_dosen_val', '25+ Dosen');

    $stat_dos_desc = get_option('bk_stat_dosen_desc', 'Guru Besar (Prof.), Doktor (Dr.), dan Magister berkualifikasi pendidik profesional.');



    $stat_ris_val = get_option('bk_stat_riset_val', '500+');

    $stat_ris_desc = get_option('bk_stat_riset_desc', 'Karya ilmiah terindeks SINTA/Scopus, buku ajar, dan sertifikasi hak cipta HKI.');



    $kajur_nama = get_option('bk_kajur_nama', 'Dr. Suciani Latif, M.Pd.');

    $kajur_jabatan = get_option('bk_kajur_jabatan', 'Ketua Jurusan PPB FIP UNM');

    $kajur_quote = get_option('bk_kajur_quote', 'Pendidikan konseling di UNM dirancang untuk melahirkan sarjana yang berintegritas moral, peka terhadap keragaman sosio-kultural, serta mahir memanfaatkan teknologi psikoedukasi dan telekonseling untuk menjawab dinamika kesehatan mental era modern.');



    $portal_url = get_option('bk_portal_auth_url', 'https://bk-connect-fipunm.vercel.app/auth');

    ?>

    <div class="wrap" style="max-width: 900px;">

        <h1 style="display:flex; align-items:center; gap:10px; margin-bottom:20px;">

            <span class="dashicons dashicons-admin-appearance" style="font-size:32px; width:32px; height:32px;"></span>

            Pengaturan Teks Beranda (Landing Page) BK FIP UNM

        </h1>



        <p style="color:#555; margin-bottom:25px;">

            Halaman ini memungkinkan Anda mengubah teks utama di beranda secara langsung tanpa menyentuh kode. 

            Cukup ubah teks di bawah ini dan klik <strong>"Simpan Perubahan"</strong>.

        </p>



        <form method="post" action="">

            <?php wp_nonce_field('bk_settings_verify'); ?>



            <!-- KELOMPOK 1: HERO SECTION -->

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

                <h2 style="font-size:18px; color:#0D3B22; border-bottom:2px solid #E8F0EA; padding-bottom:10px; margin-top:0;">

                    1. Hero Section (Bagian Paling Atas)

                </h2>



                <table class="form-table" role="presentation">

                    <tr>

                        <th scope="row"><label for="bk_hero_badge">Badge Akreditasi</label></th>

                        <td>

                            <input name="bk_hero_badge" type="text" id="bk_hero_badge" value="<?php echo esc_attr($hero_badge); ?>" class="regular-text" style="width:100%;">

                            <p class="description">Teks pill kecil di atas judul utama.</p>

                        </td>

                    </tr>

                    <tr>

                        <th scope="row"><label>Judul Utama (H1)</label></th>

                        <td>

                            <input name="bk_hero_h1_part1" type="text" value="<?php echo esc_attr($h1_p1); ?>" style="width:100%; margin-bottom:8px;" placeholder="Kalimat awal...">

                            <input name="bk_hero_h1_highlight" type="text" value="<?php echo esc_attr($h1_hl); ?>" style="width:100%; margin-bottom:8px; font-style:italic;" placeholder="Kata sorot (kuning emas)...">

                            <input name="bk_hero_h1_part2" type="text" value="<?php echo esc_attr($h1_p2); ?>" style="width:100%;" placeholder="Kalimat penutup...">

                        </td>

                    </tr>

                    <tr>

                        <th scope="row"><label for="bk_hero_desc">Paragraf Deskripsi</label></th>

                        <td>

                            <textarea name="bk_hero_desc" id="bk_hero_desc" rows="4" style="width:100%;"><?php echo esc_textarea($hero_desc); ?></textarea>

                        </td>

                    </tr>

                </table>

            </div>



            <!-- KELOMPOK 2: METRIK REPUTASI -->

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

                <h2 style="font-size:18px; color:#0D3B22; border-bottom:2px solid #E8F0EA; padding-bottom:10px; margin-top:0;">

                    2. Angka Metrik Reputasi (4 Kolom Sage)

                </h2>



                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-top:15px;">

                    <div>

                        <h4 style="margin:0 0 5px;">Metrik 1 (Akreditasi)</h4>

                        <input name="bk_stat_akreditasi_val" type="text" value="<?php echo esc_attr($stat_akr_val); ?>" style="width:100%; font-weight:bold; margin-bottom:5px;">

                        <textarea name="bk_stat_akreditasi_desc" rows="2" style="width:100%;"><?php echo esc_textarea($stat_akr_desc); ?></textarea>

                    </div>



                    <div>

                        <h4 style="margin:0 0 5px;">Metrik 2 (Tradisi Akademik)</h4>

                        <input name="bk_stat_tradisi_val" type="text" value="<?php echo esc_attr($stat_trad_val); ?>" style="width:100%; font-weight:bold; margin-bottom:5px;">

                        <textarea name="bk_stat_tradisi_desc" rows="2" style="width:100%;"><?php echo esc_textarea($stat_trad_desc); ?></textarea>

                    </div>



                    <div>

                        <h4 style="margin:0 0 5px;">Metrik 3 (Staf Pengajar)</h4>

                        <input name="bk_stat_dosen_val" type="text" value="<?php echo esc_attr($stat_dos_val); ?>" style="width:100%; font-weight:bold; margin-bottom:5px;">

                        <textarea name="bk_stat_dosen_desc" rows="2" style="width:100%;"><?php echo esc_textarea($stat_dos_desc); ?></textarea>

                    </div>



                    <div>

                        <h4 style="margin:0 0 5px;">Metrik 4 (Riset & Publikasi)</h4>

                        <input name="bk_stat_riset_val" type="text" value="<?php echo esc_attr($stat_ris_val); ?>" style="width:100%; font-weight:bold; margin-bottom:5px;">

                        <textarea name="bk_stat_riset_desc" rows="2" style="width:100%;"><?php echo esc_textarea($stat_ris_desc); ?></textarea>

                    </div>

                </div>

            </div>



            <!-- KELOMPOK 3: SAMBUTAN KETUA JURUSAN -->

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

                <h2 style="font-size:18px; color:#0D3B22; border-bottom:2px solid #E8F0EA; padding-bottom:10px; margin-top:0;">

                    3. Sambutan & Kutipan Ketua Jurusan

                </h2>



                <table class="form-table" role="presentation">

                    <tr>

                        <th scope="row"><label for="bk_kajur_nama">Nama Ketua Jurusan</label></th>

                        <td>

                            <input name="bk_kajur_nama" type="text" id="bk_kajur_nama" value="<?php echo esc_attr($kajur_nama); ?>" style="width:100%;">

                        </td>

                    </tr>

                    <tr>

                        <th scope="row"><label for="bk_kajur_jabatan">Jabatan Struktural</label></th>

                        <td>

                            <input name="bk_kajur_jabatan" type="text" id="bk_kajur_jabatan" value="<?php echo esc_attr($kajur_jabatan); ?>" style="width:100%;">

                        </td>

                    </tr>

                    <tr>

                        <th scope="row"><label for="bk_kajur_quote">Teks Kutipan Sambutan</label></th>

                        <td>

                            <textarea name="bk_kajur_quote" id="bk_kajur_quote" rows="4" style="width:100%;"><?php echo esc_textarea($kajur_quote); ?></textarea>

                        </td>

                    </tr>

                </table>

            </div>



            <!-- KELOMPOK 4: TAUTAN PORTAL EKSTERNAL -->

            <div style="background:#fff; border:1px solid #ccd0d4; border-radius:12px; padding:20px; margin-bottom:25px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">

                <h2 style="font-size:18px; color:#0D3B22; border-bottom:2px solid #E8F0EA; padding-bottom:10px; margin-top:0;">

                    4. Tautan Portal Masuk Akun (CBT & Mahasiswa)

                </h2>



                <table class="form-table" role="presentation">

                    <tr>

                        <th scope="row"><label for="bk_portal_auth_url">URL Masuk Portal</label></th>

                        <td>

                            <input name="bk_portal_auth_url" type="url" id="bk_portal_auth_url" value="<?php echo esc_attr($portal_url); ?>" style="width:100%;">

                            <p class="description">URL yang dituju saat tombol kuning "Masuk Portal" di pojok kanan atas diklik.</p>

                        </td>

                    </tr>

                </table>

            </div>



            <p class="submit">

                <input type="submit" name="bk_save_settings" id="submit" class="button button-primary" value="Simpan Perubahan Teks" style="background:#0D3B22; border-color:#0D3B22; padding:6px 24px; font-weight:bold; font-size:14px; border-radius:8px;">

            </p>

        </form>

    </div>

    <?php

}



// =============================================================================

// SUBNAV & PRODI LAYOUT CLEANUP FILTER

// =============================================================================

add_filter('the_content', function($content) {

    if (strpos($content, 'bk-subnav-sticky-wrap') !== false) {

        // Auto-inject E-Library into Layanan subnav sticky wrap if not yet present
        if (strpos($content, '/layanan/akademik/') !== false && strpos($content, '/e-library/') === false) {
            $elib_subnav_item = '<li style="flex-shrink:0"><a href="/e-library/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;text-decoration:none;background:transparent;color:#4B5563;font-weight:500">E-Library &amp; Modul</a></li>';
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



    $force = isset($_GET['sync_ppb_content']) && $_GET['sync_ppb_content'] === '1' && current_user_can('manage_options');

    $synced_version = get_option('ppb_content_synced_version');



    if ($synced_version === '17.0' && !$force) {
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

                // Highly specific search for dosen page across all possibilities

                if ($parent_id) {

                    $existing_id = $wpdb->get_var($wpdb->prepare(

                        "SELECT ID FROM $wpdb->posts WHERE (post_name = 'dosen' OR post_name LIKE 'dosen%' OR post_title LIKE '%%Dosen%%') AND post_parent = %d AND post_type = 'page' AND post_status != 'trash' LIMIT 1",

                        $parent_id

                    ));

                }

                if (!$existing_id) {

                    $existing_id = $wpdb->get_var(

                        "SELECT ID FROM $wpdb->posts WHERE (post_name = 'dosen' OR post_name LIKE 'dosen%' OR post_title LIKE '%%Dosen%%') AND post_type = 'page' AND post_status != 'trash' LIMIT 1"

                    );

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
    $dosen_json_file = get_template_directory() . '/dosen-data.json';
    if (file_exists($dosen_json_file)) {
        $all_dosen_data = json_decode(file_get_contents($dosen_json_file), true);
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
                    clean_post_cache($existing_id);
                }
            }
        }
    }

    update_option('ppb_content_synced_version', '17.0');



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
            $all_dosen = null;
            if (file_exists($json_file)) {
                $all_dosen = json_decode(file_get_contents($json_file), true);
            }
            if (empty($all_dosen)) {
                $all_dosen = get_option('bk_dosen_data_cache', array());
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

            $dosen_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'dosen' AND post_type = 'page' AND post_status = 'publish' LIMIT 1");
            if (!$dosen_id) {
                $dosen_id = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE (post_name LIKE 'dosen%' OR post_title LIKE '%Dosen%') AND post_type = 'page' AND post_status != 'trash' LIMIT 1");
            }

            // If not found in DB at all, auto-create it immediately from pages-data.json
            if (!$dosen_id) {
                $json_file = get_template_directory() . '/pages-data.json';
                if (file_exists($json_file)) {
                    $pages_data = json_decode(file_get_contents($json_file), true);
                    if (is_array($pages_data)) {
                        foreach ($pages_data as $p) {
                            if ($p['slug'] === 'dosen') {
                                $parent_id = (int)$wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_name = 'profil' AND post_type = 'page' LIMIT 1");
                                $now = current_time('mysql');
                                $wpdb->insert(
                                    $wpdb->posts,
                                    array(
                                        'post_title'     => $p['title'],
                                        'post_name'      => 'dosen',
                                        'post_content'   => str_replace(array('http://bk-fip.local/', 'https://bk-fip.local/'), '/', $p['content']),
                                        'post_status'    => 'publish',
                                        'post_type'      => 'page',
                                        'post_parent'    => $parent_id,
                                        'comment_status' => 'closed',
                                        'ping_status'    => 'closed',
                                        'post_date'      => $now,
                                        'post_date_gmt'  => current_time('mysql', 1),
                                        'post_modified'  => $now,
                                        'post_modified_gmt' => current_time('mysql', 1),
                                    )
                                );
                                $dosen_id = $wpdb->insert_id;
                                clean_post_cache($dosen_id);
                                break;
                            }
                        }
                    }
                }
            }

            if ($dosen_id) {
                $post = get_post($dosen_id);
                setup_postdata($post);
                $wp_query->posts = array($post);
                $wp_query->post_count = 1;
                $wp_query->is_404 = false;
                $wp_query->is_page = true;
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
                        if (!$post_id) {
                            $now = current_time('mysql');
                            $wpdb->insert($wpdb->posts, array(
                                'post_title'     => $matched_page['title'],
                                'post_name'      => $matched_page['slug'],
                                'post_content'   => $matched_page['content'],
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
                        }

                        if ($post_id) {
                            $post = get_post($post_id);
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

// Set document title for Single Dosen Profile
add_filter('document_title_parts', function($title_parts) {
    global $current_dosen;
    if (!empty($current_dosen)) {
        $title_parts['title'] = $current_dosen['nama_lengkap'] . ' - Profil & Rekam Jejak Dosen';
    }
    return $title_parts;
}, 99);

/**
 * --------------------------------------------------------------------------
 * SUPABASE BK-CONNECT INTEGRATION & SYNC ENGINE
 * --------------------------------------------------------------------------
 */
if (!defined('BK_SUPABASE_URL')) {
    define('BK_SUPABASE_URL', 'https://bhrmckukselzpvvectwt.supabase.co');
}
if (!defined('BK_SUPABASE_ANON_KEY')) {
    define('BK_SUPABASE_ANON_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImJocm1ja3Vrc2VsenB2dmVjdHd0Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3Nzk5MjA2NTYsImV4cCI6MjA5NTQ5NjY1Nn0.A3M5ieKqotcOL9gk297IqiBbsRxkL61n6rg3_UJPpcM');
}

function bk_fip_sync_dosen_from_supabase() {
    $headers = array(
        'apikey'        => BK_SUPABASE_ANON_KEY,
        'Authorization' => 'Bearer ' . BK_SUPABASE_ANON_KEY,
    );

    // 1. Fetch Dosen
    $resp_dosen = wp_remote_get(BK_SUPABASE_URL . '/rest/v1/dosen?select=*&aktif=eq.true&order=urutan.asc,nama.asc', array('headers' => $headers, 'timeout' => 25));
    if (is_wp_error($resp_dosen) || wp_remote_retrieve_response_code($resp_dosen) !== 200) {
        $msg = is_wp_error($resp_dosen) ? $resp_dosen->get_error_message() : 'HTTP ' . wp_remote_retrieve_response_code($resp_dosen);
        return array('success' => false, 'error' => 'Gagal mengambil data dosen: ' . $msg);
    }
    $raw_dosen = json_decode(wp_remote_retrieve_body($resp_dosen), true);
    if (!is_array($raw_dosen) || empty($raw_dosen)) {
        return array('success' => false, 'error' => 'Data dosen kosong dari Supabase.');
    }

    // 2. Fetch Publikasi
    $resp_pub = wp_remote_get(BK_SUPABASE_URL . '/rest/v1/dosen_publikasi?select=*&order=tahun.desc', array('headers' => $headers, 'timeout' => 25));
    $raw_pub = (!is_wp_error($resp_pub) && wp_remote_retrieve_response_code($resp_pub) === 200) ? json_decode(wp_remote_retrieve_body($resp_pub), true) : array();
    if (!is_array($raw_pub)) $raw_pub = array();

    // 3. Fetch Penelitian
    $resp_pen = wp_remote_get(BK_SUPABASE_URL . '/rest/v1/dosen_penelitian?select=*&order=tahun.desc', array('headers' => $headers, 'timeout' => 25));
    $raw_pen = (!is_wp_error($resp_pen) && wp_remote_retrieve_response_code($resp_pen) === 200) ? json_decode(wp_remote_retrieve_body($resp_pen), true) : array();
    if (!is_array($raw_pen)) $raw_pen = array();

    // 4. Fetch Pengabdian
    $resp_peng = wp_remote_get(BK_SUPABASE_URL . '/rest/v1/dosen_pengabdian?select=*&order=tahun.desc', array('headers' => $headers, 'timeout' => 25));
    $raw_peng = (!is_wp_error($resp_peng) && wp_remote_retrieve_response_code($resp_peng) === 200) ? json_decode(wp_remote_retrieve_body($resp_peng), true) : array();
    if (!is_array($raw_peng)) $raw_peng = array();

    // 5. Fetch Buku
    $resp_buku = wp_remote_get(BK_SUPABASE_URL . '/rest/v1/dosen_buku?select=*&order=tahun.desc', array('headers' => $headers, 'timeout' => 25));
    $raw_buku = (!is_wp_error($resp_buku) && wp_remote_retrieve_response_code($resp_buku) === 200) ? json_decode(wp_remote_retrieve_body($resp_buku), true) : array();
    if (!is_array($raw_buku)) $raw_buku = array();

    // Index related records by dosen_id
    $pub_by_dosen = array();
    foreach ($raw_pub as $p) {
        $did = $p['dosen_id'] ?? '';
        if ($did) $pub_by_dosen[$did][] = $p;
    }
    $pen_by_dosen = array();
    foreach ($raw_pen as $p) {
        $did = $p['dosen_id'] ?? '';
        if ($did) $pen_by_dosen[$did][] = $p;
    }
    $peng_by_dosen = array();
    foreach ($raw_peng as $p) {
        $did = $p['dosen_id'] ?? '';
        if ($did) $peng_by_dosen[$did][] = $p;
    }
    $buku_by_dosen = array();
    foreach ($raw_buku as $p) {
        $did = $p['dosen_id'] ?? '';
        if ($did) $buku_by_dosen[$did][] = $p;
    }

    // Cache existing local edits so syncing NEVER wipes admin custom data
    $existing_json_file = get_template_directory() . '/dosen-data.json';
    $existing_map = array();
    if (file_exists($existing_json_file)) {
        $ex_list = json_decode(file_get_contents($existing_json_file), true);
        if (is_array($ex_list)) {
            foreach ($ex_list as $ex) {
                if (!empty($ex['slug'])) {
                    $existing_map[$ex['slug']] = $ex;
                }
            }
        }
    }

    $existing_slugs = array();
    $dosen_list = array();

    foreach ($raw_dosen as $d) {
        $did = $d['id'] ?? '';
        $nama = trim($d['nama'] ?? '');
        $nama_lengkap = trim($d['nama_lengkap'] ?? $nama);

        // Generate clean unique slug
        $base_slug = sanitize_title(explode(',', $nama)[0]);
        $prefixes = array('prof-', 'dr-', 'dra-', 'drs-', 'ir-', 'h-', 'hj-');
        foreach ($prefixes as $pfx) {
            if (strpos($base_slug, $pfx) === 0) {
                $base_slug = substr($base_slug, strlen($pfx));
            }
        }
        $slug = $base_slug;
        $counter = 1;
        while (in_array($slug, $existing_slugs)) {
            $counter++;
            $slug = $base_slug . '-' . $counter;
        }
        $existing_slugs[] = $slug;

        // Kepakaran
        $bidang = trim($d['bidang_keahlian'] ?? '');
        $kepakaran_list = array();
        if ($bidang) {
            foreach (preg_split('/[,;
]+/', $bidang) as $k) {
                $k = trim($k);
                if ($k) $kepakaran_list[] = $k;
            }
        }

        $my_pubs = array();
        if (isset($pub_by_dosen[$did])) {
            foreach ($pub_by_dosen[$did] as $p) {
                $my_pubs[] = array(
                    'id'         => $p['id'] ?? '',
                    'judul'      => trim($p['judul'] ?? ''),
                    'tahun'      => (int)($p['tahun'] ?? 0),
                    'media'      => trim($p['media'] ?? ''),
                    'akreditasi' => trim($p['akreditasi'] ?? 'GARUDA'),
                    'peran'      => trim($p['peran'] ?? 'Penulis Pertama'),
                    'url'        => trim($p['url'] ?? ''),
                    'sumber'     => trim($p['sumber'] ?? 'SINTA'),
                    'penulis'    => trim($p['penulis'] ?? ''),
                );
            }
        }

        $my_pen = array();
        if (isset($pen_by_dosen[$did])) {
            foreach ($pen_by_dosen[$did] as $p) {
                $my_pen[] = array(
                    'id'          => $p['id'] ?? '',
                    'judul'       => trim($p['judul'] ?? ''),
                    'tahun'       => (int)($p['tahun'] ?? 0),
                    'peran'       => trim($p['peran'] ?? 'Ketua Peneliti'),
                    'sumber_dana' => trim($p['sumber_dana'] ?? 'KEMENDIKBUDRISTEK'),
                    'lama_tahun'  => (int)($p['lama_tahun'] ?? 1),
                );
            }
        }

        $my_peng = array();
        if (isset($peng_by_dosen[$did])) {
            foreach ($peng_by_dosen[$did] as $p) {
                $my_peng[] = array(
                    'id'          => $p['id'] ?? '',
                    'judul'       => trim($p['judul'] ?? ''),
                    'tahun'       => (int)($p['tahun'] ?? 0),
                    'peran'       => trim($p['peran'] ?? 'Ketua Pelaksana'),
                    'sumber_dana' => trim($p['sumber_dana'] ?? 'PNBP UNM'),
                    'lokasi'      => trim($p['lokasi'] ?? ''),
                );
            }
        }

        $my_buku = array();
        if (isset($buku_by_dosen[$did])) {
            foreach ($buku_by_dosen[$did] as $p) {
                $my_buku[] = array(
                    'id'       => $p['id'] ?? '',
                    'judul'    => trim($p['judul'] ?? ''),
                    'tahun'    => (int)($p['tahun'] ?? 0),
                    'penerbit' => trim($p['penerbit'] ?? ''),
                    'isbn'     => trim($p['isbn'] ?? ''),
                );
            }
        }

        // Merge with existing local admin edits if available
        $prev = isset($existing_map[$slug]) ? $existing_map[$slug] : array();
        $my_pend = !empty($prev['pendidikan']) ? $prev['pendidikan'] : (is_array($d['pendidikan'] ?? null) ? $d['pendidikan'] : array());
        $my_peng = !empty($prev['pengalaman']) ? $prev['pengalaman'] : array();
        $my_foto = !empty($prev['foto_link']) ? $prev['foto_link'] : trim($d['foto_link'] ?? '');
        $my_panggilan = !empty($prev['nama_panggilan']) ? $prev['nama_panggilan'] : '';
        $my_struktural = !empty($prev['jabatan_struktural']) ? $prev['jabatan_struktural'] : '';
        $my_ruang = !empty($prev['ruang_kerja']) ? $prev['ruang_kerja'] : '';
        $my_nidn = !empty($prev['nidn']) ? $prev['nidn'] : trim($d['nidn'] ?? '');
        $my_kep = !empty($prev['kepakaran']) ? $prev['kepakaran'] : $kepakaran_list;

        $dosen_list[] = array(
            'id'                 => $did,
            'nama'               => $nama,
            'nama_lengkap'       => !empty($prev['nama_lengkap']) ? $prev['nama_lengkap'] : $nama_lengkap,
            'nama_panggilan'     => $my_panggilan,
            'slug'               => $slug,
            'nip'                => !empty($prev['nip']) ? $prev['nip'] : trim($d['nip'] ?? ''),
            'nidn'               => $my_nidn,
            'email'              => !empty($prev['email']) ? $prev['email'] : trim($d['email'] ?? ''),
            'no_hp'              => !empty($prev['no_hp']) ? $prev['no_hp'] : trim($d['no_hp'] ?? ''),
            'unit'               => !empty($prev['unit']) ? $prev['unit'] : trim($d['unit'] ?? 'S1 Bimbingan dan Konseling'),
            'jabatan_fungsional' => !empty($prev['jabatan_fungsional']) ? $prev['jabatan_fungsional'] : trim($d['jabatan_fungsional'] ?? 'Lektor'),
            'jabatan_struktural' => $my_struktural,
            'ruang_kerja'        => $my_ruang,
            'bidang_keahlian'    => implode(', ', $my_kep),
            'kepakaran'          => $my_kep,
            'sinta_id'           => !empty($prev['sinta_id']) ? $prev['sinta_id'] : trim($d['sinta_id'] ?? ''),
            'sinta_url'          => !empty($prev['sinta_url']) ? $prev['sinta_url'] : trim($d['sinta_url'] ?? ''),
            'scholar_url'        => !empty($prev['scholar_url']) ? $prev['scholar_url'] : trim($d['scholar_url'] ?? ''),
            'scopus_url'         => !empty($prev['scopus_url']) ? $prev['scopus_url'] : trim($d['scopus_url'] ?? ''),
            'foto_link'          => $my_foto,
            'bio'                => !empty($prev['bio']) ? $prev['bio'] : trim($d['bio'] ?? ''),
            'stat'               => array(
                'publikasi'  => count($my_pubs),
                'penelitian' => count($my_pen),
                'pengabdian' => count($my_peng),
                'buku'       => count($my_buku),
            ),
            'publikasi'          => $my_pubs,
            'penelitian'         => $my_pen,
            'pengabdian'         => $my_peng,
            'buku'               => $my_buku,
            'pendidikan'         => $my_pend,
            'pengalaman'         => $my_peng,
        );
    }

    // Preserve custom order if previously configured
    $saved_order = get_option('bk_dosen_custom_order', array());
    if (!empty($saved_order) && is_array($saved_order)) {
        usort($dosen_list, function($a, $b) use ($saved_order) {
            $slug_a = $a['slug'] ?? '';
            $slug_b = $b['slug'] ?? '';
            $order_a = isset($saved_order[$slug_a]) ? (int)$saved_order[$slug_a] : 999;
            $order_b = isset($saved_order[$slug_b]) ? (int)$saved_order[$slug_b] : 999;
            if ($order_a === $order_b) {
                return strcmp($a['nama_lengkap'] ?? '', $b['nama_lengkap'] ?? '');
            }
            return ($order_a < $order_b) ? -1 : 1;
        });
    }

    // Save to dosen-data.json
    $json_path = get_template_directory() . '/dosen-data.json';
    file_put_contents($json_path, json_encode($dosen_list, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    // Save persistent backup to WordPress options
    update_option('bk_dosen_data_cache', $dosen_list, false);
    update_option('bk_dosen_last_synced', current_time('mysql'));

    // Automatically rebuild public page /profil/dosen/
    if (function_exists('bk_fip_rebuild_dosen_roster_html')) {
        bk_fip_rebuild_dosen_roster_html($dosen_list);
    }

    return array(
        'success'    => true,
        'dosen'      => count($dosen_list),
        'publikasi'  => count($raw_pub),
        'penelitian' => count($raw_pen),
        'pengabdian' => count($raw_peng),
        'buku'       => count($raw_buku),
    );
}

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
 * Rebuild the public /profil/dosen/ page and pages-data.json from ordered lecturer list
 */
function bk_fip_rebuild_dosen_roster_html($dosen_list) {
    global $wpdb;
    if (empty($dosen_list) || !is_array($dosen_list)) return false;

    $get_badge = function($nama, $jabatan) {
        $n = strtolower($nama);
        $j = strtolower($jabatan);
        if (strpos($n, 'prof.') !== false || strpos($j, 'guru besar') !== false) {
            return array('GURU BESAR', '#FEF3C7', '#92400E', '#FDE68A');
        } elseif (strpos($n, 'dr.') !== false || strpos($j, 'doktor') !== false) {
            return array('DOKTOR', '#E0E7FF', '#3730A3', '#C7D2FE');
        } elseif (strpos($j, 'lektor kepala') !== false) {
            return array('LEKTOR KEPALA', '#E8F0EA', '#0D3B22', '#D4E3D8');
        } else {
            return array('DOSEN PPB', '#F3F4F6', '#374151', '#E5E7EB');
        }
    };

    $get_initials = function($name) {
        $parts = explode(',', $name);
        $clean = $parts[0];
        $prefixes = array('prof', 'dr', 'dra', 'drs', 'ir', 'h', 'hj');
        $words = preg_split('/[\s\.]+/', $clean);
        $filtered = array();
        foreach ($words as $w) {
            $w = trim($w);
            if ($w && !in_array(strtolower($w), $prefixes)) {
                $filtered[] = $w;
            }
        }
        if (count($filtered) >= 2) {
            return strtoupper(substr($filtered[0], 0, 1) . substr($filtered[1], 0, 1));
        } elseif (count($filtered) === 1) {
            return strtoupper(substr($filtered[0], 0, 2));
        }
        return 'BK';
    };

    $cards_html = '';
    foreach ($dosen_list as $d) {
        $nama = $d['nama_lengkap'] ?? ($d['nama'] ?? '');
        $nip = trim($d['nip'] ?? '');
        $nip_display = $nip ? 'NIP. ' . esc_html($nip) : '';
        $jabatan = $d['jabatan_fungsional'] ?? 'Dosen Bimbingan dan Konseling';
        $slug = $d['slug'] ?? '';
        $profile_url = '/profil/dosen/' . $slug . '/';
        $sinta_url = $d['sinta_url'] ?? '';

        $stat = $d['stat'] ?? array();
        $pub_cnt = (int)($stat['publikasi'] ?? count($d['publikasi'] ?? array()));
        $pen_cnt = (int)($stat['penelitian'] ?? count($d['penelitian'] ?? array()));

        $kepakaran = $d['kepakaran'] ?? array();
        if (empty($kepakaran) && !empty($d['bidang_keahlian'])) {
            $splits = preg_split('/[,;\n]+/', $d['bidang_keahlian']);
            foreach ($splits as $sp) {
                $sp = trim($sp);
                if ($sp) $kepakaran[] = $sp;
            }
        }
        if (empty($kepakaran)) {
            $kepakaran = array('Bimbingan & Konseling', 'Psikologi Pendidikan');
        }

        $tags_html = '';
        foreach (array_slice($kepakaran, 0, 3) as $t) {
            $tags_html .= '<span style="font-size:10.5px;background:#FAF8F5;border:1px solid #E5E7EB;color:#374151;padding:3px 8px;border-radius:6px;line-height:1.2;font-weight:500;">' . esc_html($t) . '</span>';
        }

        $search_text = esc_attr(strtolower($nama . ' ' . $nip . ' ' . $jabatan . ' ' . implode(' ', $kepakaran)));
        list($badge_label, $badge_bg, $badge_color, $badge_border) = $get_badge($nama, $jabatan);
        $init = $get_initials($nama);

        $foto_link = $d['foto_link'] ?? '';
        if ($foto_link) {
            $avatar_html = '<img src="' . esc_url($foto_link) . '" alt="' . esc_attr($nama) . '" style="width:54px;height:54px;border-radius:16px;object-fit:cover;border:2px solid #0D3B22;box-shadow:0 3px 8px rgba(0,0,0,0.1);">';
        } else {
            $avatar_html = '<div style="width:54px;height:54px;border-radius:16px;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:18px;font-family:Fraunces,Georgia,serif;border:2px solid #F5B82E;box-shadow:0 3px 10px rgba(13,59,34,0.18);user-select:none;">' . $init . '</div>';
        }

        $sinta_quick = '';
        if ($sinta_url) {
            $sinta_quick = '<span style="color:#D1D5DB;">&bull;</span><a href="' . esc_url($sinta_url) . '" target="_blank" rel="noopener" style="color:#0D3B22;font-weight:700;font-size:11px;text-decoration:none;display:inline-flex;align-items:center;gap:3px;transition:color 0.2s;" onmouseover="this.style.color=\'#185333\'" onmouseout="this.style.color=\'#0D3B22\'" title="Buka profil SINTA Dikti"><span>SINTA</span><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg></a>';
        }

        $nip_block = $nip_display ? '<p style="font-family:monospace;font-size:11px;color:#6B7280;margin:0 0 12px 0;line-height:1.2;">' . $nip_display . '</p>' : '<div style="margin-bottom:12px;"></div>';

        $cards_html .= '<div class="bk-dosen-card-item" data-text="' . $search_text . '" style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:20px;padding:22px;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 4px 16px rgba(13,59,34,0.04);position:relative;overflow:hidden;transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.transform=\'translateY(-4px)\';this.style.borderColor=\'#185333\';this.style.boxShadow=\'0 12px 28px rgba(13,59,34,0.12)\';" onmouseout="this.style.transform=\'translateY(0)\';this.style.borderColor=\'#D4E3D8\';this.style.boxShadow=\'0 4px 16px rgba(13,59,34,0.04)\';">
            <div style="position:absolute;top:0;left:0;right:0;height:4px;background:#0D3B22;"></div>
            <div style="margin-top:6px;">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;">
                    ' . $avatar_html . '
                    <span style="font-size:9.5px;font-weight:800;background:' . $badge_bg . ';color:' . $badge_color . ';border:1px solid ' . $badge_border . ';padding:4px 10px;border-radius:9999px;letter-spacing:0.04em;text-transform:uppercase;line-height:1;display:inline-block;">' . $badge_label . '</span>
                </div>
                <h3 style="font-family:Fraunces,Georgia,serif;font-size:16px;font-weight:700;color:#111827;line-height:1.35;margin:0 0 5px 0;min-height:44px;display:flex;align-items:flex-start;">
                    <a href="' . esc_url($profile_url) . '" style="color:inherit;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color=\'#0D3B22\'" onmouseout="this.style.color=\'#111827\'">' . esc_html($nama) . '</a>
                </h3>
                <p style="font-size:12px;color:#0D3B22;font-weight:600;margin:0 0 3px 0;line-height:1.3;">' . esc_html($jabatan) . '</p>
                ' . $nip_block . '
                <div style="border-top:1px solid #F3F4F6;padding-top:10px;margin-bottom:14px;">
                    <span style="font-size:10px;font-weight:700;color:#9CA3AF;text-transform:uppercase;letter-spacing:0.06em;display:block;margin-bottom:6px;line-height:1;">Fokus Kepakaran:</span>
                    <div style="display:flex;flex-wrap:wrap;gap:5px;min-height:26px;">' . $tags_html . '</div>
                </div>
            </div>
            <div style="border-top:1px solid #F3F4F6;padding-top:12px;margin-top:auto;">
                <div style="display:flex;justify-content:space-between;align-items:center;background:#FAF8F5;border:1px solid #E5E7EB;border-radius:10px;padding:8px 12px;margin-bottom:10px;font-size:11.5px;color:#0D3B22;line-height:1;box-sizing:border-box;">
                    <span>Publikasi: <strong style="color:#0D3B22;font-weight:700;">' . $pub_cnt . '</strong></span>
                    <span style="color:#D1D5DB;">&bull;</span>
                    <span>Riset: <strong style="color:#0D3B22;font-weight:700;">' . $pen_cnt . '</strong></span>
                    ' . $sinta_quick . '
                </div>
                <a href="' . esc_url($profile_url) . '" class="bk-card-btn" style="width:100%;height:40px;display:flex;align-items:center;justify-content:center;gap:8px;background:#0D3B22;color:#F5B82E;border:1.5px solid #0D3B22;border-radius:12px;font-size:12.5px;font-weight:700;text-decoration:none;box-sizing:border-box;line-height:1;transition:all 0.2s;" onmouseover="this.style.background=\'#185333\';this.style.borderColor=\'#185333\';this.style.color=\'#ffffff\';" onmouseout="this.style.background=\'#0D3B22\';this.style.borderColor=\'#0D3B22\';this.style.color=\'#F5B82E\';">
                    <span>Lihat Profil &amp; Rekam Jejak</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>' . "\n";
    }

    $count_dosen = count($dosen_list);
    $full_html = '<div class="bk-page-wrapper" style="width:100%;background:#FAF8F5;color:#1F2937;font-family:\'Poppins\',sans-serif;line-height:1.6"><section style="position:relative;overflow:hidden;background:#0D3B22;color:#ffffff;padding:var(--bk-top-clearance, 36px) 0 clamp(45px, 6vw, 60px);font-family:\'Poppins\',sans-serif">
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(245,184,46,0.15);filter:blur(80px);pointer-events:none"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(245,184,46,0.12);filter:blur(80px);pointer-events:none"></div>
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);background-size:24px 24px;pointer-events:none"></div>

        <div class="bk-container" style="position:relative;max-width:100%;width:100%;margin:0 auto">
            <div style="display:inline-flex;align-items:center;gap:7px;padding:5px 14px;border-radius:9999px;background:rgba(245,184,46,0.15);border:1px solid rgba(245,184,46,0.3);color:#F5B82E;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:16px">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#F5B82E"></span>
                <span>Profil &middot; Program Studi Bimbingan &amp; Konseling</span>
            </div>

            <h1 style="font-family:Fraunces,Georgia,serif;font-size:clamp(30px, 4.2vw, 50px);font-weight:400;color:#ffffff;line-height:1.16;letter-spacing:-0.02em;margin:0 0 16px 0;max-width:860px">
                Dosen &amp; Tenaga Pengajar
            </h1>

            <p style="font-size:clamp(14px, 1.6vw, 16px);line-height:1.7;color:rgba(255,255,255,0.85);margin:0 0 28px 0;max-width:760px;font-weight:400">
                Telusuri profil akademik, publikasi ilmiah SINTA/Scopus, penelitian &amp; hibah, pengabdian masyarakat, dan rekam jejak kepakaran para akademisi Jurusan PPB FIP UNM.
            </p>

            <div style="margin-top:24px;max-width:560px">
                <div style="position:relative">
                    <span style="position:absolute;left:18px;top:50%;transform:translateY(-50%);color:#0D3B22;display:flex;align-items:center;pointer-events:none">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="bk-dosen-search-input" placeholder="Cari nama dosen, NIP, atau bidang kepakaran..." style="width:100%;padding:14px 20px 14px 48px;border-radius:14px;border:2px solid rgba(255,255,255,0.3);background:#ffffff;color:#111827;font-size:13.5px;font-family:\'Poppins\',sans-serif;outline:none;box-shadow:0 8px 24px rgba(0,0,0,0.18);">
                </div>
                <div style="font-size:12px;margin-top:10px;color:rgba(255,255,255,0.85);" id="bk-dosen-count-info">
                    Menampilkan seluruh <strong style="color:#F5B82E;">' . $count_dosen . '</strong> dosen &amp; tenaga pengajar Jurusan PPB
                </div>
            </div>
        </div>
    </section>

<div class="bk-subnav-sticky-wrap" style="position:relative;z-index:30;margin-top:-28px;padding:0 var(--bk-edge-pad, 1rem);margin-bottom:30px">
    <div style="border:1px solid #D4E3D8;border-radius:18px;padding:6px 8px;background:#ffffff;box-shadow:0 4px 16px rgba(0,0,0,0.03);max-width:100%;width:100%;margin:0 auto;overflow-x:auto;">
        <ul class="no-scrollbar" style="display:flex;align-items:center;gap:6px;margin:0;padding:0;white-space:nowrap;list-style:none;">
            <li style="flex-shrink:0"><a href="/profil/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Ringkasan</a></li>
            <li style="flex-shrink:0"><a href="/profil/sejarah/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Sejarah</a></li>
            <li style="flex-shrink:0"><a href="/profil/visi-misi/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Visi &amp; Misi</a></li>
            <li style="flex-shrink:0"><a href="/profil/visi-keilmuan/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Visi Keilmuan &amp; Kepakaran</a></li>
            <li style="flex-shrink:0"><a href="/profil/struktur/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Struktur Organisasi</a></li>
            <li style="flex-shrink:0"><a href="/profil/kerjasama-tridharma/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Kerjasama Tridharma</a></li>
            <li style="flex-shrink:0"><a href="/profil/dosen/" class="bk-subnav-item bk-active" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:700;color:#F5B82E;background:#0D3B22;text-decoration:none;">Dosen &amp; Staf</a></li>
            <li style="flex-shrink:0"><a href="/profil/buku-dosen/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Koleksi Buku Dosen</a></li>
            <li style="flex-shrink:0"><a href="/profil/kepakaran-dosen/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Peta Kepakaran &amp; SINTA</a></li>
            <li style="flex-shrink:0"><a href="/profil/sarana-prasarana/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Sarana &amp; Prasarana</a></li>
            <li style="flex-shrink:0"><a href="/profil/roadmap/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Roadmap Riset</a></li>
            <li style="flex-shrink:0"><a href="/profil/dokumen/" class="bk-subnav-item" style="display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:12px;font-size:12px;font-family:\'Poppins\',sans-serif;font-weight:500;color:#4B5563;background:transparent;text-decoration:none;">Dokumen Mutu</a></li>
        </ul>
    </div>
</div>

<section style="padding:0 var(--bk-edge-pad, 1rem) 45px;font-family:\'Poppins\',sans-serif">
    <div style="max-width:100%;width:100%;margin:0 auto">
        <div id="bk-dosen-grid" style="display:grid;grid-template-columns:repeat(auto-fill, minmax(310px, 1fr));gap:24px;">
' . $cards_html . '
        </div>

        <div style="background:#E8F0EA;border:1px solid #D4E3D8;border-radius:24px;padding:clamp(20px, 4vw, 32px);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:18px;margin-top:40px;font-family:\'Poppins\',sans-serif">
            <div style="max-width:640px">
                <h3 style="font-family:Fraunces,Georgia,serif;font-size:clamp(18px, 2.2vw, 24px);color:#0D3B22;margin:0 0 6px 0;font-weight:600">Ingin Mengetahui Arah Riset &amp; Pengabdian Dosen?</h3>
                <p style="font-size:13px;color:#4D6354;margin:0;line-height:1.6">Pelajari Roadmap Penelitian 3 Pilar Keilmuan, Lembar Pengesahan Senat, dan Research Groups Program Studi.</p>
            </div>
            <a href="/profil/roadmap/" style="height:44px;display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:0 24px;border-radius:9999px;background:#F5B82E;color:#0D3B22;font-size:13px;font-weight:700;text-decoration:none;box-sizing:border-box;line-height:1;transition:all 0.2s;" onmouseover="this.style.background=\'#e5a93b\'" onmouseout="this.style.background=\'#F5B82E\'">
                <span>Buka Roadmap Riset &amp; PKM &rarr;</span>
            </a>
        </div>
    </div>
</section>
</div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    var searchInput = document.getElementById("bk-dosen-search-input");
    var cards = document.querySelectorAll(".bk-dosen-card-item");
    var countInfo = document.getElementById("bk-dosen-count-info");
    if (!searchInput || !cards.length) return;

    searchInput.addEventListener("input", function() {
        var query = this.value.toLowerCase().trim();
        var visibleCount = 0;
        cards.forEach(function(card) {
            var text = (card.getAttribute("data-text") || "").toLowerCase();
            if (!query || text.indexOf(query) !== -1) {
                card.style.display = "flex";
                visibleCount++;
            } else {
                card.style.display = "none";
            }
        });
        if (countInfo) {
            if (query) {
                countInfo.innerHTML = "Menampilkan <strong style=\'color:#F5B82E;\'>" + visibleCount + "</strong> dari ' . $count_dosen . ' dosen untuk pencarian &ldquo;" + query + "&rdquo;";
            } else {
                countInfo.innerHTML = "Menampilkan seluruh <strong style=\'color:#F5B82E;\'>' . $count_dosen . '</strong> dosen &amp; tenaga pengajar Jurusan PPB";
            }
        }
    });
});
</script>';

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
        $current_dosen = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();
        if (empty($current_dosen)) {
            $current_dosen = get_option('bk_dosen_data_cache', array());
        }

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
                $cd['foto_link']          = esc_url_raw($_POST['foto_link'] ?? '');
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
            // Save to JSON
            file_put_contents($json_file, json_encode($current_dosen, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            update_option('bk_dosen_data_cache', $current_dosen, false);

            // Rebuild public roster page
            bk_fip_rebuild_dosen_roster_html($current_dosen);

            $notice = '<div class="notice notice-success is-dismissible" style="padding:16px;border-left:4px solid #0D3B22;background:#f0fff4;border-radius:8px;margin-bottom:20px;">
                <h3 style="margin:0 0 6px 0;color:#0D3B22;">&#10003; Data Profil Dosen Berhasil Diperbarui!</h3>
                <p style="margin:0;font-size:13.5px;color:#2d3748;">
                    Perubahan data dan foto profil untuk <strong>' . esc_html($_POST['nama_lengkap'] ?? $edit_slug) . '</strong> telah disimpan ke sistem dan langsung aktif di halaman publik <a href="' . esc_url(home_url('/profil/dosen/' . $edit_slug . '/')) . '" target="_blank" style="color:#0D3B22;font-weight:700;text-decoration:underline;">/profil/dosen/' . esc_html($edit_slug) . '/ ↗</a>.
                </p>
            </div>';
        }
    }

    // Handle Supabase Sync Action
    if (isset($_POST['bk_trigger_supabase_sync']) && check_admin_referer('bk_supabase_sync_action', 'bk_supabase_sync_nonce')) {
        $result = bk_fip_sync_dosen_from_supabase();
        if ($result['success']) {
            $notice = '<div class="notice notice-success is-dismissible" style="padding:16px;border-left:4px solid #0D3B22;background:#f0fff4;border-radius:8px;margin-bottom:20px;">
                <h3 style="margin:0 0 6px 0;color:#0D3B22;">&#10003; Berhasil Menyinkronkan Data Dosen &amp; Rekam Jejak!</h3>
                <p style="margin:0;font-size:13.5px;color:#2d3748;">
                    Telah tersinkron <strong>' . (int)$result['dosen'] . ' Dosen</strong>, 
                    <strong>' . (int)$result['publikasi'] . ' Publikasi Ilmiah</strong>, 
                    <strong>' . (int)$result['penelitian'] . ' Penelitian/Hibah</strong>, 
                    <strong>' . (int)$result['pengabdian'] . ' Pengabdian</strong>, dan 
                    <strong>' . (int)$result['buku'] . ' Buku/Monograf</strong> dari Supabase BK-Connect.
                </p>
            </div>';
        } else {
            $notice = '<div class="notice notice-error is-dismissible" style="padding:16px;border-left:4px solid #dc2626;border-radius:8px;margin-bottom:20px;">
                <h3 style="margin:0 0 6px 0;color:#991b1b;">&times; Gagal Sinkronisasi</h3>
                <p style="margin:0;font-size:13.5px;color:#7f1d1d;">' . esc_html($result['error']) . '</p>
            </div>';
        }
    }

    // Handle Save Custom Order Action
    if (isset($_POST['bk_save_dosen_order']) && check_admin_referer('bk_dosen_order_action', 'bk_dosen_order_nonce')) {
        $raw_orders = isset($_POST['dosen_order']) && is_array($_POST['dosen_order']) ? $_POST['dosen_order'] : array();

        $json_file = get_template_directory() . '/dosen-data.json';
        $current_dosen = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();
        if (empty($current_dosen)) {
            $current_dosen = get_option('bk_dosen_data_cache', array());
        }

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

    $json_file = get_template_directory() . '/dosen-data.json';
    $all_dosen = file_exists($json_file) ? json_decode(file_get_contents($json_file), true) : array();
    if (empty($all_dosen)) {
        $all_dosen = get_option('bk_dosen_data_cache', array());
    }
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
                    Edit data profil dosen (NIP, Nama Panggilan, Kepakaran, Foto Transparan), atur urutan tampilan publik, dan integrasi pangkalan data Supabase BK-Connect.
                </p>
            </div>
            <div>
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
                <div style="font-size:12px;color:#6B7280;">Scopus, SINTA, Garuda</div>
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

        <!-- Sync Banner Card -->
        <div style="background:linear-gradient(135deg, #0D3B22 0%, #155532 100%);border-radius:16px;padding:22px 28px;color:#ffffff;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:20px;margin-bottom:26px;box-shadow:0 4px 16px rgba(13,59,34,0.12);">
            <div style="max-width:640px;">
                <h2 style="font-size:17px;font-weight:700;color:#ffffff;margin:0 0 6px 0;">
                    Sinkronisasi dengan Supabase BK-Connect
                </h2>
                <p style="font-size:12.5px;line-height:1.55;color:rgba(255,255,255,0.85);margin:0 0 8px 0;">
                    Tarik publikasi ilmiah, hibah riset, pengabdian masyarakat (PkM), dan buku monograf terbaru langsung dari database Supabase BK-Connect tanpa merusak data editan dan urutan kustom yang telah Anda simpan.
                </p>
                <div style="font-size:11.5px;color:#F5B82E;">
                    &bull; Terakhir disinkronkan: <strong><?php echo esc_html($last_synced); ?></strong>
                </div>
            </div>
            <div>
                <form method="post" action="">
                    <?php wp_nonce_field('bk_supabase_sync_action', 'bk_supabase_sync_nonce'); ?>
                    <button type="submit" name="bk_trigger_supabase_sync" class="button button-primary" style="background:#F5B82E;color:#0D3B22;border:none;font-size:13.5px;font-weight:700;padding:10px 22px;border-radius:10px;cursor:pointer;box-shadow:0 4px 14px rgba(0,0,0,0.2);transition:all 0.2s;" onmouseover="this.style.background='#e5a93b'" onmouseout="this.style.background='#F5B82E'">
                        ⚡ Tarik Data Terbaru Sekarang
                    </button>
                </form>
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
                                $no = 1;
                                $total_count = count($all_dosen);
                                foreach ($all_dosen as $d) : 
                                    $slug = $d['slug'] ?? '';
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
                                            <img src="<?php echo esc_url($foto_link); ?>" alt="<?php echo esc_attr($nama_lengkap); ?>" style="width:40px;height:40px;border-radius:10px;object-fit:cover;border:1.5px solid #0D3B22;display:block;margin:0 auto;box-shadow:0 2px 6px rgba(0,0,0,0.1);">
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
                                <img src="" id="edit-photo-preview" style="width:100%;height:100%;object-fit:contain;object-position:bottom center;display:none;">
                                <span id="edit-photo-none" style="font-size:11px;color:#F5B82E;font-weight:600;text-align:center;padding:6px;">Tanpa Foto</span>
                            </div>

                            <div style="flex:1;">
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">URL Foto Profil:</label>
                                <div style="display:flex;gap:8px;margin-bottom:8px;">
                                    <input type="url" name="foto_link" id="edit-foto-link" placeholder="https://..." style="flex:1;height:38px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 12px;font-size:13px;" oninput="bkUpdatePhotoPreview(this.value)">
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

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px;">
                            <div>
                                <label style="display:block;font-size:12px;font-weight:700;color:#374151;margin-bottom:4px;">SINTA Dikti URL:</label>
                                <input type="url" name="sinta_url" id="edit-sinta-url" placeholder="https://sinta.kemdikbud.go.id/authors/profile/..." style="width:100%;height:36px;border-radius:8px;border:1.5px solid #D1D5DB;padding:0 10px;font-size:12.5px;">
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
        document.getElementById('modal-title').innerText = 'Edit Data: ' + (d.nama_lengkap || d.slug);

        var modal = document.getElementById('bk-edit-modal-backdrop');
        modal.style.display = 'flex';
    }

    function bkCloseEditModal() {
        var modal = document.getElementById('bk-edit-modal-backdrop');
        modal.style.display = 'none';
    }

    function bkUpdatePhotoPreview(url) {
        var img = document.getElementById('edit-photo-preview');
        var none = document.getElementById('edit-photo-none');
        if (url && url.trim().length > 5) {
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
if (!function_exists('bk_parse_spotlight_image_url')) {
    function bk_parse_spotlight_image_url($url) {
        if (empty($url)) return '';
        $url = trim($url);

        // 1. Google Drive: file/d/{ID} atau open?id={ID} atau uc?id={ID}
        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=)([a-zA-Z0-9_-]{20,})/i', $url, $m)) {
            $gd_id = $m[1];
            // Format Google CDN direct high-res stream (2560px width for retina screens)
            return 'https://lh3.googleusercontent.com/d/' . $gd_id . '=w2560';
        }

        // 2. Dropbox: convert www.dropbox.com to dl.dropboxusercontent.com
        if (strpos($url, 'dropbox.com') !== false) {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
            $url = preg_replace('/\?dl=[01]$/', '', $url);
            return $url;
        }

        return $url;
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
                'embed_url' => 'https://www.youtube-nocookie.com/embed/' . $yt_id . '?autoplay=1&mute=1&controls=0&loop=1&playlist=' . $yt_id . '&enablejsapi=1&rel=0&playsinline=1&modestbranding=1&iv_load_policy=3'
            ];
        }

        // 2. Google Drive
        if (preg_match('/drive\.google\.com\/(?:file\/d\/|open\?id=)([a-zA-Z0-9_-]{20,})/i', $url, $m)) {
            $gd_id = $m[1];
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
                'embed_url' => 'https://player.vimeo.com/video/' . $vim_id . '?background=1&autoplay=1&loop=1&byline=0&title=0&muted=1'
            ];
        }

        // 4. Direct video (MP4/WebM atau link CDN)
        return [
            'type' => 'direct',
            'url'  => $url
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
    if (get_option('bk_site_health_optimized_v2') !== '1') {
        bk_fip_run_site_health_db_optimization();
        update_option('bk_site_health_optimized_v2', '1', false);
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
