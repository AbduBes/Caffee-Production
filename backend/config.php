<?php

/**
 * Application configuration.
 *
 * Values are read from environment variables first. On hosts that don't
 * support environment variables (e.g. InfinityFree shared hosting), put them
 * in backend/config.local.php instead - copy config.local.example.php and fill
 * it in. config.local.php is git-ignored and must never be committed.
 */
class Config
{
    private static $local = null;

    private static function get($key, $default = null)
    {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return $value;
        }

        if (self::$local === null) {
            $file = __DIR__ . '/config.local.php';
            self::$local = is_readable($file) ? (require $file) : [];
        }
        if (array_key_exists($key, self::$local)) {
            return self::$local[$key];
        }

        if ($default !== null) {
            return $default;
        }
        throw new RuntimeException("Missing configuration value: $key");
    }

    public static function DB_HOST()     { return self::get('DB_HOST', 'localhost'); }
    public static function DB_PORT()     { return self::get('DB_PORT', '3306'); }
    public static function DB_NAME()     { return self::get('DB_NAME'); }
    public static function DB_USER()     { return self::get('DB_USER'); }
    public static function DB_PASSWORD() { return self::get('DB_PASSWORD', ''); }
    public static function JWT_SECRET()  { return self::get('JWT_SECRET'); }

    /** Show PHP errors in the browser. Only enable for local development. */
    public static function DEBUG()
    {
        return filter_var(self::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);
    }
}
