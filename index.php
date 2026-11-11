<?php
/**
 * The template for displaying archive and index pages
 * 1:1 Full-Width Scholaro Editorial Design
 */

if (!defined('ABSPATH')) exit;

// CRITICAL GUARD: Never display Warta & Berita for 404 Not Found requests!
if (is_404()) {
    status_header(404);
    $tpl_404 = get_template_directory() . '/404.php';
    if (file_exists($tpl_404)) {
        include $tpl_404;
        exit;
    }
}

get_header();

$page_title = 'Warta, Berita &amp; Artikel';
if (is_category()) {
    $page_title = single_cat_title('', false);
} elseif (is_tag()) {
    $page_title = 'Topik: ' . single_tag_title('', false);
} elseif (is_archive() && !is_post_type_archive()) {
    $page_title = get_the_archive_title();
} else {
    $page_title = 'Pusat Warta &amp; Publikasi Ilmiah';
}
?>

<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background-color:#FAF8F5;font-family:'Poppins',sans-serif;color:#111827;">

    <!-- =================================================================== -->
    <!-- HERO HEADER                                                         -->
    <!-- =================================================================== -->
    <section style="position:relative;overflow:hidden;background:#0D3B22;border-bottom:1px solid rgba(255,255,255,0.1);color:#ffffff;padding:95px clamp(16px, 4vw, 40px) 45px;">
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(24,83,51,0.35);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(245,184,46,0.12);filter:blur(80px);pointer-events:none;"></div>

        <div class="bk-container" style="position:relative;width:100%;margin:0 auto;">
            <div style="display:inline-flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#F5B82E;text-transform:uppercase;letter-spacing:0.12em;margin-bottom:12px;">
                <span>Kabar Jurusan &amp; Publikasi Resmi</span>
            </div>
            <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(28px, 4vw, 44px);font-weight:500;color:#ffffff;line-height:1.2;margin:0 0 12px 0;">
                <?php echo esc_html($page_title); ?>
            </h1>
            <p style="font-size:14.5px;color:rgba(255,255,255,0.8);margin:0;max-width:700px;line-height:1.65;">
                Liputan kegiatan tridharma, seminar nasional, pengumuman akademik, dan publikasi ilmiah sivitas akademika PPB FIP Universitas Negeri Makassar.
            </p>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- CONTENT GRID                                                        -->
    <!-- =================================================================== -->
    <main style="width:100%;margin:30px 0 60px;padding:0;box-sizing:border-box;">
        <?php echo do_shortcode('[ppb_berita_grid]'); ?>
    </main>

</div>

<?php get_footer(); ?>
