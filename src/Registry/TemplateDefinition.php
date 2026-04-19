<?php

declare(strict_types=1);

namespace BlackCat\Templates\Registry;

use JsonSerializable;
use RuntimeException;

final class TemplateDefinition implements JsonSerializable
{
    /** @var array<int,array{token:string,description:string,required:bool,default:?string}> */
    private array $placeholders;
    /** @var array<string,mixed> */
    private array $metadata;

    /**
     * @param array<int,array{token:string,description:string,required:bool,default:?string}> $placeholders
     * @param array<string,mixed> $metadata
     * @param list<string> $tags
     * @param list<string> $integrations
     */
    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $description,
        private readonly string $path,
        array $placeholders,
        array $metadata,
        private readonly array $tags,
        private readonly array $integrations
    ) {
        $this->placeholders = $placeholders;
        $this->metadata = $metadata;
    }

    /**
     * @param array<string,mixed> $payload
     */
    public static function fromArray(array $payload, string $baseDir): self
    {
        $id = (string) ($payload['id'] ?? '');
        $name = (string) ($payload['name'] ?? '');
        $description = (string) ($payload['description'] ?? '');
        $file = $payload['file'] ?? ($payload['path'] ?? null);
        $tags = $payload['tags'] ?? [];
        $integrations = $payload['integrations'] ?? [];
        $placeholders = $payload['placeholders'] ?? [];
        $metadata = $payload['metadata'] ?? [];

        if ($id === '' || $name === '' || $description === '' || $file === null) {
            throw new RuntimeException('Invalid template definition payload.');
        }

        $resolvedPath = self::resolvePath($file, $baseDir);
        if (!is_file($resolvedPath)) {
            throw new RuntimeException("Template file not found for {$id}: {$resolvedPath}");
        }

        $placeholderObjects = [];
        if (!is_array($placeholders)) {
            $placeholders = [];
        }
        if (!is_array($metadata)) {
            $metadata = [];
        }

        foreach ($placeholders as $placeholder) {
            $token = (string) ($placeholder['token'] ?? '');
            if ($token === '') {
                continue;
            }
            $placeholderObjects[] = [
                'token' => strtoupper($token),
                'description' => (string) ($placeholder['description'] ?? ''),
                'required' => array_key_exists('required', $placeholder)
                    ? (bool) $placeholder['required']
                    : true,
                'default' => isset($placeholder['default']) ? (string) $placeholder['default'] : null,
            ];
        }

        $metadataObject = self::normalizeMetadata($metadata);

        $tagList = [];
        if (is_array($tags)) {
            foreach ($tags as $tag) {
                $tagList[] = (string) $tag;
            }
        }

        $integrationList = [];
        if (is_array($integrations)) {
            foreach ($integrations as $integration) {
                $integrationList[] = (string) $integration;
            }
        }

        return new self($id, $name, $description, $resolvedPath, $placeholderObjects, $metadataObject, $tagList, $integrationList);
    }

    private static function resolvePath(mixed $file, string $baseDir): string
    {
        $path = (string) $file;
        if (str_starts_with($path, '/')) {
            return $path;
        }

        return rtrim($baseDir, '/\') . '/' . ltrim($path, '/');
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function path(): string
    {
        return $this->path;
    }

    /**
     * @return array<int,array{token:string,description:string,required:bool,default:?string}>
     */
    public function placeholders(): array
    {
        return $this->placeholders;
    }

    /**
     * @return array<string,mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * @return list<string>
     */
    public function tags(): array
    {
        return $this->tags;
    }

    /**
     * @return list<string>
     */
    public function integrations(): array
    {
        return $this->integrations;
    }

    /**
     * @return array{
     *   id:string,
     *   name:string,
     *   description:string,
     *   path:string,
     *   placeholders:array<int,array{token:string,description:string,required:bool,default:?string}>,
     *   metadata:array<string,mixed>,
     *   tags:list<string>,
     *   integrations:list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'path' => $this->path,
            'placeholders' => $this->placeholders,
            'metadata' => $this->metadata,
            'tags' => $this->tags,
            'integrations' => $this->integrations,
        ];
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * @param array<string,mixed> $metadata
     * @return array<string,mixed>
     */
    private static function normalizeMetadata(array $metadata): array
    {
        $normalized = [];
        foreach ($metadata as $key => $value) {
            $normalized[(string) $key] = self::normalizeMetadataValue($value);
        }

        return $normalized;
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    private static function normalizeMetadataValue(mixed $value): mixed
    {
        if (is_array($value)) {
            $normalized = [];
            foreach ($value as $key => $inner) {
                $normalized[(string) $key] = self::normalizeMetadataValue($inner);
            }

            return $normalized;
        }

        return $value;
    }
}
