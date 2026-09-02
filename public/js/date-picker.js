/**
 * Custom Gregorian / Ethiopian date picker.
 *
 * Markup (see the eth_date_input() Blade helper):
 *   <div class="date-picker" data-date-picker>
 *     <input type="text" class="date-picker-input" data-date-picker-text readonly>
 *     <input type="hidden" data-date-picker-value name="start_date" value="2026-08-27">
 *   </div>
 *
 * The hidden input keeps the real field `name` and always holds a plain
 * Gregorian 'YYYY-MM-DD' value — exactly what a native <input type="date">
 * would have submitted — so no backend/controller changes are needed.
 * The visible text input just shows that same date in whichever calendar
 * system (GC/EC) is currently selected, and opens a popup calendar in that
 * same system to pick a new one.
 */
(function () {
    'use strict';

    function cal() { return window.OudaaCalendar; }

    function pad2(n) { return (n < 10 ? '0' : '') + n; }

    function isoParts(iso) {
        var p = (iso || '').split('-');
        return { y: parseInt(p[0], 10), m: parseInt(p[1], 10), d: parseInt(p[2], 10) };
    }

    function todayISO() {
        var d = new Date();
        return d.getFullYear() + '-' + pad2(d.getMonth() + 1) + '-' + pad2(d.getDate());
    }

    function formatGregorianDisplay(iso) {
        var p = isoParts(iso);
        if (!p.y) return '';
        return p.d + ' ' + cal().GREGORIAN_MONTHS[p.m - 1] + ' ' + p.y;
    }

    function formatEthiopianDisplay(iso) {
        var e = cal().isoToEthiopian(iso);
        if (!e) return '';
        return e.day + ' ' + cal().AMHARIC_MONTHS[e.month - 1] + ' ' + e.year + ' ዓ.ም';
    }

    function displayFor(iso, system) {
        if (!iso) return '';
        return system === 'ec' ? formatEthiopianDisplay(iso) : formatGregorianDisplay(iso);
    }

    function Picker(root) {
        this.root = root;
        this.textInput = root.querySelector('[data-date-picker-text]');
        this.hiddenInput = root.querySelector('[data-date-picker-value]');
        this.popup = null;
        this.viewYear = null;
        this.viewMonth = null;
        this.bindEvents();
        this.refreshText();
    }

    Picker.prototype.system = function () {
        return cal().getSystem();
    };

    Picker.prototype.refreshText = function () {
        var iso = this.hiddenInput.value;
        this.textInput.value = iso ? displayFor(iso, this.system()) : '';
    };

    Picker.prototype.bindEvents = function () {
        var self = this;
        this.textInput.addEventListener('click', function () { self.open(); });
        this.textInput.addEventListener('focus', function () { self.open(); });
        document.addEventListener('click', function (e) {
            if (self.popup && !self.root.contains(e.target)) self.close();
        });
        document.addEventListener('oudaa:datesystem-changed', function () {
            self.refreshText();
            if (self.popup) self.render();
        });
    };

    Picker.prototype.open = function () {
        if (this.popup) return;

        var iso = this.hiddenInput.value || todayISO();
        if (this.system() === 'ec') {
            var e = cal().isoToEthiopian(iso);
            this.viewYear = e.year;
            this.viewMonth = e.month;
        } else {
            var p = isoParts(iso);
            this.viewYear = p.y;
            this.viewMonth = p.m;
        }

        this.popup = document.createElement('div');
        this.popup.className = 'date-picker-popup';
        this.root.appendChild(this.popup);
        this.render();
    };

    Picker.prototype.close = function () {
        if (this.popup) {
            this.popup.remove();
            this.popup = null;
        }
    };

    Picker.prototype.shiftMonth = function (delta) {
        var monthsInYear = this.system() === 'ec' ? 13 : 12;
        this.viewMonth += delta;
        if (this.viewMonth < 1) { this.viewMonth = monthsInYear; this.viewYear--; }
        if (this.viewMonth > monthsInYear) { this.viewMonth = 1; this.viewYear++; }
        this.render();
    };

    Picker.prototype.pick = function (iso) {
        this.hiddenInput.value = iso;
        this.hiddenInput.dispatchEvent(new Event('change', { bubbles: true }));
        this.refreshText();
        this.close();
        if (this.root.hasAttribute('data-auto-submit')) {
            var form = this.root.closest('form');
            if (form) form.submit();
        }
    };

    Picker.prototype.render = function () {
        var self = this;
        var system = this.system();
        var C = cal();
        this.popup.innerHTML = '';

        var header = document.createElement('div');
        header.className = 'date-picker-header';

        var prevBtn = document.createElement('button');
        prevBtn.type = 'button';
        prevBtn.className = 'date-picker-nav';
        prevBtn.textContent = '‹';
        prevBtn.addEventListener('click', function () { self.shiftMonth(-1); });

        var nextBtn = document.createElement('button');
        nextBtn.type = 'button';
        nextBtn.className = 'date-picker-nav';
        nextBtn.textContent = '›';
        nextBtn.addEventListener('click', function () { self.shiftMonth(1); });

        var label = document.createElement('div');
        label.className = 'date-picker-label';
        var monthName = system === 'ec' ? C.AMHARIC_MONTHS[this.viewMonth - 1] : C.GREGORIAN_MONTHS[this.viewMonth - 1];
        label.textContent = monthName + ' ' + this.viewYear + (system === 'ec' ? ' ዓ.ም' : '');

        header.appendChild(prevBtn);
        header.appendChild(label);
        header.appendChild(nextBtn);
        this.popup.appendChild(header);

        var weekdayRow = document.createElement('div');
        weekdayRow.className = 'date-picker-weekdays';
        var weekdays = system === 'ec'
            ? ['እሁ', 'ሰኞ', 'ማክ', 'ረቡ', 'ሐሙ', 'ዓር', 'ቅዳ']
            : ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'];
        weekdays.forEach(function (w) {
            var el = document.createElement('div');
            el.className = 'date-picker-weekday';
            el.textContent = w;
            weekdayRow.appendChild(el);
        });
        this.popup.appendChild(weekdayRow);

        var grid = document.createElement('div');
        grid.className = 'date-picker-grid';

        var daysInMonth, firstJDN;
        if (system === 'ec') {
            daysInMonth = C.daysInEthiopianMonth(this.viewYear, this.viewMonth);
            firstJDN = C.ethiopianToJDN(this.viewYear, this.viewMonth, 1);
        } else {
            daysInMonth = new Date(this.viewYear, this.viewMonth, 0).getDate();
            firstJDN = C.gregorianToJDN(this.viewYear, this.viewMonth, 1);
        }
        // JDN 0 = Monday in the proleptic Julian day convention; (jdn + 1) % 7 gives 0=Sunday.
        var firstWeekday = (firstJDN + 1) % 7;

        for (var i = 0; i < firstWeekday; i++) {
            grid.appendChild(document.createElement('div'));
        }

        var selectedIso = this.hiddenInput.value;

        for (var day = 1; day <= daysInMonth; day++) {
            var iso = system === 'ec'
                ? C.ethiopianToISO(this.viewYear, this.viewMonth, day)
                : this.viewYear + '-' + pad2(this.viewMonth) + '-' + pad2(day);

            var cellBtn = document.createElement('button');
            cellBtn.type = 'button';
            cellBtn.className = 'date-picker-day';
            if (iso === selectedIso) cellBtn.classList.add('is-selected');
            if (iso === todayISO()) cellBtn.classList.add('is-today');
            cellBtn.textContent = day;
            (function (isoCapture) {
                cellBtn.addEventListener('click', function () { self.pick(isoCapture); });
            })(iso);
            grid.appendChild(cellBtn);
        }

        this.popup.appendChild(grid);

        var footer = document.createElement('div');
        footer.className = 'date-picker-footer';
        var todayBtn = document.createElement('button');
        todayBtn.type = 'button';
        todayBtn.className = 'date-picker-today-btn';
        todayBtn.textContent = system === 'ec' ? 'ዛሬ' : 'Today';
        todayBtn.addEventListener('click', function () { self.pick(todayISO()); });
        var clearBtn = document.createElement('button');
        clearBtn.type = 'button';
        clearBtn.className = 'date-picker-clear-btn';
        clearBtn.textContent = system === 'ec' ? 'አጽዳ' : 'Clear';
        clearBtn.addEventListener('click', function () { self.pick(''); });
        footer.appendChild(todayBtn);
        footer.appendChild(clearBtn);
        this.popup.appendChild(footer);
    };

    function init() {
        document.querySelectorAll('[data-date-picker]').forEach(function (root) {
            if (root._oudaaPicker) return;
            root._oudaaPicker = new Picker(root);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        // ethiopian-date.js's own DOMContentLoaded listener registers
        // window.OudaaCalendar synchronously before this one runs, since
        // this script tag loads after it — but guard just in case.
        if (window.OudaaCalendar) {
            init();
        } else {
            window.addEventListener('load', init);
        }
    });
})();
