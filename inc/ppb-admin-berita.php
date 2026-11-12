<?php
/**
 * Module: PPB FIP UNM - Manajemen Berita & Informasi Resmi
 * Description: Mengubah menu standar Posts menjadi "Berita & Informasi" dengan kategori resmi, meta box redaksi, kolom thumbnail, dan shortcode portal berita Scholaro.
 * Author: Jurusan PPB FIP UNM
 */

if (!defined('ABSPATH')) exit;

/* ========================================================================== */
/* 1. KUSTOMISASI MENU WP ADMIN: POSTS -> BERITA & INFORMASI                  */
/* ========================================================================== */
if (!function_exists('ppb_customize_berita_menu')) {
function ppb_customize_berita_menu() {
    global $menu, $submenu;

    // Ubah nama menu utama "Posts"
    foreach ($menu as $key => $item) {
        if ($item[2] === 'edit.php') {
            $menu[$key][0] = 'Berita & Informasi';
            $menu[$key][6] = 'dashicons-megaphone'; // Icon pengumuman/berita
            break;
        }
    }

    // Ubah sub-menu
    if (isset($submenu['edit.php'])) {
        $submenu['edit.php'][5][0]  = 'Semua Berita';
        $submenu['edit.php'][10][0] = 'Tambah Berita Baru';
        $submenu['edit.php'][15][0] = 'Kategori Berita';
        $submenu['edit.php'][16][0] = 'Tag Berita';
    }
}
}
add_action('admin_menu', 'ppb_customize_berita_menu');

// Ubah label post type post
if (!function_exists('ppb_change_post_labels')) {
function ppb_change_post_labels() {
    global $wp_post_types;
    $labels = &$wp_post_types['post']->labels;
    $labels->name               = 'Berita & Informasi';
    $labels->singular_name      = 'Berita';
    $labels->add_new            = 'Tambah Berita Baru';
    $labels->add_new_item       = 'Tambah Berita Baru';
    $labels->edit_item          = 'Edit Berita';
    $labels->new_item           = 'Berita Baru';
    $labels->view_item          = 'Lihat Berita';
    $labels->search_items       = 'Cari Berita';
    $labels->not_found          = 'Tidak ada berita ditemukan';
    $labels->not_found_in_trash = 'Tidak ada berita di tempat sampah';
    $labels->all_items          = 'Semua Berita';
    $labels->menu_name          = 'Berita & Informasi';
    $labels->name_admin_bar     = 'Berita Baru';
}
}
add_action('init', 'ppb_change_post_labels');

/* ========================================================================== */
/* 2. AUTO-ENSURE 6 KATEGORI RESMI PRODI BK                                   */
/* ========================================================================== */
if (!function_exists('ppb_ensure_berita_categories')) {
function ppb_ensure_berita_categories() {
    $categories = [
        'pengumuman' => [
            'name' => 'Pengumuman Resmi',
            'desc' => 'Pengumuman formal jurusan, dekanat, rektorat, dan akademik.',
        ],
        'berita' => [
            'name' => 'Berita & Kegiatan',
            'desc' => 'Liputan kegiatan tridharma, seminar, workshop, dan agenda kampus.',
        ],
        'opini' => [
            'name' => 'Opini Mahasiswa',
            'desc' => 'Refleksi, esai, dan pandangan kritis mahasiswa bimbingan konseling.',
        ],
        'prestasi' => [
            'name' => 'Prestasi & Inovasi',
            'desc' => 'Kabar pencapaian kejuaraan, hibah, dan inovasi civitas akademika.',
        ],
        'psikologi_bk' => [
            'name' => 'Artikel BK & Psikologi',
            'desc' => 'Kajian keilmuan bimbingan konseling, asesmen, dan kesehatan mental.',
        ],
        'ilmiah_populer' => [
            'name' => 'Karya Ilmiah Populer',
            'desc' => 'Publikasi ringkasan riset, jurnal, dan naskah ilmiah adaptif.',
        ],
    ];

    foreach ($categories as $slug => $cat) {
        if (!term_exists($slug, 'category')) {
            wp_insert_term($cat['name'], 'category', [
                'slug'        => $slug,
                'description' => $cat['desc'],
            ]);
        }
    }
}
}
add_action('init', 'ppb_ensure_berita_categories');

/* ========================================================================== */
/* 3. META BOX REDAKSI BERITA & INFORMASI                                     */
/* ========================================================================== */
add_action('admin_enqueue_scripts', function($hook) {
    if (in_array($hook, ['post.php', 'post-new.php'])) {
        wp_enqueue_media();
    }
});

if (!function_exists('ppb_berita_add_meta_box')) {
function ppb_berita_add_meta_box() {
    add_meta_box(
        'ppb_berita_editorial_meta',
        'Informasi Redaksi & Publikasi Artikel',
        'ppb_berita_render_meta_box',
        'post',
        'normal',
        'high'
    );
}
}
add_action('add_meta_boxes', 'ppb_berita_add_meta_box');

if (!function_exists('ppb_berita_render_meta_box')) {
function ppb_berita_render_meta_box($post) {
    wp_nonce_field('ppb_berita_save_meta', 'ppb_berita_meta_nonce');

    $thumb_id     = get_post_thumbnail_id($post->ID);
    $thumb_url    = $thumb_id ? wp_get_attachment_image_url($thumb_id, 'medium') : '';
    $ringkasan    = get_post_meta($post->ID, '_ppb_berita_ringkasan', true);
    if (empty($ringkasan)) $ringkasan = $post->post_excerpt;

    $penulis_nama = get_post_meta($post->ID, '_ppb_berita_author_nama', true);
    if (empty($penulis_nama)) {
        $user = get_userdata($post->post_author);
        $penulis_nama = $user ? $user->display_name : 'Admin Program Studi';
    }
    $penulis_role = get_post_meta($post->ID, '_ppb_berita_author_role', true) ?: 'admin';
    ?>
    <style>
        .ppb-berita-row { margin-bottom: 16px; }
        .ppb-berita-row label { display: block; font-weight: 600; margin-bottom: 5px; color: #1e293b; font-size: 13px; }
        .ppb-berita-row input[type="text"],
        .ppb-berita-row input[type="number"],
        .ppb-berita-row select,
        .ppb-berita-row textarea { width: 100%; max-width: 720px; padding: 8px 12px; border: 1px solid #cbd5e1; border-radius: 6px; font-size: 13.5px; }
        .ppb-berita-hint { display: block; font-size: 11.5px; color: #64748b; margin-top: 4px; }
        .ppb-berita-tip { background: #FAF8F5; border: 1px solid #E5E7EB; border-left: 4px solid #F5B82E; padding: 10px 14px; border-radius: 6px; margin-bottom: 16px; font-size: 12.5px; color: #374151; }
    </style>

    <!-- KOTAK UPLOAD & GANTI FOTO SAMPUL LANGSUNG -->
    <div class="ppb-berita-row" style="background:#FAF8F5;border:1.5px solid #D4E3D8;border-radius:12px;padding:16px;margin-bottom:20px;">
        <label style="font-size:13.5px;color:#0D3B22;font-weight:700;display:flex;align-items:center;gap:6px;margin-bottom:10px;">
            <span style="font-size:16px;">📸</span> Foto Sampul Berita (Banner Utama):
        </label>
        
        <div style="display:flex;align-items:center;gap:18px;flex-wrap:wrap;">
            <div id="ppb-thumb-preview" style="width:160px;height:100px;border-radius:10px;background:#E8F0EA;border:1px dashed #0D3B22;display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <?php if ($thumb_url) : ?>
                    <img src="<?php echo esc_url($thumb_url); ?>" style="width:100%;height:100%;object-fit:cover;">
                <?php else : ?>
                    <span style="font-size:11px;color:#0D3B22;font-weight:600;text-align:center;padding:6px;">Belum ada foto</span>
                <?php endif; ?>
            </div>

            <div>
                <input type="hidden" id="ppb_berita_thumb_id" name="ppb_berita_thumb_id" value="<?php echo esc_attr($thumb_id); ?>">
                
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                    <button type="button" id="ppb_btn_select_thumb" class="button button-primary" style="background:#0D3B22;border-color:#0D3B22;color:#F5B82E;font-weight:700;padding:4px 14px;height:auto;">
                        <?php echo $thumb_url ? '📷 Ganti Foto Sampul' : '📷 Pilih / Unggah Foto Sampul'; ?>
                    </button>

                    <button type="button" id="ppb_btn_remove_thumb" class="button" style="color:#dc2626;border-color:#fca5a5;padding:4px 12px;height:auto;<?php echo $thumb_url ? '' : 'display:none;'; ?>">
                        ✕ Hapus Foto
                    </button>
                </div>

                <span class="ppb-berita-hint">
                    Format yang disarankan: JPG, PNG, atau WebP. Rasio ideal lanskap <strong>16:9</strong> (misal: 1200 × 675 piksel).
                </span>
            </div>
        </div>
    </div>

    <script>
    jQuery(document).ready(function($){
        var frame;
        $('#ppb_btn_select_thumb').on('click', function(e){
            e.preventDefault();
            if (frame) {
                frame.open();
                return;
            }
            frame = wp.media({
                title: 'Pilih atau Unggah Foto Sampul Berita',
                button: { text: 'Gunakan Foto Ini Sebagai Sampul' },
                multiple: false
            });
            frame.on('select', function(){
                var attachment = frame.state().get('selection').first().toJSON();
                var url = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                $('#ppb_berita_thumb_id').val(attachment.id);
                $('#ppb-thumb-preview').html('<img src="' + url + '" style="width:100%;height:100%;object-fit:cover;">');
                $('#ppb_btn_select_thumb').text('📷 Ganti Foto Sampul');
                $('#ppb_btn_remove_thumb').show();
            });
            frame.open();
        });

        $('#ppb_btn_remove_thumb').on('click', function(e){
            e.preventDefault();
            $('#ppb_berita_thumb_id').val('');
            $('#ppb-thumb-preview').html('<span style="font-size:11px;color:#0D3B22;font-weight:600;text-align:center;padding:6px;">Belum ada foto</span>');
            $('#ppb_btn_select_thumb').text('📷 Pilih / Unggah Foto Sampul');
            $(this).hide();
        });
    });
    </script>

    <div class="ppb-berita-row">
        <label for="ppb_berita_ringkasan">Ringkasan / Sinopsis Artikel (Excerpt):</label>
        <textarea id="ppb_berita_ringkasan" name="ppb_berita_ringkasan" rows="3" placeholder="Tuliskan 1 - 2 kalimat ringkasan yang menarik untuk ditampilkan pada kartu berita di halaman depan..."><?php echo esc_textarea($ringkasan); ?></textarea>
        <span class="ppb-berita-hint">Tampil sebagai teks pengantar di kartu warta dan di bawah judul artikel.</span>
    </div>

    <div style="display:flex;flex-wrap:wrap;gap:20px;">
        <div class="ppb-berita-row" style="flex:1;min-width:240px;">
            <label for="ppb_berita_author_nama">Nama Penulis / Kontributor:</label>
            <input type="text" id="ppb_berita_author_nama" name="ppb_berita_author_nama" value="<?php echo esc_attr($penulis_nama); ?>" placeholder="Contoh: Dr. Suciani Latif atau Nurul Fadhilah">
        </div>

        <div class="ppb-berita-row" style="flex:1;min-width:240px;">
            <label for="ppb_berita_author_role">Status / Peran Penulis:</label>
            <select id="ppb_berita_author_role" name="ppb_berita_author_role">
                <option value="admin" <?php selected($penulis_role, 'admin'); ?>>Admin Program Studi / Pengelola</option>
                <option value="dosen" <?php selected($penulis_role, 'dosen'); ?>>Dosen Bimbingan &amp; Konseling</option>
                <option value="mahasiswa" <?php selected($penulis_role, 'mahasiswa'); ?>>Mahasiswa BK FIP UNM</option>
                <option value="alumni" <?php selected($penulis_role, 'alumni'); ?>>Alumni BK / Mitra Kelembagaan</option>
            </select>
        </div>
    </div>
    <?php
}
}

if (!function_exists('ppb_berita_save_meta')) {
function ppb_berita_save_meta($post_id) {
    if (!isset($_POST['ppb_berita_meta_nonce']) || !wp_verify_nonce($_POST['ppb_berita_meta_nonce'], 'ppb_berita_save_meta')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (!current_user_can('edit_post', $post_id)) return;

    // Simpan / update Foto Sampul (Featured Image)
    if (isset($_POST['ppb_berita_thumb_id'])) {
        $new_thumb = intval($_POST['ppb_berita_thumb_id']);
        if ($new_thumb > 0) {
            set_post_thumbnail($post_id, $new_thumb);
        } elseif ($_POST['ppb_berita_thumb_id'] === '') {
            delete_post_thumbnail($post_id);
        }
    }

    if (isset($_POST['ppb_berita_ringkasan'])) {
        $ring = sanitize_textarea_field($_POST['ppb_berita_ringkasan']);
        update_post_meta($post_id, '_ppb_berita_ringkasan', $ring);

        // Sinkronkan juga ke post_excerpt bawaan WP
        remove_action('save_post', 'ppb_berita_save_meta');
        wp_update_post([
            'ID' => $post_id,
            'post_excerpt' => $ring,
        ]);
        add_action('save_post', 'ppb_berita_save_meta');
    }

    if (isset($_POST['ppb_berita_author_nama'])) {
        update_post_meta($post_id, '_ppb_berita_author_nama', sanitize_text_field($_POST['ppb_berita_author_nama']));
    }

    if (isset($_POST['ppb_berita_author_role'])) {
        update_post_meta($post_id, '_ppb_berita_author_role', sanitize_text_field($_POST['ppb_berita_author_role']));
    }

    // Purge element cache untuk halaman Berita (Post 20)
    delete_post_meta(20, '_elementor_element_cache');
    delete_post_meta(20, '_elementor_css');
}
}
add_action('save_post', 'ppb_berita_save_meta');

/* ========================================================================== */
/* 4. KOLOM TABEL DAFTAR BERITA DI WP ADMIN                                   */
/* ========================================================================== */
add_filter('manage_post_posts_columns', function($columns) {
    $new = [];
    $new['cb']         = $columns['cb'];
    $new['foto']       = 'Sampul';
    $new['title']      = 'Judul Berita & Warta';
    $new['kategori']   = 'Kategori';
    $new['penulis']    = 'Penulis Redaksi';
    $new['date']       = $columns['date'];
    $new['view_link']  = 'Lihat';
    return $new;
});

add_action('manage_post_posts_custom_column', function($column, $post_id) {
    switch ($column) {
        case 'foto':
            if (has_post_thumbnail($post_id)) {
                echo get_the_post_thumbnail($post_id, [60, 42], ['style' => 'border-radius:6px;object-fit:cover;width:60px;height:42px;border:1px solid #E2E8F0;']);
            } else {
                echo '<div style="width:60px;height:42px;border-radius:6px;background:#E8F0EA;display:flex;align-items:center;justify-content:center;color:#0D3B22;font-size:10px;font-weight:700;">Tanpa Foto</div>';
            }
            break;

        case 'kategori':
            $cats = get_the_category($post_id);
            if (!empty($cats)) {
                $c = $cats[0];
                $bg = '#E8F0EA';
                $color = '#0D3B22';
                if ($c->slug === 'pengumuman') { $bg = '#E0F2FE'; $color = '#0369A1'; }
                elseif ($c->slug === 'prestasi') { $bg = '#FEF3C7'; $color = '#92400E'; }
                elseif ($c->slug === 'opini') { $bg = '#EDE9FE'; $color = '#6D28D9'; }
                elseif ($c->slug === 'psikologi_bk') { $bg = '#CCFBF1'; $color = '#0F766E'; }
                elseif ($c->slug === 'ilmiah_populer') { $bg = '#E0E7FF'; $color = '#3730A3'; }

                echo '<span style="background:' . $bg . ';color:' . $color . ';font-size:10.5px;font-weight:700;padding:2px 8px;border-radius:9999px;">' . esc_html($c->name) . '</span>';
            } else {
                echo '-';
            }
            break;

        case 'penulis':
            $p_nama = get_post_meta($post_id, '_ppb_berita_author_nama', true);
            $p_role = get_post_meta($post_id, '_ppb_berita_author_role', true) ?: 'admin';
            if (!$p_nama) {
                $user = get_userdata(get_post_field('post_author', $post_id));
                $p_nama = $user ? $user->display_name : 'Admin Prodi';
            }
            echo '<strong style="display:block;font-size:12px;color:#1e293b;">' . esc_html($p_nama) . '</strong>';
            echo '<span style="font-size:10.5px;color:#64748b;">' . esc_html(ucfirst($p_role)) . '</span>';
            break;

        case 'view_link':
            echo '<a href="' . esc_url(get_permalink($post_id)) . '" target="_blank" style="display:inline-block;padding:3px 8px;background:#0D3B22;color:#F5B82E;font-size:11px;font-weight:700;border-radius:4px;text-decoration:none;">Buka ↗</a>';
            break;
    }
}, 10, 2);

/* ========================================================================== */
/* 5. ENGINE FRONTEND SHORTCODE: [ppb_berita_grid]                            */
/* ========================================================================== */

if (!function_exists('ppb_get_sample_berita_items')) {
function ppb_get_sample_berita_items() {
    $theme_uri = get_template_directory_uri();
    return [
        [
            'id'             => 99901,
            'title'          => 'Seminar Nasional Konseling Multikultural & Penguatan Karakter Nusantara di FIP UNM',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-2 days')),
            'date_iso'       => date('c', strtotime('-2 days')),
            'thumb'          => $theme_uri . '/assets/img/gedung-unm.png',
            'cat_slug'       => 'berita',
            'cat_name'       => 'Berita & Kegiatan',
            'ringkasan'      => 'Jurusan PPB FIP UNM menggelar seminar keilmuan menghadirkan pakar bimbingan dan konseling nasional guna membedah integrasi kearifan lokal Nusantara dalam intervensi konseling modern.',
            'author_name'    => 'Dr. Farida M., M.Pd.',
            'author_role'    => 'Dosen BK',
        ],
        [
            'id'             => 99902,
            'title'          => 'Pengumuman Pelaksanaan Ujian Skripsi & Yudisium Semester Ganjil TA 2026/2027',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-5 days')),
            'date_iso'       => date('c', strtotime('-5 days')),
            'thumb'          => $theme_uri . '/assets/img/banner-prodi-1.png',
            'cat_slug'       => 'pengumuman',
            'cat_name'       => 'Pengumuman Resmi',
            'ringkasan'      => 'Bagi mahasiswa tingkat akhir S1, S2, dan S3 BK FIP UNM, pendaftaran berkas ujian skripsi dan yudisium telah dibuka melalui portal akademik jurusan.',
            'author_name'    => 'Tim Akademik Jurusan',
            'author_role'    => 'Pengelola',
        ],
        [
            'id'             => 99903,
            'title'          => 'Mahasiswa BK FIP UNM Raih Juara 1 Lomba Inovasi Media Konseling Tingkat Nasional',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-8 days')),
            'date_iso'       => date('c', strtotime('-8 days')),
            'thumb'          => '',
            'cat_slug'       => 'prestasi',
            'cat_name'       => 'Prestasi & Inovasi',
            'ringkasan'      => 'Karya inovatif berbasis digital asesmen konseling yang dirancang mahasiswa BK berhasil menyabet penghargaan bergengsi dalam kompetisi inovasi mahasiswa se-Indonesia.',
            'author_name'    => 'Siti Nurhaliza',
            'author_role'    => 'Mahasiswa BK',
        ],
        [
            'id'             => 99904,
            'title'          => 'Pendekatan Solution-Focused Brief Therapy (SFBT) dalam Pelayanan Siswa di Era Digital',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-12 days')),
            'date_iso'       => date('c', strtotime('-12 days')),
            'thumb'          => '',
            'cat_slug'       => 'psikologi_bk',
            'cat_name'       => 'Artikel BK & Psikologi',
            'ringkasan'      => 'Ulasan mendalam mengenai efektivitas konseling singkat berfokus solusi untuk menangani tantangan kesehatan mental dan kecemasan akademik peserta didik.',
            'author_name'    => 'Prof. Dr. H. M. Arifin',
            'author_role'    => 'Guru Besar BK',
        ],
        [
            'id'             => 99905,
            'title'          => 'Menemukan Kembali Nilai Sipakatau dalam Relasi Konselor dan Klien di Sekolah',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-15 days')),
            'date_iso'       => date('c', strtotime('-15 days')),
            'thumb'          => '',
            'cat_slug'       => 'ilmiah_populer',
            'cat_name'       => 'Karya Ilmiah Populer',
            'ringkasan'      => 'Eksplorasi falsafah etika Bugis-Makassar sebagai modalitas relasi terapeutik konseling yang memanusiakan dan menumbuhkan rasa saling menghormati.',
            'author_name'    => 'Dr. Ahmad Harum, M.Pd.',
            'author_role'    => 'Dosen BK',
        ],
        [
            'id'             => 99906,
            'title'          => 'Opini: Tantangan Etika Kerahasiaan Konseling di Tengah Ledakan Media Sosial',
            'url'            => home_url('/berita/'),
            'date_formatted' => date_i18n('j F Y', strtotime('-20 days')),
            'date_iso'       => date('c', strtotime('-20 days')),
            'thumb'          => '',
            'cat_slug'       => 'opini',
            'cat_name'       => 'Opini Mahasiswa',
            'ringkasan'      => 'Bagaimana calon konselor menjaga asas kerahasiaan ABKIN saat batasan privasi personal dan publik semakin kabur di era digital?',
            'author_name'    => 'Andi Muh. Rian',
            'author_role'    => 'Mahasiswa BK',
        ],
    ];
}
}

/* ========================================================================== */
/* 5. ENGINE FRONTEND SHORTCODE: [ppb_berita_grid]                            */
/* Swiss Modernism 2.0 + Academic Editorial + shadcn/ui                       */
/* ========================================================================== */
if (!function_exists('ppb_render_berita_grid_swiss')) {
function ppb_render_berita_grid_swiss() {
    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $query = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $all_posts = [];
    while ($query->have_posts()) {
        $query->the_post();
        $id = get_the_ID();

        // Kategori
        $cats = get_the_category($id);
        $cat_slug = !empty($cats) ? $cats[0]->slug : 'berita';
        $cat_name = !empty($cats) ? $cats[0]->name : 'Berita & Kegiatan';

        // Thumbnail / Gambar Sampul
        $thumb_url = '';
        if (has_post_thumbnail($id)) {
            $thumb_url = get_the_post_thumbnail_url($id, 'large');
        }

        // Meta Redaksi
        $ringkasan = get_post_meta($id, '_ppb_berita_ringkasan', true);
        if (empty($ringkasan)) $ringkasan = wp_trim_words(get_the_excerpt(), 22, '...');

        $author_name  = get_post_meta($id, '_ppb_berita_author_nama', true);
        if (empty($author_name)) $author_name = get_the_author();

        $author_role  = get_post_meta($id, '_ppb_berita_author_role', true) ?: 'admin';
        $role_label   = 'Pengelola';
        if ($author_role === 'dosen') $role_label = 'Dosen BK';
        elseif ($author_role === 'mahasiswa') $role_label = 'Mahasiswa BK';
        elseif ($author_role === 'alumni') $role_label = 'Alumni BK';

        $all_posts[] = [
            'id'             => $id,
            'title'          => get_the_title(),
            'url'            => get_permalink($id),
            'date_formatted' => get_the_date('j F Y'),
            'date_iso'       => get_the_date('c'),
            'thumb'          => $thumb_url,
            'cat_slug'       => $cat_slug,
            'cat_name'       => $cat_name,
            'ringkasan'      => $ringkasan,
            'author_name'    => $author_name,
            'author_role'    => $role_label,
        ];
    }
    wp_reset_postdata();

    // Fallback sample data if no posts in DB yet
    if (empty($all_posts) && function_exists('ppb_get_sample_berita_items')) {
        $all_posts = ppb_get_sample_berita_items();
    }

    $cat_tabs = [
        'semua'          => 'Semua Kategori',
        'pengumuman'     => 'Pengumuman Resmi',
        'berita'         => 'Berita & Kegiatan',
        'opini'          => 'Opini Mahasiswa',
        'prestasi'       => 'Prestasi & Inovasi',
        'psikologi_bk'   => 'Artikel BK & Psikologi',
        'ilmiah_populer' => 'Karya Ilmiah Populer',
    ];

    ob_start();
    ?>
    <style>
        .bk-berita-grid-layout {
            display: grid !important;
            grid-template-columns: 1fr !important;
            gap: 24px !important;
            width: 100% !important;
        }
        @media (min-width: 640px) {
            .bk-berita-grid-layout {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }
        @media (min-width: 1024px) {
            .bk-berita-grid-layout {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }
    </style>
    <section class="bk-berita-section" style="padding:0 0 60px;font-family:'Plus Jakarta Sans',system-ui,-apple-system,sans-serif;width:100%;">
        <div class="bk-container" style="width:100%;box-sizing:border-box;">

            <!-- =========================================================== -->
            <!-- CONTROL PANEL: ARCHITECTURAL SEARCH & SWISS LEDGER TABS    -->
            <!-- =========================================================== -->
            <div style="margin-bottom:32px;display:flex;flex-direction:column;gap:18px;">
                <!-- Row 1: Search & Ledger Counter -->
                <div style="display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:16px;">
                    <!-- Search Input (shadcn/ui style with subtle focus ring) -->
                    <div style="position:relative;flex:1;max-width:440px;min-width:260px;">
                        <span style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#64748B;display:flex;align-items:center;pointer-events:none;">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        </span>
                        <input type="text" id="bk-berita-search" placeholder="Cari judul artikel, topik, atau penulis…" style="width:100%;height:42px;padding:0 16px 0 40px;border-radius:6px;border:1px solid #E2E8F0;background:#ffffff;font-size:13px;font-family:'Plus Jakarta Sans',system-ui,sans-serif;color:#0F172A;box-shadow:0 1px 2px rgba(0,0,0,0.03);outline:none;box-sizing:border-box;transition:border-color 0.15s ease, box-shadow 0.15s ease;" onfocus="this.style.borderColor='#0D3B22';this.style.boxShadow='0 0 0 2px rgba(13,59,34,0.12)'" onblur="this.style.borderColor='#E2E8F0';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.03)'">
                    </div>

                    <!-- Ledger Counter -->
                    <div style="font-family:'IBM Plex Mono',monospace;font-size:11.5px;font-weight:600;color:#64748B;text-transform:uppercase;letter-spacing:0.08em;" id="bk-berita-count">
                        ARSIP // <span style="color:#0D3B22;font-weight:700;"><?php echo count($all_posts); ?></span> DOKUMEN TERDATA
                    </div>
                </div>

                <!-- Row 2: Swiss Segmented Category Ledger Tabs -->
                <div style="display:flex;align-items:center;gap:6px;overflow-x:auto;padding-bottom:4px;" class="no-scrollbar" id="bk-berita-cat-pills">
                    <?php foreach ($cat_tabs as $slug => $label) : 
                        $isActive = ($slug === 'semua');
                    ?>
                    <button type="button" class="bk-berita-pill <?php echo $isActive ? 'active' : ''; ?>" data-cat="<?php echo esc_attr($slug); ?>" style="border-radius:6px;font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:<?php echo $isActive ? '700' : '600'; ?>;text-transform:uppercase;letter-spacing:0.06em;padding:7px 14px;background:<?php echo $isActive ? '#0D3B22' : '#ffffff'; ?>;color:<?php echo $isActive ? '#F5B82E' : '#475569'; ?>;border:1px solid <?php echo $isActive ? '#0D3B22' : '#E2E8F0'; ?>;cursor:pointer;white-space:nowrap;transition:all 0.15s ease;box-shadow:0 1px 2px rgba(0,0,0,0.02);">
                        <?php echo esc_html($label); ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- =========================================================== -->
            <!-- NEWS GRID: SWISS MODERNISM 2.0 EDITORIAL CARDS              -->
            <!-- =========================================================== -->
            <div id="bk-berita-grid" class="bk-berita-grid-layout">
                <?php foreach ($all_posts as $idx => $p) : 
                    $num_formatted = sprintf('%02d', $idx + 1);
                    $searchable = strtolower($p['title'] . ' ' . $p['ringkasan'] . ' ' . $p['author_name'] . ' ' . $p['cat_name']);
                ?>
                <article class="bk-berita-card-item" data-cat="<?php echo esc_attr($p['cat_slug']); ?>" data-text="<?php echo esc_attr($searchable); ?>" style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.03);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.borderColor='#CBD5E1';this.style.boxShadow='0 10px 24px -6px rgba(13,59,34,0.09)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#E2E8F0';this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';this.style.transform='translateY(0)'">
                    <div>
                        <!-- 1. Top Metadata Ledger Strip -->
                        <div style="padding:10px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;background:#FAF8F5;">
                            <span style="font-family:'IBM Plex Mono',monospace;font-size:10.5px;font-weight:600;color:#0D3B22;text-transform:uppercase;letter-spacing:0.08em;">
                                <?php echo esc_html($num_formatted . ' // ' . $p['cat_name']); ?>
                            </span>
                            <time style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#64748B;">
                                <?php echo esc_html($p['date_formatted']); ?>
                            </time>
                        </div>

                        <!-- 2. Cover Image / Academic Monograph Placeholder -->
                        <div style="position:relative;height:185px;overflow:hidden;border-bottom:1px solid #E2E8F0;background:#F8FAFC;">
                            <?php if (!empty($p['thumb'])) : ?>
                                <img src="<?php echo esc_url($p['thumb']); ?>" alt="<?php echo esc_attr($p['title']); ?>" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.4s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                            <?php else : ?>
                                <!-- Architectural Blueprint Grid Monograph (Clean academic elegance - NO toy icons) -->
                                <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background-color:#FAF8F5;background-image:linear-gradient(rgba(13,59,34,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(13,59,34,0.05) 1px, transparent 1px);background-size:20px 20px;position:relative;">
                                    <div style="padding:8px 18px;border:1px solid rgba(13,59,34,0.18);background:rgba(255,255,255,0.94);border-radius:4px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                                        <div style="font-family:'Fraunces',Georgia,serif;font-size:17px;font-weight:600;color:#0D3B22;letter-spacing:0.04em;line-height:1.2;">PPB FIP UNM</div>
                                        <div style="font-family:'IBM Plex Mono',monospace;font-size:9px;font-weight:600;color:#64748B;text-transform:uppercase;letter-spacing:0.12em;margin-top:2px;">Warta &amp; Dokumentasi</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- 3. Editorial Card Body -->
                        <div style="padding:20px 20px 14px;">
                            <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#0F172A;line-height:1.35;margin:0 0 10px 0;">
                                <a href="<?php echo esc_url($p['url']); ?>" style="color:inherit;text-decoration:none;transition:color 0.15s ease;" onmouseover="this.style.color='#0D3B22'" onmouseout="this.style.color='#0F172A'">
                                    <?php echo esc_html($p['title']); ?>
                                </a>
                            </h3>

                            <?php if (!empty($p['ringkasan'])) : ?>
                            <p style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-size:13px;color:#475569;line-height:1.65;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                                <?php echo esc_html($p['ringkasan']); ?>
                            </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- 4. Academic Editorial Footer -->
                    <div style="padding:12px 20px;border-top:1px solid #E2E8F0;display:flex;align-items:center;justify-content:space-between;background:#FAF8F5;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="width:24px;height:24px;border-radius:4px;background:#0D3B22;color:#F5B82E;display:flex;align-items:center;justify-content:center;font-family:'IBM Plex Mono',monospace;font-size:10px;font-weight:700;">
                                <?php echo strtoupper(substr($p['author_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <span style="font-family:'Plus Jakarta Sans',sans-serif;font-size:11.5px;font-weight:600;color:#1E293B;display:block;line-height:1.1;">
                                    <?php echo esc_html($p['author_name']); ?>
                                </span>
                                <span style="font-family:'IBM Plex Mono',monospace;font-size:9.5px;color:#64748B;"><?php echo esc_html($p['author_role']); ?></span>
                            </div>
                        </div>

                        <a href="<?php echo esc_url($p['url']); ?>" class="bk-berita-read-link" style="font-family:'Plus Jakarta Sans',sans-serif;display:inline-flex;align-items:center;gap:4px;font-size:12px;font-weight:700;color:#0D3B22;text-decoration:none;transition:all 0.15s ease;" onmouseover="this.style.color='#185333';this.querySelector('.arrow-ic').style.transform='translateX(3px)'" onmouseout="this.style.color='#0D3B22';this.querySelector('.arrow-ic').style.transform='translateX(0)'">
                            <span>Baca</span>
                            <span class="arrow-ic" aria-hidden="true" style="color:#F5B82E;font-size:13px;font-weight:700;transition:transform 0.15s ease;">&rarr;</span>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <!-- Empty State Box -->
            <div id="bk-berita-empty" style="display:none;padding:48px 24px;text-align:center;background:#ffffff;border:1px dashed #CBD5E1;border-radius:8px;margin-top:24px;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#64748B" stroke-width="1.75" style="margin-bottom:12px;margin-left:auto;margin-right:auto;display:block;"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <h4 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#0F172A;margin:0 0 6px 0;">Tidak Ada Publikasi Ditemukan</h4>
                <p style="font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;color:#64748B;margin:0;">Gunakan kata kunci pencarian lain atau pilih tab "Semua Kategori".</p>
            </div>

            <!-- =========================================================== -->
            <!-- BOTTOM CTA BANNER: RUANG PUBLIKASI CIVITAS AKADEMIKA        -->
            <!-- =========================================================== -->
            <div style="margin-top:48px;background:#0D3B22;color:#ffffff;border-radius:8px;padding:clamp(24px, 3.5vw, 36px);border:1px solid rgba(255,255,255,0.15);box-shadow:0 4px 16px rgba(13,59,34,0.12);display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:24px;position:relative;overflow:hidden;">
                <div style="position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);background-size:24px 24px;pointer-events:none;"></div>
                
                <div style="position:relative;max-width:680px;">
                    <div style="display:inline-flex;align-items:center;gap:6px;font-family:'IBM Plex Mono',monospace;font-size:10.5px;font-weight:600;color:#F5B82E;text-transform:uppercase;letter-spacing:0.1em;margin-bottom:10px;">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="#F5B82E" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        <span>Ruang Publikasi Civitas Akademika</span>
                    </div>
                    <h2 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(22px, 2.5vw, 28px);line-height:1.25;margin:0 0 10px 0;font-weight:500;color:#ffffff;">
                        Punya gagasan, opini, liputan kegiatan, atau kabar prestasi?
                    </h2>
                    <p style="font-family:'Plus Jakarta Sans',sans-serif;font-size:13.5px;color:rgba(255,255,255,0.85);line-height:1.65;margin:0;">
                        Mahasiswa dan Dosen Program Studi Bimbingan dan Konseling FIP UNM dapat berkontribusi mempublikasikan karya tulis ilmiah populer, refleksi konseling, liputan agenda kampus, atau pengumuman resmi ke portal ini.
                    </p>
                </div>

                <div style="position:relative;">
                    <a href="<?php echo esc_url(admin_url('post-new.php')); ?>" style="display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:6px;background:#F5B82E;color:#0D3B22;font-family:'Plus Jakarta Sans',sans-serif;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 2px 10px rgba(245,184,46,0.3);transition:all 0.15s ease;white-space:nowrap;" onmouseover="this.style.background='#E5A93B';this.style.transform='translateY(-1px)'" onmouseout="this.style.background='#F5B82E';this.style.transform='translateY(0)'">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                        <span>Tulis Artikel Baru</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Script Live Search & Filter Kategori -->
        <script>
        (function(){
            var input = document.getElementById("bk-berita-search");
            var pills = document.querySelectorAll("#bk-berita-cat-pills .bk-berita-pill");
            var cards = document.querySelectorAll("#bk-berita-grid .bk-berita-card-item");
            var emptyBox = document.getElementById("bk-berita-empty");
            var countInfo = document.getElementById("bk-berita-count");
            var activeCat = "semua";

            function filterNews() {
                var query = (input ? input.value : "").toLowerCase().trim();
                var visible = 0;

                cards.forEach(function(card) {
                    var text = card.getAttribute("data-text") || "";
                    var cat  = card.getAttribute("data-cat") || "";

                    var matchQuery = !query || text.indexOf(query) !== -1;
                    var matchCat   = (activeCat === "semua") || (cat === activeCat);

                    if (matchQuery && matchCat) {
                        card.style.display = "flex";
                        visible++;
                    } else {
                        card.style.display = "none";
                    }
                });

                if (emptyBox) {
                    emptyBox.style.display = (visible === 0) ? "block" : "none";
                }

                if (countInfo) {
                    if (query || activeCat !== "semua") {
                        countInfo.innerHTML = "ARSIP // <span style='color:#0D3B22;font-weight:700;'>" + visible + "</span> DOKUMEN DITEMUKAN";
                    } else {
                        countInfo.innerHTML = "ARSIP // <span style='color:#0D3B22;font-weight:700;'>" + cards.length + "</span> DOKUMEN TERDATA";
                    }
                }
            }

            if (input) {
                input.addEventListener("input", filterNews);
            }

            pills.forEach(function(pill) {
                pill.addEventListener("click", function(){
                    pills.forEach(function(p) {
                        p.style.background = "#ffffff";
                        p.style.color = "#475569";
                        p.style.borderColor = "#E2E8F0";
                        p.style.fontWeight = "600";
                        p.classList.remove("active");
                    });
                    this.style.background = "#0D3B22";
                    this.style.color = "#F5B82E";
                    this.style.borderColor = "#0D3B22";
                    this.style.fontWeight = "700";
                    this.classList.add("active");

                    activeCat = this.getAttribute("data-cat") || "semua";
                    filterNews();
                });
            });
        })();
        </script>
    </section>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_berita_grid', 'ppb_render_berita_grid_swiss');

/* ========================================================================== */
/* 6. SHORTCODE BERITA HOMEPAGE: [ppb_home_news]                             */
/* Swiss Modernism 2.0 + Academic Editorial + shadcn/ui                       */
/* ========================================================================== */
if (!function_exists('ppb_shortcode_home_news')) {
function ppb_shortcode_home_news($atts = []) {
    $a = shortcode_atts([
        'count' => 6,
    ], $atts, 'ppb_home_news');
    $count = intval($a['count']) > 0 ? intval($a['count']) : 6;

    $query = new WP_Query([
        'post_type'      => 'post',
        'post_status'    => 'publish',
        'posts_per_page' => $count,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ]);

    $items = [];
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            $id = get_the_ID();
            $cats = get_the_category($id);
            $cat_name = !empty($cats) ? $cats[0]->name : 'Warta Akademik';
            $ringkasan = get_post_meta($id, '_ppb_berita_ringkasan', true);
            if (empty($ringkasan)) $ringkasan = wp_trim_words(get_the_excerpt(), 22, '...');
            $items[] = [
                'title'          => get_the_title(),
                'url'            => get_permalink(),
                'date_formatted' => get_the_date('j F Y'),
                'cat_name'       => $cat_name,
                'ringkasan'      => $ringkasan,
                'thumb'          => has_post_thumbnail($id) ? get_the_post_thumbnail_url($id, 'large') : '',
            ];
        }
        wp_reset_postdata();
    }

    // Supplement with sample items if DB has fewer than $count items to guarantee an active 6-item overview
    if (count($items) < $count && function_exists('ppb_get_sample_berita_items')) {
        $sample_items = ppb_get_sample_berita_items();
        foreach ($sample_items as $s) {
            if (count($items) >= $count) break;
            $items[] = [
                'title'          => $s['title'] ?? '',
                'url'            => $s['url'] ?? '#',
                'date_formatted' => $s['date_formatted'] ?? '',
                'cat_name'       => $s['cat_name'] ?? 'Warta Akademik',
                'ringkasan'      => $s['ringkasan'] ?? '',
                'thumb'          => $s['thumb'] ?? '',
            ];
        }
    }

    if (empty($items)) {
        return '<p style="font-family:IBM Plex Mono, monospace;font-size:12px;color:#64748B;">ARSIP // Belum ada warta akademik terbit.</p>';
    }

    ob_start();
    ?>
    <style>
    .bk-news-grid {
        display: grid !important;
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
        gap: 24px !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    @media (max-width: 1024px) {
        .bk-news-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            gap: 20px !important;
        }
    }
    @media (max-width: 640px) {
        .bk-news-grid {
            grid-template-columns: 1fr !important;
            gap: 16px !important;
        }
    }
    </style>
    <div class="bk-news-grid" style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;">
        <?php foreach ($items as $idx => $item) :
            $num = sprintf('%02d', $idx + 1);
        ?>
        <article class="bk-news-card" style="background:#ffffff;border:1px solid #E2E8F0;border-radius:8px;overflow:hidden;display:flex;flex-direction:column;justify-content:space-between;box-shadow:0 1px 3px rgba(0,0,0,0.03);transition:all 0.25s cubic-bezier(0.16, 1, 0.3, 1);" onmouseover="this.style.borderColor='#CBD5E1';this.style.boxShadow='0 10px 24px -6px rgba(13,59,34,0.09)';this.style.transform='translateY(-2px)'" onmouseout="this.style.borderColor='#E2E8F0';this.style.boxShadow='0 1px 3px rgba(0,0,0,0.03)';this.style.transform='translateY(0)'">
            <div>
                <!-- Top Ledger Metadata Bar -->
                <div style="padding:10px 18px;border-bottom:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;background:#FAF8F5;">
                    <span style="font-family:'IBM Plex Mono',monospace;font-size:10.5px;font-weight:600;color:#0D3B22;text-transform:uppercase;letter-spacing:0.08em;">
                        <?php echo esc_html($num . ' // ' . $item['cat_name']); ?>
                    </span>
                    <time style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#64748B;">
                        <?php echo esc_html($item['date_formatted']); ?>
                    </time>
                </div>

                <!-- Cover Image / Architectural Grid Placeholder -->
                <div style="height:185px;position:relative;overflow:hidden;border-bottom:1px solid #E2E8F0;background:#F8FAFC;">
                    <?php if (!empty($item['thumb'])) : ?>
                        <img src="<?php echo esc_url($item['thumb']); ?>" alt="<?php echo esc_attr($item['title']); ?>" style="width:100%;height:100%;object-fit:cover;display:block;transition:transform 0.4s ease;" onmouseover="this.style.transform='scale(1.04)'" onmouseout="this.style.transform='scale(1)'">
                    <?php else : ?>
                        <div style="width:100%;height:100%;display:flex;flex-direction:column;align-items:center;justify-content:center;background-color:#FAF8F5;background-image:linear-gradient(rgba(13,59,34,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(13,59,34,0.05) 1px, transparent 1px);background-size:20px 20px;position:relative;">
                            <div style="padding:8px 18px;border:1px solid rgba(13,59,34,0.18);background:rgba(255,255,255,0.94);border-radius:4px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,0.02);">
                                <div style="font-family:'Fraunces',Georgia,serif;font-size:17px;font-weight:600;color:#0D3B22;letter-spacing:0.04em;line-height:1.2;">PPB FIP UNM</div>
                                <div style="font-family:'IBM Plex Mono',monospace;font-size:9px;font-weight:600;color:#64748B;text-transform:uppercase;letter-spacing:0.12em;margin-top:2px;">Warta &amp; Dokumentasi</div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Body -->
                <div style="padding:20px 20px 14px;">
                    <h3 style="font-family:'Fraunces',Georgia,serif;font-size:18px;font-weight:600;color:#0F172A;line-height:1.35;margin:0 0 10px 0;">
                        <a href="<?php echo esc_url($item['url']); ?>" style="color:inherit;text-decoration:none;transition:color 0.15s ease;" onmouseover="this.style.color='#0D3B22'" onmouseout="this.style.color='#0F172A'">
                            <?php echo esc_html($item['title']); ?>
                        </a>
                    </h3>
                    <p style="font-family:'Plus Jakarta Sans',system-ui,sans-serif;font-size:13px;color:#475569;line-height:1.65;margin:0;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;">
                        <?php echo esc_html($item['ringkasan']); ?>
                    </p>
                </div>
            </div>

            <!-- Footer -->
            <div style="padding:12px 20px;border-top:1px solid #E2E8F0;display:flex;justify-content:space-between;align-items:center;background:#FAF8F5;">
                <a href="<?php echo esc_url($item['url']); ?>" style="font-family:'Plus Jakarta Sans',sans-serif;color:#0D3B22;font-size:12px;font-weight:700;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:color 0.15s ease;" onmouseover="this.style.color='#185333';this.querySelector('.arrow-ic').style.transform='translateX(3px)'" onmouseout="this.style.color='#0D3B22';this.querySelector('.arrow-ic').style.transform='translateX(0)'">
                    <span>Baca Artikel Lengkap</span>
                    <span class="arrow-ic" aria-hidden="true" style="color:#F5B82E;font-size:13px;font-weight:700;transition:transform 0.15s ease;">&rarr;</span>
                </a>
                <span style="font-family:'IBM Plex Mono',monospace;font-size:10px;font-weight:600;color:#94A3B8;letter-spacing:0.04em;">PPB FIP UNM</span>
            </div>
        </article>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}
}
add_shortcode('ppb_home_news', 'ppb_shortcode_home_news');


/**
 * Gunakan template khusus Scholaro Editorial untuk Single Post Berita
 */
add_filter('single_template', function($template) {
    if (is_singular('post')) {
        $custom = __DIR__ . '/templates/single-berita.php';
        if (file_exists($custom)) {
            return $custom;
        }
    }
    return $template;
});


if (!function_exists('ppb_shortcode_berita_grid')) {
    function ppb_shortcode_berita_grid() {
        return ppb_render_berita_grid_swiss();
    }
}


// Force Swiss Modernism 2.0 grid to override any legacy mu-plugins or old theme callbacks
add_action('init', function() {
    remove_shortcode('ppb_berita_grid');
    add_shortcode('ppb_berita_grid', 'ppb_render_berita_grid_swiss');
    if (function_exists('ppb_shortcode_home_news')) {
        remove_shortcode('ppb_home_news');
        add_shortcode('ppb_home_news', 'ppb_shortcode_home_news');
    }
}, 9999);
