<?php
/**
 * Footer Situs — Editorial Calm v3.0 (Forest Green & Gold)
 * Seluruh teks alamat, akreditasi, hak cipta, dan tautan SIPADU dapat diubah dari WP-Admin > Pengaturan Beranda.
 */
if (!defined('ABSPATH')) exit;

$bk_cfg = function_exists('bk_fip_get_homepage_config') ? bk_fip_get_homepage_config() : array();
$footer_address    = $bk_cfg['footer_address']    ?? get_option('bk_footer_address', 'Gedung Jurusan PPB, Fakultas Ilmu Pendidikan UNM, Kampus Tidung Jl. Tamalate I, Makassar, Sulawesi Selatan');
$footer_akreditasi = $bk_cfg['footer_akreditasi'] ?? get_option('bk_footer_akreditasi', 'S1 & S2 Terakreditasi Unggul LAMDIK · S3 Baik Sekali');
$footer_copyright  = $bk_cfg['footer_copyright']  ?? get_option('bk_footer_copyright', '© ' . gmdate('Y') . ' Jurusan Psikologi Pendidikan dan Bimbingan · FIP Universitas Negeri Makassar');
$sipadu_url        = $bk_cfg['sipadu_url']        ?? get_option('bk_sipadu_url', 'https://bk-connect-fipunm.vercel.app/sipadu');
$portal_auth_url   = $bk_cfg['portal_auth_url']   ?? get_option('bk_portal_auth_url', 'https://bk-connect-fipunm.vercel.app/auth');
?>

<footer class="bk-footer" role="contentinfo">
    <div class="bk-wrap">
        <div class="bk-footer-grid">
            <!-- Kolom 1: Identitas & Alamat -->
            <div>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                    <div class="bk-footer-logo-box">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/img/logo-white.png'); ?>" alt="Logo UNM" style="width:100%;height:100%;object-fit:contain;">
                    </div>
                    <div>
                        <div style="font-family:var(--font-display, 'Fraunces', serif);font-size:17px;font-weight:600;color:#FFFFFF;line-height:1.2;">
                            Bimbingan dan Konseling
                        </div>
                        <div style="font-size:11px;font-weight:600;color:#F5B82E;letter-spacing:0.05em;text-transform:uppercase;margin-top:2px;">
                            Jurusan PPB · FIP Universitas Negeri Makassar
                        </div>
                    </div>
                </div>
                <p class="bk-footer-desc">
                    Menyelenggarakan pendidikan konselor profesional berlandaskan nilai diversitas, sains-teknologi, kewirausahaan, dan kearifan lokal Nusantara sejak 1969.
                </p>
                <p class="bk-footer-addr"><?php echo esc_html($footer_address); ?></p>
                <div style="margin-top:12px;display:inline-flex;align-items:center;gap:8px;padding:5px 12px;border-radius:9999px;background:rgba(245,184,46,0.14);border:1px solid rgba(245,184,46,0.34);font-family:var(--font-mono, monospace);font-size:11px;color:#F5B82E;font-weight:600;">
                    <span>★</span>
                    <span><?php echo esc_html($footer_akreditasi); ?></span>
                </div>
            </div>

            <!-- Kolom 2: Profil, Mutu & Akademik -->
            <div>
                <div class="bk-footer-kicker">Profil &amp; Akademik</div>
                <ul class="bk-footer-links">
                    <li><a href="<?php echo esc_url(home_url('/profil/visi-keilmuan/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Visi Keilmuan &amp; CARA'DE</a></li>
                    <li><a href="<?php echo esc_url(home_url('/profil/sejarah/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Sejarah 1969 &amp; Pimpinan</a></li>
                    <li><a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Direktori 25 Dosen</a></li>
                    <li><a href="<?php echo esc_url(home_url('/akreditasi/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Akreditasi Unggul LAMDIK</a></li>
                    <li><a href="<?php echo esc_url(home_url('/akademik/kurikulum/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Kurikulum OBE 2025 (144 SKS)</a></li>
                    <li><a href="<?php echo esc_url(home_url('/akademik/jadwal-ujian/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Jadwal Seminar &amp; Ujian</a></li>
                </ul>
            </div>

            <!-- Kolom 3: Layanan, E-Library & Portal -->
            <div>
                <div class="bk-footer-kicker">Layanan &amp; Portal</div>
                <ul class="bk-footer-links">
                    <li><a href="<?php echo esc_url(home_url('/layanan/persuratan/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Persuratan F-01..F-06 &amp; SOP</a></li>
                    <li><a href="<?php echo esc_url(home_url('/laboratorium/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Laboratorium BK &amp; Alat Tes</a></li>
                    <li><a href="<?php echo esc_url(home_url('/e-library/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> E-Library &amp; Repositori Modul</a></li>
                    <li><a href="<?php echo esc_url($sipadu_url); ?>" target="_blank" rel="noopener"><span class="bk-footer-chev">&rsaquo;</span> Portal SIPADU Daring ↗</a></li>
                    <li><a href="<?php echo esc_url($portal_auth_url); ?>" target="_blank" rel="noopener"><span class="bk-footer-chev">&rsaquo;</span> Masuk Portal BK-Connect ↗</a></li>
                    <li><a href="<?php echo esc_url(home_url('/berita/')); ?>"><span class="bk-footer-chev">&rsaquo;</span> Berita &amp; Pengumuman Resmi</a></li>
                </ul>
            </div>
        </div>

        <div class="bk-footer-bottom">
            <div><?php echo esc_html($footer_copyright); ?></div>
            <div>Fakultas Ilmu Pendidikan · Universitas Negeri Makassar</div>
        </div>
    </div>
</footer>

<!-- Floating Back to Top Button -->
<button
    type="button"
    id="bk-back-to-top"
    class="bk-back-to-top"
    aria-label="Kembali ke atas"
    title="Kembali ke atas"
    onclick="window.scrollTo({top: 0, behavior: 'smooth'});"
>
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 15l-6-6-6 6"/>
    </svg>
</button>

<style>
.bk-back-to-top {
    position: fixed !important;
    bottom: 28px !important;
    right: 28px !important;
    width: 44px !important;
    height: 44px !important;
    border-radius: 50% !important;
    background: #0D3B22 !important;
    color: #F5B82E !important;
    border: 1.5px solid rgba(245, 184, 46, 0.45) !important;
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.28) !important;
    cursor: pointer !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    z-index: 99980 !important;
    opacity: 0 !important;
    pointer-events: none !important;
    transform: translateY(16px) scale(0.9) !important;
    transition: opacity 0.25s ease, transform 0.25s ease, background-color 0.2s ease !important;
}
.bk-back-to-top.is-visible {
    opacity: 1 !important;
    pointer-events: auto !important;
    transform: translateY(0) scale(1) !important;
}
.bk-back-to-top:hover {
    background: #F5B82E !important;
    color: #0D3B22 !important;
}
</style>

<script>
(function() {
    var btn = document.getElementById('bk-back-to-top');
    if (!btn) return;
    function toggleBackToTop() {
        var y = window.pageYOffset || document.documentElement.scrollTop || 0;
        btn.classList.toggle('is-visible', y > 300);
    }
    window.addEventListener('scroll', toggleBackToTop, { passive: true });
    toggleBackToTop();
})();
</script>

<?php wp_footer(); ?>
</body>
</html>
