<?php

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\EasyCodingStandard\ValueObject\Option;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    // $services->set(ArraySyntaxFixer::class)->call('configure', [
    //     'syntax' => 'short'
    // ]);

    // $services->set(LineLengthFixer::class)->call('configure', [
    //     "max_line_length" => 100,
    //     "break_long_lines" => true, # default: true
    //     "inline_short_lines" => false # default: true
    // ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::SETS, [
        SetList::CLEAN_CODE,
        SetList::PSR_12
    ]);

    $parameters->set(Option::PATHS, [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set(Option::SKIP, ['Unused variable $deleted.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'], 'Unused variable $new.' => ['src/GitElephant/Objects/Diff/DiffChunk.php']]);
};
