<?php

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\InlineConstructorDefaultToPropertyRector;
use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    RectorConfig::configure()
        ->withImportNames(removeUnusedImports: true)
        ->withAttributesSets(symfony: true, doctrine: true)
        ->withSymfonyContainerXml(__DIR__.'/var/cache/dev/App_KernelDevDebugContainer.xml')
        ->withComposerBased(
            doctrine: true,
            phpunit: true,
            symfony: true,
        );

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_83,
        LevelSetList::UP_TO_PHP_84,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::PRIVATIZATION,
        SetList::TYPE_DECLARATION,
        SetList::EARLY_RETURN,
        SetList::INSTANCEOF,
        SetList::PHP_84,
        DoctrineSetList::DOCTRINE_CODE_QUALITY,
        DoctrineSetList::TYPED_COLLECTIONS,
    ]);

    $rectorConfig->paths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    // register a single rule
    $rectorConfig->rule(InlineConstructorDefaultToPropertyRector::class);

    // define sets of rules
    //    $rectorConfig->sets([
    //        LevelSetList::UP_TO_PHP_81
    //    ]);
};
