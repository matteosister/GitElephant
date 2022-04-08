<?php

use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ContainerConfigurator $containerConfigurator): void {
    // A. full sets
    $containerConfigurator->import(SetList::PSR_12);

    // B. standalone rule
    $services = $containerConfigurator->services();
    $services->set(ArraySyntaxFixer::class)
             ->call('configure', [[ 'syntax' => 'short', ]]);
};