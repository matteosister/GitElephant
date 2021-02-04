<?php

declare(strict_types=1);

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();
    $services->set(ArraySyntaxFixer::class)->call('configure', [
        'syntax' => 'short'
    ]);

    $services->set(LineLengthFixer::class)->call('configure', [
        "max_line_length" => 100,
        "break_long_lines" => true, # default: true
        "inline_short_lines" => false # default: true
    ]);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('sets', ['clean-code', 'psr12']);

    $parameters->set('paths', [__DIR__ . '/src', __DIR__ . '/tests']);

    $parameters->set('skip', ['Unused variable $deleted.' => ['src/GitElephant/Objects/Diff/DiffChunk.php'], 'Unused variable $new.' => ['src/GitElephant/Objects/Diff/DiffChunk.php']]);
};
