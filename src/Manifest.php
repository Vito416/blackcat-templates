<?php

declare(strict_types=1);

namespace BlackCat\Templates;

/**
 * Auto-generated descriptor stub to anchor blackcat-templates source tree.
 * Populate orchestrators/services per docs/ROADMAP.md.
 */
final class Manifest
{
    public const REPOSITORY = 'blackcat-templates';

    public static function describe(): array
    {
        return [
            'repository' => self::REPOSITORY,
            'role' => 'Repo/project scaffold templates.',
            'integrations' => 'Provides boilerplates for new modules, CI workflows, READMEs; CLI (`bin/template`) for README scaffolds.',
            'status' => 'bootstrap',
        ];
    }
}
