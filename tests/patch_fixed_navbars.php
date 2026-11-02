<?php
/**
 * Patch public/assets/css/app.css for Fixed Top and Bottom Navbars on Responsive Devices
 */

$cssPath = __DIR__ . '/../public/assets/css/app.css';
$css = file_get_contents($cssPath);

// Standardize CRLF to LF for reliable replacements
$isCrlf = strpos($css, "\r\n") !== false;
if ($isCrlf) {
    $css = str_replace("\r\n", "\n", $css);
}

// 1. Update lines around 2757: @media (max-width: 600px) .main-content
$target1 = "@media (max-width: 600px) {\n    .main-content {\n        padding: 1rem 0.85rem 2rem !important;\n    }";
$replace1 = "@media (max-width: 600px) {\n    .main-content {\n        padding: calc(60px + 12px) 0.85rem calc(64px + 32px + env(safe-area-inset-bottom, 0px)) !important;\n    }";

if (strpos($css, $target1) !== false) {
    $css = str_replace($target1, $replace1, $css);
    echo "Replaced target 1 (600px padding)\n";
} else {
    echo "Target 1 not found!\n";
}

// 2. Update lines around 2806: @media (max-width: 480px) .main-content
$target2 = "    .main-content {\n        padding: 0.85rem 0.65rem 2.2rem !important;\n    }";
$replace2 = "    .main-content {\n        padding: calc(60px + 10px) 0.65rem calc(64px + 32px + env(safe-area-inset-bottom, 0px)) !important;\n    }";

if (strpos($css, $target2) !== false) {
    $css = str_replace($target2, $replace2, $css);
    echo "Replaced target 2 (480px padding)\n";
} else {
    echo "Target 2 not found!\n";
}

// 3. Update lines around 5217-5272: Desktop / Mobile Dashboard Display Switch
$target3 = <<<'CSS'
@media (min-width: 769px) {
    .mv-dashboard-view,
    .mv-dash-topbar {
        display: none !important;
    }

    .desktop-dashboard-view {
        display: block !important;
    }
}

@media (max-width: 768px) {
    .desktop-dashboard-view {
        display: none !important;
    }

    .mv-dashboard-view {
        display: block !important;
        background: #f8fafc;
        min-height: 100vh;
        padding-bottom: 74px;
        /* clearance for bottom dock */
    }

    /* Hide standard desktop topbar across ALL pages on responsive devices (<= 768px) */
    .topbar {
        display: none !important;
    }

    body.mobile-dashboard-active .app-footer,
    .main-wrapper:has(.mv-dashboard-view) .app-footer {
        display: none !important;
    }

    body.mobile-dashboard-active .main-content,
    .main-wrapper:has(.mv-dashboard-view) .main-content {
        padding: 0 !important;
        margin: 0 !important;
        max-width: 100% !important;
    }

    /* --------------------------------------------------------------------------
       1. Mobile Topbar Header (Matches Dashboard & mobile_dashbord.jpeg across ALL pages)
       -------------------------------------------------------------------------- */
    .mv-dash-topbar {
        background: #ffffff;
        padding: 12px 16px;
        display: flex !important;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid #f1f5f9;
        position: sticky;
        top: 0;
        z-index: 95;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }
CSS;

$replace3 = <<<'CSS'
@media (min-width: 901px) {
    .mv-dashboard-view,
    .mv-dash-topbar {
        display: none !important;
    }

    .desktop-dashboard-view {
        display: block !important;
    }
}

@media (max-width: 900px) {
    .desktop-dashboard-view {
        display: none !important;
    }

    /* --------------------------------------------------------------------------
       1. Fixed Modern Mobile Topbar Header Across ALL Driver & Owner Pages
       -------------------------------------------------------------------------- */
    .mv-dash-topbar {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 60px !important;
        min-height: 60px !important;
        padding: 0 16px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(12px) !important;
        -webkit-backdrop-filter: blur(12px) !important;
        border-bottom: 1px solid rgba(226, 232, 240, 0.90) !important;
        box-shadow: 0 2px 12px rgba(15, 23, 42, 0.05) !important;
        z-index: 1000 !important;
        box-sizing: border-box !important;
    }

    /* Hide standard desktop topbar across ALL pages on responsive devices (<= 900px) */
    .topbar {
        display: none !important;
    }

    /* Fixed Notched Bottom Navigation Across ALL Pages on Responsive Devices */
    .notched-bottom-nav {
        display: block !important;
        position: fixed !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100% !important;
        height: 64px !important;
        z-index: 990 !important;
        background: transparent !important;
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        box-sizing: border-box !important;
        pointer-events: auto !important;
    }

    /* Ensure Navigation Drawer and Backdrop Stay Above Both Fixed Navbars */
    .sidebar-backdrop {
        z-index: 10010 !important;
    }
    .sidebar {
        z-index: 10020 !important;
    }

    /* Floating Dropdown Menus on Fixed Topbar */
    .mv-dash-menu {
        position: absolute !important;
        top: calc(100% + 6px) !important;
        right: 0 !important;
        z-index: 10005 !important;
    }

    /* Universal Content Spacing (Guarantees zero overlap with fixed topbar and fixed bottom navbar) */
    .main-content {
        padding-top: calc(60px + 14px) !important;
        padding-bottom: calc(64px + 32px + env(safe-area-inset-bottom, 0px)) !important;
        padding-left: 1rem !important;
        padding-right: 1rem !important;
        box-sizing: border-box !important;
    }

    .mv-dashboard-view {
        display: block !important;
        background: #f8fafc;
        min-height: 100vh;
        padding-top: calc(60px + 12px) !important;
        padding-bottom: calc(64px + 32px) !important;
        box-sizing: border-box !important;
    }

    body.mobile-dashboard-active .app-footer,
    .main-wrapper:has(.mv-dashboard-view) .app-footer {
        display: none !important;
    }
CSS;

if (strpos($css, $target3) !== false) {
    $css = str_replace($target3, $replace3, $css);
    echo "Replaced target 3 (Mobile topbar & dashboard display switch)\n";
} else {
    echo "Target 3 not found!\n";
}

// 4. Update line 7995: @media (max-width: 1024px) .main-content
$target4 = "    .main-content {\n        padding: 1.25rem 1rem 5rem !important;\n    }";
$replace4 = "    .main-content {\n        padding: 1.25rem 1rem 5rem;\n    }";
if (strpos($css, $target4) !== false) {
    $css = str_replace($target4, $replace4, $css);
    echo "Replaced target 4 (1024px padding)\n";
} else {
    echo "Target 4 not found!\n";
}

// 5. Update line 8186: @media (max-width: 576px) .main-content
$target5 = "    .main-content {\n        padding: 0.85rem 0.65rem 5.5rem !important;\n    }";
$replace5 = "    .main-content {\n        padding: calc(60px + 10px) 0.65rem calc(64px + 32px + env(safe-area-inset-bottom, 0px)) !important;\n    }";
if (strpos($css, $target5) !== false) {
    $css = str_replace($target5, $replace5, $css);
    echo "Replaced target 5 (576px padding)\n";
} else {
    echo "Target 5 not found!\n";
}

// Restore CRLF if file was CRLF
if ($isCrlf) {
    $css = str_replace("\n", "\r\n", $css);
}

file_put_contents($cssPath, $css);
echo "CSS file patched successfully!\n";
