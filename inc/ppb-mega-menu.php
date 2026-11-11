<?php
/**
 * PPB MEGA MENU MODULE
 * Modul Pengelolaan Mega Menu Dinamis & Terintegrasi WP-Admin
 * Program Studi Bimbingan dan Konseling FIP UNM
 *
 * @package BK_FIP_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/* ========================================================================== */
/* 1. HELPER IKON INLINE SVG (INSTANT RENDERING TANPA MENUNGGU CDN / JS)      */
/* ========================================================================== */
if (!function_exists('ppb_get_menu_icon_svg')) {
    /**
     * Mengembalikan SVG icon string berdasarkan identifier icon
     */
    function ppb_get_menu_icon_svg($icon_name, $size = 18, $class = '', $style = '') {
        $icon_name = strtolower(trim((string)$icon_name));
        $s = (int)$size;
        $cls = esc_attr($class);
        $sty = esc_attr($style);
        $common_attrs = "width=\"{$s}\" height=\"{$s}\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\" class=\"{$cls}\" style=\"{$sty}\"";

        switch ($icon_name) {
            case 'chevron-down':
                return "<svg {$common_attrs}><polyline points=\"6 9 12 15 18 9\"></polyline></svg>";
            case 'chevron-right':
                return "<svg {$common_attrs}><polyline points=\"9 18 15 12 9 6\"></polyline></svg>";
            case 'arrow-right':
                return "<svg {$common_attrs}><line x1=\"5\" y1=\"12\" x2=\"19\" y2=\"12\"></line><polyline points=\"12 5 19 12 12 19\"></polyline></svg>";
            case 'info':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><line x1=\"12\" y1=\"16\" x2=\"12\" y2=\"12\"></line><line x1=\"12\" y1=\"8\" x2=\"12.01\" y2=\"8\"></line></svg>";
            case 'history':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><polyline points=\"12 6 12 12 14 14\"></polyline><path d=\"M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8\"></path><path d=\"M3 3v5h5\"></path></svg>";
            case 'target':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><circle cx=\"12\" cy=\"12\" r=\"6\"></circle><circle cx=\"12\" cy=\"12\" r=\"2\"></circle></svg>";
            case 'compass':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><polygon points=\"16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76\"></polygon></svg>";
            case 'sitemap':
            case 'network':
                return "<svg {$common_attrs}><rect x=\"9\" y=\"3\" width=\"6\" height=\"5\" rx=\"1\"></rect><rect x=\"2\" y=\"16\" width=\"6\" height=\"5\" rx=\"1\"></rect><rect x=\"16\" y=\"16\" width=\"6\" height=\"5\" rx=\"1\"></rect><path d=\"M12 8v4\"></path><path d=\"M5 16v-2a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v2\"></path></svg>";
            case 'users':
                return "<svg {$common_attrs}><path d=\"M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"></path><circle cx=\"9\" cy=\"7\" r=\"4\"></circle><path d=\"M23 21v-2a4 4 0 0 0-3-3.87\"></path><path d=\"M16 3.13a4 4 0 0 1 0 7.75\"></path></svg>";
            case 'user-check':
                return "<svg {$common_attrs}><path d=\"M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"></path><circle cx=\"8.5\" cy=\"7\" r=\"4\"></circle><polyline points=\"17 11 19 13 23 9\"></polyline></svg>";
            case 'user-plus':
                return "<svg {$common_attrs}><path d=\"M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2\"></path><circle cx=\"8.5\" cy=\"7\" r=\"4\"></circle><line x1=\"20\" y1=\"8\" x2=\"20\" y2=\"14\"></line><line x1=\"23\" y1=\"11\" x2=\"17\" y2=\"11\"></line></svg>";
            case 'book':
            case 'book-open':
                return "<svg {$common_attrs}><path d=\"M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z\"></path><path d=\"M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z\"></path></svg>";
            case 'award':
            case 'trophy':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"8\" r=\"6\"></circle><path d=\"M15.477 12.89 17 22l-5-3-5 3 1.523-9.11\"></path></svg>";
            case 'medal':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"14\" r=\"6\"></circle><path d=\"M8.21 13.89 7 22l5-3 5 3-1.21-8.11\"></path><polyline points=\"7.5 4 12 10 16.5 4\"></polyline><line x1=\"12\" y1=\"2\" x2=\"12\" y2=\"10\"></line></svg>";
            case 'handshake':
            case 'heart-handshake':
                return "<svg {$common_attrs}><path d=\"m11 17 2 2a1 1 0 0 0 1.4 0l4.3-4.3a1 1 0 0 0 0-1.4l-2.6-2.6a1 1 0 0 0-1.4 0L13 12\"></path><path d=\"m13 7 1.4-1.4a1 1 0 0 1 1.4 0l2.6 2.6a1 1 0 0 1 0 1.4L17 11\"></path><path d=\"M2 12l5-5a2 2 0 0 1 2.8 0L12 9.2\"></path><path d=\"m7 17 2.2 2.2a2 2 0 0 0 2.8 0L14 17\"></path></svg>";
            case 'home':
            case 'building':
                return "<svg {$common_attrs}><rect x=\"4\" y=\"2\" width=\"16\" height=\"20\" rx=\"2\" ry=\"2\"></rect><path d=\"M9 22v-4h6v4\"></path><path d=\"M8 6h.01\"></path><path d=\"M16 6h.01\"></path><path d=\"M8 10h.01\"></path><path d=\"M16 10h.01\"></path><path d=\"M8 14h.01\"></path><path d=\"M16 14h.01\"></path></svg>";
            case 'map':
                return "<svg {$common_attrs}><polygon points=\"1 6 1 22 8 18 16 22 23 18 23 2 16 6 8 2 1 6\"></polygon><line x1=\"8\" y1=\"2\" x2=\"8\" y2=\"18\"></line><line x1=\"16\" y1=\"6\" x2=\"16\" y2=\"22\"></line></svg>";
            case 'file-text':
            case 'file':
                return "<svg {$common_attrs}><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"></path><polyline points=\"14 2 14 8 20 8\"></polyline><line x1=\"16\" y1=\"13\" x2=\"8\" y2=\"13\"></line><line x1=\"16\" y1=\"17\" x2=\"8\" y2=\"17\"></line><polyline points=\"10 9 9 9 8 9\"></polyline></svg>";
            case 'file-check':
                return "<svg {$common_attrs}><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"></path><polyline points=\"14 2 14 8 20 8\"></polyline><polyline points=\"9 15 11 17 15 13\"></polyline></svg>";
            case 'file-badge':
                return "<svg {$common_attrs}><path d=\"M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z\"></path><polyline points=\"14 2 14 8 20 8\"></polyline><circle cx=\"12\" cy=\"14\" r=\"3\"></circle></svg>";
            case 'graduation-cap':
                return "<svg {$common_attrs}><path d=\"M22 10v6M2 10l10-5 10 5-10 5z\"></path><path d=\"M6 12v5c3 3 9 3 12 0v-5\"></path></svg>";
            case 'layers':
                return "<svg {$common_attrs}><polygon points=\"12 2 2 7 12 12 22 7 12 2\"></polygon><polyline points=\"2 17 12 22 22 17\"></polyline><polyline points=\"2 12 12 17 22 12\"></polyline></svg>";
            case 'calendar':
                return "<svg {$common_attrs}><rect x=\"3\" y=\"4\" width=\"18\" height=\"18\" rx=\"2\" ry=\"2\"></rect><line x1=\"16\" y1=\"2\" x2=\"16\" y2=\"6\"></line><line x1=\"8\" y1=\"2\" x2=\"8\" y2=\"6\"></line><line x1=\"3\" y1=\"10\" x2=\"21\" y2=\"10\"></line></svg>";
            case 'bar-chart-2':
            case 'bar-chart':
                return "<svg {$common_attrs}><line x1=\"18\" y1=\"20\" x2=\"18\" y2=\"10\"></line><line x1=\"12\" y1=\"20\" x2=\"12\" y2=\"4\"></line><line x1=\"6\" y1=\"20\" x2=\"6\" y2=\"14\"></line></svg>";
            case 'pie-chart':
                return "<svg {$common_attrs}><path d=\"M21.21 15.89A10 10 0 1 1 8 2.83\"></path><path d=\"M22 12A10 10 0 0 0 12 2v10z\"></path></svg>";
            case 'globe':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><line x1=\"2\" y1=\"12\" x2=\"22\" y2=\"12\"></line><path d=\"M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\"></path></svg>";
            case 'spark':
            case 'sparkles':
                return "<svg {$common_attrs}><path d=\"m12 3-1.9 5.8a2 2 0 0 1-1.3 1.3L3 12l5.8 1.9a2 2 0 0 1 1.3 1.3L12 21l1.9-5.8a2 2 0 0 1 1.3-1.3L21 12l-5.8-1.9a2 2 0 0 1-1.3-1.3z\"></path></svg>";
            case 'share-2':
                return "<svg {$common_attrs}><circle cx=\"18\" cy=\"5\" r=\"3\"></circle><circle cx=\"6\" cy=\"12\" r=\"3\"></circle><circle cx=\"18\" cy=\"19\" r=\"3\"></circle><line x1=\"8.59\" y1=\"13.51\" x2=\"15.42\" y2=\"17.49\"></line><line x1=\"15.41\" y1=\"6.51\" x2=\"8.59\" y2=\"10.49\"></line></svg>";
            case 'flask-conical':
            case 'lab':
                return "<svg {$common_attrs}><path d=\"M10 2v7.527a2 2 0 0 1-.211.896L4.72 20.55a1 1 0 0 0 .9 1.45h12.76a1 1 0 0 0 .9-1.45l-5.069-10.127A2 2 0 0 1 14 9.527V2\"></path><path d=\"M8.5 2h7\"></path><path d=\"M7 16h10\"></path></svg>";
            case 'activity':
                return "<svg {$common_attrs}><polyline points=\"22 12 18 12 15 21 9 3 6 12 2 12\"></polyline></svg>";
            case 'check-square':
                return "<svg {$common_attrs}><polyline points=\"9 11 12 14 22 4\"></polyline><path d=\"M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11\"></path></svg>";
            case 'external-link':
                return "<svg {$common_attrs}><path d=\"M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6\"></path><polyline points=\"15 3 21 3 21 9\"></polyline><line x1=\"10\" y1=\"14\" x2=\"21\" y2=\"3\"></line></svg>";
            case 'heart':
                return "<svg {$common_attrs}><path d=\"M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z\"></path></svg>";
            case 'microchip':
            case 'database':
                return "<svg {$common_attrs}><ellipse cx=\"12\" cy=\"5\" rx=\"9\" ry=\"3\"></ellipse><path d=\"M21 12c0 1.66-4 3-9 3s-9-1.34-9-3\"></path><path d=\"M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5\"></path></svg>";
            case 'search':
                return "<svg {$common_attrs}><circle cx=\"11\" cy=\"11\" r=\"8\"></circle><line x1=\"21\" y1=\"21\" x2=\"16.65\" y2=\"16.65\"></line></svg>";
            case 'mail':
                return "<svg {$common_attrs}><path d=\"M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z\"></path><polyline points=\"22,6 12,13 2,6\"></polyline></svg>";
            case 'shield':
            case 'shield-check':
                return "<svg {$common_attrs}><path d=\"M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z\"></path><polyline points=\"9 12 11 14 15 10\"></polyline></svg>";
            case 'life-buoy':
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"10\"></circle><circle cx=\"12\" cy=\"12\" r=\"4\"></circle><line x1=\"4.93\" y1=\"4.93\" x2=\"9.17\" y2=\"9.17\"></line><line x1=\"14.83\" y1=\"14.83\" x2=\"19.07\" y2=\"19.07\"></line><line x1=\"14.83\" y1=\"9.17\" x2=\"19.07\" y2=\"4.93\"></line><line x1=\"14.83\" y1=\"9.17\" x2=\"19.07\" y2=\"4.93\"></line><line x1=\"4.93\" y1=\"19.07\" x2=\"9.17\" y2=\"14.83\"></line></svg>";
            case 'check-circle':
                return "<svg {$common_attrs}><path d=\"M22 11.08V12a10 10 0 1 1-5.93-9.14\"></path><polyline points=\"22 4 12 14.01 9 11.01\"></polyline></svg>";
            case 'newspaper':
                return "<svg {$common_attrs}><path d=\"M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2\"></path><path d=\"M18 14h-8\"></path><path d=\"M15 18h-5\"></path><path d=\"M10 6h8v4h-8V6Z\"></path></svg>";
            case 'download':
                return "<svg {$common_attrs}><path d=\"M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4\"></path><polyline points=\"7 10 12 15 17 10\"></polyline><line x1=\"12\" y1=\"15\" x2=\"12\" y2=\"3\"></line></svg>";
            case 'folder':
                return "<svg {$common_attrs}><path d=\"M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z\"></path></svg>";
            case 'grid':
                return "<svg {$common_attrs}><rect x=\"3\" y=\"3\" width=\"7\" height=\"7\"></rect><rect x=\"14\" y=\"3\" width=\"7\" height=\"7\"></rect><rect x=\"14\" y=\"14\" width=\"7\" height=\"7\"></rect><rect x=\"3\" y=\"14\" width=\"7\" height=\"7\"></rect></svg>";
            default:
                return "<svg {$common_attrs}><circle cx=\"12\" cy=\"12\" r=\"3\"></circle></svg>";
        }
    }
}

/* ========================================================================== */
/* 2. DATASET LENGKAP STRUKTUR DEFAULT MEGA MENU BK FIP UNM                   */
/* ========================================================================== */
if (!function_exists('ppb_get_default_mega_menu_data')) {
    /**
     * Mengembalikan data lengkap menu standar dengan hierarki mega menu
     */
    function ppb_get_default_mega_menu_data() {
        return [
            // 1. BERANDA (Tunggal)
            [
                'title'       => 'Beranda',
                'url'         => home_url('/'),
                'is_mega'     => false,
                'icon'        => 'home',
                'badge'       => '',
                'subtitle'    => 'Halaman Utama Portal',
                'sections'    => [],
            ],

            // 2. PROFIL (Mega Menu 3 Kolom + Spotlight)
            [
                'title'       => 'Profil',
                'url'         => home_url('/profil/'),
                'is_mega'     => true,
                'mega_cols'   => 3,
                'icon'        => 'building',
                'badge'       => 'Profil',
                'subtitle'    => 'Mengenal Jurusan PPB FIP UNM lebih dekat',
                'align'       => 'left',
                'spotlight'   => [
                    'badge'       => '⭐ LAMDIK UNGGUL',
                    'title'       => 'Jurusan PPB FIP UNM',
                    'description' => 'Program Studi Bimbingan dan Konseling berakreditasi Unggul dengan kurikulum adaptif masa depan.',
                    'url'         => home_url('/profil/'),
                    'action_text' => 'Buka Hub Profil &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Tentang Jurusan',
                        'icon'    => 'info',
                        'items'   => [
                            ['title' => 'Ringkasan Profil', 'url' => home_url('/profil/'), 'subtitle' => 'Visi kelembagaan & profil umum', 'icon' => 'info', 'badge' => ''],
                            ['title' => 'Sejarah Program Studi', 'url' => home_url('/profil/sejarah/'), 'subtitle' => 'Perjalanan sejarah & rekam jejak', 'icon' => 'history', 'badge' => ''],
                            ['title' => 'Visi, Misi & Sasaran', 'url' => home_url('/profil/visi-misi/'), 'subtitle' => 'Arah mutu pendidikan konseling', 'icon' => 'target', 'badge' => ''],
                            ['title' => 'Visi Keilmuan & Kepakaran', 'url' => home_url('/profil/visi-keilmuan/'), 'subtitle' => 'Peta pohon keilmuan BK adaptif', 'icon' => 'compass', 'badge' => ''],
                            ['title' => 'Struktur Organisasi', 'url' => home_url('/profil/struktur/'), 'subtitle' => 'Pimpinan & tata kelola jurusan', 'icon' => 'sitemap', 'badge' => ''],
                        ],
                    ],
                    [
                        'heading' => 'Dosen & Kepakaran',
                        'icon'    => 'users',
                        'items'   => [
                            ['title' => 'Dosen & Staf Pengajar', 'url' => home_url('/profil/dosen/'), 'subtitle' => '25 Dosen & Guru Besar BK UNM', 'icon' => 'users', 'badge' => '25 Dosen'],
                            ['title' => 'Koleksi Buku Dosen', 'url' => home_url('/profil/buku-dosen/'), 'subtitle' => 'Karya monograf & buku teks', 'icon' => 'book', 'badge' => ''],
                            ['title' => 'Peta Kepakaran & SINTA', 'url' => home_url('/profil/kepakaran-dosen/'), 'subtitle' => 'Profil riset SINTA, Scopus & HKI', 'icon' => 'award', 'badge' => 'SINTA'],
                            ['title' => 'Kerjasama Tridharma', 'url' => home_url('/profil/kerjasama-tridharma/'), 'subtitle' => 'Kemitraan nasional & internasional', 'icon' => 'handshake', 'badge' => 'Mitra'],
                        ],
                    ],
                    [
                        'heading' => 'Fasilitas & Regulasi',
                        'icon'    => 'home',
                        'items'   => [
                            ['title' => 'Sarana & Prasarana', 'url' => home_url('/profil/sarana-prasarana/'), 'subtitle' => 'Lab, ruang konseling & smart class', 'icon' => 'home', 'badge' => ''],
                            ['title' => 'Roadmap Riset & PKM', 'url' => home_url('/profil/roadmap/'), 'subtitle' => 'Peta jalan penelitian prodi', 'icon' => 'map', 'badge' => ''],
                            ['title' => 'Dokumen Mutu & Regulasi', 'url' => home_url('/profil/dokumen/'), 'subtitle' => 'Regulasi & standar penjaminan mutu', 'icon' => 'file-check', 'badge' => 'Mutu'],
                        ],
                    ],
                ],
            ],

            // 3. AKADEMIK (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Akademik',
                'url'         => home_url('/akademik/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'graduation-cap',
                'badge'       => 'OBE 2025',
                'subtitle'    => 'Kurikulum OBE, RPS, jadwal kuliah & evaluasi',
                'align'       => 'left',
                'spotlight'   => [
                    'badge'       => '🎓 KURIKULUM BARU',
                    'title'       => 'Kurikulum OBE 2025',
                    'description' => 'Mendukung Outcome-Based Education, konversi MBKM 20 SKS, dan asesmen digital terpadu.',
                    'url'         => home_url('/akademik/kurikulum/'),
                    'action_text' => 'Struktur Kurikulum &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Kurikulum & Silabus',
                        'icon'    => 'layers',
                        'items'   => [
                            ['title' => 'Ringkasan Akademik', 'url' => home_url('/akademik/'), 'subtitle' => 'Informasi perkuliahan & capaian', 'icon' => 'book-open', 'badge' => ''],
                            ['title' => 'Kurikulum OBE 2025', 'url' => home_url('/akademik/kurikulum/'), 'subtitle' => 'Struktur mata kuliah & sebaran SKS', 'icon' => 'layers', 'badge' => 'OBE 2025'],
                            ['title' => 'RPS Lengkap, SAP & Rubrik OBE', 'url' => home_url('/akademik/rps-sap/'), 'subtitle' => 'Rencana pembelajaran semester & rubrik', 'icon' => 'file-text', 'badge' => 'Silabus'],
                        ],
                    ],
                    [
                        'heading' => 'Pedoman & Penilaian',
                        'icon'    => 'calendar',
                        'items'   => [
                            ['title' => 'Pedoman Akademik & Penilaian', 'url' => home_url('/akademik/panduan/'), 'subtitle' => 'Panduan skripsi, KKN & ujian', 'icon' => 'file-check', 'badge' => ''],
                            ['title' => 'Jadwal Perkuliahan & Ujian', 'url' => home_url('/akademik/jadwal-ujian/'), 'subtitle' => 'Seminar proposal, hasil & tutup', 'icon' => 'calendar', 'badge' => 'Update'],
                            ['title' => 'Data & Statistik Akademik', 'url' => home_url('/akademik/statistik/'), 'subtitle' => 'Statistik kelulusan & IPK rata-rata', 'icon' => 'bar-chart-2', 'badge' => ''],
                            ['title' => 'Jurnal & Pengabdian Prodi BK', 'url' => home_url('/jurnal-bk/'), 'subtitle' => 'Publikasi riset dosen & mahasiswa', 'icon' => 'book', 'badge' => 'Jurnal'],
                        ],
                    ],
                ],
            ],

            // 4. KEMAHASISWAAN (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Kemahasiswaan',
                'url'         => home_url('/kemahasiswaan/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'award',
                'badge'       => 'Prestasi',
                'subtitle'    => 'Penerimaan maba, prestasi, karya & jejaring alumni',
                'align'       => 'left',
                'spotlight'   => [
                    'badge'       => '🏆 TALENTA UNM',
                    'title'       => 'Prestasi & Rekam Jejak',
                    'description' => 'Etalase kejuaraan nasional, keterlibatan proyek kemanusiaan MBKM, dan jejaring alumni berdaya saing tinggi.',
                    'url'         => home_url('/kemahasiswaan/prestasi/'),
                    'action_text' => 'Lihat Prestasi &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Penerimaan & Layanan',
                        'icon'    => 'user-plus',
                        'items'   => [
                            ['title' => 'Ringkasan Kemahasiswaan', 'url' => home_url('/kemahasiswaan/'), 'subtitle' => 'Dinamika & aktivitas mahasiswa', 'icon' => 'user-check', 'badge' => ''],
                            ['title' => 'Penerimaan Maba (Daya Tampung)', 'url' => home_url('/kemahasiswaan/penerimaan/'), 'subtitle' => 'Peminat, daya tampung & seleksi', 'icon' => 'user-plus', 'badge' => 'SNBP/SNBT'],
                            ['title' => 'Pameran Layanan MBKM & Kemanusiaan', 'url' => home_url('/kemahasiswaan/mbkm-kemanusiaan/'), 'subtitle' => 'Magang mandiri & relawan konseling', 'icon' => 'globe', 'badge' => 'MBKM'],
                        ],
                    ],
                    [
                        'heading' => 'Karya & Direktori Alumni',
                        'icon'    => 'trophy',
                        'items'   => [
                            ['title' => 'Prestasi & Riset Non-Akademik', 'url' => home_url('/kemahasiswaan/prestasi/'), 'subtitle' => 'Rekam medali & kompetisi nasional', 'icon' => 'trophy', 'badge' => 'Juara'],
                            ['title' => 'Karya & Inovasi Mahasiswa', 'url' => home_url('/kemahasiswaan/karya/'), 'subtitle' => 'Media bimbingan, video & modul konseling', 'icon' => 'spark', 'badge' => 'Karya'],
                            ['title' => 'Direktori Alumni & Tracer Study', 'url' => home_url('/kemahasiswaan/alumni/'), 'subtitle' => 'Jejaring alumni & sebaran profesi', 'icon' => 'share-2', 'badge' => 'Tracer'],
                            ['title' => 'Data & Statistik Mahasiswa', 'url' => home_url('/kemahasiswaan/statistik/'), 'subtitle' => 'Demografi & data kemahasiswaan aktif', 'icon' => 'pie-chart', 'badge' => ''],
                        ],
                    ],
                ],
            ],

            // 5. LABORATORIUM (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Laboratorium',
                'url'         => home_url('/laboratorium/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'flask-conical',
                'badge'       => 'Lab BK',
                'subtitle'    => 'Fasilitas alat tes, ruang simulasi & portal suite',
                'align'       => 'left',
                'spotlight'   => [
                    'badge'       => '🚀 PORTAL DIGITAL',
                    'title'       => 'BK Connect Suite',
                    'description' => 'Sistem asesmen psikologi digital, konseling online, dan manajemen instrumen BK FIP UNM.',
                    'url'         => 'https://bk-connect-fipunm.vercel.app/',
                    'action_text' => 'Masuk BK Connect &rarr;',
                    'target'      => '_blank',
                ],
                'sections'    => [
                    [
                        'heading' => 'Layanan & Praktikum Lab',
                        'icon'    => 'activity',
                        'items'   => [
                            ['title' => 'Ringkasan Laboratorium BK', 'url' => home_url('/laboratorium/'), 'subtitle' => 'Fasilitas & ruang konseling mikro', 'icon' => 'activity', 'badge' => ''],
                            ['title' => 'Kegiatan Mahasiswa Lab BK', 'url' => home_url('/laboratorium/kegiatan/'), 'subtitle' => 'Workshop, praktikum & simulasi tes', 'icon' => 'users', 'badge' => 'Praktikum'],
                            ['title' => 'Lomba Non-Akademik Lab', 'url' => home_url('/laboratorium/lomba/'), 'subtitle' => 'Ajang kompetisi kreativitas konseling', 'icon' => 'medal', 'badge' => 'Lomba'],
                        ],
                    ],
                    [
                        'heading' => 'Instrumen & Portal Suite',
                        'icon'    => 'check-square',
                        'items'   => [
                            ['title' => 'Inventaris Alat Tes Lab BK', 'url' => home_url('/lab-bk/inventaris/'), 'subtitle' => 'Daftar alat tes psikologi & inventori', 'icon' => 'check-square', 'badge' => 'Tes'],
                            ['title' => 'Portal BK Connect Suite', 'url' => 'https://bk-connect-fipunm.vercel.app/', 'subtitle' => 'Sistem asesmen & konseling terpadu', 'icon' => 'external-link', 'badge' => 'Suite ↗', 'target' => '_blank'],
                            ['title' => 'Portal Peersupport BK', 'url' => 'https://peersupport-bk.unm.ac.id/', 'subtitle' => 'Konselor sebaya bagi mahasiswa', 'icon' => 'heart', 'badge' => 'Portal ↗', 'target' => '_blank'],
                        ],
                    ],
                ],
            ],

            // 6. RISET & PKM (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Riset & PkM',
                'url'         => home_url('/mis-pnbp/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'microchip',
                'badge'       => 'MIS PNBP',
                'subtitle'    => 'Hibah penelitian, pengabdian masyarakat & jurnal',
                'align'       => 'center',
                'spotlight'   => [
                    'badge'       => '💡 HIBAH PNBP',
                    'title'       => 'Sistem MIS PNBP UNM',
                    'description' => 'Akselerasi riset terapan dan hilirisasi pengabdian masyarakat dosen bersama mahasiswa.',
                    'url'         => home_url('/mis-pnbp/'),
                    'action_text' => 'Buka Pusat MIS &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Pusat MIS PNBP',
                        'icon'    => 'database',
                        'items'   => [
                            ['title' => 'Pusat MIS PNBP', 'url' => home_url('/mis-pnbp/'), 'subtitle' => 'Dashboard integrasi riset & PkM', 'icon' => 'database', 'badge' => 'Hub'],
                            ['title' => 'Hibah Penelitian PNBP', 'url' => home_url('/mis-pnbp/#penelitian'), 'subtitle' => 'Proposal, laporan & luaran riset', 'icon' => 'search', 'badge' => 'Hibah'],
                            ['title' => 'Pengabdian kepada Masyarakat (PkM)', 'url' => home_url('/mis-pnbp/#pkm'), 'subtitle' => 'Pemberdayaan sekolah & mitra komunitas', 'icon' => 'heart-handshake', 'badge' => 'PkM'],
                        ],
                    ],
                    [
                        'heading' => 'Publikasi & Desiminasi',
                        'icon'    => 'book-open',
                        'items'   => [
                            ['title' => 'Publikasi Jurnal & Desiminasi', 'url' => home_url('/jurnal-bk/'), 'subtitle' => 'Artikel terindeks SINTA & Scopus', 'icon' => 'book-open', 'badge' => 'SINTA'],
                        ],
                    ],
                ],
            ],

            // 7. LAYANAN (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Layanan',
                'url'         => home_url('/layanan/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'life-buoy',
                'badge'       => 'Layanan',
                'subtitle'    => 'Persuratan, modul e-library & perlindungan mahasiswa',
                'align'       => 'right',
                'spotlight'   => [
                    'badge'       => '📚 MODUL DIGITAL',
                    'title'       => 'E-Library & Modul BK',
                    'description' => 'Akses cepat buku pegangan, instrumen konseling, dan modul ajar daring tanpa batas.',
                    'url'         => home_url('/e-library/'),
                    'action_text' => 'Kunjungi E-Library &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Layanan Administrasi',
                        'icon'    => 'mail',
                        'items'   => [
                            ['title' => 'Ringkasan Layanan', 'url' => home_url('/layanan/'), 'subtitle' => 'Pusat bantuan & layanan terpadu', 'icon' => 'life-buoy', 'badge' => ''],
                            ['title' => 'Layanan Persuratan & Kode Surat', 'url' => home_url('/layanan/persuratan/'), 'subtitle' => 'Nomor surat resmi, izin riset & pengantar', 'icon' => 'mail', 'badge' => 'Surat'],
                            ['title' => 'Panduan Prosedur & Template SOP', 'url' => home_url('/layanan/akademik/'), 'subtitle' => 'Template skripsi, berkas ujian & form', 'icon' => 'download', 'badge' => 'Unduh'],
                        ],
                    ],
                    [
                        'heading' => 'Sumber Belajar & Bantuan',
                        'icon'    => 'shield-check',
                        'items'   => [
                            ['title' => 'E-Library & Modul', 'url' => home_url('/e-library/'), 'subtitle' => 'Koleksi e-book & modul perkuliahan', 'icon' => 'folder', 'badge' => 'Digital'],
                            ['title' => 'Perlindungan Mahasiswa (No. 13)', 'url' => home_url('/layanan/perlindungan-mahasiswa/'), 'subtitle' => 'Satgas PPKS & kanal aman pengaduan', 'icon' => 'shield', 'badge' => 'Satgas'],
                            ['title' => 'Survei Kepuasan Sivitas', 'url' => home_url('/survei/'), 'subtitle' => 'Evaluasi kepuasan layanan & mutu prodi', 'icon' => 'check-circle', 'badge' => 'Survei'],
                        ],
                    ],
                ],
            ],

            // 8. AKREDITASI (Mega Menu 2 Kolom + Spotlight)
            [
                'title'       => 'Akreditasi',
                'url'         => home_url('/akreditasi/'),
                'is_mega'     => true,
                'mega_cols'   => 2,
                'icon'        => 'award',
                'badge'       => 'Unggul',
                'subtitle'    => 'Instrumen 9 Kriteria LAMDIK & Sertifikat S1/S2/S3',
                'align'       => 'right',
                'spotlight'   => [
                    'badge'       => '🥇 AKREDITASI UNGGUL',
                    'title'       => 'Peringkat LAMDIK Tertinggi',
                    'description' => 'Program Studi S1 Bimbingan dan Konseling FIP UNM meraih predikat Akreditasi Unggul nasional.',
                    'url'         => home_url('/akreditasi/sertifikat/'),
                    'action_text' => 'Lihat Sertifikat &rarr;',
                ],
                'sections'    => [
                    [
                        'heading' => 'Instrumen & Penjaminan Mutu',
                        'icon'    => 'grid',
                        'items'   => [
                            ['title' => 'Instrumen Hub 9 Kriteria LAMDIK', 'url' => home_url('/akreditasi/'), 'subtitle' => 'Kriteria 1 sampai 9 LAMDIK lengkap', 'icon' => 'grid', 'badge' => '9 Kriteria'],
                            ['title' => 'Renstra, Renop & Penjaminan Mutu', 'url' => home_url('/akreditasi/upm/'), 'subtitle' => 'Unit Penjaminan Mutu (UPM) FIP UNM', 'icon' => 'shield-check', 'badge' => 'UPM'],
                        ],
                    ],
                    [
                        'heading' => 'Sertifikat & SK Resmi',
                        'icon'    => 'file-badge',
                        'items'   => [
                            ['title' => 'Sertifikat Akreditasi S1, S2, S3', 'url' => home_url('/akreditasi/sertifikat/'), 'subtitle' => 'Unduh sertifikat & SK resmi BAN-PT/LAMDIK', 'icon' => 'file-badge', 'badge' => 'SK Resmi'],
                        ],
                    ],
                ],
            ],

            // 9. BERITA (Tunggal)
            [
                'title'       => 'Berita',
                'url'         => home_url('/berita/'),
                'is_mega'     => false,
                'icon'        => 'newspaper',
                'badge'       => '',
                'subtitle'    => 'Warta terkini & pengumuman prodi',
                'sections'    => [],
            ],
        ];
    }
}

/* ========================================================================== */
/* 3. PEMBUAT STRUKTUR TREE NAVIGASI DINAMIS DARI WP-ADMIN                    */
/* ========================================================================== */
if (!function_exists('ppb_get_nav_tree')) {
    /**
     * Membaca menu WordPress yang aktif atau jatuh tempo ke default mega menu
     */
    function ppb_get_nav_tree($current_path = '', $is_home = false) {
        $tree = [];
        $has_custom_menu = false;

        // 1. Cek apakah ada menu yang dipasang di lokasi 'primary-menu'
        if (has_nav_menu('primary-menu')) {
            $locations = get_nav_menu_locations();
            if (!empty($locations['primary-menu'])) {
                $menu_items = wp_get_nav_menu_items($locations['primary-menu']);
                if (!empty($menu_items) && is_array($menu_items)) {
                    $has_custom_menu = true;
                    $items_by_id = [];
                    $children_map = [];

                    // Simpan mapping dan sanitasi URL
                    foreach ($menu_items as $item) {
                        $raw_url = !empty($item->url) ? $item->url : '#';
                        $filtered_url = function_exists('ppb_filter_yamato_url') ? ppb_filter_yamato_url($raw_url) : $raw_url;
                        $is_active = ppb_nav_is_active($filtered_url, $current_path, $is_home);

                        // Ambil custom postmeta jika ada
                        $badge    = get_post_meta($item->ID, '_ppb_menu_badge', true) ?: (!empty($item->attr_title) ? $item->attr_title : '');
                        $subtitle = get_post_meta($item->ID, '_ppb_menu_subtitle', true) ?: (!empty($item->description) ? $item->description : '');
                        $icon     = get_post_meta($item->ID, '_ppb_menu_icon', true) ?: '';
                        $cols     = get_post_meta($item->ID, '_ppb_menu_mega_cols', true) ?: 3;
                        $is_mega_meta = get_post_meta($item->ID, '_ppb_menu_is_mega', true);

                        $node = [
                            'id'          => $item->ID,
                            'title'       => $item->title,
                            'url'         => $filtered_url,
                            'target'      => !empty($item->target) ? $item->target : '_self',
                            'badge'       => $badge,
                            'subtitle'    => $subtitle,
                            'icon'        => $icon,
                            'mega_cols'   => (int)$cols ?: 3,
                            'is_mega'     => ($is_mega_meta !== '') ? (bool)$is_mega_meta : null, // null = auto
                            'is_active'   => $is_active,
                            'classes'     => !empty($item->classes) ? (array)$item->classes : [],
                            'children'    => [],
                        ];

                        $items_by_id[$item->ID] = $node;
                        $parent_id = (int)$item->menu_item_parent;
                        $children_map[$parent_id][] = $item->ID;
                    }

                    // Susun hierarki: Level 0 (Top-level)
                    if (!empty($children_map[0])) {
                        foreach ($children_map[0] as $top_id) {
                            $top_item = $items_by_id[$top_id];
                            $level1_ids = !empty($children_map[$top_id]) ? $children_map[$top_id] : [];

                            if (empty($level1_ids)) {
                                // Item tunggal tanpa submenu
                                $top_item['is_mega'] = false;
                                $top_item['sections'] = [];
                                $tree[] = $top_item;
                                continue;
                            }

                            // Ada anak Level 1: Periksa apakah ada anak Level 2 (Kolom Terkelompok)
                            $has_level2 = false;
                            foreach ($level1_ids as $c_id) {
                                if (!empty($children_map[$c_id])) {
                                    $has_level2 = true;
                                    break;
                                }
                            }

                            $sections = [];
                            $parent_any_child_active = false;

                            if ($has_level2) {
                                // POLA 3-LEVEL: Level 1 = Heading Kolom, Level 2 = Link Item
                                foreach ($level1_ids as $col_id) {
                                    $col_item = $items_by_id[$col_id];
                                    $col_sub_ids = !empty($children_map[$col_id]) ? $children_map[$col_id] : [];
                                    $items_in_col = [];

                                    foreach ($col_sub_ids as $sub_id) {
                                        $sub_item = $items_by_id[$sub_id];
                                        if ($sub_item['is_active']) {
                                            $parent_any_child_active = true;
                                        }
                                        $items_in_col[] = $sub_item;
                                    }

                                    // Jika kolom itu sendiri punya link aktif
                                    if ($col_item['is_active']) {
                                        $parent_any_child_active = true;
                                    }

                                    $sections[] = [
                                        'heading' => $col_item['title'],
                                        'icon'    => $col_item['icon'] ?: 'layers',
                                        'url'     => ($col_item['url'] !== '#' && strpos($col_item['url'], 'http') === 0) ? $col_item['url'] : '',
                                        'items'   => $items_in_col,
                                    ];
                                }
                            } else {
                                // POLA 2-LEVEL: Semua Level 1 adalah Link langsung.
                                // Kelompokkan otomatis ke dalam 2 atau 3 kolom seimbang agar menjadi Mega Menu rapi!
                                $all_items = [];
                                foreach ($level1_ids as $l1_id) {
                                    $l1_item = $items_by_id[$l1_id];
                                    if ($l1_item['is_active']) {
                                        $parent_any_child_active = true;
                                    }
                                    $all_items[] = $l1_item;
                                }

                                $total = count($all_items);
                                if ($total <= 4) {
                                    $sections[] = [
                                        'heading' => 'Menu & Tautan',
                                        'icon'    => 'grid',
                                        'items'   => $all_items,
                                    ];
                                } elseif ($total <= 8) {
                                    $chunks = array_chunk($all_items, ceil($total / 2));
                                    $sections[] = ['heading' => 'Bagian 1', 'icon' => 'grid', 'items' => $chunks[0] ?? []];
                                    $sections[] = ['heading' => 'Bagian 2', 'icon' => 'grid', 'items' => $chunks[1] ?? []];
                                } else {
                                    $chunks = array_chunk($all_items, ceil($total / 3));
                                    $sections[] = ['heading' => 'Bagian 1', 'icon' => 'grid', 'items' => $chunks[0] ?? []];
                                    $sections[] = ['heading' => 'Bagian 2', 'icon' => 'grid', 'items' => $chunks[1] ?? []];
                                    $sections[] = ['heading' => 'Bagian 3', 'icon' => 'grid', 'items' => $chunks[2] ?? []];
                                }
                            }

                            if (!$is_home && $parent_any_child_active) {
                                $top_item['is_active'] = true;
                            }

                            $top_item['is_mega'] = true;
                            $top_item['mega_cols'] = max(1, min(4, count($sections)));
                            $top_item['sections'] = $sections;
                            $tree[] = $top_item;
                        }
                    }
                }
            }
        }

        // 2. Jika tidak ada menu dinamis WP atau kosong, gunakan DEFAULT MEGA MENU
        if (!$has_custom_menu || empty($tree)) {
            $default_data = ppb_get_default_mega_menu_data();
            $tree = [];

            foreach ($default_data as $top) {
                $top_active = ppb_nav_is_active($top['url'], $current_path, $is_home);
                $any_child_active = false;

                if (!empty($top['sections'])) {
                    foreach ($top['sections'] as &$sec) {
                        foreach ($sec['items'] as &$it) {
                            $it['is_active'] = ppb_nav_is_active($it['url'], $current_path, $is_home);
                            if ($it['is_active']) {
                                $any_child_active = true;
                            }
                        }
                        unset($it);
                    }
                    unset($sec);
                }

                if (!$is_home && $any_child_active) {
                    $top_active = true;
                }

                $top['is_active'] = $top_active;
                $tree[] = $top;
            }
        }

        return $tree;
    }
}

/* ========================================================================== */
/* 4. CUSTOM FIELDS DI EDITOR MENU WORDPRESS (Appearance > Menus)             */
/* ========================================================================== */
add_action('wp_nav_menu_item_custom_fields', 'ppb_add_mega_menu_nav_fields', 10, 4);
if (!function_exists('ppb_add_mega_menu_nav_fields')) {
    function ppb_add_mega_menu_nav_fields($item_id, $item, $depth, $args) {
        $badge    = get_post_meta($item_id, '_ppb_menu_badge', true);
        $subtitle = get_post_meta($item_id, '_ppb_menu_subtitle', true);
        $icon     = get_post_meta($item_id, '_ppb_menu_icon', true);
        $cols     = get_post_meta($item_id, '_ppb_menu_mega_cols', true) ?: '3';
        ?>
        <div class="ppb-mega-field-wrap" style="background:#f8fafc;border:1px dashed #cbd5e1;border-radius:8px;padding:12px;margin:10px 0 12px;box-sizing:border-box;">
            <div style="display:flex;align-items:center;gap:6px;font-size:11px;font-weight:700;color:#0D3B22;margin-bottom:8px;text-transform:uppercase;letter-spacing:0.04em;">
                <span>✨ Opsi Mega Menu PPB FIP</span>
            </div>

            <!-- Teks Badge -->
            <p class="description description-wide" style="margin:4px 0 8px;">
                <label for="edit-menu-item-badge-<?php echo esc_attr($item_id); ?>" style="font-size:12px;font-weight:600;color:#334155;">
                    Teks Badge / Label Highlight (Opsional):<br />
                    <input type="text" id="edit-menu-item-badge-<?php echo esc_attr($item_id); ?>" 
                           class="widefat code edit-menu-item-badge" 
                           name="ppb_menu_badge[<?php echo esc_attr($item_id); ?>]" 
                           value="<?php echo esc_attr($badge); ?>" 
                           placeholder="Contoh: OBE 2025, Unggul, Baru, 25 Dosen" 
                           style="margin-top:2px;border-radius:6px;" />
                </label>
            </p>

            <!-- Deskripsi Subtitle -->
            <p class="description description-wide" style="margin:4px 0 8px;">
                <label for="edit-menu-item-subtitle-<?php echo esc_attr($item_id); ?>" style="font-size:12px;font-weight:600;color:#334155;">
                    Deskripsi Ringkas / Subtitle di Bawah Judul Link:<br />
                    <input type="text" id="edit-menu-item-subtitle-<?php echo esc_attr($item_id); ?>" 
                           class="widefat edit-menu-item-subtitle" 
                           name="ppb_menu_subtitle[<?php echo esc_attr($item_id); ?>]" 
                           value="<?php echo esc_attr($subtitle); ?>" 
                           placeholder="Contoh: Struktur mata kuliah & SKS" 
                           style="margin-top:2px;border-radius:6px;" />
                </label>
            </p>

            <!-- Pilihan Ikon -->
            <p class="description description-wide" style="margin:4px 0 8px;">
                <label for="edit-menu-item-icon-<?php echo esc_attr($item_id); ?>" style="font-size:12px;font-weight:600;color:#334155;">
                    Nama Ikon (Opsional):<br />
                    <select id="edit-menu-item-icon-<?php echo esc_attr($item_id); ?>" 
                            name="ppb_menu_icon[<?php echo esc_attr($item_id); ?>]" 
                            class="widefat" 
                            style="margin-top:2px;border-radius:6px;">
                        <option value="" <?php selected($icon, ''); ?>>-- Tanpa Ikon Khusus (Otomatis) --</option>
                        <option value="building" <?php selected($icon, 'building'); ?>>🏛️ Profil / Gedung (building)</option>
                        <option value="users" <?php selected($icon, 'users'); ?>>👥 Dosen / Pengajar (users)</option>
                        <option value="book" <?php selected($icon, 'book'); ?>>📖 Buku / Karya Dosen (book)</option>
                        <option value="graduation-cap" <?php selected($icon, 'graduation-cap'); ?>>🎓 Akademik & Kurikulum (graduation-cap)</option>
                        <option value="file-text" <?php selected($icon, 'file-text'); ?>>📄 RPS & Dokumen Silabus (file-text)</option>
                        <option value="award" <?php selected($icon, 'award'); ?>>🏆 Akreditasi & Prestasi (award)</option>
                        <option value="trophy" <?php selected($icon, 'trophy'); ?>>🥇 Prestasi Mahasiswa (trophy)</option>
                        <option value="flask-conical" <?php selected($icon, 'flask-conical'); ?>>🧪 Laboratorium & Alat Tes (flask-conical)</option>
                        <option value="microchip" <?php selected($icon, 'microchip'); ?>>🔬 Riset & MIS PNBP (microchip)</option>
                        <option value="database" <?php selected($icon, 'database'); ?>>🗄️ Database & Sistem (database)</option>
                        <option value="life-buoy" <?php selected($icon, 'life-buoy'); ?>>🛟 Layanan & Bantuan (life-buoy)</option>
                        <option value="shield" <?php selected($icon, 'shield'); ?>>🛡️ Perlindungan Mahasiswa / Satgas (shield)</option>
                        <option value="newspaper" <?php selected($icon, 'newspaper'); ?>>📰 Berita & Warta (newspaper)</option>
                        <option value="external-link" <?php selected($icon, 'external-link'); ?>>↗️ Tautan Portal Luar (external-link)</option>
                    </select>
                </label>
            </p>

            <?php if ($depth === 0) : ?>
            <!-- Jumlah Kolom (Hanya untuk Menu Induk Level 0) -->
            <p class="description description-wide" style="margin:4px 0 4px;">
                <label for="edit-menu-item-mega-cols-<?php echo esc_attr($item_id); ?>" style="font-size:12px;font-weight:600;color:#334155;">
                    Tata Letak Kolom Mega Menu (Item Induk):<br />
                    <select id="edit-menu-item-mega-cols-<?php echo esc_attr($item_id); ?>" 
                            name="ppb_menu_mega_cols[<?php echo esc_attr($item_id); ?>]" 
                            class="widefat" 
                            style="margin-top:2px;border-radius:6px;">
                        <option value="3" <?php selected($cols, '3'); ?>>3 Kolom (Disarankan untuk Profil / Menu Banyak)</option>
                        <option value="2" <?php selected($cols, '2'); ?>>2 Kolom (Sedang - Akademik / Kemahasiswaan / Layanan)</option>
                        <option value="4" <?php selected($cols, '4'); ?>>4 Kolom (Lebar Penuh)</option>
                        <option value="1" <?php selected($cols, '1'); ?>>1 Kolom (Dropdown Tradisional Vertikal)</option>
                    </select>
                </label>
            </p>
            <?php endif; ?>
        </div>
        <?php
    }
}

// Simpan Custom Meta Nav Menu
add_action('wp_update_nav_menu_item', 'ppb_save_mega_menu_nav_fields', 10, 3);
if (!function_exists('ppb_save_mega_menu_nav_fields')) {
    function ppb_save_mega_menu_nav_fields($menu_id, $menu_item_db_id, $args) {
        if (!current_user_can('edit_theme_options')) {
            return;
        }

        if (isset($_POST['ppb_menu_badge'][$menu_item_db_id])) {
            $badge = sanitize_text_field(wp_unslash($_POST['ppb_menu_badge'][$menu_item_db_id]));
            update_post_meta($menu_item_db_id, '_ppb_menu_badge', $badge);
        }

        if (isset($_POST['ppb_menu_subtitle'][$menu_item_db_id])) {
            $subtitle = sanitize_text_field(wp_unslash($_POST['ppb_menu_subtitle'][$menu_item_db_id]));
            update_post_meta($menu_item_db_id, '_ppb_menu_subtitle', $subtitle);
        }

        if (isset($_POST['ppb_menu_icon'][$menu_item_db_id])) {
            $icon = sanitize_text_field(wp_unslash($_POST['ppb_menu_icon'][$menu_item_db_id]));
            update_post_meta($menu_item_db_id, '_ppb_menu_icon', $icon);
        }

        if (isset($_POST['ppb_menu_mega_cols'][$menu_item_db_id])) {
            $cols = (int)$_POST['ppb_menu_mega_cols'][$menu_item_db_id];
            update_post_meta($menu_item_db_id, '_ppb_menu_mega_cols', $cols);
        }
    }
}

/* ========================================================================== */
/* 5. PEMBUAT / SINKRONISASI 1-KLIK MENU UTAMA STANDAR KE DATABASE WORDPRESS   */
/* ========================================================================== */
if (!function_exists('ppb_sync_default_mega_menu_to_wp')) {
    /**
     * Membuat struktur menu lengkap di tabel WordPress dan memasangnya ke 'primary-menu'
     */
    function ppb_sync_default_mega_menu_to_wp() {
        if (!current_user_can('manage_options')) {
            return false;
        }

        $menu_name = 'Menu Utama BK FIP (Mega Menu)';
        $menu_obj = wp_get_nav_menu_object($menu_name);

        if (!$menu_obj) {
            $menu_id = wp_create_nav_menu($menu_name);
            if (is_wp_error($menu_id)) {
                return false;
            }
        } else {
            $menu_id = $menu_obj->term_id;
            // Hapus item lama dalam menu ini agar sinkron bersih
            $old_items = wp_get_nav_menu_items($menu_id);
            if (!empty($old_items)) {
                foreach ($old_items as $oi) {
                    wp_delete_post($oi->ID, true);
                }
            }
        }

        $default_data = ppb_get_default_mega_menu_data();
        $order = 1;

        foreach ($default_data as $top) {
            // Buat Item Level 0 (Top-Level)
            $top_id = wp_update_nav_menu_item($menu_id, 0, [
                'menu-item-title'   => $top['title'],
                'menu-item-url'     => $top['url'],
                'menu-item-status'  => 'publish',
                'menu-item-position'=> $order++,
            ]);

            if ($top_id && !is_wp_error($top_id)) {
                if (!empty($top['badge'])) update_post_meta($top_id, '_ppb_menu_badge', $top['badge']);
                if (!empty($top['subtitle'])) update_post_meta($top_id, '_ppb_menu_subtitle', $top['subtitle']);
                if (!empty($top['icon'])) update_post_meta($top_id, '_ppb_menu_icon', $top['icon']);
                if (!empty($top['mega_cols'])) update_post_meta($top_id, '_ppb_menu_mega_cols', $top['mega_cols']);

                // Jika memiliki sections (Kolom)
                if (!empty($top['sections'])) {
                    foreach ($top['sections'] as $sec) {
                        // Level 1: Kolom Heading
                        $col_id = wp_update_nav_menu_item($menu_id, 0, [
                            'menu-item-title'    => $sec['heading'],
                            'menu-item-url'      => '#',
                            'menu-item-parent-id'=> $top_id,
                            'menu-item-status'   => 'publish',
                            'menu-item-position' => $order++,
                        ]);

                        if ($col_id && !is_wp_error($col_id)) {
                            if (!empty($sec['icon'])) update_post_meta($col_id, '_ppb_menu_icon', $sec['icon']);

                            // Level 2: Link di dalam Kolom
                            if (!empty($sec['items'])) {
                                foreach ($sec['items'] as $child) {
                                    $child_id = wp_update_nav_menu_item($menu_id, 0, [
                                        'menu-item-title'    => $child['title'],
                                        'menu-item-url'      => $child['url'],
                                        'menu-item-target'   => !empty($child['target']) ? $child['target'] : '_self',
                                        'menu-item-parent-id'=> $col_id,
                                        'menu-item-status'   => 'publish',
                                        'menu-item-position' => $order++,
                                    ]);

                                    if ($child_id && !is_wp_error($child_id)) {
                                        if (!empty($child['badge'])) update_post_meta($child_id, '_ppb_menu_badge', $child['badge']);
                                        if (!empty($child['subtitle'])) update_post_meta($child_id, '_ppb_menu_subtitle', $child['subtitle']);
                                        if (!empty($child['icon'])) update_post_meta($child_id, '_ppb_menu_icon', $child['icon']);
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        // Tetapkan menu ini ke lokasi tema 'primary-menu'
        $locations = get_theme_mod('nav_menu_locations') ?: [];
        $locations['primary-menu'] = $menu_id;
        set_theme_mod('nav_menu_locations', $locations);

        return $menu_id;
    }
}

// Handler Aksi Sinkronisasi Admin POST
add_action('admin_post_ppb_sync_mega_menu', function() {
    check_admin_referer('ppb_sync_mega_menu_nonce');
    if (!current_user_can('manage_options')) {
        wp_die('Akses ditolak.');
    }

    $menu_id = ppb_sync_default_mega_menu_to_wp();
    if ($menu_id) {
        wp_safe_redirect(admin_url('nav-menus.php?action=edit&menu=' . $menu_id . '&ppb_synced=1'));
    } else {
        wp_safe_redirect(admin_url('nav-menus.php?ppb_sync_failed=1'));
    }
    exit;
});

/* ========================================================================== */
/* 6. HALAMAN ADMIN PENGELOLAAN MEGA MENU (PORTAL PPB FIP & TAMPILAN)         */
/* ========================================================================== */
add_action('admin_menu', function() {
    // 1. Tambah Submenu di Portal PPB FIP jika ada
    add_submenu_page(
        'ppb-portal-hub',
        'Kelola Navigasi & Mega Menu Navbar',
        '🧭 Kelola Mega Menu',
        'manage_options',
        'ppb-mega-menu-manager',
        'ppb_render_mega_menu_admin_page'
    );

    // 2. Tambah di Menu Tampilan (Appearance > Mega Menu)
    add_theme_page(
        'Mega Menu Navbar',
        'Mega Menu Navbar',
        'manage_options',
        'ppb-mega-menu-manager',
        'ppb_render_mega_menu_admin_page'
    );
}, 25);

if (!function_exists('ppb_render_mega_menu_admin_page')) {
    function ppb_render_mega_menu_admin_page() {
        if (!current_user_can('manage_options')) {
            return;
        }

        $locations = get_nav_menu_locations();
        $assigned_id = !empty($locations['primary-menu']) ? $locations['primary-menu'] : 0;
        $assigned_menu = $assigned_id ? wp_get_nav_menu_object($assigned_id) : null;
        $total_items = $assigned_id ? count((array)wp_get_nav_menu_items($assigned_id)) : 0;
        ?>
        <div class="wrap" style="max-width:1160px;margin:20px auto;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,sans-serif;">
            
            <?php if (isset($_GET['ppb_synced'])) : ?>
                <div class="notice notice-success is-dismissible" style="border-left-color:#0D3B22;padding:12px 16px;">
                    <p style="font-size:14px;font-weight:700;color:#0D3B22;margin:0;">
                        ✅ Berhasil! Struktur 45+ Tautan Mega Menu Standar BK FIP telah dibuat dan dihubungkan ke lokasi 'Menu Utama (Navbar)'.
                    </p>
                    <p style="font-size:13px;color:#334155;margin:4px 0 0;">
                        Anda dapat langsung mengatur urutan, mengubah judul label, atau menambahkan tautan baru di bawah ini.
                    </p>
                </div>
            <?php endif; ?>

            <!-- Header Card -->
            <div style="background:linear-gradient(135deg, #0D3B22 0%, #185333 100%);color:#ffffff;padding:30px 32px;border-radius:18px;margin-bottom:24px;box-shadow:0 10px 25px rgba(13,59,34,0.18);position:relative;overflow:hidden;">
                <div style="display:inline-flex;align-items:center;gap:8px;background:rgba(245,184,46,0.18);border:1px solid rgba(245,184,46,0.4);color:#F5B82E;padding:4px 14px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;margin-bottom:12px;">
                    <span>🧭 Navigasi Modern PPB FIP UNM</span>
                </div>
                <h1 style="font-size:26px;font-weight:800;color:#ffffff;margin:0 0 8px;line-height:1.2;">
                    Pusat Pengelolaan Mega Menu Navbar
                </h1>
                <p style="font-size:14px;color:rgba(255,255,255,0.85);margin:0;max-width:760px;line-height:1.6;">
                    Navbar website telah mendukung tata letak <strong>Mega Menu Multi-Kolom</strong> dengan ikon, subtitle, badge sorotan (seperti <em>OBE 2025</em>, <em>LAMDIK Unggul</em>, <em>25 Dosen</em>), dan kartu spotlight aksi cepat. Semua tautan dapat dikelola secara fleksibel dari WP-Admin!
                </p>
            </div>

            <!-- Status & Action Grid -->
            <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:20px;margin-bottom:28px;">
                
                <!-- Status Lokasi Menu -->
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;margin-bottom:8px;">
                        Status Lokasi Menu Navbar
                    </div>
                    <?php if ($assigned_menu) : ?>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#10b981;"></span>
                            <strong style="font-size:17px;color:#0D3B22;"><?php echo esc_html($assigned_menu->name); ?></strong>
                        </div>
                        <p style="font-size:13px;color:#64748b;margin:0 0 16px;line-height:1.5;">
                            Menu sedang <strong>Aktif</strong> dengan total <strong><?php echo esc_html($total_items); ?> item tautan</strong>. Perubahan yang Anda simpan di Menu Editor akan langsung tayang seketika.
                        </p>
                        <a href="<?php echo admin_url('nav-menus.php?action=edit&menu=' . (int)$assigned_id); ?>" class="button button-primary button-hero" style="background:#0D3B22;border-color:#0D3B22;box-shadow:none;text-shadow:none;font-weight:700;border-radius:8px;">
                            ⚙️ Buka Editor Menu (Drag & Drop)
                        </a>
                    <?php else : ?>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                            <span style="display:inline-block;width:12px;height:12px;border-radius:50%;background:#f59e0b;"></span>
                            <strong style="font-size:17px;color:#92400e;">Mode Fallback Standar Aktif</strong>
                        </div>
                        <p style="font-size:13px;color:#64748b;margin:0 0 16px;line-height:1.5;">
                            Belum ada menu kustom yang ditautkan ke lokasi <em>Menu Utama (Navbar)</em>. Website saat ini otomatis menggunakan 45+ tautan mega menu standar bawaan tema.
                        </p>
                    <?php endif; ?>
                </div>

                <!-- 1-Click Sync Card -->
                <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:22px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                    <div style="font-size:12px;font-weight:700;text-transform:uppercase;letter-spacing:0.04em;color:#64748b;margin-bottom:8px;">
                        Sinkronisasi Cepat (1-Klik)
                    </div>
                    <strong style="display:block;font-size:17px;color:#0f172a;margin-bottom:10px;">
                        Buat / Reset Struktur Menu Mega Menu
                    </strong>
                    <p style="font-size:13px;color:#64748b;margin:0 0 16px;line-height:1.5;">
                        Ingin mengedit menu tapi belum ada di WP-Admin? Klik tombol di bawah untuk membuat seluruh 9 menu induk, kolom-kolom submenu, ikon, dan badge secara otomatis ke database WordPress!
                    </p>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="return confirm('Apakah Anda yakin ingin menyinkronkan seluruh menu standar ke Menu Utama WordPress? Item menu pada Menu Utama BK FIP akan disesuaikan.');">
                        <?php wp_nonce_field('ppb_sync_mega_menu_nonce'); ?>
                        <input type="hidden" name="action" value="ppb_sync_mega_menu" />
                        <button type="submit" class="button button-secondary button-hero" style="background:#F5B82E;border-color:#e5a925;color:#0D3B22;font-weight:800;border-radius:8px;">
                            ⚡ Buat / Sinkronkan Menu Standar ke WP-Admin
                        </button>
                    </form>
                </div>

            </div>

            <!-- Petunjuk Pengaturan Mega Menu -->
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:26px;box-shadow:0 2px 8px rgba(0,0,0,0.03);margin-bottom:28px;">
                <h3 style="font-size:18px;font-weight:800;color:#0D3B22;margin:0 0 16px;display:flex;align-items:center;gap:8px;">
                    💡 Cara Mengatur Struktur Mega Menu di WP-Admin
                </h3>
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:16px;">
                    <div style="background:#FAF8F5;border:1px solid #E8F0EA;border-radius:10px;padding:16px;">
                        <span style="font-size:12px;font-weight:800;color:#0D3B22;background:#E8F0EA;padding:2px 8px;border-radius:4px;">Langkah 1: Menu Induk</span>
                        <h4 style="margin:10px 0 6px;font-size:14px;color:#0f172a;">Item Baris Teratas (Level 0)</h4>
                        <p style="margin:0;font-size:12.5px;color:#475569;line-height:1.5;">
                            Item yang berada di posisi paling kiri (tanpa indentasi) akan menjadi tombol utama di navbar atas (seperti <em>Profil</em>, <em>Akademik</em>, <em>Layanan</em>).
                        </p>
                    </div>

                    <div style="background:#FAF8F5;border:1px solid #E8F0EA;border-radius:10px;padding:16px;">
                        <span style="font-size:12px;font-weight:800;color:#0D3B22;background:#E8F0EA;padding:2px 8px;border-radius:4px;">Langkah 2: Kolom Submenu</span>
                        <h4 style="margin:10px 0 6px;font-size:14px;color:#0f172a;">Kolom Heading (Level 1)</h4>
                        <p style="margin:0;font-size:12.5px;color:#475569;line-height:1.5;">
                            Geser item satu tingkat ke kanan di bawah menu induk. Jika item ini memiliki anak lagi di bawahnya, ia otomatis menjadi <strong>Judul Kolom</strong> dalam Mega Menu.
                        </p>
                    </div>

                    <div style="background:#FAF8F5;border:1px solid #E8F0EA;border-radius:10px;padding:16px;">
                        <span style="font-size:12px;font-weight:800;color:#0D3B22;background:#E8F0EA;padding:2px 8px;border-radius:4px;">Langkah 3: Tautan Link</span>
                        <h4 style="margin:10px 0 6px;font-size:14px;color:#0f172a;">Link Item (Level 2)</h4>
                        <p style="margin:0;font-size:12.5px;color:#475569;line-height:1.5;">
                            Geser item dua tingkat ke kanan. Item ini akan menjadi tautan link yang tersusun rapi di dalam kolom lengkap dengan subtitle dan badge sorotan.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Pratinjau Daftar Menu Standar -->
            <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:14px;padding:26px;box-shadow:0 2px 8px rgba(0,0,0,0.03);">
                <h3 style="font-size:18px;font-weight:800;color:#0D3B22;margin:0 0 16px;">
                    📋 Pratinjau Struktur Menu Standar Jurusan PPB FIP
                </h3>
                <table class="widefat striped" style="border:1px solid #e2e8f0;border-radius:8px;overflow:hidden;">
                    <thead>
                        <tr style="background:#FAF8F5;">
                            <th style="font-weight:700;color:#0D3B22;width:200px;">Menu Utama</th>
                            <th style="font-weight:700;color:#0D3B22;width:120px;">Tipe</th>
                            <th style="font-weight:700;color:#0D3B22;">Kolom &amp; Tautan Submenu</th>
                            <th style="font-weight:700;color:#0D3B22;width:120px;">Badge Khusus</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $preview_data = ppb_get_default_mega_menu_data();
                        foreach ($preview_data as $m) : 
                        ?>
                            <tr>
                                <td>
                                    <strong style="color:#0D3B22;font-size:14px;"><?php echo esc_html($m['title']); ?></strong>
                                    <?php if (!empty($m['subtitle'])) : ?>
                                        <div style="font-size:11.5px;color:#64748b;"><?php echo esc_html($m['subtitle']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['is_mega'])) : ?>
                                        <span style="display:inline-block;background:#E8F0EA;color:#0D3B22;font-weight:700;font-size:11px;padding:2px 8px;border-radius:4px;">Mega Menu (<?php echo esc_html($m['mega_cols'] ?? 2); ?> Kolom)</span>
                                    <?php else : ?>
                                        <span style="display:inline-block;background:#f1f5f9;color:#475569;font-weight:600;font-size:11px;padding:2px 8px;border-radius:4px;">Tautan Langsung</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['sections'])) : ?>
                                        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:8px;">
                                            <?php foreach ($m['sections'] as $sec) : ?>
                                                <div style="background:#FAF8F5;border:1px solid #E8F0EA;border-radius:6px;padding:8px;">
                                                    <div style="font-size:11px;font-weight:700;color:#185333;text-transform:uppercase;margin-bottom:4px;">
                                                        📁 <?php echo esc_html($sec['heading']); ?>
                                                    </div>
                                                    <ul style="margin:0;padding-left:14px;font-size:12px;color:#334155;">
                                                        <?php foreach ($sec['items'] as $item) : ?>
                                                            <li>
                                                                <?php echo esc_html($item['title']); ?>
                                                                <?php if (!empty($item['badge'])) : ?>
                                                                    <span style="background:rgba(245,184,46,0.2);color:#92400e;font-size:9.5px;padding:1px 4px;border-radius:3px;font-weight:700;"><?php echo esc_html($item['badge']); ?></span>
                                                                <?php endif; ?>
                                                            </li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else : ?>
                                        <span style="font-size:12px;color:#64748b;">(Tautan langsung ke <?php echo esc_html($m['url']); ?>)</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($m['badge'])) : ?>
                                        <span style="background:#F5B82E;color:#0D3B22;font-size:11px;font-weight:800;padding:2px 8px;border-radius:12px;">
                                            <?php echo esc_html($m['badge']); ?>
                                        </span>
                                    <?php else : ?>
                                        <span style="color:#94a3b8;">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </div>
        <?php
    }
}

/* ========================================================================== */
/* 7. ADMIN NOTICE DI HALAMAN TAMPILAN > MENU (Appearance > Menus)            */
/* ========================================================================== */
add_action('admin_notices', function() {
    $screen = get_current_screen();
    if ($screen && $screen->id === 'nav-menus') {
        $locations = get_nav_menu_locations();
        $assigned_id = !empty($locations['primary-menu']) ? $locations['primary-menu'] : 0;
        ?>
        <div class="notice notice-info" style="border-left-color:#0D3B22;padding:12px 16px;">
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
                <div>
                    <strong style="color:#0D3B22;font-size:13.5px;">🧭 Dukungan Mega Menu PPB FIP UNM Aktif</strong>
                    <p style="margin:3px 0 0;font-size:12.5px;color:#475569;">
                        Susun item Level 1 sebagai Judul Kolom dan Level 2 sebagai Link di dalamnya. Anda juga dapat menambahkan <strong>Teks Badge</strong> dan <strong>Subtitle</strong> di setiap kartu menu di bawah.
                        <?php if (!$assigned_id) : ?>
                            <br /><span style="color:#d97706;font-weight:600;">⚠️ Lokasi 'Menu Utama (Navbar)' belum dipilih. Silakan buat atau sinkronkan menu standar.</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;" onsubmit="return confirm('Buat atau perbarui Menu Utama BK FIP dengan 45+ tautan standar sekarang?');">
                        <?php wp_nonce_field('ppb_sync_mega_menu_nonce'); ?>
                        <input type="hidden" name="action" value="ppb_sync_mega_menu" />
                        <button type="submit" class="button" style="background:#0D3B22;color:#ffffff;border-color:#0D3B22;font-weight:700;">
                            ⚡ Sinkronkan Menu Standar Mega Menu
                        </button>
                    </form>
                    <a href="<?php echo admin_url('admin.php?page=ppb-mega-menu-manager'); ?>" class="button" style="margin-left:6px;">
                        Bantuan &amp; Panduan
                    </a>
                </div>
            </div>
        </div>
        <?php
    }
});

/* ========================================================================== */
/* 8. AUTO-SEED MEGA MENU ON FIRST ADMIN VISIT (IF NO MENU ASSIGNED YET)      */
/* ========================================================================== */
add_action('admin_init', function() {
    if (!current_user_can('manage_options')) {
        return;
    }
    if (get_option('ppb_mega_menu_autoseeded_v1') !== 'yes') {
        $locations = get_nav_menu_locations();
        if (empty($locations['primary-menu'])) {
            ppb_sync_default_mega_menu_to_wp();
        }
        update_option('ppb_mega_menu_autoseeded_v1', 'yes');
    }
});
