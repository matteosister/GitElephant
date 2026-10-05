<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSets([
        SetList::CLEAN_CODE,
        SetList::PSR_12,
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ])
    ->withSkip([
        'Unused variable $deleted.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'],
        'Unused variable $new.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'],
    ]);
