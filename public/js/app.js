/**
 * Oudaa shared UI behaviors:
 *  1. Any link with class "js-modal-link" opens its target page's form
 *     in a small popup instead of navigating away. Works with zero
 *     backend changes: it fetches the normal page, pulls out the
 *     ".content" block (the form), and re-submits via fetch too.
 *  2. Any <form data-confirm="..."> shows a small popup confirmation
 *     instead of the native confirm() dialog.
 *
 * Progressive enhancement: if JS fails for any reason, links still
 * navigate normally and forms still submit normally.
 */
(function () {
    'use strict';

    var overlay, card;

    function ensureOverlay() {
        if (overlay) return;
        overlay = document.createElement('div');
        overlay.className = 'modal-overlay';
        overlay.innerHTML = '<div class="modal-card"><button type="button" class="modal-close" aria-label="Close">&times;</button><div class="modal-body"></div></div>';
        document.body.appendChild(overlay);
        card = overlay.querySelector('.modal-card');

        overlay.querySelector('.modal-close').addEventListener('click', closeModal);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeModal();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('open')) closeModal();
        });
    }

    function openModal(variantClass) {
        ensureOverlay();
        card.className = 'modal-card' + (variantClass ? ' ' + variantClass : '');
        // Belt-and-braces alongside the CSS :has() rule for older browsers:
        // confirmation popups center vertically, add/edit forms sit near
        // the top so long forms are reachable without feeling awkward.
        overlay.classList.toggle('modal-overlay--center', variantClass === 'confirm-card');
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal() {
        if (!overlay) return;
        overlay.classList.remove('open');
        document.body.style.overflow = '';
    }

    // Executes any <script> tags inside a freshly-injected HTML block —
    // innerHTML-inserted scripts don't run on their own.
    function runScripts(container) {
        var scripts = container.querySelectorAll('script');
        scripts.forEach(function (old) {
            var fresh = document.createElement('script');
            for (var i = 0; i < old.attributes.length; i++) {
                fresh.setAttribute(old.attributes[i].name, old.attributes[i].value);
            }
            fresh.textContent = old.textContent;
            old.parentNode.replaceChild(fresh, old);
        });
    }

    function extractContent(doc) {
        var content = doc.querySelector('.content');
        return content ? content.innerHTML : '<p>Could not load this form.</p>';
    }

    function loadIntoModal(url) {
        ensureOverlay(); // must exist before we touch overlay.querySelector below
        var body = overlay.querySelector('.modal-body');
        body.innerHTML = '<div class="modal-loading">Loading…</div>';
        openModal();

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (res) { return res.text(); })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                body.innerHTML = extractContent(doc);
                runScripts(body);
                wireForms(body, url);
            })
            .catch(function () {
                body.innerHTML = '<div class="modal-loading">Something went wrong loading this form. <a href="' + url + '">Open it directly</a> instead.</div>';
            });
    }

    // Wires up any <form> found inside the modal so submitting it happens
    // over fetch instead of a full page navigation.
    function wireForms(scope, currentUrl) {
        var forms = scope.querySelectorAll('form');
        forms.forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                submitModalForm(form, currentUrl);
            });
        });
    }

    function submitModalForm(form, currentUrl) {
        var body = overlay.querySelector('.modal-body');
        var formData = new FormData(form);
        var submitBtn = form.querySelector('button[type="submit"]');
        var originalLabel = submitBtn ? submitBtn.textContent : null;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Saving…';
        }

        fetch(form.getAttribute('action') || currentUrl, {
            method: 'POST', // Laravel reads the real verb from the _method spoof field
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (res) {
                var finalUrl = res.url || '';
                // Success: Laravel redirected us to the index page (a
                // different path than the create/edit form we posted to).
                // Reload so the list picks up the change + flash message.
                if (res.redirected && stripQuery(finalUrl) !== stripQuery(currentUrl)) {
                    window.location.reload();
                    return null;
                }
                // Otherwise we're still looking at the form: either a
                // validation error (back()->withErrors()) or something
                // else went wrong. Re-render the form with the errors.
                return res.text();
            })
            .then(function (html) {
                if (html === null) return;
                var doc = new DOMParser().parseFromString(html, 'text/html');
                body.innerHTML = extractContent(doc);
                runScripts(body);
                wireForms(body, currentUrl);
            })
            .catch(function () {
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.textContent = originalLabel;
                }
                alert('Could not save — check your connection and try again.');
            });
    }

    function stripQuery(url) {
        return url.split('?')[0].replace(/\/$/, '');
    }

    // ---- 1. Modal-link create/edit popups ----
    document.addEventListener('click', function (e) {
        var link = e.target.closest('.js-modal-link');
        if (!link) return;
        e.preventDefault();
        loadIntoModal(link.getAttribute('href'));
    });

    // ---- 2. Confirmation popups (replaces native confirm()) ----
    function showConfirm(message, onConfirm) {
        ensureOverlay();
        var body = overlay.querySelector('.modal-body');
        body.innerHTML =
            '<p></p>' +
            '<div class="confirm-actions">' +
            '<button type="button" class="btn" data-role="cancel">Cancel</button>' +
            '<button type="button" class="btn btn-danger" data-role="ok">Confirm</button>' +
            '</div>';
        body.querySelector('p').textContent = message;
        openModal('confirm-card');

        body.querySelector('[data-role="cancel"]').addEventListener('click', closeModal);
        body.querySelector('[data-role="ok"]').addEventListener('click', function () {
            closeModal();
            onConfirm();
        });
    }

    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!(form instanceof HTMLFormElement)) return;
        if (!form.hasAttribute('data-confirm')) return;
        if (form.dataset.confirmed === '1') return; // already approved, let it through

        e.preventDefault();
        var message = form.getAttribute('data-confirm') || 'Are you sure?';
        showConfirm(message, function () {
            form.dataset.confirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });
})();
