/**
 * Ethiopian (EC) / Gregorian (GC) date system toggle.
 *
 * Every date the app renders is wrapped server-side by the `eth_date()`
 * helper as:
 *   <span class="eth-date" data-iso="2026-08-27">27 Aug 2026</span>
 *
 * This script converts the ISO date to the Ethiopian calendar locally
 * (pure math — the standard Julian-Day-Number based conversion used by
 * most open-source Ethiopian calendar libraries), so it works instantly,
 * offline, for any date already stored in the app (join dates, payment
 * dates, hire dates, etc.) without calling an external date API per row.
 *
 * The chosen system ('gc' or 'ec') is remembered in localStorage and
 * re-applied on every page load.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'oudaa_date_system'; // 'gc' | 'ec'

    var AMHARIC_MONTHS = [
        'መስከረም', 'ጥቅምት', 'ኅዳር', 'ታኅሳስ', 'ጥር', 'የካቲት',
        'መጋቢት', 'ሚያዝያ', 'ግንቦት', 'ሰኔ', 'ሐምሌ', 'ነሐሴ', 'ጳጉሜ',
    ];

    // JDN of Ethiopian epoch (1 Meskerem, year 1 — Amete Mihret).
    var JD_EPOCH_OFFSET_AMETE_MIHRET = 1723856;

    function gregorianToJDN(year, month, day) {
        var a = Math.floor((14 - month) / 12);
        var y = year + 4800 - a;
        var m = month + 12 * a - 3;
        return day + Math.floor((153 * m + 2) / 5) + 365 * y
            + Math.floor(y / 4) - Math.floor(y / 100) + Math.floor(y / 400) - 32045;
    }

    function jdnToEthiopian(jdn) {
        var r = (jdn - JD_EPOCH_OFFSET_AMETE_MIHRET) % 1461;
        var n = (r % 365) + 365 * Math.floor(r / 1460);
        var year = 4 * Math.floor((jdn - JD_EPOCH_OFFSET_AMETE_MIHRET) / 1461)
            + Math.floor(r / 365) - Math.floor(r / 1460);
        var month = Math.floor(n / 30) + 1;
        var day = (n % 30) + 1;
        return { year: year, month: month, day: day };
    }

    function ethiopianToJDN(year, month, day) {
        return JD_EPOCH_OFFSET_AMETE_MIHRET
            + 1461 * Math.floor(year / 4)
            + 365 * (year % 4)
            + (month - 1) * 30
            + (day - 1);
    }

    function jdnToGregorian(jdn) {
        var a = jdn + 32044;
        var b = Math.floor((4 * a + 3) / 146097);
        var c = a - Math.floor(146097 * b / 4);
        var d = Math.floor((4 * c + 3) / 1461);
        var e = c - Math.floor(1461 * d / 4);
        var m = Math.floor((5 * e + 2) / 153);
        var day = e - Math.floor((153 * m + 2) / 5) + 1;
        var month = m + 3 - 12 * Math.floor(m / 10);
        var year = 100 * b + d - 4800 + Math.floor(m / 10);
        return { year: year, month: month, day: day };
    }

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    function isoToEthiopian(iso) {
        // iso: 'YYYY-MM-DD'
        var parts = iso.split('-');
        var y = parseInt(parts[0], 10);
        var m = parseInt(parts[1], 10);
        var d = parseInt(parts[2], 10);
        if (!y || !m || !d) return null;
        var jdn = gregorianToJDN(y, m, d);
        return jdnToEthiopian(jdn);
    }

    function ethiopianToISO(year, month, day) {
        var jdn = ethiopianToJDN(year, month, day);
        var g = jdnToGregorian(jdn);
        return g.year + '-' + pad2(g.month) + '-' + pad2(g.day);
    }

    function isEthiopianLeap(year) {
        return (year % 4) === 3;
    }

    function daysInEthiopianMonth(year, month) {
        if (month < 13) return 30;
        return isEthiopianLeap(year) ? 6 : 5;
    }

    var GREGORIAN_MONTHS = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December',
    ];

    function formatEthiopian(iso, time) {
        var e = isoToEthiopian(iso);
        if (!e) return iso;
        var monthName = AMHARIC_MONTHS[e.month - 1] || '';
        var out = e.day + ' ' + monthName + ' ' + e.year + ' ዓ.ም';
        if (time) out += ' ' + time;
        return out;
    }

    function getSystem() {
        try {
            return localStorage.getItem(STORAGE_KEY) || 'gc';
        } catch (e) {
            return 'gc';
        }
    }

    function setSystem(system) {
        try {
            localStorage.setItem(STORAGE_KEY, system);
        } catch (e) { /* ignore (private mode, etc.) */ }
    }

    function applyDateSystem() {
        var system = getSystem();
        var nodes = document.querySelectorAll('.eth-date[data-iso]');
        nodes.forEach(function (node) {
            var iso = node.getAttribute('data-iso');
            if (!iso) return;
            if (!node.hasAttribute('data-gc-text')) {
                // remember the original server-rendered Gregorian text once
                node.setAttribute('data-gc-text', node.textContent);
            }
            node.textContent = system === 'ec'
                ? formatEthiopian(iso, node.getAttribute('data-time'))
                : node.getAttribute('data-gc-text');
        });

        document.querySelectorAll('[data-date-toggle]').forEach(function (btn) {
            btn.classList.toggle('is-ec', system === 'ec');
            btn.setAttribute('aria-pressed', system === 'ec' ? 'true' : 'false');
        });

        document.dispatchEvent(new CustomEvent('oudaa:datesystem-changed', { detail: { system: system } }));
    }

    function toggleSystem() {
        setSystem(getSystem() === 'ec' ? 'gc' : 'ec');
        applyDateSystem();
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyDateSystem();
        document.querySelectorAll('[data-date-toggle]').forEach(function (btn) {
            btn.addEventListener('click', toggleSystem);
        });
    });

    // Exposed for pages that inject rows dynamically after load (if any).
    window.OudaaDateSystem = { apply: applyDateSystem, toggle: toggleSystem, get: getSystem };

    // Shared calendar math + vocab, reused by the date-picker widget
    // (public/js/date-picker.js) so both files agree on one conversion.
    window.OudaaCalendar = {
        gregorianToJDN: gregorianToJDN,
        jdnToEthiopian: jdnToEthiopian,
        ethiopianToJDN: ethiopianToJDN,
        jdnToGregorian: jdnToGregorian,
        isoToEthiopian: isoToEthiopian,
        ethiopianToISO: ethiopianToISO,
        isEthiopianLeap: isEthiopianLeap,
        daysInEthiopianMonth: daysInEthiopianMonth,
        formatEthiopian: formatEthiopian,
        AMHARIC_MONTHS: AMHARIC_MONTHS,
        GREGORIAN_MONTHS: GREGORIAN_MONTHS,
        getSystem: getSystem,
    };
})();
