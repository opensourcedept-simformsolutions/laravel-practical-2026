<?php

use Carbon\Carbon;

function format_date($value, $format = 'd M Y, h:i A')
{
    if (! $value) {
        return '-';
    }

    try {
        return Carbon::parse($value)->format($format);
    } catch (Exception $e) {
        return '-';
    }
}
