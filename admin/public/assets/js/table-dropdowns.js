(function () {
    const portals = new Map();

    function getDropdownParts(toggle) {
        const dropdown = toggle.closest('.dropdown');
        const menu = dropdown && dropdown.querySelector('.dropdown-menu');
        if (!dropdown || !menu) return null;
        return { dropdown, menu };
    }

    function isTableAction(toggle) {
        return !!toggle.closest('table, .table-responsive, .table-card')
            || toggle.matches('.action-dots-btn, .action-btn-table, .btn-dots-menu, .action-btn-circle, .rp-action-btn');
    }

    function positionMenu(toggle, menu) {
        if (!toggle.isConnected || !menu.isConnected) return;

        const toggleRect = toggle.getBoundingClientRect();
        const viewportPadding = 8;
        const gap = 6;
        const maxHeight = Math.max(120, window.innerHeight - viewportPadding * 2);

        menu.style.position = 'fixed';
        menu.style.inset = 'auto';
        menu.style.left = '0';
        menu.style.top = '0';
        menu.style.right = 'auto';
        menu.style.bottom = 'auto';
        menu.style.transform = 'none';
        menu.style.margin = '0';
        menu.style.maxHeight = `${maxHeight}px`;
        menu.style.overflowY = 'auto';
        menu.style.zIndex = '1085';

        const menuRect = menu.getBoundingClientRect();
        const left = Math.max(
            viewportPadding,
            Math.min(toggleRect.right - menuRect.width, window.innerWidth - menuRect.width - viewportPadding)
        );
        const belowTop = toggleRect.bottom + gap;
        const aboveTop = toggleRect.top - menuRect.height - gap;
        const top = belowTop + menuRect.height <= window.innerHeight - viewportPadding || aboveTop < viewportPadding
            ? belowTop
            : aboveTop;

        menu.style.left = `${left}px`;
        menu.style.top = `${Math.max(viewportPadding, top)}px`;
    }

    document.addEventListener('click', function (event) {
        const toggle = event.target.closest('[data-bs-toggle="dropdown"]');
        if (!toggle || !isTableAction(toggle)) return;
        toggle.setAttribute('data-bs-display', 'static');
    }, true);

    document.addEventListener('shown.bs.dropdown', function (event) {
        const toggle = event.target;
        if (!(toggle instanceof Element) || !toggle.matches('[data-bs-toggle="dropdown"]') || !isTableAction(toggle)) return;

        const parts = getDropdownParts(toggle);
        if (!parts || portals.has(toggle)) return;

        const placeholder = document.createComment('table-action-dropdown-menu');
        const originalStyle = parts.menu.getAttribute('style');
        parts.menu.parentNode.insertBefore(placeholder, parts.menu);
        document.body.appendChild(parts.menu);

        parts.menu.setAttribute('data-table-dropdown', 'true');
        const portal = { ...parts, placeholder, originalStyle };
        portals.set(toggle, portal);
        positionMenu(toggle, parts.menu);
    });

    document.addEventListener('hidden.bs.dropdown', function (event) {
        const toggle = event.target;
        if (!(toggle instanceof Element)) return;

        const portal = portals.get(toggle);
        if (!portal) return;

        portal.menu.removeAttribute('data-table-dropdown');
        if (portal.placeholder.isConnected) {
            portal.placeholder.parentNode.insertBefore(portal.menu, portal.placeholder);
            portal.placeholder.remove();
        }

        if (portal.originalStyle === null) {
            portal.menu.removeAttribute('style');
        } else {
            portal.menu.setAttribute('style', portal.originalStyle);
        }

        portals.delete(toggle);
    });

    function repositionOpenMenus() {
        portals.forEach(function (portal, toggle) {
            if (!toggle.isConnected) return;
            positionMenu(toggle, portal.menu);
        });
    }

    window.addEventListener('resize', repositionOpenMenus);
    window.addEventListener('scroll', repositionOpenMenus, true);
})();
