<?php
/**
 * The template for displaying all pages - 100% Full-Width matching Local WP
 */

if (!defined('ABSPATH')) exit;

get_header();

// Disable wpautop to prevent rogue <p> and <br> tags from breaking CSS Grids and buttons
remove_filter('the_content', 'wpautop');

if (have_posts()) : while (have_posts()) : the_post();
    global $post, $wpdb;
    $content = get_the_content();
    $current_slug = $post->post_name;

    // FAILSAFE: If content in DB lacks 'bk-page-wrapper' and 'elementor',
    // or is empty/truncated, automatically restore the full rich content from pages-data.json!
    $has_modern_markup = (strpos($content, 'bk-page-wrapper') !== false || strpos($content, 'elementor') !== false);
    $is_empty = empty(trim(strip_tags($content))) || strlen(trim($content)) < 100;
    $is_dosen = (strpos($current_slug, 'dosen') !== false);
    $needs_dosen_refresh = ($is_dosen && (strpos($content, 'bk-dosen-card-name') === false || strlen($content) < 1000 || isset($_GET['refresh_dosen'])));
    if (!$has_modern_markup || $is_empty || $needs_dosen_refresh) {
        $json_file = get_template_directory() . '/pages-data.json';
        if (file_exists($json_file)) {
            $pages_data = json_decode(file_get_contents($json_file), true);
            if (is_array($pages_data)) {
                foreach ($pages_data as $p) {
                    if ($p['slug'] === $current_slug || ($is_dosen && $p['slug'] === 'dosen')) {
                        $content = $p['content'];
                        // Instantly repair in database
                        $wpdb->update(
                            $wpdb->posts,
                            array('post_content' => $content, 'post_status' => 'publish'),
                            array('ID' => $post->ID)
                        );
                        clean_post_cache($post->ID);
                        break;
                    }
                }
            }
        }
    }
    ?>

    <main class="flex-grow w-full" style="width:100%;margin:0;padding:0;overflow-x:hidden;">
        <?php
        // Render full-width layout matching local WP 1:1
        if (strpos($content, 'bk-page-wrapper') !== false || strpos($content, 'bk-section') !== false || strpos($content, 'elementor') !== false) {
            echo apply_filters('the_content', $content);
        } else {
            // Full-width editorial fallback matching Scholaro design
            ?>
            <div class="bk-page-wrapper" style="width:100%;min-height:80vh;background:#FAF8F5;color:#1F2937;font-family:'Poppins',sans-serif;line-height:1.6;margin:0;padding:0;">
                <!-- Hero Header Band -->
                <section style="position:relative;overflow:hidden;background:#0D3B22;color:#ffffff;padding:var(--bk-top-clearance, 125px) 0 45px;font-family:'Poppins',sans-serif;width:100%;">
                    <div class="bk-container" style="position:relative;">
                        <div style="display:inline-flex;align-items:center;gap:7px;padding:5px 14px;border-radius:9999px;background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.15);color:#F5B82E;font-size:11px;font-weight:700;letter-spacing:0.06em;text-transform:uppercase;margin-bottom:14px;">
                            <span>Jurusan PPB FIP UNM</span>
                        </div>
                        <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(28px, 4vw, 44px);color:#ffffff;margin:0;font-weight:600;line-height:1.2;">
                            <?php the_title(); ?>
                        </h1>
                    </div>
                </section>
                <!-- Body Content -->
                <section style="width:100%;padding:40px 0 60px;">
                    <div class="bk-container"><div style="background:#ffffff;border:1px solid #D4E3D8;border-radius:24px;padding:clamp(24px, 4vw, 44px);box-shadow:0 6px 24px rgba(0,0,0,0.04);font-size:15px;line-height:1.8;color:#374151;">
                        <?php echo apply_filters('the_content', $content); ?>
                    </div>
                </div></section>
            </div>
            <?php
        }
        ?>
    </main>
    <?php
endwhile; endif;

get_footer();
