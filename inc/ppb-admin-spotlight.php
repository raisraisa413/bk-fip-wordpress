<?php
/**
 * Module: PPB FIP UNM - Manajemen Hero Spotlight & Media Beranda (Editorial Calm v3.0)
 * Description: Pengelolaan slide spotlight beranda 100vh penuh layar:
 *              - Video Profil (`video`),
 *              - Kartu Panggung Akreditasi LAMDIK (`card` dengan 3 metrik),
 *              - Foto / Brosur Pengumuman (`image`),
 *              - Berita Tersemat (`pinned_post`).
 *              Mendukung teks editorial (kicker, headline, summary, mode tag), tombol CTA,
 *              serta konfirmasi anti salah-klik (`prompt_text` / `prompt_url`).
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DATA DEFAULT & HELPER RETRIEVAL                                         */
/* ========================================================================== */
if (!function_exists('ppb_spotlight_sanitize_asset_or_url')) {
    function ppb_spotlight_sanitize_asset_or_url($val) {
        if (function_exists('bk_sanitize_asset_or_url')) {
            return bk_sanitize_asset_or_url($val);
        }
        $val = trim(wp_strip_all_tags((string) $val));
        if ($val === '') return '';
        if (preg_match('#^(https?:)?//#i', $val)) return esc_url_raw($val);
        if (strpos($val, '..') !== false || preg_match('#^[a-z0-9+.-]+:#i', $val)) return '';
        return preg_replace('#[^A-Za-z0-9/_\-.%~]#', '', $val);
    }
}

if (!function_exists('ppb_get_default_spotlight_slides')) {
function ppb_get_default_spotlight_slides() {
    return [
        [
            'id'               => 'slide-video-profil',
            'type'             => 'video',
            'device_target'    => 'all',
            'mode_tag'         => 'VIDEO PROFIL',
            'kicker'           => 'Sorotan 01 · Video Profil 1969–2025',
            'eyebrow'          => '',
            'headline'         => 'Membentuk Konselor Humanis, Adaptif, dan Berintegritas.',
            'summary'          => 'Jurusan PPB / Prodi Bimbingan & Konseling FIP UNM memadukan sains perilaku, kearifan Bugis-Makassar, dan teknologi asesmen sejak 1969.',
            'caption_title'    => '57 Tahun Mendidik Calon Konselor Nusantara — Kampus Tidung FIP UNM',
            'video_url'        => 'assets/video/video-profil-ppb-fip-unm.mp4',
            'image_url'        => 'assets/img/hero-campus.jpg',
            'image_mobile_url' => '',
            'target_url'       => home_url('/akademik/kurikulum/'),
            'action_text'      => 'Jelajahi Kurikulum OBE',
            'prompt_text'      => 'Lihat Sejarah 1969 & Profil',
            'prompt_url'       => home_url('/profil/sejarah/'),
            'metrics'          => [],
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 1,
        ],
        [
            'id'               => 'slide-akreditasi-unggul',
            'type'             => 'card',
            'device_target'    => 'all',
            'mode_tag'         => 'AKREDITASI UNGGUL',
            'kicker'           => 'Sorotan 02 · Mutu Nasional LAMDIK',
            'eyebrow'          => 'AKREDITASI UNGGUL · LAMDIK',
            'headline'         => 'Prodi S1 & S2 Terakreditasi Unggul',
            'summary'          => 'Ditopang 25 dosen (4 Guru Besar & Doktor), Kurikulum OBE 2025 (144 SKS), serta siklus penjaminan mutu PPEPP.',
            'caption_title'    => 'Akreditasi Unggul S1 & S2 Bimbingan dan Konseling FIP UNM',
            'video_url'        => '',
            'image_url'        => 'assets/img/hero-campus.jpg',
            'image_mobile_url' => '',
            'target_url'       => home_url('/akreditasi/'),
            'action_text'      => 'Lihat 9 Kriteria LAMDIK',
            'prompt_text'      => 'Lihat 9 Kriteria LAMDIK',
            'prompt_url'       => home_url('/akreditasi/'),
            'metrics'          => [
                ['label' => 'S1 Sarjana',  'val' => 'SK 742/SK/LAMDIK (s.d. 2028)'],
                ['label' => 'S2 Magister', 'val' => 'SK 412/SK/LAMDIK (Unggul)'],
                ['label' => 'S3 Doktoral', 'val' => 'Baik Sekali · Riset Indigenos'],
            ],
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 2,
        ],
        [
            'id'               => 'slide-laboratorium-bk',
            'type'             => 'image',
            'device_target'    => 'all',
            'mode_tag'         => 'LAB BK GUNUNGSARI',
            'kicker'           => 'Sorotan 03 · Fasilitas Praktikum',
            'eyebrow'          => '',
            'headline'         => 'Laboratorium Mikrokonseling & Asesmen Psikologi Terstandar.',
            'summary'          => 'Dilengkapi 4 bilik kedap suara one-way mirror, studio cybercounseling, dan inventaris instrumen tes IST, CFIT, RMIB, serta SDS.',
            'caption_title'    => 'Laboratorium Mikrokonseling 4 Bilik & Asesmen Psikologi Terstandar',
            'video_url'        => '',
            'image_url'        => 'assets/img/lab-studio.jpg',
            'image_mobile_url' => '',
            'target_url'       => home_url('/laboratorium/'),
            'action_text'      => 'Buka Lab BK & Alat Tes',
            'prompt_text'      => 'Buka Lab BK & Alat Tes',
            'prompt_url'       => home_url('/laboratorium/'),
            'metrics'          => [],
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 3,
        ],
    ];
}
}

if (!function_exists('ppb_get_spotlight_slides')) {
function ppb_get_spotlight_slides() {
    // One-time migration to v2 defaults if legacy slides lack `headline`
    if (get_option('ppb_spotlight_defaults_v2') !== '1') {
        $existing = get_option('ppb_spotlight_slides', null);
        $has_v2 = false;
        if (is_array($existing)) {
            foreach ($existing as $ex) {
                if (!empty($ex['headline'])) {
                    $has_v2 = true;
                    break;
                }
            }
        }
        if (!$has_v2) {
            update_option('ppb_spotlight_slides', ppb_get_default_spotlight_slides(), false);
        }
        update_option('ppb_spotlight_defaults_v2', '1', false);
    }

    $saved = get_option('ppb_spotlight_slides', null);
    if ($saved === null || !is_array($saved) || empty($saved)) {
        return ppb_get_default_spotlight_slides();
    }
    usort($saved, function($a, $b) {
        return (int)($a['order'] ?? 0) - (int)($b['order'] ?? 0);
    });
    return $saved;
}
}

if (!function_exists('ppb_get_spotlight_settings')) {
function ppb_get_spotlight_settings() {
    return [
        'autoplay'           => get_option('ppb_spotlight_autoplay', '1'),
        'speed'              => (int)get_option('ppb_spotlight_speed', 8),
        'fit_mode'           => get_option('ppb_spotlight_fit_mode', 'cover'),
        'height_mode'        => get_option('ppb_spotlight_height', 'fullscreen'),
        'mobile_height_mode' => get_option('ppb_spotlight_mobile_height', 'fullscreen'),
    ];
}
}

/* ========================================================================== */
/* 2. REGISTRASI MENU ADMIN: SPOTLIGHT BERANDA                                */
/* ========================================================================== */
if (!function_exists('ppb_register_spotlight_menu')) {
function ppb_register_spotlight_menu() {
    add_submenu_page(
        'ppb-portal-hub',
        'Hero Spotlight Beranda',
        '🌟 Spotlight Beranda',
        'manage_options',
        'ppb-spotlight-beranda',
        'ppb_render_spotlight_admin_page'
    );
}
}

/* ========================================================================== */
/* 3. PROSES SUBMIT & ACTION ADMIN SPOTLIGHT                                   */
/* ========================================================================== */
if (!function_exists('ppb_handle_spotlight_admin_actions')) {
function ppb_handle_spotlight_admin_actions() {
    if (!isset($_POST['ppb_spotlight_action']) || !current_user_can('manage_options')) {
        return;
    }

    check_admin_referer('ppb_spotlight_nonce', 'ppb_spotlight_nonce_field');

    $action = sanitize_text_field(wp_unslash($_POST['ppb_spotlight_action']));

    if ($action === 'save_slides') {
        $slides_raw = isset($_POST['slides']) && is_array($_POST['slides']) ? wp_unslash($_POST['slides']) : [];
        $cleaned_slides = [];

        foreach ($slides_raw as $idx => $s) {
            $type = in_array($s['type'] ?? '', ['video', 'card', 'image', 'pinned_post'], true) ? $s['type'] : 'image';
            if ($type !== 'card' && empty($s['video_url']) && empty($s['image_url']) && empty($s['image_mobile_url']) && empty($s['pinned_post_id']) && empty($s['headline'])) {
                continue;
            }

            $action_txt = trim(sanitize_text_field($s['action_text'] ?? ''));
            if ($action_txt === '') $action_txt = 'Lihat lebih lanjut';

            $prompt_txt = trim(sanitize_text_field($s['prompt_text'] ?? ''));
            if ($prompt_txt === '') $prompt_txt = $action_txt;

            $device = sanitize_text_field($s['device_target'] ?? 'all');
            if (!in_array($device, ['all', 'desktop_only', 'mobile_only'], true)) {
                $device = 'all';
            }

            $metrics = [];
            if (!empty($s['metrics']) && is_array($s['metrics'])) {
                foreach (array_slice($s['metrics'], 0, 3) as $m) {
                    $ml = trim(sanitize_text_field($m['label'] ?? ''));
                    $mv = trim(sanitize_text_field($m['val'] ?? ''));
                    if ($ml !== '' || $mv !== '') {
                        $metrics[] = ['label' => $ml, 'val' => $mv];
                    }
                }
            }

            $target_url = ppb_spotlight_sanitize_asset_or_url($s['target_url'] ?? '');
            $prompt_url = ppb_spotlight_sanitize_asset_or_url($s['prompt_url'] ?? '');
            if ($prompt_url === '') $prompt_url = $target_url;

            $cleaned_slides[] = [
                'id'               => sanitize_text_field($s['id'] ?? ('slide-' . uniqid())),
                'type'             => $type,
                'device_target'    => $device,
                'mode_tag'         => sanitize_text_field($s['mode_tag'] ?? strtoupper($type)),
                'kicker'           => sanitize_text_field($s['kicker'] ?? ''),
                'eyebrow'          => sanitize_text_field($s['eyebrow'] ?? ''),
                'headline'         => sanitize_text_field($s['headline'] ?? ''),
                'summary'          => sanitize_textarea_field($s['summary'] ?? ''),
                'caption_title'    => sanitize_text_field($s['caption_title'] ?? ''),
                'video_url'        => ppb_spotlight_sanitize_asset_or_url($s['video_url'] ?? ''),
                'image_url'        => ppb_spotlight_sanitize_asset_or_url($s['image_url'] ?? ''),
                'image_mobile_url' => ppb_spotlight_sanitize_asset_or_url($s['image_mobile_url'] ?? ''),
                'target_url'       => $target_url,
                'action_text'      => $action_txt,
                'prompt_text'      => $prompt_txt,
                'prompt_url'       => $prompt_url,
                'metrics'          => $metrics,
                'pinned_post_id'   => !empty($s['pinned_post_id']) ? (int)$s['pinned_post_id'] : '',
                'active'           => !empty($s['active']) ? 1 : 0,
                'order'            => (int)($s['order'] ?? ($idx + 1)),
            ];
        }

        update_option('ppb_spotlight_slides', $cleaned_slides, false);
        update_option('ppb_spotlight_defaults_v2', '1', false);

        update_option('ppb_spotlight_autoplay', !empty($_POST['spotlight_autoplay']) ? '1' : '0');
        update_option('ppb_spotlight_speed', max(4, min(30, (int)($_POST['spotlight_speed'] ?? 8))));
        update_option('ppb_spotlight_fit_mode', sanitize_text_field($_POST['spotlight_fit_mode'] ?? 'cover'));
        update_option('ppb_spotlight_height', sanitize_text_field($_POST['spotlight_height'] ?? 'fullscreen'));
        update_option('ppb_spotlight_mobile_height', sanitize_text_field($_POST['spotlight_mobile_height'] ?? 'fullscreen'));

        add_settings_error('ppb_spotlight_notices', 'saved', 'Semua perubahan Spotlight Beranda berhasil disimpan!', 'success');
    } elseif ($action === 'reset_default') {
        update_option('ppb_spotlight_slides', ppb_get_default_spotlight_slides(), false);
        update_option('ppb_spotlight_defaults_v2', '1', false);
        add_settings_error('ppb_spotlight_notices', 'reset', 'Spotlight Beranda dikembalikan ke 3 slide bawaan Editorial Calm.', 'info');
    }
}
}
add_action('admin_init', 'ppb_handle_spotlight_admin_actions');

/* ========================================================================== */
/* 4. TAMPILAN ADMIN PAGE SPOTLIGHT (EDITORIAL CALM v3.0)                     */
/* ========================================================================== */
if (!function_exists('ppb_render_spotlight_admin_page')) {
function ppb_render_spotlight_admin_page() {
    if (!current_user_can('manage_options')) return;

    wp_enqueue_media();

    $slides   = ppb_get_spotlight_slides();
    $settings = ppb_get_spotlight_settings();

    $recent_posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 40,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);
    ?>
    <div class="wrap" style="max-width:1160px;margin-top:24px;font-family:'Plus Jakarta Sans','Segoe UI',-apple-system,sans-serif;">

        <!-- Header Card -->
        <div style="background:#0D3B22;color:#ffffff;border-radius:12px;padding:26px 32px;margin-bottom:24px;border:1px solid #1E5128;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-family:monospace;font-size:11px;letter-spacing:0.12em;color:#F5B82E;font-weight:700;margin-bottom:6px;">
                    PPB FIP UNM &middot; FULL-SCREEN 100VH SPOTLIGHT STAGE
                </div>
                <h1 style="color:#ffffff;font-size:26px;font-weight:700;margin:0 0 8px 0;line-height:1.2;">
                    Kelola Spotlight Beranda (Video, Kartu LAMDIK, Foto &amp; Berita)
                </h1>
                <p style="color:rgba(255,255,255,0.85);font-size:13.5px;margin:0;max-width:760px;line-height:1.5;">
                    Atur slide panggung utama 100vh: <strong>Video Profil</strong>, <strong>Kartu Akreditasi LAMDIK (3 metrik)</strong>, <strong>Foto Kegiatan/Fasilitas</strong>, atau <strong>Berita Tersemat</strong> — lengkap dengan judul caption, tombol aksi, dan konfirmasi anti salah-klik.
                </p>
            </div>
            <div>
                <a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" class="button" style="background:#F5B82E;color:#0D3B22;font-weight:700;border:none;padding:8px 18px;border-radius:6px;height:auto;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
                    <span>Lihat Beranda Live</span>
                    <span class="dashicons dashicons-external" style="font-size:16px;line-height:1.4;"></span>
                </a>
            </div>
        </div>

        <?php settings_errors('ppb_spotlight_notices'); ?>

        <form method="post" action="" id="ppb-spotlight-form">
            <?php wp_nonce_field('ppb_spotlight_nonce', 'ppb_spotlight_nonce_field'); ?>
            <input type="hidden" name="ppb_spotlight_action" id="ppb_spotlight_action" value="save_slides">

            <!-- Global Slider Settings Bar -->
            <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:10px;padding:18px 22px;margin-bottom:24px;box-shadow:0 2px 6px rgba(0,0,0,0.03);display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:20px;align-items:center;">
                <div>
                    <label style="display:flex;align-items:center;gap:8px;font-weight:600;font-size:13.5px;color:#0F172A;cursor:pointer;">
                        <input type="checkbox" name="spotlight_autoplay" value="1" <?php checked($settings['autoplay'], '1'); ?>>
                        <span>Autoplay Berjalan Otomatis</span>
                    </label>
                    <span style="font-size:11.5px;color:#64748B;display:block;margin-top:2px;">Slide berganti otomatis sesuai durasi di samping.</span>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Durasi Per Slide:</label>
                    <input type="number" name="spotlight_speed" min="4" max="30" value="<?php echo esc_attr($settings['speed']); ?>" style="width:90px;border-radius:6px;padding:4px 8px;border:1px solid #CBD5E1;">
                    <span style="font-size:11.5px;color:#64748B;margin-left:4px;">detik</span>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Kesesuaian Media:</label>
                    <select name="spotlight_fit_mode" style="border-radius:6px;padding:4px 10px;border:1px solid #CBD5E1;font-size:13px;width:100%;">
                        <option value="cover" <?php selected($settings['fit_mode'], 'cover'); ?>>Penuhi Layar 100vh (Cover)</option>
                        <option value="contain" <?php selected($settings['fit_mode'], 'contain'); ?>>Utuh Tidak Terpotong (Contain)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Tinggi di Layar HP (Mobile):</label>
                    <select name="spotlight_mobile_height" style="border-radius:6px;padding:4px 10px;border:1px solid #CBD5E1;font-size:13px;width:100%;">
                        <option value="fullscreen" <?php selected($settings['mobile_height_mode'] ?? 'fullscreen', 'fullscreen'); ?>>Layar Penuh (100dvh)</option>
                        <option value="cinema" <?php selected($settings['mobile_height_mode'] ?? 'fullscreen', 'cinema'); ?>>Proporsional Cinema (72vh)</option>
                    </select>
                </div>
            </div>

            <!-- List Header -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h2 style="font-size:18px;font-weight:700;color:#0D3B22;margin:0;">
                    Daftar Slide Spotlight (<?php echo count($slides); ?> Item)
                </h2>
                <div style="display:flex;gap:10px;">
                    <button type="button" id="btn-add-slide" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:600;border-radius:6px;padding:4px 14px;height:auto;display:inline-flex;align-items:center;gap:6px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px;line-height:1.4;"></span>
                        <span>Tambah Slide Baru</span>
                    </button>
                    <button type="button" id="btn-reset-default" class="button" style="border-radius:6px;color:#64748B;font-weight:600;">
                        Reset ke 3 Slide Bawaan
                    </button>
                </div>
            </div>

            <div id="slides-container" style="display:flex;flex-direction:column;gap:18px;margin-bottom:28px;">
                <?php foreach ($slides as $index => $slide) :
                    $slide_type  = $slide['type'] ?? 'image';
                    $slide_id    = $slide['id'] ?? ('slide-' . $index);
                    $device      = $slide['device_target'] ?? 'all';
                    $action_text = $slide['action_text'] ?: 'Lihat lebih lanjut';
                    $prompt_text = $slide['prompt_text'] ?? $action_text;
                    $img_raw     = $slide['image_url'] ?? '';
                    $img_prev    = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($img_raw) : $img_raw;
                    $img_mobile  = $slide['image_mobile_url'] ?? '';
                    $img_mob_p   = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($img_mobile) : $img_mobile;
                    $metrics     = isset($slide['metrics']) && is_array($slide['metrics']) ? $slide['metrics'] : [];
                ?>
                <div class="ppb-slide-card" data-index="<?php echo (int)$index; ?>" style="background:#FFFFFF;border:1px solid #CBD5E1;border-radius:10px;overflow:hidden;box-shadow:0 3px 10px rgba(0,0,0,0.04);">
                    <!-- Slide Header Bar -->
                    <div style="background:#F8FAFC;padding:12px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <span class="slide-handle" style="cursor:move;color:#94A3B8;font-size:18px;line-height:1;">&#9776;</span>
                            <span style="font-family:monospace;font-weight:700;font-size:13px;background:#0D3B22;color:#F5B82E;padding:3px 8px;border-radius:4px;">
                                SLIDE #<span class="slide-number-display"><?php echo (int)$index + 1; ?></span>
                            </span>
                            <span class="slide-type-badge" style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:4px;background:#E8F0EA;color:#0D3B22;text-transform:uppercase;">
                                <?php echo esc_html($slide_type); ?>
                            </span>
                            <span style="font-size:13px;color:#0F172A;font-weight:700;">
                                <?php echo esc_html($slide['headline'] ?? ($slide['caption_title'] ?? '')); ?>
                            </span>
                            <span style="font-size:12.5px;color:#475569;">
                                &middot; CTA: <strong class="slide-action-preview" style="color:#0D3B22;">&ldquo;<?php echo esc_html($action_text); ?>&rdquo;</strong>
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:14px;">
                            <label style="font-size:12.5px;color:#334155;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="slides[<?php echo (int)$index; ?>][active]" value="1" <?php checked($slide['active'] ?? 1, 1); ?>>
                                <span>Tampilkan</span>
                            </label>
                            <input type="hidden" name="slides[<?php echo (int)$index; ?>][id]" value="<?php echo esc_attr($slide_id); ?>">
                            <input type="hidden" name="slides[<?php echo (int)$index; ?>][order]" class="slide-order-input" value="<?php echo esc_attr($slide['order'] ?? ($index + 1)); ?>">
                            <button type="button" class="btn-toggle-slide button button-small" style="font-size:11.5px;font-weight:600;">Edit &darr;</button>
                            <button type="button" class="btn-delete-slide button button-small button-link-delete" style="color:#DC2626;font-size:11.5px;">Hapus</button>
                        </div>
                    </div>

                    <!-- Slide Body -->
                    <div class="slide-body" style="padding:20px 24px;display:grid;grid-template-columns:1.15fr 0.85fr;gap:24px;">
                        <!-- Left Column: Media & Editorial Content -->
                        <div>
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px;">
                                <div>
                                    <label style="display:block;font-weight:600;font-size:12.5px;margin-bottom:4px;">Tipe Slide:</label>
                                    <select name="slides[<?php echo (int)$index; ?>][type]" class="slide-type-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-weight:600;">
                                        <option value="video" <?php selected($slide_type, 'video'); ?>>🎥 Video Profil / Sinematik</option>
                                        <option value="card" <?php selected($slide_type, 'card'); ?>>🏛 Kartu Panggung LAMDIK (3 Metrik)</option>
                                        <option value="image" <?php selected($slide_type, 'image'); ?>>🖼 Foto / Brosur Pengumuman</option>
                                        <option value="pinned_post" <?php selected($slide_type, 'pinned_post'); ?>>📌 Berita Tersemat</option>
                                    </select>
                                </div>
                                <div>
                                    <label style="display:block;font-weight:600;font-size:12.5px;margin-bottom:4px;">Target Perangkat:</label>
                                    <select name="slides[<?php echo (int)$index; ?>][device_target]" class="slide-device-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                                        <option value="all" <?php selected($device, 'all'); ?>>🌐 Semua Perangkat</option>
                                        <option value="desktop_only" <?php selected($device, 'desktop_only'); ?>>🖥 Khusus Desktop</option>
                                        <option value="mobile_only" <?php selected($device, 'mobile_only'); ?>>📱 Khusus Mobile (HP)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Editorial Caption Fields -->
                            <div style="background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:14px;margin-bottom:14px;">
                                <div style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;margin-bottom:8px;">TEKS PANGGUNG &amp; CAPTION EDITORIAL</div>
                                <div style="display:grid;grid-template-columns:160px 1fr;gap:8px;margin-bottom:8px;">
                                    <div>
                                        <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Badge Pojok Kiri Atas</label>
                                        <input type="text" name="slides[<?php echo (int)$index; ?>][mode_tag]" value="<?php echo esc_attr($slide['mode_tag'] ?? ''); ?>" placeholder="VIDEO PROFIL" style="width:100%;">
                                    </div>
                                    <div>
                                        <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Kicker / Baris Sorotan</label>
                                        <input type="text" name="slides[<?php echo (int)$index; ?>][kicker]" value="<?php echo esc_attr($slide['kicker'] ?? ''); ?>" placeholder="Sorotan 01 · Video Profil 1969–2025" style="width:100%;">
                                    </div>
                                </div>
                                <div style="margin-bottom:8px;">
                                    <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Judul Utama Slide (Headline)</label>
                                    <input type="text" name="slides[<?php echo (int)$index; ?>][headline]" value="<?php echo esc_attr($slide['headline'] ?? ''); ?>" placeholder="Membentuk Konselor Humanis, Adaptif, dan Berintegritas." style="width:100%;font-weight:700;">
                                </div>
                                <div style="margin-bottom:8px;">
                                    <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Ringkasan (Summary)</label>
                                    <textarea name="slides[<?php echo (int)$index; ?>][summary]" rows="2" style="width:100%;"><?php echo esc_textarea($slide['summary'] ?? ''); ?></textarea>
                                </div>
                                <div>
                                    <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Judul Bar Bawah (Caption Bar)</label>
                                    <input type="text" name="slides[<?php echo (int)$index; ?>][caption_title]" value="<?php echo esc_attr($slide['caption_title'] ?? ''); ?>" style="width:100%;">
                                </div>
                            </div>

                            <!-- Card Metrics (shown when type == card) -->
                            <div class="field-card-metrics" style="background:#FEF3C7;border:1px solid #F5B82E;border-radius:8px;padding:14px;margin-bottom:14px;<?php echo $slide_type === 'card' ? '' : 'display:none;'; ?>">
                                <div style="font-family:monospace;font-size:11px;font-weight:700;color:#92400E;margin-bottom:8px;">KARTU PANGGUNG AKREDITASI (EYEBROW &amp; 3 METRIK)</div>
                                <div style="margin-bottom:8px;">
                                    <label style="display:block;font-size:11.5px;font-weight:600;color:#92400E;">Eyebrow Kartu</label>
                                    <input type="text" name="slides[<?php echo (int)$index; ?>][eyebrow]" value="<?php echo esc_attr($slide['eyebrow'] ?? 'AKREDITASI UNGGUL · LAMDIK'); ?>" style="width:100%;">
                                </div>
                                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                                    <?php for ($mi = 0; $mi < 3; $mi++) :
                                        $ml = $metrics[$mi]['label'] ?? '';
                                        $mv = $metrics[$mi]['val'] ?? '';
                                    ?>
                                        <div style="background:#FFF;padding:8px;border-radius:6px;border:1px solid #FDE68A;">
                                            <input type="text" name="slides[<?php echo (int)$index; ?>][metrics][<?php echo $mi; ?>][label]" value="<?php echo esc_attr($ml); ?>" placeholder="Jenjang (mis. S1)" style="width:100%;margin-bottom:4px;font-weight:700;font-size:12px;">
                                            <input type="text" name="slides[<?php echo (int)$index; ?>][metrics][<?php echo $mi; ?>][val]" value="<?php echo esc_attr($mv); ?>" placeholder="Nomor SK / Status" style="width:100%;font-size:11.5px;">
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Pinned Post Selector -->
                            <div class="field-pinned-post" style="margin-bottom:14px;<?php echo $slide_type === 'pinned_post' ? '' : 'display:none;'; ?>">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#0D3B22;margin-bottom:4px;">Pilih Berita dari Database:</label>
                                <select name="slides[<?php echo (int)$index; ?>][pinned_post_id]" class="pinned-post-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                                    <option value="">-- Pilih Berita --</option>
                                    <?php foreach ($recent_posts as $p) : ?>
                                        <option value="<?php echo (int)$p->ID; ?>" <?php selected($slide['pinned_post_id'] ?? '', $p->ID); ?> data-title="<?php echo esc_attr($p->post_title); ?>" data-url="<?php echo esc_url(get_permalink($p->ID)); ?>" data-thumb="<?php echo esc_url(get_the_post_thumbnail_url($p->ID, 'large') ?: ''); ?>">
                                            <?php echo esc_html($p->post_title); ?> (<?php echo esc_html(get_the_date('d M Y', $p->ID)); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Video URL Field -->
                            <div class="field-video-url" style="margin-bottom:14px;<?php echo $slide_type === 'video' ? '' : 'display:none;'; ?>">
                                <label style="display:block;font-weight:600;font-size:12.5px;margin-bottom:4px;">Tautan Video (YouTube, Google Drive, atau MP4):</label>
                                <div style="display:flex;gap:8px;">
                                    <input type="text" name="slides[<?php echo (int)$index; ?>][video_url]" class="slide-video-url" value="<?php echo esc_attr($slide['video_url'] ?? ''); ?>" placeholder="assets/video/... atau https://..." style="flex:1;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                                    <button type="button" class="btn-select-video button">Pilih Video</button>
                                </div>
                            </div>

                            <!-- Desktop Image Field -->
                            <div class="field-image-url" style="margin-bottom:14px;">
                                <label style="display:block;font-weight:600;font-size:12.5px;margin-bottom:6px;">Gambar Latar / Poster Desktop (mendukung <code>assets/img/...</code>, Media Library &amp; Google Drive):</label>
                                <div style="display:flex;gap:12px;align-items:flex-start;">
                                    <div class="image-preview-box" style="width:130px;height:80px;background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                        <?php if (!empty($img_prev)) : ?>
                                            <img src="<?php echo esc_url($img_prev); ?>" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else : ?>
                                            <span style="font-size:10px;color:#94A3B8;">Preview</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;">
                                        <input type="text" name="slides[<?php echo (int)$index; ?>][image_url]" class="slide-image-url" value="<?php echo esc_attr($img_raw); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;margin-bottom:6px;" placeholder="assets/img/hero-campus.jpg atau https://...">
                                        <button type="button" class="btn-select-image button">Pilih Gambar Desktop</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Mobile Image -->
                            <div class="field-image-mobile-url" style="background:#FAF8F5;border:1px dashed #CBD5E1;padding:12px 14px;border-radius:8px;">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#B45309;margin-bottom:6px;">📱 Gambar Khusus Mobile / HP (Opsional):</label>
                                <div style="display:flex;gap:12px;align-items:flex-start;">
                                    <div class="image-mobile-preview-box" style="width:64px;height:80px;background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                        <?php if (!empty($img_mob_p)) : ?>
                                            <img src="<?php echo esc_url($img_mob_p); ?>" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else : ?>
                                            <span style="font-size:9.5px;color:#94A3B8;">HP</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;">
                                        <input type="text" name="slides[<?php echo (int)$index; ?>][image_mobile_url]" class="slide-image-mobile-url" value="<?php echo esc_attr($img_mobile); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;margin-bottom:6px;" placeholder="Opsional brosur vertikal HP...">
                                        <button type="button" class="btn-select-image-mobile button">Pilih Gambar Mobile</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: CTA & Anti-Misclick Prompt -->
                        <div style="background:#FAF8F5;border:1.5px solid #E5E7EB;border-radius:8px;padding:18px 20px;">
                            <div style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;letter-spacing:0.08em;margin-bottom:10px;">
                                TOMBOL AKSI (CTA) &amp; KONFIRMASI KLIK TENGAH
                            </div>

                            <div style="margin-bottom:14px;">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">Teks Tombol Utama (Emas):</label>
                                <input type="text" name="slides[<?php echo (int)$index; ?>][action_text]" class="slide-action-text-input" value="<?php echo esc_attr($action_text); ?>" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13.5px;font-weight:600;color:#0D3B22;">
                            </div>

                            <div style="margin-bottom:14px;">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">URL Tujuan Tombol Utama:</label>
                                <input type="text" name="slides[<?php echo (int)$index; ?>][target_url]" class="slide-target-url" value="<?php echo esc_attr($slide['target_url'] ?? ''); ?>" placeholder="/akademik/kurikulum/ atau https://..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                            </div>

                            <div style="margin-bottom:14px;padding-top:10px;border-top:1px dashed #CBD5E1;">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">Teks Prompt Konfirmasi Tengah (Saat Kanvas Diklik):</label>
                                <input type="text" name="slides[<?php echo (int)$index; ?>][prompt_text]" value="<?php echo esc_attr($prompt_text); ?>" placeholder="Lihat Sejarah 1969 & Profil" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                            </div>

                            <div style="margin-bottom:14px;">
                                <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">URL Tujuan Prompt Tengah:</label>
                                <input type="text" name="slides[<?php echo (int)$index; ?>][prompt_url]" value="<?php echo esc_attr($slide['prompt_url'] ?? ($slide['target_url'] ?? '')); ?>" placeholder="/profil/sejarah/ atau https://..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                            </div>

                            <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:6px;padding:12px 14px;font-size:12px;color:#475569;line-height:1.45;">
                                💡 <strong>Fitur Anti Salah-Klik:</strong> Saat pengunjung menyentuh area gambar/kanvas panggung, tombol konfirmasi emas muncul di tengah layar sebelum berpindah halaman.
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Sticky Save Floating Bar -->
            <div style="position:sticky;bottom:20px;background:#0D3B22;border:1px solid #1E5128;border-radius:10px;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 12px 32px rgba(0,0,0,0.25);z-index:99;">
                <div style="color:rgba(255,255,255,0.85);font-size:13.5px;">
                    Simpan perubahan agar panggung spotlight 100vh di beranda segera diperbarui.
                </div>
                <div>
                    <button type="submit" class="button button-primary button-hero" style="background:#F5B82E;color:#0D3B22;font-weight:700;border:none;border-radius:6px;padding:8px 24px;font-size:14px;cursor:pointer;height:auto;">
                        Simpan Semua Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var themeBase = <?php echo wp_json_encode(trailingslashit(get_template_directory_uri())); ?>;

        $(document).on('click', '.btn-toggle-slide', function() {
            var body = $(this).closest('.ppb-slide-card').find('.slide-body');
            body.slideToggle(180);
            $(this).html(body.is(':visible') ? 'Tutup &uarr;' : 'Edit &darr;');
        });

        $(document).on('click', '.btn-delete-slide', function() {
            if (confirm('Hapus slide ini dari spotlight?')) {
                $(this).closest('.ppb-slide-card').fadeOut(200, function() {
                    $(this).remove();
                    updateSlideNumbers();
                });
            }
        });

        $(document).on('change', '.slide-type-select', function() {
            var card = $(this).closest('.ppb-slide-card');
            var val = $(this).val();
            card.find('.slide-type-badge').text(val);
            card.find('.field-video-url').toggle(val === 'video');
            card.find('.field-pinned-post').toggle(val === 'pinned_post');
            card.find('.field-card-metrics').toggle(val === 'card');
        });

        $(document).on('change', '.pinned-post-select', function() {
            var opt = $(this).find('option:selected');
            if (!opt.val()) return;
            var card = $(this).closest('.ppb-slide-card');
            var url = opt.data('url');
            var thumb = opt.data('thumb');
            if (url) card.find('.slide-target-url').val(url);
            if (thumb) {
                card.find('.slide-image-url').val(thumb);
                card.find('.image-preview-box').html('<img src="' + thumb + '" style="width:100%;height:100%;object-fit:cover;">');
            }
            if (!card.find('.slide-action-text-input').val()) {
                card.find('.slide-action-text-input').val('Baca berita selengkapnya');
            }
        });

        $(document).on('input paste change', '.slide-image-url', function() {
            var val = $(this).val().trim();
            var card = $(this).closest('.ppb-slide-card');
            if (!val) return;
            var m = val.match(/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=)([a-zA-Z0-9_-]{10,})/i);
            var previewUrl = m ? ('https://drive.google.com/thumbnail?id=' + m[1] + '&sz=w800') : (val.indexOf('assets/') === 0 ? themeBase + val : val);
            card.find('.image-preview-box').html('<img src="' + previewUrl + '" style="width:100%;height:100%;object-fit:cover;">');
        });

        $(document).on('input', '.slide-action-text-input', function() {
            var val = $(this).val() || 'Lihat lebih lanjut';
            $(this).closest('.ppb-slide-card').find('.slide-action-preview').text('“' + val + '”');
        });

        $(document).on('click', '.btn-select-image', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var frame = wp.media({ title: 'Pilih Foto / Banner Desktop', button: { text: 'Gunakan Gambar Ini' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-image-url').val(attachment.url);
                card.find('.image-preview-box').html('<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        $(document).on('click', '.btn-select-image-mobile', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var frame = wp.media({ title: 'Pilih Gambar Mobile', button: { text: 'Gunakan Gambar Ini' }, multiple: false, library: { type: 'image' } });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-image-mobile-url').val(attachment.url);
                card.find('.image-mobile-preview-box').html('<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        $(document).on('click', '.btn-select-video', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var frame = wp.media({ title: 'Pilih Video MP4', button: { text: 'Gunakan Video Ini' }, multiple: false, library: { type: 'video' } });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-video-url').val(attachment.url);
            });
            frame.open();
        });

        $('#btn-add-slide').on('click', function() {
            var count = $('.ppb-slide-card').length;
            var newIdx = count;
            var template = '<div class="ppb-slide-card" data-index="' + newIdx + '" style="background:#FFFFFF;border:1.5px solid #0D3B22;border-radius:10px;overflow:hidden;margin-top:10px;">' +
                '<div style="background:#F8FAFC;padding:12px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">' +
                '<div style="display:flex;align-items:center;gap:12px;"><span style="font-family:monospace;font-weight:700;font-size:13px;background:#0D3B22;color:#F5B82E;padding:3px 8px;border-radius:4px;">SLIDE #<span class="slide-number-display">' + (count + 1) + '</span></span>' +
                '<span class="slide-type-badge" style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:4px;background:#E8F0EA;color:#0D3B22;">IMAGE</span></div>' +
                '<div style="display:flex;align-items:center;gap:12px;"><label><input type="checkbox" name="slides[' + newIdx + '][active]" value="1" checked> Tampilkan</label>' +
                '<input type="hidden" name="slides[' + newIdx + '][id]" value="slide-' + Date.now() + '">' +
                '<input type="hidden" name="slides[' + newIdx + '][order]" class="slide-order-input" value="' + (count + 1) + '">' +
                '<button type="button" class="btn-delete-slide button button-small button-link-delete" style="color:#DC2626;">Hapus</button></div></div>' +
                '<div class="slide-body" style="padding:20px 24px;display:grid;grid-template-columns:1.15fr 0.85fr;gap:24px;">' +
                '<div><p><label><strong>Tipe Slide:</strong></label> <select name="slides[' + newIdx + '][type]" class="slide-type-select"><option value="image" selected>Foto / Brosur</option><option value="video">Video</option><option value="card">Kartu Akreditasi</option><option value="pinned_post">Berita Tersemat</option></select></p>' +
                '<p><label><strong>Badge Mode:</strong></label><br><input type="text" name="slides[' + newIdx + '][mode_tag]" value="SOROTAN" style="width:100%;"></p>' +
                '<p><label><strong>Kicker:</strong></label><br><input type="text" name="slides[' + newIdx + '][kicker]" style="width:100%;"></p>' +
                '<p><label><strong>Headline:</strong></label><br><input type="text" name="slides[' + newIdx + '][headline]" style="width:100%;font-weight:700;"></p>' +
                '<p><label><strong>Ringkasan:</strong></label><br><textarea name="slides[' + newIdx + '][summary]" rows="2" style="width:100%;"></textarea></p>' +
                '<p><label><strong>Gambar Desktop (URL / assets/img/...):</strong></label><br><input type="text" name="slides[' + newIdx + '][image_url]" class="slide-image-url" value="assets/img/hero-campus.jpg" style="width:100%;"></p></div>' +
                '<div style="background:#FAF8F5;padding:16px;border-radius:8px;">' +
                '<p><label><strong>Teks Tombol CTA:</strong></label><br><input type="text" name="slides[' + newIdx + '][action_text]" class="slide-action-text-input" value="Lihat lebih lanjut" style="width:100%;"></p>' +
                '<p><label><strong>URL Tujuan:</strong></label><br><input type="text" name="slides[' + newIdx + '][target_url]" class="slide-target-url" value="/profil/" style="width:100%;"></p>' +
                '<p><label><strong>Teks Prompt Konfirmasi Tengah:</strong></label><br><input type="text" name="slides[' + newIdx + '][prompt_text]" value="Lihat lebih lanjut" style="width:100%;"></p>' +
                '<p><label><strong>URL Prompt Tengah:</strong></label><br><input type="text" name="slides[' + newIdx + '][prompt_url]" value="/profil/" style="width:100%;"></p></div></div></div>';
            $('#slides-container').append(template);
        });

        $('#btn-reset-default').on('click', function() {
            if (confirm('Kembalikan slide spotlight ke 3 slide standar Editorial Calm?')) {
                $('#ppb_spotlight_action').val('reset_default');
                $('#ppb-spotlight-form').submit();
            }
        });

        function updateSlideNumbers() {
            $('.ppb-slide-card').each(function(i) {
                $(this).find('.slide-number-display').text(i + 1);
                $(this).find('.slide-order-input').val(i + 1);
            });
        }
    });
    </script>
    <?php
}
}
