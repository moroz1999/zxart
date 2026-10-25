<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\BoolConfig;
use Deptrac\Deptrac\Contract\Config\Collector\ClassNameRegexConfig;
use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\Collector\InheritsConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

/*
 * The layers are disjoint, so each class falls under one ruleset:
 *
 * - Dbal: `<Module>/Dbal/`, hand-written table access. Uses no repository.
 * - Repositories: `<Module>/Repositories/*Repository`, the services that supply,
 *   save and delete an entity and hold no business logic.
 * - MisnamedRepository: a `*Repository` anywhere else, the Dbal layer included.
 *   Nothing may use one, so any use of one is a violation.
 * - NonLegacy: the rest of `ZxArt\`.
 * - Entities: the CMS structure elements (`structureElement` and every class
 *   inheriting it). They are the entities for now, so the modern layers may
 *   use them.
 * - Legacy: the rest of the CMS and the legacy project code.
 */
return static function (DeptracConfig $config): void {
    $nonLegacyDirectory = DirectoryConfig::create('project/core/ZxArt/.*');
    $dbalDirectory = DirectoryConfig::create('project/core/ZxArt/.*/Dbal/.*');
    $repositoriesDirectory = DirectoryConfig::create('project/core/ZxArt/.*/Repositories/.*');
    $repositoryName = ClassNameRegexConfig::create('/Repository$/');
    $entityClass = InheritsConfig::create('structureElement');
    $entityBase = ClassNameRegexConfig::create('/^structureElement$/');
    $misnamedRepository = BoolConfig::create()
        ->must($nonLegacyDirectory)
        ->must($repositoryName)
        ->mustNot($repositoriesDirectory);

    $config
        ->paths('project', 'trickster-cms')
        ->baseline('deptrac.baseline.yaml')
        ->layers(
            $nonLegacy = Layer::withName('NonLegacy')->collectors(
                BoolConfig::create()
                    ->must($nonLegacyDirectory)
                    ->mustNot($dbalDirectory)
                    ->mustNot($repositoriesDirectory)
                    ->mustNot($repositoryName),
            ),
            $dbal = Layer::withName('Dbal')->collectors(
                BoolConfig::create()
                    ->must($dbalDirectory)
                    ->mustNot($repositoryName),
            ),
            $repositories = Layer::withName('Repositories')->collectors(
                BoolConfig::create()
                    ->must($repositoriesDirectory)
                    ->must($repositoryName),
            ),
            $misnamed = Layer::withName('MisnamedRepository')->collectors(
                $misnamedRepository,
            ),
            $entities = Layer::withName('Entities')->collectors(
                BoolConfig::create()->must($entityClass)->mustNot($nonLegacyDirectory),
                BoolConfig::create()->must($entityBase),
            ),
            $legacy = Layer::withName('Legacy')->collectors(
                BoolConfig::create()
                    ->must(DirectoryConfig::create('(project|trickster-cms)/.*'))
                    ->mustNot($nonLegacyDirectory)
                    ->mustNot($entityClass)
                    ->mustNot($entityBase),
            ),
        )
        ->rulesets(
            Ruleset::forLayer($nonLegacy)->accesses($dbal, $repositories, $entities),
            Ruleset::forLayer($dbal)->accesses($nonLegacy, $entities),
            Ruleset::forLayer($repositories)->accesses($nonLegacy, $dbal, $entities),
            Ruleset::forLayer($misnamed),
            Ruleset::forLayer($entities)->accesses($nonLegacy, $dbal, $repositories, $legacy),
            Ruleset::forLayer($legacy)->accesses($nonLegacy, $dbal, $repositories, $entities),
        );
};
