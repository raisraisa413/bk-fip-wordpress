<?php
/**
 * Template for Single Dosen Profile & Academic Track Record (Rekam Jejak SINTA, Riset & Pengajaran)
 * 1:1 Full-Width Scholaro Editorial Design
 */

if (!defined('ABSPATH')) exit;

get_header();

// 1. Resolve requested lecturer from query var or URL path
global $current_dosen;

if (empty($current_dosen)) {
    $req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
    $path = trim(parse_url($req_uri, PHP_URL_PATH), '/');
    $parts = explode('/', $path);
    $slug = end($parts);
    if (isset($_GET['dosen'])) {
        $slug = sanitize_text_field($_GET['dosen']);
    }

    $json_file = get_template_directory() . '/dosen-data.json';
    if (file_exists($json_file)) {
        $all_dosen = json_decode(file_get_contents($json_file), true);
        if (is_array($all_dosen)) {
            foreach ($all_dosen as $d) {
                if ($d['slug'] === $slug || (string)$d['id'] === $slug) {
                    $current_dosen = $d;
                    break;
                }
            }
        }
    }
}

// Fallback if not found
if (empty($current_dosen)) {
    wp_redirect(home_url('/profil/dosen/'));
    exit;
}

$d = $current_dosen;

// Prefer portrait set from WP-Admin (Dosen CPT metabox / Featured Image) over JSON data.
$bk_cpt = get_page_by_path($d['slug'] ?? '', OBJECT, 'dosen');
if ($bk_cpt) {
    $bk_cpt_foto = get_post_meta($bk_cpt->ID, '_dosen_foto_link', true);
    if (!$bk_cpt_foto && has_post_thumbnail($bk_cpt)) {
        $bk_cpt_foto = get_the_post_thumbnail_url($bk_cpt, 'large');
    }
    if ($bk_cpt_foto) {
        $d['foto_link'] = $bk_cpt_foto;
    }
}
$bk_foto_url = function_exists('bk_resolve_theme_asset_url') ? bk_resolve_theme_asset_url($d['foto_link'] ?? '') : ($d['foto_link'] ?? '');

// Compute clean initials (excluding academic title prefixes like Prof, Dr)
$name_clean = explode(',', $d['nama'])[0];
$prefixes = array('prof', 'dr', 'dra', 'drs', 'ir', 'h', 'hj');
$words = preg_split('/[\s\.]+/', $name_clean);
$valid_words = array();
if (is_array($words)) {
    foreach ($words as $w) {
        $w_clean = trim($w);
        if (!empty($w_clean) && !in_array(strtolower($w_clean), $prefixes)) {
            $valid_words[] = $w_clean;
        }
    }
}
$initials = 'BK';
if (count($valid_words) >= 2) {
    $initials = strtoupper(substr($valid_words[0], 0, 1) . substr($valid_words[1], 0, 1));
} elseif (count($valid_words) === 1) {
    $initials = strtoupper(substr($valid_words[0], 0, 2));
}

$stat = $d['stat'] ?? array('publikasi' => 0, 'penelitian' => 0, 'pengabdian' => 0, 'buku' => 0);
$wa_url = '';
if (!empty($d['no_hp'])) {
    $num = preg_replace('/\D/', '', $d['no_hp']);
    if (strpos($num, '0') === 0) $num = '62' . substr($num, 1);
    $wa_url = 'https://wa.me/' . $num;
}

// Prepare courses count
$courses_list = $d['mata_kuliah'] ?? array();
$pendidikan_list = $d['pendidikan'] ?? array();
$total_pedagogik_count = count($pendidikan_list) + count($courses_list);
?>

<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background-color:#FAF8F5;font-family:'Plus Jakarta Sans',sans-serif;color:#111827;">

    <!-- =================================================================== -->
    <!-- 1. HERO HEADER (SCHOLARO DEEP FOREST GREEN)                         -->
    <!-- =================================================================== -->
    <section style="position:relative;overflow:hidden;background:#0D3B22;border-bottom:1.5px solid rgba(255,255,255,0.12);color:#ffffff;padding:0;padding-top:var(--bk-top-clearance, 125px);">
        <!-- Ambient Glow Orbs -->
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(245,184,46,0.14);filter:blur(90px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(24,83,51,0.45);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

        <div class="bk-container" style="position:relative;">
            
            <!-- Breadcrumb Navigation & Admin Shortcut -->
            <div style="margin-bottom:20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>" style="display:inline-flex;align-items:center;gap:8px;padding:8px 18px;border-radius:9999px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.2);color:#ffffff;font-size:12px;font-weight:600;text-decoration:none;line-height:1;box-sizing:border-box;transition:all 0.2s;" onmouseover="this.style.background='rgba(245,184,46,0.15)';this.style.borderColor='#F5B82E';this.style.color='#F5B82E';" onmouseout="this.style.background='rgba(255,255,255,0.08)';this.style.borderColor='rgba(255,255,255,0.2)';this.style.color='#ffffff';">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
                    <span>Kembali ke Direktori Dosen &amp; Staf</span>
                </a>

                <?php if (current_user_can('manage_options')) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=bk-direktori-dosen')); ?>" target="_blank" style="display:inline-flex;align-items:center;gap:6px;padding:7px 16px;border-radius:9999px;background:#F5B82E;color:#0D3B22;font-size:11.5px;font-weight:700;text-decoration:none;line-height:1;box-shadow:0 3px 10px rgba(0,0,0,0.2);">
                        <span>✏️ Edit Dosen Ini di WP-Admin</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Profile Info Main Layout: Left Tall Cutout Photo + Right Identity Details -->
            <div style="display:flex;align-items:flex-end;gap:clamp(24px, 4vw, 56px);flex-wrap:wrap;position:relative;">
                
                <!-- Left: Tall Cutout Photo / Executive Academic Seal -->
                <div class="bk-dosen-hero-photo-wrap" style="flex:0 0 clamp(240px, 30vw, 360px);align-self:flex-end;position:relative;display:flex;align-items:flex-end;justify-content:flex-start;margin-bottom:0;line-height:0;">
                    <?php if (!empty($bk_foto_url)) : ?>
                        <div class="bk-dossier-photo" style="width:100%;max-width:300px;margin-bottom:38px;line-height:normal;">
                            <div class="bk-dossier-portrait" style="width:100%;aspect-ratio:4/5;border-radius:20px;overflow:hidden;border:2px solid rgba(245,184,46,0.55);box-shadow:0 20px 45px rgba(0,0,0,0.45);background:#072314;">
                                <img src="<?php echo esc_url($bk_foto_url); ?>" alt="<?php echo esc_attr($d['nama_lengkap'] ?? $d['nama']); ?>" onerror="this.closest('.bk-dossier-photo').style.display='none';document.getElementById('bk-academic-seal').style.display='flex';" style="width:100%;height:100%;object-fit:cover;object-position:50% 18%;display:block;">
                            </div>
                        </div>
                    <?php endif; ?>
                        <!-- Executive Academic Seal Card (fallback when no photo / photo fails to load) -->
                        <div id="bk-academic-seal" class="bk-dossier-seal" style="<?php echo !empty($bk_foto_url) ? 'display:none;' : 'display:flex;'; ?>width:100%;max-width:280px;height:350px;border-radius:24px;background:linear-gradient(145deg, #134e2c 0%, #082616 100%);border:2px solid rgba(245,184,46,0.45);flex-direction:column;align-items:center;justify-content:center;padding:28px 20px;box-sizing:border-box;position:relative;overflow:hidden;box-shadow:0 20px 45px rgba(0,0,0,0.45), inset 0 1px 0 rgba(255,255,255,0.15);">
                            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;opacity:0.08;pointer-events:none;">
                                <svg width="220" height="220" viewBox="0 0 24 24" fill="currentColor" color="#F5B82E">
                                    <path d="M12 2L1 7l11 5 9-4.09V17h2V7L12 2zm0 13.5L4.2 12 2 13.09l10 5.41 10-5.41L19.8 12 12 15.5z"/>
                                </svg>
                            </div>
                            <div style="width:84px;height:84px;border-radius:50%;background:#0D3B22;border:3px solid #F5B82E;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-family:Fraunces,Georgia,serif;font-weight:700;font-size:32px;box-shadow:0 10px 28px rgba(0,0,0,0.4);position:relative;z-index:2;margin-bottom:18px;">
                                <?php echo esc_html($initials); ?>
                            </div>
                            <div style="text-align:center;position:relative;z-index:2;background:rgba(13,59,34,0.75);backdrop-filter:blur(8px);border:1px solid rgba(245,184,46,0.3);border-radius:14px;padding:10px 16px;width:100%;box-sizing:border-box;">
                                <span style="font-size:12px;font-weight:700;color:#F5B82E;letter-spacing:0.03em;display:block;text-transform:uppercase;line-height:1.2;">Profil Akademisi</span>
                                <span style="font-size:11px;color:rgba(255,255,255,0.85);line-height:1.3;display:block;margin-top:3px;font-weight:500;">PPB &middot; FIP UNM</span>
                            </div>
                        </div>
                </div>

                <!-- Right: Identity Details & Quick Stats -->
                <div style="flex:1;min-width:300px;padding-bottom:38px;">
                    
                    <!-- Badges Row -->
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-bottom:10px;">
                        <span style="background:rgba(245,184,46,0.18);border:1px solid rgba(245,184,46,0.35);color:#F5B82E;font-size:11px;font-weight:700;padding:4px 12px;border-radius:9999px;text-transform:uppercase;letter-spacing:0.06em;line-height:1;display:inline-block;">
                            <?php echo esc_html($d['jabatan_fungsional']); ?>
                        </span>
                        <?php if (!empty($d['jabatan_struktural'])) : ?>
                            <span style="background:rgba(245,184,46,0.25);border:1px solid #F5B82E;color:#ffffff;font-size:11.5px;font-weight:700;padding:4px 12px;border-radius:9999px;line-height:1;display:inline-block;">
                                🏛 <?php echo esc_html($d['jabatan_struktural']); ?>
                            </span>
                        <?php endif; ?>
                        <span style="background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.15);color:rgba(255,255,255,0.85);font-size:11.5px;padding:4px 12px;border-radius:9999px;line-height:1;display:inline-block;">
                            <?php echo esc_html($d['unit'] ?? 'S1 Bimbingan dan Konseling'); ?>
                        </span>
                    </div>

                    <!-- Name & Sapaan -->
                    <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(24px, 3.2vw, 36px);font-weight:600;color:#ffffff;line-height:1.25;margin:0 0 4px 0;">
                        <?php echo esc_html($d['nama_lengkap']); ?>
                    </h1>
                    <?php if (!empty($d['nama_panggilan'])) : ?>
                        <p style="font-size:13px;color:#F5B82E;font-weight:500;margin:0 0 8px 0;">
                            Sapaan Akrab: <em>&ldquo;<?php echo esc_html($d['nama_panggilan']); ?>&rdquo;</em>
                        </p>
                    <?php endif; ?>

                    <!-- NIP / NIDN / Ruang Kerja -->
                    <p style="font-family:monospace;font-size:12px;color:rgba(255,255,255,0.7);margin:0 0 12px 0;line-height:1.4;">
                        <?php if (!empty($d['nip'])) : ?>NIP. <?php echo esc_html($d['nip']); ?><?php endif; ?>
                        <?php if (!empty($d['nidn'])) : ?> &middot; NIDN. <?php echo esc_html($d['nidn']); ?><?php endif; ?>
                        <?php if (!empty($d['ruang_kerja'])) : ?> &middot; 📍 <?php echo esc_html($d['ruang_kerja']); ?><?php endif; ?>
                    </p>

                    <!-- Fokus Kepakaran Tags -->
                    <?php 
                    $my_kepakaran = $d['kepakaran'] ?? array();
                    if (empty($my_kepakaran) && !empty($d['bidang_keahlian'])) {
                        $my_kepakaran = array_filter(array_map('trim', preg_split('/[,;
]+/', $d['bidang_keahlian'])));
                    }
                    if (empty($my_kepakaran)) {
                        $my_kepakaran = array('Bimbingan dan Konseling', 'Psikologi Pendidikan');
                    }
                    if (!empty($my_kepakaran)) : ?>
                        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-bottom:14px;">
                            <span style="font-size:10.5px;font-weight:700;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:0.05em;margin-right:2px;">Kepakaran:</span>
                            <?php foreach (array_slice($my_kepakaran, 0, 4) as $kp) : ?>
                                <span style="font-size:11px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.18);color:#ffffff;padding:2px 9px;border-radius:6px;line-height:1.2;">
                                    <?php echo esc_html($kp); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Bio / Sambutan -->
                    <?php if (!empty($d['bio'])) : ?>
                        <p style="font-size:13.5px;line-height:1.65;color:rgba(255,255,255,0.85);margin:0 0 18px 0;max-width:820px;">
                            <?php echo esc_html($d['bio']); ?>
                        </p>
                    <?php endif; ?>

                    <!-- Academic Links & Contacts (Pixel-Perfect Buttons) -->
                    <div style="display:flex;flex-wrap:wrap;align-items:center;gap:10px;padding-top:2px;">
                        
                        <?php if (!empty($d['sinta_url'])) : ?>
                            <a href="<?php echo esc_url($d['sinta_url']); ?>" target="_blank" rel="noopener" style="height:38px;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:#ffffff;color:#0D3B22;border:1.5px solid #ffffff;font-size:12px;font-weight:700;padding:0 18px;border-radius:9999px;text-decoration:none;box-sizing:border-box;line-height:1;box-shadow:0 3px 10px rgba(0,0,0,0.15);transition:all 0.2s;" onmouseover="this.style.background='#F5B82E';this.style.borderColor='#F5B82E';this.style.color='#0D3B22';" onmouseout="this.style.background='#ffffff';this.style.borderColor='#ffffff';this.style.color='#0D3B22';">
                                <span>Profil SINTA Dikti</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($d['scholar_url'])) : ?>
                            <a href="<?php echo esc_url($d['scholar_url']); ?>" target="_blank" rel="noopener" style="height:38px;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.3);color:#ffffff;font-size:12px;font-weight:600;padding:0 18px;border-radius:9999px;text-decoration:none;box-sizing:border-box;line-height:1;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)';this.style.borderColor='#ffffff';" onmouseout="this.style.background='rgba(255,255,255,0.12)';this.style.borderColor='rgba(255,255,255,0.3)';">
                                <span>Google Scholar</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($d['scopus_url'])) : ?>
                            <a href="<?php echo esc_url($d['scopus_url']); ?>" target="_blank" rel="noopener" style="height:38px;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:rgba(255,255,255,0.12);border:1.5px solid rgba(255,255,255,0.3);color:#ffffff;font-size:12px;font-weight:600;padding:0 18px;border-radius:9999px;text-decoration:none;box-sizing:border-box;line-height:1;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)';this.style.borderColor='#ffffff';" onmouseout="this.style.background='rgba(255,255,255,0.12)';this.style.borderColor='rgba(255,255,255,0.3)';">
                                <span>Scopus ID</span>
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($d['email'])) : ?>
                            <a href="mailto:<?php echo esc_attr($d['email']); ?>" style="height:38px;display:inline-flex;align-items:center;justify-content:center;gap:8px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.25);color:#ffffff;font-size:12px;font-weight:600;padding:0 18px;border-radius:9999px;text-decoration:none;box-sizing:border-box;line-height:1;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.22)';this.style.borderColor='#ffffff';" onmouseout="this.style.background='rgba(255,255,255,0.12)';this.style.borderColor='rgba(255,255,255,0.25)';">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                <span>Email: <?php echo esc_html($d['email']); ?></span>
                            </a>
                        <?php endif; ?>

                    </div>

                    <!-- Quick Stat Counters Row -->
                    <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:12px;margin-top:22px;border-top:1px solid rgba(255,255,255,0.12);padding-top:18px;">
                        <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:12px 14px;">
                            <span style="font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;display:block;">Mata Kuliah</span>
                            <strong style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:#F5B82E;line-height:1.1;display:block;margin-top:2px;">
                                <?php echo count($courses_list); ?>
                            </strong>
                            <span style="font-size:10px;color:rgba(255,255,255,0.65);">Course Dibina</span>
                        </div>

                        <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:12px 14px;">
                            <span style="font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;display:block;">Publikasi</span>
                            <strong style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:#ffffff;line-height:1.1;display:block;margin-top:2px;">
                                <?php echo (int)$stat['publikasi']; ?>
                            </strong>
                            <span style="font-size:10px;color:rgba(255,255,255,0.65);">SINTA &amp; Scopus</span>
                        </div>

                        <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:12px 14px;">
                            <span style="font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;display:block;">Penelitian</span>
                            <strong style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:#ffffff;line-height:1.1;display:block;margin-top:2px;">
                                <?php echo (int)$stat['penelitian']; ?>
                            </strong>
                            <span style="font-size:10px;color:rgba(255,255,255,0.65);">Hibah Dikti &amp; PNBP</span>
                        </div>

                        <div style="background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.12);border-radius:14px;padding:12px 14px;">
                            <span style="font-size:10px;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.06em;font-weight:600;display:block;">Pengabdian</span>
                            <strong style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:#ffffff;line-height:1.1;display:block;margin-top:2px;">
                                <?php echo (int)$stat['pengabdian']; ?>
                            </strong>
                            <span style="font-size:10px;color:rgba(255,255,255,0.65);">PkM Institusi</span>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. REKAM JEJAK AKADEMIK (INTERACTIVE TABS)                          -->
    <!-- =================================================================== -->
    <main class="bk-container bk-subpage-main" style="padding-top:36px !important;padding-bottom:80px !important;">
        
        <!-- Tab Navigation Buttons (Enhanced Spacing, Default: Pendidikan & Mata Kuliah) -->
        <div style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;padding:6px 0 20px;margin-bottom:32px;border-bottom:2px solid #E5E7EB;" id="dosen-tab-nav">
            
            <!-- TAB 1: PENDIDIKAN & MATA KULIAH (DEFAULT ACTIVE) -->
            <button type="button" class="dosen-tab-btn active" data-tab="tab-pendidikan" style="height:48px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 22px;border-radius:14px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:700;white-space:nowrap;background:#0D3B22;color:#F5B82E;border:1.5px solid #0D3B22;box-sizing:border-box;line-height:1;box-shadow:0 4px 14px rgba(13,59,34,0.18);transition:all 0.2s;">
                <span>🎓 Pendidikan &amp; Mata Kuliah</span>
                <span class="dosen-tab-count" style="background:rgba(245,184,46,0.22);color:#F5B82E;font-size:11px;font-weight:800;padding:4px 9px;border-radius:9999px;line-height:1;">
                    <?php echo (int)$total_pedagogik_count; ?>
                </span>
            </button>

            <!-- TAB 2: PUBLIKASI ILMIAH -->
            <button type="button" class="dosen-tab-btn" data-tab="tab-publikasi" style="height:48px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 22px;border-radius:14px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:600;white-space:nowrap;background:#ffffff;color:#4B5563;border:1.5px solid #D4E3D8;box-sizing:border-box;line-height:1;transition:all 0.2s;">
                <span>📄 Publikasi Ilmiah</span>
                <span class="dosen-tab-count" style="background:#F3F4F6;color:#6B7280;font-size:11px;font-weight:700;padding:4px 9px;border-radius:9999px;line-height:1;">
                    <?php echo (int)$stat['publikasi']; ?>
                </span>
            </button>

            <!-- TAB 3: PENELITIAN & RISET -->
            <button type="button" class="dosen-tab-btn" data-tab="tab-penelitian" style="height:48px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 22px;border-radius:14px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:600;white-space:nowrap;background:#ffffff;color:#4B5563;border:1.5px solid #D4E3D8;box-sizing:border-box;line-height:1;transition:all 0.2s;">
                <span>🔬 Penelitian &amp; Riset</span>
                <span class="dosen-tab-count" style="background:#F3F4F6;color:#6B7280;font-size:11px;font-weight:700;padding:4px 9px;border-radius:9999px;line-height:1;">
                    <?php echo (int)$stat['penelitian']; ?>
                </span>
            </button>

            <!-- TAB 4: PENGABDIAN MASYARAKAT -->
            <button type="button" class="dosen-tab-btn" data-tab="tab-pengabdian" style="height:48px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 22px;border-radius:14px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:600;white-space:nowrap;background:#ffffff;color:#4B5563;border:1.5px solid #D4E3D8;box-sizing:border-box;line-height:1;transition:all 0.2s;">
                <span>🤝 Pengabdian Masyarakat</span>
                <span class="dosen-tab-count" style="background:#F3F4F6;color:#6B7280;font-size:11px;font-weight:700;padding:4px 9px;border-radius:9999px;line-height:1;">
                    <?php echo (int)$stat['pengabdian']; ?>
                </span>
            </button>

            <!-- TAB 5: BUKU & KARYA -->
            <button type="button" class="dosen-tab-btn" data-tab="tab-buku" style="height:48px;display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:0 22px;border-radius:14px;cursor:pointer;font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;font-weight:600;white-space:nowrap;background:#ffffff;color:#4B5563;border:1.5px solid #D4E3D8;box-sizing:border-box;line-height:1;transition:all 0.2s;">
                <span>📚 Buku &amp; HKI</span>
                <span class="dosen-tab-count" style="background:#F3F4F6;color:#6B7280;font-size:11px;font-weight:700;padding:4px 9px;border-radius:9999px;line-height:1;">
                    <?php echo (int)$stat['buku']; ?>
                </span>
            </button>
        </div>

        <!-- =============================================================== -->
        <!-- TAB CONTENT 1: PENDIDIKAN & MATA KULIAH (DEFAULT VISIBLE)       -->
        <!-- =============================================================== -->
        <div id="tab-pendidikan" class="dosen-tab-pane" style="display:block;">
            
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:28px;margin-bottom:28px;">
                
                <!-- CARD 1: RIWAYAT PENDIDIKAN TINGGI -->
                <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:24px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;padding-bottom:16px;border-bottom:1.5px solid #F3F4F6;">
                        <div style="width:46px;height:46px;border-radius:14px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                            🎓
                        </div>
                        <div>
                            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:19px;color:#0D3B22;margin:0 0 2px 0;font-weight:700;">
                                Riwayat Pendidikan Tinggi
                            </h3>
                            <span style="font-size:12px;color:#6B7280;">Kualifikasi gelar formal &amp; almamater lulusan</span>
                        </div>
                    </div>

                    <?php if (!empty($pendidikan_list) && is_array($pendidikan_list)) : ?>
                        <div style="display:grid;gap:18px;">
                            <?php foreach ($pendidikan_list as $pend) : 
                                $j = strtoupper($pend['jenjang'] ?? 'S1');
                                $j_bg = '#E8F0EA'; $j_col = '#0D3B22';
                                if ($j === 'S3' || strpos($j, 'DOKTOR') !== false) {
                                    $j_bg = '#E0E7FF'; $j_col = '#3730A3';
                                } elseif ($j === 'S2' || strpos($j, 'MAGISTER') !== false) {
                                    $j_bg = '#FEF3C7'; $j_col = '#92400E';
                                } elseif (strpos($j, 'PROFESI') !== false || strpos($j, 'KONS') !== false) {
                                    $j_bg = '#FCE7F3'; $j_col = '#9D174D';
                                }
                            ?>
                                <div style="display:flex;gap:14px;align-items:flex-start;padding:12px 14px;background:#FAF8F5;border-radius:14px;border:1px solid #ECE7E1;">
                                    <span style="background:<?php echo $j_bg; ?>;color:<?php echo $j_col; ?>;font-weight:800;font-size:11.5px;padding:5px 12px;border-radius:8px;letter-spacing:0.04em;line-height:1.2;margin-top:2px;flex-shrink:0;">
                                        <?php echo esc_html($j); ?>
                                    </span>
                                    <div style="flex:1;">
                                        <strong style="font-size:14.5px;color:#111827;display:block;line-height:1.35;">
                                            <?php echo esc_html($pend['bidang'] ?: 'Bimbingan dan Konseling'); ?>
                                        </strong>
                                        <span style="font-size:13px;color:#4B5563;display:block;margin-top:3px;">
                                            <?php echo esc_html($pend['institusi'] ?: 'Universitas'); ?>
                                        </span>
                                        <?php if (!empty($pend['tahun'])) : ?>
                                            <span style="font-size:11.5px;color:#6B7280;font-family:monospace;display:inline-block;margin-top:4px;font-weight:600;">
                                                Tahun Kelulusan: <?php echo esc_html($pend['tahun']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="font-size:13.5px;color:#6B7280;margin:0;">Belum ada riwayat pendidikan yang dicantumkan.</p>
                    <?php endif; ?>
                </div>

                <!-- CARD 2: MATA KULIAH / COURSE YANG DIAMPU (UNIFIED) -->
                <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:24px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                    <div style="display:flex;align-items:center;gap:14px;margin-bottom:22px;padding-bottom:16px;border-bottom:1.5px solid #F3F4F6;">
                        <div style="width:46px;height:46px;border-radius:14px;background:#FEF3C7;color:#92400E;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0;">
                            📖
                        </div>
                        <div>
                            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:19px;color:#0D3B22;margin:0 0 2px 0;font-weight:700;">
                                Mata Kuliah yang Diampu
                            </h3>
                            <span style="font-size:12px;color:#6B7280;">Course pengajaran &amp; praktikum kurikulum OBE</span>
                        </div>
                    </div>

                    <?php if (!empty($courses_list) && is_array($courses_list)) : ?>
                        <div style="display:grid;gap:14px;">
                            <?php foreach ($courses_list as $c) : ?>
                                <div style="display:flex;flex-direction:column;gap:6px;padding:14px 16px;background:#FAF8F5;border-radius:14px;border:1px solid #ECE7E1;">
                                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;">
                                        <div style="display:flex;align-items:center;gap:8px;">
                                            <span style="background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:800;padding:3px 8px;border-radius:6px;font-family:monospace;letter-spacing:0.04em;">
                                                <?php echo esc_html($c['kode'] ?? 'BK'); ?>
                                            </span>
                                            <span style="background:#E8F0EA;color:#0D3B22;font-size:11px;font-weight:700;padding:3px 8px;border-radius:6px;">
                                                <?php echo esc_html($c['jenjang'] ?? 'S1'); ?> &middot; <?php echo esc_html($c['sks'] ?? '3'); ?> SKS
                                            </span>
                                        </div>
                                        <span style="font-size:11px;color:#6B7280;font-weight:600;">
                                            <?php echo esc_html($c['sifat'] ?? 'Wajib Keilmuan'); ?>
                                        </span>
                                    </div>
                                    <strong style="font-size:14px;color:#111827;line-height:1.4;">
                                        <?php echo esc_html($c['nama'] ?? 'Mata Kuliah Bimbingan Konseling'); ?>
                                    </strong>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="font-size:13.5px;color:#6B7280;margin:0;">Mata kuliah dalam penyesuaian kurikulum aktif.</p>
                    <?php endif; ?>
                </div>

            </div>

            <!-- ROW 2: KEPAKARAN & PENGALAMAN KARIR -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(360px, 1fr));gap:28px;">
                
                <!-- BIDANG KEPAKARAN -->
                <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:24px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                    <h4 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0D3B22;margin:0 0 16px 0;font-weight:700;display:flex;align-items:center;gap:10px;">
                        <span>🎯 Bidang Kepakaran &amp; Fokus Keilmuan</span>
                    </h4>
                    <?php 
                    $all_kep = $d['kepakaran'] ?? array();
                    if (empty($all_kep) && !empty($d['bidang_keahlian'])) {
                        $all_kep = array_filter(array_map('trim', preg_split('/[,;
]+/', $d['bidang_keahlian'])));
                    }
                    if (empty($all_kep)) {
                        $all_kep = array('Bimbingan dan Konseling', 'Psikologi Pendidikan');
                    }
                    if (!empty($all_kep)) : ?>
                        <div style="display:flex;flex-wrap:wrap;gap:10px;">
                            <?php foreach ($all_kep as $k) : ?>
                                <span style="background:#E8F0EA;border:1.5px solid #D4E3D8;color:#0D3B22;font-size:12.5px;font-weight:600;padding:8px 16px;border-radius:12px;line-height:1.3;display:inline-flex;align-items:center;gap:6px;">
                                    <span style="color:#F5B82E;">★</span>
                                    <span><?php echo esc_html($k); ?></span>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- PENGALAMAN & JABATAN -->
                <div style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:24px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                    <h4 style="font-family:'Fraunces',Georgia,serif;font-size:18px;color:#0D3B22;margin:0 0 16px 0;font-weight:700;display:flex;align-items:center;gap:10px;">
                        <span>💼 Rekam Jejak Karir &amp; Pengalaman</span>
                    </h4>
                    <?php if (!empty($d['pengalaman']) && is_array($d['pengalaman'])) : ?>
                        <div style="display:grid;gap:14px;">
                            <?php foreach ($d['pengalaman'] as $peng) : ?>
                                <div style="border-left:3.5px solid #F5B82E;padding-left:14px;background:#FAF8F5;padding-top:10px;padding-bottom:10px;padding-right:12px;border-radius:0 12px 12px 0;">
                                    <strong style="font-size:14px;color:#111827;display:block;line-height:1.35;">
                                        <?php echo esc_html($peng['posisi'] ?? ''); ?>
                                    </strong>
                                    <span style="font-size:12.5px;color:#4B5563;display:block;margin-top:2px;">
                                        <?php echo esc_html($peng['instansi'] ?? ''); ?>
                                    </span>
                                    <?php if (!empty($peng['periode'])) : ?>
                                        <span style="font-size:11px;background:#F3F4F6;color:#374151;padding:2px 8px;border-radius:6px;display:inline-block;margin-top:4px;font-weight:600;">
                                            📅 <?php echo esc_html($peng['periode']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else : ?>
                        <p style="font-size:13.5px;color:#6B7280;margin:0;">Dosen Tetap Jurusan Bimbingan dan Konseling FIP Universitas Negeri Makassar.</p>
                    <?php endif; ?>
                </div>

            </div>

        </div>

        <!-- =============================================================== -->
        <!-- TAB CONTENT 2: PUBLIKASI ILMIAH (GENEROUS SPACING & BADGES)    -->
        <!-- =============================================================== -->
        <div id="tab-publikasi" class="dosen-tab-pane" style="display:none;">
            <?php if (empty($d['publikasi'])) : ?>
                <div style="background:#ffffff;border:1.5px dashed #D4E3D8;border-radius:24px;padding:50px 24px;text-align:center;">
                    <p style="color:#6B7280;font-size:14.5px;margin:0;">Belum ada data publikasi tercatat pada profil dosen ini.</p>
                </div>
            <?php else : ?>
                <div style="display:grid;gap:22px;">
                    <?php foreach ($d['publikasi'] as $pub) : 
                        $badge_bg = '#E8F0EA';
                        $badge_color = '#0D3B22';
                        $akred = $pub['akreditasi'] ?? '';
                        if (stripos($akred, 'q1') !== false || stripos($akred, 'sinta 1') !== false) {
                            $badge_bg = '#FEF3C7';
                            $badge_color = '#92400E';
                        } elseif (stripos($akred, 'q2') !== false || stripos($akred, 'sinta 2') !== false) {
                            $badge_bg = '#E0E7FF';
                            $badge_color = '#3730A3';
                        }
                    ?>
                    <article style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:22px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);transition:all 0.2s;">
                        
                        <!-- Top Metadata Row (Generous Margins) -->
                        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:16px;">
                            <div style="display:flex;align-items:center;flex-wrap:wrap;gap:10px;">
                                <?php if (!empty($akred)) : ?>
                                    <span style="background:<?php echo esc_attr($badge_bg); ?>;color:<?php echo esc_attr($badge_color); ?>;font-size:11px;font-weight:800;padding:6px 14px;border-radius:9999px;text-transform:uppercase;letter-spacing:0.04em;line-height:1;display:inline-block;">
                                        <?php echo esc_html($akred); ?>
                                    </span>
                                <?php endif; ?>
                                <span style="font-size:12px;color:#4B5563;background:#F3F4F6;padding:6px 14px;border-radius:8px;font-weight:600;line-height:1;display:inline-block;">
                                    <?php echo esc_html($pub['sumber'] ?? 'SINTA'); ?> &middot; Tahun <?php echo esc_html($pub['tahun'] ?? '-'); ?>
                                </span>
                            </div>
                            <?php if (!empty($pub['peran'])) : ?>
                                <span style="font-size:12px;color:#0D3B22;background:#E8F0EA;padding:6px 14px;border-radius:8px;font-weight:700;line-height:1;display:inline-block;">
                                    Peran: <?php echo esc_html($pub['peran']); ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <!-- Article Title -->
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#111827;line-height:1.5;margin:0 0 12px 0;">
                            <?php if (!empty($pub['url'])) : ?>
                                <a href="<?php echo esc_url($pub['url']); ?>" target="_blank" rel="noopener" style="color:inherit;text-decoration:none;transition:color 0.2s;" onmouseover="this.style.color='#0D3B22'" onmouseout="this.style.color='#111827'">
                                    <?php echo esc_html($pub['judul']); ?>
                                </a>
                            <?php else : ?>
                                <?php echo esc_html($pub['judul']); ?>
                            <?php endif; ?>
                        </h3>

                        <!-- Media / Journal Name -->
                        <?php if (!empty($pub['media'])) : ?>
                            <p style="font-size:13.5px;color:#374151;margin:0 0 8px 0;line-height:1.55;">
                                <strong style="color:#0D3B22;">Jurnal / Media:</strong> <?php echo esc_html($pub['media']); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Authors List -->
                        <?php if (!empty($pub['penulis'])) : ?>
                            <p style="font-size:12.5px;color:#6B7280;margin:0 0 16px 0;line-height:1.55;">
                                <strong>Penulis:</strong> <?php echo esc_html($pub['penulis']); ?>
                            </p>
                        <?php endif; ?>

                        <!-- Action Button -->
                        <?php if (!empty($pub['url'])) : ?>
                            <div style="padding-top:6px;">
                                <a href="<?php echo esc_url($pub['url']); ?>" target="_blank" rel="noopener" style="height:36px;display:inline-flex;align-items:center;justify-content:center;gap:8px;font-size:12px;font-weight:700;color:#0D3B22;background:#E8F0EA;border:1.5px solid #D4E3D8;padding:0 18px;border-radius:10px;text-decoration:none;line-height:1;box-sizing:border-box;transition:all 0.2s;" onmouseover="this.style.background='#d0e3d4';this.style.borderColor='#0D3B22';" onmouseout="this.style.background='#E8F0EA';this.style.borderColor='#D4E3D8';">
                                    <span>Buka Tautan Jurnal / Artikel</span>
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                                </a>
                            </div>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- =============================================================== -->
        <!-- TAB CONTENT 3: PENELITIAN & RISET                               -->
        <!-- =============================================================== -->
        <div id="tab-penelitian" class="dosen-tab-pane" style="display:none;">
            <?php if (empty($d['penelitian'])) : ?>
                <div style="background:#ffffff;border:1.5px dashed #D4E3D8;border-radius:24px;padding:50px 24px;text-align:center;">
                    <p style="color:#6B7280;font-size:14.5px;margin:0;">Belum ada rekam jejak penelitian tercatat pada profil dosen ini.</p>
                </div>
            <?php else : ?>
                <div style="display:grid;gap:22px;">
                    <?php foreach ($d['penelitian'] as $pen) : ?>
                    <article style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:22px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="background:#E0E7FF;color:#3730A3;font-size:11px;font-weight:800;padding:6px 14px;border-radius:9999px;text-transform:uppercase;line-height:1;">
                                    <?php echo esc_html($pen['skema'] ?? 'Hibah Penelitian'); ?>
                                </span>
                                <span style="font-size:12px;color:#6B7280;background:#F3F4F6;padding:6px 14px;border-radius:8px;font-weight:600;line-height:1;">
                                    Tahun <?php echo esc_html($pen['tahun']); ?>
                                </span>
                            </div>
                            <span style="font-size:12px;color:#0D3B22;background:#E8F0EA;padding:6px 14px;border-radius:8px;font-weight:700;line-height:1;">
                                <?php echo esc_html($pen['peran']); ?>
                            </span>
                        </div>
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#111827;line-height:1.5;margin:0 0 10px 0;">
                            <?php echo esc_html($pen['judul']); ?>
                        </h3>
                        <p style="font-size:13.5px;color:#4B5563;margin:0;line-height:1.5;">
                            <strong>Sumber Pendanaan:</strong> <?php echo esc_html($pen['sumber'] ?? 'DIPA UNM'); ?>
                        </p>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- =============================================================== -->
        <!-- TAB CONTENT 4: PENGABDIAN MASYARAKAT (PKM)                      -->
        <!-- =============================================================== -->
        <div id="tab-pengabdian" class="dosen-tab-pane" style="display:none;">
            <?php if (empty($d['pengabdian'])) : ?>
                <div style="background:#ffffff;border:1.5px dashed #D4E3D8;border-radius:24px;padding:50px 24px;text-align:center;">
                    <p style="color:#6B7280;font-size:14.5px;margin:0;">Belum ada data pengabdian masyarakat tercatat pada profil dosen ini.</p>
                </div>
            <?php else : ?>
                <div style="display:grid;gap:22px;">
                    <?php foreach ($d['pengabdian'] as $pkm) : ?>
                    <article style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:22px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                        <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <span style="background:#FEF3C7;color:#92400E;font-size:11px;font-weight:800;padding:6px 14px;border-radius:9999px;text-transform:uppercase;line-height:1;">
                                    <?php echo esc_html($pkm['skema'] ?? 'PkM Institusi'); ?>
                                </span>
                                <span style="font-size:12px;color:#6B7280;background:#F3F4F6;padding:6px 14px;border-radius:8px;font-weight:600;line-height:1;">
                                    Tahun <?php echo esc_html($pkm['tahun']); ?>
                                </span>
                            </div>
                            <span style="font-size:12px;color:#0D3B22;background:#E8F0EA;padding:6px 14px;border-radius:8px;font-weight:700;line-height:1;">
                                <?php echo esc_html($pkm['peran']); ?>
                            </span>
                        </div>
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#111827;line-height:1.5;margin:0 0 10px 0;">
                            <?php echo esc_html($pkm['judul']); ?>
                        </h3>
                        <?php if (!empty($pkm['mitra'])) : ?>
                            <p style="font-size:13.5px;color:#4B5563;margin:0 0 4px 0;line-height:1.5;">
                                <strong>Mitra Kegiatan:</strong> <?php echo esc_html($pkm['mitra']); ?>
                            </p>
                        <?php endif; ?>
                        <p style="font-size:13.5px;color:#6B7280;margin:0;line-height:1.5;">
                            <strong>Sumber Dana:</strong> <?php echo esc_html($pkm['sumber'] ?? 'DIPA UNM'); ?>
                        </p>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- =============================================================== -->
        <!-- TAB CONTENT 5: BUKU & KARYA CIPTA (HKI)                         -->
        <!-- =============================================================== -->
        <div id="tab-buku" class="dosen-tab-pane" style="display:none;">
            <?php if (empty($d['buku'])) : ?>
                <div style="background:#ffffff;border:1.5px dashed #D4E3D8;border-radius:24px;padding:50px 24px;text-align:center;">
                    <p style="color:#6B7280;font-size:14.5px;margin:0;">Belum ada data buku / karya tercatat pada profil dosen ini.</p>
                </div>
            <?php else : ?>
                <div style="display:grid;gap:22px;">
                    <?php foreach ($d['buku'] as $bk) : ?>
                    <article style="background:#ffffff;border:1.5px solid #D4E3D8;border-radius:22px;padding:clamp(22px, 2.5vw, 32px);box-shadow:0 4px 16px rgba(13,59,34,0.04);">
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
                            <span style="background:#FCE7F3;color:#9D174D;font-size:11px;font-weight:800;padding:6px 14px;border-radius:9999px;text-transform:uppercase;line-height:1;">
                                <?php echo esc_html($bk['kategori'] ?? 'Buku Referensi'); ?>
                            </span>
                            <span style="font-size:12px;color:#6B7280;background:#F3F4F6;padding:6px 14px;border-radius:8px;font-weight:600;line-height:1;">
                                Tahun <?php echo esc_html($bk['tahun']); ?>
                            </span>
                        </div>
                        <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#111827;line-height:1.5;margin:0 0 10px 0;">
                            <?php echo esc_html($bk['judul']); ?>
                        </h3>
                        <p style="font-size:13.5px;color:#4B5563;margin:0 0 4px 0;line-height:1.5;">
                            <strong>Penerbit:</strong> <?php echo esc_html($bk['penerbit']); ?>
                        </p>
                        <?php if (!empty($bk['isbn'])) : ?>
                            <p style="font-size:12.5px;color:#6B7280;margin:0;font-family:monospace;">
                                <strong>ISBN:</strong> <?php echo esc_html($bk['isbn']); ?>
                            </p>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

    </main>

</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const navBtns = document.querySelectorAll("#dosen-tab-nav .dosen-tab-btn");
    const panes = document.querySelectorAll(".dosen-tab-pane");

    navBtns.forEach(btn => {
        btn.addEventListener("click", function() {
            const targetId = this.getAttribute("data-tab");

            // Reset all buttons
            navBtns.forEach(b => {
                b.style.background = "#ffffff";
                b.style.color = "#4B5563";
                b.style.borderColor = "#D4E3D8";
                b.style.fontWeight = "600";
                b.style.boxShadow = "none";
                b.classList.remove("active");
                const countBadge = b.querySelector(".dosen-tab-count");
                if (countBadge) {
                    countBadge.style.background = "#F3F4F6";
                    countBadge.style.color = "#6B7280";
                }
            });

            // Activate clicked button
            this.style.background = "#0D3B22";
            this.style.color = "#F5B82E";
            this.style.borderColor = "#0D3B22";
            this.style.fontWeight = "700";
            this.style.boxShadow = "0 4px 14px rgba(13,59,34,0.18)";
            this.classList.add("active");
            const activeBadge = this.querySelector(".dosen-tab-count");
            if (activeBadge) {
                activeBadge.style.background = "rgba(245,184,46,0.22)";
                activeBadge.style.color = "#F5B82E";
            }

            // Switch panes
            panes.forEach(p => {
                if (p.id === targetId) {
                    p.style.display = "block";
                } else {
                    p.style.display = "none";
                }
            });
        });
    });
});
</script>

<?php get_footer(); ?>
