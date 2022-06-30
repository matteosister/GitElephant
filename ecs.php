<?php

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $configurator): void {
    $configurator->paths(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $configurator->sets([
        SetList::CLEAN_CODE,
        SetList::PSR_12
    ]);

    $configurator->skip(['Unused variable $deleted.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'], 'Unused variable $new.' => ['src/GitElephant/Objects/Diff/DiffChunk.php']]);
};
