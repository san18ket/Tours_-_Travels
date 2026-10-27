/**
 * School Management System - Centralized Theme & Appearance Builder
 * Real-time Interactive Live Preview Engine & Configuration Manager
 */

(function () {
    'use strict';

    let currentTheme = null;
    let designs = {};
    let presets = {};
    let isSaving = false;

    // Radius token definitions
    const radiusTokens = {
        sharp: { sm: '0px', md: '0px', lg: '0px', xl: '0px', pill: '0px' },
        small: { sm: '2px', md: '4px', lg: '6px', xl: '8px', pill: '9999px' },
        medium: { sm: '4px', md: '8px', lg: '12px', xl: '16px', pill: '9999px' },
        large: { sm: '6px', md: '10px', lg: '14px', xl: '20px', pill: '9999px' },
        xl: { sm: '8px', md: '14px', lg: '20px', xl: '28px', pill: '9999px' },
        pill: { sm: '8px', md: '16px', lg: '24px', xl: '32px', pill: '9999px' }
    };

    // Shadow token definitions
    const shadowTokens = {
        none: { card: 'none', dropdown: '0 4px 12px rgba(0,0,0,0.06)', modal: '0 10px 25px rgba(0,0,0,0.1)', sidebar: 'none', navbar: 'none' },
        subtle: { card: '0 1px 3px rgba(15,23,42,0.05)', dropdown: '0 4px 16px rgba(15,23,42,0.08)', modal: '0 10px 30px rgba(15,23,42,0.12)', sidebar: '0 2px 10px rgba(15,23,42,0.04)', navbar: '0 1px 3px rgba(15,23,42,0.04)' },
        soft: { card: '0 4px 16px rgba(15,23,42,0.05)', dropdown: '0 10px 30px rgba(15,23,42,0.12)', modal: '0 20px 40px rgba(15,23,42,0.18)', sidebar: '0 0 30px rgba(15,23,42,0.15)', navbar: '0 2px 8px rgba(15,23,42,0.05)' },
        medium: { card: '0 6px 20px rgba(15,23,42,0.09)', dropdown: '0 12px 36px rgba(15,23,42,0.16)', modal: '0 24px 50px rgba(15,23,42,0.24)', sidebar: '0 0 35px rgba(15,23,42,0.3)', navbar: '0 4px 12px rgba(15,23,42,0.08)' },
        strong: { card: '0 10px 30px rgba(15,23,42,0.14)', dropdown: '0 16px 40px rgba(15,23,42,0.22)', modal: '0 30px 60px rgba(15,23,42,0.3)', sidebar: '0 0 45px rgba(0,0,0,0.4)', navbar: '0 6px 20px rgba(15,23,42,0.12)' },
        floating: { card: '0 12px 35px -4px rgba(15,23,42,0.08), 0 4px 12px -2px rgba(15,23,42,0.06)', dropdown: '0 20px 45px -5px rgba(15,23,42,0.2)', modal: '0 35px 70px -10px rgba(15,23,42,0.35)', sidebar: '0 15px 40px -5px rgba(15,23,42,0.25)', navbar: '0 8px 24px -3px rgba(15,23,42,0.1)' }
    };

    // Font definitions
    const fontFamilies = {
        Inter: "'Inter', system-ui, -apple-system, sans-serif",
        Outfit: "'Outfit', 'Inter', system-ui, sans-serif",
        Poppins: "'Poppins', 'Segoe UI', Roboto, sans-serif",
        Manrope: "'Manrope', 'Inter', sans-serif",
        Roboto: "'Roboto', 'Helvetica Neue', Arial, sans-serif",
        'Open Sans': "'Open Sans', 'Segoe UI', Arial, sans-serif",
        Nunito: "'Nunito', system-ui, sans-serif",
        'system-ui': "system-ui, -apple-system, BlinkMacSystemFont, sans-serif"
    };

    function hexToRgb(hex) {
        hex = (hex || '#B9828F').replace('#', '').trim();
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        if (hex.length < 6) return '185, 130, 143';
        const r = parseInt(hex.substring(0, 2), 16) || 0;
        const g = parseInt(hex.substring(2, 4), 16) || 0;
        const b = parseInt(hex.substring(4, 6), 16) || 0;
        return `${r}, ${g}, ${b}`;
    }

    function isHexColorDark(hex) {
        hex = (hex || '#FFFFFF').replace('#', '').trim();
        if (hex.length === 3) {
            hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
        }
        if (hex.length < 6) return false;
        const r = parseInt(hex.substring(0, 2), 16) || 0;
        const g = parseInt(hex.substring(2, 4), 16) || 0;
        const b = parseInt(hex.substring(4, 6), 16) || 0;
        const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255;
        return luminance < 0.55;
    }

    document.addEventListener('DOMContentLoaded', function () {
        initThemeBuilder();
    });

    function initThemeBuilder() {
        const rootDataEl = document.getElementById('theme-init-data');
        if (!rootDataEl) return;

        try {
            currentTheme = JSON.parse(rootDataEl.textContent);
        } catch (e) {
            console.error('Failed to parse initial theme data', e);
            return;
        }

        const designsDataEl = document.getElementById('theme-designs-data');
        if (designsDataEl) {
            try {
                designs = JSON.parse(designsDataEl.textContent);
            } catch (e) {
                console.error('Failed to parse designs data', e);
            }
        }

        const presetsDataEl = document.getElementById('theme-presets-data');
        if (presetsDataEl) {
            try {
                presets = JSON.parse(presetsDataEl.textContent);
            } catch (e) {
                console.error('Failed to parse presets data', e);
            }
        }

        bindColorInputs();
        bindPresetCards();
        bindDesignCards();
        bindDesignPreviewModal();
        bindSidebarCards();
        bindNavbarCards();
        bindTypographyInputs();
        bindRadiusAndShadow();
        bindDensityAndMode();
        bindComponentToggles();
        bindActionButtons();
        bindImportExport();

        // Initial preview render
        updateLivePreview();
        checkContrastSafety();
    }

    /**
     * Color Inputs (Dual Color Picker + Hex Text Input Sync)
     */
    function bindColorInputs() {
        document.querySelectorAll('.theme-color-input-sync').forEach(container => {
            const picker = container.querySelector('input[type="color"]');
            const textInput = container.querySelector('input[type="text"]');
            const colorKey = container.getAttribute('data-color-key');

            if (!picker || !textInput || !colorKey) return;

            // Sync from color picker
            picker.addEventListener('input', function () {
                textInput.value = this.value;
                setColorValue(colorKey, this.value);
            });

            // Sync from text input
            textInput.addEventListener('input', function () {
                const val = this.value.trim();
                if (/^#[0-9a-fA-F]{6}$/i.test(val)) {
                    picker.value = val;
                }
                setColorValue(colorKey, val);
            });
        });

        // Brand Gradient Input
        const gradientInput = document.getElementById('input_brand_gradient');
        if (gradientInput) {
            gradientInput.addEventListener('input', function () {
                if (!currentTheme.colors) currentTheme.colors = {};
                currentTheme.colors.brand_gradient = this.value;
                updateLivePreview();
            });
        }
    }

    function setColorValue(key, val) {
        if (!currentTheme.colors) currentTheme.colors = {};
        currentTheme.colors[key] = val;

        // Auto-compute hover and active states for primary if not explicitly customized
        if (key === 'primary' && /^#[0-9a-fA-F]{6}$/i.test(val)) {
            const hover = adjustColorBrightness(val, -15);
            const active = adjustColorBrightness(val, -25);
            const hoverInput = document.querySelector('[data-color-key="primary_hover"] input[type="text"]');
            const activeInput = document.querySelector('[data-color-key="primary_active"] input[type="text"]');
            const hoverPicker = document.querySelector('[data-color-key="primary_hover"] input[type="color"]');
            const activePicker = document.querySelector('[data-color-key="primary_active"] input[type="color"]');

            if (hoverInput && hoverPicker) {
                hoverInput.value = hover;
                hoverPicker.value = hover;
                currentTheme.colors.primary_hover = hover;
            }
            if (activeInput && activePicker) {
                activeInput.value = active;
                activePicker.value = active;
                currentTheme.colors.primary_active = active;
            }
        }

        updateLivePreview();
        checkContrastSafety();
    }

    /**
     * Presets Selection
     */
    function bindPresetCards() {
        document.querySelectorAll('.theme-preset-card').forEach(card => {
            card.addEventListener('click', function () {
                const presetId = this.getAttribute('data-preset-id');
                const p = presets[presetId];
                if (!p) return;

                // Update selected UI
                document.querySelectorAll('.theme-preset-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                currentTheme.preset_name = presetId;
                if (!currentTheme.colors) currentTheme.colors = {};

                // Apply preset colors
                currentTheme.colors.primary = p.primary;
                currentTheme.colors.secondary = p.secondary;
                currentTheme.colors.accent = p.accent;
                currentTheme.colors.brand_gradient = p.brand_gradient;
                currentTheme.colors.sidebar_bg = p.sidebar_bg;
                currentTheme.colors.navbar_bg = p.navbar_bg;
                currentTheme.colors.body_bg = p.body_bg;
                currentTheme.colors.surface = p.surface;
                currentTheme.colors.card_bg = p.surface;
                currentTheme.colors.text_primary = p.text_primary;
                currentTheme.mode = p.mode || 'light';

                // Sync form inputs
                syncAllFormInputs();
                updateLivePreview();
                checkContrastSafety();

                if (typeof window.showToast === 'function') {
                    window.showToast(`Applied preset: ${p.name}`, 'info');
                }
            });
        });
    }

    /**
     * 20 Complete Admin Panel Luxury Design Cards
     */
    function bindDesignCards() {
        let previousThemeSnapshot = null;

        // Category Filter Buttons
        document.querySelectorAll('.theme-cat-filter').forEach(btn => {
            btn.addEventListener('click', function () {
                document.querySelectorAll('.theme-cat-filter').forEach(b => {
                    b.classList.remove('btn-primary', 'active');
                    b.classList.add('btn-outline-secondary');
                    b.setAttribute('aria-pressed', 'false');
                });
                this.classList.remove('btn-outline-secondary');
                this.classList.add('btn-primary', 'active');
                this.setAttribute('aria-pressed', 'true');

                const filter = this.getAttribute('data-filter');
                filterThemeCards(filter, document.getElementById('themeSearchInput')?.value || '');
            });
        });

        // Search Input Filter
        const searchInput = document.getElementById('themeSearchInput');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const activeCatBtn = document.querySelector('.theme-cat-filter.active');
                const catFilter = activeCatBtn ? activeCatBtn.getAttribute('data-filter') : 'all';
                filterThemeCards(catFilter, this.value.trim().toLowerCase());
            });
        }

        // Reset Filter Button (Empty State)
        const btnResetFilter = document.getElementById('btnResetThemeFilter');
        if (btnResetFilter) {
            btnResetFilter.addEventListener('click', function () {
                const sInput = document.getElementById('themeSearchInput');
                if (sInput) sInput.value = '';
                document.querySelectorAll('.theme-cat-filter').forEach(b => {
                    const isAll = b.getAttribute('data-filter') === 'all';
                    b.classList.toggle('btn-primary', isAll);
                    b.classList.toggle('active', isAll);
                    b.classList.toggle('btn-outline-secondary', !isAll);
                    b.setAttribute('aria-pressed', isAll ? 'true' : 'false');
                });
                filterThemeCards('all', '');
            });
        }

        function filterThemeCards(category, query) {
            let visibleCount = 0;
            const cards = document.querySelectorAll('.theme-design-card');
            cards.forEach(card => {
                const cardCat = (card.getAttribute('data-category') || '').toLowerCase();
                const cardName = (card.getAttribute('data-name') || '').toLowerCase();
                const cardTagline = (card.getAttribute('data-tagline') || '').toLowerCase();
                const cardId = (card.getAttribute('data-design-id') || '').toLowerCase();
                const cardMode = (card.getAttribute('data-mode') || '').toLowerCase();

                const matchesCat = (category === 'all' || cardCat === category.toLowerCase());
                const q = (query || '').trim().toLowerCase();
                const matchesQuery = (!q || cardName.includes(q) || cardTagline.includes(q) || cardId.includes(q) || cardCat.includes(q) || cardMode.includes(q));

                if (matchesCat && matchesQuery) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            const emptyState = document.getElementById('themeGalleryEmptyState');
            if (emptyState) {
                emptyState.classList.toggle('d-none', visibleCount > 0);
            }

            const countEl = document.getElementById('count-all');
            if (countEl && category === 'all' && !query) {
                countEl.textContent = visibleCount;
            }
        }

        // Live Preview Button on Cards
        document.querySelectorAll('.btn-live-preview-theme').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const designId = this.getAttribute('data-preview-theme-id');
                triggerRealTimePreview(designId);
            });
        });

        // Direct Apply Button on Cards
        document.querySelectorAll('.btn-apply-theme').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const designId = this.getAttribute('data-apply-theme-id');
                selectDesign(designId);
                saveThemeConfiguration(currentTheme, this);
            });
        });

        // Floating Preview Action Bar Handlers
        const floatingBar = document.getElementById('floatingLivePreviewBar');
        const btnCancel = document.getElementById('btnPreviewCancel');
        const btnApply = document.getElementById('btnPreviewApply');

        function triggerRealTimePreview(designId) {
            const d = designs[designId];
            if (!d) return;

            if (!previousThemeSnapshot) {
                previousThemeSnapshot = JSON.parse(JSON.stringify(currentTheme));
            }

            selectDesign(designId);

            if (floatingBar) {
                floatingBar.classList.remove('d-none');
                const themeNameEl = document.getElementById('previewBarThemeName');
                if (themeNameEl) themeNameEl.textContent = `${d.name} (${d.category || 'Luxury'})`;
            }

            if (typeof window.showToast === 'function') {
                window.showToast(`Live Previewing: ${d.name}. Click Apply to save.`, 'info');
            }
        }

        if (btnCancel) {
            btnCancel.addEventListener('click', function () {
                if (previousThemeSnapshot) {
                    currentTheme = JSON.parse(JSON.stringify(previousThemeSnapshot));
                    syncAllFormInputs();
                    updateLivePreview();
                    previousThemeSnapshot = null;
                }
                const liveStyle = document.getElementById('theme-engine-live-style');
                if (liveStyle) liveStyle.remove();

                if (floatingBar) floatingBar.classList.add('d-none');
                if (typeof window.showToast === 'function') {
                    window.showToast('Preview cancelled. Restored previous active theme.', 'secondary');
                }
            });
        }

        if (btnApply) {
            btnApply.addEventListener('click', function () {
                saveThemeConfiguration(currentTheme, this);
            });
        }

        document.querySelectorAll('.theme-design-card').forEach(card => {
            card.addEventListener('click', function (e) {
                if (e.target.closest('.btn-live-preview-theme') || e.target.closest('.btn-apply-theme')) return;
                const designId = this.getAttribute('data-design-id');
                triggerRealTimePreview(designId);
            });
        });
    }

    /**
     * Select Complete Admin Panel Design System (Supports all 50 Luxury Identities)
     */
    function selectDesign(designId) {
        if (!designId) return;

        currentTheme.design = designId;
        currentTheme.id = designId;

        const d = designs[designId];
        if (d) {
            currentTheme.name = d.name || designId;
            currentTheme.category = d.category || 'Light Luxury';
            currentTheme.mode = d.mode || d.default_mode || 'light';

            const colors = d.colors || d.defaults || {};
            const sw = d.swatches || [];

            if (!currentTheme.colors) currentTheme.colors = {};
            currentTheme.colors.primary = colors.primary || colors.primary_color || (sw[0] || '#B9828F');
            currentTheme.colors.primary_hover = colors.primary_hover || '#A96F7D';
            currentTheme.colors.primary_active = colors.primary_active || '#985C6A';
            currentTheme.colors.secondary = colors.secondary || colors.secondary_color || (sw[1] || '#292326');
            currentTheme.colors.accent = colors.accent || colors.accent_color || (sw[2] || '#C8A96B');
            currentTheme.colors.brand_gradient = colors.brand_gradient || `linear-gradient(135deg, ${currentTheme.colors.primary} 0%, ${currentTheme.colors.secondary} 100%)`;
            currentTheme.colors.body_bg = colors.body_bg || (currentTheme.mode === 'dark' ? '#0F1218' : '#F8F6F4');
            currentTheme.colors.surface = colors.surface || colors.surface_color || (currentTheme.mode === 'dark' ? '#181C24' : '#FFFFFF');
            currentTheme.colors.surface_secondary = colors.surface_secondary || (currentTheme.mode === 'dark' ? '#1E232E' : '#F3ECE7');
            currentTheme.colors.card_bg = colors.card_bg || currentTheme.colors.surface;
            currentTheme.colors.sidebar_bg = colors.sidebar_bg || colors.sidebar_color || (currentTheme.mode === 'dark' ? '#12151C' : '#FFFDFC');
            currentTheme.colors.navbar_bg = colors.navbar_bg || colors.navbar_color || (currentTheme.mode === 'dark' ? '#181C24' : '#FFFFFF');
            currentTheme.colors.modal_bg = colors.modal_bg || currentTheme.colors.surface;
            currentTheme.colors.input_bg = colors.input_bg || (currentTheme.mode === 'dark' ? '#1C212B' : '#FFFFFF');
            currentTheme.colors.text_primary = colors.text_primary || (currentTheme.mode === 'dark' ? '#F1F5F9' : '#332D2F');
            currentTheme.colors.text_secondary = colors.text_secondary || (currentTheme.mode === 'dark' ? '#94A3B8' : '#817477');
            currentTheme.colors.text_muted = colors.text_muted || (currentTheme.mode === 'dark' ? '#64748B' : '#9A8E91');
            currentTheme.colors.border = colors.border || (currentTheme.mode === 'dark' ? 'rgba(255,255,255,0.08)' : '#E9DFD8');
            currentTheme.colors.border_focus = colors.border_focus || currentTheme.colors.primary;
            currentTheme.colors.success = colors.success || '#5D9078';
            currentTheme.colors.warning = colors.warning || '#C59A55';
            currentTheme.colors.danger = colors.danger || '#B56B70';
            currentTheme.colors.info = colors.info || '#62829A';

            if (d.typography) {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.font_body = d.typography.font_body || d.typography.body || 'Inter';
                currentTheme.typography.font_heading = d.typography.font_heading || d.typography.heading || 'Outfit';
            }

            if (d.components) {
                if (!currentTheme.components) currentTheme.components = {};
                currentTheme.components.sidebar_style = d.components.sidebar_style || 'classic';
                currentTheme.components.card_style = d.components.card_style || 'elevated';
                currentTheme.components.density = d.components.density || 'comfortable';
                currentTheme.sidebar_style = currentTheme.components.sidebar_style;
                currentTheme.density = currentTheme.components.density;
            }

            if (d.radius) {
                currentTheme.radius_preset = d.radius.preset || 'large';
            }
            if (d.shadows) {
                currentTheme.shadow_preset = d.shadows.preset || 'soft';
            }
        }

        // Synchronize UI and live preview
        syncAllFormInputs();
        updateLivePreview();
        checkContrastSafety();

        if (typeof window.showToast === 'function') {
            const name = d ? d.name : designId;
            window.showToast(`Active Design: ${name} (${d ? d.category : 'Luxury'})`, 'info');
        }
    }

    /**
     * Full Design Preview Modal Handler
     */
    function bindDesignPreviewModal() {
        const modalEl = document.getElementById('designFullPreviewModal');
        if (!modalEl) return;

        let activePreviewDesignId = null;

        document.querySelectorAll('.btn-preview-design').forEach(btn => {
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                const designId = this.getAttribute('data-preview-design-id');
                activePreviewDesignId = designId;

                const canvas = document.getElementById('modalDesignPreviewCanvas');
                const categoryLabel = document.getElementById('previewModalDesignCategory');
                const titleLabel = document.getElementById('designFullPreviewModalLabel');
                const d = designs[designId];

                if (canvas && d && d.defaults) {
                    canvas.setAttribute('data-theme-id', designId);
                    canvas.setAttribute('data-theme-design', designId);
                    canvas.setAttribute('data-theme-mode', 'light');
                    canvas.style.setProperty('--theme-primary', d.defaults.primary_color || '#B9828F');
                    canvas.style.setProperty('--theme-secondary', d.defaults.secondary_color || '#292326');
                    canvas.style.setProperty('--theme-accent', d.defaults.accent_color || '#C8A96B');
                    canvas.style.setProperty('--theme-body-bg', d.defaults.body_bg || '#f8fafc');
                    canvas.style.setProperty('--theme-surface', d.defaults.surface_color || '#ffffff');
                    canvas.style.setProperty('--theme-card-bg', d.defaults.surface_color || '#ffffff');
                    canvas.style.setProperty('--theme-sidebar-bg', d.defaults.sidebar_color || '#FFFDFC');
                    canvas.style.setProperty('--theme-navbar-bg', d.defaults.navbar_color || '#ffffff');
                    canvas.style.setProperty('--theme-text-primary', '#0f172a');
                    canvas.style.setProperty('--theme-text-muted', '#64748b');
                    canvas.style.setProperty('--theme-border', '#e2e8f0');
                    canvas.style.backgroundColor = d.defaults.body_bg || '#f8fafc';
                }
                if (titleLabel && d) {
                    titleLabel.textContent = `${d.name} — Design System Preview`;
                }
                if (categoryLabel && d) {
                    categoryLabel.textContent = `${d.category} • ${d.tagline}`;
                }

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                    modalInstance.show();
                }
            });
        });

        const applyBtn = document.getElementById('btn_apply_modal_design');
        if (applyBtn) {
            applyBtn.addEventListener('click', function () {
                if (activePreviewDesignId) {
                    selectDesign(activePreviewDesignId);
                }
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) modalInstance.hide();
                }
            });
        }
    }

    /**
     * 10 Sidebar Design Cards
     */
    function bindSidebarCards() {
        document.querySelectorAll('.sidebar-style-card').forEach(card => {
            card.addEventListener('click', function () {
                const styleId = this.getAttribute('data-sidebar-style');
                if (!styleId) return;

                document.querySelectorAll('.sidebar-style-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                currentTheme.sidebar_style = styleId;
                updateLivePreview();
            });
        });

        // Sidebar behavior controls
        const sbWidth = document.getElementById('input_sidebar_width');
        if (sbWidth) {
            sbWidth.addEventListener('input', function () {
                if (!currentTheme.sidebar) currentTheme.sidebar = {};
                currentTheme.sidebar.width = parseInt(this.value, 10) || 280;
                const display = document.getElementById('display_sidebar_width');
                if (display) display.textContent = this.value + 'px';
                updateLivePreview();
            });
        }

        const sbHover = document.getElementById('input_sidebar_hover');
        if (sbHover) {
            sbHover.addEventListener('change', function () {
                if (!currentTheme.sidebar) currentTheme.sidebar = {};
                currentTheme.sidebar.expand_on_hover = this.checked;
            });
        }
    }

    /**
     * 10 Navbar Design Cards
     */
    function bindNavbarCards() {
        document.querySelectorAll('.navbar-style-card').forEach(card => {
            card.addEventListener('click', function () {
                const styleId = this.getAttribute('data-navbar-style');
                if (!styleId) return;

                document.querySelectorAll('.navbar-style-card').forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');

                currentTheme.navbar_style = styleId;
                updateLivePreview();
            });
        });

        // Navbar height control
        const nbHeight = document.getElementById('input_navbar_height');
        if (nbHeight) {
            nbHeight.addEventListener('input', function () {
                if (!currentTheme.navbar) currentTheme.navbar = {};
                currentTheme.navbar.height = parseInt(this.value, 10) || 68;
                const display = document.getElementById('display_navbar_height');
                if (display) display.textContent = this.value + 'px';
                updateLivePreview();
            });
        }
    }

    /**
     * Typography Inputs
     */
    function bindTypographyInputs() {
        const bodyFontSelect = document.getElementById('input_font_body');
        if (bodyFontSelect) {
            bodyFontSelect.addEventListener('change', function () {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.font_body = this.value;
                updateLivePreview();
            });
        }

        const headingFontSelect = document.getElementById('input_font_heading');
        if (headingFontSelect) {
            headingFontSelect.addEventListener('change', function () {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.font_heading = this.value;
                updateLivePreview();
            });
        }

        const baseSizeInput = document.getElementById('input_font_size_base');
        if (baseSizeInput) {
            baseSizeInput.addEventListener('input', function () {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.font_size_base = parseInt(this.value, 10) || 14;
                const display = document.getElementById('display_font_size_base');
                if (display) display.textContent = this.value + 'px';
                updateLivePreview();
            });
        }

        const lineHeightInput = document.getElementById('input_line_height');
        if (lineHeightInput) {
            lineHeightInput.addEventListener('input', function () {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.line_height = parseFloat(this.value) || 1.5;
                const display = document.getElementById('display_line_height');
                if (display) display.textContent = this.value;
                updateLivePreview();
            });
        }

        const letterSpacingInput = document.getElementById('input_letter_spacing');
        if (letterSpacingInput) {
            letterSpacingInput.addEventListener('input', function () {
                if (!currentTheme.typography) currentTheme.typography = {};
                currentTheme.typography.letter_spacing = parseFloat(this.value) || -0.2;
                const display = document.getElementById('display_letter_spacing');
                if (display) display.textContent = this.value + 'px';
                updateLivePreview();
            });
        }
    }

    /**
     * Border Radius & Shadow Presets
     */
    function bindRadiusAndShadow() {
        document.querySelectorAll('input[name="radius_preset"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    currentTheme.radius_preset = this.value;
                    updateLivePreview();
                }
            });
        });

        document.querySelectorAll('input[name="shadow_preset"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    currentTheme.shadow_preset = this.value;
                    updateLivePreview();
                }
            });
        });
    }

    /**
     * Layout Density & Appearance Mode
     */
    function bindDensityAndMode() {
        document.querySelectorAll('input[name="density"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    currentTheme.density = this.value;
                    updateLivePreview();
                }
            });
        });

        document.querySelectorAll('input[name="mode"]').forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.checked) {
                    currentTheme.mode = this.value;
                    updateLivePreview();
                }
            });
        });
    }

    /**
     * Component Specific Toggles
     */
    function bindComponentToggles() {
        const btnStyleSelect = document.getElementById('input_button_style');
        if (btnStyleSelect) {
            btnStyleSelect.addEventListener('change', function () {
                if (!currentTheme.components) currentTheme.components = {};
                currentTheme.components.button_style = this.value;
                updateLivePreview();
            });
        }

        const cardBorderCheck = document.getElementById('input_card_border');
        if (cardBorderCheck) {
            cardBorderCheck.addEventListener('change', function () {
                if (!currentTheme.components) currentTheme.components = {};
                currentTheme.components.card_border = this.checked;
                updateLivePreview();
            });
        }
    }

    /**
     * Synchronize All Form Fields with currentTheme Object
     */
    function syncAllFormInputs() {
        if (!currentTheme) return;

        // Mode Radio
        const modeRadio = document.querySelector(`input[name="mode"][value="${currentTheme.mode}"]`);
        if (modeRadio) modeRadio.checked = true;

        // Radius Radio
        const radRadio = document.querySelector(`input[name="radius_preset"][value="${currentTheme.radius_preset}"]`);
        if (radRadio) radRadio.checked = true;

        // Shadow Radio
        const shRadio = document.querySelector(`input[name="shadow_preset"][value="${currentTheme.shadow_preset}"]`);
        if (shRadio) shRadio.checked = true;

        // Density Radio
        const denRadio = document.querySelector(`input[name="density"][value="${currentTheme.density}"]`);
        if (denRadio) denRadio.checked = true;

        // Colors
        if (currentTheme.colors) {
            document.querySelectorAll('.theme-color-input-sync').forEach(container => {
                const key = container.getAttribute('data-color-key');
                const val = currentTheme.colors[key];
                if (val) {
                    const txt = container.querySelector('input[type="text"]');
                    const clr = container.querySelector('input[type="color"]');
                    if (txt) txt.value = val;
                    if (clr && /^#[0-9a-fA-F]{6}$/i.test(val)) clr.value = val;
                }
            });

            const grad = document.getElementById('input_brand_gradient');
            if (grad && currentTheme.colors.brand_gradient) {
                grad.value = currentTheme.colors.brand_gradient;
            }
        }

        // Typography
        if (currentTheme.typography) {
            const bodyF = document.getElementById('input_font_body');
            if (bodyF && currentTheme.typography.font_body) bodyF.value = currentTheme.typography.font_body;

            const headF = document.getElementById('input_font_heading');
            if (headF && currentTheme.typography.font_heading) headF.value = currentTheme.typography.font_heading;

            const sizeB = document.getElementById('input_font_size_base');
            if (sizeB && currentTheme.typography.font_size_base) {
                sizeB.value = currentTheme.typography.font_size_base;
                const disp = document.getElementById('display_font_size_base');
                if (disp) disp.textContent = currentTheme.typography.font_size_base + 'px';
            }
        }

        // Complete Admin Panel Design selection
        document.querySelectorAll('.theme-design-card').forEach(c => {
            const dId = c.getAttribute('data-design-id');
            const btn = c.querySelector('.btn-select-design');
            if (dId === currentTheme.design) {
                c.classList.add('selected');
                if (btn) btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Active';
            } else {
                c.classList.remove('selected');
                if (btn) btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Use Design';
            }
        });

        // Sidebar card selection
        document.querySelectorAll('.sidebar-style-card').forEach(c => {
            if (c.getAttribute('data-sidebar-style') === currentTheme.sidebar_style) {
                c.classList.add('selected');
            } else {
                c.classList.remove('selected');
            }
        });

        // Navbar card selection
        document.querySelectorAll('.navbar-style-card').forEach(c => {
            if (c.getAttribute('data-navbar-style') === currentTheme.navbar_style) {
                c.classList.add('selected');
            } else {
                c.classList.remove('selected');
            }
        });
    }

    /**
     * Instantly apply full visual theme to entire application and sidebar
     */
    function applyThemeToPage(t) {
        if (!t) return;

        const colors = t.colors || {};
        const typo = t.typography || {};
        const radius = radiusTokens[t.radius_preset || 'large'] || radiusTokens.large;
        const shadows = shadowTokens[t.shadow_preset || 'soft'] || shadowTokens.soft;

        const fontBody = fontFamilies[typo.font_body || 'Inter'] || fontFamilies.Inter;
        const fontHead = fontFamilies[typo.font_heading || 'Outfit'] || fontFamilies.Outfit;

        const primary = colors.primary || '#B9828F';
        const primaryHover = colors.primary_hover || '#A96F7D';
        const primaryActive = colors.primary_active || '#985C6A';
        const secondary = colors.secondary || '#292326';
        const accent = colors.accent || '#C8A96B';
        const sidebarBg = colors.sidebar_bg || (t.mode === 'dark' ? '#090A0D' : '#FFFDFC');
        const navbarBg = colors.navbar_bg || (t.mode === 'dark' ? '#141720' : '#FFFFFF');
        const bodyBg = colors.body_bg || (t.mode === 'dark' ? '#0D0E12' : '#F8F6F4');
        const surface = colors.surface || (t.mode === 'dark' ? '#141720' : '#FFFFFF');
        const surfaceSec = colors.surface_secondary || (t.mode === 'dark' ? '#1C212B' : '#F3ECE7');
        const cardBg = colors.card_bg || surface;
        const textPrimary = colors.text_primary || (t.mode === 'dark' ? '#F1F5F9' : '#332D2F');
        const textSecondary = colors.text_secondary || (t.mode === 'dark' ? '#94A3B8' : '#817477');
        const textMuted = colors.text_muted || (t.mode === 'dark' ? '#64748B' : '#9A8E91');
        const border = colors.border || (t.mode === 'dark' ? 'rgba(255,255,255,0.08)' : '#E9DFD8');
        const success = colors.success || '#5D9078';
        const warning = colors.warning || '#C59A55';
        const danger = colors.danger || '#B56B70';
        const info = colors.info || '#62829A';
        const brandGrad = colors.brand_gradient || `linear-gradient(135deg, ${primary} 0%, ${accent} 100%)`;

        const isDarkSidebar = isHexColorDark(sidebarBg) || t.mode === 'dark';
        const sidebarTextColor = isDarkSidebar ? '#FFFFFF' : textPrimary;
        const sidebarTextMuted = isDarkSidebar ? 'rgba(255, 255, 255, 0.70)' : textMuted;
        const sidebarIconColor = isDarkSidebar ? 'rgba(255, 255, 255, 0.75)' : textSecondary;
        const sidebarHoverBg = isDarkSidebar ? 'rgba(255, 255, 255, 0.08)' : 'rgba(0, 0, 0, 0.04)';
        const sidebarActiveBg = isDarkSidebar ? 'linear-gradient(135deg, rgba(255,255,255,0.14) 0%, rgba(255,255,255,0.04) 100%)' : `linear-gradient(135deg, rgba(${hexToRgb(primary)}, 0.14) 0%, rgba(${hexToRgb(accent)}, 0.08) 100%)`;
        const sidebarActiveText = isDarkSidebar ? '#FFFFFF' : primary;
        const sidebarBorder = isDarkSidebar ? 'rgba(255, 255, 255, 0.12)' : border;
        const sidebarDrawerBg = isDarkSidebar ? 'rgba(0, 0, 0, 0.28)' : 'rgba(0, 0, 0, 0.025)';

        // Set Root Attributes
        document.documentElement.setAttribute('data-theme-id', t.id || t.design || 'pearl-royale');
        document.documentElement.setAttribute('data-theme-design', t.design || t.id || 'pearl-royale');
        document.documentElement.setAttribute('data-theme-mode', t.mode || 'light');
        document.documentElement.setAttribute('data-sidebar-theme', isDarkSidebar ? 'dark' : 'light');
        document.documentElement.setAttribute('data-density', t.density || 'comfortable');

        // Set Direct Style Properties on Root
        const s = document.documentElement.style;
        s.setProperty('--theme-primary', primary);
        s.setProperty('--theme-primary-hover', primaryHover);
        s.setProperty('--theme-primary-active', primaryActive);
        s.setProperty('--theme-secondary', secondary);
        s.setProperty('--theme-accent', accent);
        s.setProperty('--theme-brand-gradient', brandGrad);
        s.setProperty('--theme-body-bg', bodyBg);
        s.setProperty('--theme-surface', surface);
        s.setProperty('--theme-surface-secondary', surfaceSec);
        s.setProperty('--theme-card-bg', cardBg);
        s.setProperty('--theme-sidebar-bg', sidebarBg);
        s.setProperty('--theme-navbar-bg', navbarBg);
        s.setProperty('--theme-text-primary', textPrimary);
        s.setProperty('--theme-text-secondary', textSecondary);
        s.setProperty('--theme-text-muted', textMuted);
        s.setProperty('--theme-border', border);
        s.setProperty('--theme-border-focus', primary);

        s.setProperty('--theme-sidebar-is-dark', isDarkSidebar ? '1' : '0');
        s.setProperty('--theme-sidebar-text', sidebarTextColor);
        s.setProperty('--theme-sidebar-text-muted', sidebarTextMuted);
        s.setProperty('--theme-sidebar-icon', sidebarIconColor);
        s.setProperty('--theme-sidebar-hover-bg', sidebarHoverBg);
        s.setProperty('--theme-sidebar-active-bg', sidebarActiveBg);
        s.setProperty('--theme-sidebar-active-text', sidebarActiveText);
        s.setProperty('--theme-sidebar-border', sidebarBorder);
        s.setProperty('--theme-sidebar-drawer-bg', sidebarDrawerBg);

        s.setProperty('--lx-sb-bg', sidebarBg);
        s.setProperty('--lx-sb-bg-dark', sidebarBg);
        s.setProperty('--lx-sb-bg-mid', sidebarBg);
        s.setProperty('--lx-sb-bg-base', sidebarBg);
        s.setProperty('--lx-sb-gold', accent);
        s.setProperty('--lx-sb-gold-border', sidebarBorder);
        s.setProperty('--lx-sb-rose-primary', primary);
        s.setProperty('--lx-sb-rose-hover', primaryHover);
        s.setProperty('--lx-sb-slate-muted', sidebarTextMuted);
        s.setProperty('--lx-sb-slate-icon', sidebarIconColor);
        s.setProperty('--lx-sb-white', sidebarTextColor);
        s.setProperty('--lx-sb-text-main', sidebarTextColor);

        s.setProperty('--theme-radius-sm', radius.sm);
        s.setProperty('--theme-radius-md', radius.md);
        s.setProperty('--theme-radius-lg', radius.lg);
        s.setProperty('--theme-radius-xl', radius.xl);
        s.setProperty('--theme-radius-pill', radius.pill);
        s.setProperty('--theme-shadow-card', shadows.card);
        s.setProperty('--theme-shadow-dropdown', shadows.dropdown);
        s.setProperty('--theme-shadow-modal', shadows.modal);
        s.setProperty('--theme-shadow-sidebar', shadows.sidebar);
        s.setProperty('--theme-shadow-navbar', shadows.navbar);

        s.setProperty('--theme-font-body', fontBody);
        s.setProperty('--theme-font-heading', fontHead);

        const primaryRgb = hexToRgb(primary);
        const accentRgb = hexToRgb(accent);
        const secondaryRgb = hexToRgb(secondary);
        const surfaceRgb = hexToRgb(surface);
        const successRgb = hexToRgb(success);
        const warningRgb = hexToRgb(warning);
        const dangerRgb = hexToRgb(danger);
        const infoRgb = hexToRgb(info);
        const bodyBgRgb = hexToRgb(bodyBg);
        const textPrimaryRgb = hexToRgb(textPrimary);
        const surfaceSecRgb = hexToRgb(surfaceSec);

        s.setProperty('--theme-primary-rgb', primaryRgb);
        s.setProperty('--theme-accent-rgb', accentRgb);
        s.setProperty('--theme-secondary-rgb', secondaryRgb);
        s.setProperty('--theme-surface-rgb', surfaceRgb);
        s.setProperty('--theme-primary-subtle', `rgba(${primaryRgb}, 0.15)`);
        s.setProperty('--theme-accent-subtle', `rgba(${accentRgb}, 0.15)`);
        s.setProperty('--theme-btn-primary-text', isHexColorDark(primary) ? '#FFFFFF' : '#111318');
        s.setProperty('--theme-btn-primary-hover-text', isHexColorDark(primaryHover) ? '#FFFFFF' : '#111318');
        s.setProperty('--bs-primary', primary);
        s.setProperty('--bs-primary-rgb', primaryRgb);
        s.setProperty('--bs-primary-bg-subtle', `rgba(${primaryRgb}, 0.12)`);
        s.setProperty('--bs-primary-border-subtle', `rgba(${primaryRgb}, 0.3)`);
        s.setProperty('--bs-primary-text-emphasis', isHexColorDark(primary) ? '#FFFFFF' : '#111318');
        s.setProperty('--bs-secondary', secondary);
        s.setProperty('--bs-secondary-rgb', secondaryRgb);
        s.setProperty('--bs-light-rgb', surfaceSecRgb);
        s.setProperty('--bs-dark-rgb', secondaryRgb);
        s.setProperty('--bs-success', success);
        s.setProperty('--bs-success-rgb', successRgb);
        s.setProperty('--bs-success-bg-subtle', `rgba(${successRgb}, 0.12)`);
        s.setProperty('--bs-success-border-subtle', `rgba(${successRgb}, 0.3)`);
        s.setProperty('--bs-success-text-emphasis', success);
        s.setProperty('--bs-warning', warning);
        s.setProperty('--bs-warning-rgb', warningRgb);
        s.setProperty('--bs-warning-bg-subtle', `rgba(${warningRgb}, 0.12)`);
        s.setProperty('--bs-warning-border-subtle', `rgba(${warningRgb}, 0.3)`);
        s.setProperty('--bs-warning-text-emphasis', warning);
        s.setProperty('--bs-danger', danger);
        s.setProperty('--bs-danger-rgb', dangerRgb);
        s.setProperty('--bs-danger-bg-subtle', `rgba(${dangerRgb}, 0.12)`);
        s.setProperty('--bs-danger-border-subtle', `rgba(${dangerRgb}, 0.3)`);
        s.setProperty('--bs-danger-text-emphasis', danger);
        s.setProperty('--bs-info', info);
        s.setProperty('--bs-info-rgb', infoRgb);
        s.setProperty('--bs-info-bg-subtle', `rgba(${infoRgb}, 0.12)`);
        s.setProperty('--bs-info-border-subtle', `rgba(${infoRgb}, 0.3)`);
        s.setProperty('--bs-info-text-emphasis', info);
        s.setProperty('--bs-body-bg', bodyBg);
        s.setProperty('--bs-body-bg-rgb', bodyBgRgb);
        s.setProperty('--bs-body-color', textPrimary);
        s.setProperty('--bs-body-color-rgb', textPrimaryRgb);
        s.setProperty('--bs-emphasis-color', textPrimary);
        s.setProperty('--bs-emphasis-color-rgb', textPrimaryRgb);
        s.setProperty('--bs-secondary-color', textSecondary);
        s.setProperty('--bs-tertiary-color', textMuted);
        s.setProperty('--bs-secondary-bg', surface);
        s.setProperty('--bs-secondary-bg-rgb', surfaceRgb);
        s.setProperty('--bs-tertiary-bg', surfaceSec);
        s.setProperty('--bs-tertiary-bg-rgb', surfaceSecRgb);
        s.setProperty('--bs-secondary-bg-subtle', surfaceSec);
        s.setProperty('--bs-secondary-text-emphasis', textSecondary);
        s.setProperty('--bs-secondary-border-subtle', border);
        s.setProperty('--bs-border-color', border);
        s.setProperty('--bs-link-color', primary);
        s.setProperty('--bs-link-color-rgb', primaryRgb);
        s.setProperty('--bs-link-hover-color', primaryHover);
        s.setProperty('--bs-primary-text-emphasis', isHexColorDark(primary) ? '#FFFFFF' : '#111318');
        s.setProperty('--bs-primary-bg-subtle', `rgba(${primaryRgb}, 0.12)`);
        s.setProperty('--bs-primary-border-subtle', `rgba(${primaryRgb}, 0.3)`);

        s.setProperty('--luxury-primary-rgb', primaryRgb);
        s.setProperty('--luxury-accent-rgb', accentRgb);
        s.setProperty('--luxury-secondary-rgb', secondaryRgb);
        s.setProperty('--luxury-surface-rgb', surfaceRgb);
        s.setProperty('--luxury-rose', primary);
        s.setProperty('--luxury-rose-hover', primaryHover);
        s.setProperty('--luxury-rose-active', primaryActive);
        s.setProperty('--luxury-gold', accent);
        s.setProperty('--luxury-gold-light', accent);
        s.setProperty('--luxury-plum', secondary);
        s.setProperty('--luxury-bg', bodyBg);
        s.setProperty('--luxury-surface', surface);
        s.setProperty('--luxury-surface-subtle', surfaceSec);
        s.setProperty('--luxury-muted-bg', surfaceSec);
        s.setProperty('--luxury-text', textPrimary);
        s.setProperty('--luxury-muted', textMuted);
        s.setProperty('--luxury-border', border);

        // Inject / Update High-Specificity Dynamic Style Block
        let dynamicStyle = document.getElementById('theme-engine-live-style');
        if (!dynamicStyle) {
            dynamicStyle = document.createElement('style');
            dynamicStyle.id = 'theme-engine-live-style';
            document.head.appendChild(dynamicStyle);
        }

        dynamicStyle.textContent = `
            :root {
                --theme-primary: ${primary} !important;
                --theme-primary-hover: ${primaryHover} !important;
                --theme-primary-active: ${primaryActive} !important;
                --theme-secondary: ${secondary} !important;
                --theme-accent: ${accent} !important;
                --theme-brand-gradient: ${brandGrad} !important;
                --theme-body-bg: ${bodyBg} !important;
                --theme-surface: ${surface} !important;
                --theme-surface-secondary: ${surfaceSec} !important;
                --theme-card-bg: ${cardBg} !important;
                --theme-sidebar-bg: ${sidebarBg} !important;
                --theme-navbar-bg: ${navbarBg} !important;
                --theme-text-primary: ${textPrimary} !important;
                --theme-text-secondary: ${textSecondary} !important;
                --theme-text-muted: ${textMuted} !important;
                --theme-border: ${border} !important;
                --theme-border-focus: ${primary} !important;
                --theme-primary-rgb: ${primaryRgb} !important;
                --theme-accent-rgb: ${accentRgb} !important;
                --theme-secondary-rgb: ${secondaryRgb} !important;
                --theme-surface-rgb: ${surfaceRgb} !important;
                --theme-btn-primary-text: ${isHexColorDark(primary) ? '#FFFFFF' : '#111318'} !important;
                --theme-btn-primary-hover-text: ${isHexColorDark(primaryHover) ? '#FFFFFF' : '#111318'} !important;
                --bs-primary: ${primary} !important;
                --bs-primary-rgb: ${primaryRgb} !important;
                --bs-primary-bg-subtle: rgba(${primaryRgb}, 0.12) !important;
                --bs-primary-border-subtle: rgba(${primaryRgb}, 0.3) !important;
                --bs-primary-text-emphasis: ${isHexColorDark(primary) ? '#FFFFFF' : '#111318'} !important;
                --bs-secondary: ${secondary} !important;
                --bs-secondary-rgb: ${secondaryRgb} !important;
                --bs-light-rgb: ${surfaceSecRgb} !important;
                --bs-dark-rgb: ${secondaryRgb} !important;
                --bs-success: ${success} !important;
                --bs-success-rgb: ${successRgb} !important;
                --bs-success-bg-subtle: rgba(${successRgb}, 0.12) !important;
                --bs-success-border-subtle: rgba(${successRgb}, 0.3) !important;
                --bs-success-text-emphasis: ${success} !important;
                --bs-warning: ${warning} !important;
                --bs-warning-rgb: ${warningRgb} !important;
                --bs-warning-bg-subtle: rgba(${warningRgb}, 0.12) !important;
                --bs-warning-border-subtle: rgba(${warningRgb}, 0.3) !important;
                --bs-warning-text-emphasis: ${warning} !important;
                --bs-danger: ${danger} !important;
                --bs-danger-rgb: ${dangerRgb} !important;
                --bs-danger-bg-subtle: rgba(${dangerRgb}, 0.12) !important;
                --bs-danger-border-subtle: rgba(${dangerRgb}, 0.3) !important;
                --bs-danger-text-emphasis: ${danger} !important;
                --bs-info: ${info} !important;
                --bs-info-rgb: ${infoRgb} !important;
                --bs-info-bg-subtle: rgba(${infoRgb}, 0.12) !important;
                --bs-info-border-subtle: rgba(${infoRgb}, 0.3) !important;
                --bs-info-text-emphasis: ${info} !important;
                --bs-body-bg: ${bodyBg} !important;
                --bs-body-bg-rgb: ${bodyBgRgb} !important;
                --bs-body-color: ${textPrimary} !important;
                --bs-body-color-rgb: ${textPrimaryRgb} !important;
                --bs-emphasis-color: ${textPrimary} !important;
                --bs-emphasis-color-rgb: ${textPrimaryRgb} !important;
                --bs-secondary-color: ${textSecondary} !important;
                --bs-tertiary-color: ${textMuted} !important;
                --bs-secondary-bg: ${surface} !important;
                --bs-secondary-bg-rgb: ${surfaceRgb} !important;
                --bs-tertiary-bg: ${surfaceSec} !important;
                --bs-tertiary-bg-rgb: ${surfaceSecRgb} !important;
                --bs-secondary-bg-subtle: ${surfaceSec} !important;
                --bs-secondary-text-emphasis: ${textSecondary} !important;
                --bs-secondary-border-subtle: ${border} !important;
                --bs-border-color: ${border} !important;
                --bs-link-color: ${primary} !important;
                --bs-link-color-rgb: ${primaryRgb} !important;
                --bs-link-hover-color: ${primaryHover} !important;
                --bs-primary-text-emphasis: ${isHexColorDark(primary) ? '#FFFFFF' : '#111318'} !important;
                --bs-primary-bg-subtle: rgba(${primaryRgb}, 0.12) !important;
                --bs-primary-border-subtle: rgba(${primaryRgb}, 0.3) !important;

                --luxury-rose: ${primary} !important;
                --luxury-rose-hover: ${primaryHover} !important;
                --luxury-rose-active: ${primaryActive} !important;
                --luxury-gold: ${accent} !important;
                --luxury-gold-light: ${accent} !important;
                --luxury-plum: ${secondary} !important;
                --luxury-bg: ${bodyBg} !important;
                --luxury-surface: ${surface} !important;
                --luxury-surface-subtle: ${surfaceSec} !important;
                --luxury-muted-bg: ${surfaceSec} !important;
                --luxury-text: ${textPrimary} !important;
                --luxury-muted: ${textMuted} !important;
                --luxury-border: ${border} !important;
                --luxury-primary-rgb: ${primaryRgb} !important;
                --luxury-accent-rgb: ${accentRgb} !important;
                --luxury-secondary-rgb: ${secondaryRgb} !important;
                --luxury-surface-rgb: ${surfaceRgb} !important;

                --theme-sidebar-bg: ${sidebarBg} !important;
                --theme-sidebar-text: ${sidebarTextColor} !important;
                --theme-sidebar-text-muted: ${sidebarTextMuted} !important;
                --theme-sidebar-icon: ${sidebarIconColor} !important;
                --theme-sidebar-hover-bg: ${sidebarHoverBg} !important;
                --theme-sidebar-active-bg: ${sidebarActiveBg} !important;
                --theme-sidebar-active-text: ${sidebarActiveText} !important;
                --theme-sidebar-border: ${sidebarBorder} !important;
                --theme-sidebar-drawer-bg: ${sidebarDrawerBg} !important;
                --lx-sb-bg: ${sidebarBg} !important;
                --lx-sb-gold: ${accent} !important;
                --lx-sb-rose-primary: ${primary} !important;
            }

            /* 1. Global Canvas & Typography */
            body, #wrapper, #page-content-wrapper, main.container-fluid {
                background-color: ${bodyBg} !important;
                color: ${textPrimary} !important;
            }
            h1, h2, h3, h4, h5, h6, .page-title, .project-title, .dashboard-card-title, .card-title {
                color: ${textPrimary} !important;
            }
            a {
                color: ${primary};
            }
            a:hover {
                color: ${primaryHover};
            }

            /* 2. All Page Headers & Hero Banners */
            .project-header,
            .lx-dashboard-header,
            .exam-header-banner,
            .page-header-luxury,
            .builder-hero-banner,
            .vacation-hero,
            .planner-hero,
            .theme-builder-header {
                background: ${brandGrad} !important;
                border-color: rgba(${accentRgb}, 0.35) !important;
                box-shadow: 0 10px 30px rgba(${secondaryRgb}, 0.35) !important;
            }
            .project-header::after,
            .lx-dashboard-header::after,
            .exam-header-banner::after,
            .page-header-luxury::after {
                background: radial-gradient(circle, rgba(${accentRgb}, 0.22) 0%, transparent 70%) !important;
            }
            .lx-title-highlight {
                color: ${accent} !important;
            }
            .lx-eyebrow, .exam-eyebrow {
                background: rgba(${accentRgb}, 0.18) !important;
                border: 1px solid rgba(${accentRgb}, 0.35) !important;
                color: ${accent} !important;
            }
            .lx-dot, .exam-live-dot {
                background: ${accent} !important;
                box-shadow: 0 0 10px ${accent} !important;
            }
            .lx-role-tag {
                background: rgba(${primaryRgb}, 0.28) !important;
                border-color: rgba(${primaryRgb}, 0.45) !important;
                color: #ffffff !important;
            }
            .card-header,
            .dashboard-card-header,
            .category-card-header {
                background-color: ${surfaceSec} !important;
                border-bottom-color: ${border} !important;
            }
            .card-header .card-title,
            .dashboard-card-header .dashboard-card-title {
                color: ${textPrimary} !important;
            }
            .modal-header {
                background-color: ${surfaceSec} !important;
                border-bottom-color: ${border} !important;
            }
            .modal-title {
                color: ${textPrimary} !important;
            }
            .top-navbar {
                background: ${navbarBg} !important;
                border-bottom-color: ${border} !important;
            }

            /* 3. Sidebar Navigation */
            .sidebar, aside.sidebar, #sidebar {
                background: ${sidebarBg} !important;
                border-right-color: ${sidebarBorder} !important;
                color: ${sidebarTextColor} !important;
            }
            .sidebar-header {
                background: ${sidebarBg} !important;
                border-bottom-color: ${sidebarBorder} !important;
            }
            .sidebar-company-name {
                color: ${sidebarTextColor} !important;
            }
            .sidebar .nav-link {
                color: ${sidebarTextMuted} !important;
            }
            .sidebar .nav-link i {
                color: ${sidebarIconColor} !important;
            }
            .sidebar .nav-link:hover {
                color: ${primary} !important;
                background: ${sidebarHoverBg} !important;
                border-left-color: ${primary} !important;
            }
            .sidebar .nav-link:hover i {
                color: ${primary} !important;
            }
            .sidebar .nav-link.active {
                color: ${sidebarActiveText} !important;
                background: ${sidebarActiveBg} !important;
                border-left: 3px solid ${isDarkSidebar ? accent : primary} !important;
            }
            .sidebar .nav-link.active i {
                color: ${isDarkSidebar ? accent : primary} !important;
            }
            .category-title {
                color: ${accent} !important;
            }
            .dropdown-title {
                color: ${sidebarTextMuted} !important;
            }
            .dropdown-title i {
                color: ${sidebarIconColor} !important;
            }
            .dropdown-header:hover {
                background: ${sidebarHoverBg} !important;
            }
            .dropdown-header:hover .dropdown-title,
            .dropdown-header:hover .dropdown-title i {
                color: ${primary} !important;
            }
            .dropdown-header.active {
                background: ${sidebarHoverBg} !important;
                border-left: 3px solid ${accent} !important;
            }
            .dropdown-header.active .dropdown-title {
                color: ${sidebarTextColor} !important;
            }
            .dropdown-header.active .dropdown-title i {
                color: ${accent} !important;
            }
            .dropdown-content {
                background: ${sidebarDrawerBg} !important;
                border-left-color: ${sidebarBorder} !important;
            }
            .sidebar .dropdown-item {
                color: ${sidebarTextMuted} !important;
            }
            .sidebar .dropdown-item:hover {
                color: ${primary} !important;
                background: ${sidebarHoverBg} !important;
            }
            .sidebar .dropdown-item.active {
                color: ${sidebarActiveText} !important;
                background: ${sidebarActiveBg} !important;
            }

            /* 4. All Tables & Data Grids */
            table, .table, .modern-table, .table-custom {
                background: ${surface} !important;
                color: ${textPrimary} !important;
            }
            .table-responsive {
                background: ${surface} !important;
                border-color: ${border} !important;
            }
            table thead, .table thead, .modern-table thead, .table-custom thead {
                background-color: ${surfaceSec} !important;
            }
            table thead th, .table thead th, .modern-table thead th, .table-custom thead th {
                background-color: ${surfaceSec} !important;
                color: ${textSecondary} !important;
                border-bottom: 2px solid ${border} !important;
            }
            table td, .table td, .modern-table td, .table-custom td {
                color: ${textPrimary} !important;
                border-bottom: 1px solid ${border} !important;
            }
            table tbody tr:hover, .table tbody tr:hover, .modern-table tbody tr:hover, .table-hover tbody tr:hover {
                background-color: rgba(${primaryRgb}, 0.08) !important;
            }
            .table-striped > tbody > tr:nth-of-type(odd) > * {
                background-color: rgba(${primaryRgb}, 0.025) !important;
            }
            .table-bordered, .table-bordered th, .table-bordered td {
                border-color: ${border} !important;
            }
            .action-btn-table, .table-action-btn {
                background: ${surface} !important;
                border-color: ${border} !important;
                color: ${textPrimary} !important;
            }
            .action-btn-table:hover, .table-action-btn:hover, .action-btn-table.view:hover {
                background: ${primary} !important;
                border-color: ${primary} !important;
                color: #ffffff !important;
                box-shadow: 0 3px 10px rgba(${primaryRgb}, 0.35) !important;
            }
            .action-btn-table.edit:hover {
                background: ${accent} !important;
                border-color: ${accent} !important;
                color: #ffffff !important;
            }
            .pagination .page-item .page-link {
                background-color: ${surface} !important;
                border-color: ${border} !important;
                color: ${textPrimary} !important;
            }
            .pagination .page-item:hover .page-link {
                background-color: ${surfaceSec} !important;
                color: ${primary} !important;
                border-color: ${primary} !important;
            }
            .pagination .page-item.active .page-link {
                background-color: ${primary} !important;
                border-color: ${primary} !important;
                color: #ffffff !important;
                box-shadow: 0 3px 10px rgba(${primaryRgb}, 0.35) !important;
            }

            /* 5. All Buttons */
            .btn-primary,
            .btn-header-action,
            .btn-lx-action-primary,
            .btn-save,
            .btn-submit {
                background: ${primary} !important;
                border-color: ${primary} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(${primaryRgb}, 0.32) !important;
            }
            .btn-primary:hover,
            .btn-primary:focus,
            .btn-header-action:hover,
            .btn-lx-action-primary:hover {
                background: ${primaryHover} !important;
                border-color: ${primaryHover} !important;
                color: #ffffff !important;
                box-shadow: 0 6px 20px rgba(${primaryRgb}, 0.45) !important;
            }
            .btn-primary:active,
            .btn-header-action:active,
            .btn-lx-action-primary:active {
                background: ${primaryActive} !important;
                border-color: ${primaryActive} !important;
            }
            .btn-secondary {
                background: ${surfaceSec} !important;
                border-color: ${border} !important;
                color: ${textPrimary} !important;
            }
            .btn-secondary:hover {
                background: ${border} !important;
                border-color: ${border} !important;
                color: ${textPrimary} !important;
            }
            .btn-outline-primary {
                background: transparent !important;
                border-color: ${primary} !important;
                color: ${primary} !important;
            }
            .btn-outline-primary:hover,
            .btn-outline-primary:focus {
                background: rgba(${primaryRgb}, 0.12) !important;
                border-color: ${primary} !important;
                color: ${primary} !important;
            }
            .btn-luxury,
            .btn-gold {
                background: ${accent} !important;
                border-color: ${accent} !important;
                color: #ffffff !important;
                box-shadow: 0 4px 14px rgba(${accentRgb}, 0.35) !important;
            }
            .btn-luxury:hover,
            .btn-gold:hover {
                background: ${accent} !important;
                border-color: ${accent} !important;
                box-shadow: 0 6px 20px rgba(${accentRgb}, 0.45) !important;
            }

            /* 6. Form Controls, Cards, Badges & Navigation */
            .form-control, .form-select, textarea.form-control {
                background-color: ${surface} !important;
                border-color: ${border} !important;
                color: ${textPrimary} !important;
            }
            .form-control:focus, .form-select:focus, textarea.form-control:focus {
                border-color: ${primary} !important;
                box-shadow: 0 0 0 3.5px rgba(${primaryRgb}, 0.22) !important;
            }
            .form-check-input:checked {
                background-color: ${primary} !important;
                border-color: ${primary} !important;
                box-shadow: 0 0 0 2.5px rgba(${primaryRgb}, 0.22) !important;
            }
            .form-switch .form-check-input:checked {
                background-color: ${primary} !important;
                border-color: ${primary} !important;
            }
            .card, .stat-card, .filter-card, .dashboard-card, .card-luxury {
                background: ${cardBg} !important;
                border-color: ${border} !important;
            }
            .card:hover, .card-luxury:hover {
                border-color: rgba(${primaryRgb}, 0.30) !important;
            }
            .stat-card:hover {
                border-color: rgba(${primaryRgb}, 0.35) !important;
            }
            .badge.bg-primary, .badge.bg-primary-subtle {
                background-color: rgba(${primaryRgb}, 0.14) !important;
                color: ${primary} !important;
                border: 1px solid rgba(${primaryRgb}, 0.28) !important;
            }
            .badge-luxury, .badge-gold {
                background-color: rgba(${accentRgb}, 0.14) !important;
                color: ${accent} !important;
                border: 1px solid rgba(${accentRgb}, 0.28) !important;
            }
            .nav-tabs .nav-link:hover {
                color: ${primary} !important;
            }
            .nav-tabs .nav-link.active {
                color: ${primary} !important;
                border-bottom-color: ${primary} !important;
            }
            .nav-pills .nav-link.active {
                background-color: ${primary} !important;
                color: #ffffff !important;
            }
            .quick-action-link:hover {
                border-color: ${primary} !important;
                box-shadow: 0 4px 14px rgba(${primaryRgb}, 0.20) !important;
                color: ${primary} !important;
            }
        `;
    }

    /**
     * UPDATE LIVE PREVIEW (Instantaneous Application-Wide Reflection)
     */
    function updateLivePreview() {
        if (!currentTheme) return;

        // Apply immediately to the real application and sidebar
        applyThemeToPage(currentTheme);

        const previewBox = document.getElementById('themeLivePreviewContainer');
        if (previewBox) {
            const colors = currentTheme.colors || {};
            const typo = currentTheme.typography || {};
            const radius = radiusTokens[currentTheme.radius_preset || 'large'] || radiusTokens.large;
            const shadows = shadowTokens[currentTheme.shadow_preset || 'soft'] || shadowTokens.soft;

            const fontBody = fontFamilies[typo.font_body || 'Inter'] || fontFamilies.Inter;
            const fontHead = fontFamilies[typo.font_heading || 'Outfit'] || fontFamilies.Outfit;

            previewBox.setAttribute('data-theme-id', currentTheme.id || currentTheme.design || 'pearl-royale');
            previewBox.setAttribute('data-theme-design', currentTheme.design || currentTheme.id || 'pearl-royale');
            previewBox.setAttribute('data-sidebar-style', currentTheme.sidebar_style || 'classic');
            previewBox.setAttribute('data-navbar-style', currentTheme.navbar_style || 'classic');
            previewBox.setAttribute('data-theme-mode', currentTheme.mode || 'light');
            previewBox.setAttribute('data-density', currentTheme.density || 'comfortable');

            previewBox.style.setProperty('--theme-primary', colors.primary || '#B9828F');
            previewBox.style.setProperty('--theme-primary-hover', colors.primary_hover || '#A96F7D');
            previewBox.style.setProperty('--theme-secondary', colors.secondary || '#292326');
            previewBox.style.setProperty('--theme-accent', colors.accent || '#C8A96B');
            previewBox.style.setProperty('--theme-brand-gradient', colors.brand_gradient || `linear-gradient(135deg, ${colors.primary} 0%, ${colors.accent} 100%)`);
            previewBox.style.setProperty('--theme-body-bg', colors.body_bg || '#F8F6F4');
            previewBox.style.setProperty('--theme-surface', colors.surface || '#FFFFFF');
            previewBox.style.setProperty('--theme-card-bg', colors.card_bg || '#FFFFFF');
            previewBox.style.setProperty('--theme-sidebar-bg', colors.sidebar_bg || '#FFFDFC');
            previewBox.style.setProperty('--theme-navbar-bg', colors.navbar_bg || '#FFFFFF');
            previewBox.style.setProperty('--theme-text-primary', colors.text_primary || '#332D2F');
            previewBox.style.setProperty('--theme-text-muted', colors.text_muted || '#817477');
            previewBox.style.setProperty('--theme-border', colors.border || '#E9DFD8');

            previewBox.style.setProperty('--theme-font-body', fontBody);
            previewBox.style.setProperty('--theme-font-heading', fontHead);

            previewBox.style.setProperty('--theme-radius-sm', radius.sm);
            previewBox.style.setProperty('--theme-radius-md', radius.md);
            previewBox.style.setProperty('--theme-radius-lg', radius.lg);
            previewBox.style.setProperty('--theme-radius-xl', radius.xl);
            previewBox.style.setProperty('--theme-radius-pill', radius.pill);
        }

        // Update typography sample card
        const typoSample = document.getElementById('typography_preview_box');
        if (typoSample) {
            const fontBody = fontFamilies[currentTheme.typography?.font_body || 'Inter'] || fontFamilies.Inter;
            const fontHead = fontFamilies[currentTheme.typography?.font_heading || 'Outfit'] || fontFamilies.Outfit;
            typoSample.style.fontFamily = fontBody;
            const headSample = typoSample.querySelector('.typo-sample-heading');
            if (headSample) headSample.style.fontFamily = fontHead;
        }
    }

    /**
     * WCAG Contrast Checker & Alert
     */
    function checkContrastSafety() {
        const textCol = currentTheme.colors?.text_primary || '#0f172a';
        const bgCol = currentTheme.colors?.surface || currentTheme.colors?.body_bg || '#ffffff';
        const alertBox = document.getElementById('contrast_warning_banner');

        if (!alertBox) return;

        const ratio = calculateContrastRatio(textCol, bgCol);
        if (ratio < 4.5) {
            alertBox.style.display = 'flex';
            alertBox.innerHTML = `
                <i class="fa-solid fa-triangle-exclamation text-warning fs-5 me-2 mt-0.5"></i>
                <div>
                    <div class="fw-bold">Low Color Contrast Notice (Ratio: ${ratio.toFixed(2)}:1)</div>
                    <div class="small">The combination of text color (<code>${textCol}</code>) and surface background (<code>${bgCol}</code>) does not meet WCAG AA standards (minimum 4.5:1). Important text may be difficult to read.</div>
                </div>
            `;
        } else {
            alertBox.style.display = 'none';
        }
    }

    /**
     * Save Theme Configuration (Universal Multi-Client Scoped Handler)
     */
    function saveThemeConfiguration(themePayload, btnElement) {
        if (isSaving) return;
        isSaving = true;

        const payload = themePayload || currentTheme;
        const designId = payload.id || payload.design || 'pearl-royale';

        let originalHtml = '';
        if (btnElement) {
            originalHtml = btnElement.innerHTML;
            btnElement.disabled = true;
            btnElement.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> Saving Design...';
        }

        const appUrl = (window.APP_URL || '').replace(/\/$/, '');
        const targetUrl = appUrl ? `${appUrl}/settings/theme/apply` : '/settings/theme/apply';

        // Get CSRF Token
        const csrfToken = (window.Api && typeof window.Api.getCsrfToken === 'function')
            ? window.Api.getCsrfToken()
            : (document.querySelector('meta[name="csrf-token"]')?.content || '');

        const postBody = {
            theme_id: designId,
            theme: payload,
            csrf_token: csrfToken,
            _csrf: csrfToken
        };

        fetch(targetUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(postBody)
        })
        .then(response => {
            if (!response.ok && response.status !== 200) {
                throw new Error(`HTTP Error ${response.status}`);
            }
            return response.json();
        })
        .then(res => {
            if (btnElement) {
                btnElement.disabled = false;
                btnElement.innerHTML = originalHtml;
            }
            isSaving = false;

            if (res && res.success) {
                if (typeof window.showToast === 'function') {
                    window.showToast(res.message || `Theme '${payload.name || designId}' applied successfully!`, 'success');
                }
                const floatingBar = document.getElementById('floatingLivePreviewBar');
                if (floatingBar) floatingBar.classList.add('d-none');

                setTimeout(() => {
                    window.location.reload();
                }, 600);
            } else {
                const validationDetails = res?.errors && typeof res.errors === 'object'
                    ? Object.values(res.errors).join(' ')
                    : '';
                const msg = [res?.message || 'Failed to save theme configuration.', validationDetails]
                    .filter(Boolean)
                    .join(' ');
                if (typeof window.showToast === 'function') {
                    window.showToast(msg, 'danger');
                }
            }
        })
        .catch(err => {
            // Fallback via window.Api if available
            if (window.Api && typeof window.Api.post === 'function') {
                window.Api.post('/api/v1/theme/save', { theme: payload })
                    .then(res => {
                        if (btnElement) {
                            btnElement.disabled = false;
                            btnElement.innerHTML = originalHtml;
                        }
                        isSaving = false;
                        if (res && res.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast(res.message || 'Theme saved successfully!', 'success');
                            }
                            setTimeout(() => { window.location.reload(); }, 600);
                        } else {
                            const validationDetails = res?.errors && typeof res.errors === 'object'
                                ? Object.values(res.errors).join(' ')
                                : '';
                            const msg = [res?.message || 'Failed to save theme.', validationDetails]
                                .filter(Boolean)
                                .join(' ');
                            if (typeof window.showToast === 'function') {
                                window.showToast(msg, 'danger');
                            }
                        }
                    })
                    .catch(() => {
                        if (btnElement) {
                            btnElement.disabled = false;
                            btnElement.innerHTML = originalHtml;
                        }
                        isSaving = false;
                        if (typeof window.showToast === 'function') {
                            window.showToast('Network error while saving theme configuration.', 'danger');
                        }
                    });
            } else {
                if (btnElement) {
                    btnElement.disabled = false;
                    btnElement.innerHTML = originalHtml;
                }
                isSaving = false;
                if (typeof window.showToast === 'function') {
                    window.showToast('Error saving theme: ' + err.message, 'danger');
                }
            }
        });
    }

    /**
     * Action Buttons (Save, Reset, Full Preview)
     */
    function bindActionButtons() {
        // Save Theme
        const saveBtn = document.getElementById('btn_save_theme');
        if (saveBtn) {
            saveBtn.addEventListener('click', function () {
                saveThemeConfiguration(currentTheme, this);
            });
        }

        // Reset to Default
        const resetBtn = document.getElementById('btn_reset_theme');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (!confirm('Are you sure you want to reset Theme & Appearance to system defaults? (This will ONLY reset visual design tokens; your school data, students, and users remain 100% intact).')) {
                    return;
                }

                resetBtn.disabled = true;
                resetBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> Resetting...';

                const appUrl = (window.APP_URL || '').replace(/\/$/, '');
                const targetUrl = appUrl ? `${appUrl}/settings/theme/reset` : '/settings/theme/reset';
                const csrfToken = (window.Api && typeof window.Api.getCsrfToken === 'function')
                    ? window.Api.getCsrfToken()
                    : (document.querySelector('meta[name="csrf-token"]')?.content || '');

                fetch(targetUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ _csrf: csrfToken, csrf_token: csrfToken })
                })
                .then(res => res.json())
                .then(res => {
                    resetBtn.disabled = false;
                    resetBtn.innerHTML = '<i class="fa-solid fa-rotate-left me-2"></i> Reset to Default';
                    if (res && res.success) {
                        if (typeof window.showToast === 'function') {
                            window.showToast('Theme reset to defaults. Reloading...', 'success');
                        }
                        setTimeout(() => { window.location.reload(); }, 600);
                    } else {
                        if (typeof window.showToast === 'function') {
                            window.showToast(res?.message || 'Failed to reset theme.', 'danger');
                        }
                    }
                })
                .catch(() => {
                    if (window.Api && typeof window.Api.post === 'function') {
                        window.Api.post('/api/v1/theme/reset', {})
                            .then(res => {
                                resetBtn.disabled = false;
                                resetBtn.innerHTML = '<i class="fa-solid fa-rotate-left me-2"></i> Reset to Default';
                                if (res && res.success) {
                                    if (typeof window.showToast === 'function') {
                                        window.showToast('Theme reset to defaults. Reloading...', 'success');
                                    }
                                    setTimeout(() => { window.location.reload(); }, 600);
                                }
                            });
                    } else {
                        resetBtn.disabled = false;
                        resetBtn.innerHTML = '<i class="fa-solid fa-rotate-left me-2"></i> Reset to Default';
                    }
                });
            });
        }
    }

    /**
     * Import / Export Handlers
     */
    function bindImportExport() {
        const fileInput = document.getElementById('theme_import_file_input');
        if (fileInput) {
            fileInput.addEventListener('change', function () {
                const file = this.files[0];
                if (!file) return;

                const reader = new FileReader();
                reader.onload = function (e) {
                    try {
                        const parsed = JSON.parse(e.target.result);
                        const formData = new FormData();
                        formData.append('json_data', JSON.stringify(parsed));

                        Api.post('/api/v1/theme/import', formData)
                            .then(res => {
                                if (res && res.success) {
                                    if (typeof window.showToast === 'function') {
                                        window.showToast(res.message || 'Theme imported successfully!', 'success');
                                    }
                                    setTimeout(() => window.location.reload(), 800);
                                } else {
                                    if (typeof window.showToast === 'function') {
                                        window.showToast(res?.message || 'Failed to import theme configuration.', 'danger');
                                    }
                                }
                            })
                            .catch(() => {
                                if (typeof window.showToast === 'function') {
                                    window.showToast('Error uploading theme file.', 'danger');
                                }
                            });
                    } catch (err) {
                        alert('Invalid JSON file format: ' + err.message);
                    }
                };
                reader.readAsText(file);
            });
        }
    }

    // --- Helper Utilities ---

    function adjustColorBrightness(hex, percent) {
        let num = parseInt(hex.replace('#', ''), 16);
        let amt = Math.round(2.55 * percent);
        let R = (num >> 16) + amt;
        let G = (num >> 8 & 0x00FF) + amt;
        let B = (num & 0x0000FF) + amt;
        return '#' + (0x1000000 + (R < 255 ? (R < 1 ? 0 : R) : 255) * 0x10000 + (G < 255 ? (G < 1 ? 0 : G) : 255) * 0x100 + (B < 255 ? (B < 1 ? 0 : B) : 255)).toString(16).slice(1);
    }

    function getLuminance(hex) {
        if (!hex || !hex.startsWith('#') || (hex.length !== 7 && hex.length !== 4)) return 0.5;
        let rgb = hex.length === 4
            ? [parseInt(hex[1]+hex[1], 16), parseInt(hex[2]+hex[2], 16), parseInt(hex[3]+hex[3], 16)]
            : [parseInt(hex.substr(1, 2), 16), parseInt(hex.substr(3, 2), 16), parseInt(hex.substr(5, 2), 16)];

        let a = rgb.map(v => {
            v /= 255;
            return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
        });
        return a[0] * 0.2126 + a[1] * 0.7152 + a[2] * 0.0722;
    }

    function calculateContrastRatio(hex1, hex2) {
        const lum1 = getLuminance(hex1);
        const lum2 = getLuminance(hex2);
        const brightest = Math.max(lum1, lum2);
        const darkest = Math.min(lum1, lum2);
        return (brightest + 0.05) / (darkest + 0.05);
    }

})();
