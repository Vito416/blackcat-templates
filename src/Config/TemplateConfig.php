<?php

declare(strict_types=1);

namespace BlackCat\Templates\Config;

use InvalidArgumentException;

final class TemplateConfig
{
    /** @var array<string,mixed> */
    private array $payload;

    private function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    public static function fromFile(string $path): self
    {
        if (!is_file($path)) {
            throw new InvalidArgumentException("Template config missing: {$path}");
        }

        $payload = require $path;
        if (!is_array($payload)) {
            throw new InvalidArgumentException('Template config must return array.');
        }

        $profileEnv = self::loadProfileEnv($payload);
        return new self(self::resolve($payload, $profileEnv));
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function fromArray(array $payload): self
    {
        return new self(self::resolve($payload, []));
    }

    public function templatesDir(): string
    {
        return (string) ($this->payload['templates_dir'] ?? (__DIR__ . '/../../templates'));
    }

    public function catalogFile(): string
    {
        if (isset($this->payload['catalog_file'])) {
            return (string) $this->payload['catalog_file'];
        }

        return $this->templatesDir() . '/catalog.php';
    }

    public function storageDir(): string
    {
        return (string) ($this->payload['storage_dir'] ?? (__DIR__ . '/../../var'));
    }

    public function telemetryFile(): ?string
    {
        $file = $this->payload['telemetry']['prometheus_file'] ?? null;
        return $file ? (string) $file : null;
    }

    /**
     * @return array<string,string>
     */
    public function integrations(): array
    {
        $integrations = $this->payload['integrations'] ?? [];
        if (!is_array($integrations)) {
            return [];
        }

        $normalized = [];
        foreach ($integrations as $key => $value) {
            $normalized[(string) $key] = (string) $value;
        }
        return $normalized;
    }

    /**
     * @return list<string>
     */
    public function allowedExtensions(): array
    {
        $allowed = $this->payload['security']['allowed_extensions'] ?? [];
        if (!is_array($allowed)) {
            return [];
        }

        return array_values(array_map(static fn($ext) => strtolower((string) $ext), $allowed));
    }

    public function disallowPhpTags(): bool
    {
        return (bool) ($this->payload['security']['disallow_php'] ?? true);
    }

    /**
     * @return list<string>
     */
    public function requiredSecurityIntegrations(): array
    {
        $required = $this->payload['security']['require_integrations'] ?? [];
        if (!is_array($required)) {
            return [];
        }

        return array_values(array_map(static fn($value) => (string) $value, $required));
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function resolve(mixed $value, array $profileEnv): mixed
    {
        if (is_string($value)) {
            if (preg_match('/^\$\{env:([^}]+)\}/', $value, $matches)) {
                return $profileEnv[$matches[1]] ?? getenv($matches[1]) ?: '';
            }
            if (preg_match('/^\$\{file:([^}]+)\}/', $value, $matches)) {
                return is_file($matches[1]) ? trim((string) file_get_contents($matches[1])) : '';
            }

            return $value;
        }

        if (is_array($value)) {
            $resolved = [];
            foreach ($value as $key => $inner) {
                $resolved[$key] = self::resolve($inner, $profileEnv);
            }
            return $resolved;
        }

        return $value;
    }

    /**
     * @param array<string,mixed> $payload
     * @return array<string,string>
     */
    private static function loadProfileEnv(array $payload): array
    {
        $profileConfig = $payload['config_profile'] ?? null;
        if (!is_array($profileConfig)) {
            return [];
        }

        $file = $profileConfig['file'] ?? null;
        if (!is_string($file) || !is_file($file)) {
            return [];
        }

        $targetEnv = $profileConfig['environment'] ?? null;
        $targetName = $profileConfig['name'] ?? null;

        $autoloadCandidates = [
            dirname(__DIR__, 3) . '/blackcat-config/src/autoload.php',
            dirname(__DIR__, 3) . '/blackcat-darkmesh-gateway/libs/legacy/blackcat-config/src/autoload.php',
        ];
        foreach ($autoloadCandidates as $autoload) {
            if (is_file($autoload)) {
                require_once $autoload;
                break;
            }
        }

        if (class_exists('\\BlackCat\\Config\\Config\\ProfileConfig')) {
            $profiles = \BlackCat\Config\Config\ProfileConfig::fromFile($file)->profiles();
            foreach ($profiles as $profile) {
                $match = ($targetName && $profile->name() === $targetName)
                    || ($targetEnv && $profile->environment() === $targetEnv);
                if ($match) {
                    return $profile->env();
                }
            }
        }

        $raw = require $file;
        if (!is_array($raw)) {
            return [];
        }

        foreach ($raw as $candidate) {
            $match = ($targetName && ($candidate['name'] ?? null) === $targetName)
                || ($targetEnv && ($candidate['environment'] ?? null) === $targetEnv);
            if ($match) {
                $env = $candidate['env'] ?? [];
                return is_array($env) ? array_map('strval', $env) : [];
            }
        }

        return [];
    }
}
