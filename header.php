<!DOCTYPE html>
<html <?php language_attributes(); ?> class="scroll-smooth">
<head>
    <!-- BK_THEME_VER: 2.1.2_20261028_URLFIX -->
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php wp_head(); ?>
        <style>
    /* CSS VARIABLES: NAVBAR EDGE ALIGNMENT, TOP CLEARANCE & ADMIN BAR INTEGRATION */
    :root {
        --bk-edge-pad: 1rem;
        --bk-navbar-height: 56px;
        --bk-admin-bar-offset: 0px;
        --bk-top-clearance: 16px;
    }
    @media (min-width: 640px) {
        :root {
            --bk-edge-pad: 2rem;
            --bk-navbar-height: 64px;
            --bk-top-clearance: 20px;
        }
    }
    @media (min-width: 1024px) {
        :root {
            --bk-edge-pad: 3rem;
        }
    }
    body.admin-bar {
        --bk-admin-bar-offset: 32px;
    }
    @media screen and (max-width: 782px) {
        body.admin-bar {
            --bk-admin-bar-offset: 46px;
        }
    }

    /* WordPress Top Admin Bar Protection (Never distorts or shifts page layout) */
    #wpadminbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 32px !important;
        min-height: 32px !important;
        max-height: 32px !important;
        background: #1d2327 !important;
        color: #c3c4c7 !important;
        z-index: 999999 !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    @media screen and (max-width: 782px) {
        #wpadminbar {
            height: 46px !important;
            min-height: 46px !important;
            max-height: 46px !important;
        }
    }

    /* Fixed Navbar & Spacer Positioning (Immune to Tailwind CDN late injection) */
    #bk-main-navbar,
    nav.fixed {
        position: fixed !important;
        top: 0px !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        z-index: 99990 !important;
    }
    body.admin-bar #bk-main-navbar,
    body.admin-bar nav.fixed {
        top: 32px !important;
    }
    @media screen and (max-width: 782px) {
        body.admin-bar #bk-main-navbar,
        body.admin-bar nav.fixed {
            top: 46px !important;
        }
    }

    /* Dedicated Flow Spacer Below Fixed Navbar (100% prevents navbar overlap on all pages) */
    #bk-navbar-spacer {
        width: 100% !important;
        height: 64px !important;
        background-color: #0D3B22 !important;
        display: block !important;
        margin: 0 !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
    }
    body.admin-bar #bk-navbar-spacer {
        height: 112px !important;
    }
    @media (min-width: 640px) {
        #bk-navbar-spacer {
            height: 88px !important;
        }
        body.admin-bar #bk-navbar-spacer {
            height: 120px !important;
        }
    }
    @media screen and (min-width: 640px) and (max-width: 782px) {
        body.admin-bar #bk-navbar-spacer {
            height: 134px !important;
        }
    }
    @media screen and (max-width: 639px) {
        body.admin-bar #bk-navbar-spacer {
            height: 126px !important;
        }
    }

    /* UNIFIED CONTAINER GRID: Exact Left & Right Alignment with Navbar (px-4 sm:px-8 lg:px-12) */
    
    /* Prevent nested container double padding */
    .bk-container .bk-container {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .bk-container,
    .bk-content-wrap {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, 1rem) !important;
        padding-right: var(--bk-edge-pad, 1rem) !important;
        box-sizing: border-box !important;
    }

    /* Subpage Sticky Sub-Navigation Bar Alignment */
    .bk-subnav-sticky-wrap {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: var(--bk-edge-pad, 1rem) !important;
        padding-right: var(--bk-edge-pad, 1rem) !important;
        box-sizing: border-box !important;
    }
    .bk-subnav-sticky-wrap > div {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }

    /* Subpage Section & Main Body Alignment */
    .bk-page-wrapper > main,
    main.bk-subpage-main {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, 1rem) !important;
        padding-right: var(--bk-edge-pad, 1rem) !important;
        padding-bottom: 80px !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:first-of-type {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }
    .bk-page-wrapper > section:first-of-type > div:not([style*="position:absolute"]):not([style*="position: absolute"]):not(.orb-1):not(.orb-2):not(.dot-grid) {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        padding-left: var(--bk-edge-pad, 1rem) !important;
        padding-right: var(--bk-edge-pad, 1rem) !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:not(:first-of-type) {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: var(--bk-edge-pad, 1rem) !important;
        padding-right: var(--bk-edge-pad, 1rem) !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section:not(:first-of-type) > div {
        width: 100% !important;
        max-width: 100% !important;
        margin-left: auto !important;
        margin-right: auto !important;
        box-sizing: border-box !important;
    }

    /* Shortcode Wrapper Alignment (Avoid double padding when inside bk-content-wrap) */
    .bk-content-wrap .ppb-jadwal-wrapper,
    .bk-content-wrap .ppb-prestasi-wrapper,
    .bk-content-wrap .ppb-karya-wrapper,
    .bk-content-wrap .ppb-tracer-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    /* UNIVERSAL TOP CLEARANCE BELOW NAVBAR SPACER */
    .bk-hero-kemahasiswaan,
    .bk-section-hero_section,
    .bk-page-wrapper > section:first-of-type,
    .bk-subpage-container,
    main > section:first-of-type,
    main.content-area > section:first-of-type {
        padding-top: var(--bk-top-clearance, 36px) !important;
    }

    /* CRITICAL DROPDOWN & FULL-WIDTH PROTECTION */
    html, body {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow-x: hidden !important;
    }
    main:not(.bk-container):not(.bk-subpage-main), .site-main, .content-area, #primary, #content, .bk-page-wrapper {
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        box-sizing: border-box !important;
    }
    .bk-page-wrapper > section {
        width: 100% !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
    }
    
    /* Navbar Button Styles & Smooth Hover Effects */
    .bk-nav-btn {
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        text-decoration: none !important;
        display: inline-flex !important;
        align-items: center !important;
        white-space: nowrap !important;
        border-radius: 9999px !important;
        padding: 5px 9px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        color: rgba(255, 255, 255, 0.9) !important;
        border: 1px solid transparent !important;
        background: transparent !important;
        cursor: pointer !important;
        flex-shrink: 0 !important;
    }
    @media (min-width: 1280px) {
        .bk-nav-btn {
            padding: 6px 12px !important;
            font-size: 13px !important;
        }
    }
    .bk-nav-btn:hover {
        background: rgba(255, 255, 255, 0.15) !important;
        color: #F5B82E !important;
        border-color: rgba(245, 184, 46, 0.4) !important;
        transform: translateY(-1px);
    }
    .bk-nav-btn.is-active-btn {
        background: #F5B82E !important;
        color: #0D3B22 !important;
        font-weight: 700 !important;
        border: 1px solid #F5B82E !important;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2) !important;
    }
    .bk-nav-btn.is-active-btn:hover {
        background: #fcd34d !important;
        color: #0D3B22 !important;
        box-shadow: 0 4px 12px rgba(245, 184, 46, 0.35) !important;
    }
    .bk-nav-btn.is-active-dropdown {
        background: rgba(255, 255, 255, 0.14) !important;
        color: #F5B82E !important;
        border: 1px solid rgba(245, 184, 46, 0.45) !important;
        font-weight: 700 !important;
    }
    .bk-nav-btn.is-active-dropdown:hover {
        background: rgba(255, 255, 255, 0.22) !important;
        color: #F5B82E !important;
        border-color: rgba(245, 184, 46, 0.6) !important;
    }

    /* =========================================================================
       BK MEGA MENU & DROPDOWN STYLES (ACADEMIC EDITORIAL AESTHETIC)
       ========================================================================= */
    .bk-nav-item-wrap {
        position: relative !important;
    }

    /* Pill Badge on Navbar Trigger Button (e.g. OBE 2025, Unggul) */
    .bk-nav-pill-badge {
        background: rgba(245, 184, 46, 0.22) !important;
        color: #F5B82E !important;
        border: 1px solid rgba(245, 184, 46, 0.45) !important;
        font-size: 9px !important;
        font-weight: 700 !important;
        padding: 1px 5px !important;
        border-radius: 9999px !important;
        letter-spacing: 0.03em !important;
        line-height: 1.2 !important;
        margin-left: 2px !important;
        flex-shrink: 0 !important;
    }

    /* Fallback Standard Dropdown Menu */
    .dropdown-menu {
        background-color: #ffffff !important;
        border-radius: 18px !important;
        box-shadow: 0 20px 45px -10px rgba(13, 59, 34, 0.22), 0 0 0 1px rgba(0, 0, 0, 0.06) !important;
        border: 1px solid rgba(212, 227, 216, 0.9) !important;
        padding: 6px !important;
        min-width: 240px !important;
    }
    .dropdown-menu a.bk-dropdown-link {
        display: flex !important;
        align-items: center !important;
        padding: 8px 12px !important;
        border-radius: 10px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #374151 !important;
        background-color: transparent !important;
        text-decoration: none !important;
        line-height: 1.25 !important;
        position: relative !important;
        transition: all 0.15s ease !important;
    }
    .dropdown-menu a.bk-dropdown-link:hover {
        background-color: #FAF8F5 !important;
        color: #0D3B22 !important;
    }
    .dropdown-menu a.bk-dropdown-link.is-active {
        background-color: #E8F0EA !important;
        color: #0D3B22 !important;
        font-weight: 700 !important;
    }
    .dropdown-menu a.bk-dropdown-link .bk-dropdown-indicator {
        width: 4px !important;
        height: 12px !important;
        border-radius: 2px !important;
        margin-right: 8px !important;
        display: inline-block !important;
        flex-shrink: 0 !important;
        background-color: transparent !important;
    }
    .dropdown-menu a.bk-dropdown-link.is-active .bk-dropdown-indicator {
        background-color: #0D3B22 !important;
    }
    .dropdown-menu a::before,
    .dropdown-menu a::after {
        content: none !important;
        display: none !important;
    }

    /* =========================================================================
       MODERN MEGA MENU DROPDOWN PANEL
       ========================================================================= */
    .bk-mega-dropdown {
        display: none !important;
        position: absolute !important;
        top: 100% !important;
        margin-top: 6px !important;
        background-color: #ffffff !important;
        border-radius: 20px !important;
        box-shadow: 0 25px 50px -12px rgba(13, 59, 34, 0.25), 0 0 0 1px rgba(0, 0, 0, 0.06) !important;
        border: 1px solid rgba(212, 227, 216, 0.95) !important;
        overflow: hidden !important;
        z-index: 99999 !important;
        text-align: left !important;
        color: #1f2937 !important;
        box-sizing: border-box !important;
        padding: 0 !important;
        max-width: calc(100vw - 32px) !important;
    }

    /* Alignment Modes */
    .bk-mega-dropdown.align-left {
        left: 0 !important;
        right: auto !important;
    }
    .bk-mega-dropdown.align-right {
        right: 0 !important;
        left: auto !important;
    }
    .bk-mega-dropdown.align-center {
        left: 50% !important;
        right: auto !important;
        transform: translateX(-50%) !important;
    }

    /* Dimension Presets Based on Columns */
    .bk-mega-dropdown.mega-cols-3 {
        width: 760px !important;
        max-width: min(800px, calc(100vw - 40px)) !important;
    }
    .bk-mega-dropdown.mega-cols-2 {
        width: 580px !important;
        max-width: min(620px, calc(100vw - 40px)) !important;
    }
    .bk-mega-dropdown.mega-cols-1 {
        width: 320px !important;
        max-width: min(340px, calc(100vw - 40px)) !important;
    }
    .bk-mega-dropdown.mega-cols-4 {
        width: 880px !important;
        max-width: min(940px, calc(100vw - 40px)) !important;
    }

    /* Hover & Focus Interaction */
    .dropdown-container:hover > .bk-mega-dropdown,
    .group:hover > .bk-mega-dropdown,
    .dropdown-container:focus-within > .bk-mega-dropdown,
    .bk-mega-dropdown:hover {
        display: block !important;
    }

    /* Invisible Bridge between button and mega menu */
    .bk-mega-dropdown::before {
        content: "" !important;
        display: block !important;
        position: absolute !important;
        top: -14px !important;
        left: 0 !important;
        right: 0 !important;
        height: 16px !important;
        background: transparent !important;
    }

    /* Mega Menu Header Bar */
    .bk-mega-header {
        background: #FAF8F5 !important;
        border-bottom: 1px solid #E8F0EA !important;
        padding: 12px 20px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
    }
    .bk-mega-header-icon {
        width: 32px !important;
        height: 32px !important;
        border-radius: 9px !important;
        background: #E8F0EA !important;
        color: #0D3B22 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
    }
    .bk-mega-header-title {
        font-family: 'Fraunces', Georgia, serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #0D3B22 !important;
        line-height: 1.2 !important;
    }
    .bk-mega-header-sub {
        font-size: 11px !important;
        color: #64748b !important;
        margin-top: 1px !important;
        line-height: 1.25 !important;
    }
    .bk-mega-badge-pill {
        background: #FEF3C7 !important;
        color: #92400e !important;
        border: 1px solid #FDE68A !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        padding: 2px 8px !important;
        border-radius: 9999px !important;
        letter-spacing: 0.03em !important;
        text-transform: uppercase !important;
        white-space: nowrap !important;
    }

    /* Mega Menu Grid Body */
    .bk-mega-body {
        display: grid !important;
        grid-template-columns: repeat(var(--mega-cols, 3), minmax(0, 1fr)) !important;
        gap: 16px 20px !important;
        padding: 16px 20px !important;
    }
    .bk-mega-col {
        display: flex !important;
        flex-direction: column !important;
    }
    .bk-mega-col-heading {
        font-size: 10.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        color: #185333 !important;
        margin-bottom: 6px !important;
        padding-bottom: 4px !important;
        border-bottom: 1.5px solid #E8F0EA !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
    }
    .bk-mega-col-list {
        display: flex !important;
        flex-direction: column !important;
        gap: 2px !important;
    }

    /* Mega Menu Links */
    .bk-mega-link {
        display: flex !important;
        align-items: flex-start !important;
        gap: 8px !important;
        padding: 6px 8px !important;
        border-radius: 10px !important;
        text-decoration: none !important;
        transition: all 0.15s ease !important;
        background-color: transparent !important;
        position: relative !important;
        line-height: 1.25 !important;
    }
    .bk-mega-link:hover {
        background-color: #FAF8F5 !important;
        transform: translateX(3px) !important;
    }
    .bk-mega-link.is-active {
        background-color: #E8F0EA !important;
    }
    .bk-mega-link-icon {
        width: 24px !important;
        height: 24px !important;
        min-width: 24px !important;
        border-radius: 6px !important;
        background-color: #F1F5F2 !important;
        color: #0D3B22 !important;
        display: flex !important;
        align-items: center !important;
        justify-content: center !important;
        flex-shrink: 0 !important;
        margin-top: 1px !important;
        transition: all 0.15s ease !important;
    }
    .bk-mega-link:hover .bk-mega-link-icon,
    .bk-mega-link.is-active .bk-mega-link-icon {
        background-color: #0D3B22 !important;
        color: #F5B82E !important;
    }
    .bk-mega-link-text {
        flex: 1 1 auto !important;
        min-width: 0 !important;
    }
    .bk-mega-link-title {
        font-size: 12.5px !important;
        font-weight: 600 !important;
        color: #1f2937 !important;
        line-height: 1.3 !important;
        display: flex !important;
        align-items: center !important;
        gap: 6px !important;
        flex-wrap: wrap !important;
    }
    .bk-mega-link:hover .bk-mega-link-title {
        color: #0D3B22 !important;
    }
    .bk-mega-link.is-active .bk-mega-link-title {
        color: #0D3B22 !important;
        font-weight: 700 !important;
    }
    .bk-mega-link-sub {
        font-size: 10.5px !important;
        font-weight: 400 !important;
        color: #64748b !important;
        line-height: 1.25 !important;
        margin-top: 2px !important;
        display: -webkit-box !important;
        -webkit-line-clamp: 1 !important;
        -webkit-box-orient: vertical !important;
        overflow: hidden !important;
    }
    .bk-mega-item-badge {
        display: inline-block !important;
        font-size: 9px !important;
        font-weight: 700 !important;
        padding: 1px 5px !important;
        border-radius: 4px !important;
        background: rgba(245, 184, 46, 0.2) !important;
        color: #92400e !important;
        border: 1px solid rgba(245, 184, 46, 0.4) !important;
        line-height: 1.2 !important;
    }

    /* Spotlight Footer Bar */
    .bk-mega-spotlight {
        background: linear-gradient(135deg, #0D3B22 0%, #185333 100%) !important;
        color: #ffffff !important;
        padding: 10px 18px !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        gap: 12px !important;
    }
    .bk-mega-spotlight-badge {
        display: inline-block !important;
        background: rgba(245, 184, 46, 0.22) !important;
        color: #F5B82E !important;
        border: 1px solid rgba(245, 184, 46, 0.45) !important;
        font-size: 9.5px !important;
        font-weight: 700 !important;
        padding: 2px 7px !important;
        border-radius: 4px !important;
        text-transform: uppercase !important;
        letter-spacing: 0.04em !important;
        flex-shrink: 0 !important;
    }
    .bk-mega-spotlight-btn {
        display: inline-flex !important;
        align-items: center !important;
        gap: 4px !important;
        background: #F5B82E !important;
        color: #0D3B22 !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        padding: 5px 12px !important;
        border-radius: 6px !important;
        text-decoration: none !important;
        white-space: nowrap !important;
        transition: all 0.15s ease !important;
        flex-shrink: 0 !important;
    }
    .bk-mega-spotlight-btn:hover {
        background: #fcd34d !important;
        transform: translateY(-1px) !important;
    }
    
    /* BULLETPROOF NAVBAR & DROPDOWN DISPLAY RULES (Immune to CDN delays or offline states) */
    /* =========================================================================
       BULLETPROOF RESPONSIVE NAVIGATION DISPLAY & HOVER RULES
       ========================================================================= */

    /* 1. Responsive Navigation Bar: Switch at 1024px for 9 Menu Items */
    @media (max-width: 1023px) {
        #bk-desktop-nav,
        .bk-desktop-nav {
            display: none !important;
        }
        #mobile-nav-toggle {
            display: inline-flex !important;
        }
        #mobile-nav-menu {
            width: 100% !important;
            background-color: #0D3B22 !important;
            border-bottom: 2px solid rgba(245, 184, 46, 0.4) !important;
            box-shadow: 0 16px 36px rgba(0, 0, 0, 0.45) !important;
            max-height: calc(100vh - 80px) !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box !important;
            z-index: 99995 !important;
        }
        #mobile-nav-menu.is-open {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        #mobile-nav-menu:not(.is-open) {
            display: none !important;
        }
    }

    @media (min-width: 1024px) {
        #bk-desktop-nav,
        .bk-desktop-nav {
            display: flex !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            flex-shrink: 0 !important;
            gap: 4px !important;
        }
        #mobile-nav-toggle {
            display: none !important;
        }
        #mobile-nav-menu {
            display: none !important;
        }
        .md\:hidden {
            display: none !important;
        }
        .md\:flex {
            display: flex !important;
        }
    }
    @media (min-width: 1280px) {
        #bk-desktop-nav,
        .bk-desktop-nav {
            gap: 6px !important;
        }
    }

    /* 2. Desktop Dropdown Menus (Bulletproof Pure CSS Hover) */
    .dropdown-container {
        position: relative;
    }
    .dropdown-menu {
        display: none !important;
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        z-index: 99999 !important;
    }
    .dropdown-container:hover > .dropdown-menu,
    .group:hover > .dropdown-menu,
    .dropdown-menu:hover {
        display: block !important;
    }
    /* Invisible hover bridge to prevent losing hover when cursor travels between button and dropdown */
    .dropdown-menu::before {
        content: "" !important;
        display: block !important;
        position: absolute !important;
        top: -12px !important;
        left: 0 !important;
        right: 0 !important;
        height: 14px !important;
        background: transparent !important;
    }

    /* 3. Mobile Hamburger Toggle Button */
    #mobile-nav-toggle {
        display: none;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        min-width: 44px;
        min-height: 44px;
        padding: 0;
        background: transparent;
        border: none;
        cursor: pointer;
        color: #ffffff;
        border-radius: 10px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: transparent;
        z-index: 99999;
    }
    #mobile-nav-toggle:hover,
    #mobile-nav-toggle:focus {
        background-color: rgba(255, 255, 255, 0.12);
    }

    /* 4. Mobile Submenu Accordions */
    .mobile-acc-content {
        display: none !important;
    }
    .mobile-acc-content.is-open {
        display: block !important;
    }
    .mobile-acc-btn svg {
        transition: transform 0.25s ease !important;
    }
    .mobile-acc-btn.is-open svg {
        transform: rotate(180deg) !important;
    }
    </style>
</head>
<body <?php body_class('min-h-screen bg-[#FAF8F5] text-gray-900 selection:bg-[#F5B82E] selection:text-[#0D3B22]'); ?>>
<?php wp_body_open(); ?>

<?php
// =============================================================================
// Active Page & Navigation Detection (Bulletproof Path Matching)
// =============================================================================
$req_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
$req_path = parse_url($req_uri, PHP_URL_PATH) ?? '';
$home_path = parse_url(home_url(), PHP_URL_PATH) ?? '';
if (!empty($home_path) && strpos($req_path, $home_path) === 0) {
    $req_path = substr($req_path, strlen($home_path));
}
// Normalize WP-Staging /yamato subfolder prefix
if (strpos($req_path, '/yamato/') === 0) {
    $req_path = substr($req_path, 7);
} elseif ($req_path === '/yamato') {
    $req_path = '/';
}
$current_path = trim($req_path, '/');

// Is Front Page
$is_home = is_front_page() || is_home() || empty($current_path) || $current_path === 'beranda';

// Helper function to check if a navigation URL matches the current path
if (!function_exists('ppb_nav_is_active')) {
    function ppb_nav_is_active($url, $current_path, $is_home) {
        if (empty($url)) return false;
        
        $clean_url = trim($url);
        // An item with '#' or empty as URL is a placeholder parent item, NEVER directly active
        if ($clean_url === '#' || strpos($clean_url, '#') === 0) {
            return false;
        }

        $m_path = parse_url($clean_url, PHP_URL_PATH) ?? '';
        $home_path = parse_url(home_url(), PHP_URL_PATH) ?? '';
        if (!empty($home_path) && strpos($m_path, $home_path) === 0) {
            $m_path = substr($m_path, strlen($home_path));
        }
        if (strpos($m_path, '/yamato/') === 0) {
            $m_path = substr($m_path, 7);
        } elseif ($m_path === '/yamato') {
            $m_path = '/';
        }
        $m_path = trim($m_path, '/');

        if ($is_home) {
            $clean_item_url = rtrim(preg_replace('/^https?:/', '', $clean_url), '/');
            $clean_home_url = rtrim(preg_replace('/^https?:/', '', home_url()), '/');
            return ($clean_item_url === $clean_home_url || $m_path === 'beranda');
        }

        // On non-homepage:
        if (empty($m_path) || $m_path === 'beranda') {
            return false;
        }

        return ($m_path === $current_path || strpos($current_path, $m_path . '/') === 0 || strpos($m_path, $current_path . '/') === 0);
    }
}

// Section Active Checks (for Fallback Menus)
$is_profil = (!$is_home && (strpos($current_path, 'profil') === 0 || in_array($current_path, array('sejarah', 'visi-misi', 'visi-keilmuan', 'struktur', 'kerjasama-tridharma', 'dosen', 'buku-dosen', 'kepakaran-dosen', 'sarana-prasarana', 'roadmap', 'dokumen'))));
$is_akademik = (!$is_home && (strpos($current_path, 'akademik') === 0 || in_array($current_path, array('kurikulum', 'rps-sap', 'panduan', 'jadwal-ujian', 'statistik', 'jurnal-bk'))));
$is_kemahasiswaan = (!$is_home && (strpos($current_path, 'kemahasiswaan') === 0 || in_array($current_path, array('penerimaan', 'prestasi', 'mbkm-kemanusiaan', 'karya', 'alumni'))));
$is_lab = (!$is_home && (strpos($current_path, 'lab') === 0 || in_array($current_path, array('laboratorium', 'lab-bk'))));
$is_riset = (!$is_home && (strpos($current_path, 'mis-pnbp') === 0 || strpos($current_path, 'riset') === 0 || in_array($current_path, array('penelitian', 'pkm'))));
$is_akreditasi = (!$is_home && (strpos($current_path, 'akreditasi') === 0 || strpos($current_path, 'kriteria') !== false || in_array($current_path, array('upm', 'penjaminan-mutu'))));
$is_elib_active = (!$is_home && (strpos($current_path, 'library') !== false || is_page('e-library') || is_page('elibrary') || (function_exists('is_page_template') && (is_page_template('page-elibrary.php') || is_page_template('page-e-library.php')))));
$is_layanan = (!$is_home && (strpos($current_path, 'layanan') === 0 || strpos($current_path, 'survei') === 0 || strpos($current_path, 'persuratan') !== false || strpos($current_path, 'perlindungan-mahasiswa') !== false || strpos($current_path, 'e-library') !== false || strpos($current_path, 'elibrary') !== false || $is_elib_active));
$is_berita = (!$is_home && (strpos($current_path, 'berita') === 0 || is_singular('post') || is_category()));

// Dropdown Menu Datasets (Fallback Bawaan)
$profil_menu = array(
    array('url' => home_url('/profil/'), 'path' => 'profil', 'label' => 'Ringkasan Profil'),
    array('url' => home_url('/profil/sejarah/'), 'path' => 'profil/sejarah', 'label' => 'Sejarah Program Studi'),
    array('url' => home_url('/profil/visi-misi/'), 'path' => 'profil/visi-misi', 'label' => 'Visi, Misi & Sasaran'),
    array('url' => home_url('/profil/visi-keilmuan/'), 'path' => 'profil/visi-keilmuan', 'label' => 'Visi Keilmuan & Bagan Kepakaran'),
    array('url' => home_url('/profil/struktur/'), 'path' => 'profil/struktur', 'label' => 'Struktur Organisasi'),
    array('url' => home_url('/profil/kerjasama-tridharma/'), 'path' => 'profil/kerjasama-tridharma', 'label' => 'Kerjasama Tridharma (Pendidikan/Riset/PkM)'),
    array('url' => home_url('/profil/dosen/'), 'path' => 'profil/dosen', 'label' => 'Dosen & Staf Pengajar'),
    array('url' => home_url('/profil/buku-dosen/'), 'path' => 'profil/buku-dosen', 'label' => 'Koleksi Buku Dosen BK'),
    array('url' => home_url('/profil/kepakaran-dosen/'), 'path' => 'profil/kepakaran-dosen', 'label' => 'Peta Kepakaran, SINTA & HKI'),
    array('url' => home_url('/profil/sarana-prasarana/'), 'path' => 'profil/sarana-prasarana', 'label' => 'Sarana & Prasarana (Ruang Kelas/Lab/Konseling)'),
    array('url' => home_url('/profil/roadmap/'), 'path' => 'profil/roadmap', 'label' => 'Roadmap Riset & PKM'),
    array('url' => home_url('/profil/dokumen/'), 'path' => 'profil/dokumen', 'label' => 'Dokumen Mutu & Regulasi'),
);

$akademik_menu = array(
    array('url' => home_url('/akademik/'), 'path' => 'akademik', 'label' => 'Ringkasan Akademik'),
    array('url' => home_url('/akademik/kurikulum/'), 'path' => 'akademik/kurikulum', 'label' => 'Kurikulum OBE 2025'),
    array('url' => home_url('/akademik/rps-sap/'), 'path' => 'akademik/rps-sap', 'label' => 'RPS Lengkap, SAP & Rubrik OBE'),
    array('url' => home_url('/akademik/panduan/'), 'path' => 'akademik/panduan', 'label' => 'Pedoman Akademik & Penilaian'),
    array('url' => home_url('/akademik/jadwal-ujian/'), 'path' => 'akademik/jadwal-ujian', 'label' => 'Jadwal Perkuliahan & Ujian'),
    array('url' => home_url('/akademik/statistik/'), 'path' => 'akademik/statistik', 'label' => 'Data & Statistik Akademik'),
    array('url' => home_url('/jurnal-bk/'), 'path' => 'jurnal-bk', 'label' => 'Jurnal & Pengabdian Prodi BK'),
);

$kemahasiswaan_menu = array(
    array('url' => home_url('/kemahasiswaan/'), 'path' => 'kemahasiswaan', 'label' => 'Ringkasan Kemahasiswaan'),
    array('url' => home_url('/kemahasiswaan/penerimaan/'), 'path' => 'kemahasiswaan/penerimaan', 'label' => 'Penerimaan Maba (Peminat & Daya Tampung)'),
    array('url' => home_url('/kemahasiswaan/prestasi/'), 'path' => 'kemahasiswaan/prestasi', 'label' => 'Prestasi & Riset Non-Akademik'),
    array('url' => home_url('/kemahasiswaan/mbkm-kemanusiaan/'), 'path' => 'kemahasiswaan/mbkm-kemanusiaan', 'label' => 'Pameran Layanan MBKM & Proyek Kemanusiaan'),
    array('url' => home_url('/kemahasiswaan/karya/'), 'path' => 'kemahasiswaan/karya', 'label' => 'Karya & Inovasi Mahasiswa'),
    array('url' => home_url('/kemahasiswaan/alumni/'), 'path' => 'kemahasiswaan/alumni', 'label' => 'Direktori Alumni & Tracer Study'),
    array('url' => home_url('/kemahasiswaan/statistik/'), 'path' => 'kemahasiswaan/statistik', 'label' => 'Data & Statistik Mahasiswa'),
);

$lab_menu = array(
    array('url' => home_url('/laboratorium/'), 'path' => 'laboratorium', 'label' => 'Ringkasan Laboratorium BK'),
    array('url' => home_url('/laboratorium/kegiatan/'), 'path' => 'laboratorium/kegiatan', 'label' => 'Kegiatan Mahasiswa Lab BK'),
    array('url' => home_url('/laboratorium/lomba/'), 'path' => 'laboratorium/lomba', 'label' => 'Lomba Non-Akademik Lab'),
    array('url' => home_url('/lab-bk/inventaris/'), 'path' => 'lab-bk/inventaris', 'label' => 'Inventaris Alat Tes Lab BK'),
    array('url' => 'https://bk-connect-fipunm.vercel.app/', 'path' => 'bk-connect', 'label' => 'Portal BK Connect Suite &rarr;'),
    array('url' => 'https://peersupport-bk.unm.ac.id/', 'path' => 'peersupport', 'label' => 'Portal Peersupport BK &rarr;'),
);

$riset_menu = array(
    array('url' => home_url('/mis-pnbp/'), 'path' => 'mis-pnbp', 'label' => 'Pusat MIS PNBP'),
    array('url' => home_url('/mis-pnbp/penelitian/'), 'path' => 'mis-pnbp/penelitian', 'label' => 'Hibah Penelitian PNBP'),
    array('url' => home_url('/mis-pnbp/pkm/'), 'path' => 'mis-pnbp/pkm', 'label' => 'Pengabdian kepada Masyarakat (PkM) PNBP'),
    array('url' => home_url('/jurnal-bk/'), 'path' => 'jurnal-bk', 'label' => 'Publikasi Jurnal & Desiminasi Riset'),
);

$layanan_menu = array(
    array('url' => home_url('/layanan/'), 'path' => 'layanan', 'label' => 'Ringkasan Layanan'),
    array('url' => home_url('/layanan/persuratan/'), 'path' => 'layanan/persuratan', 'label' => 'Layanan Persuratan & Kode Nomor Surat'),
    array('url' => home_url('/e-library/'), 'path' => 'e-library', 'label' => 'E-Library & Modul'),
    array('url' => home_url('/layanan/akademik/'), 'path' => 'layanan/akademik', 'label' => 'Panduan Prosedur & Template'),
    array('url' => home_url('/layanan/perlindungan-mahasiswa/'), 'path' => 'layanan/perlindungan-mahasiswa', 'label' => 'Perlindungan Mahasiswa (No. 13)'),
    array('url' => home_url('/survei/'), 'path' => 'survei', 'label' => 'Survei Kepuasan'),
);

$akreditasi_menu = array(
    array('url' => home_url('/akreditasi/'), 'path' => 'akreditasi', 'label' => 'Instrumen Hub 9 Kriteria LAMDIK'),
    array('url' => home_url('/akreditasi/sertifikat/'), 'path' => 'akreditasi/sertifikat', 'label' => 'Sertifikat Akreditasi S1, S2, S3'),
    array('url' => home_url('/akreditasi/upm/'), 'path' => 'akreditasi/upm', 'label' => 'Renstra, Renop & Penjaminan Mutu (UPM)'),
);
// PPB Dynamic Mega Menu Module Safeguard
$mega_menu_inc = get_template_directory() . '/inc/ppb-mega-menu.php';
if (file_exists($mega_menu_inc)) {
    require_once $mega_menu_inc;
}

// Build Navigation Tree (Reads WP-Admin Appearance > Menus or falls back to complete default dataset)
$nav_tree = function_exists('ppb_get_nav_tree') ? ppb_get_nav_tree($current_path, $is_home) : [];
?>

<!-- FIXED NAVBAR (Original Left/Right Layout: w-full px-4 sm:px-8 lg:px-12) -->
<nav id="bk-main-navbar" class="fixed w-full z-50 transition-all duration-300 bg-[#0D3B22] border-b border-white/10 text-white shadow-lg">
    <!-- Top Portal Notification Strip -->
    <div style="background:#061d11;border-bottom:1px solid rgba(255,255,255,0.06);min-height:22px;">
        <div class="w-full px-4 sm:px-8 lg:px-12 flex items-center justify-center font-sans" style="padding-top:3px;padding-bottom:3px;font-size:9.5px;color:rgba(255,255,255,0.78);line-height:1.2;padding-left:var(--bk-edge-pad, 1rem);padding-right:var(--bk-edge-pad, 1rem);box-sizing:border-box;">
            <span style="background:rgba(245,184,46,0.15);color:#F5B82E;font-size:7.5px;font-weight:700;padding:1px 5px;border-radius:3px;letter-spacing:0.04em;text-transform:uppercase;border:1px solid rgba(245,184,46,0.25);margin-right:6px;">Portal</span>
            <span>Sistem Akademik &amp; Asesmen: </span>
            <a href="https://bk-connect-fipunm.vercel.app/auth" target="_blank" rel="noopener" style="color:#F5B82E;font-weight:700;text-decoration:none;margin-left:4px;display:inline-flex;align-items:center;gap:2px;">Masuk Portal &rarr;</a>
        </div>
    </div>

    <div id="bk-main-navbar-bar" class="w-full px-4 sm:px-8 lg:px-12 h-14 sm:h-16 flex items-center justify-between" style="padding-left:var(--bk-edge-pad, 1rem);padding-right:var(--bk-edge-pad, 1rem);box-sizing:border-box;">
        
        <!-- Logo & Brand Title -->
        <a href="<?php echo esc_url(home_url('/')); ?>" class="flex items-center gap-3 group" style="text-decoration:none;">
            <div class="h-9 sm:h-10 flex items-center justify-center shrink-0">
                <img 
                    src="<?php echo get_template_directory_uri(); ?>/assets/img/logo-white.png" 
                    alt="Logo UNM" 
                    class="h-8 sm:h-9 w-auto object-contain drop-shadow-sm"
                />
            </div>
            <div class="flex flex-col justify-center leading-none">
                <strong class="font-bold text-sm sm:text-base tracking-tight text-white font-display" style="font-family:'Fraunces',Georgia,serif;">
                    Jurusan PPB FIP UNM
                </strong>
                <span class="text-[8px] sm:text-[9px] text-[#F5B82E] font-semibold tracking-wider uppercase mt-0.5" style="font-family:'Poppins',sans-serif;">
                    BIMBINGAN DAN KONSELING
                </span>
            </div>
        </a>

        <!-- Desktop Navigation: Modern Mega Menu & Tautan Dinamis WP-Admin -->
        <div id="bk-desktop-nav" class="bk-desktop-nav items-center gap-1 xl:gap-1.5 text-xs font-semibold">
            <?php foreach ($nav_tree as $item) : 
                $has_sub   = !empty($item['sections']);
                $is_active = !empty($item['is_active']);
                $item_url  = !empty($item['url']) ? $item['url'] : '#';
                $badge     = !empty($item['badge']) ? $item['badge'] : '';
                $cols      = !empty($item['mega_cols']) ? (int)$item['mega_cols'] : (count($item['sections'] ?? []) > 1 ? count($item['sections']) : 2);
                $align     = !empty($item['align']) ? $item['align'] : 'left';
                $is_ext    = (!empty($item['target']) && $item['target'] === '_blank');
            ?>
                <?php if ($has_sub) : ?>
                    <!-- Mega Menu Dropdown: <?php echo esc_attr($item['title']); ?> -->
                    <div class="relative group dropdown-container bk-nav-item-wrap">
                        <button type="button" class="bk-nav-btn dropdown-trigger flex items-center gap-1 rounded-full <?php echo $is_active ? 'is-active-dropdown' : ''; ?>" aria-expanded="false" aria-haspopup="true">
                            <span><?php echo esc_html($item['title']); ?></span>
                            <?php if (!empty($badge) && in_array($badge, ['OBE 2025', 'Unggul', 'Baru', '25 Dosen', 'Prestasi', 'MIS PNBP'])) : ?>
                                <span class="bk-nav-pill-badge"><?php echo esc_html($badge); ?></span>
                            <?php endif; ?>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="transition-transform duration-200 group-hover:rotate-180" style="<?php echo $is_active ? 'color:#F5B82E;' : 'color:inherit;'; ?>"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        
                        <!-- Panel Mega Menu -->
                        <div class="bk-mega-dropdown dropdown-menu animate-fadeIn <?php echo esc_attr('mega-cols-' . $cols . ' align-' . $align); ?>">
                            
                            <!-- Header Bar -->
                            <?php if (!empty($item['subtitle']) || !empty($item['spotlight'])) : ?>
                                <div class="bk-mega-header">
                                    <div class="flex items-center gap-2.5">
                                        <?php if (!empty($item['icon'])) : ?>
                                            <div class="bk-mega-header-icon">
                                                <?php echo ppb_get_menu_icon_svg($item['icon'], 16); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <div class="bk-mega-header-title"><?php echo esc_html($item['title']); ?></div>
                                            <?php if (!empty($item['subtitle'])) : ?>
                                                <div class="bk-mega-header-sub"><?php echo esc_html($item['subtitle']); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <?php if (!empty($badge)) : ?>
                                        <span class="bk-mega-badge-pill"><?php echo esc_html($badge); ?></span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Kolom-Kolom Menu -->
                            <div class="bk-mega-body" style="--mega-cols: <?php echo (int)$cols; ?>;">
                                <?php foreach ($item['sections'] as $sec) : ?>
                                    <div class="bk-mega-col">
                                        <?php if (!empty($sec['heading'])) : ?>
                                            <div class="bk-mega-col-heading">
                                                <?php if (!empty($sec['icon'])) : ?>
                                                    <?php echo ppb_get_menu_icon_svg($sec['icon'], 13); ?>
                                                <?php endif; ?>
                                                <span><?php echo esc_html($sec['heading']); ?></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="bk-mega-col-list">
                                            <?php foreach ($sec['items'] as $link) : 
                                                $is_link_active = !empty($link['is_active']);
                                                $link_target = (!empty($link['target']) && $link['target'] === '_blank') ? ' target="_blank" rel="noopener"' : '';
                                            ?>
                                                <a href="<?php echo esc_url($link['url']); ?>" class="bk-mega-link <?php echo $is_link_active ? 'is-active' : ''; ?>"<?php echo $link_target; ?>>
                                                    <div class="bk-mega-link-icon">
                                                        <?php echo ppb_get_menu_icon_svg(!empty($link['icon']) ? $link['icon'] : 'chevron-right', 13); ?>
                                                    </div>
                                                    <div class="bk-mega-link-text">
                                                        <div class="bk-mega-link-title">
                                                            <span><?php echo esc_html($link['title']); ?></span>
                                                            <?php if (!empty($link['badge'])) : ?>
                                                                <span class="bk-mega-item-badge"><?php echo esc_html($link['badge']); ?></span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if (!empty($link['subtitle'])) : ?>
                                                            <div class="bk-mega-link-sub"><?php echo esc_html($link['subtitle']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <!-- Kartu Spotlight Aksi Cepat Bawah -->
                            <?php if (!empty($item['spotlight'])) : 
                                $sp = $item['spotlight'];
                                $sp_target = (!empty($sp['target']) && $sp['target'] === '_blank') ? ' target="_blank" rel="noopener"' : '';
                            ?>
                                <div class="bk-mega-spotlight">
                                    <div class="flex items-center gap-3">
                                        <?php if (!empty($sp['badge'])) : ?>
                                            <span class="bk-mega-spotlight-badge"><?php echo esc_html($sp['badge']); ?></span>
                                        <?php endif; ?>
                                        <div>
                                            <strong class="text-white text-xs block"><?php echo esc_html($sp['title']); ?></strong>
                                            <span class="text-white/80 text-[11px] block line-clamp-1"><?php echo esc_html($sp['description']); ?></span>
                                        </div>
                                    </div>
                                    <a href="<?php echo esc_url($sp['url']); ?>" class="bk-mega-spotlight-btn"<?php echo $sp_target; ?>>
                                        <?php echo esc_html(!empty($sp['action_text']) ? $sp['action_text'] : 'Buka &rarr;'); ?>
                                    </a>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>
                <?php else : ?>
                    <!-- Tautan Tunggal Tanpa Submenu -->
                    <a href="<?php echo esc_url($item_url); ?>" class="bk-nav-btn <?php echo $is_active ? 'is-active-btn' : ''; ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($item['title']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Mobile Menu Toggle Button (Bulletproof Native SVG & OnClick) -->
        <button 
            type="button" 
            id="mobile-nav-toggle" 
            class="rounded-xl text-white hover:bg-white/10 active:bg-white/20 transition-colors"
            aria-label="Buka Menu Navigasi"
            aria-expanded="false"
        >
            <!-- Hamburger Icon (Open) -->
            <svg id="menu-icon-open" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:block;pointer-events:none;">
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
            <!-- Close Icon (X) -->
            <svg id="menu-icon-close" width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="display:none;pointer-events:none;">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

    </div>

    <!-- Mobile Dropdown Navigation Menu: Accordion Mega Menu -->
    <div id="mobile-nav-menu" class="bg-[#0D3B22] border-b border-white/10 px-6 py-4 space-y-3 transition-all duration-300 max-h-[85vh] overflow-y-auto">
        <div class="space-y-1">
            <?php foreach ($nav_tree as $item) : 
                $has_sub   = !empty($item['sections']);
                $is_active = !empty($item['is_active']);
                $item_url  = !empty($item['url']) ? $item['url'] : '#';
                $badge     = !empty($item['badge']) ? $item['badge'] : '';
                $is_ext    = (!empty($item['target']) && $item['target'] === '_blank');
            ?>
                <?php if ($has_sub) : ?>
                    <!-- Accordion Submenu: <?php echo esc_attr($item['title']); ?> -->
                    <div class="py-2 border-b border-white/10">
                        <button type="button" onclick="bkToggleAccordion(this)" class="mobile-acc-btn w-full flex items-center justify-between text-sm font-semibold py-2 px-1 rounded-lg <?php echo $is_active ? 'text-[#F5B82E]' : 'text-white/90 hover:text-[#F5B82E]'; ?>" style="cursor:pointer;background:transparent;border:none;">
                            <span class="flex items-center gap-2">
                                <?php if (!empty($item['icon'])) : ?>
                                    <span style="opacity:0.85;"><?php echo ppb_get_menu_icon_svg($item['icon'], 16); ?></span>
                                <?php endif; ?>
                                <span><?php echo esc_html($item['title']); ?></span>
                                <?php if (!empty($badge)) : ?>
                                    <span style="background:rgba(245,184,46,0.22);color:#F5B82E;font-size:10px;padding:1px 6px;border-radius:4px;font-weight:700;"><?php echo esc_html($badge); ?></span>
                                <?php endif; ?>
                            </span>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="pointer-events:none;color:#F5B82E;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="mobile-acc-content pl-2 pt-2 space-y-3 border-l-2 border-white/20 ml-2 mt-2">
                            <?php foreach ($item['sections'] as $sec) : ?>
                                <div class="mobile-sec-group">
                                    <?php if (!empty($sec['heading']) && count($item['sections']) > 1) : ?>
                                        <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.05em;color:#F5B82E;opacity:0.9;margin-bottom:4px;padding-left:4px;display:flex;align-items:center;gap:4px;">
                                            📁 <?php echo esc_html($sec['heading']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="space-y-1">
                                        <?php foreach ($sec['items'] as $link) : 
                                            $is_link_active = !empty($link['is_active']);
                                            $link_target = (!empty($link['target']) && $link['target'] === '_blank') ? ' target="_blank" rel="noopener"' : '';
                                        ?>
                                            <a href="<?php echo esc_url($link['url']); ?>" class="block text-sm py-1.5 px-2 rounded-lg transition-colors <?php echo $is_link_active ? 'text-[#F5B82E] font-bold bg-white/10' : 'text-white/80 hover:text-[#F5B82E] hover:bg-white/5'; ?>"<?php echo $link_target; ?> style="text-decoration:none;">
                                                <div class="flex items-center justify-between">
                                                    <span><?php echo esc_html($link['title']); ?></span>
                                                    <?php if (!empty($link['badge'])) : ?>
                                                        <span style="background:rgba(245,184,46,0.25);color:#F5B82E;font-size:9.5px;padding:1px 5px;border-radius:3px;font-weight:700;"><?php echo esc_html($link['badge']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($link['subtitle'])) : ?>
                                                    <div style="font-size:11px;color:rgba(255,255,255,0.55);line-height:1.2;margin-top:2px;"><?php echo esc_html($link['subtitle']); ?></div>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <a href="<?php echo esc_url($item_url); ?>" class="block text-sm font-bold py-2 <?php echo $is_active ? 'text-[#F5B82E]' : 'text-white/90 hover:text-[#F5B82E]'; ?>" style="<?php echo $is_active ? 'color:#F5B82E !important;' : 'color:rgba(255,255,255,0.9) !important;'; ?>"<?php echo $is_ext ? ' target="_blank" rel="noopener"' : ''; ?>>
                        <?php echo esc_html($item['title']); ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</nav>
<!-- Flow Spacer to guarantee Fixed Navbar & Admin Bar never cover top page content -->
<div id="bk-navbar-spacer" aria-hidden="true"></div>

<script>
(function() {
    function syncBkNavbarSpacer() {
        var nav = document.getElementById("bk-main-navbar");
        var spacer = document.getElementById("bk-navbar-spacer");
        if (!nav || !spacer) return;
        var adminBar = document.getElementById("wpadminbar");
        var isBarVisible = document.body.classList.contains("admin-bar");
        var adminH = 0;
        if (isBarVisible) {
            adminH = (adminBar && adminBar.offsetHeight > 0) ? adminBar.offsetHeight : (window.innerWidth <= 782 ? 46 : 32);
            nav.style.setProperty("top", adminH + "px", "important");
        } else {
            nav.style.setProperty("top", "0px", "important");
        }
        var bar = document.getElementById("bk-main-navbar-bar");
        var navH = nav.offsetHeight;
        if (bar && bar.offsetHeight > 0) {
            navH = bar.offsetTop + bar.offsetHeight;
        }
        spacer.style.setProperty("height", (adminH + navH) + "px", "important");
        document.documentElement.style.setProperty("--bk-navbar-height", navH + "px");
        document.documentElement.style.setProperty("--bk-admin-bar-offset", adminH + "px");
    }
    syncBkNavbarSpacer();
    document.addEventListener("DOMContentLoaded", syncBkNavbarSpacer);
    window.addEventListener("load", syncBkNavbarSpacer);
    window.addEventListener("resize", syncBkNavbarSpacer);
})();
</script>

<script>
(function() {
    // 1. Mobile Menu Toggle Controller (Bulletproof Global Scope)
    window.bkToggleMobileNav = function(forceState) {
        var menu = document.getElementById("mobile-nav-menu");
        var btn = document.getElementById("mobile-nav-toggle");
        var iconOpen = document.getElementById("menu-icon-open");
        var iconClose = document.getElementById("menu-icon-close");
        if (!menu) return;

        var isOpen = menu.classList.contains("is-open");
        var shouldOpen = (typeof forceState === "boolean") ? forceState : !isOpen;
        
        if (shouldOpen) {
            menu.classList.add("is-open");
            if (btn) btn.setAttribute("aria-expanded", "true");
            if (iconOpen) iconOpen.style.setProperty("display", "none", "important");
            if (iconClose) iconClose.style.setProperty("display", "block", "important");
        } else {
            menu.classList.remove("is-open");
            if (btn) btn.setAttribute("aria-expanded", "false");
            if (iconOpen) iconOpen.style.setProperty("display", "block", "important");
            if (iconClose) iconClose.style.setProperty("display", "none", "important");
        }
    };

    // 2. Submenu Accordion Controller
    window.bkToggleAccordion = function(btn) {
        var content = btn.nextElementSibling;
        if (!content) return;
        var isOpen = content.classList.contains("is-open");
        if (isOpen) {
            content.classList.remove("is-open");
            btn.classList.remove("is-open");
        } else {
            content.classList.add("is-open");
            btn.classList.add("is-open");
        }
    };

    // 3. Bind Single Event Listener to Toggle Button
    function bindMobileNav() {
        var btn = document.getElementById("mobile-nav-toggle");
        if (btn) {
            btn.onclick = function(e) {
                if (e) {
                    e.preventDefault();
                    e.stopPropagation();
                }
                window.bkToggleMobileNav();
            };
        }
    }
    bindMobileNav();
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", bindMobileNav);
    }

    // 4. Close mobile menu on desktop resize (>= 1024px)
    window.addEventListener("resize", function() {
        if (window.innerWidth >= 1024) {
            window.bkToggleMobileNav(false);
        }
    });

    // 5. Close mobile menu when clicking page links
    document.addEventListener("click", function(e) {
        var link = e.target.closest("#mobile-nav-menu a");
        if (link) {
            var href = link.getAttribute("href");
            if (href && href !== "#") {
                window.bkToggleMobileNav(false);
            }
        }
    });

    // 6. Lucide icons initialization safeguard
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // 7. Auto-lock all navigation links to /yamato staging environment
    (function() {
        var isYamato = (window.location.pathname.indexOf('/yamato') === 0 || window.location.href.indexOf('/yamato') !== -1);
        if (!isYamato) return;

        function fixYamatoUrl(url) {
            if (!url) return url;
            if (url.indexOf('#') === 0 || url.indexOf('mailto:') === 0 || url.indexOf('tel:') === 0 || url.indexOf('javascript:') === 0) return url;
            if (url.indexOf('//') === 0 && url.indexOf('//bk-fip.unm.ac.id') === -1) return url;

            // Relative path
            if (url.indexOf('/') === 0) {
                if (url.indexOf('/yamato/') === 0 || url === '/yamato') return url;
                return '/yamato' + (url === '/' ? '/' : url);
            }

            // Absolute URL to bk-fip.unm.ac.id
            if (url.indexOf('://bk-fip.unm.ac.id') !== -1) {
                try {
                    var u = new URL(url);
                    if (u.pathname.indexOf('/yamato/') !== 0 && u.pathname !== '/yamato') {
                        u.pathname = '/yamato' + (u.pathname === '/' ? '/' : u.pathname);
                        return u.toString();
                    }
                } catch(e) {}
            }
            return url;
        }

        function patchLinks() {
            var links = document.querySelectorAll('a[href]');
            for (var i = 0; i < links.length; i++) {
                var raw = links[i].getAttribute('href');
                var fixed = fixYamatoUrl(raw);
                if (fixed !== raw) {
                    links[i].setAttribute('href', fixed);
                }
            }
        }

        patchLinks();
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', patchLinks);
        }
        window.addEventListener('load', patchLinks);

        // Global capture-phase click interceptor (bulletproof guarantee on any link click)
        document.addEventListener('click', function(e) {
            var a = e.target.closest('a');
            if (!a) return;
            var href = a.getAttribute('href');
            if (!href) return;
            var fixed = fixYamatoUrl(href);
            if (fixed !== href) {
                e.preventDefault();
                e.stopPropagation();
                a.setAttribute('href', fixed);
                window.location.href = fixed;
            }
        }, true);
    })();
})();
</script>
