<?php

namespace Philip\LaravelInstagres\Support;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Helper class for safely managing .env file modifications
 */
class EnvManager
{
    protected string $basePath;

    protected string $envPath;

    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->envPath = $basePath.'/.env';
    }

    /**
     * Check if .env file exists
     */
    public function exists(): bool
    {
        return File::exists($this->envPath);
    }

    /**
     * Set or update an environment variable in the .env file
     *
     * @param  string  $key  The environment variable name
     * @param  string  $value  The value to set
     * @param  bool  $createBackup  Whether to create a backup before modifying
     * @return bool Success status
     */
    public function set(string $key, string $value, bool $createBackup = true): bool
    {
        if (! $this->exists()) {
            // Create new .env file if it doesn't exist
            return $this->create([$key => $value]);
        }

        if ($createBackup) {
            $this->backup();
        }

        try {
            $envContent = File::get($this->envPath);
        } catch (\Exception $e) {
            throw new RuntimeException("Failed to read .env file at {$this->envPath}: {$e->getMessage()}");
        }

        // Escape special characters in value for shell usage
        $escapedValue = $this->escapeValue($value);

        // Check if the key already exists (active or commented)
        $activePattern = "/^{$key}=.*/m";
        $commentedPattern = "/^#\s*{$key}=.*/m";

        if (preg_match($activePattern, $envContent)) {
            // Update existing active key
            $newContent = preg_replace($activePattern, "{$key}={$escapedValue}", $envContent);
        } elseif (preg_match($commentedPattern, $envContent)) {
            // Replace commented key with active key
            $newContent = preg_replace($commentedPattern, "{$key}={$escapedValue}", $envContent);
        } else {
            // Add new key at the end
            $newContent = rtrim($envContent)."\n{$key}={$escapedValue}\n";
        }

        try {
            File::put($this->envPath, $newContent);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Set multiple environment variables at once
     *
     * @param  array<string, string>  $variables  Key-value pairs to set
     * @param  bool  $createBackup  Whether to create a backup before modifying
     * @return bool Success status
     */
    public function setMultiple(array $variables, bool $createBackup = true): bool
    {
        if (empty($variables)) {
            return true;
        }

        if ($createBackup && $this->exists()) {
            $this->backup();
        }

        foreach ($variables as $key => $value) {
            if (! $this->set($key, $value, false)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get an environment variable value from the .env file
     *
     * @param  string  $key  The environment variable name
     * @return string|null The value or null if not found
     */
    public function get(string $key): ?string
    {
        if (! $this->exists()) {
            return null;
        }

        try {
            $envContent = File::get($this->envPath);
        } catch (\Exception $e) {
            return null;
        }

        $pattern = "/^{$key}=(.*)$/m";

        if (preg_match($pattern, $envContent, $matches)) {
            return $this->unescapeValue($matches[1]);
        }

        return null;
    }

    /**
     * Create a new .env file with initial variables
     *
     * @param  array<string, string>  $variables  Initial key-value pairs
     * @return bool Success status
     */
    protected function create(array $variables): bool
    {
        $content = '';

        foreach ($variables as $key => $value) {
            $content .= "{$key}={$this->escapeValue($value)}\n";
        }

        try {
            File::put($this->envPath, $content);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Create a backup of the .env file
     *
     * @return bool Success status
     */
    public function backup(): bool
    {
        if (! $this->exists()) {
            return false;
        }

        $backupPath = $this->envPath.'.backup';

        try {
            File::copy($this->envPath, $backupPath);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Escape a value for safe storage in .env file
     *
     * @param  string  $value  The value to escape
     * @return string The escaped value
     */
    protected function escapeValue(string $value): string
    {
        // If value contains spaces or special characters, wrap in quotes
        if (preg_match('/[\s#"]/', $value)) {
            // Escape existing quotes
            $value = Str::replace('"', '\\"', $value);

            return "\"{$value}\"";
        }

        return $value;
    }

    /**
     * Unescape a value read from .env file
     *
     * @param  string  $value  The value to unescape
     * @return string The unescaped value
     */
    protected function unescapeValue(string $value): string
    {
        $value = trim($value);

        // Remove surrounding quotes if present
        if (preg_match('/^"(.*)"$/', $value, $matches)) {
            $value = $matches[1];
            // Unescape quotes
            $value = Str::replace('\\"', '"', $value);
        }

        return $value;
    }
}
