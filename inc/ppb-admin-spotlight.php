<?php
/**
 * Module: PPB FIP UNM - Manajemen Hero Spotlight & Media Beranda
 * Description: Pengelolaan slide spotlight beranda (Video Profil, Foto/Brosur, Berita Tersemat).
 * Media tampil bersih tanpa teks statis yang menutupi konten (True Spotlight).
 * Mendukung pengaturan tampilan terpisah untuk Desktop (Landscape) dan Mobile (Portrait / HP).
 * Klik pertama memunculkan prompt "Lihat lebih lanjut" (mencegah salah klik) menuju link berita/tujuan.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. DATA DEFAULT & HELPER RETRIEVAL                                         */
/* ========================================================================== */
if (!function_exists('ppb_get_default_spotlight_slides')) {
function ppb_get_default_spotlight_slides() {
    $theme_uri = get_template_directory_uri();
    return [
        [
            'id'               => 'slide-video-profil',
            'type'             => 'video',
            'device_target'    => 'all', // all, desktop_only, mobile_only
            'video_url'        => $theme_uri . '/assets/video/video-profil-ppb-fip-unm.mp4',
            'image_url'        => $theme_uri . '/assets/img/gedung-unm.png',
            'image_mobile_url' => '', // Optional portrait image for mobile
            'target_url'       => home_url('/profil/sejarah/'),
            'action_text'      => 'Lihat profil lengkap',
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 1
        ],
        [
            'id'               => 'slide-akreditasi-unggul',
            'type'             => 'image',
            'device_target'    => 'all',
            'video_url'        => '',
            'image_url'        => $theme_uri . '/assets/img/banner-prodi-1.png',
            'image_mobile_url' => '',
            'target_url'       => '#stats_section',
            'action_text'      => 'Lihat lebih lanjut',
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 2
        ],
        [
            'id'               => 'slide-laboratorium-bk',
            'type'             => 'image',
            'device_target'    => 'all',
            'video_url'        => '',
            'image_url'        => $theme_uri . '/assets/img/gedung-unm.png',
            'image_mobile_url' => '',
            'target_url'       => '#lab-section',
            'action_text'      => 'Lihat fasilitas laboratorium',
            'pinned_post_id'   => '',
            'active'           => 1,
            'order'            => 3
        ]
    ];
}
}

if (!function_exists('ppb_get_spotlight_slides')) {
function ppb_get_spotlight_slides() {
    $saved = get_option('ppb_spotlight_slides', null);
    if ($saved === null || !is_array($saved) || empty($saved)) {
        return ppb_get_default_spotlight_slides();
    }
    // Filter active and sort by order
    usort($saved, function($a, $b) {
        return (int)($a['order'] ?? 0) - (int)($b['order'] ?? 0);
    });
    return $saved;
}
}

if (!function_exists('ppb_get_spotlight_settings')) {
function ppb_get_spotlight_settings() {
    return [
        'autoplay'              => get_option('ppb_spotlight_autoplay', '1'),
        'speed'                 => (int)get_option('ppb_spotlight_speed', 8), // seconds
        'fit_mode'              => get_option('ppb_spotlight_fit_mode', 'contain'),
        'height_mode'           => get_option('ppb_spotlight_height', 'fullscreen'),
        'mobile_height_mode'    => get_option('ppb_spotlight_mobile_height', 'fullscreen'), // fullscreen or cinema
    ];
}
}

/* ========================================================================== */
/* 2. REGISTRASI MENU ADMIN: SPOTLIGHT BERANDA                                */
/* ========================================================================== */
if (!function_exists('ppb_register_spotlight_menu')) {
function ppb_register_spotlight_menu() {
    // Disatukan di bawah Menu Induk Portal PPB FIP
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
// Handled by ppb-portal-hub
// add_action('admin_menu', 'ppb_register_spotlight_menu', 20);

/* ========================================================================== */
/* 3. PROSES SUBMIT & ACTION ADMIN SPOTLIGHT                                   */
/* ========================================================================== */
if (!function_exists('ppb_handle_spotlight_admin_actions')) {
function ppb_handle_spotlight_admin_actions() {
    if (!isset($_POST['ppb_spotlight_action']) || !current_user_can('manage_options')) {
        return;
    }

    check_admin_referer('ppb_spotlight_nonce', 'ppb_spotlight_nonce_field');

    $action = sanitize_text_field($_POST['ppb_spotlight_action']);

    if ($action === 'save_slides') {
        $slides_raw = $_POST['slides'] ?? [];
        $cleaned_slides = [];

        if (is_array($slides_raw)) {
            foreach ($slides_raw as $idx => $s) {
                if (empty($s['video_url']) && empty($s['image_url']) && empty($s['image_mobile_url']) && empty($s['pinned_post_id'])) {
                    continue; // Skip empty
                }

                $action_txt = trim(sanitize_text_field($s['action_text'] ?? ''));
                if (empty($action_txt)) {
                    $action_txt = 'Lihat lebih lanjut';
                }

                $device = sanitize_text_field($s['device_target'] ?? 'all');
                if (!in_array($device, ['all', 'desktop_only', 'mobile_only'])) {
                    $device = 'all';
                }

                $cleaned_slides[] = [
                    'id'                 => sanitize_text_field($s['id'] ?? ('slide-' . uniqid())),
                    'type'               => in_array($s['type'] ?? '', ['video', 'image', 'pinned_post']) ? $s['type'] : 'image',
                    'device_target'      => $device,
                    'video_url'          => esc_url_raw($s['video_url'] ?? ''),
                    'image_url'          => esc_url_raw(function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($s['image_url'] ?? '') : ($s['image_url'] ?? '')),
                    'image_mobile_url'   => esc_url_raw(function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($s['image_mobile_url'] ?? '') : ($s['image_mobile_url'] ?? '')),
                    'target_url'         => esc_url_raw($s['target_url'] ?? ''),
                    'action_text'        => $action_txt,
                    'pinned_post_id'     => !empty($s['pinned_post_id']) ? (int)$s['pinned_post_id'] : '',
                    'active'             => !empty($s['active']) ? 1 : 0,
                    'order'              => (int)($s['order'] ?? ($idx + 1)),
                ];
            }
        }

        update_option('ppb_spotlight_slides', $cleaned_slides, false);

        // General settings
        update_option('ppb_spotlight_autoplay', !empty($_POST['spotlight_autoplay']) ? '1' : '0');
        update_option('ppb_spotlight_speed', max(4, min(30, (int)($_POST['spotlight_speed'] ?? 8))));
        update_option('ppb_spotlight_fit_mode', sanitize_text_field($_POST['spotlight_fit_mode'] ?? 'contain'));
        update_option('ppb_spotlight_height', sanitize_text_field($_POST['spotlight_height'] ?? 'fullscreen'));
        update_option('ppb_spotlight_mobile_height', sanitize_text_field($_POST['spotlight_mobile_height'] ?? 'fullscreen'));

        add_settings_error('ppb_spotlight_notices', 'saved', 'Semua perubahan Spotlight Beranda berhasil disimpan!', 'success');
    } elseif ($action === 'reset_default') {
        update_option('ppb_spotlight_slides', ppb_get_default_spotlight_slides(), false);
        add_settings_error('ppb_spotlight_notices', 'reset', 'Spotlight Beranda dikembalikan ke susunan bawaan resmi.', 'info');
    }
}
}
add_action('admin_init', 'ppb_handle_spotlight_admin_actions');

/* ========================================================================== */
/* 4. TAMPILAN ADMIN PAGE SPOTLIGHT (SWISS EDITORIAL + SHADCN)               */
/* ========================================================================== */
if (!function_exists('ppb_render_spotlight_admin_page')) {
function ppb_render_spotlight_admin_page() {
    if (!current_user_can('manage_options')) return;

    // Load WordPress media scripts for image/video upload modal
    wp_enqueue_media();

    $slides = ppb_get_spotlight_slides();
    $settings = ppb_get_spotlight_settings();

    // Query recent published posts for Pinned Post selector
    $recent_posts = get_posts([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => 40,
        'orderby'        => 'date',
        'order'          => 'DESC'
    ]);
    ?>
    <div class="wrap" style="max-width:1160px;margin-top:24px;font-family:'Segoe UI',-apple-system,BlinkMacSystemFont,sans-serif;">
        
        <!-- Header Monogram & Title -->
        <div style="background:#0D3B22;color:#ffffff;border-radius:12px;padding:26px 32px;margin-bottom:24px;border:1px solid #1E5128;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
            <div>
                <div style="font-family:monospace;font-size:11px;letter-spacing:0.12em;color:#F5B82E;font-weight:700;margin-bottom:6px;">
                    PPB FIP UNM &middot; RESPONSIVE SPOTLIGHT SHOWCASE
                </div>
                <h1 style="color:#ffffff;font-size:26px;font-weight:700;margin:0 0 8px 0;line-height:1.2;">
                    Kelola Spotlight Beranda (Desktop &amp; Mobile)
                </h1>
                <p style="color:rgba(255,255,255,0.85);font-size:13.5px;margin:0;max-width:760px;line-height:1.5;">
                    Media tampil bersih ke paling bawah tanpa terhalang teks. Anda dapat mengatur apakah slide tampil di <strong>Semua Perangkat</strong>, <strong>Hanya Desktop (Landscape 16:9)</strong>, atau <strong>Hanya Mobile (Portrait / Brosur HP)</strong>, serta mengunggah gambar khusus versi mobile untuk slide yang sama.
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
                        <input type="checkbox" name="spotlight_autoplay" value="1" <?php checked($settings['autoplay'], '1'); ?> style="border-radius:4px;">
                        <span>Autoplay Berjalan Otomatis</span>
                    </label>
                    <span style="font-size:11.5px;color:#64748B;display:block;margin-top:2px;">Slide berganti otomatis jika video telah selesai diputar.</span>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Durasi Slide Gambar/Brosur:</label>
                    <input type="number" name="spotlight_speed" min="4" max="30" value="<?php echo esc_attr($settings['speed']); ?>" style="width:90px;border-radius:6px;padding:4px 8px;border:1px solid #CBD5E1;">
                    <span style="font-size:11.5px;color:#64748B;margin-left:4px;">detik</span>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Kesesuaian Media Desktop:</label>
                    <select name="spotlight_fit_mode" style="border-radius:6px;padding:4px 10px;border:1px solid #CBD5E1;font-size:13px;width:100%;">
                        <option value="contain" <?php selected($settings['fit_mode'], 'contain'); ?>>Utuh Tidak Terpotong (Contain - Brosur &amp; Video Sempurna)</option>
                        <option value="cover" <?php selected($settings['fit_mode'], 'cover'); ?>>Penuhi Layar (Cover / Penuh)</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-weight:600;font-size:13px;color:#0F172A;margin-bottom:4px;">Tinggi di Layar HP (Mobile):</label>
                    <select name="spotlight_mobile_height" style="border-radius:6px;padding:4px 10px;border:1px solid #CBD5E1;font-size:13px;width:100%;">
                        <option value="fullscreen" <?php selected($settings['mobile_height_mode'] ?? 'fullscreen', 'fullscreen'); ?>>Sampai Paling Bawah HP (100dvh - Layar Penuh)</option>
                        <option value="cinema" <?php selected($settings['mobile_height_mode'] ?? 'fullscreen', 'cinema'); ?>>Proporsional Cinema (72vh)</option>
                    </select>
                </div>
            </div>

            <!-- List of Spotlight Slides -->
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                <h2 style="font-size:18px;font-weight:700;color:#0D3B22;margin:0;">
                    Daftar Slide Media (<?php echo count($slides); ?> Item)
                </h2>
                <div style="display:flex;gap:10px;">
                    <button type="button" id="btn-add-slide" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;font-weight:600;border-radius:6px;padding:4px 14px;height:auto;display:inline-flex;align-items:center;gap:6px;">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:16px;line-height:1.4;"></span>
                        <span>Tambah Media Baru</span>
                    </button>
                    <button type="button" id="btn-reset-default" class="button" style="border-radius:6px;color:#64748B;font-weight:600;">
                        Reset ke Bawaan
                    </button>
                </div>
            </div>

            <div id="slides-container" style="display:flex;flex-direction:column;gap:18px;margin-bottom:28px;">
                <?php foreach ($slides as $index => $slide) : 
                    $slide_type = $slide['type'] ?? 'image';
                    $slide_id = $slide['id'] ?? ('slide-' . $index);
                    $device = $slide['device_target'] ?? 'all';
                    $action_text = $slide['action_text'] ?: 'Lihat lebih lanjut';
                    $img_mobile = $slide['image_mobile_url'] ?? '';
                ?>
                <div class="ppb-slide-card" data-index="<?php echo $index; ?>" style="background:#FFFFFF;border:1px solid #CBD5E1;border-radius:10px;overflow:hidden;box-shadow:0 3px 10px rgba(0,0,0,0.04);transition:border-color 0.2s;">
                    <!-- Slide Header Bar -->
                    <div style="background:#F8FAFC;padding:12px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;gap:12px;">
                        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                            <span class="slide-handle" style="cursor:move;color:#94A3B8;font-size:18px;line-height:1;" title="Geser untuk mengatur urutan">&#9776;</span>
                            <span style="font-family:monospace;font-weight:700;font-size:13px;background:#0D3B22;color:#F5B82E;padding:3px 8px;border-radius:4px;">
                                SLIDE #<span class="slide-number-display"><?php echo $index + 1; ?></span>
                            </span>
                            <span class="slide-type-badge" style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:4px;background:#E8F0EA;color:#0D3B22;text-transform:uppercase;">
                                <?php echo esc_html($slide_type); ?>
                            </span>

                            <!-- Device Visibility Indicator Badge -->
                            <span class="slide-device-badge" style="font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:4px;font-family:monospace;<?php 
                                if ($device === 'desktop_only') echo 'background:#E0F2FE;color:#0369A1;';
                                elseif ($device === 'mobile_only') echo 'background:#FEF3C7;color:#B45309;';
                                else echo 'background:#F1F5F9;color:#475569;';
                            ?>">
                                <?php 
                                    if ($device === 'desktop_only') echo '🖥 KHUSUS DESKTOP';
                                    elseif ($device === 'mobile_only') echo '📱 KHUSUS MOBILE (HP)';
                                    else echo '🌐 SEMUA PERANGKAT';
                                ?>
                            </span>

                            <span style="font-size:13px;color:#334155;">
                                Aksi: <strong class="slide-action-preview" style="color:#0D3B22;">&ldquo;<?php echo esc_html($action_text); ?>&rdquo;</strong>
                            </span>
                        </div>
                        <div style="display:flex;align-items:center;gap:14px;">
                            <label style="font-size:12.5px;color:#334155;font-weight:600;display:flex;align-items:center;gap:6px;cursor:pointer;">
                                <input type="checkbox" name="slides[<?php echo $index; ?>][active]" value="1" <?php checked($slide['active'] ?? 1, 1); ?>>
                                <span>Tampilkan</span>
                            </label>
                            <input type="hidden" name="slides[<?php echo $index; ?>][id]" value="<?php echo esc_attr($slide_id); ?>">
                            <input type="hidden" name="slides[<?php echo $index; ?>][order]" class="slide-order-input" value="<?php echo esc_attr($slide['order'] ?? ($index + 1)); ?>">
                            <button type="button" class="btn-toggle-slide button button-small" style="font-size:11.5px;font-weight:600;">Edit &darr;</button>
                            <button type="button" class="btn-delete-slide button button-small button-link-delete" style="color:#DC2626;font-size:11.5px;">Hapus</button>
                        </div>
                    </div>

                    <!-- Slide Body Form -->
                    <div class="slide-body" style="padding:20px 24px;display:grid;grid-template-columns:1.15fr 0.85fr;gap:24px;">
                        
                        <!-- Left Column: Media Source & Device Visibility -->
                        <div>
                            <!-- Device Visibility Selection -->
                            <div style="background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:8px;padding:12px 16px;margin-bottom:18px;">
                                <label style="display:block;font-weight:700;font-size:13px;color:#166534;margin-bottom:6px;">
                                    🎯 Target Tampilan Perangkat:
                                </label>
                                <select name="slides[<?php echo $index; ?>][device_target]" class="slide-device-select" style="width:100%;max-width:420px;padding:6px 10px;border-radius:6px;border:1.5px solid #86EFAC;font-weight:600;font-size:13px;">
                                    <option value="all" <?php selected($device, 'all'); ?>>🌐 Tampil di Semua Perangkat (Desktop &amp; Mobile)</option>
                                    <option value="desktop_only" <?php selected($device, 'desktop_only'); ?>>🖥 Hanya Tampil di Layar Desktop (Laptop/PC)</option>
                                    <option value="mobile_only" <?php selected($device, 'mobile_only'); ?>>📱 Hanya Tampil di Layar HP (Smartphone / Layar Kecil)</option>
                                </select>
                                <span style="font-size:11.5px;color:#15803D;display:block;margin-top:4px;">
                                    Fitur ini memungkinkan Anda membuat slide yang berbeda untuk Desktop (misal Video Landscape) dan Mobile (misal Brosur Vertikal).
                                </span>
                            </div>

                            <div style="margin-bottom:16px;">
                                <label style="display:block;font-weight:600;font-size:13px;color:#1E293B;margin-bottom:6px;">Tipe Media:</label>
                                <select name="slides[<?php echo $index; ?>][type]" class="slide-type-select" style="width:100%;max-width:380px;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-weight:600;">
                                    <option value="video" <?php selected($slide_type, 'video'); ?>>🎥 Video (MP4 / Web Video)</option>
                                    <option value="image" <?php selected($slide_type, 'image'); ?>>🖼 Foto / Brosur Pengumuman</option>
                                    <option value="pinned_post" <?php selected($slide_type, 'pinned_post'); ?>>📌 Berita Tersemat (Tarik dari Berita)</option>
                                </select>
                            </div>

                            <!-- Pinned Post Selector (Shows when type == pinned_post) -->
                            <div class="field-pinned-post" style="margin-bottom:16px;<?php echo $slide_type === 'pinned_post' ? '' : 'display:none;'; ?>">
                                <label style="display:block;font-weight:600;font-size:13px;color:#0D3B22;margin-bottom:6px;">Pilih Berita dari Database:</label>
                                <select name="slides[<?php echo $index; ?>][pinned_post_id]" class="pinned-post-select" style="width:100%;max-width:440px;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                                    <option value="">-- Pilih Berita --</option>
                                    <?php foreach ($recent_posts as $p) : ?>
                                        <option value="<?php echo $p->ID; ?>" <?php selected($slide['pinned_post_id'] ?? '', $p->ID); ?> data-title="<?php echo esc_attr($p->post_title); ?>" data-url="<?php echo esc_url(get_permalink($p->ID)); ?>" data-thumb="<?php echo esc_url(get_the_post_thumbnail_url($p->ID, 'large') ?: ''); ?>">
                                            <?php echo esc_html($p->post_title); ?> (<?php echo get_the_date('d M Y', $p->ID); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <span style="font-size:11.5px;color:#64748B;display:block;margin-top:4px;">Foto sampul dan link berita akan otomatis diisikan.</span>
                            </div>

                            <!-- Video URL Field -->
                            <div class="field-video-url" style="margin-bottom:16px;<?php echo $slide_type === 'video' ? '' : 'display:none;'; ?>">
                                <label style="display:block;font-weight:600;font-size:13px;color:#1E293B;margin-bottom:6px;">Tautan Video (YouTube, Google Drive, atau Direct MP4):</label>
                                <div style="display:flex;gap:8px;max-width:480px;">
                                    <input type="text" name="slides[<?php echo $index; ?>][video_url]" class="slide-video-url" value="<?php echo esc_attr($slide['video_url'] ?? ''); ?>" placeholder="https://www.youtube.com/watch?v=... atau https://drive.google.com/..." style="flex:1;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                                    <button type="button" class="btn-select-video button" style="border-radius:6px;">Pilih Video</button>
                                </div>
                                <span style="font-size:11.5px;color:#64748B;display:block;margin-top:4px;">
                                    💡 <strong>Bebas Batas Upload 2MB:</strong> Cukup masukkan link <strong>YouTube</strong> (contoh: <code>https://youtu.be/...</code>) atau <strong>Google Drive</strong> (contoh: <code>https://drive.google.com/file/d/.../view</code>). Video akan otomatis tampil sinematik penuh layar tanpa perlu diupload ke WordPress!
                                </span>
                            </div>

                            <!-- Desktop Image Field -->
                            <div class="field-image-url" style="margin-bottom:16px;">
                                <label style="display:block;font-weight:600;font-size:13px;color:#1E293B;margin-bottom:6px;">
                                    <span class="image-field-label">File Media Utama / Desktop (Landscape):</span>
                                </label>
                                <span style="font-size:11.5px;color:#059669;display:block;margin-bottom:8px;font-weight:500;">
                                    💡 <strong>Bebas Batas 2MB (Mendukung Google Drive):</strong> Anda dapat memasukkan link Google Drive (misal: <code>https://drive.google.com/file/d/.../view</code>). Sistem otomatis mengonversinya menjadi gambar langsung. <em>(Pastikan akses file di Drive diatur ke "Siapa saja yang memiliki link")</em>.
                                </span>
                                <div style="display:flex;gap:12px;align-items:flex-start;">
                                    <div class="image-preview-box" style="width:130px;height:80px;background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                        <?php if (!empty($slide['image_url'])) : ?>
                                            <img src="<?php echo esc_url($slide['image_url']); ?>" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else : ?>
                                            <span style="font-size:10px;color:#94A3B8;">Preview</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;">
                                        <input type="text" name="slides[<?php echo $index; ?>][image_url]" class="slide-image-url" value="<?php echo esc_attr($slide['image_url'] ?? ''); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;margin-bottom:6px;" placeholder="URL file gambar desktop...">
                                        <button type="button" class="btn-select-image button" style="border-radius:6px;">Pilih Gambar Desktop</button>
                                    </div>
                                </div>
                            </div>

                            <!-- Optional Mobile Portrait Image Field -->
                            <div class="field-image-mobile-url" style="margin-bottom:16px;background:#FAF8F5;border:1px dashed #CBD5E1;padding:12px 14px;border-radius:8px;">
                                <label style="display:block;font-weight:600;font-size:13px;color:#B45309;margin-bottom:6px;">
                                    📱 Gambar Khusus Mobile / HP (Opsional - Brosur Portrait):
                                </label>
                                <div style="display:flex;gap:12px;align-items:flex-start;">
                                    <div class="image-mobile-preview-box" style="width:70px;height:90px;background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;">
                                        <?php if (!empty($img_mobile)) : ?>
                                            <img src="<?php echo esc_url($img_mobile); ?>" style="width:100%;height:100%;object-fit:cover;">
                                        <?php else : ?>
                                            <span style="font-size:9.5px;color:#94A3B8;text-align:center;">Portrait HP</span>
                                        <?php endif; ?>
                                    </div>
                                    <div style="flex:1;">
                                        <input type="text" name="slides[<?php echo $index; ?>][image_mobile_url]" class="slide-image-mobile-url" value="<?php echo esc_attr($img_mobile); ?>" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;margin-bottom:6px;" placeholder="URL brosur vertikal khusus HP...">
                                        <button type="button" class="btn-select-image-mobile button" style="border-radius:6px;">Pilih Gambar Mobile</button>
                                        <span style="font-size:11px;color:#64748B;display:block;margin-top:3px;">
                                            Jika diisi, pengunjung yang membuka dari ponsel pintar akan melihat brosur portrait ini.
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Interactive Link & Action Prompt Setup -->
                        <div style="background:#FAF8F5;border:1.5px solid #E5E7EB;border-radius:8px;padding:18px 20px;">
                            <div style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;letter-spacing:0.08em;margin-bottom:10px;">
                                PENGATURAN AKSI KLIK &amp; TAUTAN (LINK)
                            </div>
                            
                            <div style="margin-bottom:16px;">
                                <label style="display:block;font-weight:600;font-size:13px;color:#1E293B;margin-bottom:4px;">
                                    Tautan URL Tujuan (Link saat diklik):
                                </label>
                                <input type="text" name="slides[<?php echo $index; ?>][target_url]" class="slide-target-url" value="<?php echo esc_attr($slide['target_url'] ?? ''); ?>" placeholder="https://... atau /berita/... (Boleh dikosongkan jika video profil)" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                                <span style="font-size:11.5px;color:#64748B;display:block;margin-top:4px;">
                                    Jika diisi, saat pengunjung menyentuh/mengklik gambar ini, akan memunculkan tombol konfirmasi menuju link tersebut.
                                </span>
                            </div>

                            <div style="margin-bottom:16px;">
                                <label style="display:block;font-weight:600;font-size:13px;color:#1E293B;margin-bottom:4px;">
                                    Teks Tombol yang Muncul saat Dipencet:
                                </label>
                                <input type="text" name="slides[<?php echo $index; ?>][action_text]" class="slide-action-text-input" value="<?php echo esc_attr($action_text); ?>" placeholder="Lihat lebih lanjut" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13.5px;font-weight:600;color:#0D3B22;">
                                <span style="font-size:11.5px;color:#64748B;display:block;margin-top:4px;">
                                    Default: <strong>Lihat lebih lanjut</strong>. Anda juga bisa menulis: <em>&ldquo;Baca berita selengkapnya &rarr;&rdquo;</em> atau <em>&ldquo;Daftar PMB sekarang &rarr;&rdquo;</em>.
                                </span>
                            </div>

                            <div style="background:#FFFFFF;border:1px solid #E2E8F0;border-radius:6px;padding:12px 14px;font-size:12px;color:#475569;line-height:1.45;">
                                💡 <strong>Fitur Anti Salah-Klik:</strong> Saat pengunjung pertama kali mengeklik/menyentuh media, tidak akan langsung berpindah halaman, melainkan memunculkan tombol konfirmasi besar di tengah layar dengan teks di atas. Pengunjung mengklik tombol tersebut untuk menuju link.
                            </div>
                        </div>

                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Sticky Save Floating Bar -->
            <div style="position:sticky;bottom:20px;background:#0D3B22;border:1px solid #1E5128;border-radius:10px;padding:14px 24px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 12px 32px rgba(0,0,0,0.25);z-index:99;">
                <div style="color:rgba(255,255,255,0.85);font-size:13.5px;">
                    Simpan perubahan agar spotlight di beranda segera diperbarui.
                </div>
                <div style="display:flex;gap:12px;">
                    <button type="submit" class="button button-primary button-hero" style="background:#F5B82E;color:#0D3B22;font-weight:700;border:none;border-radius:6px;padding:8px 24px;font-size:14px;cursor:pointer;height:auto;">
                        Simpan Semua Perubahan
                    </button>
                </div>
            </div>

        </form>
    </div>

    <!-- JavaScript Interactive Admin Logic -->
    <script>
    jQuery(document).ready(function($) {
        // Toggle slide body collapse
        $(document).on('click', '.btn-toggle-slide', function() {
            var body = $(this).closest('.ppb-slide-card').find('.slide-body');
            body.slideToggle(180);
            $(this).html(body.is(':visible') ? 'Tutup &uarr;' : 'Edit &darr;');
        });

        // Delete slide
        $(document).on('click', '.btn-delete-slide', function() {
            if (confirm('Hapus slide ini dari spotlight?')) {
                $(this).closest('.ppb-slide-card').fadeOut(200, function() {
                    $(this).remove();
                    updateSlideNumbers();
                });
            }
        });

        // Device target change handler
        $(document).on('change', '.slide-device-select', function() {
            var val = $(this).val();
            var badge = $(this).closest('.ppb-slide-card').find('.slide-device-badge');
            if (val === 'desktop_only') {
                badge.text('🖥 KHUSUS DESKTOP').css({background: '#E0F2FE', color: '#0369A1'});
            } else if (val === 'mobile_only') {
                badge.text('📱 KHUSUS MOBILE (HP)').css({background: '#FEF3C7', color: '#B45309'});
            } else {
                badge.text('🌐 SEMUA PERANGKAT').css({background: '#F1F5F9', color: '#475569'});
            }
        });

        // Type select change handler
        $(document).on('change', '.slide-type-select', function() {
            var card = $(this).closest('.ppb-slide-card');
            var val = $(this).val();
            card.find('.slide-type-badge').text(val);
            if (val === 'video') {
                card.find('.field-video-url').slideDown(150);
                card.find('.field-pinned-post').slideUp(150);
            } else if (val === 'pinned_post') {
                card.find('.field-video-url').slideUp(150);
                card.find('.field-pinned-post').slideDown(150);
            } else {
                card.find('.field-video-url').slideUp(150);
                card.find('.field-pinned-post').slideUp(150);
            }
        });

        // Pinned post select handler -> auto populate fields
        $(document).on('change', '.pinned-post-select', function() {
            var opt = $(this).find('option:selected');
            if (opt.val()) {
                var card = $(this).closest('.ppb-slide-card');
                var url = opt.data('url');
                var thumb = opt.data('thumb');

                if (url) {
                    card.find('.slide-target-url').val(url);
                }
                if (thumb) {
                    card.find('.slide-image-url').val(thumb);
                    card.find('.image-preview-box').html('<img src="' + thumb + '" style="width:100%;height:100%;object-fit:cover;">');
                }
            }
        });

        // Live Preview for Manual Input / Pasted Image URLs (supports Google Drive links)
        $(document).on('input paste change', '.slide-image-url', function() {
            var val = $(this).val().trim();
            var card = $(this).closest('.ppb-slide-card');
            if (!val) return;
            var m = val.match(/drive\.google\.com\/(?:file\/d\/|open\?id=|uc\?id=)([a-zA-Z0-9_-]{20,})/i);
            var previewUrl = m ? ('https://lh3.googleusercontent.com/d/' + m[1] + '=w800') : val;
            card.find('.image-preview-box').html('<img src="' + previewUrl + '" style="width:100%;height:100%;object-fit:cover;" onerror="this.onerror=null;this.parentElement.innerHTML=\'<span style=\\'font-size:9.5px;color:#EF4444;text-align:center;padding:4px;display:block;line-height:1.2;\\'>Gagal memuat.<br>Periksa izin link Drive (Set Publik).</span>\';">');
                }
                if (!card.find('.slide-action-text-input').val()) {
                    card.find('.slide-action-text-input').val('Lihat lebih lanjut');
                }
            }
        });

        // Action text live preview in header
        $(document).on('input', '.slide-action-text-input', function() {
            var val = $(this).val() || 'Lihat lebih lanjut';
            $(this).closest('.ppb-slide-card').find('.slide-action-preview').text('“' + val + '”');
        });

        // Media Library Frame for Desktop Image
        $(document).on('click', '.btn-select-image', function(e) {
            e.preventDefault();
            var btn = $(this);
            var card = btn.closest('.ppb-slide-card');
            var frame = wp.media({
                title: 'Pilih Foto / Banner Desktop',
                button: { text: 'Gunakan Gambar Ini' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-image-url').val(attachment.url);
                card.find('.image-preview-box').html('<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        // Media Library Frame for Mobile Image
        $(document).on('click', '.btn-select-image-mobile', function(e) {
            e.preventDefault();
            var btn = $(this);
            var card = btn.closest('.ppb-slide-card');
            var frame = wp.media({
                title: 'Pilih Brosur Vertikal / Gambar Mobile',
                button: { text: 'Gunakan Gambar Mobile Ini' },
                multiple: false,
                library: { type: 'image' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-image-mobile-url').val(attachment.url);
                card.find('.image-mobile-preview-box').html('<img src="' + attachment.url + '" style="width:100%;height:100%;object-fit:cover;">');
            });
            frame.open();
        });

        // Media Library Frame for Video
        $(document).on('click', '.btn-select-video', function(e) {
            e.preventDefault();
            var btn = $(this);
            var card = btn.closest('.ppb-slide-card');
            var frame = wp.media({
                title: 'Pilih Video MP4',
                button: { text: 'Gunakan Video Ini' },
                multiple: false,
                library: { type: 'video' }
            });
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                card.find('.slide-video-url').val(attachment.url);
            });
            frame.open();
        });

        // Add new slide button
        $('#btn-add-slide').on('click', function() {
            var count = $('.ppb-slide-card').length;
            var newIdx = count;
            var template = `
            <div class="ppb-slide-card" data-index="${newIdx}" style="background:#FFFFFF;border:1.5px solid #0D3B22;border-radius:10px;overflow:hidden;box-shadow:0 3px 10px rgba(0,0,0,0.04);margin-top:10px;">
                <div style="background:#F8FAFC;padding:12px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;">
                    <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                        <span class="slide-handle" style="cursor:move;color:#94A3B8;font-size:18px;">&#9776;</span>
                        <span style="font-family:monospace;font-weight:700;font-size:13px;background:#0D3B22;color:#F5B82E;padding:3px 8px;border-radius:4px;">
                            SLIDE #<span class="slide-number-display">${count + 1}</span>
                        </span>
                        <span class="slide-type-badge" style="font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:4px;background:#E8F0EA;color:#0D3B22;text-transform:uppercase;">IMAGE</span>
                        <span class="slide-device-badge" style="font-size:11.5px;font-weight:700;padding:3px 10px;border-radius:4px;font-family:monospace;background:#F1F5F9;color:#475569;">🌐 SEMUA PERANGKAT</span>
                        <span style="font-size:13px;color:#334155;">
                            Aksi: <strong class="slide-action-preview" style="color:#0D3B22;">&ldquo;Lihat lebih lanjut&rdquo;</strong>
                        </span>
                    </div>
                    <div style="display:flex;align-items:center;gap:14px;">
                        <label style="font-size:12.5px;color:#334155;font-weight:600;display:flex;align-items:center;gap:6px;">
                            <input type="checkbox" name="slides[${newIdx}][active]" value="1" checked>
                            <span>Tampilkan</span>
                        </label>
                        <input type="hidden" name="slides[${newIdx}][id]" value="slide-${Date.now()}">
                        <input type="hidden" name="slides[${newIdx}][order]" class="slide-order-input" value="${count + 1}">
                        <button type="button" class="btn-toggle-slide button button-small">Tutup &uarr;</button>
                        <button type="button" class="btn-delete-slide button button-small button-link-delete" style="color:#DC2626;">Hapus</button>
                    </div>
                </div>
                <div class="slide-body" style="padding:20px 24px;display:grid;grid-template-columns:1.15fr 0.85fr;gap:24px;">
                    <div>
                        <div style="background:#F0FDF4;border:1.5px solid #BBF7D0;border-radius:8px;padding:12px 16px;margin-bottom:18px;">
                            <label style="display:block;font-weight:700;font-size:13px;color:#166534;margin-bottom:6px;">
                                🎯 Target Tampilan Perangkat:
                            </label>
                            <select name="slides[${newIdx}][device_target]" class="slide-device-select" style="width:100%;max-width:420px;padding:6px 10px;border-radius:6px;border:1.5px solid #86EFAC;font-weight:600;font-size:13px;">
                                <option value="all" selected>🌐 Tampil di Semua Perangkat (Desktop &amp; Mobile)</option>
                                <option value="desktop_only">🖥 Hanya Tampil di Layar Desktop (Laptop/PC)</option>
                                <option value="mobile_only">📱 Hanya Tampil di Layar HP (Smartphone / Layar Kecil)</option>
                            </select>
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:6px;">Tipe Media:</label>
                            <select name="slides[${newIdx}][type]" class="slide-type-select" style="width:100%;max-width:380px;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                                <option value="image" selected>🖼 Foto / Brosur Pengumuman</option>
                                <option value="video">🎥 Video (MP4 / Web Video)</option>
                                <option value="pinned_post">📌 Berita Tersemat (Tarik dari Berita)</option>
                            </select>
                        </div>
                        <div class="field-video-url" style="margin-bottom:16px;display:none;">
                            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:6px;">URL File Video MP4:</label>
                            <div style="display:flex;gap:8px;">
                                <input type="text" name="slides[${newIdx}][video_url]" class="slide-video-url" style="flex:1;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;">
                                <button type="button" class="btn-select-video button">Pilih Video</button>
                            </div>
                        </div>
                        <div class="field-image-url" style="margin-bottom:16px;">
                            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:6px;">
                                <span class="image-field-label">File Media Utama / Desktop (Landscape):</span>
                            </label>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <div class="image-preview-box" style="width:130px;height:80px;background:#F1F5F9;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:10px;color:#94A3B8;">Preview</span>
                                </div>
                                <div style="flex:1;">
                                    <input type="text" name="slides[${newIdx}][image_url]" class="slide-image-url" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;margin-bottom:6px;">
                                    <button type="button" class="btn-select-image button">Pilih Gambar Desktop</button>
                                </div>
                            </div>
                        </div>
                        <div class="field-image-mobile-url" style="margin-bottom:16px;background:#FAF8F5;border:1px dashed #CBD5E1;padding:12px 14px;border-radius:8px;">
                            <label style="display:block;font-weight:600;font-size:13px;color:#B45309;margin-bottom:6px;">
                                📱 Gambar Khusus Mobile / HP (Opsional - Brosur Portrait):
                            </label>
                            <div style="display:flex;gap:12px;align-items:flex-start;">
                                <div class="image-mobile-preview-box" style="width:70px;height:90px;background:#FFFFFF;border:1px dashed #CBD5E1;border-radius:6px;display:flex;align-items:center;justify-content:center;">
                                    <span style="font-size:9.5px;color:#94A3B8;text-align:center;">Portrait HP</span>
                                </div>
                                <div style="flex:1;">
                                    <input type="text" name="slides[${newIdx}][image_mobile_url]" class="slide-image-mobile-url" style="width:100%;padding:6px 10px;border-radius:6px;border:1px solid #CBD5E1;margin-bottom:6px;" placeholder="URL brosur vertikal khusus HP...">
                                    <button type="button" class="btn-select-image-mobile button">Pilih Gambar Mobile</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="background:#FAF8F5;border:1.5px solid #E5E7EB;border-radius:8px;padding:18px 20px;">
                        <div style="font-family:monospace;font-size:11px;font-weight:700;color:#0D3B22;letter-spacing:0.08em;margin-bottom:10px;">
                            PENGATURAN AKSI KLIK &amp; TAUTAN (LINK)
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Tautan URL Tujuan:</label>
                            <input type="text" name="slides[${newIdx}][target_url]" class="slide-target-url" placeholder="https://..." style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13px;">
                        </div>
                        <div style="margin-bottom:16px;">
                            <label style="display:block;font-weight:600;font-size:13px;margin-bottom:4px;">Teks Tombol saat Dipencet:</label>
                            <input type="text" name="slides[${newIdx}][action_text]" class="slide-action-text-input" value="Lihat lebih lanjut" style="width:100%;padding:7px 10px;border-radius:6px;border:1px solid #CBD5E1;font-size:13.5px;font-weight:600;color:#0D3B22;">
                        </div>
                    </div>
                </div>
            </div>`;
            $('#slides-container').append(template);
            $('html, body').animate({ scrollTop: $('#slides-container .ppb-slide-card:last').offset().top - 80 }, 300);
        });

        // Reset to default button
        $('#btn-reset-default').on('click', function() {
            if (confirm('Apakah Anda yakin ingin mengembalikan slide spotlight ke susunan standar?')) {
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
