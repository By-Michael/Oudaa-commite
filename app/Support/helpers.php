<?php

if (! function_exists('eth_date')) {
    /**
     * Render a date so it can be instantly switched between the Gregorian
     * (GC) and Ethiopian (EC) calendars from the client side, via the date
     * system toggle in the top bar (see public/js/ethiopian-date.js).
     *
     * We deliberately do the Gregorian->Ethiopian conversion in JS rather
     * than calling a third-party date API per row: this app needs to
     * convert arbitrary, already-stored dates (join dates, payment dates,
     * hire dates, etc.) — often hundreds of them on one page — not just
     * "today's date", so a local, offline, instant conversion is both more
     * reliable and far cheaper than a network call per date.
     */
    function eth_date($date, string $format = 'd M Y'): string
    {
        if (! $date) {
            return '—';
        }

        $carbon = $date instanceof \Illuminate\Support\Carbon
            ? $date
            : \Illuminate\Support\Carbon::parse($date);

        $timeAttr = preg_match('/[HhGgis]/', $format)
            ? ' data-time="'.$carbon->format('H:i').'"'
            : '';

        return '<span class="eth-date" data-iso="'.$carbon->toDateString().'"'.$timeAttr.'>'
            .e($carbon->format($format))
            .'</span>';
    }
}

if (! function_exists('eth_date_input')) {
    /**
     * Render a text-input-driven date field backed by the custom GC/EC
     * calendar popup (public/js/date-picker.js), instead of a native
     * <input type="date"> (which only ever shows the Gregorian calendar,
     * regardless of the site's date-system toggle).
     *
     * The hidden input keeps the real `name` and always holds a plain
     * Gregorian 'YYYY-MM-DD' value, so this is a drop-in replacement:
     * controllers, validation rules ($request->validate(['x' => 'date'])),
     * and $request->input('x') all keep working exactly as before.
     */
    function eth_date_input(string $name, $value = null, array $attrs = [], bool $autoSubmit = false): string
    {
        $iso = $value ? \Illuminate\Support\Carbon::parse($value)->toDateString() : '';

        $attrs = array_merge(['placeholder' => __('Select a date')], $attrs);
        $attrString = '';
        foreach ($attrs as $k => $v) {
            $attrString .= ' '.e($k).'="'.e($v).'"';
        }

        $wrapperAttrs = $autoSubmit ? ' data-auto-submit' : '';

        return '<div class="date-picker" data-date-picker'.$wrapperAttrs.'>'
            .'<input type="text" class="date-picker-input" data-date-picker-text readonly'.$attrString.'>'
            .'<input type="hidden" data-date-picker-value name="'.e($name).'" value="'.e($iso).'">'
            .'</div>';
    }
}

if (! function_exists('money')) {
    /**
     * Format a monetary amount for display in Ethiopian Birr (ETB).
     * Every fund/fee/payment/expense/salary figure in the app should be
     * rendered through this helper so the currency is consistent everywhere.
     */
    function money($amount): string
    {
        return 'ETB '.number_format((float) $amount, 2);
    }
}
