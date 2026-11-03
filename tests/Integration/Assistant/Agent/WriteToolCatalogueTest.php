<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant\Agent;

use controller;
use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Agent\PlanRefusedException;
use ZxArt\Assistant\Agent\WriteToolCatalogue;

/**
 * The write tools as the model learns them: a short list in the plan tool, the
 * arguments of one on request.
 */
final class WriteToolCatalogueTest extends TestCase
{
    private WriteToolCatalogue $catalogue;

    #[\Override]
    protected function setUp(): void
    {
        $this->catalogue = controller::getInstance()->getContainer()->get(WriteToolCatalogue::class);
    }

    public function testTheListNamesTheWriteToolsOnly(): void
    {
        $list = $this->catalogue->list();

        self::assertStringContainsString('update_prod, ', $list);
        self::assertStringNotContainsString('Changes the fields', $list);
        self::assertStringNotContainsString('search, ', $list);
    }

    public function testTheDescribeToolOffersTheWriteToolsByName(): void
    {
        $tool = $this->catalogue->describeTool();

        self::assertSame('describe_write_tool', $tool->name);
        $names = $tool->parameters['properties']['name']['enum'];
        self::assertContains('update_prod', $names);
        self::assertNotContains('search', $names);
    }

    public function testDescribingAWriteToolAnswersTheSchemaOfItsArguments(): void
    {
        $schema = json_decode($this->catalogue->describe('{"name":"update_release"}'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('update_release', $schema['name']);
        self::assertStringStartsWith('Changes the fields', $schema['description']);
        self::assertSame(['id', 'title', 'year', 'version', 'description', 'releaseType', 'releaseFormats', 'languages', 'hardware', 'publisherIds', 'prodId'], array_keys($schema['arguments']['properties']));
    }

    public function testDescribingAnActionOfTheAssistantAnswersItsOwnSchema(): void
    {
        $schema = json_decode($this->catalogue->describe('{"name":"update_author"}'), true, 512, JSON_THROW_ON_ERROR);

        self::assertSame('update_author', $schema['name']);
        self::assertSame(['id', 'title', 'realName', 'countryId', 'cityId', 'wikiLink'], array_keys($schema['arguments']['properties']));
    }

    public function testTheListNamesTheActionsOfTheAssistantToo(): void
    {
        self::assertStringContainsString('update_author', $this->catalogue->list());
        self::assertContains('update_author', $this->catalogue->describeTool()->parameters['properties']['name']['enum']);
    }

    public function testOnlyAWriteToolIsDescribed(): void
    {
        $this->expectException(PlanRefusedException::class);
        $this->expectExceptionMessage('search is not a write tool');

        $this->catalogue->describe('{"name":"search"}');
    }
}
