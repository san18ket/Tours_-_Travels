(function () {
    const modalElement = document.getElementById('globalDeleteConfirmModal');
    if (!modalElement || typeof bootstrap === 'undefined') return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const checkbox = document.getElementById('globalDeleteConfirmCheckbox');
    const confirmButton = document.getElementById('globalDeleteConfirmButton');
    const titleElement = document.getElementById('globalDeleteConfirmTitle');
    const messageElement = document.getElementById('globalDeleteConfirmMessage');
    const summaryElement = document.getElementById('globalDeleteConfirmSummary');
    const recordElement = document.getElementById('globalDeleteConfirmRecord');
    let onConfirm = null;
    let pendingForm = null;

    function showConfirmation(options, callback) {
        const settings = options || {};
        titleElement.textContent = settings.title || 'Confirm deletion';
        messageElement.textContent = settings.message || 'This action may permanently remove this record and cannot be undone.';

        const record = (settings.record || '').trim();
        recordElement.textContent = record;
        summaryElement.classList.toggle('d-none', record === '');

        checkbox.checked = false;
        confirmButton.disabled = true;
        onConfirm = callback;
        modal.show();
    }

    checkbox.addEventListener('change', function () {
        confirmButton.disabled = !checkbox.checked;
    });

    confirmButton.addEventListener('click', function () {
        if (!checkbox.checked) return;

        const confirm = onConfirm;
        onConfirm = null;
        if (confirm) confirm();
        modal.hide();
    });

    modalElement.addEventListener('hidden.bs.modal', function () {
        onConfirm = null;
        pendingForm = null;
        checkbox.checked = false;
        confirmButton.disabled = true;
    });

    window.confirmDeleteAction = function (options) {
        return new Promise(function (resolve) {
            showConfirmation(options, function () {
                resolve(true);
            });
            modalElement.addEventListener('hidden.bs.modal', function () {
                resolve(false);
            }, { once: true });
        });
    };

    function getFormRecord(form, submitter) {
        const explicitRecord = form.dataset.deleteRecord;
        if (explicitRecord) return explicitRecord;

        const submitterLabel = submitter && (submitter.dataset.deleteRecord || submitter.getAttribute('aria-label') || submitter.title);
        if (submitterLabel) return submitterLabel;

        const text = submitter && submitter.textContent ? submitter.textContent.trim().replace(/\s+/g, ' ') : '';
        if (text && !/^(delete|remove)$/i.test(text)) return text;

        const formLabel = form.getAttribute('aria-label') || form.dataset.deleteTitle;
        return formLabel || '';
    }

    function isDeleteForm(form) {
        if (form.dataset.skipSharedDeleteConfirmation === 'true') return false;

        const methodOverride = form.querySelector('input[name="_method"]');
        const method = (methodOverride ? methodOverride.value : form.method || 'get').toUpperCase();
        if (!['POST', 'DELETE'].includes(method)) return false;

        let path = '';
        try {
            path = new URL(form.action, window.location.href).pathname;
        } catch (error) {
            return false;
        }

        return /(^|\/)(delete|destroy|remove)(\/|$)/i.test(path);
    }

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!(form instanceof HTMLFormElement)) return;

        if (form.dataset.sharedDeleteConfirmed === 'true') {
            delete form.dataset.sharedDeleteConfirmed;
            return;
        }
        if (!isDeleteForm(form)) return;

        const existingModal = document.querySelector('.modal.show:not(#globalDeleteConfirmModal)');
        if (existingModal) return;

        event.preventDefault();
        event.stopImmediatePropagation();

        const submitter = event.submitter || null;
        const specificMessage = form.dataset.deleteMessage || 'This action may permanently remove this record and cannot be undone.';
        pendingForm = form;

        showConfirmation({
            message: specificMessage,
            record: getFormRecord(form, submitter)
        }, function () {
            if (!pendingForm || !pendingForm.isConnected) return;

            const targetForm = pendingForm;
            const originalHandler = targetForm.getAttribute('onsubmit');
            if (originalHandler && /confirm\s*\(/i.test(originalHandler)) {
                targetForm.removeAttribute('onsubmit');
                window.setTimeout(function () {
                    targetForm.setAttribute('onsubmit', originalHandler);
                }, 0);
            }

            targetForm.dataset.sharedDeleteConfirmed = 'true';
            if (typeof targetForm.requestSubmit === 'function') {
                const validSubmitter = submitter && submitter.form === targetForm ? submitter : undefined;
                if (validSubmitter) {
                    targetForm.requestSubmit(validSubmitter);
                } else {
                    targetForm.requestSubmit();
                }
            } else {
                targetForm.submit();
            }
        });
    }, true);
})();
