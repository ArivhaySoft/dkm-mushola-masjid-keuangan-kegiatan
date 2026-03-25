<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PDO;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        $this->ensureMysqlTestingDatabaseExists();
        parent::setUp();
    }

    protected function ensureMysqlTestingDatabaseExists(): void
    {
        if (env('DB_CONNECTION') !== 'mysql') {
            return;
        }

        $this->hydrateDbEnvFromDotEnvFile();

        $host = env('DB_HOST', '127.0.0.1');
        $port = env('DB_PORT', '3306');
        $username = env('DB_USERNAME', 'root');
        $password = env('DB_PASSWORD', '');
        $database = env('DB_DATABASE', 'db_mushola_keuangan_test');

        try {
            $pdo = new PDO(
                "mysql:host={$host};port={$port};charset=utf8mb4",
                $username,
                $password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // Create dedicated testing database if it does not exist.
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

        } catch (\Throwable $e) {
            throw new RuntimeException('Unable to prepare MySQL testing database: '.$e->getMessage(), 0, $e);
        }
    }

    protected function hydrateDbEnvFromDotEnvFile(): void
    {
        $envPath = dirname(__DIR__).'/.env';
        if (!is_file($envPath)) {
            return;
        }

        $content = (string) file_get_contents($envPath);
        foreach (['DB_HOST', 'DB_PORT', 'DB_USERNAME', 'DB_PASSWORD'] as $key) {
            if (!env($key) || env($key) === 'root' || env($key) === '127.0.0.1') {
                $value = $this->extractEnvValue($content, $key);
                if ($value !== null && $value !== '') {
                    putenv("{$key}={$value}");
                    $_ENV[$key] = $value;
                    $_SERVER[$key] = $value;
                }
            }
        }
    }

    protected function extractEnvValue(string $content, string $key): ?string
    {
        if (!preg_match('/^'.preg_quote($key, '/').'=(.*)$/m', $content, $matches)) {
            return null;
        }

        $raw = trim($matches[1]);
        if ($raw === '') {
            return '';
        }

        if (
            (str_starts_with($raw, '"') && str_ends_with($raw, '"')) ||
            (str_starts_with($raw, "'") && str_ends_with($raw, "'"))
        ) {
            return substr($raw, 1, -1);
        }

        return $raw;
    }
}
