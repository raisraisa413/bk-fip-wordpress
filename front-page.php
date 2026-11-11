<?php
/**
 * The template for displaying the Front Page (Landing Page)
 * Redesigned with Swiss Modernism 2.0 + Academic Editorial + shadcn/ui Architecture
 * Zero "AI Slop" — Distinct compositional patterns per section (Ledger, Matrix, Hairline Index, Data Table, Bento Split)
 */
get_header();

// Load real Dosen data from dosen-data.json (fallback to curated list if missing)
$dosen_json_path = get_template_directory() . '/dosen-data.json';
$dosen_all = array();
if (file_exists($dosen_json_path)) {
    $decoded = json_decode(file_get_contents($dosen_json_path), true);
    if (is_array($decoded)) {
        $dosen_all = $decoded;
    }
}

// Helper for initials
if (!function_exists('bk_home_initials')) {
    function bk_home_initials($name) {
        $parts = explode(',', $name);
        $clean = $parts[0];
        $prefixes = array('prof', 'dr', 'dra', 'drs', 'ir', 'h', 'hj');
        $words = preg_split('/[\s\.]+/', $clean);
        $filtered = array();
        foreach ($words as $w) {
            $w = trim($w);
            if ($w && !in_array(strtolower($w), $prefixes)) {
                $filtered[] = $w;
            }
        }
        if (count($filtered) >= 2) {
            return strtoupper(substr($filtered[0], 0, 1) . substr($filtered[1], 0, 1));
        } elseif (count($filtered) === 1) {
            return strtoupper(substr($filtered[0], 0, 2));
        }
        return 'BK';
    }
}

// Sort featured faculty: Dekan -> Kajur -> Sekjur -> Guru Besar -> Doktor
if (!empty($dosen_all)) {
    usort($dosen_all, function($a, $b) {
        $rank = function($d) {
            $s = $d['slug'] ?? '';
            $n = strtolower($d['nama_lengkap'] ?? '');
            $j = strtolower($d['jabatan_fungsional'] ?? '');
            if ($s === 'abdul-saman') return 1;
            if ($s === 'suciani-latif') return 2;
            if ($s === 'sahril-buchori') return 3;
            if (strpos($n, 'prof.') !== false || strpos($j, 'guru besar') !== false) return 10;
            if (strpos($n, 'dr.') !== false || strpos($j, 'doktor') !== false) return 20;
            return 30;
        };
        $ra = $rank($a);
        $rb = $rank($b);
        if ($ra !== $rb) return $ra - $rb;
        $pa = (int)($a['stat']['publikasi'] ?? count($a['publikasi'] ?? array()));
        $pb = (int)($b['stat']['publikasi'] ?? count($b['publikasi'] ?? array()));
        return $pb - $pa;
    });
}

$total_dosen_count = !empty($dosen_all) ? count($dosen_all) : 25;
$featured_dosen = !empty($dosen_all) ? array_slice($dosen_all, 0, 10) : array();
?>

<style>
/* ==========================================================================
   SWISS MODERNISM 2.0 + SHADCN ACADEMIC EDITORIAL SYSTEM (HOMEPAGE)
   ========================================================================== */
:root {
    --bk-bg-warm: #FAF8F5;
    --bk-bg-surface: #FFFFFF;
    --bk-bg-muted: #F2EFE9;
    --bk-bg-sage: #E8F0EA;
    --bk-ink: #0F172A;
    --bk-ink-muted: #475569;
    --bk-ink-subtle: #64748B;
    --bk-forest: #0D3B22;
    --bk-forest-mid: #164E30;
    --bk-forest-deep: #072314;
    --bk-gold: #F5B82E;
    --bk-gold-muted: #B45309;
    --bk-border: #D4E3D8;
    --bk-border-strong: #94A3B8;
    --bk-border-subtle: #E2E8F0;
    --bk-radius-sm: 6px;
    --bk-radius-md: 10px;
    --bk-radius-lg: 14px;
}

.bk-mono {
    font-family: 'IBM Plex Mono', ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
    font-variant-numeric: tabular-nums;
}

.bk-serif {
    font-family: 'Fraunces', Georgia, serif !important;
}

.bk-sans {
    font-family: 'Poppins', -apple-system, BlinkMacSystemFont, sans-serif !important;
}

/* Section Header Architecture */
.bk-sec-kicker {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--bk-forest);
    margin-bottom: 10px;
}
.bk-sec-kicker::before {
    content: "";
    display: inline-block;
    width: 14px;
    height: 2px;
    background-color: var(--bk-gold-muted);
}

/* Buttons: shadcn-inspired crisp architectural proportions (not cartoon 30px pills) */
.bk-btn-primary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: var(--bk-gold);
    color: var(--bk-forest-deep);
    font-family: 'Poppins', sans-serif;
    font-size: 13.5px;
    font-weight: 600;
    padding: 12px 22px;
    border-radius: var(--bk-radius-md);
    border: 1px solid var(--bk-gold);
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
}
.bk-btn-primary:hover {
    background-color: #fbcb46;
    transform: translateY(-1px);
    box-shadow: 0 8px 20px -4px rgba(245, 184, 46, 0.4);
}

.bk-btn-outline-light {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: rgba(255, 255, 255, 0.04);
    color: #FFFFFF;
    font-family: 'Poppins', sans-serif;
    font-size: 13.5px;
    font-weight: 500;
    padding: 12px 22px;
    border-radius: var(--bk-radius-md);
    border: 1px solid rgba(255, 255, 255, 0.24);
    text-decoration: none;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.bk-btn-outline-light:hover {
    background-color: rgba(255, 255, 255, 0.1);
    border-color: rgba(255, 255, 255, 0.45);
}

.bk-btn-outline-dark {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    background-color: #FFFFFF;
    color: var(--bk-forest);
    font-family: 'Poppins', sans-serif;
    font-size: 13px;
    font-weight: 600;
    padding: 10px 18px;
    border-radius: var(--bk-radius-md);
    border: 1px solid var(--bk-border);
    text-decoration: none;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.bk-btn-outline-dark:hover {
    background-color: var(--bk-bg-sage);
    border-color: var(--bk-forest);
}

/* 1. HERO SECTION ARCHITECTURAL FRAME */
.bk-hero-grid-layout {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 36px;
    align-items: stretch;
}
.bk-hero-col-left {
    grid-column: span 7 / span 7;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.bk-hero-col-right {
    grid-column: span 5 / span 5;
    display: flex;
    flex-direction: column;
}

.bk-hero-dossier {
    background: rgba(7, 35, 20, 0.65);
    border: 1px solid rgba(255, 255, 255, 0.16);
    border-radius: var(--bk-radius-lg);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 24px 48px -12px rgba(0, 0, 0, 0.35);
}
.bk-hero-photo-frame {
    position: relative;
    height: 250px;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    background: #061d11;
}
.bk-hero-photo-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    filter: contrast(1.04) saturate(0.95);
    transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}
.bk-hero-dossier:hover .bk-hero-photo-frame img {
    transform: scale(1.03);
}
.bk-hero-ledger-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    background: rgba(0, 0, 0, 0.2);
}
.bk-hero-ledger-cell {
    padding: 14px 16px;
    border-right: 1px solid rgba(255, 255, 255, 0.1);
}
.bk-hero-ledger-cell:last-child {
    border-right: none;
}

/* 2. SWISS HAIRLINE METRICS BAR */
.bk-ledger-strip {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-radius-lg);
    background: #FFFFFF;
    overflow: hidden;
}
.bk-ledger-item {
    padding: 24px 26px;
    border-right: 1px solid var(--bk-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: background-color 0.2s ease;
}
.bk-ledger-item:last-child {
    border-right: none;
}
.bk-ledger-item:hover {
    background-color: #FAF8F5;
}

/* 3. PRODI UNIFIED ARCHITECTURAL MATRIX + SHADCN TABS */
.bk-tabs-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-bottom: 24px;
}
.bk-tabs-list {
    display: inline-flex;
    align-items: center;
    background: #EAE6DF;
    padding: 4px;
    border-radius: var(--bk-radius-md);
    border: 1px solid var(--bk-border);
    gap: 4px;
}
.bk-tab-trigger {
    appearance: none;
    border: none;
    background: transparent;
    color: var(--bk-ink-muted);
    font-family: 'Poppins', sans-serif;
    font-size: 12.5px;
    font-weight: 600;
    padding: 8px 16px;
    border-radius: 7px;
    cursor: pointer;
    transition: all 0.18s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.bk-tab-trigger:hover {
    color: var(--bk-ink);
}
.bk-tab-trigger.is-active {
    background: #FFFFFF;
    color: var(--bk-forest);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.1);
}

.bk-prodi-matrix {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    background: #FFFFFF;
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-radius-lg);
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(13, 59, 34, 0.03);
}
.bk-prodi-col {
    border-right: 1px solid var(--bk-border);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: background-color 0.2s ease;
}
.bk-prodi-col:last-child {
    border-right: none;
}
.bk-prodi-col:hover {
    background-color: #FCFBF9;
}
.bk-spec-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 9px 0;
    border-bottom: 1px dashed #E2E8F0;
    font-size: 12px;
}
.bk-spec-row:last-child {
    border-bottom: none;
}

/* 4. ABOUT & PILLARS: SWISS HORIZONTAL HAIRLINE INDEX */
.bk-about-split {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 40px;
    align-items: start;
}
.bk-about-left {
    grid-column: span 5 / span 5;
}
.bk-about-right {
    grid-column: span 7 / span 7;
}
.bk-pillar-row {
    display: grid;
    grid-template-columns: 48px 1fr auto;
    gap: 20px;
    align-items: start;
    padding: 24px 0;
    border-bottom: 1px solid var(--bk-border);
    transition: all 0.2s ease;
}
.bk-pillar-row:first-child {
    border-top: 2px solid var(--bk-forest);
}

/* 5. CAREER PROSPECTUS: SHADCN DATA TABLE / ARCHITECTURAL LEDGER */
.bk-career-table-wrap {
    background: #FFFFFF;
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-radius-lg);
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
}
.bk-career-thead {
    display: grid;
    grid-template-columns: 110px 1.3fr 1.6fr 1.3fr;
    gap: 20px;
    padding: 14px 24px;
    background: #F4F1EA;
    border-bottom: 1px solid var(--bk-border);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    color: var(--bk-ink-muted);
    text-transform: uppercase;
    letter-spacing: 0.08em;
}
.bk-career-trow {
    display: grid;
    grid-template-columns: 110px 1.3fr 1.6fr 1.3fr;
    gap: 20px;
    padding: 22px 24px;
    border-bottom: 1px solid #E2E8F0;
    align-items: start;
    transition: background-color 0.18s ease;
}
.bk-career-trow:last-child {
    border-bottom: none;
}
.bk-career-trow:hover {
    background-color: #FAF8F5;
}

/* 6. FACULTY ROSTER CARDS (ARCHITECTURAL PORTRAIT) */
.bk-faculty-card {
    flex: 0 0 300px;
    background: #FFFFFF;
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-radius-md);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
}
.bk-faculty-card:hover {
    border-color: var(--bk-forest);
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -8px rgba(13, 59, 34, 0.12);
}

/* 7. LAB BENTO ARCHITECTURAL SPLIT */
.bk-lab-bento {
    display: grid;
    grid-template-columns: repeat(12, minmax(0, 1fr));
    gap: 24px;
    align-items: stretch;
}
.bk-lab-main {
    grid-column: span 6 / span 6;
    background: var(--bk-forest);
    color: #FFFFFF;
    border-radius: var(--bk-radius-lg);
    padding: 32px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    border: 1px solid rgba(255, 255, 255, 0.12);
    position: relative;
    overflow: hidden;
}
.bk-lab-side {
    grid-column: span 6 / span 6;
    display: flex;
    flex-direction: column;
    gap: 24px;
}
.bk-lab-module {
    background: #FFFFFF;
    border: 1px solid var(--bk-border);
    border-radius: var(--bk-radius-lg);
    padding: 26px 28px;
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: border-color 0.2s ease;
}
.bk-lab-module:hover {
    border-color: var(--bk-forest);
}

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 1024px) {
    .bk-hero-col-left,
    .bk-hero-col-right,
    .bk-about-left,
    .bk-about-right,
    .bk-lab-main,
    .bk-lab-side {
        grid-column: span 12 / span 12;
    }
    .bk-ledger-strip {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .bk-ledger-item:nth-child(2) {
        border-right: none;
    }
    .bk-ledger-item:nth-child(1),
    .bk-ledger-item:nth-child(2) {
        border-bottom: 1px solid var(--bk-border);
    }
    .bk-prodi-matrix {
        grid-template-columns: 1fr;
    }
    .bk-prodi-col {
        border-right: none;
        border-bottom: 1px solid var(--bk-border);
    }
    .bk-prodi-col:last-child {
        border-bottom: none;
    }
    .bk-career-thead {
        display: none;
    }
    .bk-career-trow {
        grid-template-columns: 1fr;
        gap: 10px;
        padding: 20px;
    }
    .bk-news-grid {
        grid-template-columns: 1fr !important;
        gap: 0 !important;
    }
    .bk-news-editorial-col {
        border-right: none !important;
        border-bottom: 1px solid var(--bk-border) !important;
    }
    .bk-news-editorial-col:last-child {
        border-bottom: none !important;
    }
}

@media (max-width: 640px) {
    .bk-ledger-strip {
        grid-template-columns: 1fr;
    }
    .bk-ledger-item {
        border-right: none !important;
        border-bottom: 1px solid var(--bk-border);
        padding: 18px 20px;
    }
    .bk-ledger-item:last-child {
        border-bottom: none;
    }
    .bk-pillar-row {
        grid-template-columns: 1fr;
        gap: 10px;
    }
    .bk-hero-ledger-grid {
        grid-template-columns: 1fr;
    }
    .bk-hero-ledger-cell {
        border-right: none;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .bk-tabs-list {
        width: 100%;
        overflow-x: auto;
    }
}

/* ==========================================================================
   TRUE HERO MEDIA SPOTLIGHT (RESPONSIVE DESKTOP & MOBILE CANVAS)
   ========================================================================== */
.bk-spotlight-hero-wrap {
    position: relative;
    width: 100%;
    /* Full height accounting for fixed navbar AND WordPress admin bar (zero overflow) */
    height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
    min-height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
    max-height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
    background: #0D3B22; /* University Forest Deep Green: blends seamlessly with navbar */
    overflow: hidden;
    margin-top: 0;
    padding-top: 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.12);
    display: block;
}
@media (min-width: 1024px) {
    .bk-spotlight-hero-wrap {
        height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
        min-height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
        max-height: calc(100vh - var(--bk-navbar-height, 64px) - var(--bk-admin-bar-offset, 0px));
    }
}
@media (max-width: 768px) {
    .bk-spotlight-hero-wrap {
        height: calc(100dvh - var(--bk-navbar-height, 56px) - var(--bk-admin-bar-offset, 0px));
        min-height: calc(100dvh - var(--bk-navbar-height, 56px) - var(--bk-admin-bar-offset, 0px));
        max-height: calc(100dvh - var(--bk-navbar-height, 56px) - var(--bk-admin-bar-offset, 0px));
    }
    .bk-spotlight-hero-wrap.mobile-cinema {
        height: clamp(480px, 72vh, 660px);
        min-height: 480px;
    }
}

/* Device Visibility Targeting */
@media (max-width: 768px) {
    .bk-spotlight-slide.bk-device-desktop-only {
        display: none !important;
    }
}
@media (min-width: 769px) {
    .bk-spotlight-slide.bk-device-mobile-only {
        display: none !important;
    }
}

.bk-spotlight-stage-grid {
    position: absolute;
    inset: 0;
    background-image: 
        linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
        linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
    z-index: 1;
}

.bk-spotlight-viewport {
    position: relative;
    width: 100%;
    height: 100%;
    flex: 1;
    overflow: hidden;
    z-index: 2;
}

.bk-spotlight-slide {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 1;
    transition: opacity 0.7s cubic-bezier(0.16, 1, 0.3, 1), visibility 0.7s;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 0;
}
.bk-spotlight-slide.is-active {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    z-index: 3;
}

/* Fullscreen Media Canvas */
.bk-spotlight-canvas-box {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #0D3B22; /* University Forest Deep Green */
}

/* Video Player: Pinned to Top with ZERO gap and full cover fill */
.bk-spotlight-canvas-box video,
.bk-spotlight-video-el {
    position: absolute !important;
    top: 0 !important;
    left: 0 !important;
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    max-height: none !important;
    display: block !important;
    object-fit: cover !important;
    object-position: center top !important; /* Anchors to top: ELIMINATES any black gap */
}

/* Cloud Video Embed (YouTube, Google Drive, Vimeo) - Fullscreen Cinematic Background */
.bk-spotlight-embed-wrap {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #0D3B22;
}
.bk-spotlight-embed-wrap iframe {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 100vw;
    height: 56.25vw;
    min-height: 100%;
    min-width: 177.77vh;
    transform: translate(-50%, -50%) scale(1.08);
    border: none;
    pointer-events: none;
}
.bk-spotlight-canvas-box picture,
.bk-spotlight-canvas-box img {
    width: 100%;
    height: 100%;
    display: block;
    object-fit: contain;
    object-position: center center;
    background: radial-gradient(circle at center, #0d3b22 0%, #051a0f 70%, #000000 100%);
}
.bk-spotlight-canvas-box.fit-cover picture,
.bk-spotlight-canvas-box.fit-cover img {
    object-fit: cover;
}

/* Click Shield Layer */
.bk-spotlight-click-shield {
    position: absolute;
    inset: 0;
    z-index: 10;
    cursor: pointer;
    background: transparent;
}

/* Floating Action Prompt Pill (Dead Center of Viewport & Significantly Larger) */
.bk-spotlight-prompt-pill {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(0.85);
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
    z-index: 30;
    background: #F5B82E;
    color: #072314;
    padding: 24px 52px;
    border-radius: 999px;
    border: 3px solid rgba(255, 255, 255, 0.65);
    box-shadow: 0 30px 80px rgba(0, 0, 0, 0.8), 0 0 0 8px rgba(245, 184, 46, 0.45);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 18px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 19px;
    font-weight: 800;
    letter-spacing: 0.07em;
    text-transform: uppercase;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    text-decoration: none;
    user-select: none;
    backdrop-filter: blur(8px);
}
.bk-spotlight-prompt-pill.is-shown {
    opacity: 1;
    visibility: visible;
    pointer-events: auto;
    transform: translate(-50%, -50%) scale(1);
}
.bk-spotlight-prompt-pill:hover {
    background: #FFFFFF;
    color: #072314;
    transform: translate(-50%, -50%) scale(1.06);
    border-color: #FFFFFF;
    box-shadow: 0 36px 90px rgba(0, 0, 0, 0.9), 0 0 0 9px rgba(255, 255, 255, 0.75);
}
.bk-spotlight-prompt-pill svg {
    transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    flex-shrink: 0;
}
.bk-spotlight-prompt-pill:hover svg {
    transform: translateX(6px);
}

/* Corner Video Controls */
.bk-spotlight-corner-ctrls {
    position: absolute;
    top: 24px;
    right: 28px;
    z-index: 20;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    background: rgba(7, 35, 20, 0.82);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.25);
    border-radius: 999px;
    padding: 7px 16px;
    box-shadow: 0 8px 28px rgba(0, 0, 0, 0.45);
}
.bk-spotlight-corner-btn {
    background: transparent;
    border: none;
    color: #ffffff;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    padding: 3px 6px;
    border-radius: 4px;
    transition: color 0.2s ease;
}
.bk-spotlight-corner-btn:hover {
    color: #F5B82E;
}

/* Edge Arrow Navigation Controls (shadcn glassmorphism) */
.bk-spotlight-arrow {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 35;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: rgba(7, 35, 20, 0.72);
    backdrop-filter: blur(14px);
    -webkit-backdrop-filter: blur(14px);
    border: 1.5px solid rgba(255, 255, 255, 0.28);
    color: #FFFFFF;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    box-shadow: 0 12px 30px rgba(0, 0, 0, 0.45);
    padding: 0;
    outline: none;
}
.bk-spotlight-arrow:hover {
    background: #F5B82E;
    border-color: #F5B82E;
    color: #0D3B22;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 16px 36px rgba(245, 184, 46, 0.55);
}
.bk-spotlight-arrow:focus-visible {
    outline: 2px solid #F5B82E;
    outline-offset: 3px;
}
.bk-spotlight-prev {
    left: max(22px, env(safe-area-inset-left, 22px));
}
.bk-spotlight-next {
    right: max(22px, env(safe-area-inset-right, 22px));
}

/* Bottom Bar: Hairline Progress & Slide Indicator */
.bk-spotlight-bottom-bar {
    position: absolute;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 30;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.92), rgba(0, 0, 0, 0.4) 75%, transparent);
    padding: 14px var(--bk-edge-pad, 2rem) 16px var(--bk-edge-pad, 2rem);
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}
.bk-spotlight-progress-line {
    position: absolute;
    top: 0;
    left: 0;
    height: 4px;
    background: #F5B82E;
    width: 0%;
    transition: width 0.15s linear;
    z-index: 31;
}

/* Subtle Scroll Down Indicator */
.bk-spotlight-scroll-cue {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: rgba(255, 255, 255, 0.85);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11.5px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    text-decoration: none;
    transition: color 0.2s ease;
    cursor: pointer;
}
.bk-spotlight-scroll-cue:hover {
    color: #F5B82E;
}
.bk-spotlight-scroll-cue svg {
    animation: bk-bounce-down 1.8s infinite ease-in-out;
}
@keyframes bk-bounce-down {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(5px); }
}

@media (max-width: 640px) {
    .bk-spotlight-arrow {
        width: 44px;
        height: 44px;
    }
    .bk-spotlight-prev { left: 10px; }
    .bk-spotlight-next { right: 10px; }
    .bk-spotlight-prompt-pill {
        top: 50%;
        left: 50%;
        font-size: 15px;
        padding: 18px 32px;
        gap: 14px;
        max-width: calc(100vw - 40px);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.85), 0 0 0 5px rgba(245, 184, 46, 0.45);
    }
    .bk-spotlight-scroll-cue span {
        display: none;
    }
    .bk-spotlight-corner-ctrls {
        top: 14px;
        right: 14px;
        padding: 5px 12px;
    }
}
</style>

<main class="flex-grow bk-sans" style="background-color: var(--bk-bg-warm); margin-top: 0; padding-top: 0;">

<!-- =====================================================================
     SECTION 1: HERO SPOTLIGHT & MEDIA SHOWCASE SLIDER (RESPONSIVE DESKTOP & MOBILE)
     ===================================================================== -->
<?php
$spotlight_slides = function_exists('ppb_get_spotlight_slides') ? ppb_get_spotlight_slides() : array();
$spotlight_settings = function_exists('ppb_get_spotlight_settings') ? ppb_get_spotlight_settings() : array(
    'autoplay' => '1', 'speed' => 8, 'fit_mode' => 'contain', 'height_mode' => 'fullscreen', 'mobile_height_mode' => 'fullscreen'
);
$total_slides = count($spotlight_slides);
$fit_class = ($spotlight_settings['fit_mode'] === 'cover') ? 'fit-cover' : '';
$mobile_h_class = (($spotlight_settings['mobile_height_mode'] ?? 'fullscreen') === 'cinema') ? 'mobile-cinema' : 'mobile-fullscreen';
?>
<section id="hero_spotlight" class="bk-spotlight-hero-wrap <?php echo esc_attr($mobile_h_class); ?> relative w-full" aria-label="Hero Spotlight Media Showcase">

    <!-- Ambient Grid Backdrop -->
    <div class="bk-spotlight-stage-grid"></div>

    <!-- Edge Arrow Navigation Buttons (Prev & Next) -->
    <?php if ($total_slides > 1) : ?>
        <button type="button" class="bk-spotlight-arrow bk-spotlight-prev" id="bk-spotlight-btn-prev" aria-label="Slide sebelumnya" title="Slide Sebelumnya (Arrow Left)">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
        </button>
        <button type="button" class="bk-spotlight-arrow bk-spotlight-next" id="bk-spotlight-btn-next" aria-label="Slide berikutnya" title="Slide Berikutnya (Arrow Right)">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
        </button>
    <?php endif; ?>

    <!-- Slides Viewport Track (Full Viewport Screen-Filling) -->
    <div class="bk-spotlight-viewport" id="bk-spotlight-viewport" data-autoplay="<?php echo esc_attr($spotlight_settings['autoplay']); ?>" data-speed="<?php echo esc_attr($spotlight_settings['speed']); ?>">
        
        <?php foreach ($spotlight_slides as $s_idx => $s_item) : 
            $is_first = ($s_idx === 0);
            $s_type = $s_item['type'] ?? 'image';
            $s_device = $s_item['device_target'] ?? 'all';
            $s_video = $s_item['video_url'] ?? '';
            $s_raw_img = $s_item['image_url'] ?? '';
            $s_img = !empty($s_raw_img) ? (function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($s_raw_img) : $s_raw_img) : (get_template_directory_uri() . '/assets/img/gedung-unm.png');
            $s_raw_mob = $s_item['image_mobile_url'] ?? '';
            $s_img_mobile = !empty($s_raw_mob) ? (function_exists('bk_parse_spotlight_image_url') ? bk_parse_spotlight_image_url($s_raw_mob) : $s_raw_mob) : '';
            $target_url = $s_item['target_url'] ?? '';
            $action_text = $s_item['action_text'] ?: 'Lihat lebih lanjut';

            $device_class = '';
            if ($s_device === 'desktop_only') $device_class = 'bk-device-desktop-only';
            elseif ($s_device === 'mobile_only') $device_class = 'bk-device-mobile-only';
        ?>
        <div class="bk-spotlight-slide <?php echo $is_first ? 'is-active' : ''; ?> <?php echo esc_attr($device_class); ?>" 
             data-slide-index="<?php echo $s_idx; ?>" 
             data-slide-type="<?php echo esc_attr($s_type); ?>"
             data-device-target="<?php echo esc_attr($s_device); ?>"
             data-target-url="<?php echo esc_url($target_url); ?>"
             data-action-text="<?php echo esc_attr($action_text); ?>">
            
            <!-- Fullscreen Unobstructed Canvas Box -->
            <div class="bk-spotlight-canvas-box <?php echo esc_attr($fit_class); ?>">
                
                <?php if ($s_type === 'video' && !empty($s_video)) : 
                    $v_source = bk_parse_spotlight_video_source($s_video);
                ?>
                    <?php if ($v_source && in_array($v_source['type'], ['youtube', 'gdrive', 'vimeo'])) : ?>
                        <!-- Fullscreen Cinematic Cloud Video Stream (YouTube / Google Drive / Vimeo) -->
                        <div class="bk-spotlight-video-el bk-spotlight-embed-wrap">
                            <iframe id="bk-cloud-iframe-<?php echo $s_idx; ?>" 
                                    class="bk-cloud-iframe"
                                    src="<?php echo esc_url($v_source['embed_url']); ?>" 
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                                    allowfullscreen></iframe>
                        </div>

                        <!-- Corner Video Controls for Cloud Video (YouTube / Drive) -->
                        <div class="bk-spotlight-corner-ctrls">
                            <button type="button" class="bk-spotlight-corner-btn btn-toggle-audio" title="Nyalakan/Matikan Suara Video">
                                <span class="audio-icon">🔊</span>
                                <span class="audio-text">Nyalakan Audio</span>
                            </button>
                            <span style="color:rgba(255,255,255,0.3);">|</span>
                            <button type="button" class="bk-spotlight-corner-btn btn-toggle-play" title="Jeda/Putar Video">
                                <span class="play-icon">⏸</span>
                                <span class="play-text">Jeda</span>
                            </button>
                            <?php if ($v_source['type'] === 'youtube') : ?>
                                <span style="color:rgba(255,255,255,0.3);">|</span>
                                <span class="bk-mono" style="font-size:11px;color:#F5B82E;font-weight:700;">YOUTUBE</span>
                            <?php endif; ?>
                        </div>
                    <?php else : ?>
                        <!-- Direct MP4 Video Player -->
                        <video class="bk-spotlight-video-el" autoplay muted playsinline preload="auto" poster="<?php echo esc_url($s_img); ?>">
                            <source src="<?php echo esc_url($s_video); ?>" type="video/mp4">
                        </video>

                        <!-- Corner Controls for MP4 -->
                        <div class="bk-spotlight-corner-ctrls">
                            <button type="button" class="bk-spotlight-corner-btn btn-toggle-audio" title="Nyalakan/Matikan Suara Narasi">
                                <span class="audio-icon">🔊</span>
                                <span class="audio-text">Nyalakan Audio</span>
                            </button>
                            <span style="color:rgba(255,255,255,0.3);">|</span>
                            <button type="button" class="bk-spotlight-corner-btn btn-toggle-play" title="Jeda/Putar Video">
                                <span class="play-icon">⏸</span>
                                <span class="play-text">Jeda</span>
                            </button>
                        </div>
                    <?php endif; ?>

                <?php else : ?>
                    <!-- Responsive Photo / News Flyer / Banner using <picture> -->
                    <picture>
                        <?php if (!empty($s_img_mobile)) : ?>
                            <source media="(max-width: 768px)" srcset="<?php echo esc_url($s_img_mobile); ?>">
                        <?php endif; ?>
                        <img src="<?php echo esc_url($s_img); ?>" alt="Banner Informasi PPB FIP UNM" loading="<?php echo $is_first ? 'eager' : 'lazy'; ?>">
                    </picture>
                <?php endif; ?>

                <!-- Click Detection Shield (Triggers Action Prompt on first click to avoid misclicks) -->
                <?php if (!empty($target_url)) : ?>
                    <div class="bk-spotlight-click-shield" title="Klik untuk menampilkan aksi"></div>
                    <div class="bk-spotlight-prompt-pill" data-href="<?php echo esc_url($target_url); ?>">
                        <span><?php echo esc_html($action_text); ?></span>
                        <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </div>
                <?php endif; ?>

            </div>

        </div>
        <?php endforeach; ?>

    </div>

    <!-- Bottom Bar: Hairline Progress Line + Non-Obstructive Floating Badges -->
    <div class="bk-spotlight-bottom-bar">
        <div class="bk-spotlight-progress-line" id="bk-spotlight-progress-line"></div>

        <!-- Left Floating Pill: Slide Counter & Dots -->
        <div class="bk-spotlight-ctrl-pill">
            <div class="bk-mono" style="font-size:13px;color:#ffffff;font-weight:700;display:flex;align-items:center;gap:8px;">
                <span id="bk-spotlight-current-idx" style="color:#F5B82E;">01</span>
                <span style="color:rgba(255,255,255,0.4);">/</span>
                <span id="bk-spotlight-total-idx" style="color:rgba(255,255,255,0.7);"><?php echo sprintf("%02d", $total_slides); ?></span>
            </div>
            <span style="color:rgba(255,255,255,0.25);font-size:12px;">|</span>
            <div style="display:flex;align-items:center;gap:6px;" id="bk-spotlight-dots">
                <?php for ($d = 0; $d < $total_slides; $d++) : ?>
                    <button type="button" class="bk-spotlight-dot-pill <?php echo $d === 0 ? "active" : ""; ?>" data-go-to="<?php echo $d; ?>" aria-label="Menuju Slide <?php echo $d + 1; ?>" style="width:<?php echo $d === 0 ? "30px" : "10px"; ?>;height:6px;border-radius:3px;background:<?php echo $d === 0 ? "#F5B82E" : "rgba(255,255,255,0.35)"; ?>;border:none;padding:0;cursor:pointer;transition:all 0.25s ease;"></button>
                <?php endfor; ?>
            </div>
        </div>

        <!-- Center Floating Pill: Scroll Down Cue -->
        <a href="#stats_section" class="bk-spotlight-scroll-cue bk-spotlight-ctrl-pill" style="text-decoration:none;" title="Gulir ke bawah untuk melihat data &amp; rekam jejak">
            <span>GULIR KE BAWAH</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14"/><path d="m19 12-7 7-7-7"/></svg>
        </a>

        <!-- Right Floating Pill: Status & Autoplay Toggle -->
        <div class="bk-spotlight-ctrl-pill">
            <span class="bk-mono" id="bk-spotlight-mode-tag" style="font-size:11px;color:#F5B82E;font-weight:700;">
                VIDEO AKTIF
            </span>
            <span style="color:rgba(255,255,255,0.25);font-size:12px;">|</span>
            <button type="button" id="bk-spotlight-toggle-play" style="background:transparent;border:none;color:rgba(255,255,255,0.9);padding:0;font-size:11px;font-family:monospace;cursor:pointer;" title="Jeda/Lanjutkan pergantian slide otomatis">
                JEDA
            </button>
        </div>
    </div>

</section>
<!-- =====================================================================
     SECTION 2: SWISS 4-COLUMN ARCHITECTURAL DATA LEDGER (STATS)
     ===================================================================== -->
<section id="stats_section" class="bk-section-stats_section relative w-full" style="background-color: var(--bk-bg-warm); padding-top: 36px; padding-bottom: 20px;">
    <div class="bk-container">
        <div class="bk-ledger-strip">
            <!-- Metric 01 -->
            <div class="bk-ledger-item">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-ink-subtle);letter-spacing:0.08em;">01 // MUTU AKADEMIK</span>
                    <span class="bk-mono" style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;background:var(--bk-bg-sage);color:var(--bk-forest);">LAMDIK</span>
                </div>
                <div>
                    <div class="bk-serif" style="font-size:32px;font-weight:600;color:var(--bk-forest);line-height:1.05;letter-spacing:-0.02em;margin-bottom:6px;">
                        Unggul
                    </div>
                    <p style="font-size:12px;color:var(--bk-ink-muted);line-height:1.5;margin:0;">
                        Peringkat akreditasi tertinggi nasional untuk penyelenggaraan pendidikan konselor.
                    </p>
                </div>
            </div>

            <!-- Metric 02 -->
            <div class="bk-ledger-item">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-ink-subtle);letter-spacing:0.08em;">02 // TRADISI KEILMUAN</span>
                    <span class="bk-mono" style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;background:#F1F5F9;color:#334155;">HISTORIS</span>
                </div>
                <div>
                    <div class="bk-serif" style="font-size:32px;font-weight:600;color:var(--bk-ink);line-height:1.05;letter-spacing:-0.02em;margin-bottom:6px;font-variant-numeric:tabular-nums;">
                        1969
                    </div>
                    <p style="font-size:12px;color:var(--bk-ink-muted);line-height:1.5;margin:0;">
                        Lebih dari 55 tahun mencetak pendidik, konselor sekolah, dan ilmuwan psikologi pendidikan.
                    </p>
                </div>
            </div>

            <!-- Metric 03 -->
            <div class="bk-ledger-item">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-ink-subtle);letter-spacing:0.08em;">03 // OTORITAS PAKAR</span>
                    <span class="bk-mono" style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;background:#FEF3C7;color:#92400E;">PROFESOR</span>
                </div>
                <div>
                    <div class="bk-serif" style="font-size:32px;font-weight:600;color:var(--bk-ink);line-height:1.05;letter-spacing:-0.02em;margin-bottom:6px;font-variant-numeric:tabular-nums;">
                        4 Guru Besar
                    </div>
                    <p style="font-size:12px;color:var(--bk-ink-muted);line-height:1.5;margin:0;">
                        Didukung puluhan Doktor dan Lektor Kepala aktif dengan riset terindeks SINTA &amp; Scopus.
                    </p>
                </div>
            </div>

            <!-- Metric 04 -->
            <div class="bk-ledger-item">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                    <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-ink-subtle);letter-spacing:0.08em;">04 // ARSITEKTUR STUDI</span>
                    <span class="bk-mono" style="font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;background:var(--bk-bg-sage);color:var(--bk-forest);">KKNI 6-9</span>
                </div>
                <div>
                    <div class="bk-serif" style="font-size:32px;font-weight:600;color:var(--bk-forest);line-height:1.05;letter-spacing:-0.02em;margin-bottom:6px;">
                        S1 &middot; S2 &middot; S3
                    </div>
                    <p style="font-size:12px;color:var(--bk-ink-muted);line-height:1.5;margin:0;">
                        Kesinambungan studi linier dari jenjang Sarjana, Magister, hingga Doktoral di satu jurusan.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 3: PROGRAM STUDI TERINTEGRASI (SHADCN TABS + ARCHITECTURAL MATRIX)
     ===================================================================== -->
<section id="prodi_unified_section" class="bk-section-prodi_unified_section relative w-full" style="background-color: var(--bk-bg-warm); padding-top: 44px; padding-bottom: 56px;">
    <div class="bk-container" id="prodi-section">
        
        <!-- Section Header + shadcn Tabs Switcher -->
        <div class="bk-tabs-header">
            <div style="max-width:640px;">
                <div class="bk-sec-kicker">Kurikulum &amp; Jenjang Akademik</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3.2vw, 36px);color:var(--bk-ink);margin:0 0 8px 0;line-height:1.18;font-weight:500;letter-spacing:-0.02em;">
                    Pendidikan Berkelanjutan Sarjana hingga Doktor
                </h2>
                <p style="font-size:13.5px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                    Pilih tampilan matriks perbandingan atau telaah spesifikasi kurikulum Outcome-Based Education (OBE) pada masing-masing jenjang studi.
                </p>
            </div>

            <!-- Interactive TabsList -->
            <div class="bk-tabs-list" role="tablist" aria-label="Pilih Jenjang Studi">
                <button type="button" class="bk-tab-trigger is-active" data-prodi-tab="all" role="tab" aria-selected="true">
                    <span class="bk-mono" style="font-size:11px;opacity:0.7;">00</span> Semua Jenjang
                </button>
                <button type="button" class="bk-tab-trigger" data-prodi-tab="s1" role="tab" aria-selected="false">
                    <span class="bk-mono" style="font-size:11px;opacity:0.7;">S1</span> Sarjana
                </button>
                <button type="button" class="bk-tab-trigger" data-prodi-tab="s2" role="tab" aria-selected="false">
                    <span class="bk-mono" style="font-size:11px;opacity:0.7;">S2</span> Magister
                </button>
                <button type="button" class="bk-tab-trigger" data-prodi-tab="s3" role="tab" aria-selected="false">
                    <span class="bk-mono" style="font-size:11px;opacity:0.7;">S3</span> Doktoral
                </button>
            </div>
        </div>

        <!-- Unified Architectural Matrix (Shared 1px Hairlines, NOT floating AI cards) -->
        <div class="bk-prodi-matrix" id="bk-prodi-matrix-view">
            
            <!-- Column S1 -->
            <div class="bk-prodi-col" data-prodi-panel="s1">
                <div style="padding:28px 28px 20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid var(--bk-border-subtle);margin-bottom:18px;">
                        <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);letter-spacing:0.06em;">JENJANG 01 // KKNI LEVEL 6</span>
                        <span class="bk-mono" style="background:var(--bk-forest);color:var(--bk-gold);font-size:10px;font-weight:600;padding:3px 9px;border-radius:4px;">UNGGUL</span>
                    </div>

                    <h3 class="bk-serif" style="font-size:22px;color:var(--bk-ink);margin:0 0 8px 0;font-weight:600;line-height:1.25;">
                        S1 Bimbingan dan Konseling
                    </h3>
                    <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.65;margin:0 0 20px 0;">
                        Mempersiapkan sarjana pendidik dan praktisi layanan bimbingan konseling yang menguasai asesmen psikologis, konseling individual/kelompok, serta bimbingan klasikal di satuan pendidikan.
                    </p>

                    <!-- Technical Specification Ledger -->
                    <div style="background:var(--bk-bg-warm);border:1px solid var(--bk-border-subtle);border-radius:8px;padding:10px 14px;margin-bottom:20px;">
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Gelar Akademik</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">Sarjana Pendidikan (S.Pd.)</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Beban &amp; Masa Studi</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">144–146 SKS &middot; 8 Semester</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Kurikulum Aktif</span>
                            <strong class="bk-mono" style="color:var(--bk-forest);font-size:12px;">OBE Terintegrasi MBKM</strong>
                        </div>
                    </div>

                    <div class="bk-mono" style="font-size:10.5px;font-weight:600;color:var(--bk-ink-subtle);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">
                        KOMPETENSI UTAMA KURIKULUM:
                    </div>
                    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;font-size:12.5px;color:var(--bk-ink);">
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Mikrokonseling &amp; Teknik Terapi Perilaku/Kognitif</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Asesmen Psikologi Tes &amp; Non-Tes (DCM, AUM, IKMS)</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Manajemen Program BK Sekolah &amp; Konseling Karier</span>
                        </li>
                    </ul>
                </div>

                <div style="padding:16px 28px;border-top:1px solid var(--bk-border-subtle);background:#FAF8F5;display:flex;justify-content:space-between;align-items:center;">
                    <a href="<?php echo esc_url(home_url('/akademik/kurikulum/')); ?>" style="color:var(--bk-forest);font-size:12.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                        <span>Struktur Kurikulum S1</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/profil/visi-misi/')); ?>" class="bk-mono" style="font-size:11px;color:var(--bk-ink-subtle);text-decoration:none;">Visi Keilmuan</a>
                </div>
            </div>

            <!-- Column S2 -->
            <div class="bk-prodi-col" data-prodi-panel="s2">
                <div style="padding:28px 28px 20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid var(--bk-border-subtle);margin-bottom:18px;">
                        <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);letter-spacing:0.06em;">JENJANG 02 // KKNI LEVEL 8</span>
                        <span class="bk-mono" style="background:var(--bk-bg-sage);color:var(--bk-forest);border:1px solid var(--bk-border);font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">TERAKREDITASI</span>
                    </div>

                    <h3 class="bk-serif" style="font-size:22px;color:var(--bk-ink);margin:0 0 8px 0;font-weight:600;line-height:1.25;">
                        S2 Bimbingan dan Konseling
                    </h3>
                    <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.65;margin:0 0 20px 0;">
                        Mengembangkan penguasaan metodologi riset lanjut, rekayasa model intervensi psikososial, konseling lintas budaya, serta kepemimpinan akademik dan profesi konseling.
                    </p>

                    <!-- Technical Specification Ledger -->
                    <div style="background:var(--bk-bg-warm);border:1px solid var(--bk-border-subtle);border-radius:8px;padding:10px 14px;margin-bottom:20px;">
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Gelar Akademik</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">Magister Pendidikan (M.Pd.)</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Beban &amp; Masa Studi</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">38–42 SKS &middot; 4 Semester</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Fokus Luaran</span>
                            <strong class="bk-mono" style="color:var(--bk-forest);font-size:12px;">Tesis &amp; Jurnal Terindeks</strong>
                        </div>
                    </div>

                    <div class="bk-mono" style="font-size:10.5px;font-weight:600;color:var(--bk-ink-subtle);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">
                        KOMPETENSI UTAMA KURIKULUM:
                    </div>
                    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;font-size:12.5px;color:var(--bk-ink);">
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Metodologi Penelitian Kuantitatif, Kualitatif &amp; R&amp;D BK</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Pengembangan Modul &amp; Instrumen Psikometri Terapan</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Konseling Krisis, Rehabilitasi &amp; Trauma Multi-Kultur</span>
                        </li>
                    </ul>
                </div>

                <div style="padding:16px 28px;border-top:1px solid var(--bk-border-subtle);background:#FAF8F5;display:flex;justify-content:space-between;align-items:center;">
                    <a href="<?php echo esc_url(home_url('/akademik/')); ?>" style="color:var(--bk-forest);font-size:12.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                        <span>Informasi Akademik S2</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/profil/roadmap/')); ?>" class="bk-mono" style="font-size:11px;color:var(--bk-ink-subtle);text-decoration:none;">Peta Riset</a>
                </div>
            </div>

            <!-- Column S3 -->
            <div class="bk-prodi-col" data-prodi-panel="s3">
                <div style="padding:28px 28px 20px;">
                    <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:14px;border-bottom:1px solid var(--bk-border-subtle);margin-bottom:18px;">
                        <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);letter-spacing:0.06em;">JENJANG 03 // KKNI LEVEL 9</span>
                        <span class="bk-mono" style="background:var(--bk-bg-sage);color:var(--bk-forest);border:1px solid var(--bk-border);font-size:10px;font-weight:600;padding:2px 8px;border-radius:4px;">TERAKREDITASI</span>
                    </div>

                    <h3 class="bk-serif" style="font-size:22px;color:var(--bk-ink);margin:0 0 8px 0;font-weight:600;line-height:1.25;">
                        S3 Bimbingan dan Konseling
                    </h3>
                    <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.65;margin:0 0 20px 0;">
                        Mencetak filosof keilmuan, akademisi tingkat universitas, dan perumus kebijakan nasional yang mampu melahirkan kebaruan teori (novelty) konseling berakar kearifan budaya.
                    </p>

                    <!-- Technical Specification Ledger -->
                    <div style="background:var(--bk-bg-warm);border:1px solid var(--bk-border-subtle);border-radius:8px;padding:10px 14px;margin-bottom:20px;">
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Gelar Akademik</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">Doktor (Dr.)</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Beban &amp; Masa Studi</span>
                            <strong class="bk-mono" style="color:var(--bk-ink);font-size:12px;">42–48 SKS &middot; 6 Semester</strong>
                        </div>
                        <div class="bk-spec-row">
                            <span style="color:var(--bk-ink-subtle);">Fokus Luaran</span>
                            <strong class="bk-mono" style="color:var(--bk-forest);font-size:12px;">Disertasi &amp; Scopus/WoS</strong>
                        </div>
                    </div>

                    <div class="bk-mono" style="font-size:10.5px;font-weight:600;color:var(--bk-ink-subtle);text-transform:uppercase;letter-spacing:0.08em;margin-bottom:10px;">
                        KOMPETENSI UTAMA KURIKULUM:
                    </div>
                    <ul style="list-style:none;margin:0;padding:0;display:flex;flex-direction:column;gap:8px;font-size:12.5px;color:var(--bk-ink);">
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Filsafat Keilmuan &amp; Epistemologi Konseling Kontemporer</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Konstruksi Model Konseling Indigenos &amp; Kearifan Lokal</span>
                        </li>
                        <li style="display:flex;align-items:flex-start;gap:8px;">
                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#0D3B22" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px;flex-shrink:0;"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Publikasi Bereputasi Internasional &amp; Kebijakan Pendidikan</span>
                        </li>
                    </ul>
                </div>

                <div style="padding:16px 28px;border-top:1px solid var(--bk-border-subtle);background:#FAF8F5;display:flex;justify-content:space-between;align-items:center;">
                    <a href="<?php echo esc_url(home_url('/akademik/panduan/')); ?>" style="color:var(--bk-forest);font-size:12.5px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
                        <span>Pedoman Akademik S3</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/jurnal-bk/')); ?>" class="bk-mono" style="font-size:11px;color:var(--bk-ink-subtle);text-decoration:none;">Jurnal Ilmiah</a>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 4: VISI KEILMUAN & PILAR KELEMBAGAAN (SWISS HAIRLINE INDEX)
     ===================================================================== -->
<section id="about_section" class="bk-section-about_section relative w-full" style="background-color: #FFFFFF; padding-top: 60px; padding-bottom: 64px; border-top: 1px solid var(--bk-border); border-bottom: 1px solid var(--bk-border);">
    <div class="bk-container">
        <div class="bk-about-split">
            
            <!-- Left Column (5 cols): Editorial Essay & Leadership Pull-Quote -->
            <div class="bk-about-left">
                <div class="bk-sec-kicker">Identitas &amp; Filosofi Keilmuan</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3vw, 34px);color:var(--bk-ink);margin:0 0 14px 0;line-height:1.2;font-weight:500;letter-spacing:-0.02em;">
                    Berakar pada Integritas Insani, Bertumbuh melalui Sains &amp; Empati
                </h2>
                <p style="font-size:13.5px;color:var(--bk-ink-muted);line-height:1.7;margin:0 0 24px 0;">
                    Jurusan Psikologi Pendidikan dan Bimbingan (PPB) FIP UNM menyelenggarakan tridharma perguruan tinggi yang memadukan etika humanistik, praktik berbasis bukti (<em>evidence-based counseling</em>), dan nilai budaya lokal Sulawesi Selatan serta Nusantara.
                </p>

                <!-- Editorial Pull-Quote (Architectural Left Double Rule, not a rounded card) -->
                <blockquote style="margin:0 0 24px 0;padding:18px 20px;background:var(--bk-bg-warm);border-left:3px solid var(--bk-forest);border-top:1px solid var(--bk-border);border-right:1px solid var(--bk-border);border-bottom:1px solid var(--bk-border);border-radius:0 10px 10px 0;">
                    <p class="bk-serif" style="font-size:14.5px;font-style:italic;color:var(--bk-ink);line-height:1.65;margin:0 0 14px 0;">
                        &ldquo;Pendidikan konseling di FIP UNM dirancang untuk melahirkan sarjana, magister, dan doktor yang berintegritas moral, peka terhadap keragaman sosio-kultural, serta adaptif memanfaatkan inovasi psikoedukasi dan telekonseling.&rdquo;
                    </p>
                    <footer style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--bk-border);padding-top:10px;">
                        <div>
                            <div style="font-weight:700;color:var(--bk-forest);font-size:12.5px;">Dr. Suciani Latif, M.Pd.</div>
                            <div class="bk-mono" style="font-size:10.5px;color:var(--bk-ink-subtle);">KETUA JURUSAN PPB FIP UNM</div>
                        </div>
                        <a href="<?php echo esc_url(home_url('/profil/struktur/')); ?>" class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);text-decoration:none;">Struktur Pimpinan &rarr;</a>
                    </footer>
                </blockquote>

                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="<?php echo esc_url(home_url('/profil/dokumen/')); ?>" class="bk-btn-outline-dark">
                        <span>Dokumen Mutu &amp; Akreditasi</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>
                    <a href="<?php echo esc_url(home_url('/profil/roadmap/')); ?>" class="bk-btn-outline-dark">
                        <span>Roadmap Riset &amp; PkM</span>
                    </a>
                </div>
            </div>

            <!-- Right Column (7 cols): Swiss Horizontal Hairline Rows (Replaces 3 boxy AI cards) -->
            <div class="bk-about-right">
                <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:12px;">
                    <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-ink-subtle);letter-spacing:0.08em;">PILAR EKOSISTEM AKADEMIK // 01&mdash;03</span>
                    <span class="bk-mono" style="font-size:11px;color:var(--bk-forest);font-weight:600;">STANDAR NASIONAL &amp; ABKIN</span>
                </div>

                <!-- Pillar Row 01 -->
                <div class="bk-pillar-row">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span class="bk-mono" style="font-size:13px;font-weight:700;color:var(--bk-forest);">01</span>
                        <div style="width:36px;height:36px;border-radius:8px;border:1px solid var(--bk-border);background:var(--bk-bg-warm);display:flex;align-items:center;justify-content:center;color:var(--bk-forest);">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="bk-serif" style="font-size:18px;font-weight:600;color:var(--bk-ink);margin:0 0 6px 0;">
                            Laboratorium Mikrokonseling &amp; Asesmen Terpadu
                        </h3>
                        <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                            Fasilitas praktikum konseling individual dan kelompok berstandar akustik dengan <em>one-way mirror</em>, sistem supervisi audio-visual PTZ resolusi tinggi, serta repositori instrumen tes psikologi dan non-tes (IKMS Digital).
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span class="bk-mono" style="display:inline-block;font-size:10.5px;font-weight:600;padding:4px 10px;border-radius:4px;background:var(--bk-bg-sage);color:var(--bk-forest);white-space:nowrap;">
                            Lab BK Terpadu
                        </span>
                    </div>
                </div>

                <!-- Pillar Row 02 -->
                <div class="bk-pillar-row">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span class="bk-mono" style="font-size:13px;font-weight:700;color:var(--bk-forest);">02</span>
                        <div style="width:36px;height:36px;border-radius:8px;border:1px solid var(--bk-border);background:var(--bk-bg-warm);display:flex;align-items:center;justify-content:center;color:var(--bk-forest);">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5v-15A2.5 2.5 0 0 1 6.5 2H20v20H6.5a2.5 2.5 0 0 1 0-5H20"/></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="bk-serif" style="font-size:18px;font-weight:600;color:var(--bk-ink);margin:0 0 6px 0;">
                            Produktivitas Riset SINTA, Scopus &amp; Hibah PNBP
                        </h3>
                        <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                            Sivitas akademika aktif menyelenggarakan penelitian kompetitif nasional (DRTPM Kemendikbudristek) dan PNBP UNM, serta mengelola publikasi berkala melalui Jurnal Ilmiah Program Studi Bimbingan dan Konseling.
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span class="bk-mono" style="display:inline-block;font-size:10.5px;font-weight:600;padding:4px 10px;border-radius:4px;background:#F1F5F9;color:#334155;white-space:nowrap;">
                            SINTA &amp; Scopus
                        </span>
                    </div>
                </div>

                <!-- Pillar Row 03 -->
                <div class="bk-pillar-row">
                    <div style="display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span class="bk-mono" style="font-size:13px;font-weight:700;color:var(--bk-forest);">03</span>
                        <div style="width:36px;height:36px;border-radius:8px;border:1px solid var(--bk-border);background:var(--bk-bg-warm);display:flex;align-items:center;justify-content:center;color:var(--bk-forest);">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        </div>
                    </div>
                    <div>
                        <h3 class="bk-serif" style="font-size:18px;font-weight:600;color:var(--bk-ink);margin:0 0 6px 0;">
                            Kemitraan Profesi ABKIN, MGBK &amp; Jejaring Instansi
                        </h3>
                        <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                            Kolaborasi erat bersama Pengurus Daerah/Cabang ABKIN, Musyawarah Guru Bimbingan dan Konseling (MGBK), sekolah mitra PLP/PPL, BNN, dinas pemberdayaan perempuan-anak, serta lembaga layanan psikososial.
                        </p>
                    </div>
                    <div style="text-align:right;">
                        <span class="bk-mono" style="display:inline-block;font-size:10.5px;font-weight:600;padding:4px 10px;border-radius:4px;background:var(--bk-bg-sage);color:var(--bk-forest);white-space:nowrap;">
                            Mitra Strategis
                        </span>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>

<!-- =====================================================================
     SECTION 5: PROSPEK PROFESI & KARIER LULUSAN (SHADCN DATA TABLE LEDGER)
     ===================================================================== -->
<section id="lulusan_unified_section" class="bk-section-lulusan_unified_section relative w-full" style="background-color: var(--bk-bg-warm); padding-top: 56px; padding-bottom: 60px;">
    <div class="bk-container">
        
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
            <div style="max-width:660px;">
                <div class="bk-sec-kicker">Profil Lulusan &amp; Keterserapan Kerja</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3vw, 34px);color:var(--bk-ink);margin:0 0 6px 0;font-weight:500;letter-spacing:-0.02em;">
                    Matriks Karier &amp; Kualifikasi Lulusan PPB UNM
                </h2>
                <p style="font-size:13.5px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                    Pemetaan profil lulusan berdasarkan Capaian Pembelajaran Lulusan (CPL) dan data penelusuran alumni (Tracer Study) pada berbagai sektor pendidikan dan layanan masyarakat.
                </p>
            </div>
            <a href="<?php echo esc_url(home_url('/kemahasiswaan/alumni/')); ?>" class="bk-btn-outline-dark">
                <span>Direktori Alumni &amp; Tracer Study</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <!-- Structured Prospectus Table (Replaces 3 identical cards) -->
        <div class="bk-career-table-wrap">
            <div class="bk-career-thead">
                <div>KODE // JALUR</div>
                <div>PROFIL PROFESI UTAMA</div>
                <div>DESKRIPSI PERAN &amp; RANAH INSTITUSI</div>
                <div>KOMPETENSI INTI TERUJI</div>
            </div>

            <!-- Row 1 -->
            <div class="bk-career-trow">
                <div>
                    <span class="bk-mono" style="font-size:11.5px;font-weight:700;color:var(--bk-forest);display:block;">PRF-01</span>
                    <span class="bk-mono" style="font-size:10px;color:var(--bk-ink-subtle);background:var(--bk-bg-sage);padding:2px 6px;border-radius:4px;display:inline-block;margin-top:4px;">PENDIDIKAN</span>
                </div>
                <div>
                    <h3 class="bk-serif" style="font-size:17px;font-weight:600;color:var(--bk-ink);margin:0 0 3px 0;">Konselor Satuan Pendidikan</h3>
                    <div style="font-size:12px;color:var(--bk-forest);font-weight:600;">Guru Bimbingan dan Konseling (SD/SMP/SMA/SMK)</div>
                </div>
                <div style="font-size:12.5px;color:var(--bk-ink-muted);line-height:1.6;">
                    Merancang, melaksanakan, dan mengevaluasi layanan dasar, layanan responsif, perencanaan individual, serta dukungan sistem pada sekolah negeri maupun swasta.
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Konseling Individual</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Bimbingan Klasikal</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Asesmen IKMS</span>
                </div>
            </div>

            <!-- Row 2 -->
            <div class="bk-career-trow">
                <div>
                    <span class="bk-mono" style="font-size:11.5px;font-weight:700;color:var(--bk-forest);display:block;">PRF-02</span>
                    <span class="bk-mono" style="font-size:10px;color:var(--bk-ink-subtle);background:#F1F5F9;padding:2px 6px;border-radius:4px;display:inline-block;margin-top:4px;">PRAKTISI</span>
                </div>
                <div>
                    <h3 class="bk-serif" style="font-size:17px;font-weight:600;color:var(--bk-ink);margin:0 0 3px 0;">Konselor Komunitas &amp; Edupreneur</h3>
                    <div style="font-size:12px;color:var(--bk-forest);font-weight:600;">Praktisi Pengembangan Potensi &amp; Biro Konsultasi</div>
                </div>
                <div style="font-size:12.5px;color:var(--bk-ink-muted);line-height:1.6;">
                    Mengelola biro layanan konsultasi psikologi-pendidikan, bimbingan keluarga, <em>career coaching</em>, rehabilitasi sosial (BNN/Lapas/P2TP2A), serta pelatihan SDM.
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Career Coaching</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Konseling Keluarga</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Fasilitator Pelatihan</span>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="bk-career-trow">
                <div>
                    <span class="bk-mono" style="font-size:11.5px;font-weight:700;color:var(--bk-forest);display:block;">PRF-03</span>
                    <span class="bk-mono" style="font-size:10px;color:var(--bk-ink-subtle);background:#F1F5F9;padding:2px 6px;border-radius:4px;display:inline-block;margin-top:4px;">RISET &amp; MEDIA</span>
                </div>
                <div>
                    <h3 class="bk-serif" style="font-size:17px;font-weight:600;color:var(--bk-ink);margin:0 0 3px 0;">Peneliti &amp; Pengembang Instrumen</h3>
                    <div style="font-size:12px;color:var(--bk-forest);font-weight:600;">Inovator Media BK &amp; Asesmen Psikometri</div>
                </div>
                <div style="font-size:12.5px;color:var(--bk-ink-muted);line-height:1.6;">
                    Merancang dan memvalidasi instrumen asesmen psikologi pendidikan, mengembangkan media bimbingan berbasis digital/aplikasi, serta melaksanakan riset kebijakan pendidikan.
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Uji Psikometri</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Media Cybercounseling</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Publikasi Ilmiah</span>
                </div>
            </div>

            <!-- Row 4 -->
            <div class="bk-career-trow">
                <div>
                    <span class="bk-mono" style="font-size:11.5px;font-weight:700;color:var(--bk-forest);display:block;">PRF-04</span>
                    <span class="bk-mono" style="font-size:10px;color:#92400E;background:#FEF3C7;padding:2px 6px;border-radius:4px;display:inline-block;margin-top:4px;">MAGISTER / DOKTOR</span>
                </div>
                <div>
                    <h3 class="bk-serif" style="font-size:17px;font-weight:600;color:var(--bk-ink);margin:0 0 3px 0;">Akademisi &amp; Pakar Kebijakan BK</h3>
                    <div style="font-size:12px;color:var(--bk-forest);font-weight:600;">Dosen Perguruan Tinggi, Widyaiswara &amp; Konsultan Ahli</div>
                </div>
                <div style="font-size:12.5px;color:var(--bk-ink-muted);line-height:1.6;">
                    Lulusan jenjang S2 dan S3 berkiprah sebagai dosen program studi BK/Psikologi di berbagai perguruan tinggi nasional, widyaiswara balai diklat, dan pengawas/konsultan pendidikan.
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Konstruksi Teori BK</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Supervisi Klinis</span>
                    <span class="bk-mono" style="font-size:10.5px;background:var(--bk-bg-warm);border:1px solid var(--bk-border);color:var(--bk-ink);padding:3px 8px;border-radius:4px;">Riset Bereputasi</span>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 6: DIREKTORI DOSEN & KEPAKARAN AKADEMIK (DYNAMIC ROSTER)
     ===================================================================== -->
<section id="dosen_animated_section" class="bk-section-dosen_animated_section relative w-full" style="background-color: var(--bk-bg-sage); padding-top: 56px; padding-bottom: 60px; border-top: 1px solid var(--bk-border); border-bottom: 1px solid var(--bk-border);">
    <div class="bk-container">
        
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:24px;">
            <div style="max-width:680px;">
                <div class="bk-sec-kicker">Sivitas Akademika &amp; Peneliti</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3vw, 34px);color:var(--bk-forest);margin:0 0 6px 0;font-weight:500;letter-spacing:-0.02em;">
                    Dosen &amp; Otoritas Kepakaran Akademik
                </h2>
                <p style="font-size:13.5px;color:#334E3F;margin:0;line-height:1.6;">
                    Profil pimpinan jurusan, Guru Besar (Prof.), Doktor (Dr.), dan tenaga pengajar beserta rekam jejak publikasi SINTA/Scopus. Geser atau klik kartu untuk membuka kurikulum vitae lengkap.
                </p>
            </div>
            <div style="display:flex;align-items:center;gap:10px;">
                <button type="button" id="bk-dosen-left" style="width:40px;height:40px;border-radius:8px;background:#ffffff;border:1px solid var(--bk-border);color:var(--bk-forest);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,0.04);" aria-label="Geser ke kiri">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                <button type="button" id="bk-dosen-right" style="width:40px;height:40px;border-radius:8px;background:#ffffff;border:1px solid var(--bk-border);color:var(--bk-forest);display:inline-flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 1px 2px rgba(0,0,0,0.04);" aria-label="Geser ke kanan">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
                <a href="<?php echo esc_url(home_url('/profil/dosen/')); ?>" class="bk-btn-outline-dark" style="margin-left:4px;">
                    <span>Direktori Lengkap (<?php echo esc_html($total_dosen_count); ?> Dosen)</span>
                    <span aria-hidden="true">&rarr;</span>
                </a>
            </div>
        </div>

        <div id="bk-dosen-container" style="position:relative;width:100%;overflow:hidden;">
            <div id="bk-dosen-track" class="no-scrollbar" style="display:flex;gap:20px;overflow-x:auto;padding:6px 2px 14px 2px;user-select:none;cursor:grab;">
                <?php
                // Render Featured Dosen + Duplicate set for seamless loop
                $render_list = array_merge($featured_dosen, $featured_dosen);
                foreach ($render_list as $d) :
                    $nama = $d['nama_lengkap'] ?? ($d['nama'] ?? '');
                    $slug = $d['slug'] ?? '';
                    $jabatan = $d['jabatan_fungsional'] ?? 'Dosen Bimbingan & Konseling';
                    $struktural = $d['jabatan_struktural'] ?? '';
                    if ($slug === 'abdul-saman' && empty($struktural)) $struktural = 'Dekan FIP UNM';
                    if ($slug === 'suciani-latif' && empty($struktural)) $struktural = 'Ketua Jurusan PPB';
                    if ($slug === 'sahril-buchori' && empty($struktural)) $struktural = 'Sekretaris Jurusan PPB';
                    $role_line = $struktural ? ($struktural . ' · ' . $jabatan) : $jabatan;

                    $is_prof = (stripos($nama, 'prof.') !== false || stripos($jabatan, 'guru besar') !== false);
                    $is_dr = (!$is_prof && (stripos($nama, 'dr.') !== false || stripos($jabatan, 'doktor') !== false));
                    $badge_txt = $is_prof ? 'GURU BESAR' : ($is_dr ? 'DOKTOR (DR.)' : 'DOSEN PPB');
                    $top_bar_color = $is_prof ? '#F5B82E' : '#0D3B22';
                    $badge_bg = $is_prof ? '#FEF3C7' : '#E8F0EA';
                    $badge_fg = $is_prof ? '#92400E' : '#0D3B22';

                    $pub_count = (int)($d['stat']['publikasi'] ?? count($d['publikasi'] ?? array()));
                    $pen_count = (int)($d['stat']['penelitian'] ?? count($d['penelitian'] ?? array()));
                    $kepakaran = $d['kepakaran'] ?? array();
                    if (empty($kepakaran) && !empty($d['bidang_keahlian'])) {
                        $kepakaran = array_filter(array_map('trim', preg_split('/[,;]+/', $d['bidang_keahlian'])));
                    }
                    if (empty($kepakaran)) {
                        $kepakaran = array('Bimbingan & Konseling', 'Psikologi Pendidikan');
                    }
                    $foto = $d['foto_link'] ?? '';
                    $init = bk_home_initials($nama);
                    $url = home_url('/profil/dosen/' . $slug . '/');
                ?>
                <div class="bk-faculty-card" data-url="<?php echo esc_url($url); ?>" role="link" tabindex="0">
                    <div>
                        <div style="height:4px;width:100%;background:<?php echo esc_attr($top_bar_color); ?>;"></div>
                        <div style="padding:20px 20px 14px;">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px;">
                                <?php if (!empty($foto)) : ?>
                                    <img src="<?php echo esc_url($foto); ?>" alt="<?php echo esc_attr($nama); ?>" style="width:46px;height:46px;border-radius:8px;object-fit:cover;border:1px solid var(--bk-border);background:var(--bk-bg-warm);">
                                <?php else : ?>
                                    <div class="bk-serif" style="width:46px;height:46px;border-radius:8px;background:var(--bk-forest);color:var(--bk-gold);display:flex;align-items:center;justify-content:center;font-weight:600;font-size:16px;border:1px solid rgba(13,59,34,0.2);">
                                        <?php echo esc_html($init); ?>
                                    </div>
                                <?php endif; ?>
                                <span class="bk-mono" style="background:<?php echo esc_attr($badge_bg); ?>;color:<?php echo esc_attr($badge_fg); ?>;font-size:10px;font-weight:600;padding:3px 8px;border-radius:4px;letter-spacing:0.04em;">
                                    <?php echo esc_html($badge_txt); ?>
                                </span>
                            </div>

                            <h3 class="bk-serif" style="font-size:15.5px;font-weight:600;color:var(--bk-ink);line-height:1.35;margin:0 0 4px 0;">
                                <?php echo esc_html($nama); ?>
                            </h3>
                            <p style="font-size:11.5px;color:var(--bk-forest);font-weight:600;margin:0 0 12px 0;line-height:1.4;">
                                <?php echo esc_html($role_line); ?>
                            </p>

                            <div style="display:flex;gap:5px;flex-wrap:wrap;margin-bottom:4px;">
                                <?php foreach (array_slice($kepakaran, 0, 2) as $tag) : ?>
                                    <span style="background:var(--bk-bg-warm);border:1px solid var(--bk-border-subtle);font-size:10.5px;color:var(--bk-ink-muted);padding:2px 7px;border-radius:4px;">
                                        <?php echo esc_html($tag); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div style="padding:12px 20px;background:var(--bk-bg-warm);border-top:1px solid var(--bk-border-subtle);display:flex;justify-content:space-between;align-items:center;">
                        <div class="bk-mono" style="font-size:11px;color:var(--bk-ink-muted);">
                            Publikasi: <strong style="color:var(--bk-forest);"><?php echo esc_html($pub_count); ?></strong> &middot; Riset: <strong style="color:var(--bk-forest);"><?php echo esc_html($pen_count); ?></strong>
                        </div>
                        <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);">Profil &rarr;</span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <script>
    (function() {
        var track = document.getElementById('bk-dosen-track');
        var btnLeft = document.getElementById('bk-dosen-left');
        var btnRight = document.getElementById('bk-dosen-right');
        if (!track) return;

        if (btnLeft) {
            btnLeft.addEventListener('click', function(e) {
                e.preventDefault();
                track.scrollBy({ left: -320, behavior: 'smooth' });
            });
        }
        if (btnRight) {
            btnRight.addEventListener('click', function(e) {
                e.preventDefault();
                track.scrollBy({ left: 320, behavior: 'smooth' });
            });
        }

        var isDown = false;
        var startX = 0;
        var scrollLeft = 0;
        var isDragging = false;

        track.addEventListener('mousedown', function(e) {
            isDown = true;
            isDragging = false;
            track.style.cursor = 'grabbing';
            startX = e.pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
        });

        track.addEventListener('mouseleave', function() {
            isDown = false;
            track.style.cursor = 'grab';
        });

        track.addEventListener('mouseup', function() {
            isDown = false;
            track.style.cursor = 'grab';
        });

        track.addEventListener('mousemove', function(e) {
            if (!isDown) return;
            var x = e.pageX - track.offsetLeft;
            var walk = (x - startX);
            if (Math.abs(walk) > 6) {
                isDragging = true;
                e.preventDefault();
                track.scrollLeft = scrollLeft - walk;
            }
        });

        var touchStartX = 0;
        var touchMoved = false;
        track.addEventListener('touchstart', function(e) {
            touchStartX = e.touches[0].clientX;
            touchMoved = false;
        }, { passive: true });

        track.addEventListener('touchmove', function(e) {
            var diff = Math.abs(e.touches[0].clientX - touchStartX);
            if (diff > 8) touchMoved = true;
        }, { passive: true });

        track.querySelectorAll('.bk-faculty-card').forEach(function(card) {
            card.addEventListener('click', function(e) {
                if (isDragging || touchMoved) {
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                var targetUrl = card.getAttribute('data-url');
                if (targetUrl) {
                    window.location.href = targetUrl;
                }
            });
            card.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    var targetUrl = card.getAttribute('data-url');
                    if (targetUrl) window.location.href = targetUrl;
                }
            });
        });
    })();
    </script>
</section>

<!-- =====================================================================
     SECTION 7: LABORATORIUM BK TERPADU (ASYMMETRIC BENTO ARCHITECTURAL SPLIT)
     ===================================================================== -->
<section id="lab_unified_section" class="bk-section-lab_unified_section relative w-full" style="background-color: #FFFFFF; padding-top: 60px; padding-bottom: 64px; border-bottom: 1px solid var(--bk-border);">
    <div class="bk-container">
        
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:28px;">
            <div style="max-width:660px;">
                <div class="bk-sec-kicker">Infrastruktur Praktikum &amp; Layanan</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3vw, 34px);color:var(--bk-ink);margin:0 0 6px 0;font-weight:500;letter-spacing:-0.02em;">
                    Laboratorium Bimbingan dan Konseling Terpadu
                </h2>
                <p style="font-size:13.5px;color:var(--bk-ink-muted);line-height:1.6;margin:0;">
                    Dirancang sesuai standar kode etik dan pedoman sarana praktikum Asosiasi Bimbingan dan Konseling Indonesia (ABKIN) untuk menjamin kerahasiaan, akurasi asesmen, dan mutu supervisi klinis.
                </p>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" class="bk-btn-outline-dark">
                    <span>Inventaris Alat &amp; Instrumen</span>
                </a>
                <a href="<?php echo esc_url(home_url('/lab-bk/')); ?>" class="bk-btn-outline-dark" style="background:var(--bk-forest);color:#ffffff;border-color:var(--bk-forest);">
                    <span>Portal Laboratorium BK &rarr;</span>
                </a>
            </div>
        </div>

        <!-- Asymmetric Bento Split: 1 Dark Featured Blueprint + 2 Stacked Technical Modules (Zero Emojis) -->
        <div class="bk-lab-bento">
            
            <!-- Left Main Module (6 cols) -->
            <div class="bk-lab-main">
                <div>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:22px;">
                        <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-gold);letter-spacing:0.08em;">FASILITAS UTAMA // LAB-01</span>
                        <span class="bk-mono" style="font-size:10px;padding:3px 9px;border-radius:4px;background:rgba(255,255,255,0.1);color:#ffffff;border:1px solid rgba(255,255,255,0.15);">REAL-TIME SUPERVISION</span>
                    </div>

                    <h3 class="bk-serif" style="font-size:26px;font-weight:500;color:#ffffff;margin:0 0 12px 0;line-height:1.25;">
                        Studio Mikrokonseling &amp; Ruang Observasi Cermin Satu Arah
                    </h3>
                    <p style="font-size:13.5px;color:rgba(255,255,255,0.82);line-height:1.7;margin:0 0 28px 0;">
                        Ruang praktikum kedap suara yang memisahkan ruang sesi konseling dan ruang pengamat melalui <em>one-way mirror glass</em>, memungkinkan dosen pembimbing dan rekan mahasiswa melakukan observasi keterampilan dasar komunikasi konseling tanpa mengganggu jalannya sesi.
                    </p>
                </div>

                <div>
                    <div style="display:grid;grid-template-columns:repeat(2, minmax(0, 1fr));gap:12px;border-top:1px solid rgba(255,255,255,0.14);padding-top:20px;">
                        <div style="background:rgba(0,0,0,0.22);border:1px solid rgba(255,255,255,0.1);padding:12px 14px;border-radius:8px;">
                            <div class="bk-mono" style="font-size:10px;color:var(--bk-gold);margin-bottom:3px;">VISUAL &amp; OPTIK</div>
                            <div style="font-size:12.5px;font-weight:600;color:#ffffff;">One-Way Mirror &amp; Kamera PTZ Multi-Sudut</div>
                        </div>
                        <div style="background:rgba(0,0,0,0.22);border:1px solid rgba(255,255,255,0.1);padding:12px 14px;border-radius:8px;">
                            <div class="bk-mono" style="font-size:10px;color:var(--bk-gold);margin-bottom:3px;">AKUSTIK &amp; AUDIO</div>
                            <div style="font-size:12.5px;font-weight:600;color:#ffffff;">Peredam Akustik &amp; Perekam Kondensor</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Stacked Modules (6 cols) -->
            <div class="bk-lab-side">
                
                <!-- Module 02 -->
                <div class="bk-lab-module">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                            <div style="display:inline-flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:var(--bk-bg-sage);color:var(--bk-forest);display:flex;align-items:center;justify-content:center;">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="8" height="4" x="8" y="2" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                                </div>
                                <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);letter-spacing:0.06em;">LAB-02 // PSIKOMETRI &amp; DIAGNOSTIK</span>
                            </div>
                            <span class="bk-mono" style="font-size:10.5px;color:var(--bk-ink-subtle);background:var(--bk-bg-warm);border:1px solid var(--bk-border);padding:2px 8px;border-radius:4px;">TES &amp; NON-TES</span>
                        </div>

                        <h3 class="bk-serif" style="font-size:20px;font-weight:600;color:var(--bk-ink);margin:0 0 8px 0;">
                            Bank Instrumen Tes Psikologi &amp; Sistem IKMS Digital
                        </h3>
                        <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.65;margin:0 0 16px 0;">
                            Menyediakan perangkat tes inteligensi (SPM/CPM/CFIT), tes bakat diferensial (DAT), inventori minat jabatan (RMIB), serta instrumen non-tes terstandar seperti Alat Ungkap Masalah (AUM), Daftar Cek Masalah (DCM), dan Identifikasi Kebutuhan dan Masalah Siswa (IKMS).
                        </p>
                    </div>
                    <div style="border-top:1px solid var(--bk-border-subtle);padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                        <span class="bk-mono" style="color:var(--bk-ink-subtle);font-size:11px;">Standar Praktikum Asesmen BK</span>
                        <a href="<?php echo esc_url(home_url('/lab-bk/inventaris/')); ?>" style="color:var(--bk-forest);font-weight:600;text-decoration:none;">Lihat Katalog Alat Tes &rarr;</a>
                    </div>
                </div>

                <!-- Module 03 -->
                <div class="bk-lab-module">
                    <div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
                            <div style="display:inline-flex;align-items:center;gap:10px;">
                                <div style="width:36px;height:36px;border-radius:8px;background:var(--bk-bg-sage);color:var(--bk-forest);display:flex;align-items:center;justify-content:center;">
                                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10"/><path d="m9 12 2 2 4-4"/></svg>
                                </div>
                                <span class="bk-mono" style="font-size:11px;font-weight:600;color:var(--bk-forest);letter-spacing:0.06em;">LAB-03 // LAYANAN KONSELING</span>
                            </div>
                            <span class="bk-mono" style="font-size:10.5px;color:var(--bk-ink-subtle);background:var(--bk-bg-warm);border:1px solid var(--bk-border);padding:2px 8px;border-radius:4px;">PRIVASI 100%</span>
                        </div>

                        <h3 class="bk-serif" style="font-size:20px;font-weight:600;color:var(--bk-ink);margin:0 0 8px 0;">
                            Bilik Konseling Individual &amp; Ruang Dinamika Kelompok
                        </h3>
                        <p style="font-size:13px;color:var(--bk-ink-muted);line-height:1.65;margin:0 0 16px 0;">
                            Bilik konseling individual dengan tata ruang ergonomis dan pencahayaan hangat untuk menjaga asas kerahasiaan konseli, dilengkapi ruang bimbingan/konseling kelompok berkapasitas 8&ndash;12 peserta untuk simulasi dinamika kelompok.
                        </p>
                    </div>
                    <div style="border-top:1px solid var(--bk-border-subtle);padding-top:12px;display:flex;justify-content:space-between;align-items:center;font-size:12px;">
                        <span class="bk-mono" style="color:var(--bk-ink-subtle);font-size:11px;">Sesuai Kode Etik Konselor Indonesia</span>
                        <a href="<?php echo esc_url(home_url('/layanan/')); ?>" style="color:var(--bk-forest);font-weight:600;text-decoration:none;">Prosedur Layanan &rarr;</a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>

<!-- =====================================================================
     SECTION 8: WARTA AKADEMIK & PENGABDIAN (EDITORIAL DISPATCH)
     ===================================================================== -->
<section id="news_unified_section" class="bk-section-news_unified_section relative w-full" style="background-color: var(--bk-bg-warm); padding-top: 56px; padding-bottom: 64px;">
    <div class="bk-container">
        <div style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:16px;margin-bottom:28px;">
            <div style="max-width:640px;">
                <div class="bk-sec-kicker">Warta Jurusan &amp; Diseminasi</div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3vw, 34px);color:var(--bk-ink);margin:0 0 6px 0;font-weight:500;letter-spacing:-0.02em;">
                    Kabar Akademik, Riset &amp; Pengabdian
                </h2>
                <p style="font-size:13.5px;color:var(--bk-ink-muted);margin:0;line-height:1.6;">
                    Dokumentasi kegiatan tridharma, kuliah tamu, seminar nasional, pengukuhan guru besar, serta capaian prestasi mahasiswa PPB FIP UNM.
                </p>
            </div>
            <a href="<?php echo esc_url(home_url('/berita/')); ?>" class="bk-btn-outline-dark">
                <span>Indeks Arsip Berita</span>
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>

        <?php echo do_shortcode('[ppb_home_news]'); ?>
    </div>
</section>

<!-- =====================================================================
     SECTION 9: ARCHITECTURAL ADMISSION LEDGER BANNER (PMB UNM)
     ===================================================================== -->
<section id="cta_section" class="bk-section-cta_section relative w-full" style="background-color: #0D3B22; padding-top: 56px; padding-bottom: 60px; border-top: 1px solid rgba(255,255,255,0.12);">
    <div class="bk-container">
        <div style="background:rgba(7, 35, 20, 0.72);border:1px solid rgba(255,255,255,0.16);border-radius:14px;padding:clamp(28px, 4vw, 44px);display:grid;grid-template-columns:repeat(12, minmax(0, 1fr));gap:32px;align-items:center;">
            
            <div style="grid-column: span 8 / span 8;" class="bk-cta-col-left">
                <div class="bk-mono" style="font-size:11px;font-weight:600;color:#F5B82E;letter-spacing:0.1em;text-transform:uppercase;margin-bottom:10px;">
                    ADMISI &amp; PENERIMAAN MAHASISWA BARU // PMB UNM
                </div>
                <h2 class="bk-serif" style="font-size:clamp(26px, 3.2vw, 38px);color:#ffffff;margin:0 0 12px 0;line-height:1.18;font-weight:500;letter-spacing:-0.02em;">
                    Bergabung Bersama Komunitas Akademik Bimbingan dan Konseling FIP UNM
                </h2>
                <p style="font-size:14px;color:rgba(255,255,255,0.82);line-height:1.65;margin:0 0 20px 0;max-width:640px;">
                    Pendaftaran mahasiswa baru tersedia melalui jalur Seleksi Nasional Berdasarkan Prestasi (SNBP), Seleksi Nasional Berdasarkan Tes (SNBT), Jalur Mandiri UNM untuk jenjang Sarjana (S1), serta Seleksi Pascasarjana untuk jenjang Magister (S2) dan Doktoral (S3).
                </p>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    <span class="bk-mono" style="font-size:10.5px;color:rgba(255,255,255,0.85);background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);padding:4px 10px;border-radius:4px;">JALUR SNBP</span>
                    <span class="bk-mono" style="font-size:10.5px;color:rgba(255,255,255,0.85);background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);padding:4px 10px;border-radius:4px;">JALUR UTBK-SNBT</span>
                    <span class="bk-mono" style="font-size:10.5px;color:rgba(255,255,255,0.85);background:rgba(255,255,255,0.08);border:1px solid rgba(255,255,255,0.14);padding:4px 10px;border-radius:4px;">MANDIRI UNM</span>
                    <span class="bk-mono" style="font-size:10.5px;color:#F5B82E;background:rgba(245,184,46,0.12);border:1px solid rgba(245,184,46,0.3);padding:4px 10px;border-radius:4px;">PASCASARJANA S2 &amp; S3</span>
                </div>
            </div>

            <div style="grid-column: span 4 / span 4;display:flex;flex-direction:column;gap:12px;" class="bk-cta-col-right">
                <a href="https://pmb.unm.ac.id" target="_blank" rel="noopener" class="bk-btn-primary" style="width:100%;box-sizing:border-box;">
                    <span>Portal Resmi PMB UNM</span>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M7 17L17 7"/><path d="M7 7h10v10"/></svg>
                </a>
                <a href="<?php echo esc_url(home_url('/layanan/akademik/')); ?>" class="bk-btn-outline-light" style="width:100%;box-sizing:border-box;">
                    <span>Panduan &amp; Layanan Akademik</span>
                </a>
                <div class="bk-mono" style="text-align:center;font-size:10.5px;color:rgba(255,255,255,0.6);margin-top:4px;">
                    Kampus FIP UNM Tamalate &middot; Makassar
                </div>
            </div>

        </div>
    </div>
</section>

<style>
@media (max-width: 1024px) {
    .bk-cta-col-left,
    .bk-cta-col-right {
        grid-column: span 12 / span 12 !important;
    }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Interactive Tabs for Program Studi Matrix (shadcn Tabs pattern)
    var tabButtons = document.querySelectorAll("[data-prodi-tab]");
    var prodiPanels = document.querySelectorAll("[data-prodi-panel]");
    var matrixContainer = document.getElementById("bk-prodi-matrix-view");

    if (tabButtons.length > 0 && prodiPanels.length > 0 && matrixContainer) {
        tabButtons.forEach(function(btn) {
            btn.addEventListener("click", function() {
                var target = this.getAttribute("data-prodi-tab");

                tabButtons.forEach(function(b) {
                    b.classList.remove("is-active");
                    b.setAttribute("aria-selected", "false");
                });
                this.classList.add("is-active");
                this.setAttribute("aria-selected", "true");

                if (target === "all") {
                    matrixContainer.style.gridTemplateColumns = "";
                    prodiPanels.forEach(function(panel) {
                        panel.style.display = "flex";
                    });
                } else {
                    matrixContainer.style.gridTemplateColumns = "1fr";
                    prodiPanels.forEach(function(panel) {
                        if (panel.getAttribute("data-prodi-panel") === target) {
                            panel.style.display = "flex";
                        } else {
                            panel.style.display = "none";
                        }
                    });
                }
            });
        });
    }
});
</script>


<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        var viewport = document.getElementById('bk-spotlight-viewport');
        if (!viewport) return;

        var allSlides = Array.from(viewport.querySelectorAll('.bk-spotlight-slide'));
        if (!allSlides || allSlides.length === 0) return;

        var prevBtn = document.getElementById('bk-spotlight-btn-prev');
        var nextBtn = document.getElementById('bk-spotlight-btn-next');
        var dotsContainer = document.getElementById('bk-spotlight-dots');
        var currentIdxEl = document.getElementById('bk-spotlight-current-idx');
        var totalIdxEl = document.getElementById('bk-spotlight-total-idx');
        var progressLine = document.getElementById('bk-spotlight-progress-line');
        var togglePlayBtn = document.getElementById('bk-spotlight-toggle-play');
        var modeTag = document.getElementById('bk-spotlight-mode-tag');

        var isAutoplay = viewport.getAttribute('data-autoplay') === '1';
        var standardDuration = Math.max(4, parseInt(viewport.getAttribute('data-speed'), 10) || 8) * 1000;
        var currentVisibleIndex = 0;
        var standardTimer = null;
        var standardStart = 0;
        var isManualPaused = false;
        var promptTimeout = null;

        function padZero(n) {
            return n < 10 ? '0' + n : '' + n;
        }

        // Get currently visible slides according to screen width (Desktop vs Mobile)
        function getVisibleSlides() {
            var isMobile = window.innerWidth <= 768;
            return allSlides.filter(function(s) {
                var target = s.getAttribute('data-device-target') || 'all';
                if (isMobile && target === 'desktop_only') return false;
                if (!isMobile && target === 'mobile_only') return false;
                return true;
            });
        }

        function refreshDots() {
            var visibleSlides = getVisibleSlides();
            if (totalIdxEl) {
                totalIdxEl.textContent = padZero(visibleSlides.length);
            }
            if (dotsContainer) {
                dotsContainer.innerHTML = '';
                visibleSlides.forEach(function(s, idx) {
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'bk-spotlight-dot-pill' + (idx === currentVisibleIndex ? ' active' : '');
                    btn.setAttribute('data-go-to', idx);
                    btn.setAttribute('aria-label', 'Menuju Slide ' + (idx + 1));
                    btn.style.width = (idx === currentVisibleIndex ? '30px' : '10px');
                    btn.style.height = '6px';
                    btn.style.borderRadius = '3px';
                    btn.style.backgroundColor = (idx === currentVisibleIndex ? '#F5B82E' : 'rgba(255,255,255,0.35)');
                    btn.style.border = 'none';
                    btn.style.padding = '0';
                    btn.style.cursor = 'pointer';
                    btn.style.transition = 'all 0.25s ease';
                    btn.addEventListener('click', function(e) {
                        e.preventDefault();
                        updateSlide(idx);
                    });
                    dotsContainer.appendChild(btn);
                });
            }
        }

        function hideAllPills() {
            clearTimeout(promptTimeout);
            document.querySelectorAll('.bk-spotlight-prompt-pill').forEach(function(pill) {
                pill.classList.remove('is-shown');
            });
        }

        var ytMutedMap = {};
        var ytPausedMap = {};

        function updateSlide(newVisibleIndex) {
            var visibleSlides = getVisibleSlides();
            if (visibleSlides.length === 0) return;

            if (newVisibleIndex < 0) newVisibleIndex = visibleSlides.length - 1;
            if (newVisibleIndex >= visibleSlides.length) newVisibleIndex = 0;

            currentVisibleIndex = newVisibleIndex;
            hideAllPills();

            clearInterval(standardTimer);
            if (progressLine) progressLine.style.width = '0%';

            var activeSlide = visibleSlides[currentVisibleIndex];
            var slideType = activeSlide.getAttribute('data-slide-type') || 'image';

            allSlides.forEach(function(s) {
                if (s === activeSlide) {
                    s.classList.add('is-active');
                    // Unpause active slide YouTube video if present
                    var actIfr = s.querySelector('iframe');
                    if (actIfr && actIfr.contentWindow) {
                        try {
                            actIfr.contentWindow.postMessage(JSON.stringify({
                                event: 'command',
                                func: 'playVideo',
                                args: []
                            }), '*');
                        } catch(e) {}
                    }
                } else {
                    s.classList.remove('is-active');
                    // 1. Native HTML5 video pause & rewind
                    var v = s.querySelector('video');
                    if (v) {
                        v.pause();
                        v.currentTime = 0;
                    }
                    // 2. YouTube / External IFrame pause & mute IMMEDIATELY (no audio leak)
                    var ifr = s.querySelector('iframe');
                    if (ifr && ifr.contentWindow) {
                        try {
                            ifr.contentWindow.postMessage(JSON.stringify({
                                event: 'command',
                                func: 'pauseVideo',
                                args: []
                            }), '*');
                            ifr.contentWindow.postMessage(JSON.stringify({
                                event: 'command',
                                func: 'mute',
                                args: []
                            }), '*');
                        } catch(e) {}
                    }
                    // 3. Reset audio toggle button state on inactive slide
                    var sIdx = s.getAttribute('data-slide-index');
                    if (sIdx !== null && typeof ytMutedMap !== 'undefined') {
                        ytMutedMap[sIdx] = true;
                    }
                    var aBtn = s.querySelector('.btn-toggle-audio');
                    if (aBtn) {
                        var aIcon = aBtn.querySelector('.audio-icon');
                        var aTxt = aBtn.querySelector('.audio-text');
                        if (aIcon) aIcon.textContent = '🔇';
                        if (aTxt) aTxt.textContent = 'Nyalakan Audio';
                    }
                }
            });

            if (currentIdxEl) {
                currentIdxEl.textContent = padZero(currentVisibleIndex + 1);
            }

            if (dotsContainer) {
                var dots = dotsContainer.querySelectorAll('.bk-spotlight-dot-pill');
                dots.forEach(function(d, dIdx) {
                    if (dIdx === currentVisibleIndex) {
                        d.classList.add('active');
                        d.style.width = '30px';
                        d.style.backgroundColor = '#F5B82E';
                    } else {
                        d.classList.remove('active');
                        d.style.width = '10px';
                        d.style.backgroundColor = 'rgba(255,255,255,0.35)';
                    }
                });
            }

            // ATURAN VIDEO: Selesaikan video terlebih dahulu sebelum lanjut
            function runStandardSlideTimer(dur) {
                clearInterval(standardTimer);
                var duration = dur || standardDuration;
                standardStart = Date.now();
                standardTimer = setInterval(function() {
                    var elapsed = Date.now() - standardStart;
                    var pct = Math.min(100, (elapsed / duration) * 100);
                    if (progressLine) {
                        progressLine.style.width = pct + '%';
                    }
                    if (elapsed >= duration) {
                        clearInterval(standardTimer);
                        standardTimer = null;
                        updateSlide(currentVisibleIndex + 1);
                    }
                }, 50);
            }

            // ATURAN VIDEO & ROTASI SLIDE
            if (slideType === 'video') {
                var video = activeSlide.querySelector('video');
                var iframe = activeSlide.querySelector('iframe');

                if (video) {
                    if (modeTag) {
                        modeTag.textContent = 'VIDEO MP4';
                        modeTag.style.display = 'inline';
                    }
                    video.onerror = function() {
                        console.warn('Video error, running standard slide timer.');
                        if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                            runStandardSlideTimer();
                        }
                    };
                    video.currentTime = 0;
                    var playPromise = video.play();
                    if (playPromise !== undefined) {
                        playPromise.catch(function() {
                            video.muted = true;
                            video.play().catch(function() {
                                if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                                    runStandardSlideTimer();
                                }
                            });
                        });
                    }

                    video.ontimeupdate = function() {
                        if (video.duration && progressLine) {
                            var pct = (video.currentTime / video.duration) * 100;
                            progressLine.style.width = pct + '%';
                        }
                    };

                    video.onended = function() {
                        if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                            updateSlide(currentVisibleIndex + 1);
                        }
                    };
                } else if (iframe) {
                    // YouTube / Google Drive Cloud Video Stream
                    if (modeTag) {
                        modeTag.textContent = 'STREAMING VIDEO';
                        modeTag.style.display = 'inline';
                    }
                    if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                        runStandardSlideTimer(15000); // 15 detik untuk preview streaming video
                    }
                } else {
                    if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                        runStandardSlideTimer();
                    }
                }
            } else {
                if (modeTag) {
                    modeTag.textContent = 'BANNER INFORMASI';
                    modeTag.style.display = 'inline';
                }
                if (isAutoplay && !isManualPaused && visibleSlides.length > 1) {
                    runStandardSlideTimer();
                }
            }
        }

        document.querySelectorAll('.bk-spotlight-click-shield').forEach(function(shield) {
            shield.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                var slide = this.closest('.bk-spotlight-slide');
                if (!slide) return;

                var pill = slide.querySelector('.bk-spotlight-prompt-pill');
                if (!pill) return;

                if (pill.classList.contains('is-shown')) {
                    var href = pill.getAttribute('data-href');
                    if (href) {
                        window.location.href = href;
                    }
                    return;
                }

                hideAllPills();
                pill.classList.add('is-shown');

                promptTimeout = setTimeout(function() {
                    pill.classList.remove('is-shown');
                }, 4500);
            });
        });

        document.querySelectorAll('.bk-spotlight-prompt-pill').forEach(function(pill) {
            pill.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var href = this.getAttribute('data-href');
                if (href) {
                    window.location.href = href;
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('.bk-spotlight-canvas-box')) {
                hideAllPills();
            }
        });

        if (prevBtn) {
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                updateSlide(currentVisibleIndex - 1);
            });
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                updateSlide(currentVisibleIndex + 1);
            });
        }

        if (togglePlayBtn) {
            togglePlayBtn.addEventListener('click', function(e) {
                e.preventDefault();
                isManualPaused = !isManualPaused;
                var icon = this.querySelector('.status-icon');
                var text = this.querySelector('.status-text');
                if (isManualPaused) {
                    clearInterval(standardTimer);
                    var curVisible = getVisibleSlides();
                    var act = curVisible[currentVisibleIndex];
                    if (act) {
                        var v = act.querySelector('video');
                        if (v) v.pause();
                    }
                    if (icon) icon.textContent = '▶';
                    if (text) text.textContent = 'JEDA';
                    this.style.borderColor = '#F5B82E';
                    this.style.color = '#F5B82E';
                } else {
                    if (icon) icon.textContent = '⏸';
                    if (text) text.textContent = 'AUTOPLAY';
                    this.style.borderColor = 'rgba(255,255,255,0.25)';
                    this.style.color = 'rgba(255,255,255,0.9)';
                    var curVis = getVisibleSlides();
                    var actSlide = curVis[currentVisibleIndex];
                    if (actSlide) {
                        var curV = actSlide.querySelector('video');
                        if (curV) curV.play();
                    }
                    updateSlide(currentVisibleIndex);
                }
            });
        }

        // yt maps hoisted to top

        // Audio Toggle: supports both native HTML5 <video> and YouTube <iframe> via postMessage
        document.querySelectorAll('.btn-toggle-audio').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var slide = this.closest('.bk-spotlight-slide');
                if (!slide) return;
                var slideIdx = slide.getAttribute('data-slide-index') || '0';
                var video = slide.querySelector('video');
                var iframe = slide.querySelector('iframe');
                var icon = this.querySelector('.audio-icon');
                var txt = this.querySelector('.audio-text');

                if (video) {
                    video.muted = !video.muted;
                    if (video.muted) {
                        if (icon) icon.textContent = '🔇';
                        if (txt) txt.textContent = 'Nyalakan Audio';
                    } else {
                        if (icon) icon.textContent = '🔊';
                        if (txt) txt.textContent = 'Matikan Suara';
                    }
                } else if (iframe && iframe.contentWindow) {
                    // YouTube / Vimeo cross-origin postMessage command
                    var isCurrentlyMuted = ytMutedMap[slideIdx] !== false; // default true (muted autoplay)
                    if (isCurrentlyMuted) {
                        // UNMUTE YOUTUBE
                        iframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'unMute',
                            args: []
                        }), '*');
                        iframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'setVolume',
                            args: [100]
                        }), '*');
                        ytMutedMap[slideIdx] = false;
                        if (icon) icon.textContent = '🔊';
                        if (txt) txt.textContent = 'Matikan Suara';
                    } else {
                        // MUTE YOUTUBE
                        iframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'mute',
                            args: []
                        }), '*');
                        ytMutedMap[slideIdx] = true;
                        if (icon) icon.textContent = '🔇';
                        if (txt) txt.textContent = 'Nyalakan Audio';
                    }
                }
            });
        });

        // Play/Pause Toggle: supports both native HTML5 <video> and YouTube <iframe> via postMessage
        document.querySelectorAll('.btn-toggle-play').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                var slide = this.closest('.bk-spotlight-slide');
                if (!slide) return;
                var slideIdx = slide.getAttribute('data-slide-index') || '0';
                var video = slide.querySelector('video');
                var iframe = slide.querySelector('iframe');
                var icon = this.querySelector('.play-icon');
                var txt = this.querySelector('.play-text');

                if (video) {
                    if (video.paused) {
                        video.play();
                        if (icon) icon.textContent = '⏸';
                        if (txt) txt.textContent = 'Jeda';
                    } else {
                        video.pause();
                        if (icon) icon.textContent = '▶';
                        if (txt) txt.textContent = 'Putar';
                    }
                } else if (iframe && iframe.contentWindow) {
                    var isPaused = ytPausedMap[slideIdx] === true;
                    if (isPaused) {
                        iframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'playVideo',
                            args: []
                        }), '*');
                        ytPausedMap[slideIdx] = false;
                        if (icon) icon.textContent = '⏸';
                        if (txt) txt.textContent = 'Jeda';
                    } else {
                        iframe.contentWindow.postMessage(JSON.stringify({
                            event: 'command',
                            func: 'pauseVideo',
                            args: []
                        }), '*');
                        ytPausedMap[slideIdx] = true;
                        if (icon) icon.textContent = '▶';
                        if (txt) txt.textContent = 'Putar';
                    }
                }
            });
        });

        // Handle window resize dynamically between desktop and mobile views
        var resizeTimer = null;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                refreshDots();
                updateSlide(0);
            }, 250);
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            var rect = viewport.getBoundingClientRect();
            if (rect.bottom > 0 && rect.top < window.innerHeight) {
                if (e.key === 'ArrowLeft') {
                    updateSlide(currentVisibleIndex - 1);
                } else if (e.key === 'ArrowRight') {
                    updateSlide(currentVisibleIndex + 1);
                }
            }
        });

        // Mobile touch swipe
        var touchStartX = 0;
        var touchEndX = 0;
        viewport.addEventListener('touchstart', function(e) {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        viewport.addEventListener('touchend', function(e) {
            touchEndX = e.changedTouches[0].screenX;
            var diff = touchStartX - touchEndX;
            if (Math.abs(diff) > 50) {
                if (diff > 0) {
                    updateSlide(currentVisibleIndex + 1);
                } else {
                    updateSlide(currentVisibleIndex - 1);
                }
            }
        }, { passive: true });

        // Initial setup
        refreshDots();
        updateSlide(0);
    });
})();
</script>

</main>

<?php
get_footer();
