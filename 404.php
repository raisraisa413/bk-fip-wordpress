<?php
/**
 * Template 404 - Halaman Tidak Ditemukan / Dalam Penyesuaian
 * Scholaro 1:1 Full-Width Editorial Design
 * Program Studi Bimbingan dan Konseling FIP UNM
 *
 * @package BK_FIP_Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

status_header(404);

get_header();

$current_req_url = isset($_SERVER['REQUEST_URI']) ? esc_html($_SERVER['REQUEST_URI']) : '';
?>

<div class="bk-page-wrapper" style="width:100%;min-height:100vh;background-color:#FAF8F5;font-family:'Poppins',sans-serif;color:#111827;">

    <!-- =================================================================== -->
    <!-- 1. HERO HEADER                                                      -->
    <!-- =================================================================== -->
    <section style="position:relative;overflow:hidden;background:#0D3B22;border-bottom:1px solid rgba(255,255,255,0.1);color:#ffffff;padding:var(--bk-top-clearance, 125px) clamp(16px, 4vw, 40px) 50px;">
        <!-- Ambient Glow Orbs -->
        <div style="position:absolute;top:-100px;right:-10%;width:500px;height:500px;border-radius:50%;background:rgba(24,83,51,0.35);filter:blur(80px);pointer-events:none;"></div>
        <div style="position:absolute;bottom:-120px;left:-10%;width:420px;height:420px;border-radius:50%;background:rgba(245,184,46,0.12);filter:blur(80px);pointer-events:none;"></div>

        <div class="bk-container" style="position:relative;width:100%;max-width:1100px;margin:0 auto;text-align:center;">
            
            <div style="display:inline-flex;align-items:center;gap:8px;padding:6px 16px;border-radius:9999px;background:rgba(245,184,46,0.15);border:1px solid rgba(245,184,46,0.35);color:#F5B82E;font-size:12px;font-weight:700;letter-spacing:0.08em;text-transform:uppercase;margin-bottom:18px;">
                <span>⚠️ 404 &bull; Tautan Sedang Diperbarui</span>
            </div>

            <h1 style="font-family:'Fraunces',Georgia,serif;font-size:clamp(30px, 4.5vw, 48px);font-weight:600;color:#ffffff;line-height:1.2;margin:0 0 16px 0;">
                Halaman Sedang Diperbarui / Belum Ditemukan
            </h1>

            <p style="font-size:clamp(14px, 1.8vw, 16.5px);color:rgba(255,255,255,0.85);margin:0 auto 24px;max-width:760px;line-height:1.7;">
                Tautan yang Anda tuju (<code><?php echo $current_req_url; ?></code>) mungkin telah mengalami perubahan struktur URL, sedang dalam sinkronisasi konten oleh pengelola portal, atau alamat yang diketikkan belum tepat.
            </p>

            <!-- Search Bar Inline -->
            <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" style="max-width:560px;margin:0 auto;display:flex;gap:10px;background:rgba(255,255,255,0.08);backdrop-filter:blur(10px);padding:6px;border-radius:14px;border:1px solid rgba(255,255,255,0.2);">
                <input type="search" name="s" placeholder="Cari layanan, kurikulum, dosen, atau dokumen..." value="<?php echo get_search_query(); ?>" style="flex:1;background:transparent;border:none;padding:12px 18px;color:#ffffff;font-size:14px;font-family:'Poppins',sans-serif;outline:none;" required>
                <button type="submit" style="background:#F5B82E;color:#0D3B22;font-family:'Poppins',sans-serif;font-weight:700;font-size:13.5px;border:none;padding:12px 24px;border-radius:10px;cursor:pointer;transition:all 0.2s ease;">
                    Cari Portal
                </button>
            </form>

        </div>
    </section>

    <!-- =================================================================== -->
    <!-- 2. QUICK NAVIGATION HUB CARDS                                       -->
    <!-- =================================================================== -->
    <main style="width:100%;max-width:1200px;margin:40px auto 70px;padding:0 20px;box-sizing:border-box;">
        
        <div style="text-align:center;margin-bottom:30px;">
            <h2 style="font-family:'Fraunces',Georgia,serif;font-size:24px;color:#0D3B22;margin:0 0 8px;font-weight:600;">
                Pilih Destinasi Cepat Portal Jurusan
            </h2>
            <p style="font-size:14px;color:#6B7280;margin:0;">
                Gunakan tautan cepat di bawah ini untuk mengakses informasi penting yang Anda perlukan:
            </p>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill, minmax(260px, 1fr));gap:20px;">
            
            <!-- Card 1: Beranda -->
            <a href="<?php echo esc_url(home_url('/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Beranda Utama</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Halaman utama portal resmi Program Studi Bimbingan dan Konseling FIP UNM.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 2: Profil Jurusan -->
            <a href="<?php echo esc_url(home_url('/profil/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect><path d="M9 22v-4h6v4"></path></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Profil Program Studi</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Visi misi, sejarah perjalanan prodi, struktur organisasi, dan penjaminan mutu.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 3: Dosen & Staf -->
            <a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Dosen &amp; Kepakaran</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Direktori 25 dosen &amp; guru besar, profil SINTA, Scopus, dan karya monograf.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 4: Kurikulum OBE 2025 -->
            <a href="<?php echo esc_url(home_url('/akademik/kurikulum/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"></path></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Kurikulum OBE 2025</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Struktur sebaran 146 SKS mata kuliah, RPS lengkap, dan rubrik asesmen OBE.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 5: E-Library & Modul -->
            <a href="<?php echo esc_url(home_url('/e-library/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">E-Library &amp; Modul</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Perpustakaan digital prodi: modul praktikum konseling, buku ajar, dan panduan PPL.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 6: MIS PNBP -->
            <a href="<?php echo esc_url(home_url('/mis-pnbp/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path><path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">MIS PNBP Riset &amp; PkM</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Pusat manajemen hibah penelitian dan aksi pengabdian masyarakat dosen berbasis PNBP.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 7: Berita & Informasi -->
            <a href="<?php echo esc_url(home_url('/berita/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 22h16a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2H8a2 2 0 0 0-2 2v16a2 2 0 0 1-2 2Zm0 0a2 2 0 0 1-2-2v-9c0-1.1.9-2 2-2h2"></path><path d="M18 14h-8"></path><path d="M15 18h-5"></path><path d="M10 6h8v4h-8V6Z"></path></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Warta &amp; Berita Resmi</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Liputan kegiatan akademik, seminar nasional, pengumuman, dan artikel opini.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

            <!-- Card 8: Layanan & SOP -->
            <a href="<?php echo esc_url(home_url('/layanan/')); ?>" style="text-decoration:none;display:flex;flex-direction:column;background:#ffffff;border:1.5px solid #D4E3D8;border-radius:18px;padding:22px;transition:all 0.25s ease;box-shadow:0 4px 15px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#0D3B22';this.style.transform='translateY(-3px)';this.style.boxShadow='0 10px 25px rgba(13,59,34,0.08)';" onmouseout="this.style.borderColor='#D4E3D8';this.style.transform='translateY(0)';this.style.boxShadow='0 4px 15px rgba(0,0,0,0.02)';">
                <div style="width:42px;height:42px;border-radius:12px;background:#E8F0EA;color:#0D3B22;display:flex;align-items:center;justify-content:center;margin-bottom:14px;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line></svg>
                </div>
                <h3 style="font-family:'Fraunces',Georgia,serif;font-size:17px;color:#0D3B22;margin:0 0 6px;font-weight:600;">Layanan Terpadu &amp; SOP</h3>
                <p style="font-size:12.5px;color:#6B7280;line-height:1.5;margin:0 0 12px;flex:1;">Layanan persuratan, template permohonan riset, dan satgas perlindungan mahasiswa.</p>
                <span style="font-size:12px;font-weight:700;color:#0D3B22;display:inline-flex;align-items:center;gap:4px;">Kunjungi &rarr;</span>
            </a>

        </div>

    </main>

</div>

<?php get_footer(); ?>
