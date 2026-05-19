<?php

namespace Transitive\Utils;

abstract class Signals
{
    // http://php.net/manual/fr/function.posix-kill.php#118228
    public static function isRunning(int $pid): bool
    {
        if($pid <= 0)
            return false;

        if(0 == strncasecmp(PHP_OS, 'win', 3)) {
            $out = [];
            exec("TASKLIST /FO LIST /FI \"PID eq $pid\"", $out);

            return count($out) > 1;
        }

        if(!function_exists('posix_kill'))
            return false;

        return posix_kill($pid, 0) || posix_get_last_error() === 1;
    }
}
