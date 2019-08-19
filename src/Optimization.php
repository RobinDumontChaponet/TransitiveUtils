<?php

namespace Transitive\Utils;

function humanWeight($bytes, $decimals = 2) {
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);

    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)).@$sz[$factor];
}

abstract class Optimization
{
    public static function minify($src)
    {
        // Nothing there (but us chickens) anymore... for now.
        // It didn't work well anyway...
    }

    public static function newTimer(): Timed
    {
        return new Timed();
    }

    public static function cacheBust(string $src): string
    {
        $path = pathinfo($src);

        return $path['dirname'].'/'.$path['filename'].'.'.filemtime($src).'.'.$path['extension'];
    }

    public static function listIncludes(): void
    {
        $includes = get_included_files();

        echo '<details><summary>', count($includes), ' include(s)', '</summary>', PHP_EOL;
        echo '<ul>';
        foreach ($includes as $filename)
            echo '<li>', $filename, '</li>', PHP_EOL;
        echo '</ul></details>', PHP_EOL;
    }
}

class Timed
{
    private $start;

    public function __construct()
    {
        $this->start = getrusage();
    }

    private static function _getrtime($ru, $rus, $index)
    {
        return ($ru["ru_$index.tv_sec"] * 1000 + intval($ru["ru_$index.tv_usec"] / 1000))
         - ($rus["ru_$index.tv_sec"] * 1000 + intval($rus["ru_$index.tv_usec"] / 1000));
    }

    public function printResult(): void
    {
        $ru = getrusage();
        $time = microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'];

        echo '<dl>';
        echo '<dt>xDebug</dt><dd>', (extension_loaded('xdebug') ? 'enabled' : 'disabled'), '</dd>';
        echo '<dt>Memory</dt><dd>', humanWeight(memory_get_usage()), '; Peak: ', humanWeight(memory_get_peak_usage()), '</dd>';
        echo '<dt>Process Time</dt><dd> ', $time * 1000, ' ms';

        echo '<dt>utime (compute)</dt><dd>', self::_getrtime($ru, $this->start, 'utime'), ' ms</dd>';
        echo '<dt>stime (syscall)</dt><dd>', self::_getrtime($ru, $this->start, 'stime'), ' ms</dd>';
        echo '</dl>';
    }
}
