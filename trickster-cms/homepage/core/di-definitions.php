<?php

declare(strict_types=1);

use DI\Container;
use function DI\autowire;

return [
    VerifaliaAdapter::class => autowire()
        ->method('setConfigManager', DI\get(ConfigManager::class)),
    VerifyMailAdapter::class => autowire()
        ->method('setConfigManager', DI\get(ConfigManager::class)),
    uriSwitchLogics::class => autowire()
        ->method('setContainer', DI\get(Container::class))
        ->method('setController', DI\get(controller::class))
        ->method('setLanguagesManager', DI\get(LanguagesManager::class))
        ->method('setStructureManager', DI\get(structureManager::class))
        ->method('setLinksManager', DI\get(linksManager::class)),
];
