<?php
/**
 * Template Name: Halaman Warta & Berita
 * Template for displaying Berita page (Swiss Modernism 2.0)
 * Aligned 1:1 with Navbar Logo (Full Width .bk-container)
 */

if (!defined('ABSPATH')) exit;

get_header();

$all_posts_count = wp_count_posts('post')->publish ?? 6;
?>

<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background-color:#FAF8F5;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#111827;">

    <!-- =================================================================== -->
    <!-- HERO HEADER (Swiss Academic Editorial)                              -->
    <!-- =================================================================== -->
    <section style="position:relative;overflow:hidden;background:#0D3B22;border-bottom:1px solid rgba(255,255,255,0.1);color:#ffffff;padding:var(--bk-top-clearance, 36px) 0 45px;">
        <!-- Ambient Glow -->
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(24,83,51,0.35);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(245,184,46,0.12);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

        <div class="bk-container" style="position:relative;width:100%;box-sizing:border-box;">
            <!-- Breadcrumb -->
            <a href="<?php echo esc_url(home_url('/')); ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:12px;font-weight:600;color:rgba(255,255,255,0.7);text-decoration:none;margin-bottom:16px;transition:color 0.2s;" onmouseover="this.style.color='#F5B82E'" onmouseout="this.style.color='rgba(255,255,255,0.7)'">
                <span>&larr; Kembali ke Beranda</span>
            </a>

            <!-- Badge -->
            <div style="display:inline-flex;align-items:center;gap:7px;padding:4px 12px;border-radius:4px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#F5B82E;font-size:11px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:14px;">
                <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#F5B82E;"></span>
                <span>Pusat Informasi &amp; Publikasi Ilmiah Resmi</span>
            </div>

            <!-- Title -->
            <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(28px, 4.2vw, 48px);font-weight:500;color:#ffffff;line-height:1.18;letter-spacing:-0.02em;margin:0 0 12px 0;">
                Warta, Berita &amp; Artikel <span style="font-style:italic;color:#F5B82E;">Prodi BK</span>
            </h1>

            <p style="font-size:clamp(14px, 1.5vw, 15.5px);line-height:1.65;color:rgba(255,255,255,0.85);margin:0 0 24px 0;max-width:760px;">
                Kabar kegiatan tridharma perguruan tinggi, pengumuman akademik, prestasi sivitas akademika, serta publikasi karya ilmiah Bimbingan dan Konseling Universitas Negeri Makassar.
            </p>

            <!-- Swiss Ledger Metrics Bar -->
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
                <div style="border-radius:6px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);padding:10px 18px;text-align:left;">
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:22px;font-weight:700;color:#ffffff;display:block;line-height:1;"><?php echo $all_posts_count; ?></span>
                    <span style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Artikel Terbit</span>
                </div>
                <div style="border-radius:6px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);padding:10px 18px;text-align:left;">
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:22px;font-weight:700;color:#F5B82E;display:block;line-height:1;">6</span>
                    <span style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Kategori Warta</span>
                </div>
                <div style="border-radius:6px;background:rgba(255,255,255,0.07);border:1px solid rgba(255,255,255,0.12);padding:10px 18px;text-align:left;">
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:22px;font-weight:700;color:#34D399;display:block;line-height:1;">Aktif</span>
                    <span style="font-size:11px;font-weight:500;color:rgba(255,255,255,0.7);text-transform:uppercase;letter-spacing:0.05em;">Ruang Kontribusi</span>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- NEWS GRID CONTENT (100% Full-Width Aligned with Navbar Logo)         -->
    <!-- =================================================================== -->
    <main class="ppb-admin-managed-zone" data-wfie-page-locked="true" data-ppb-admin-label="Warta &amp; Berita Jurusan" data-ppb-admin-url="<?php echo esc_url(admin_url('edit.php')); ?>" style="padding:32px 0 60px;width:100%;box-sizing:border-box;margin:0;">
        <?php
        if (!function_exists('ppb_render_berita_grid_swiss')) {
            $berita_inc = get_template_directory() . '/inc/ppb-admin-berita.php';
            if (file_exists($berita_inc)) {
                require_once $berita_inc;
            }
        }
        if (function_exists('ppb_render_berita_grid_swiss')) {
            echo ppb_render_berita_grid_swiss();
        } elseif (shortcode_exists('ppb_berita_grid')) {
            echo do_shortcode('[ppb_berita_grid]');
        }
        ?>
    </main>

</div>

<?php get_footer(); ?>
