<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\BoolConfig;
use Deptrac\Deptrac\Contract\Config\Collector\ClassNameRegexConfig;
use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
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
 * - Legacy: the CMS and the legacy project code.
 */
return static function (DeptracConfig $config): void {
    $nonLegacyDirectory = DirectoryConfig::create('project/core/ZxArt/.*');
    $dbalDirectory = DirectoryConfig::create('project/core/ZxArt/.*/Dbal/.*');
    $repositoriesDirectory = DirectoryConfig::create('project/core/ZxArt/.*/Repositories/.*');
    $repositoryName = ClassNameRegexConfig::create('/Repository$/');
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
            $legacy = Layer::withName('Legacy')->collectors(
                BoolConfig::create()
                    ->must(DirectoryConfig::create('(project|trickster-cms)/.*'))
                    ->mustNot($nonLegacyDirectory),
            ),
        )
        ->rulesets(
            Ruleset::forLayer($nonLegacy)->accesses($dbal, $repositories),
            Ruleset::forLayer($dbal)->accesses($nonLegacy),
            Ruleset::forLayer($repositories)->accesses($nonLegacy, $dbal),
            Ruleset::forLayer($misnamed),
            Ruleset::forLayer($legacy)->accesses($nonLegacy, $dbal, $repositories),
        );
};
