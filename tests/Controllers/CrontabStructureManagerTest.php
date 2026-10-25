<?php

declare(strict_types=1);

namespace ZxArt\Tests\Controllers;

use DI\Container;
use DI\ContainerBuilder;
use PHPUnit\Framework\TestCase;
use ReflectionProperty;
use ZxArt\Authors\Services\AuthorImportService;
use ZxArt\Authors\UseCases\ImportAuthorHandler;
use ZxArt\Controllers\Crontab;
use ZxArt\Import\Press\DataUpdater\ArticleParsedDataUpdater;

/**
 * Cron jobs create authors, prods and other catalogue elements, and those
 * catalogues live under the admin root. The whole cron request must therefore
 * run on the admin structure manager, including the services it pulls in.
 */
class CrontabStructureManagerTest extends TestCase
{
    public function testCrontabRunsOnAdminRoot(): void
    {
        $crontab = $this->buildContainer()->get(Crontab::class);
        $this->assertInstanceOf(Crontab::class, $crontab);

        $structureManager = $this->readProperty($crontab, 'structureManager');

        $this->assertSame('admin_root', $structureManager->getRootElementMarker());
    }

    public function testPressParserCreatesAuthorsOnAdminRoot(): void
    {
        $crontab = $this->buildContainer()->get(Crontab::class);
        $this->assertInstanceOf(Crontab::class, $crontab);

        $pressDataUpdater = $this->readProperty($crontab, 'pressDataUpdater');
        $this->assertInstanceOf(ArticleParsedDataUpdater::class, $pressDataUpdater);
        $authorImportService = $this->readProperty($pressDataUpdater, 'authorImportService');
        $this->assertInstanceOf(AuthorImportService::class, $authorImportService);
        $importAuthorHandler = $this->readProperty($authorImportService, 'importAuthorHandler');
        $this->assertInstanceOf(ImportAuthorHandler::class, $importAuthorHandler);
        $structureManager = $this->readProperty($importAuthorHandler, 'structureManager');

        $this->assertSame('admin_root', $structureManager->getRootElementMarker());
    }

    private function buildContainer(): Container
    {
        $definitions = array_merge(
            include ROOT_PATH . 'trickster-cms/cms/core/di-definitions.php',
            include ROOT_PATH . 'project/core/di-definitions.php',
        );
        $builder = new ContainerBuilder();
        $builder->addDefinitions($definitions);
        return $builder->build();
    }

    private function readProperty(object $object, string $name): mixed
    {
        return (new ReflectionProperty($object, $name))->getValue($object);
    }
}
