<?php

namespace Transitive\Utils;

abstract class Signals {
	// http://php.net/manual/fr/function.posix-kill.php#118228
	public static function isRunning($pid) {
		$isRunning = false;
		if(strncasecmp(PHP_OS, 'win', 3) == 0) {
			$out = [];
			exec("TASKLIST /FO LIST /FI \"PID eq $pid\"", $out);
			if(count($out) > 1)
				$isRunning = true;
		} elseif(posix_kill(intval($prevPid), 0))
            $isRunning = true;

		return $isRunning;
	}
}
