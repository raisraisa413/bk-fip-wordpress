<?php
/**
 * Template for Single Post (Berita & Informasi Detail)
 * 1:1 Full-Width Scholaro Editorial Design
 */

if (!defined('ABSPATH')) exit;

get_header();

while (have_posts()) : the_post();
    $id = get_the_ID();

    // Kategori
    $cats = get_the_category($id);
    $cat_name = !empty($cats) ? $cats[0]->name : 'Berita & Kegiatan';

    // Meta Redaksi
    $ringkasan    = get_post_meta($id, '_ppb_berita_ringkasan', true);
    $author_name  = get_post_meta($id, '_ppb_berita_author_nama', true);
    if (empty($author_name)) $author_name = get_the_author();

    $author_role  = get_post_meta($id, '_ppb_berita_author_role', true) ?: 'admin';
    $role_label   = 'Pengelola';
    if ($author_role === 'dosen') $role_label = 'Dosen Bimbingan &amp; Konseling';
    elseif ($author_role === 'mahasiswa') $role_label = 'Mahasiswa BK FIP UNM';
    elseif ($author_role === 'alumni') $role_label = 'Alumni BK / Mitra Kelembagaan';

    $current_url = get_permalink($id);
    $share_wa = 'https://wa.me/?text=' . rawurlencode(get_the_title() . "\n\nBaca selengkapnya di: " . $current_url);
?>

<div class="bk-page-wrapper" style="width:100%;background-color:#FAF8F5;min-height:100vh;font-family:'Plus Jakarta Sans',sans-serif;color:#111827;">

    <!-- =================================================================== -->
    <!-- 1. HERO BAND (SCHOLARO DEEP FOREST GREEN)                           -->
    <!-- =================================================================== -->
    <section style="position:relative;overflow:hidden;background:#0D3B22;border-bottom:1px solid rgba(255,255,255,0.1);color:#ffffff;padding:var(--bk-top-clearance, 36px) 0 clamp(35px, 5vw, 50px);">
        <!-- Ambient Glow Orbs -->
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(24,83,51,0.35);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(245,184,46,0.12);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;inset:0;background-image:radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);background-size:24px 24px;pointer-events:none;"></div>

        <div class="bk-container" style="position:relative;">
            <!-- Top Breadcrumb & Back Link -->
            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(home_url('/berita/')); ?>" style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.75);text-decoration:none;transition:color 0.2s;">
                    &larr; <span>Kembali ke Semua Warta &amp; Berita</span>
                </a>

                <?php if (current_user_can('edit_post', $id)) : ?>
                    <a href="<?php echo esc_url(get_edit_post_link($id)); ?>" style="display:inline-flex;align-items:center;gap:5px;background:#F5B82E;color:#0D3B22;font-size:11.5px;font-weight:700;padding:5px 12px;border-radius:9999px;text-decoration:none;">
                        <span>Edit Artikel di WP Admin</span>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Category Badge -->
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.12);backdrop-filter:blur(8px);border:1px solid rgba(255,255,255,0.2);color:#F5B82E;font-size:11px;font-weight:700;padding:4px 12px;border-radius:9999px;text-transform:uppercase;letter-spacing:0.06em;">
                    <?php echo esc_html($cat_name); ?>
                </span>
            </div>

            <!-- Main Title (Fraunces Display) -->
            <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(26px, 3.8vw, 42px);font-weight:600;color:#ffffff;line-height:1.22;letter-spacing:-0.02em;margin:0 0 16px 0;">
                <?php the_title(); ?>
            </h1>

            <?php if (!empty($ringkasan)) : ?>
                <p style="font-size:clamp(15px, 1.8vw, 17.5px);line-height:1.68;color:rgba(255,255,255,0.88);margin:0 0 24px 0;font-style:italic;">
                    <?php echo esc_html($ringkasan); ?>
                </p>
            <?php endif; ?>

            <!-- Byline & Share Bar -->
            <div style="border-top:1px solid rgba(255,255,255,0.14);padding-top:18px;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
                <div style="display:flex;align-items:center;gap:10px;">
                    <div style="width:38px;height:38px;border-radius:50%;background:#F5B82E;color:#0D3B22;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:14px;font-family:'Fraunces',Georgia,serif;">
                        <?php echo strtoupper(substr($author_name, 0, 1)); ?>
                    </div>
                    <div>
                        <strong style="color:#ffffff;font-size:13.5px;display:block;line-height:1.2;">
                            <?php echo esc_html($author_name); ?>
                        </strong>
                        <span style="font-size:11.5px;color:rgba(255,255,255,0.7);">
                            <?php echo esc_html($role_label); ?> &middot; <?php echo get_the_date('j F Y'); ?>
                        </span>
                    </div>
                </div>

                <!-- Share Buttons -->
                <div style="display:flex;align-items:center;gap:8px;">
                    <a href="<?php echo esc_url($share_wa); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.1);border:1px solid rgba(255,255,255,0.18);color:#ffffff;font-size:12px;font-weight:600;padding:6px 14px;border-radius:9999px;text-decoration:none;">
                        <span>Bagikan ke WhatsApp</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. ARTICLE BODY CONTENT                                             -->
    <!-- =================================================================== -->
    <main class="bk-container bk-subpage-main" style="margin:-25px auto 60px !important;padding-bottom:60px !important;position:relative;z-index:10;">
        <article style="background:#ffffff;border:1px solid #D4E3D8;border-radius:28px;padding:clamp(24px, 4.5vw, 48px);box-shadow:0 8px 30px -10px rgba(13,59,34,0.08);">
            
            <?php if (has_post_thumbnail()) : ?>
                <div style="margin:-10px -10px 30px -10px;border-radius:20px;overflow:hidden;max-height:480px;">
                    <?php the_post_thumbnail('full', array('style' => 'width:100%;height:auto;object-fit:cover;display:block;')); ?>
                </div>
            <?php endif; ?>

            <!-- Article HTML Content -->
            <div class="ppb-article-body" style="font-size:15px;line-height:1.82;color:#374151;">
                <?php the_content(); ?>
            </div>

            <!-- Tags Section -->
            <?php
            $tags = get_the_tags();
            if (!empty($tags)) : ?>
                <div style="margin-top:35px;padding-top:20px;border-top:1px solid #E5E7EB;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <span style="font-size:11.5px;font-weight:700;color:#0D3B22;text-transform:uppercase;letter-spacing:0.06em;">Topik Terkait:</span>
                    <?php foreach ($tags as $t) : ?>
                        <span style="background:#FAF8F5;border:1px solid #E2E8F0;font-size:11.5px;font-weight:600;color:#4B5563;padding:3px 10px;border-radius:6px;">
                            #<?php echo esc_html($t->name); ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Bottom Author Box -->
            <div style="margin-top:40px;background:#FAF8F5;border:1px solid #E5E7EB;border-radius:20px;padding:22px;display:flex;align-items:center;gap:16px;">
                <div style="width:48px;height:48px;border-radius:50%;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18px;font-family:'Fraunces',Georgia,serif;flex-shrink:0;">
                    <?php echo strtoupper(substr($author_name, 0, 1)); ?>
                </div>
                <div>
                    <span style="font-size:11px;font-weight:700;color:#B45309;text-transform:uppercase;letter-spacing:0.08em;display:block;margin-bottom:2px;">Ditulis &amp; Dipublikasikan Oleh</span>
                    <strong style="font-family:'Fraunces',Georgia,serif;font-size:16px;color:#111827;display:block;">
                        <?php echo esc_html($author_name); ?>
                    </strong>
                    <span style="font-size:12px;color:#6B7280;"><?php echo esc_html($role_label); ?> &middot; Jurusan Psikologi Pendidikan dan Bimbingan, Fakultas Ilmu Pendidikan UNM</span>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div style="margin-top:35px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
                <a href="<?php echo esc_url(home_url('/berita/')); ?>" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:9999px;background:#E8F0EA;color:#0D3B22;font-size:12.5px;font-weight:700;text-decoration:none;">
                    &larr; <span>Kembali ke Indeks Warta</span>
                </a>

                <a href="<?php echo esc_url($share_wa); ?>" target="_blank" rel="noopener" style="display:inline-flex;align-items:center;gap:6px;padding:10px 20px;border-radius:9999px;background:#F5B82E;color:#0D3B22;font-size:12.5px;font-weight:700;text-decoration:none;box-shadow:0 3px 10px rgba(245,184,46,0.3);">
                    <span>Bagikan Artikel Ini &rarr;</span>
                </a>
            </div>
        </article>

        <!-- Related News / Berita Lainnya -->
        <div style="margin-top:50px;">
            <div style="font-size:11px;font-weight:700;color:#B45309;text-transform:uppercase;letter-spacing:0.15em;margin-bottom:6px;">Publikasi Terkait</div>
            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:22px;color:#0D3B22;margin:0 0 20px 0;font-weight:600;">Warta &amp; Artikel Lainnya</h3>

            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(260px, 1fr));gap:16px;">
                <?php
                $related = new WP_Query(array(
                    'post_type'      => 'post',
                    'posts_per_page' => 3,
                    'post__not_in'   => array($id),
                    'orderby'        => 'date',
                    'order'          => 'DESC',
                ));
                while ($related->have_posts()) : $related->the_post();
                    $rel_cats = get_the_category();
                    $rel_cat_name = !empty($rel_cats) ? $rel_cats[0]->name : 'Berita';
                ?>
                <a href="<?php the_permalink(); ?>" style="background:#ffffff;border:1px solid #D4E3D8;border-radius:18px;padding:18px;text-decoration:none;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 4px 12px rgba(13,59,34,0.04);transition:transform 0.2s;">
                    <div>
                        <span style="font-size:10px;font-weight:700;background:#E8F0EA;color:#0D3B22;padding:2px 8px;border-radius:9999px;text-transform:uppercase;display:inline-block;margin-bottom:8px;">
                            <?php echo esc_html($rel_cat_name); ?>
                        </span>
                        <h4 style="font-family:'Fraunces',Georgia,serif;font-size:14px;font-weight:600;color:#111827;line-height:1.4;margin:0 0 6px 0;">
                            <?php the_title(); ?>
                        </h4>
                    </div>
                    <span style="font-size:11px;color:#0D3B22;font-weight:700;margin-top:10px;display:flex;align-items:center;gap:4px;">
                        Baca Selengkapnya &rarr;
                    </span>
                </a>
                <?php
                endwhile;
                wp_reset_postdata();
                ?>
            </div>
        </div>
    </main>

</div>

<style>
.ppb-article-body h2, .ppb-article-body h3, .ppb-article-body h4 {
    font-family: 'Fraunces', Georgia, serif;
    color: #0D3B22;
    margin-top: 1.8em;
    margin-bottom: 0.6em;
    font-weight: 600;
}
.ppb-article-body p {
    margin-bottom: 1.4em;
}
.ppb-article-body blockquote {
    font-family: 'Fraunces', Georgia, serif;
    border-left: 4px solid #F5B82E;
    background: #FAF8F5;
    padding: 16px 22px;
    margin: 24px 0;
    border-radius: 0 16px 16px 0;
    font-style: italic;
    color: #0D3B22;
    font-size: 16px;
    line-height: 1.7;
}
.ppb-article-body ul, .ppb-article-body ol {
    margin-bottom: 1.4em;
    padding-left: 24px;
}
.ppb-article-body li {
    margin-bottom: 0.5em;
}
</style>

<?php
endwhile;

get_footer();
