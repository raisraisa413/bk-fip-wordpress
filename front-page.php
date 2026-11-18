<?php
/**
 * The template for displaying the Front Page (Landing Page)
 * "Editorial Calm & Progressive Disclosure" — Forest Green & Royal Gold
 * Full-Screen 100vh Spotlight Hero + UGM-Inspired Sections + 1160px Editorial Container
 */
get_header();

$cfg = function_exists('bk_fip_get_homepage_config') ? bk_fip_get_homepage_config() : array();

// Helper for portal/mega SVG icons
if (!function_exists('bk_home_svg_icon')) {
    function bk_home_svg_icon($name, $size = 20) {
        $px = intval($size) ?: 20;
        $icons = array(
            'award'         => '<circle cx="12" cy="8" r="6"/><path d="M15.477 12.89 17 22l-5-3-5 3 1.523-9.11"/>',
            'users'         => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
            'mail'          => '<path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/>',
            'file-text'     => '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>',
            'lab'           => '<path d="M9 3h6"/><path d="M10 3v6l-4.5 7.5A2 2 0 0 0 7.2 19.5h9.6a2 2 0 0 0 1.7-3L14 9V3"/>',
            'flask-conical' => '<path d="M9 3h6"/><path d="M10 3v6l-4.5 7.5A2 2 0 0 0 7.2 19.5h9.6a2 2 0 0 0 1.7-3L14 9V3"/>',
            'book'          => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'library'       => '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
            'shield'        => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
            'compass'       => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
            'layers'        => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/>',
            'globe'         => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/>',
        );
        $path = $icons[$name] ?? $icons['award'];
        return '<svg width="' . esc_attr($px) . '" height="' . esc_attr($px) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $path . '</svg>';
    }
}

// Spotlight slides & settings
$raw_spotlight_slides = function_exists('ppb_get_spotlight_slides') ? ppb_get_spotlight_slides() : array();
$spotlight_settings = function_exists('ppb_get_spotlight_settings') ? ppb_get_spotlight_settings() : array(
    'autoplay'           => '1',
    'speed'              => 8,
    'fit_mode'           => 'cover',
    'height_mode'        => 'fullscreen',
    'mobile_height_mode' => 'fullscreen',
);
if (empty($raw_spotlight_slides) && function_exists('ppb_get_default_spotlight_slides')) {
    $raw_spotlight_slides = ppb_get_default_spotlight_slides();
}
$spotlight_slides = array_values(array_filter($raw_spotlight_slides, function($s) {
    return !isset($s['active']) || !empty($s['active']);
}));
if (empty($spotlight_slides) && !empty($raw_spotlight_slides)) {
    $spotlight_slides = array($raw_spotlight_slides[0]);
}
$total_slides = count($spotlight_slides);

// Stats, Degrees, Video Chapters, Portal Cards from $cfg
$stats_list     = $cfg['stats'] ?? array();
$degrees_map    = $cfg['degrees'] ?? array();
$video_chapters = $cfg['video_chapters'] ?? array();
$portal_cards   = $cfg['portal_cards'] ?? array();

$kajur_quote   = $cfg['kajur_quote'] ?? 'Pendidikan konseling di FIP UNM dirancang untuk melahirkan sarjana, magister, dan doktor yang berintegritas moral, peka terhadap keragaman sosio-kultural, serta adaptif memanfaatkan inovasi psikoedukasi dan telekonseling.';
$kajur_nama    = $cfg['kajur_nama'] ?? 'Dr. Suciani Latif, M.Pd.';
$kajur_jabatan = $cfg['kajur_jabatan'] ?? 'KETUA JURUSAN PPB FIP UNM';
$kajur_link    = $cfg['kajur_link'] ?? home_url('/profil/struktur/');

$admisi_kicker     = $cfg['admisi_kicker'] ?? 'Admisi UNM & BK-Connect';
$admisi_title      = $cfg['admisi_title'] ?? 'Penerimaan Mahasiswa Baru & Portal Akademik';
$admisi_desc       = $cfg['admisi_desc'] ?? 'Jalur SNBP, SNBT, Ujian Mandiri (S1), serta Pascasarjana S2 & S3 Bimbingan dan Konseling — terhubung ke SIPADU & Asesmen BK-Connect.';
$admisi_btn1_label = $cfg['admisi_btn1_label'] ?? 'Pelajari Profil Prodi →';
$admisi_btn1_url   = $cfg['admisi_btn1_url'] ?? home_url('/profil/visi-misi/');
$admisi_btn2_label = $cfg['admisi_btn2_label'] ?? 'Masuk BK-Connect ↗';
$admisi_btn2_url   = $cfg['admisi_btn2_url'] ?? 'https://bk-connect-fipunm.vercel.app/auth';
?>
<main id="bk-front-page" class="bk-sans" data-wfie-page-locked="true" data-ppb-admin-label="Teks &amp; Seksi Beranda" data-ppb-admin-url="<?php echo esc_url(admin_url('admin.php?page=bk-pengaturan-beranda')); ?>" style="background-color: var(--bg-canvas, #FAF8F5); margin-top: 0; padding-top: 0;">

    <!-- =====================================================================
         SECTION 1: FULL-SCREEN 100VH SPOTLIGHT HERO & MEDIA SHOWCASE
         ===================================================================== -->
    <section class="bk-spotlight-hero ppb-admin-managed-zone" id="hero_spotlight" data-ppb-admin-label="Spotlight Video &amp; Slide Beranda" data-ppb-admin-url="<?php echo esc_url(admin_url('admin.php?page=ppb-spotlight-beranda')); ?>" aria-label="Sorotan Utama Jurusan PPB BK FIP UNM" data-autoplay="<?php echo esc_attr($spotlight_settings['autoplay'] ?? '1'); ?>" data-speed="<?php echo esc_attr($spotlight_settings['speed'] ?? 8); ?>" data-fit-mode="<?php echo esc_attr($spotlight_settings['fit_mode'] ?? 'cover'); ?>">
        <div class="bk-spotlight-grid-overlay" aria-hidden="true"></div>

        <div class="bk-spotlight-track" id="bk-sp-track" role="region" aria-roledescription="carousel" aria-label="Sorotan Utama BK FIP UNM">
            <?php foreach ($spotlight_slides as $s_idx => $sl) :
                $is_first     = ($s_idx === 0);
                $h_tag        = $is_first ? 'h1' : 'h2';
                $s_type       = $sl['type'] ?? 'image';
                $s_device     = $sl['device_target'] ?? 'all';
                $mode_tag     = !empty($sl['mode_tag']) ? $sl['mode_tag'] : ($s_type === 'video' ? 'VIDEO PROFIL' : ($s_type === 'card' ? 'AKREDITASI UNGGUL' : ($s_type === 'pinned_post' ? 'WARTA UTAMA' : 'SOROTAN UTAMA')));
                $kicker       = !empty($sl['kicker']) ? $sl['kicker'] : ('Sorotan 0' . ($s_idx + 1) . ' · ' . $mode_tag);
                $eyebrow      = !empty($sl['eyebrow']) ? $sl['eyebrow'] : 'AKREDITASI UNGGUL · LAMDIK';

                // Pinned post fallback when admin chooses Berita Tersemat
                $pinned_title = '';
                $pinned_link  = '';
                $pinned_thumb = '';
                if ($s_type === 'pinned_post' && !empty($sl['pinned_post_id']) && function_exists('get_post')) {
                    $pp_obj = get_post((int) $sl['pinned_post_id']);
                    if ($pp_obj && $pp_obj->post_status === 'publish') {
                        $pinned_title = get_the_title($pp_obj->ID);
                        $pinned_link  = get_permalink($pp_obj->ID);
                        if (function_exists('get_the_post_thumbnail_url')) {
                            $pinned_thumb = (string) get_the_post_thumbnail_url($pp_obj->ID, 'large');
                        }
                    }
                }

                $headline_fallback = $pinned_title ?: (!empty($sl['caption_title']) ? $sl['caption_title'] : (($is_first || $s_type === 'card') ? 'Membentuk Konselor Humanis, Adaptif, dan Berintegritas.' : ''));
                $headline     = !empty($sl['headline']) ? $sl['headline'] : $headline_fallback;
                $summary_fallback = ($is_first || $s_type === 'card') ? 'Jurusan PPB / Prodi Bimbingan & Konseling FIP UNM memadukan sains perilaku, kearifan Bugis-Makassar, dan teknologi asesmen sejak 1969.' : '';
                $summary      = !empty($sl['summary']) ? $sl['summary'] : $summary_fallback;
                $action_text  = !empty($sl['action_text']) ? $sl['action_text'] : 'Jelajahi Selengkapnya';
                $target_url   = !empty($sl['target_url']) ? $sl['target_url'] : ($pinned_link ?: home_url('/akademik/kurikulum/'));
                $prompt_text  = !empty($sl['prompt_text']) ? $sl['prompt_text'] : $action_text;
                $prompt_url   = !empty($sl['prompt_url']) ? $sl['prompt_url'] : $target_url;
                $metrics      = (!empty($sl['metrics']) && is_array($sl['metrics'])) ? $sl['metrics'] : array(
                    array('label' => 'S1 Sarjana',  'value' => 'SK 742/SK/LAMDIK (s.d. 2028)'),
                    array('label' => 'S2 Magister', 'value' => 'SK 412/SK/LAMDIK (Unggul)'),
                    array('label' => 'S3 Doktoral', 'value' => 'Baik Sekali · Riset Indigenos'),
                );

                $raw_img = !empty($sl['image_url']) ? $sl['image_url'] : $pinned_thumb;
                $img_url = !empty($raw_img)
                    ? (function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($raw_img) : $raw_img)
                    : (get_template_directory_uri() . '/assets/img/hero-campus.jpg');
                $img_gd_id = (!empty($raw_img) && function_exists('bk_extract_gdrive_id')) ? bk_extract_gdrive_id($raw_img) : '';
                $img_gd_fallback = $img_gd_id !== '' ? ('https://lh3.googleusercontent.com/d/' . $img_gd_id . '=w1920') : '';

                $raw_mob = $sl['image_mobile_url'] ?? '';
                $mob_url = !empty($raw_mob)
                    ? (function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($raw_mob) : $raw_mob)
                    : '';
                $video_url_raw = trim((string)($sl['video_url'] ?? ''));
                $video_url = '';
                if ($video_url_raw !== '') {
                    if (preg_match('#^(https?:)?//#i', $video_url_raw)) {
                        $video_url = $video_url_raw;
                    } else {
                        $rel_vid = ltrim($video_url_raw, '/');
                        if (file_exists(get_template_directory() . '/' . $rel_vid)) {
                            $video_url = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($video_url_raw) : (get_template_directory_uri() . '/' . $rel_vid);
                        }
                    }
                }

                $s_id = $sl['id'] ?? ('slide-' . $s_idx);
                $default_dm = ($s_type === 'image' && $s_id !== 'slide-laboratorium-bk') ? 'poster' : 'editorial';
                $display_mode = !empty($sl['display_mode']) ? $sl['display_mode'] : $default_dm;
                if (($spotlight_settings['fit_mode'] ?? 'cover') === 'contain' && in_array($s_type, array('image', 'pinned_post'), true)) {
                    $display_mode = 'poster';
                }
                $is_poster_mode = (in_array($s_type, array('image', 'pinned_post'), true) && $display_mode === 'poster');
                $mobile_layout  = !empty($sl['mobile_layout']) ? $sl['mobile_layout'] : 'stacked';
                $mobile_ov_cls  = ($is_poster_mode && $mobile_layout === 'overlay') ? 'is-mobile-overlay' : '';

                $has_no_copy = ($headline === '' && $summary === '');
                $device_cls = '';
                if ($s_device === 'desktop_only') $device_cls = 'bk-device-desktop-only';
                elseif ($s_device === 'mobile_only') $device_cls = 'bk-device-mobile-only';
            ?>
            <div class="bk-spotlight-slide <?php echo $is_first ? 'is-active' : ''; ?> <?php echo $is_poster_mode ? 'is-poster-mode' : ''; ?> <?php echo $mobile_ov_cls; ?> <?php echo $has_no_copy ? 'has-no-copy' : ''; ?> <?php echo esc_attr($device_cls); ?>"
                 data-sp-slide="<?php echo esc_attr($s_idx); ?>"
                 data-sp-type="<?php echo esc_attr($s_type); ?>"
                 data-sp-mode="<?php echo esc_attr($mode_tag); ?>"
                 data-display-mode="<?php echo esc_attr($display_mode); ?>"
                 data-device-target="<?php echo esc_attr($s_device); ?>">

                <?php if ($s_type === 'card') : ?>
                    <!-- Slide Variant: Full-Viewport LAMDIK / Institutional Stage Card -->
                    <div class="bk-sp-card-slide">
                        <div class="bk-sp-card-inner">
                            <div class="bk-sp-card-eyebrow"><?php echo esc_html($eyebrow); ?></div>
                            <<?php echo $h_tag; ?>><?php echo esc_html($headline); ?></<?php echo $h_tag; ?>>
                            <?php if ($summary !== '') : ?>
                            <p><?php echo esc_html($summary); ?></p>
                            <?php endif; ?>
                            <div class="bk-sp-card-metrics">
                                <?php foreach ($metrics as $m) :
                                    $m_lbl = $m['label'] ?? ($m[0] ?? '');
                                    $m_val = $m['value'] ?? ($m['val'] ?? ($m[1] ?? ''));
                                    if ($m_lbl === '' && $m_val === '') continue;
                                ?>
                                <div class="bk-sp-metric">
                                    <strong><?php echo esc_html($m_lbl); ?></strong>
                                    <span><?php echo esc_html($m_val); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="bk-sp-cta-row" style="margin-top:6px;">
                                <a href="<?php echo esc_url($target_url); ?>" class="bk-btn bk-btn-gold">
                                    <span><?php echo esc_html($action_text); ?></span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php elseif ($is_poster_mode) : ?>
                    <!-- Slide Variant: Uncropped, Undimmed Poster / Infografis Stage -->
                    <div class="bk-sp-poster-stage">
                        <div class="bk-sp-poster-media">
                            <picture>
                                <?php if (!empty($mob_url)) : ?>
                                    <source media="(max-width: 768px)" srcset="<?php echo esc_url($mob_url); ?>">
                                <?php endif; ?>
                                <img src="<?php echo esc_url($img_url); ?>"
                                     alt="<?php echo esc_attr($headline !== '' ? $headline : ($mode_tag ?: 'Poster Sorotan PPB FIP UNM')); ?>"
                                     loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                                     data-poster-zoom="<?php echo esc_attr($img_url); ?>"
                                     <?php if (!empty($mob_url)) : ?>
                                     data-poster-zoom-mobile="<?php echo esc_attr($mob_url); ?>"
                                     <?php endif; ?>
                                     data-poster-title="<?php echo esc_attr($headline !== '' ? $headline : $mode_tag); ?>"
                                     <?php if ($img_gd_fallback !== '') : ?>
                                     data-gdrive-fallback="<?php echo esc_attr($img_gd_fallback); ?>"
                                     onerror="if(this.dataset.gdriveFallback && this.src !== this.dataset.gdriveFallback){this.src=this.dataset.gdriveFallback;this.setAttribute('data-poster-zoom',this.src);}"
                                     <?php endif; ?>>
                            </picture>
                        </div>

                        <div class="bk-sp-poster-panel">
                            <div class="bk-sp-poster-kicker">
                                <span class="bk-sp-dot-live" aria-hidden="true"></span>
                                <span><?php echo esc_html($kicker); ?></span>
                            </div>
                            <?php if ($headline !== '') : ?>
                            <<?php echo $h_tag; ?> class="bk-sp-poster-title"><?php echo esc_html($headline); ?></<?php echo $h_tag; ?>>
                            <?php endif; ?>
                            <?php if ($summary !== '') : ?>
                            <p class="bk-sp-poster-lead"><?php echo esc_html($summary); ?></p>
                            <?php endif; ?>
                            <div class="bk-sp-cta-row">
                                <a href="<?php echo esc_url($target_url); ?>" class="bk-btn bk-btn-gold bk-btn-sm">
                                    <span><?php echo esc_html($action_text); ?></span>
                                    <span aria-hidden="true">&rarr;</span>
                                </a>
                                <button type="button"
                                        class="bk-btn bk-btn-glass bk-btn-sm"
                                        data-poster-zoom="<?php echo esc_attr($img_url); ?>"
                                        <?php if (!empty($mob_url)) : ?>
                                        data-poster-zoom-mobile="<?php echo esc_attr($mob_url); ?>"
                                        <?php endif; ?>
                                        data-poster-title="<?php echo esc_attr($headline !== '' ? $headline : $mode_tag); ?>">
                                    <span>Perbesar Gambar</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php else : ?>
                    <!-- Slide Variant: Full-Bleed Image / Video + Editorial Lower-Left Caption Overlay -->
                    <?php if ($s_type === 'video' && !empty($video_url) && preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $video_url)) : ?>
                        <video autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url($img_url); ?>">
                            <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
                        </video>
                    <?php else : ?>
                        <picture>
                            <?php if (!empty($mob_url)) : ?>
                                <source media="(max-width: 768px)" srcset="<?php echo esc_url($mob_url); ?>">
                            <?php endif; ?>
                            <img src="<?php echo esc_url($img_url); ?>"
                                 alt="<?php echo esc_attr($headline !== '' ? $headline : ($mode_tag ?: 'Sorotan Jurusan PPB FIP UNM')); ?>"
                                 loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>"
                                 <?php if ($img_gd_fallback !== '') : ?>
                                 data-gdrive-fallback="<?php echo esc_attr($img_gd_fallback); ?>"
                                 onerror="if(this.dataset.gdriveFallback && this.src !== this.dataset.gdriveFallback){this.src=this.dataset.gdriveFallback;}"
                                 <?php endif; ?>>
                        </picture>
                    <?php endif; ?>

                    <div class="bk-sp-scrim" aria-hidden="true"></div>

                    <div class="bk-sp-caption-overlay">
                        <div class="bk-sp-caption-inner">
                            <div class="bk-sp-caption-box">
                                <?php if ($headline !== '') : ?>
                                <<?php echo $h_tag; ?>><?php echo esc_html($headline); ?></<?php echo $h_tag; ?>>
                                <?php endif; ?>
                                <?php if ($summary !== '') : ?>
                                <p class="bk-sp-caption-lead"><?php echo esc_html($summary); ?></p>
                                <?php endif; ?>
                                <div class="bk-sp-cta-row">
                                    <a href="<?php echo esc_url($target_url); ?>" class="bk-btn bk-btn-gold">
                                        <span><?php echo esc_html($action_text); ?></span>
                                        <span aria-hidden="true">&rarr;</span>
                                    </a>
                                    <?php if ($s_type === 'video') : ?>
                                    <button type="button" class="bk-btn bk-btn-glass"
                                            data-open-vchap="0"
                                            <?php if ($video_url !== '') : ?>
                                            data-open-vurl="<?php echo esc_attr($video_url); ?>"
                                            data-open-vtitle="<?php echo esc_attr($headline !== '' ? $headline : 'Video Profil Jurusan PPB'); ?>"
                                            data-open-vbadge="<?php echo esc_attr($mode_tag); ?>"
                                            data-open-vdesc="<?php echo esc_attr($summary); ?>"
                                            data-open-vposter="<?php echo esc_attr($img_url); ?>"
                                            <?php endif; ?>>
                                        <span>Tonton Video Profil</span>
                                        <span aria-hidden="true">&#9654;</span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!$is_poster_mode) : ?>
                <!-- Top-Left Kicker Pill -->
                <div class="bk-sp-mode-tag">
                    <span class="bk-sp-dot-live" aria-hidden="true"></span>
                    <span><?php echo esc_html($kicker); ?></span>
                </div>
                <?php endif; ?>

                <!-- Top-Right Video Modal Trigger (on video slides) -->
                <?php if ($s_type === 'video') : ?>
                <button type="button" class="bk-sp-video-btn"
                        data-open-vchap="0"
                        <?php if ($video_url !== '') : ?>
                        data-open-vurl="<?php echo esc_attr($video_url); ?>"
                        data-open-vtitle="<?php echo esc_attr($headline !== '' ? $headline : 'Video Profil Jurusan PPB'); ?>"
                        data-open-vbadge="<?php echo esc_attr($mode_tag); ?>"
                        data-open-vdesc="<?php echo esc_attr($summary); ?>"
                        data-open-vposter="<?php echo esc_attr($img_url); ?>"
                        <?php endif; ?>
                        aria-label="Tonton Video Profil Jurusan">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                    <span class="bk-sp-video-btn-text">Tonton Video</span>
                </button>
                <?php endif; ?>

                <?php if (!$is_poster_mode) : ?>
                <!-- Anti-Misclick Background Shield + Centered Action Prompt Pill -->
                <div class="bk-sp-shield" data-sp-shield="<?php echo esc_attr($s_idx); ?>" title="Klik area latar untuk menampilkan tautan sorotan"></div>
                <a href="<?php echo esc_url($prompt_url); ?>" class="bk-sp-prompt">
                    <span><?php echo esc_html($prompt_text); ?></span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if ($total_slides > 1) : ?>
        <button type="button" class="bk-sp-arrow bk-sp-prev" id="bk-sp-prev" aria-label="Slide Sebelumnya">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="15 18 9 12 15 6"/></svg>
        </button>
        <button type="button" class="bk-sp-arrow bk-sp-next" id="bk-sp-next" aria-label="Slide Berikutnya">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><polyline points="9 6 15 12 9 18"/></svg>
        </button>
        <?php endif; ?>

        <!-- Bottom Control & Progress Bar -->
        <div class="bk-sp-bar">
            <div class="bk-sp-progress" id="bk-sp-prog"></div>

            <div class="bk-sp-bar-group">
                <div class="bk-sp-counter">
                    <span id="bk-sp-curr">01</span>
                    <span>/</span>
                    <span id="bk-sp-total"><?php echo sprintf('%02d', max(1, $total_slides)); ?></span>
                </div>
                <span class="bk-sp-sep">|</span>
                <div class="bk-sp-dots" id="bk-sp-dots" role="tablist" aria-label="Pilih Slide Sorotan">
                    <?php foreach ($spotlight_slides as $d_idx => $d_sl) :
                        $d_mode = $d_sl['mode_tag'] ?? ('Slide ' . ($d_idx + 1));
                    ?>
                    <button type="button" class="bk-sp-dot <?php echo $d_idx === 0 ? 'is-active' : ''; ?>" data-sp-dot="<?php echo esc_attr($d_idx); ?>" role="tab" aria-selected="<?php echo $d_idx === 0 ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr('Sorotan ' . ($d_idx + 1) . ': ' . $d_mode); ?>"></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="bk-sp-scroll-btn" id="bk-sp-down" aria-label="Gulir ke ringkasan profil dan jenjang">
                <span>Gulir ke bawah</span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3"><line x1="12" y1="5" x2="12" y2="19"/><polyline points="19 12 12 19 5 12"/></svg>
            </button>

            <div class="bk-sp-bar-group">
                <span class="bk-sp-mode-label" id="bk-sp-mode">
                    <?php echo esc_html($spotlight_slides[0]['mode_tag'] ?? 'VIDEO PROFIL'); ?>
                </span>
                <span class="bk-sp-sep bk-sp-sep-mode">|</span>
                <button type="button" class="bk-sp-Pause" id="bk-sp-tog" aria-label="Jeda Slide Otomatis">⏸ Jeda</button>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SECTION 2: 4-COLUMN ARCHITECTURAL DATA LEDGER (#beranda-intro)
         ===================================================================== -->
    <section class="bk-ledger-band" id="beranda-intro" aria-label="Fakta Utama Jurusan PPB FIP UNM">
        <div class="bk-wrap">
            <div class="bk-ledger">
                <?php foreach ($stats_list as $st_idx => $st) :
                    $tag_cls = ($st_idx === 0) ? 'bk-tag-gold' : 'bk-tag-sage';
                ?>
                <div class="bk-ledger-cell">
                    <div class="bk-ledger-top">
                        <span class="bk-ledger-code"><?php echo esc_html($st['code'] ?? ''); ?></span>
                        <span class="bk-tag <?php echo esc_attr($tag_cls); ?>"><?php echo esc_html($st['badge'] ?? ''); ?></span>
                    </div>
                    <div>
                        <div class="bk-ledger-val"><?php echo esc_html($st['number'] ?? ''); ?></div>
                        <p class="bk-ledger-desc"><?php echo esc_html($st['desc'] ?? ''); ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SECTION 3: PENDIDIKAN BERKELANJUTAN (S1 / S2 / S3 SEGMENTED TABS + KAJUR QUOTE)
         ===================================================================== -->
    <section class="bk-band bk-band-surface" id="prodi_unified_section" aria-label="Jenjang Pendidikan Sarjana hingga Doktor">
        <div class="bk-wrap">
            <div class="bk-ugm-head">
                <div class="bk-ugm-title-block">
                    <div class="bk-kicker">Pendidikan Berkelanjutan</div>
                    <h2 class="bk-section-h2">Pilih Jenjang Studi Sarjana hingga Doktor</h2>
                </div>
                <div class="bk-sec-rule" aria-hidden="true"></div>
                <div class="bk-ugm-actions">
                    <div class="bk-seg-control" id="bk-degree-tabs" role="tablist" aria-label="Pilih Jenjang Studi">
                        <?php foreach (array('s1', 's2', 's3') as $d_idx => $deg_key) :
                            $deg_item = $degrees_map[$deg_key] ?? array();
                            $deg_lbl  = !empty($deg_item['tab_label']) ? $deg_item['tab_label'] : ($deg_item['label'] ?? strtoupper($deg_key));
                        ?>
                        <button type="button" class="bk-seg-btn <?php echo $d_idx === 0 ? 'is-active' : ''; ?>" data-deg-tab="<?php echo esc_attr($deg_key); ?>" role="tab" aria-selected="<?php echo $d_idx === 0 ? 'true' : 'false'; ?>">
                            <?php echo esc_html($deg_lbl); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <?php foreach (array('s1', 's2', 's3') as $d_idx => $deg_key) :
                $dg = $degrees_map[$deg_key] ?? array();
                if (!empty($dg['kompetensi']) && is_array($dg['kompetensi'])) {
                    $komp_list = $dg['kompetensi'];
                } elseif (!empty($dg['kompetensi']) && is_string($dg['kompetensi'])) {
                    $komp_list = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $dg['kompetensi']))));
                } else {
                    $komp_list = array();
                }
                $cta_url = !empty($dg['cta_url']) ? $dg['cta_url'] : (!empty($dg['url']) ? $dg['url'] : home_url('/akademik/kurikulum/'));
                $lamdik_cert_fallback = '';
                if (function_exists('ppb_get_lamdik_settings')) {
                    $lamdik_cfg = ppb_get_lamdik_settings();
                    $lamdik_cert_fallback = $lamdik_cfg['sertifikat'][$deg_key]['cert_url']
                        ?? ($lamdik_cfg['sertifikat'][$deg_key]['sk_url'] ?? ($lamdik_cfg['folder_utama_url'] ?? ''));
                }
                $cert_url = !empty($dg['cert_url'])
                    ? $dg['cert_url']
                    : ($lamdik_cert_fallback !== '' ? $lamdik_cert_fallback : home_url('/akreditasi/sertifikat/'));
                $fokus_val = !empty($dg['fokus']) ? $dg['fokus'] : ($dg['kurikulum'] ?? '');
            ?>
            <div class="bk-prodi-panel" data-deg-panel="<?php echo esc_attr($deg_key); ?>" style="<?php echo $d_idx === 0 ? '' : 'display:none;'; ?>">
                <div>
                    <span class="bk-tag bk-tag-sage" style="margin-bottom:10px;"><?php echo esc_html($dg['kkni'] ?? ''); ?></span>
                    <h3 class="bk-font-display" style="font-size:23px;margin:0 0 8px;color:var(--text-primary, #111C16);"><?php echo esc_html($dg['title'] ?? ''); ?></h3>
                    <p class="bk-read-width" style="font-size:14.5px;color:var(--text-secondary, #2E3D35);margin:0;line-height:1.68;"><?php echo esc_html($dg['summary'] ?? ''); ?></p>
                    <?php if (!empty($komp_list)) : ?>
                    <ul class="bk-check-list">
                        <?php foreach ($komp_list as $km) : ?>
                        <li>
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><?php echo esc_html($km); ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <div class="bk-prodi-specs">
                    <ul class="bk-spec-list">
                        <li>
                            <span style="color:var(--text-muted, #55625B);">Gelar</span>
                            <strong><?php echo esc_html($dg['gelar'] ?? ''); ?></strong>
                        </li>
                        <li>
                            <span style="color:var(--text-muted, #55625B);">Beban Studi</span>
                            <strong><?php echo esc_html($dg['beban'] ?? ''); ?></strong>
                        </li>
                        <li>
                            <span style="color:var(--text-muted, #55625B);">Kurikulum &amp; Fokus</span>
                            <strong style="color:var(--unm-forest, #0D3B22);"><?php echo esc_html($fokus_val); ?></strong>
                        </li>
                        <li>
                            <span style="color:var(--text-muted, #55625B);">SK LAMDIK</span>
                            <span class="bk-mono" style="font-size:11.5px;"><?php echo esc_html($dg['sk'] ?? ''); ?></span>
                        </li>
                    </ul>
                    <div style="margin-top:16px;display:flex;gap:8px;flex-wrap:wrap;">
                        <a href="<?php echo esc_url($cta_url); ?>" class="bk-btn bk-btn-primary bk-btn-sm">
                            <span>Buka Kurikulum <?php echo esc_html($dg['code'] ?? strtoupper($deg_key)); ?></span>
                            <span aria-hidden="true">&rarr;</span>
                        </a>
                        <a href="<?php echo esc_url($cert_url); ?>" class="bk-btn bk-btn-ghost bk-btn-sm">Sertifikat</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Kajur Editorial Pull-Quote -->
            <blockquote class="bk-quote-card" style="margin-top:26px;margin-bottom:0;">
                <div class="bk-kicker" style="margin-bottom:6px;">Pesan Ketua Jurusan PPB FIP UNM</div>
                <p class="bk-quote-text">&ldquo;<?php echo esc_html($kajur_quote); ?>&rdquo;</p>
                <footer style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                    <div>
                        <strong style="font-size:13.5px;color:var(--unm-forest, #0D3B22);display:block;"><?php echo esc_html($kajur_nama); ?></strong>
                        <span class="bk-mono" style="font-size:11px;color:var(--text-muted, #55625B);"><?php echo esc_html($kajur_jabatan); ?></span>
                    </div>
                    <a href="<?php echo esc_url($kajur_link); ?>" class="bk-btn bk-btn-ghost bk-btn-sm">Sejarah &amp; Struktur Pimpinan &rarr;</a>
                </footer>
            </blockquote>
        </div>
    </section>

    <!-- =====================================================================
         SECTION 4: BERITA & AGENDA TERKINI (UGM FEATURED LEAD + 3-COL GRID)
         ===================================================================== -->
    <section class="bk-band ppb-admin-managed-zone" id="news_unified_section" data-ppb-admin-label="Warta &amp; Berita Jurusan" data-ppb-admin-url="<?php echo esc_url(admin_url('edit.php')); ?>" aria-label="Warta Jurusan dan Pengumuman">
        <div class="bk-wrap">
            <?php echo do_shortcode('[ppb_home_news count="4"]'); ?>
        </div>
    </section>

    <!-- =====================================================================
         SECTION 5: DARK FOREST VIDEO SHOWCASE BAND (#bk-video-band)
         ===================================================================== -->
    <?php
    if (!empty($video_chapters) && is_array($video_chapters)) {
        foreach ($video_chapters as $vc_i => $vc_item) {
            $raw_poster = !empty($vc_item['poster']) ? $vc_item['poster'] : (!empty($vc_item['thumb']) ? $vc_item['thumb'] : 'assets/img/hero-campus.jpg');
            $video_chapters[$vc_i]['poster'] = function_exists('bk_parse_spotlight_image_url')
                ? bk_parse_spotlight_image_url($raw_poster)
                : (function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($raw_poster) : $raw_poster);
            $video_chapters[$vc_i]['meta']   = !empty($vc_item['meta']) ? $vc_item['meta'] : ($vc_item['date'] ?? 'Profil Jurusan · Kampus Tidung');
        }
    }
    $first_ch = $video_chapters[0] ?? array(
        'num'       => '01',
        'duration'  => '03:45',
        'badge'     => 'Bagian 1 · Visi & Sejarah 1969',
        'title'     => '57 Tahun Mendidik Calon Konselor Nusantara',
        'desc'      => 'Dari Bimbingan Penyuluhan (1969) menjadi prodi terakreditasi Unggul LAMDIK untuk jenjang Sarjana dan Magister.',
        'meta'      => 'Profil Jurusan · Kampus Tidung',
        'poster'    => get_template_directory_uri() . '/assets/img/hero-campus.jpg',
        'video_url' => '',
    );
    ?>
    <section class="bk-video-band ppb-admin-managed-zone" id="bk-video-band" data-ppb-admin-label="Teks &amp; Seksi Beranda (Video Profil)" data-ppb-admin-url="<?php echo esc_url(admin_url('admin.php?page=bk-pengaturan-beranda')); ?>" aria-label="Video Profil dan Dokumentasi Kampus">
        <div class="bk-wrap">
            <div class="bk-ugm-head">
                <div class="bk-ugm-title-block">
                    <div class="bk-kicker">Dokumentasi &amp; Mengenal Prodi</div>
                    <h2 class="bk-section-h2">Video Profil &amp; Ekosistem Lab BK</h2>
                </div>
                <div class="bk-sec-rule" aria-hidden="true"></div>
                <div class="bk-ugm-actions">
                    <button type="button" class="bk-btn bk-btn-gold bk-btn-sm" id="bk-open-vid-btn" data-open-vchap="0">
                        <span>Buka Pemutar Video</span>
                        <span aria-hidden="true">&#9654;</span>
                    </button>
                </div>
            </div>

            <div class="bk-video-band-grid" id="bk-video-showcase">
                <button type="button" class="bk-video-stage-card" id="bk-vid-stage-card" data-open-vchap="0">
                    <div class="bk-video-stage-media">
                        <img id="bk-vid-stage-img" src="<?php echo esc_url($first_ch['poster'] ?? ''); ?>" alt="<?php echo esc_attr($first_ch['title'] ?? ''); ?>">
                        <div class="bk-video-stage-scrim"></div>
                        <span class="bk-video-play-ring" aria-hidden="true">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor"><polygon points="6 3 20 12 6 21 6 3"/></svg>
                        </span>
                    </div>
                    <div class="bk-video-stage-body">
                        <div class="bk-video-ch-meta">
                            <span id="bk-vid-stage-badge"><?php echo esc_html($first_ch['badge'] ?? ''); ?></span>
                            <span id="bk-vid-stage-dur">Durasi <?php echo esc_html($first_ch['duration'] ?? ''); ?></span>
                        </div>
                        <h3 class="bk-video-stage-title" id="bk-vid-stage-title"><?php echo esc_html($first_ch['title'] ?? ''); ?></h3>
                        <p class="bk-video-stage-desc" id="bk-vid-stage-desc"><?php echo esc_html($first_ch['desc'] ?? ''); ?></p>
                        <div class="bk-video-stage-foot">
                            <span id="bk-vid-stage-meta"><?php echo esc_html($first_ch['meta'] ?? ''); ?></span>
                            <span>Putar di Modal Video &#9654;</span>
                        </div>
                    </div>
                </button>

                <div class="bk-video-playlist" role="tablist" aria-label="Pilih Bab Video Profil">
                    <?php foreach ($video_chapters as $vc_idx => $vc) : ?>
                    <button type="button" class="bk-video-ch-btn <?php echo $vc_idx === 0 ? 'is-active' : ''; ?>" data-vpick="<?php echo esc_attr($vc_idx); ?>" role="tab" aria-selected="<?php echo $vc_idx === 0 ? 'true' : 'false'; ?>">
                        <span class="bk-video-ch-num"><?php echo esc_html($vc['num'] ?? sprintf('%02d', $vc_idx + 1)); ?></span>
                        <span class="bk-video-ch-body">
                            <span class="bk-video-ch-meta">
                                <span><?php echo esc_html($vc['badge'] ?? ''); ?></span>
                                <span><?php echo esc_html($vc['duration'] ?? ''); ?></span>
                            </span>
                            <div class="bk-video-ch-title"><?php echo esc_html($vc['title'] ?? ''); ?></div>
                            <div class="bk-video-ch-desc"><?php echo esc_html($vc['desc'] ?? ''); ?></div>
                        </span>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- =====================================================================
         SECTION 6: 3x2 DARK FOREST PORTAL GRID + ADMISI STRIP
         ===================================================================== -->
    <section class="bk-band bk-band-surface ppb-admin-managed-zone" id="portal_ecosystem_section" data-ppb-admin-label="Teks &amp; Seksi Beranda (Ekosistem Layanan)" data-ppb-admin-url="<?php echo esc_url(admin_url('admin.php?page=bk-pengaturan-beranda')); ?>" aria-label="Akses Cepat Layanan, Mutu dan Infrastruktur">
        <div class="bk-wrap">
            <div class="bk-ugm-head">
                <div class="bk-ugm-title-block">
                    <div class="bk-kicker">Akses Cepat Ekosistem Jurusan</div>
                    <h2 class="bk-section-h2">Layanan, Mutu &amp; Infrastruktur</h2>
                </div>
                <div class="bk-sec-rule" aria-hidden="true"></div>
                <div class="bk-ugm-actions">
                    <a href="<?php echo esc_url(home_url('/layanan/persuratan/')); ?>" class="bk-btn bk-btn-ghost bk-btn-sm">Semua Layanan &rarr;</a>
                </div>
            </div>

            <div class="bk-portal-grid">
                <?php foreach ($portal_cards as $pc) :
                    $pc_url = $pc['url'] ?? home_url('/');
                    $pc_ext = (strpos($pc_url, 'http') === 0 && strpos($pc_url, home_url()) !== 0);
                ?>
                <a href="<?php echo esc_url($pc_url); ?>" class="bk-portal-card" <?php echo $pc_ext ? 'target="_blank" rel="noopener noreferrer"' : ''; ?>>
                    <span class="bk-portal-icon"><?php echo bk_home_svg_icon($pc['icon'] ?? 'award', 20); ?></span>
                    <div>
                        <div class="bk-portal-title"><?php echo esc_html($pc['title'] ?? ''); ?></div>
                        <div class="bk-portal-sub"><?php echo esc_html($pc['subtitle'] ?? ($pc['desc'] ?? '')); ?></div>
                    </div>
                    <span class="bk-portal-arrow" aria-hidden="true">&rarr;</span>
                </a>
                <?php endforeach; ?>
            </div>

            <aside class="bk-admisi-strip" aria-label="Informasi Admisi dan BK-Connect">
                <div>
                    <div class="bk-kicker" style="margin-bottom:4px;"><?php echo esc_html($admisi_kicker); ?></div>
                    <h3 class="bk-font-display" style="font-size:20px;margin:0 0 4px;color:var(--text-primary, #111C16);"><?php echo esc_html($admisi_title); ?></h3>
                    <p style="font-size:13.5px;color:var(--text-secondary, #2E3D35);margin:0;"><?php echo esc_html($admisi_desc); ?></p>
                </div>
                <div class="bk-admisi-strip-actions">
                    <a href="<?php echo esc_url($admisi_btn1_url); ?>" class="bk-btn bk-btn-secondary bk-btn-sm"><?php echo esc_html($admisi_btn1_label); ?></a>
                    <a href="<?php echo esc_url($admisi_btn2_url); ?>" target="_blank" rel="noopener noreferrer" class="bk-btn bk-btn-primary bk-btn-sm"><?php echo esc_html($admisi_btn2_label); ?></a>
                </div>
            </aside>
        </div>
    </section>

</main>

<!-- =====================================================================
     LIGHTWEIGHT VIDEO PROFILE MODAL (#bk-video-modal)
     ===================================================================== -->
<div class="bk-backdrop" id="bk-vid-backdrop" aria-hidden="true"></div>
<div class="bk-video-modal" id="bk-video-modal" role="dialog" aria-modal="true" aria-labelledby="bk-vmod-heading">
    <div style="padding:16px 20px;border-bottom:1px solid var(--border-hairline, #E4E0D8);display:flex;justify-content:space-between;align-items:center;background:var(--bg-surface, #FFFFFF);">
        <div>
            <div class="bk-kicker" style="margin-bottom:2px;">PROFIL VIDEO JURUSAN PPB</div>
            <h3 class="bk-font-display" id="bk-vmod-heading" style="margin:0;font-size:17px;color:var(--text-primary);">Mengenal Prodi Bimbingan &amp; Konseling FIP UNM</h3>
        </div>
        <button type="button" class="bk-icon-btn" id="bk-vmod-close" aria-label="Tutup Modal Video">&#10005;</button>
    </div>
    <div class="bk-video-stage">
        <div class="bk-video-screen" id="bk-vmod-screen">
            <img id="bk-vmod-img" src="<?php echo esc_url($first_ch['poster']); ?>" alt="<?php echo esc_attr($first_ch['title']); ?>">
            <div id="bk-vmod-embed-mount" style="display:none;position:absolute;inset:0;z-index:2;background:#000;"></div>
            <div class="bk-video-overlay" id="bk-vmod-overlay">
                <span class="bk-tag bk-tag-gold" id="bk-vmod-badge" style="margin-bottom:10px;"><?php echo esc_html($first_ch['badge']); ?></span>
                <h4 class="bk-font-display" id="bk-vmod-title" style="margin:0 0 8px;font-size:21px;color:#FFFFFF;"><?php echo esc_html($first_ch['title']); ?></h4>
                <p id="bk-vmod-desc" style="margin:0 0 14px;font-size:13.5px;color:rgba(255,255,255,0.86);max-width:48ch;"><?php echo esc_html($first_ch['desc']); ?></p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;justify-content:center;">
                    <?php foreach ($video_chapters as $vc_idx => $vc) :
                        $short_lbl = ($vc['num'] ?? sprintf('%02d', $vc_idx + 1)) . '. ' . wp_trim_words($vc['title'] ?? 'Bab', 3, '');
                    ?>
                    <button type="button" class="bk-btn bk-btn-sm <?php echo $vc_idx === 0 ? 'bk-btn-gold' : 'bk-btn-secondary'; ?>" data-vchap="<?php echo esc_attr($vc_idx); ?>">
                        <?php echo esc_html($short_lbl); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <p style="margin:0;font-size:12px;color:rgba(255,255,255,0.72);">Video profil dibuka melalui modal ringan agar halaman utama tetap tenang dan cepat diakses.</p>
    </div>
</div>

<!-- =====================================================================
     POSTER / INFOGRAFIS ZOOM LIGHTBOX (#bk-poster-lightbox)
     ===================================================================== -->
<div class="bk-poster-lightbox" id="bk-poster-lightbox" role="dialog" aria-modal="true" aria-labelledby="bk-plb-title" hidden>
    <div class="bk-plb-head">
        <span id="bk-plb-title" class="bk-plb-title">Pratinjau Poster</span>
        <button type="button" class="bk-plb-close" id="bk-plb-close" aria-label="Tutup Pratinjau Poster">&#10005;</button>
    </div>
    <div class="bk-plb-body" id="bk-plb-body">
        <img id="bk-plb-img" src="" alt="">
    </div>
</div>

<script type="application/json" id="bk-video-chapters-data"><?php echo wp_json_encode($video_chapters); ?></script>
<script>
(function(){
    'use strict';

    var $ = function(id) { return document.getElementById(id); };

    // 1. Segmented Jenjang Tabs (S1 / S2 / S3)
    var degTabs = document.querySelectorAll('[data-deg-tab]');
    var degPanels = document.querySelectorAll('[data-deg-panel]');
    degTabs.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var target = this.getAttribute('data-deg-tab');
            degTabs.forEach(function(b) {
                var act = (b === btn);
                b.classList.toggle('is-active', act);
                b.setAttribute('aria-selected', act ? 'true' : 'false');
            });
            degPanels.forEach(function(p) {
                p.style.display = (p.getAttribute('data-deg-panel') === target) ? '' : 'none';
            });
        });
    });

    // 2. Video Showcase & Modal
    var vChapters = [];
    try {
        vChapters = JSON.parse(($('bk-video-chapters-data') || {}).textContent || '[]');
    } catch(e) {}
    var activeVidIdx = 0;

    function selectShowcaseChap(idx) {
        if (!vChapters.length) return;
        activeVidIdx = ((idx % vChapters.length) + vChapters.length) % vChapters.length;
        var ch = vChapters[activeVidIdx];
        if ($('bk-vid-stage-img') && ch.poster) $('bk-vid-stage-img').src = ch.poster;
        if ($('bk-vid-stage-badge')) $('bk-vid-stage-badge').textContent = ch.badge || '';
        if ($('bk-vid-stage-dur')) $('bk-vid-stage-dur').textContent = 'Durasi ' + (ch.duration || '');
        if ($('bk-vid-stage-title')) $('bk-vid-stage-title').textContent = ch.title || '';
        if ($('bk-vid-stage-desc')) $('bk-vid-stage-desc').textContent = ch.desc || '';
        if ($('bk-vid-stage-meta')) $('bk-vid-stage-meta').textContent = ch.meta || '';
        if ($('bk-vid-stage-card')) $('bk-vid-stage-card').setAttribute('data-open-vchap', String(activeVidIdx));
        if ($('bk-open-vid-btn')) $('bk-open-vid-btn').setAttribute('data-open-vchap', String(activeVidIdx));

        document.querySelectorAll('[data-vpick]').forEach(function(b) {
            var act = Number(b.getAttribute('data-vpick')) === activeVidIdx;
            b.classList.toggle('is-active', act);
            b.setAttribute('aria-selected', act ? 'true' : 'false');
        });
    }

    function renderVideoEmbed(vUrl, embedMount) {
        if (!embedMount) return;
        embedMount.innerHTML = '';
        embedMount.style.display = 'none';
        vUrl = (vUrl || '').trim();
        if (!vUrl) return;

        var ytMatch = vUrl.match(/(?:youtube\.com\/(?:watch\?v=|embed\/|shorts\/)|youtu\.be\/)([A-Za-z0-9_-]{6,})/i);
        if (ytMatch && ytMatch[1]) {
            embedMount.style.display = 'block';
            embedMount.innerHTML = '<iframe src="https://www.youtube-nocookie.com/embed/' + ytMatch[1] + '?autoplay=1&rel=0&playsinline=1" style="width:100%;height:100%;border:0;" allow="autoplay; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
            return;
        }

        var gdMatch = vUrl.match(/drive\.google\.com\/(?:file\/d\/|thumbnail\?id=|open\?id=|uc\?(?:[^&]*&)*id=)([a-zA-Z0-9_-]{10,})/i)
            || vUrl.match(/drive\.google\.com\/[^\s]*?\/d\/([a-zA-Z0-9_-]{10,})/i);
        if (gdMatch && gdMatch[1]) {
            embedMount.style.display = 'block';
            embedMount.innerHTML = '<iframe src="https://drive.google.com/file/d/' + gdMatch[1] + '/preview" style="width:100%;height:100%;border:0;" allow="autoplay; fullscreen" allowfullscreen></iframe>';
            return;
        }

        var vimMatch = vUrl.match(/vimeo\.com\/(?:video\/)?([0-9]+)/i);
        if (vimMatch && vimMatch[1]) {
            embedMount.style.display = 'block';
            embedMount.innerHTML = '<iframe src="https://player.vimeo.com/video/' + vimMatch[1] + '?autoplay=1&byline=0&title=0" style="width:100%;height:100%;border:0;" allow="autoplay; fullscreen" allowfullscreen></iframe>';
            return;
        }

        if (/\.(mp4|webm|ogg)(\?.*)?$/i.test(vUrl)) {
            embedMount.style.display = 'block';
            embedMount.innerHTML = '<video src="' + vUrl.replace(/"/g, '&quot;') + '" controls autoplay playsinline style="width:100%;height:100%;object-fit:contain;background:#000;"></video>';
        }
    }

    function setModalChap(idx) {
        if (!vChapters.length) return;
        var cIdx = ((Number(idx) % vChapters.length) + vChapters.length) % vChapters.length;
        var ch = vChapters[cIdx];
        if ($('bk-vmod-badge')) $('bk-vmod-badge').textContent = ch.badge || '';
        if ($('bk-vmod-title')) $('bk-vmod-title').textContent = ch.title || '';
        if ($('bk-vmod-desc')) $('bk-vmod-desc').textContent = ch.desc || '';
        if ($('bk-vmod-img') && ch.poster) $('bk-vmod-img').src = ch.poster;

        renderVideoEmbed(ch.video_url || '', $('bk-vmod-embed-mount'));

        document.querySelectorAll('#bk-video-modal [data-vchap]').forEach(function(b) {
            var act = Number(b.getAttribute('data-vchap')) === cIdx;
            b.classList.toggle('bk-btn-gold', act);
            b.classList.toggle('bk-btn-secondary', !act);
        });
    }

    function openCustomVideoModal(opts) {
        stopSpotlight();
        if ($('bk-vmod-badge')) $('bk-vmod-badge').textContent = opts.badge || 'VIDEO PROFIL';
        if ($('bk-vmod-title')) $('bk-vmod-title').textContent = opts.title || 'Video Spotlight';
        if ($('bk-vmod-desc')) $('bk-vmod-desc').textContent = opts.desc || '';
        if ($('bk-vmod-img') && opts.poster) $('bk-vmod-img').src = opts.poster;

        renderVideoEmbed(opts.url || '', $('bk-vmod-embed-mount'));

        document.querySelectorAll('#bk-video-modal [data-vchap]').forEach(function(b) {
            b.classList.remove('bk-btn-gold');
            b.classList.add('bk-btn-secondary');
        });
        if ($('bk-vid-backdrop')) $('bk-vid-backdrop').classList.add('is-open');
        if ($('bk-video-modal')) $('bk-video-modal').classList.add('is-open');
    }

    function openVideoModal(chapIdx) {
        stopSpotlight();
        setModalChap(chapIdx !== undefined ? chapIdx : activeVidIdx);
        if ($('bk-vid-backdrop')) $('bk-vid-backdrop').classList.add('is-open');
        if ($('bk-video-modal')) $('bk-video-modal').classList.add('is-open');
    }

    function closeVideoModal() {
        var embedMount = $('bk-vmod-embed-mount');
        if (embedMount) {
            embedMount.innerHTML = '';
            embedMount.style.display = 'none';
        }
        if ($('bk-vid-backdrop')) $('bk-vid-backdrop').classList.remove('is-open');
        if ($('bk-video-modal')) $('bk-video-modal').classList.remove('is-open');
        if (!spPaused) startSpotlight();
    }

    document.querySelectorAll('[data-vpick]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            selectShowcaseChap(Number(this.getAttribute('data-vpick') || 0));
        });
    });

    document.addEventListener('click', function(e) {
        var openBtn = e.target.closest('[data-open-vurl], [data-open-vchap]');
        if (openBtn) {
            e.preventDefault();
            var customUrl = openBtn.getAttribute('data-open-vurl') || '';
            if (customUrl) {
                openCustomVideoModal({
                    url: customUrl,
                    title: openBtn.getAttribute('data-open-vtitle') || '',
                    badge: openBtn.getAttribute('data-open-vbadge') || '',
                    desc: openBtn.getAttribute('data-open-vdesc') || '',
                    poster: openBtn.getAttribute('data-open-vposter') || ''
                });
            } else {
                openVideoModal(Number(openBtn.getAttribute('data-open-vchap') || 0));
            }
            return;
        }
        var chapBtn = e.target.closest('#bk-video-modal [data-vchap]');
        if (chapBtn) {
            e.preventDefault();
            setModalChap(Number(chapBtn.getAttribute('data-vchap') || 0));
        }
    });

    if ($('bk-vmod-close')) $('bk-vmod-close').addEventListener('click', closeVideoModal);
    if ($('bk-vid-backdrop')) $('bk-vid-backdrop').addEventListener('click', closeVideoModal);

    // 3b. Poster / Infografis Zoom Lightbox
    var posterLb = $('bk-poster-lightbox');
    var posterLbImg = $('bk-plb-img');
    var posterLbTitle = $('bk-plb-title');

    function openPosterLightbox(src, title) {
        if (!posterLb || !posterLbImg || !src) return;
        stopSpotlight();
        posterLbImg.src = src;
        posterLbImg.alt = title || 'Poster Sorotan';
        if (posterLbTitle) posterLbTitle.textContent = title || 'Pratinjau Poster';
        posterLb.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closePosterLightbox() {
        if (!posterLb || posterLb.hidden) return;
        posterLb.hidden = true;
        if (posterLbImg) posterLbImg.src = '';
        document.body.style.overflow = '';
        if (!spPaused) startSpotlight();
    }

    document.addEventListener('click', function(e) {
        var zoomTrigger = e.target.closest('[data-poster-zoom]');
        if (zoomTrigger) {
            e.preventDefault();
            var zoomSrc = zoomTrigger.getAttribute('data-poster-zoom') || '';
            if (window.innerWidth <= 768) {
                var mobZoom = zoomTrigger.getAttribute('data-poster-zoom-mobile') || '';
                if (mobZoom) {
                    zoomSrc = mobZoom;
                } else {
                    var slideEl = zoomTrigger.closest('.bk-spotlight-slide');
                    var slideImg = slideEl ? slideEl.querySelector('.bk-sp-poster-media img') : null;
                    if (slideImg && slideImg.currentSrc) zoomSrc = slideImg.currentSrc;
                }
            }
            openPosterLightbox(
                zoomSrc,
                zoomTrigger.getAttribute('data-poster-title') || ''
            );
        }
    });
    if ($('bk-plb-close')) $('bk-plb-close').addEventListener('click', closePosterLightbox);
    if ($('bk-plb-body')) {
        $('bk-plb-body').addEventListener('click', function(e) {
            if (e.target === this) closePosterLightbox();
        });
    }

    document.querySelectorAll('.bk-spotlight-slide.is-poster-mode .bk-sp-poster-media img').forEach(function(img) {
        function syncPosterOrientation() {
            var sl = img.closest('.bk-spotlight-slide');
            if (!sl || !img.naturalWidth || !img.naturalHeight) return;
            sl.classList.toggle('is-landscape-poster', img.naturalWidth > img.naturalHeight * 1.15);
        }
        if (img.complete) syncPosterOrientation();
        img.addEventListener('load', syncPosterOrientation);
    });

    // 4. Full-Screen 100vh Spotlight Carousel (active viewport-aware height)
    var heroEl = $('hero_spotlight');
    function syncSpotlightViewportHeight() {
        if (!heroEl) return;
        var vpH = (window.visualViewport && window.visualViewport.height) ? window.visualViewport.height : window.innerHeight;
        if (!vpH) return;
        var spacerEl = document.getElementById('bk-navbar-spacer');
        var navEl = document.getElementById('bk-main-navbar');
        var barEl = document.getElementById('wpadminbar');
        var navH = navEl ? navEl.offsetHeight : 88;
        var barH = (barEl && document.body && document.body.classList.contains('admin-bar')) ? barEl.offsetHeight : 0;
        var headerH = spacerEl ? Math.max(navH, spacerEl.getBoundingClientRect().height) : (navH + barH);
        var avail = Math.ceil(vpH - headerH);
        var minH = window.innerWidth <= 768 ? 440 : 380;
        var clamped = Math.max(minH, avail);
        heroEl.style.setProperty('--bk-hero-h', clamped + 'px');
    }
    syncSpotlightViewportHeight();
    window.addEventListener('resize', syncSpotlightViewportHeight);
    window.addEventListener('load', syncSpotlightViewportHeight);
    if (window.visualViewport && window.visualViewport.addEventListener) {
        window.visualViewport.addEventListener('resize', syncSpotlightViewportHeight);
    }
    var slides = heroEl ? Array.prototype.slice.call(heroEl.querySelectorAll('.bk-spotlight-slide')) : [];
    var spIdx = 0;
    var spPaused = heroEl ? (heroEl.getAttribute('data-autoplay') === '0') : false;
    var spDuration = heroEl ? Math.max(4, parseInt(heroEl.getAttribute('data-speed') || '8', 10)) * 1000 : 7500;
    var spTick = null;
    var spPromptTimer = null;
    var spStart = Date.now();

    function stopSpotlight() {
        clearInterval(spTick);
        spTick = null;
        clearTimeout(spPromptTimer);
        spPromptTimer = null;
    }

    function goSpotlight(idx) {
        if (!slides.length || !heroEl) return;
        var total = slides.length;
        var next = ((idx % total) + total) % total;
        spIdx = next;
        spStart = Date.now();

        clearTimeout(spPromptTimer);
        heroEl.querySelectorAll('.bk-sp-prompt.is-shown').forEach(function(p) {
            p.classList.remove('is-shown');
        });
        slides.forEach(function(el, i) {
            el.classList.toggle('is-active', i === next);
        });
        heroEl.querySelectorAll('[data-sp-dot]').forEach(function(d) {
            var act = Number(d.getAttribute('data-sp-dot')) === next;
            d.classList.toggle('is-active', act);
            d.setAttribute('aria-selected', act ? 'true' : 'false');
        });

        var curSlide = slides[next];
        if ($('bk-sp-curr')) $('bk-sp-curr').textContent = String(next + 1).padStart(2, '0');
        if ($('bk-sp-mode') && curSlide) $('bk-sp-mode').textContent = curSlide.getAttribute('data-sp-mode') || 'SOROTAN';
        if ($('bk-sp-prog')) $('bk-sp-prog').style.width = '0%';
    }

    function startSpotlight() {
        clearInterval(spTick);
        spTick = null;
        if (spPaused || !heroEl || slides.length <= 1) return;
        spStart = Date.now();
        if ($('bk-sp-prog')) $('bk-sp-prog').style.width = '0%';
        spTick = setInterval(function() {
            if (spPaused || !heroEl) {
                stopSpotlight();
                return;
            }
            var elapsed = Date.now() - spStart;
            var pct = Math.min(100, (elapsed / spDuration) * 100);
            if ($('bk-sp-prog')) $('bk-sp-prog').style.width = pct.toFixed(1) + '%';
            if (elapsed >= spDuration) {
                goSpotlight(spIdx + 1);
            }
        }, 80);
    }

    if (heroEl && slides.length) {
        if ($('bk-sp-prev')) {
            $('bk-sp-prev').addEventListener('click', function() {
                goSpotlight(spIdx - 1);
                if (!spPaused) startSpotlight();
            });
        }
        if ($('bk-sp-next')) {
            $('bk-sp-next').addEventListener('click', function() {
                goSpotlight(spIdx + 1);
                if (!spPaused) startSpotlight();
            });
        }
        heroEl.querySelectorAll('[data-sp-dot]').forEach(function(dot) {
            dot.addEventListener('click', function() {
                goSpotlight(Number(this.getAttribute('data-sp-dot') || 0));
                if (!spPaused) startSpotlight();
            });
        });
        if ($('bk-sp-tog')) {
            $('bk-sp-tog').addEventListener('click', function() {
                spPaused = !spPaused;
                this.textContent = spPaused ? '▶ Putar' : '⏸ Jeda';
                this.setAttribute('aria-label', spPaused ? 'Putar Slide Otomatis' : 'Jeda Slide Otomatis');
                if (spPaused) stopSpotlight();
                else startSpotlight();
            });
        }
        if ($('bk-sp-down')) {
            $('bk-sp-down').addEventListener('click', function() {
                var intro = $('beranda-intro');
                if (intro) intro.scrollIntoView({ behavior: 'smooth' });
            });
        }

        // Anti-misclick background shield -> toggles centered prompt pill
        heroEl.querySelectorAll('[data-sp-shield]').forEach(function(sh) {
            sh.addEventListener('click', function() {
                var sl = this.closest('.bk-spotlight-slide');
                var pr = sl ? sl.querySelector('.bk-sp-prompt') : null;
                if (pr) {
                    var show = !pr.classList.contains('is-shown');
                    pr.classList.toggle('is-shown', show);
                    clearTimeout(spPromptTimer);
                    if (show) {
                        spPromptTimer = setTimeout(function() {
                            pr.classList.remove('is-shown');
                        }, 3200);
                    }
                }
            });
        });

        // Touch swipe support
        var touchX = 0;
        heroEl.addEventListener('touchstart', function(e) {
            if (e.touches && e.touches[0]) touchX = e.touches[0].clientX;
        }, { passive: true });
        heroEl.addEventListener('touchend', function(e) {
            if (!e.changedTouches || !e.changedTouches[0]) return;
            var dx = e.changedTouches[0].clientX - touchX;
            if (Math.abs(dx) > 42) {
                goSpotlight(dx < 0 ? spIdx + 1 : spIdx - 1);
                if (!spPaused) startSpotlight();
            }
        }, { passive: true });

        heroEl.addEventListener('mouseenter', function() {
            if (!spPaused) {
                clearInterval(spTick);
                spTick = null;
            }
        });
        heroEl.addEventListener('mouseleave', function() {
            var vMod = $('bk-video-modal');
            if (!spPaused && (!vMod || !vMod.classList.contains('is-open'))) {
                startSpotlight();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePosterLightbox();
                closeVideoModal();
                return;
            }
            if ((e.key === 'ArrowLeft' || e.key === 'ArrowRight') && !['INPUT', 'TEXTAREA', 'SELECT'].includes((document.activeElement || {}).tagName)) {
                var vMod = $('bk-video-modal');
                if ((vMod && vMod.classList.contains('is-open')) || (posterLb && !posterLb.hidden)) return;
                var ae = document.activeElement;
                if (ae && ae.closest && ae.closest('#hero_spotlight')) {
                    e.preventDefault();
                    goSpotlight(e.key === 'ArrowRight' ? spIdx + 1 : spIdx - 1);
                    if (!spPaused) startSpotlight();
                }
            }
        });

        startSpotlight();
    }
})();
</script>

<?php get_footer(); ?>
