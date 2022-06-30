<?php

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ECSConfig $configurator): void {
    $configurator->paths([__DIR__ . '/src', __DIR__ . '/tests']);

    // A. full sets
    $configurator->sets([
        SetList::CLEAN_CODE,
        SetList::PSR_12
    ]);

    // B. standalone rule
    $configurator->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $configurator->skip(['Unused variable $deleted.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'], 'Unused variable $new.' => ['src/GitElephant/Objects/Diff/DiffChunk.php']]);
};
