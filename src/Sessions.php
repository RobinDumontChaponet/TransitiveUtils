<?php

namespace Transitive\Utils;

abstract class Sessions
{
	public static string $keyPrefix = '';

	private static bool $readOnly = false;
	private static bool $loaded = false;
	private static array $options = [];

	public static function isStarted(): bool
	{
		return session_status() === PHP_SESSION_ACTIVE;
	}
	public static function isEnabled(): bool
	{
		return session_status() !== PHP_SESSION_DISABLED;
	}

	public static function start(bool $readAndClose = true, array $options = []): void
	{
		if (self::isStarted()) {
			return;
		}

		self::$readOnly = $readAndClose;
		self::$options = $options;

		session_start([
			...$options,
			'read_and_close' => $readAndClose,
		]);

		self::$loaded = true;
	}

	public static function startWritable(array $options = []): void
	{
		self::start(false, $options);
		self::$readOnly = false;
	}

	public static function close(): void
	{
		if (self::isStarted()) {
			session_write_close();
		}

		self::$readOnly = true;
	}

	public static function getName(): string
	{
		return session_name();
	}

	public static function setName(string $name): void
	{
		if (self::isStarted()) {
			throw new \LogicException('Cannot change session name after session start.');
		}

		session_name($name);
	}

	public static function getId(): string|false
	{
		$id = session_id();

		return $id !== '' ? $id : false;
	}

	public static function regenerateId(bool $deleteOldSession = true): bool
	{
		return session_regenerate_id($deleteOldSession);
	}

	public static function set(string $key, mixed $value = null): bool
	{
		self::ensureWritable();

		$_SESSION[self::key($key)] = $value;

		return true;
	}

	public static function has(string $key): bool
	{
		self::ensureReadable();

		return isset($_SESSION[self::key($key)]);
	}

	// legacy
	public static function isset(string $key): bool
	{
		return self::has($key);
	}

	// legacy
	public static function exist(string $key): bool
	{
		return self::exists($key);
	}

	public static function exists(string $key): bool
	{
		self::ensureReadable();

		return array_key_exists(self::key($key), $_SESSION);
	}

	public static function get(string $key, mixed $default = null): mixed
	{
		self::ensureReadable();

		return $_SESSION[self::key($key)] ?? $default;
	}

	public static function delete(string $key): void
	{
		self::ensureWritable();

		unset($_SESSION[self::key($key)]);
	}

	public static function pull(string $key, mixed $default = null): mixed
	{
		self::ensureWritable();

		$key = self::key($key);
		$value = $_SESSION[$key] ?? $default;

		unset($_SESSION[$key]);

		return $value;
	}

	public static function destroy(): void
	{
		if (!self::isStarted()) {
			session_start(self::$options);
		}

		$_SESSION = [];

		if (ini_get('session.use_cookies')) {
			$params = session_get_cookie_params();

			setcookie(
				session_name(),
				'',
				[
					'expires' => time() - 42000,
					'path' => $params['path'],
					'domain' => $params['domain'],
					'secure' => $params['secure'],
					'httponly' => $params['httponly'],
					'samesite' => $params['samesite'] ?? 'Lax',
				]
			);
		}

		session_destroy();

		self::$readOnly = true;
		self::$loaded = false;
	}

	private static function ensureReadable(): void
	{
		if (!self::$loaded) {
			self::start(true);
		}
	}

	private static function ensureWritable(): void
	{
		if (self::isStarted() && !self::$readOnly) {
			return;
		}

		if (self::isStarted()) {
			session_write_close();
		}

		session_start(self::$options);

		self::$readOnly = false;
		self::$loaded = true;
	}

	private static function key(string $key): string
	{
		return self::$keyPrefix . $key;
	}
}
