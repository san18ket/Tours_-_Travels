/**
 * School Management System — Universal Responsive Table-to-Cards Engine
 * Inspired by Project Management System reference architecture
 * 
 * Automatically converts all data tables into responsive modern cards on tablet and mobile (< 992px)
 * while preserving the desktop table view (>= 992px).
 * Fully supports live search filtering, AJAX pagination, Bootstrap modals, dropdowns, and status switches.
 */

(function () {
  'use strict';

  // Check if we are running in browser
  if (typeof window === 'undefined' || typeof document === 'undefined') return;

  const BREAKPOINT_LG = 992;
  const PAGE_SIZE_OPTIONS = [5, 10, 15, 25, 50];
  const tableKeys = new WeakMap();
  const paginationState = new WeakMap();
  let nextTableKey = 1;

  function getTableKey(table) {
    let key = tableKeys.get(table);
    if (!key) {
      key = table.id || `responsive-table-${nextTableKey++}`;
      tableKeys.set(table, key);
    }
    return key;
  }

  function removeResponsiveCards(table) {
    const tableKey = tableKeys.get(table);
    if (!tableKey) return;

    const container = table.closest('.table-responsive') || table.parentElement;
    const cardsGrid = container.parentElement
      ? Array.from(container.parentElement.querySelectorAll('.cards-grid'))
        .find(grid => grid.getAttribute('data-for-table') === tableKey)
      : null;
    if (cardsGrid) {
      const pager = cardsGrid.nextElementSibling;
      if (pager && pager.classList.contains('responsive-table-pagination')) {
        pager.remove();
      }
      cardsGrid.remove();
    }

    table.querySelectorAll('tbody tr.responsive-page-hidden').forEach(row => {
      row.classList.remove('responsive-page-hidden');
    });
    table.removeAttribute('data-cards-generated');
  }

  function isNestedTable(table) {
    return !!(table.parentElement && table.parentElement.closest('table'));
  }

  /**
   * Helper: extract clean text label from a TH element, removing hidden badges or icons
   */
  function getHeaderLabel(th, index) {
    if (!th) return 'Column ' + (index + 1);

    // If data-label or data-column attribute exists
    const explicitLabel = th.getAttribute('data-label') || th.getAttribute('data-column');
    if (explicitLabel) return explicitLabel.trim();

    // Clone TH to safely extract text without sorting icons or screen reader text
    const clone = th.cloneNode(true);
    clone.querySelectorAll('.d-none, .visually-hidden, .sorting-icon, script, style').forEach(el => el.remove());
    
    // Get text content
    let text = clone.textContent.replace(/\s+/g, ' ').trim();
    if (!text && th.querySelector('i')) {
      const iconClass = th.querySelector('i').className;
      if (iconClass.includes('user')) text = 'User';
      else if (iconClass.includes('calendar')) text = 'Date';
      else if (iconClass.includes('tag')) text = 'Category';
      else if (iconClass.includes('money') || iconClass.includes('dollar') || iconClass.includes('rupee')) text = 'Amount';
    }

    return text || 'Column ' + (index + 1);
  }

  /**
   * Helper: extract icon class from TH if present
   */
  function getHeaderIcon(th, label) {
    if (!th) return null;
    const icon = th.querySelector('i');
    if (icon) {
      return icon.className;
    }

    // Default icons based on common column names
    const l = (label || '').toLowerCase();
    if (l.includes('name') || l.includes('student') || l.includes('resident') || l.includes('client') || l.includes('user') || l.includes('profile')) return 'fa-solid fa-user';
    if (l.includes('adm') || l.includes('admission') || l.includes('code') || l.includes('gr') || l.includes('roll') || l.includes('id')) return 'fa-solid fa-id-card';
    if (l.includes('class') || l.includes('section') || l.includes('grade')) return 'fa-solid fa-graduation-cap';
    if (l.includes('room') || l.includes('block') || l.includes('bed')) return 'fa-solid fa-door-closed';
    if (l.includes('amount') || l.includes('fee') || l.includes('bill') || l.includes('paid') || l.includes('balance') || l.includes('due') || l.includes('rent') || l.includes('cost')) return 'fa-solid fa-circle-dollar-to-slot';
    if (l.includes('date') || l.includes('period') || l.includes('month') || l.includes('year') || l.includes('created') || l.includes('time')) return 'fa-solid fa-calendar';
    if (l.includes('phone') || l.includes('mobile') || l.includes('contact') || l.includes('email')) return 'fa-solid fa-address-book';
    if (l.includes('gender')) return 'fa-solid fa-venus-mars';
    if (l.includes('subject') || l.includes('book') || l.includes('exam')) return 'fa-solid fa-book-open';
    if (l.includes('status') || l.includes('occupancy')) return 'fa-solid fa-circle-check';
    if (l.includes('receipt') || l.includes('invoice')) return 'fa-solid fa-receipt';
    
    return null;
  }

  /**
   * Inspect column headers and determine their functional roles
   */
  function analyzeColumns(table) {
    const thead = table.querySelector('thead');
    const headerRow = thead ? thead.querySelector('tr') : table.querySelector('tr');
    if (!headerRow) return [];

    const ths = Array.from(headerRow.querySelectorAll('th, td'));
    const totalCols = ths.length;

    return ths.map((th, index) => {
      const rawText = (th.textContent || '').trim().toLowerCase();
      const label = getHeaderLabel(th, index);
      const icon = getHeaderIcon(th, label);

      const isIndex = (index === 0 && (rawText === '#' || rawText === 'no' || rawText === 'sr' || rawText === 'sr.' || rawText === 'no.' || rawText === 'sr no' || th.querySelector('input[type="checkbox"]')));
      const firstDataRow = table.querySelector('tbody tr');
      const firstRowCell = firstDataRow && firstDataRow.children[index];
      const hasActionControls = !!(firstRowCell && firstRowCell.querySelector('button, form, .dropdown, [data-bs-toggle="dropdown"], .action-dots-btn'));
      const isAction = rawText.includes('action') ||
        rawText.includes('manage') ||
        rawText.includes('option') ||
        rawText.includes('operation') ||
        (index === totalCols - 1 && !rawText && hasActionControls);
      const isStatus = (rawText.includes('status') || rawText.includes('occupancy') || rawText.includes('state') || rawText.includes('active'));
      const isPrimary = (!isIndex && !isAction && (
        rawText.includes('name') ||
        rawText.includes('student') ||
        rawText.includes('profile') ||
        rawText.includes('resident') ||
        rawText.includes('title') ||
        rawText.includes('room') ||
        rawText.includes('exam') ||
        rawText.includes('subject') ||
        rawText.includes('user') ||
        rawText.includes('employee') ||
        rawText.includes('book') ||
        rawText.includes('project') ||
        index === (isIndex ? 1 : 0)
      ));

      return {
        index,
        th,
        label,
        icon,
        isIndex,
        isAction,
        isStatus,
        isPrimary
      };
    });
  }

  /**
   * Generate cards for a single table
   */
  function convertTableToCards(table) {
    if (isNestedTable(table)) return;
    if (window.innerWidth >= BREAKPOINT_LG) {
      removeResponsiveCards(table);
      return;
    }

    // Avoid double generation, excluded tables, complex matrices, reports, or interactive input sheets
    const responsiveMode = table.getAttribute('data-responsive') || (table.closest('[data-responsive]') ? table.closest('[data-responsive]').getAttribute('data-responsive') : null);
    if (responsiveMode === 'scroll' || responsiveMode === 'special' || responsiveMode === 'none') {
      return;
    }

    if (table.closest('#printContainer, .schools-table-wrapper, .schools-card-list, .modal, .table-scroll, .matrix-container') ||
        table.classList.contains('no-card-convert') || 
        table.classList.contains('table-scroll') ||
        table.classList.contains('table-preserve-layout') ||
        table.classList.contains('table-preserve-size') ||
        table.classList.contains('calendar-table') ||
        table.classList.contains('timetable-grid') ||
        table.classList.contains('view-timetable-grid') ||
        table.classList.contains('timetable-matrix') ||
        table.classList.contains('schedule-table') ||
        table.classList.contains('monthly-table') ||
        table.classList.contains('monthly-attendance-table') ||
        table.classList.contains('attendance-matrix') ||
        table.classList.contains('att-day-table') ||
        table.classList.contains('ea-table') ||
        table.classList.contains('sheet-table') ||
        table.classList.contains('matrix-table') ||
        table.classList.contains('table-matrix') ||
        table.classList.contains('marks-table') ||
        table.classList.contains('marks-matrix') ||
        table.classList.contains('results-table') ||
        table.classList.contains('permission-matrix') ||
        table.classList.contains('role-permission-matrix') ||
        table.classList.contains('financial-report-table') ||
        table.classList.contains('report-data-table') ||
        table.classList.contains('report-table')) {
      return;
    }

    // Preserve tables that contain bulk interactive form inputs (e.g. marks entry, bulk attendance)
    // so user input is never detached or duplicated in mobile view
    if (table.querySelector('tbody input[name], tbody select[name]')) {
      return;
    }

    const tbody = table.querySelector('tbody');
    if (!tbody) return;
    const tableKey = getTableKey(table);

    // Ensure desktop table container is properly marked
    let container = table.closest('.table-responsive') || table.parentElement;
    if (!container.classList.contains('desktop-table-container')) {
      container.classList.add('desktop-table-container');
    }

    // Identify or create the companion cards-grid container
    let cardsGrid = container.nextElementSibling;
    if (!cardsGrid || !cardsGrid.classList.contains('cards-grid') || cardsGrid.getAttribute('data-for-table') !== tableKey) {
      // Look for already existing one right after container
      const existingGrid = Array.from(container.parentElement.querySelectorAll('.cards-grid')).find(grid => grid.getAttribute('data-for-table') === tableKey);
      if (existingGrid) {
        cardsGrid = existingGrid;
      } else {
        cardsGrid = document.createElement('div');
        cardsGrid.className = 'd-lg-none cards-grid';
        cardsGrid.setAttribute('data-for-table', tableKey);
        container.parentNode.insertBefore(cardsGrid, container.nextSibling);
      }
    }

    const columns = analyzeColumns(table);
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Check if table is empty or has a single colspan row
    if (rows.length === 0 || (rows.length === 1 && rows[0].querySelector('td[colspan]'))) {
      const emptyTd = rows.length ? rows[0].querySelector('td[colspan]') : null;
      let emptyText = emptyTd ? emptyTd.innerText.trim() : 'No records found.';
      if (emptyText.length > 150) emptyText = 'No records found matching your query.';

      const emptyCard = document.createElement('div');
      emptyCard.className = 'no-data-message modern-card text-center py-4';
      const icon = document.createElement('div');
      icon.className = 'no-data-icon mb-2';
      icon.innerHTML = '<i class="fa-solid fa-folder-open"></i>';
      const heading = document.createElement('h5');
      heading.className = 'fw-bold mb-1';
      heading.textContent = emptyText || 'No Records Found';
      const message = document.createElement('p');
      message.className = 'text-muted small mb-0';
      message.textContent = 'Try adjusting your filters or search terms.';
      emptyCard.append(icon, heading, message);
      cardsGrid.replaceChildren(emptyCard);
      setupTablePagination(table, cardsGrid);
      return;
    }

    // Clear previous cards and rebuild
    cardsGrid.innerHTML = '';

    // Find primary column
    let primaryCol = columns.find(c => c.isPrimary) || columns.find(c => !c.isIndex && !c.isAction) || columns[0];
    let statusCol = columns.find(c => c.isStatus);
    let actionCol = columns.find(c => c.isAction);

    rows.forEach((row, rowIndex) => {
      // Skip if this is a subrow or hidden filter row
      if (row.querySelector('td[colspan]')) return;

      const cells = Array.from(row.querySelectorAll('td'));
      if (cells.length === 0) return;

      const card = document.createElement('div');
      card.className = 'modern-card';
      card.setAttribute('data-row-index', rowIndex);
      
      // Mirror row data attributes (e.g. data-id, data-search, data-avail)
      Array.from(row.attributes).forEach(attr => {
        if (attr.name.startsWith('data-') || attr.name === 'id') {
          if (attr.name === 'id') {
            card.setAttribute('data-target-row-id', attr.value);
          } else {
            card.setAttribute(attr.name, attr.value);
          }
        }
      });

      // Synchronize visibility if row was hidden by client search
      if (row.style.display === 'none' || row.classList.contains('d-none')) {
        card.style.display = 'none';
      }

      // 1. CARD HEADER (Primary Title + Status Badge)
      const cardHeader = document.createElement('div');
      cardHeader.className = 'card-header';

      // Primary Title Left Side
      const titleWrapper = document.createElement('div');
      titleWrapper.className = 'card-title-wrap';
      titleWrapper.style.cssText = 'flex: 1; min-width: 0; display: flex; align-items: center; gap: 12px;';

      const primaryCell = primaryCol ? cells[primaryCol.index] : cells[0];
      if (primaryCell) {
        // Clone primary cell contents (e.g. avatar, name, badges)
        const primaryClone = primaryCell.cloneNode(true);
        
        // Clean up any dropdowns or actions that might be inside primary cell
        primaryClone.querySelectorAll('.dropdown, .action-dots-btn').forEach(d => d.remove());
        
        // Create title heading
        const titleH5 = document.createElement('h5');
        titleH5.className = 'card-title';
        titleH5.style.cssText = 'margin: 0; font-size: 1.05rem; font-weight: 700; color: var(--theme-text-primary, #1e293b);';
        titleH5.innerHTML = primaryClone.innerHTML;
        
        titleWrapper.appendChild(titleH5);
      } else {
        const titleH5 = document.createElement('h5');
        titleH5.className = 'card-title';
        titleH5.textContent = 'Record #' + (rowIndex + 1);
        titleWrapper.appendChild(titleH5);
      }
      cardHeader.appendChild(titleWrapper);

      // Status Badge Right Side
      if (statusCol && cells[statusCol.index]) {
        const statusCell = cells[statusCol.index];
        const statusClone = statusCell.cloneNode(true);
        const statusWrap = document.createElement('div');
        statusWrap.className = 'card-header-status';
        statusWrap.style.cssText = 'flex-shrink: 0; margin-left: 10px; display: flex; align-items: center; gap: 6px;';
        statusWrap.innerHTML = statusClone.innerHTML;

        // If status contains an input switch, sync its change event back to the table row
        const originalSwitch = statusCell.querySelector('input[type="checkbox"]');
        const cardSwitch = statusWrap.querySelector('input[type="checkbox"]');
        if (originalSwitch && cardSwitch) {
          cardSwitch.addEventListener('change', function () {
            originalSwitch.checked = cardSwitch.checked;
            originalSwitch.dispatchEvent(new Event('change', { bubbles: true }));
          });
        }

        cardHeader.appendChild(statusWrap);
      }

      card.appendChild(cardHeader);

      // 2. CARD DETAILS (Remaining Columns as Key-Value Pairs)
      const cardDetails = document.createElement('div');
      cardDetails.className = 'card-details';

      columns.forEach(col => {
        // Skip index column, primary column, status column, and action column in details list
        if (col.isIndex) return;
        if (primaryCol && col.index === primaryCol.index) return;
        if (statusCol && col.index === statusCol.index) return;
        if (actionCol && col.index === actionCol.index) return;

        const cell = cells[col.index];
        if (!cell) return;

        // Skip completely empty cells
        const cellText = cell.textContent.trim();
        if (!cellText && !cell.querySelector('img, i, span, badge, a, button')) return;

        const detailItem = document.createElement('div');
        detailItem.className = 'detail-item';

        const keySpan = document.createElement('span');
        keySpan.className = 'detail-item-key';
        
        const iconHtml = col.icon ? `<i class="${col.icon} me-1.5" style="color: var(--theme-primary); width: 16px; text-align: center;"></i>` : '';
        keySpan.innerHTML = `${iconHtml}<span>${col.label}</span>`;

        const valSpan = document.createElement('span');
        valSpan.className = 'detail-item-val';
        valSpan.innerHTML = cell.innerHTML;

        // Handle any interactive link or button inside the detail cell
        valSpan.querySelectorAll('a, button').forEach(btn => {
          btn.addEventListener('click', function (e) {
            // If the element has a matching counterpart in the table cell, trigger it
            const selector = btn.className.split(' ').map(c => '.' + c).join('');
            const originalBtn = cell.querySelector(selector);
            if (originalBtn && originalBtn !== btn) {
              originalBtn.click();
            }
          });
        });

        detailItem.appendChild(keySpan);
        detailItem.appendChild(valSpan);
        cardDetails.appendChild(detailItem);
      });

      card.appendChild(cardDetails);

      // 3. CARD ACTIONS (View, Edit, Delete, Print, Dropdowns)
      if (actionCol && cells[actionCol.index]) {
        const actionCell = cells[actionCol.index];
        const actionHtml = actionCell.innerHTML.trim();

        if (actionHtml) {
          const cardActions = document.createElement('div');
          cardActions.className = 'card-actions';

          // Clone actions HTML
          const actionsWrap = document.createElement('div');
          actionsWrap.className = 'card-action-wrapper';
          actionsWrap.style.cssText = 'display: flex; align-items: center; justify-content: flex-end; gap: 8px; width: 100%;';
          actionsWrap.innerHTML = actionHtml;

          // Wire up event listeners for cloned buttons to proxy back to table or execute identical handler
          actionsWrap.querySelectorAll('button, a, .action-btn-table, .btn, .dropdown-item').forEach(clonedBtn => {
            // Find original button in the row's action cell
            const originalBtns = Array.from(actionCell.querySelectorAll('button, a, .action-btn-table, .btn, .dropdown-item'));
            const matchingIndex = Array.from(actionsWrap.querySelectorAll('button, a, .action-btn-table, .btn, .dropdown-item')).indexOf(clonedBtn);
            const targetOriginalBtn = originalBtns[matchingIndex];

            // Only proxy if not handled by Bootstrap dropdown/modal or inline onclick
            const hasBsToggle = clonedBtn.hasAttribute('data-bs-toggle') || clonedBtn.hasAttribute('data-bs-target');
            const hasInlineClick = clonedBtn.getAttribute('onclick');
            
            if (targetOriginalBtn && !hasBsToggle && !hasInlineClick) {
              clonedBtn.addEventListener('click', function (e) {
                const href = clonedBtn.getAttribute('href');
                if (!href || href === '#' || href.startsWith('javascript:')) {
                  // Proxy click to original table button so any element-bound event listeners fire
                  targetOriginalBtn.click();
                }
              });
            }
          });

          cardActions.appendChild(actionsWrap);
          card.appendChild(cardActions);
        }
      }

      cardsGrid.appendChild(card);
    });

    table.setAttribute('data-cards-generated', 'true');
    setupTablePagination(table, cardsGrid);
  }

  function hasExistingPagination(table, container) {
    const selector = '#paginationContainer, .pagination-wrapper, [data-table-pagination]';
    const isActivePager = function (pager) {
      if (pager.classList.contains('responsive-table-pagination')) return false;
      if (pager.matches('.pagination-wrapper, [data-table-pagination]')) return true;
      return !!pager.querySelector('.pagination-wrapper, .page-link, [data-table-pagination]');
    };
    const containsExistingPager = function (element) {
      return !!element && Array.from(element.querySelectorAll(selector)).some(isActivePager);
    };
    const panel = table.closest('.table-card, .decision-table-card, .card');
    if (containsExistingPager(panel)) return true;

    const scope = (panel && panel.parentElement) || container.parentElement;
    if (!scope) return false;

    let sibling = panel || container;
    for (let i = 0; i < 4 && sibling; i++, sibling = sibling.nextElementSibling) {
      if (sibling.matches && sibling.matches(selector) && isActivePager(sibling)) return true;
      if (containsExistingPager(sibling)) return true;
      if (sibling !== (panel || container) && sibling.matches && sibling.matches('.table-card, .decision-table-card')) break;
    }
    return false;
  }

  function createPager(table, cardsGrid) {
    let pager = cardsGrid.nextElementSibling;
    if (!pager || !pager.classList.contains('responsive-table-pagination')) {
      pager = document.createElement('div');
      pager.className = 'pagination-wrapper responsive-table-pagination d-lg-none';
      pager.setAttribute('aria-label', 'Table pagination');
      cardsGrid.parentNode.insertBefore(pager, cardsGrid.nextSibling);
    }
    pager.classList.add('d-lg-none');

    if (!paginationState.has(table)) {
      paginationState.set(table, { page: 1, pageSize: PAGE_SIZE_OPTIONS[0] });
    }
    return { pager, state: paginationState.get(table) };
  }

  function setupTablePagination(table, cardsGrid) {
    const container = table.closest('.table-responsive') || table.parentElement;
    if (hasExistingPagination(table, container)) return;

    const tbody = table.querySelector('tbody');
    const rows = tbody ? Array.from(tbody.querySelectorAll('tr')) : [];
    const dataRows = rows.filter(row => !row.querySelector('td[colspan]'));
    const { pager, state } = createPager(table, cardsGrid);
    const eligibleRows = dataRows.filter(row => row.style.display !== 'none' && !row.classList.contains('d-none'));
    const pageCount = Math.ceil(eligibleRows.length / state.pageSize);

    state.page = Math.min(Math.max(state.page, 1), Math.max(pageCount, 1));
    pager.replaceChildren();
    pager.classList.remove('d-none');

    const pageRows = eligibleRows.slice((state.page - 1) * state.pageSize, state.page * state.pageSize);
    const pageRowSet = new Set(pageRows);
    rows.forEach(row => {
      const shouldHide = eligibleRows.includes(row) && !pageRowSet.has(row);
      if (row.classList.contains('responsive-page-hidden') !== shouldHide) {
        row.classList.toggle('responsive-page-hidden', shouldHide);
      }
    });

    cardsGrid.querySelectorAll('.modern-card[data-row-index]').forEach(card => {
      const row = rows[Number(card.getAttribute('data-row-index'))];
      const display = !row || row.style.display === 'none' || row.classList.contains('d-none') || row.classList.contains('responsive-page-hidden') ? 'none' : '';
      if (card.style.display !== display) card.style.display = display;
    });

    const summary = document.createElement('div');
    summary.className = 'pagination-info responsive-table-pagination-info';
    const from = eligibleRows.length ? ((state.page - 1) * state.pageSize) + 1 : 0;
    const to = Math.min(state.page * state.pageSize, eligibleRows.length);
    summary.innerHTML = `Showing <span>${from}&ndash;${to}</span> of <span>${eligibleRows.length.toLocaleString()}</span> record(s)`;

    const controls = document.createElement('div');
    controls.className = 'responsive-table-pagination-controls';

    const sizeLabel = document.createElement('label');
    sizeLabel.className = 'responsive-table-page-size-label';
    sizeLabel.textContent = 'Rows per page';

    const sizeSelect = document.createElement('select');
    sizeSelect.className = 'form-select form-select-sm responsive-table-page-size';
    sizeSelect.setAttribute('aria-label', 'Rows per page');
    sizeLabel.htmlFor = `responsive-page-size-${getTableKey(table)}`;
    sizeSelect.id = sizeLabel.htmlFor;
    PAGE_SIZE_OPTIONS.forEach(function (size) {
      const option = document.createElement('option');
      option.value = String(size);
      option.textContent = String(size);
      option.selected = state.pageSize === size;
      sizeSelect.appendChild(option);
    });
    sizeSelect.addEventListener('change', function () {
      state.pageSize = Number(sizeSelect.value) || PAGE_SIZE_OPTIONS[0];
      state.page = 1;
      setupTablePagination(table, cardsGrid);
    });

    const nav = document.createElement('nav');
    nav.setAttribute('aria-label', 'Table pages');
    if (pageCount > 1) {
      const pages = document.createElement('div');
      pages.className = 'pagination responsive-table-page-links';
      nav.appendChild(pages);

      function addPage(label, page, disabled, active, ariaLabel) {
        const link = document.createElement('a');
        link.href = '#';
        link.className = `${active ? 'active' : ''}${disabled ? ' disabled' : ''}`.trim();
        link.textContent = label;
        if (ariaLabel) link.setAttribute('aria-label', ariaLabel);
        if (active) link.setAttribute('aria-current', 'page');
        link.addEventListener('click', function (event) {
          event.preventDefault();
          if (disabled || active) return;
          state.page = page;
          setupTablePagination(table, cardsGrid);
        });
        pages.appendChild(link);
      }

      function addEllipsis() {
        const dots = document.createElement('span');
        dots.className = 'dots';
        dots.textContent = '...';
        pages.appendChild(dots);
      }

      addPage('‹', state.page - 1, state.page <= 1, false, 'Previous page');
      let rangeStart = Math.max(1, state.page - 1);
      let rangeEnd = rangeStart + 2;
      if (rangeEnd > pageCount) {
        rangeEnd = pageCount;
        rangeStart = Math.max(1, rangeEnd - 2);
      }
      for (let page = rangeStart; page <= rangeEnd; page++) {
        addPage(String(page), page, false, page === state.page, `Page ${page}`);
      }
      if (rangeEnd < pageCount) {
        addEllipsis();
        addPage(String(pageCount), pageCount, false, state.page === pageCount, `Page ${pageCount}`);
      }
      addPage('›', state.page + 1, state.page >= pageCount, false, 'Next page');
    }

    controls.append(sizeLabel, sizeSelect);
    if (pageCount > 1) controls.appendChild(nav);
    pager.append(summary, controls);
  }

  /**
   * Observe table body mutations (e.g. dynamic AJAX load, search filter updates)
   */
  function observeTableChanges(table) {
    if (table._cardsObserverAttached) return;
    table._cardsObserverAttached = true;

    const tbody = table.querySelector('tbody');
    if (!tbody) return;

    let debounceTimer;
    const observer = new MutationObserver(function (mutations) {
      clearTimeout(debounceTimer);
      // If only style/class changed (e.g. client-side search hiding/showing rows), sync display immediately
      const isOnlyAttr = mutations.length > 0 && mutations.every(m => m.type === 'attributes');
      if (isOnlyAttr) {
        const container = table.closest('.table-responsive') || table.parentElement;
        const tableKey = getTableKey(table);
        const cardsGrid = Array.from(container.parentElement.querySelectorAll('.cards-grid')).find(grid => grid.getAttribute('data-for-table') === tableKey);
        if (cardsGrid) {
          const rows = Array.from(tbody.querySelectorAll('tr'));
          rows.forEach((row, i) => {
            const card = cardsGrid.querySelector(`.modern-card[data-row-index="${i}"]`);
            if (card) {
              card.style.display = (row.style.display === 'none' || row.classList.contains('d-none') || row.classList.contains('responsive-page-hidden')) ? 'none' : '';
            }
          });
          setupTablePagination(table, cardsGrid);
          return;
        }
      }

      // If rows were added, deleted or replaced (AJAX / pagination), debounce full rebuild
      debounceTimer = setTimeout(function () {
        convertTableToCards(table);
      }, 100);
    });

    observer.observe(tbody, {
      childList: true,
      subtree: true,
      attributes: true,
      attributeFilter: ['style', 'class']
    });
  }

  /**
   * Main scan: find and convert all data tables on page
   */
  function initAllTableCards() {
    const selector = 'table.modern-table, table.table:not(.no-card-convert):not(.calendar-table):not(.timetable-grid), .table-responsive > table:not(.no-card-convert)';
    document.querySelectorAll(selector).forEach(table => {
      convertTableToCards(table);
      if (window.innerWidth < BREAKPOINT_LG && !isNestedTable(table)) {
        observeTableChanges(table);
      }
    });
  }

  document.addEventListener('change', function (event) {
    const pageSize = event.target.closest('.pagination-page-size');
    if (!pageSize) return;

    const limitSelect = document.querySelector('select[name="limit"]:not(.pagination-page-size), #limitSelect, #filterLimit');
    if (limitSelect instanceof HTMLSelectElement) {
      let option = Array.from(limitSelect.options).find(item => item.value === pageSize.value);
      if (!option) {
        option = document.createElement('option');
        option.value = pageSize.value;
        option.textContent = pageSize.value === 'all' ? 'All' : pageSize.value;
        limitSelect.appendChild(option);
      }
      limitSelect.value = pageSize.value;
      limitSelect.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }
    if (limitSelect instanceof HTMLInputElement) {
      limitSelect.value = pageSize.value;
      limitSelect.dispatchEvent(new Event('change', { bubbles: true }));
      return;
    }

    const url = new URL(window.location.href);
    url.searchParams.set('limit', pageSize.value);
    url.searchParams.set('page', '1');
    window.location.assign(url.toString());
  });

  document.addEventListener('change', function (event) {
    const limitSelect = event.target;
    if (!(limitSelect instanceof HTMLSelectElement) ||
        !limitSelect.matches('select[name="limit"]:not(.pagination-page-size), #limitSelect, #filterLimit')) {
      return;
    }

    document.querySelectorAll('.pagination-page-size').forEach(function (pageSize) {
      let option = Array.from(pageSize.options).find(item => item.value === limitSelect.value);
      if (!option) {
        option = document.createElement('option');
        option.value = limitSelect.value;
        option.textContent = limitSelect.value === 'all' ? 'All' : limitSelect.value;
        pageSize.appendChild(option);
      }
      pageSize.value = limitSelect.value;
    });
  });

  // Run on DOM ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initAllTableCards);
  } else {
    initAllTableCards();
  }

  // Also re-check on resize (throttled)
  let resizeTimer;
  window.addEventListener('resize', function () {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(initAllTableCards, 200);
  });

  // Expose global helper for manual triggering after custom AJAX requests
  window.refreshResponsiveTableCards = initAllTableCards;

})();
