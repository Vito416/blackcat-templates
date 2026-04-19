<?php

declare(strict_types=1);

namespace BlackCat\Templates\Release;

use RuntimeException;

final class ReleaseManifestValidator
{
    /**
     * @return array{releaseVersion:string,generatedAt:string,variants:array<string,array{variant:string,templateId:string,file:string,sha256:string,bytes:int}>}
     */
    public function validateFile(string $path): array
    {
        if ($path === '') {
            throw new RuntimeException('Manifest file path must not be empty.');
        }

        if (!is_file($path)) {
            throw new RuntimeException('Manifest file not found: ' . $path);
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException('Unable to read manifest file: ' . $path);
        }

        $decoded = json_decode($content, true);
        if (!is_array($decoded)) {
            throw new RuntimeException('Manifest must be valid JSON.');
        }

        $errors = [];

        $releaseVersion = $this->requireString($decoded, 'releaseVersion', $errors);
        $generatedAt = $this->requireString($decoded, 'generatedAt', $errors);
        $variants = $decoded['variants'] ?? null;
        if (!is_array($variants) || $variants === [] || array_is_list($variants)) {
            $errors[] = 'Top-level key "variants" must be a non-empty object keyed by variant name.';
        }

        $normalizedVariants = [];
        if (is_array($variants)) {
            foreach ($variants as $key => $variant) {
                if (!is_array($variant)) {
                    $errors[] = 'Variant entry "' . (string) $key . '" must be an object.';
                    continue;
                }

                $variantName = $this->requireString($variant, 'variant', $errors, 'variants.' . $key);
                $templateId = $this->requireString($variant, 'templateId', $errors, 'variants.' . $key);
                $file = $this->requireString($variant, 'file', $errors, 'variants.' . $key);
                $sha256 = $this->requireString($variant, 'sha256', $errors, 'variants.' . $key);
                $bytes = $this->requireInt($variant, 'bytes', $errors, 'variants.' . $key);

                if ($variantName !== null && is_string($key) && $key !== $variantName) {
                    $errors[] = 'Variant key "' . $key . '" does not match variant value "' . $variantName . '".';
                }

                if ($variantName !== null) {
                    $normalizedVariants[(string) $variantName] = [
                        'variant' => $variantName,
                        'templateId' => $templateId ?? '',
                        'file' => $file ?? '',
                        'sha256' => $sha256 ?? '',
                        'bytes' => $bytes ?? 0,
                    ];
                }
            }
        }

        if ($errors !== []) {
            throw new RuntimeException("Invalid release manifest:\n- " . implode("\n- ", $errors));
        }

        assert(is_string($releaseVersion));
        assert(is_string($generatedAt));

        return [
            'releaseVersion' => $releaseVersion,
            'generatedAt' => $generatedAt,
            'variants' => $normalizedVariants,
        ];
    }

    /**
     * @param array<string,mixed> $data
     * @param list<string> $errors
     */
    private function requireString(array $data, string $key, array &$errors, string $path = ''): ?string
    {
        $label = $path === '' ? $key : $path . '.' . $key;
        if (!array_key_exists($key, $data)) {
            $errors[] = 'Missing required key: ' . $label;
            return null;
        }

        if (!is_string($data[$key]) || trim($data[$key]) === '') {
            $errors[] = 'Key "' . $label . '" must be a non-empty string.';
            return null;
        }

        return $data[$key];
    }

    /**
     * @param array<string,mixed> $data
     * @param list<string> $errors
     */
    private function requireInt(array $data, string $key, array &$errors, string $path = ''): ?int
    {
        $label = $path === '' ? $key : $path . '.' . $key;
        if (!array_key_exists($key, $data)) {
            $errors[] = 'Missing required key: ' . $label;
            return null;
        }

        $value = $data[$key];
        if (is_int($value)) {
            return $value;
        }

        if (is_string($value) && ctype_digit($value)) {
            return (int) $value;
        }

        $errors[] = 'Key "' . $label . '" must be an integer.';
        return null;
    }
}
