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
if (!function_exists('ppb_spotlight_extract_gdrive_id')) {
    function ppb_spotlight_extract_gdrive_id($url) {
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

if (!function_exists('ppb_spotlight_resolve_preview_url')) {
    function ppb_spotlight_resolve_preview_url($url) {
        $url = trim((string) $url);
        if ($url === '') return '';
        $gd_id = ppb_spotlight_extract_gdrive_id($url);
        if ($gd_id !== '') {
            return 'https://drive.google.com/thumbnail?id=' . $gd_id . '&sz=w1200';
        }
        if (strpos($url, 'dropbox.com') !== false) {
            $url = str_replace('www.dropbox.com', 'dl.dropboxusercontent.com', $url);
            return preg_replace('/\?dl=[01]$/', '', $url);
        }
        return function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($url) : $url;
    }
}

if (!function_exists('ppb_spotlight_sanitize_asset_or_url')) {
    /**
     * Sanitasi URL/path sambil mempertahankan link Google Drive asli (untuk gambar, video, dan CTA),
     * link YouTube/Vimeo, anchor (#section), URL penuh, maupun path relatif tema (assets/...).
     */
    function ppb_spotlight_sanitize_asset_or_url($val) {
        $val = trim(wp_strip_all_tags((string) $val));
        if ($val === '') return '';
        if ($val[0] === '#') {
            return preg_replace('/[^A-Za-z0-9_\-#]/', '', $val);
        }
        if (preg_match('#^(https?:)?//#i', $val)) {
            return esc_url_raw($val);
        }
        if (strpos($val, '..') !== false || preg_match('#^[a-z0-9+.-]+:#i', $val)) {
            return '';
        }
        return preg_replace('#[^A-Za-z0-9/_\-.%~?=&]#', '', $val);
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
    // Migrasi non-destruktif v2: PERTAHANKAN semua slide lama user, hanya lengkapi metadata v2
    // dan tambahkan slide default Editorial Calm yang belum ada (tidak pernah menimpa/menghapus).
    if (get_option('ppb_spotlight_defaults_v2') !== '1') {
        $existing = get_option('ppb_spotlight_slides', null);
        $defaults = ppb_get_default_spotlight_slides();
        $default_by_id = [];
        foreach ($defaults as $def) {
            $default_by_id[$def['id']] = $def;
        }

        if (is_array($existing) && !empty($existing)) {
            if (get_option('ppb_spotlight_slides_legacy_backup', null) === null) {
                update_option('ppb_spotlight_slides_legacy_backup', $existing, false);
            }
            $merged = [];
            $seen_ids = [];
            foreach ($existing as $idx => $ex) {
                if (!is_array($ex)) continue;
                $sid = (string) ($ex['id'] ?? ('slide-legacy-' . $idx));
                $seen_ids[$sid] = true;
                if (isset($default_by_id[$sid])) {
                    $def = $default_by_id[$sid];
                    foreach (['mode_tag', 'kicker', 'eyebrow', 'headline', 'summary', 'caption_title', 'prompt_text', 'prompt_url'] as $k) {
                        if (empty($ex[$k]) && !empty($def[$k])) {
                            $ex[$k] = $def[$k];
                        }
                    }
                    if (empty($ex['metrics']) && !empty($def['metrics'])) {
                        $ex['metrics'] = $def['metrics'];
                    }
                } else {
                    if (empty($ex['headline']) && !empty($ex['caption_title'])) {
                        $ex['headline'] = $ex['caption_title'];
                    }
                    if (empty($ex['prompt_text']) && !empty($ex['action_text'])) {
                        $ex['prompt_text'] = $ex['action_text'];
                    }
                    if (empty($ex['prompt_url']) && !empty($ex['target_url'])) {
                        $ex['prompt_url'] = $ex['target_url'];
                    }
                }
                if (!isset($ex['order'])) {
                    $ex['order'] = $idx + 1;
                }
                $merged[] = $ex;
            }
            $next_order = count($merged) + 1;
            foreach ($defaults as $def) {
                if (!isset($seen_ids[$def['id']])) {
                    $def['order'] = $next_order++;
                    $merged[] = $def;
                }
            }
            update_option('ppb_spotlight_slides', $merged, false);
        } else {
            update_option('ppb_spotlight_slides', $defaults, false);
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
/* 2. REGISTRASI MENU ADMIN & ENQUEUE MEDIA LIBRARY SPOTLIGHT                 */
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

if (!function_exists('ppb_spotlight_enqueue_admin_assets')) {
function ppb_spotlight_enqueue_admin_assets($hook_suffix = '') {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';
    if ($page === 'ppb-spotlight-beranda' || strpos((string) $hook_suffix, 'ppb-spotlight-beranda') !== false) {
        wp_enqueue_media();
    }
}
}
add_action('admin_enqueue_scripts', 'ppb_spotlight_enqueue_admin_assets');

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
        $current = get_option('ppb_spotlight_slides', []);
        if (is_array($current) && !empty($current)) {
            update_option('ppb_spotlight_slides_legacy_backup', $current, false);
        }
        update_option('ppb_spotlight_slides', ppb_get_default_spotlight_slides(), false);
        update_option('ppb_spotlight_defaults_v2', '1', false);
        add_settings_error('ppb_spotlight_notices', 'reset', 'Spotlight Beranda dikembalikan ke 3 slide bawaan Editorial Calm.', 'info');
    } elseif ($action === 'restore_legacy_backup') {
        $backup = get_option('ppb_spotlight_slides_legacy_backup', null);
        if (is_array($backup) && !empty($backup)) {
            $current = ppb_get_spotlight_slides();
            $existing_ids = [];
            $merged = [];
            foreach ($backup as $b_idx => $b) {
                if (!is_array($b)) continue;
                $bid = (string) ($b['id'] ?? ('slide-restored-' . $b_idx));
                $existing_ids[$bid] = true;
                $merged[] = $b;
            }
            $ord = count($merged) + 1;
            foreach ($current as $c) {
                $cid = (string) ($c['id'] ?? '');
                if ($cid === '' || !isset($existing_ids[$cid])) {
                    $c['order'] = $ord++;
                    $merged[] = $c;
                }
            }
            update_option('ppb_spotlight_slides', $merged, false);
            add_settings_error('ppb_spotlight_notices', 'restored', 'Slide spotlight sebelumnya berhasil dipulihkan dan digabungkan!', 'success');
        }
    }
}
}
add_action('admin_init', 'ppb_handle_spotlight_admin_actions');

/* ========================================================================== */
/* 4. TAMPILAN ADMIN PAGE SPOTLIGHT (EDITORIAL CALM v3.0)                     */
/* ========================================================================== */
if (!function_exists('ppb_render_spotlight_slide_card')) {
function ppb_render_spotlight_slide_card($index, $slide, $recent_posts, $is_template = false) {
    $idx_attr    = $is_template ? '__INDEX__' : (int) $index;
    $num_display = $is_template ? '__NUM__' : ((int) $index + 1);
    $slide_type  = $slide['type'] ?? 'image';
    $slide_id    = $is_template ? '__SLIDE_ID__' : ($slide['id'] ?? ('slide-' . $index));
    $device      = $slide['device_target'] ?? 'all';
    $action_text = !empty($slide['action_text']) ? $slide['action_text'] : 'Lihat lebih lanjut';
    $prompt_text = $slide['prompt_text'] ?? $action_text;
    $img_raw     = $slide['image_url'] ?? '';
    $img_prev    = ppb_spotlight_resolve_preview_url($img_raw);
    $img_gd_id   = ppb_spotlight_extract_gdrive_id($img_raw);
    $img_mobile  = $slide['image_mobile_url'] ?? '';
    $img_mob_p   = ppb_spotlight_resolve_preview_url($img_mobile);
    $mob_gd_id   = ppb_spotlight_extract_gdrive_id($img_mobile);
    $video_raw   = $slide['video_url'] ?? '';
    $vid_gd_id   = ppb_spotlight_extract_gdrive_id($video_raw);
    $metrics     = isset($slide['metrics']) && is_array($slide['metrics']) ? $slide['metrics'] : [];
    ?>
    <div class="ppb-slide-card" data-index="<?php echo esc_attr($idx_attr); ?>" style="background:#FFFFFF;border:1px solid #CBD5E1;border-radius:10px;overflow:hidden;box-shadow:0 3px 10px rgba(0,0,0,0.04);">
        <!-- Slide Header Bar -->
        <div style="background:#F8FAFC;padding:12px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <span class="slide-handle" style="cursor:move;color:#94A3B8;font-size:18px;line-height:1;">&#9776;</span>
                <span style="font-family:monospace;font-weight:700;font-size:13px;background:#0D3B22;color:#F5B82E;padding:3px 8px;border-radius:4px;">
                    SLIDE #<span class="slide-number-display"><?php echo esc_html($num_display); ?></span>
                </span>
                <span class="slide-type-badge" style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:4px;background:#E8F0EA;color:#0D3B22;text-transform:uppercase;">
                    <?php echo esc_html($slide_type); ?>
                </span>
                <span class="slide-headline-preview" style="font-size:13px;color:#0F172A;font-weight:700;">
                    <?php echo esc_html($slide['headline'] ?? ($slide['caption_title'] ?? '')); ?>
                </span>
                <span style="font-size:12.5px;color:#475569;">
                    &middot; CTA: <strong class="slide-action-preview" style="color:#0D3B22;">&ldquo;<?php echo esc_html($action_text); ?>&rdquo;</strong>
                </span>
            </div>
            <div style="display:flex;align-items:center;gap:14px;">
                <label style="font-size:12.5px;color:#334155;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer;">
                    <input type="checkbox" name="slides[<?php echo esc_attr($idx_attr); ?>][active]" value="1" <?php checked($slide['active'] ?? 1, 1); ?>>
                    <span>Tampilkan</span>
                </label>
                <input type="hidden" name="slides[<?php echo esc_attr($idx_attr); ?>][id]" class="slide-id-input" value="<?php echo esc_attr($slide_id); ?>">
                <input type="hidden" name="slides[<?php echo esc_attr($idx_attr); ?>][order]" class="slide-order-input" value="<?php echo esc_attr($is_template ? '__NUM__' : ($slide['order'] ?? ($index + 1))); ?>">
                <button type="button" class="btn-toggle-slide button button-small" style="font-size:11.5px;font-weight:600;">Tutup &uarr;</button>
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
                        <select name="slides[<?php echo esc_attr($idx_attr); ?>][type]" class="slide-type-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-weight:600;">
                            <option value="image" <?php selected($slide_type, 'image'); ?>>🖼 Foto / Brosur Pengumuman</option>
                            <option value="video" <?php selected($slide_type, 'video'); ?>>🎥 Video Profil / Sinematik</option>
                            <option value="card" <?php selected($slide_type, 'card'); ?>>🏛 Kartu Panggung LAMDIK (3 Metrik)</option>
                            <option value="pinned_post" <?php selected($slide_type, 'pinned_post'); ?>>📌 Berita Tersemat</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:12.5px;margin-bottom:4px;">Target Perangkat:</label>
                        <select name="slides[<?php echo esc_attr($idx_attr); ?>][device_target]" class="slide-device-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
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
                            <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][mode_tag]" value="<?php echo esc_attr($slide['mode_tag'] ?? ''); ?>" placeholder="SOROTAN UTAMA" style="width:100%;">
                        </div>
                        <div>
                            <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Kicker / Baris Sorotan</label>
                            <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][kicker]" value="<?php echo esc_attr($slide['kicker'] ?? ''); ?>" placeholder="Sorotan · Pengumuman / Kegiatan" style="width:100%;">
                        </div>
                    </div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Judul Utama Slide (Headline)</label>
                        <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][headline]" class="slide-headline-input" value="<?php echo esc_attr($slide['headline'] ?? ''); ?>" placeholder="Judul utama slide..." style="width:100%;font-weight:700;">
                    </div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Ringkasan (Summary)</label>
                        <textarea name="slides[<?php echo esc_attr($idx_attr); ?>][summary]" rows="2" placeholder="Keterangan singkat yang tampil di overlay kiri bawah..." style="width:100%;"><?php echo esc_textarea($slide['summary'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#334155;">Judul Bar Bawah (Caption Bar)</label>
                        <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][caption_title]" value="<?php echo esc_attr($slide['caption_title'] ?? ''); ?>" style="width:100%;">
                    </div>
                </div>

                <!-- Card Metrics (shown when type == card) -->
                <div class="field-card-metrics" style="background:#FEF3C7;border:1px solid #F5B82E;border-radius:8px;padding:14px;margin-bottom:14px;<?php echo $slide_type === 'card' ? '' : 'display:none;'; ?>">
                    <div style="font-family:monospace;font-size:11px;font-weight:700;color:#92400E;margin-bottom:8px;">KARTU PANGGUNG AKREDITASI (EYEBROW &amp; 3 METRIK)</div>
                    <div style="margin-bottom:8px;">
                        <label style="display:block;font-size:11.5px;font-weight:600;color:#92400E;">Eyebrow Kartu</label>
                        <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][eyebrow]" value="<?php echo esc_attr($slide['eyebrow'] ?? 'AKREDITASI UNGGUL · LAMDIK'); ?>" style="width:100%;">
                    </div>
                    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;">
                        <?php for ($mi = 0; $mi < 3; $mi++) :
                            $ml = $metrics[$mi]['label'] ?? '';
                            $mv = $metrics[$mi]['val'] ?? '';
                        ?>
                            <div style="background:#FFF;padding:8px;border-radius:6px;border:1px solid #FDE68A;">
                                <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][metrics][<?php echo $mi; ?>][label]" value="<?php echo esc_attr($ml); ?>" placeholder="Jenjang (mis. S1)" style="width:100%;margin-bottom:4px;font-weight:700;font-size:12px;">
                                <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][metrics][<?php echo $mi; ?>][val]" value="<?php echo esc_attr($mv); ?>" placeholder="Nomor SK / Status" style="width:100%;font-size:11.5px;">
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Pinned Post Selector -->
                <div class="field-pinned-post" style="margin-bottom:14px;<?php echo $slide_type === 'pinned_post' ? '' : 'display:none;'; ?>">
                    <label style="display:block;font-weight:600;font-size:12.5px;color:#0D3B22;margin-bottom:4px;">Pilih Berita dari Database:</label>
                    <select name="slides[<?php echo esc_attr($idx_attr); ?>][pinned_post_id]" class="pinned-post-select" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                        <option value="">-- Pilih Berita --</option>
                        <?php foreach ($recent_posts as $p) : ?>
                            <option value="<?php echo (int)$p->ID; ?>" <?php selected($slide['pinned_post_id'] ?? '', $p->ID); ?> data-title="<?php echo esc_attr($p->post_title); ?>" data-url="<?php echo esc_url(get_permalink($p->ID)); ?>" data-thumb="<?php echo esc_url(get_the_post_thumbnail_url($p->ID, 'large') ?: ''); ?>">
                                <?php echo esc_html($p->post_title); ?> (<?php echo esc_html(get_the_date('d M Y', $p->ID)); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Video URL Field -->
                <div class="field-video-url" style="margin-bottom:14px;background:#F8FAFC;border:1px solid #E2E8F0;border-radius:8px;padding:12px 14px;<?php echo $slide_type === 'video' ? '' : 'display:none;'; ?>">
                    <label style="display:block;font-weight:700;font-size:12.5px;color:#0D3B22;margin-bottom:6px;">
                        🎥 Sumber Video Spotlight (Unggah MP4, Link Google Drive, atau YouTube):
                    </label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                        <button type="button" class="btn-select-video button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:600;">🎞 Unggah / Pilih Video MP4</button>
                        <button type="button" class="btn-gdrive-video button" style="font-weight:600;">☁️ Tempel Link Google Drive / YouTube</button>
                        <button type="button" class="btn-clear-video button" style="color:#DC2626;">✕ Hapus</button>
                    </div>
                    <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][video_url]" class="slide-video-url" value="<?php echo esc_attr($video_raw); ?>" placeholder="https://www.youtube.com/watch?v=... | https://drive.google.com/file/d/.../view | assets/video/..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                    <div class="video-source-status" style="font-size:11.5px;color:#475569;margin-top:5px;">
                        <?php
                        if ($vid_gd_id !== '') {
                            echo '☁️ <strong style="color:#0D3B22;">Google Drive Video terdeteksi</strong> (ID: ' . esc_html($vid_gd_id) . ') — pastikan akses <em>Siapa saja yang memiliki link</em>.';
                        } elseif (preg_match('/youtu(\.be|be\.com)/i', $video_raw)) {
                            echo '▶️ <strong style="color:#0D3B22;">Video YouTube terdeteksi</strong> — diputar di modal video saat diklik.';
                        } elseif ($video_raw !== '') {
                            echo '🎬 <strong style="color:#0D3B22;">Video MP4 / URL</strong> — jangan lupa pasang Gambar Latar/Poster Desktop di bawah sebagai sampul awal.';
                        } else {
                            echo 'Unggah MP4 lewat Media Library atau tempel link YouTube / Google Drive video.';
                        }
                        ?>
                    </div>
                </div>

                <!-- Desktop Image Field: Upload Media Library OR Google Drive Link -->
                <div class="field-image-url" style="margin-bottom:14px;background:#FFFFFF;border:1.5px solid #D4E3D8;border-radius:8px;padding:12px 14px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:8px;">
                        <label style="font-weight:700;font-size:12.5px;color:#0D3B22;margin:0;">
                            🖼 Gambar Latar / Poster Desktop <span style="font-weight:400;color:#64748B;">(Unggah Media Library atau Link Google Drive)</span>
                        </label>
                        <span class="image-source-badge" style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:999px;background:<?php echo $img_gd_id ? '#FEF3C7' : ($img_raw ? '#E8F0EA' : '#F1F5F9'); ?>;color:<?php echo $img_gd_id ? '#92400E' : ($img_raw ? '#0D3B22' : '#64748B'); ?>;">
                            <?php
                            if ($img_gd_id !== '') {
                                echo '☁️ Google Drive';
                            } elseif ($img_raw !== '' && strpos($img_raw, 'assets/') === 0) {
                                echo '📁 Aset Tema';
                            } elseif ($img_raw !== '') {
                                echo '🖼 Media Library / URL';
                            } else {
                                echo 'Belum dipilih';
                            }
                            ?>
                        </span>
                    </div>
                    <div style="display:flex;gap:12px;align-items:flex-start;">
                        <div class="image-preview-box" style="width:148px;height:92px;background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;text-align:center;">
                            <?php if (!empty($img_prev)) : ?>
                                <img src="<?php echo esc_url($img_prev); ?>" style="width:100%;height:100%;object-fit:cover;" onerror="this.style.display='none';this.parentNode.innerHTML='<span style=&quot;font-size:10px;color:#B45309;padding:4px;&quot;>Pratinjau GDrive / URL gagal dimuat. Cek izin Siapa saja yang memiliki link.</span>';">
                            <?php else : ?>
                                <span style="font-size:10.5px;color:#94A3B8;padding:6px;">Belum ada gambar</span>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                                <button type="button" class="btn-select-image button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:600;">📷 Unggah / Pilih Gambar</button>
                                <button type="button" class="btn-gdrive-image button" style="border-color:#F5B82E;background:#FFFBEB;color:#92400E;font-weight:600;">☁️ Pakai Link Google Drive</button>
                                <button type="button" class="btn-clear-image button" style="color:#DC2626;<?php echo $img_raw !== '' ? '' : 'display:none;'; ?>">✕ Hapus</button>
                            </div>
                            <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][image_url]" class="slide-image-url" value="<?php echo esc_attr($img_raw); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" placeholder="Tempel link Google Drive (https://drive.google.com/file/d/.../view), URL Media, atau assets/img/...">
                            <div class="gdrive-image-hint" style="font-size:11px;color:#64748B;margin-top:4px;line-height:1.4;">
                                Klik <strong>Unggah / Pilih Gambar</strong> untuk mengunggah file dari komputer, atau tempel link bagikan <strong>Google Drive</strong> (pastikan akses <em>Siapa saja yang memiliki link</em>).
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Optional Mobile Portrait Image -->
                <div class="field-image-mobile-url" style="background:#FAF8F5;border:1px dashed #CBD5E1;padding:12px 14px;border-radius:8px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:6px;">
                        <label style="font-weight:600;font-size:12.5px;color:#B45309;margin:0;">📱 Gambar Khusus Mobile / HP (Opsional — Unggah atau Link GDrive):</label>
                        <span class="image-mobile-source-badge" style="font-size:10.5px;font-weight:700;padding:2px 7px;border-radius:999px;background:<?php echo $mob_gd_id ? '#FEF3C7' : ($img_mobile ? '#E8F0EA' : '#F1F5F9'); ?>;color:<?php echo $mob_gd_id ? '#92400E' : ($img_mobile ? '#0D3B22' : '#64748B'); ?>;">
                            <?php echo $mob_gd_id ? '☁️ GDrive' : ($img_mobile ? '🖼 Terpasang' : 'Opsional'); ?>
                        </span>
                    </div>
                    <div style="display:flex;gap:12px;align-items:flex-start;">
                        <div class="image-mobile-preview-box" style="width:64px;height:80px;background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;text-align:center;">
                            <?php if (!empty($img_mob_p)) : ?>
                                <img src="<?php echo esc_url($img_mob_p); ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else : ?>
                                <span style="font-size:9.5px;color:#94A3B8;">HP</span>
                            <?php endif; ?>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px;">
                                <button type="button" class="btn-select-image-mobile button">📷 Unggah / Pilih Gambar HP</button>
                                <button type="button" class="btn-gdrive-image-mobile button" style="font-size:12px;">☁️ Link GDrive HP</button>
                                <button type="button" class="btn-clear-image-mobile button" style="color:#DC2626;<?php echo $img_mobile !== '' ? '' : 'display:none;'; ?>">✕ Hapus</button>
                            </div>
                            <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][image_mobile_url]" class="slide-image-mobile-url" value="<?php echo esc_attr($img_mobile); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;" placeholder="Opsional brosur vertikal HP atau link Google Drive...">
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
                    <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][action_text]" class="slide-action-text-input" value="<?php echo esc_attr($action_text); ?>" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13.5px;font-weight:600;color:#0D3B22;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">URL Tujuan Tombol Utama:</label>
                    <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][target_url]" class="slide-target-url" value="<?php echo esc_attr($slide['target_url'] ?? ''); ?>" placeholder="/akademik/kurikulum/ atau https://..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                </div>

                <div style="margin-bottom:14px;padding-top:10px;border-top:1px dashed #CBD5E1;">
                    <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">Teks Prompt Konfirmasi Tengah (Saat Kanvas Diklik):</label>
                    <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][prompt_text]" value="<?php echo esc_attr($prompt_text); ?>" placeholder="Lihat lebih lanjut" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                </div>

                <div style="margin-bottom:14px;">
                    <label style="display:block;font-weight:600;font-size:12.5px;color:#1E293B;margin-bottom:4px;">URL Tujuan Prompt Tengah:</label>
                    <input type="text" name="slides[<?php echo esc_attr($idx_attr); ?>][prompt_url]" value="<?php echo esc_attr($slide['prompt_url'] ?? ($slide['target_url'] ?? '')); ?>" placeholder="/profil/sejarah/ atau https://..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                </div>

                <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:6px;padding:12px 14px;font-size:12px;color:#475569;line-height:1.45;">
                    💡 <strong>Fitur Anti Salah-Klik:</strong> Saat pengunjung menyentuh area gambar/kanvas panggung, tombol konfirmasi emas muncul di tengah layar sebelum berpindah halaman.
                </div>
            </div>
        </div>
    </div>
    <?php
}
}

if (!function_exists('ppb_render_spotlight_admin_page')) {
function ppb_render_spotlight_admin_page() {
    if (!current_user_can('manage_options')) return;

    wp_enqueue_media();

    $slides   = ppb_get_spotlight_slides();
    $settings = ppb_get_spotlight_settings();
    $legacy_backup = get_option('ppb_spotlight_slides_legacy_backup', null);
    $has_unmerged_backup = false;
    if (is_array($legacy_backup) && !empty($legacy_backup)) {
        $curr_ids = array_map(function($s) { return (string) ($s['id'] ?? ''); }, $slides);
        foreach ($legacy_backup as $b) {
            $bid = (string) ($b['id'] ?? '');
            if ($bid !== '' && !in_array($bid, $curr_ids, true)) {
                $has_unmerged_backup = true;
                break;
            }
        }
    }

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

        <?php if ($has_unmerged_backup) : ?>
        <div class="notice notice-warning" style="padding:14px 18px;border-left:4px solid #F5B82E;display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;margin:0 0 18px;">
            <div style="font-size:13.5px;color:#0F172A;">
                <strong>Cadangan slide spotlight sebelumnya terdeteksi.</strong> Klik tombol di samping untuk menggabungkan slide sebelumnya ke daftar aktif tanpa menimpa slide baru.
            </div>
            <button type="button" id="btn-restore-legacy" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:700;">
                Pulihkan &amp; Gabungkan Slide Sebelumnya
            </button>
        </div>
        <?php endif; ?>

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
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
                <h2 style="font-size:18px;font-weight:700;color:#0D3B22;margin:0;">
                    Daftar Slide Spotlight (<span id="ppb-slide-total-count"><?php echo count($slides); ?></span> Item)
                </h2>
                <div style="display:flex;gap:10px;flex-wrap:wrap;">
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
                <?php foreach ($slides as $index => $slide) : ?>
                    <?php ppb_render_spotlight_slide_card($index, $slide, $recent_posts, false); ?>
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

    <?php
    $blank_slide = [
        'id'               => '',
        'type'             => 'image',
        'device_target'    => 'all',
        'mode_tag'         => 'SOROTAN BARU',
        'kicker'           => '',
        'eyebrow'          => 'AKREDITASI UNGGUL · LAMDIK',
        'headline'         => '',
        'summary'          => '',
        'caption_title'    => '',
        'video_url'        => '',
        'image_url'        => '',
        'image_mobile_url' => '',
        'target_url'       => '/profil/',
        'action_text'      => 'Lihat lebih lanjut',
        'prompt_text'      => 'Lihat lebih lanjut',
        'prompt_url'       => '/profil/',
        'metrics'          => [],
        'pinned_post_id'   => '',
        'active'           => 1,
        'order'            => 1,
    ];
    ?>
    <script type="text/html" id="tmpl-ppb-spotlight-slide">
        <?php ppb_render_spotlight_slide_card('__INDEX__', $blank_slide, $recent_posts, true); ?>
    </script>

    <script>
    jQuery(document).ready(function($) {
        var themeBase = <?php echo wp_json_encode(trailingslashit(get_template_directory_uri())); ?>;
        var nextSlideIndex = $('.ppb-slide-card').length;

        function extractGdriveId(val) {
            if (!val) return '';
            var m = val.match(/drive\.google\.com\/(?:file\/d\/|thumbnail\?id=|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]{10,})/i)
                || val.match(/drive\.google\.com\/[^\s]*?\/d\/([a-zA-Z0-9_-]{10,})/i)
                || val.match(/lh3\.googleusercontent\.com\/d\/([a-zA-Z0-9_-]{10,})/i);
            return m ? m[1] : '';
        }

        function resolvePreviewUrl(val) {
            val = (val || '').trim();
            if (!val) return '';
            var gdId = extractGdriveId(val);
            if (gdId) {
                return 'https://drive.google.com/thumbnail?id=' + gdId + '&sz=w1200';
            }
            if (val.indexOf('dropbox.com') !== -1) {
                return val.replace('www.dropbox.com', 'dl.dropboxusercontent.com').replace(/\?dl=[01]$/, '');
            }
            return val.indexOf('assets/') === 0 ? (themeBase + val) : val;
        }

        function escHtml(str) {
            return String(str || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function refreshImageField(card, isMobile) {
            var inputSel   = isMobile ? '.slide-image-mobile-url' : '.slide-image-url';
            var boxSel     = isMobile ? '.image-mobile-preview-box' : '.image-preview-box';
            var badgeSel   = isMobile ? '.image-mobile-source-badge' : '.image-source-badge';
            var clearSel   = isMobile ? '.btn-clear-image-mobile' : '.btn-clear-image';
            var emptyLabel = isMobile ? 'HP' : 'Belum ada gambar';

            var val = (card.find(inputSel).val() || '').trim();
            var box = card.find(boxSel);
            var badge = card.find(badgeSel);
            var clearBtn = card.find(clearSel);

            if (!val) {
                box.html('<span style="font-size:10px;color:#94A3B8;padding:4px;">' + emptyLabel + '</span>');
                badge.text(isMobile ? 'Opsional' : 'Belum dipilih').css({ background: '#F1F5F9', color: '#64748B' });
                clearBtn.hide();
                return;
            }

            clearBtn.show();
            var gdId = extractGdriveId(val);
            if (gdId) {
                badge.text(isMobile ? '☁️ GDrive' : '☁️ Google Drive').css({ background: '#FEF3C7', color: '#92400E' });
            } else if (val.indexOf('assets/') === 0) {
                badge.text(isMobile ? '📁 Aset' : '📁 Aset Tema').css({ background: '#E8F0EA', color: '#0D3B22' });
            } else {
                badge.text(isMobile ? '🖼 Terpasang' : '🖼 Media Library / URL').css({ background: '#E8F0EA', color: '#0D3B22' });
            }

            var previewUrl = resolvePreviewUrl(val);
            var fallbackAttr = gdId ? ' data-gd-fallback="' + escHtml('https://lh3.googleusercontent.com/d/' + gdId + '=w1200') + '"' : '';
            var img = $('<img style="width:100%;height:100%;object-fit:cover;"' + fallbackAttr + '>');
            img.on('error', function() {
                var fb = $(this).attr('data-gd-fallback');
                if (fb && $(this).attr('src') !== fb) {
                    $(this).attr('src', fb);
                } else {
                    box.html('<span style="font-size:10px;color:#B45309;padding:4px;">Pratinjau GDrive / URL gagal dimuat. Cek izin Siapa saja yang memiliki link.</span>');
                }
            });
            img.attr('src', previewUrl);
            box.empty().append(img);
        }

        function refreshVideoStatus(card) {
            var val = (card.find('.slide-video-url').val() || '').trim();
            var st  = card.find('.video-source-status');
            var gdId = extractGdriveId(val);
            if (gdId) {
                st.html('☁️ <strong style="color:#0D3B22;">Google Drive Video terdeteksi</strong> (ID: ' + escHtml(gdId) + ') — pastikan akses <em>Siapa saja yang memiliki link</em>.');
            } else if (/youtu(\.be|be\.com)/i.test(val)) {
                st.html('▶️ <strong style="color:#0D3B22;">Video YouTube terdeteksi</strong> — diputar di modal video saat diklik.');
            } else if (val !== '') {
                st.html('🎬 <strong style="color:#0D3B22;">Video MP4 / URL</strong> — pastikan Gambar Latar/Poster Desktop diisi sebagai sampul awal.');
            } else {
                st.text('Unggah MP4 lewat Media Library atau tempel link YouTube / Google Drive video.');
            }
        }

        function openWpMediaPicker(opts, onSelect) {
            if (typeof wp === 'undefined' || !wp.media) {
                alert('Media Library WordPress belum termuat pada halaman ini. Muat ulang halaman atau tempel link Google Drive / URL secara langsung pada kolom isian.');
                return;
            }
            var frame = wp.media(opts);
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                onSelect(attachment);
            });
            frame.open();
        }

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
            var title = opt.data('title');
            var url = opt.data('url');
            var thumb = opt.data('thumb');
            if (title && !card.find('.slide-headline-input').val()) {
                card.find('.slide-headline-input').val(title).trigger('input');
            }
            if (url) card.find('.slide-target-url').val(url);
            if (thumb) {
                card.find('.slide-image-url').val(thumb);
                refreshImageField(card, false);
            }
            if (!card.find('.slide-action-text-input').val()) {
                card.find('.slide-action-text-input').val('Baca berita selengkapnya').trigger('input');
            }
        });

        $(document).on('input paste change', '.slide-image-url', function() {
            refreshImageField($(this).closest('.ppb-slide-card'), false);
        });

        $(document).on('input paste change', '.slide-image-mobile-url', function() {
            refreshImageField($(this).closest('.ppb-slide-card'), true);
        });

        $(document).on('input paste change', '.slide-video-url', function() {
            refreshVideoStatus($(this).closest('.ppb-slide-card'));
        });

        $(document).on('input', '.slide-headline-input', function() {
            $(this).closest('.ppb-slide-card').find('.slide-headline-preview').text($(this).val());
        });

        $(document).on('input', '.slide-action-text-input', function() {
            var val = $(this).val() || 'Lihat lebih lanjut';
            $(this).closest('.ppb-slide-card').find('.slide-action-preview').text('“' + val + '”');
        });

        $(document).on('click', '.btn-select-image', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            openWpMediaPicker({
                title: 'Unggah atau Pilih Gambar Spotlight Desktop',
                button: { text: 'Gunakan Gambar Ini' },
                multiple: false,
                library: { type: 'image' }
            }, function(attachment) {
                card.find('.slide-image-url').val(attachment.url);
                refreshImageField(card, false);
            });
        });

        $(document).on('click', '.btn-gdrive-image', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var inp = card.find('.slide-image-url');
            var pasted = window.prompt('Tempel link bagikan Google Drive gambar (contoh: https://drive.google.com/file/d/.../view?usp=sharing):\n\nPastikan izin akses file Google Drive disetel ke "Siapa saja yang memiliki link" (Anyone with the link).', inp.val() || '');
            if (pasted !== null) {
                inp.val(pasted.trim());
                refreshImageField(card, false);
            } else {
                inp.focus();
            }
        });

        $(document).on('click', '.btn-clear-image', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            card.find('.slide-image-url').val('');
            refreshImageField(card, false);
        });

        $(document).on('click', '.btn-select-image-mobile', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            openWpMediaPicker({
                title: 'Unggah atau Pilih Gambar Mobile / HP',
                button: { text: 'Gunakan Gambar Ini' },
                multiple: false,
                library: { type: 'image' }
            }, function(attachment) {
                card.find('.slide-image-mobile-url').val(attachment.url);
                refreshImageField(card, true);
            });
        });

        $(document).on('click', '.btn-gdrive-image-mobile', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var inp = card.find('.slide-image-mobile-url');
            var pasted = window.prompt('Tempel link Google Drive gambar khusus HP (portrait):', inp.val() || '');
            if (pasted !== null) {
                inp.val(pasted.trim());
                refreshImageField(card, true);
            } else {
                inp.focus();
            }
        });

        $(document).on('click', '.btn-clear-image-mobile', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            card.find('.slide-image-mobile-url').val('');
            refreshImageField(card, true);
        });

        $(document).on('click', '.btn-select-video', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            openWpMediaPicker({
                title: 'Unggah atau Pilih Video MP4',
                button: { text: 'Gunakan Video Ini' },
                multiple: false,
                library: { type: 'video' }
            }, function(attachment) {
                card.find('.slide-video-url').val(attachment.url);
                refreshVideoStatus(card);
            });
        });

        $(document).on('click', '.btn-gdrive-video', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            var inp = card.find('.slide-video-url');
            var pasted = window.prompt('Tempel link Google Drive video atau link YouTube:', inp.val() || '');
            if (pasted !== null) {
                inp.val(pasted.trim());
                refreshVideoStatus(card);
            } else {
                inp.focus();
            }
        });

        $(document).on('click', '.btn-clear-video', function(e) {
            e.preventDefault();
            var card = $(this).closest('.ppb-slide-card');
            card.find('.slide-video-url').val('');
            refreshVideoStatus(card);
        });

        $('#btn-add-slide').on('click', function() {
            var count = $('.ppb-slide-card').length;
            var newIdx = nextSlideIndex++;
            var tmpl = ($('#tmpl-ppb-spotlight-slide').html() || '')
                .replace(/__INDEX__/g, String(newIdx))
                .replace(/__NUM__/g, String(count + 1))
                .replace(/__SLIDE_ID__/g, 'slide-' + Date.now());
            var $card = $(tmpl);
            $card.css('border', '1.5px solid #0D3B22');
            $('#slides-container').append($card);
            updateSlideNumbers();
            if ($card.offset()) {
                $('html, body').animate({ scrollTop: $card.offset().top - 60 }, 250);
            }
        });

        $('#btn-reset-default').on('click', function() {
            if (confirm('PERHATIAN: Ini akan mengganti daftar slide aktif ke 3 slide bawaan Editorial Calm.\n\nApakah Anda yakin? (Slide sebelumnya tetap tersimpan di cadangan.)')) {
                $('#ppb_spotlight_action').val('reset_default');
                $('#ppb-spotlight-form').submit();
            }
        });

        $('#btn-restore-legacy').on('click', function() {
            $('#ppb_spotlight_action').val('restore_legacy_backup');
            $('#ppb-spotlight-form').submit();
        });

        function updateSlideNumbers() {
            var cards = $('.ppb-slide-card');
            cards.each(function(i) {
                $(this).find('.slide-number-display').text(i + 1);
                $(this).find('.slide-order-input').val(i + 1);
            });
            $('#ppb-slide-total-count').text(cards.length);
        }
    });
    </script>
    <?php
}
}

