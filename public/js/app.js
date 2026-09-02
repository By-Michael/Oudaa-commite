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
            // A syntax error in one script (e.g. bad data corrupting the
            // markup) would otherwise throw here and silently skip every
            // script after it in the list — isolate each one so a single
            // bad script can't take out the rest of the form's behavior.
            try {
                var fresh = document.createElement('script');
                for (var i = 0; i < old.attributes.length; i++) {
                    fresh.setAttribute(old.attributes[i].name, old.attributes[i].value);
                }
                fresh.textContent = old.textContent;
                old.parentNode.replaceChild(fresh, old);
            } catch (err) {
                console.error('A script inside this popup failed to run:', err);
            }
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
            submitBtn.textContent = submitBtn.getAttribute('data-loading-label') || (window.i18n && window.i18n.savingLabel) || 'Saving…';
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
                alert((window.i18n && window.i18n.saveErrorMessage) || 'Could not save — check your connection and try again.');
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
        var message = form.getAttribute('data-confirm') || (window.i18n && window.i18n.confirmFallback) || 'Are you sure?';
        showConfirm(message, function () {
            form.dataset.confirmed = '1';
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                form.submit();
            }
        });
    });

    // ---- 3. Character filtering on data-filter="..." fields ----
    // Delegated (not bound per-element) so it works on fields injected
    // later into a modal too, without needing to be re-wired.
    // Never applied to password fields — restricting characters there
    // would only weaken the passwords people are able to choose.
    var FIELD_FILTERS = {
        // Personal names: letters, spaces, and the punctuation real names use.
        letters: function (v) { return v.replace(/[^A-Za-z\u00C0-\u024F\s.'-]/g, ''); },
        // Whole numbers only (recurrence day, counts, etc).
        digits: function (v) { return v.replace(/[^0-9]/g, ''); },
        // Money/quantity fields: digits and a single decimal point.
        decimal: function (v) {
            v = v.replace(/[^0-9.]/g, '');
            var firstDot = v.indexOf('.');
            if (firstDot !== -1) {
                v = v.slice(0, firstDot + 1) + v.slice(firstDot + 1).replace(/\./g, '');
            }
            return v;
        },
        // Phone numbers: digits and the punctuation people format them with.
        phone: function (v) { return v.replace(/[^0-9+\-\s()]/g, ''); },
        // Unit/block/ID numbers: letters, digits, spaces, hyphens, slashes.
        alnum: function (v) { return v.replace(/[^A-Za-z0-9\s\-\/]/g, ''); },
        // Free text (notes, vendor, descriptions): block markup/script-y
        // symbols people never legitimately need here, allow everything else.
        'safe-text': function (v) { return v.replace(/[<>{}\[\]\\`^~]/g, ''); },
    };

    document.addEventListener('input', function (e) {
        var el = e.target;
        var filterName = el.getAttribute && el.getAttribute('data-filter');
        if (!filterName || el.type === 'password') return;

        var fn = FIELD_FILTERS[filterName];
        if (!fn) return;

        var start = el.selectionStart, end = el.selectionEnd;
        var next = fn(el.value);
        if (next !== el.value) {
            var diff = el.value.length - next.length;
            el.value = next;
            // Keep the caret where the user was typing instead of jumping to the end.
            if (start != null && end != null) {
                el.setSelectionRange(Math.max(0, start - diff), Math.max(0, end - diff));
            }
        }
    });
})();
