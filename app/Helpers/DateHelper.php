<?php

namespace App\Helpers;

use Carbon\Carbon;

class DateHelper
{
    public static function humanizeDate($date)
    {
        return Carbon::parse($date)->diffForHumans();
    }
}
