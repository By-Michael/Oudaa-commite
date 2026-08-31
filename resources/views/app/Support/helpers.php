<?php

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
