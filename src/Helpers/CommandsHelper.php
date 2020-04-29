<?php

namespace GiataCommands;

use DateTime;

class CommandsHelper
{
    public static function calcTime($startTime, $endTime)
    {
        $start = new DateTime($startTime);
        $end = new DateTime($endTime);
        $interval = $start->diff($end);
        return $interval->format('%hH %iM %sS');
    }
}
