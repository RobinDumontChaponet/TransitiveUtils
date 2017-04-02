<?php

namespace Transitive\Utils;

abstract class Sessions
{
    public static $keyPrefix = '';

    public static function isStarted(): bool
    {
        return session_status() != PHP_SESSION_NONE;
    }

    public static function start(/*...*/): void
    {
    if (!self::isStarted())
        session_start();

        // .... @TO-DO ?
    }

    public static function getId()
    {
        if (self::isStarted())
            return session_id();

        return false;
    }

    public static function set(string $key, $value = null): bool
    {
        if(self::isStarted()) {
            $_SESSION[self::$keyPrefix.$key] = $value;

            return true;
        }

        return false;
    }

    public static function isset(string $key): bool
    {
        return self::isStarted() && isset($_SESSION[self::$keyPrefix.$key]);
    }
    public static function exist(string $key): bool
    {
		return self::isset($key);
    }

    public static function get(string $key)
    {
        if(self::isset($key))
            return $_SESSION[self::$keyPrefix.$key];
    }

    public static function delete(string $key): void
    {
        if(self::isset($key))
            unset($_SESSION[self::$keyPrefix.$key]);
    }

    public static function destroy(): void
    {
        if (self::isStarted()) {
            session_unset();
            session_destroy();
        }
    }
}
