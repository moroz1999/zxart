<?php

declare(strict_types=1);

use Deptrac\Deptrac\Contract\Config\Collector\BoolConfig;
use Deptrac\Deptrac\Contract\Config\Collector\DirectoryConfig;
use Deptrac\Deptrac\Contract\Config\DeptracConfig;
use Deptrac\Deptrac\Contract\Config\Layer;
use Deptrac\Deptrac\Contract\Config\Ruleset;

return static function (DeptracConfig $config): void {
    $nonLegacyDirectory = DirectoryConfig::create('project/core/ZxArt/.*');

    $config
        ->paths('project', 'trickster-cms')
        ->baseline('deptrac.baseline.yaml')
        ->layers(
            $nonLegacy = Layer::withName('NonLegacy')->collectors(
                $nonLegacyDirectory,
            ),
            $legacy = Layer::withName('Legacy')->collectors(
                BoolConfig::create()
                    ->must(DirectoryConfig::create('(project|trickster-cms)/.*'))
                    ->mustNot($nonLegacyDirectory),
            ),
        )
        ->rulesets(
            Ruleset::forLayer($nonLegacy),
            Ruleset::forLayer($legacy)->accesses($nonLegacy),
        );
};
