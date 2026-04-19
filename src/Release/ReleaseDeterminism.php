<?php

declare(strict_types=1);

namespace BlackCat\Templates\Release;

use DateTimeImmutable;
use DateTimeZone;

final class ReleaseDeterminism
{
    private const MANIFEST_TIMESTAMP_FORMAT = 'Y-m-d\TH:i:s.u\Z';

    /**
     * @param array<string,mixed> $payload
     */
    public static function generatedAt(array $payload): string
    {
        $payloadGeneratedAt = self::stringValue($payload['RELEASE_GENERATED_AT'] ?? null);
        if ($payloadGeneratedAt !== null) {
            $normalized = self::normalizeTimestamp($payloadGeneratedAt);
            if ($normalized !== null) {
                return $normalized;
            }
        }

        $sourceDateEpoch = getenv('SOURCE_DATE_EPOCH');
        if ($sourceDateEpoch !== false && trim($sourceDateEpoch) !== '') {
            $fromEpoch = self::fromSourceDateEpoch($sourceDateEpoch);
            if ($fromEpoch !== null) {
                return $fromEpoch;
            }
        }

        return (new DateTimeImmutable('now', new DateTimeZone('UTC')))->format(self::MANIFEST_TIMESTAMP_FORMAT);
    }

    private static function fromSourceDateEpoch(string $value): ?string
    {
        if (!preg_match('/^-?\d+$/', trim($value))) {
            return null;
        }

        $epoch = (int) trim($value);

        return (new DateTimeImmutable('@' . $epoch))
            ->setTimezone(new DateTimeZone('UTC'))
            ->format(self::MANIFEST_TIMESTAMP_FORMAT);
    }

    private static function normalizeTimestamp(string $value): ?string
    {
        try {
            return (new DateTimeImmutable($value))
                ->setTimezone(new DateTimeZone('UTC'))
                ->format(self::MANIFEST_TIMESTAMP_FORMAT);
        } catch (\Throwable $_exception) {
            return null;
        }
    }

    private static function stringValue(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
