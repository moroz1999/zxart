<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Catalogues;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Actions\Arguments;
use ZxArt\Assistant\Actions\ReadAction;
use ZxArt\Assistant\Actions\ToolArgumentsException;
use ZxArt\Assistant\Catalogues\CategoriesDto;
use ZxArt\Assistant\Catalogues\CategoryDto;
use ZxArt\Assistant\Catalogues\FindTagsAction;
use ZxArt\Assistant\Catalogues\FoundTagsDto;
use ZxArt\Assistant\Catalogues\HardwareGroupDto;
use ZxArt\Assistant\Catalogues\HardwareListDto;
use ZxArt\Assistant\Catalogues\HardwareOptionDto;
use ZxArt\Assistant\Catalogues\ListCategoriesAction;
use ZxArt\Assistant\Catalogues\ListHardwareAction;
use ZxArt\Assistant\Catalogues\ListOptionsAction;
use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Groups\GroupTypes;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseFormatsProvider;
use ZxArt\Shared\LanguageCodes;
use ZxArt\Tags\Dbal\TagSearchDbal;
use ZxArt\Tags\Dto\FoundTagDto;
use ZxArt\ZxProdCategories\CategoryManageService;
use ZxArt\ZxProdCategories\Dto\CategoryNodeDto;

final class CatalogueActionsTest extends TestCase
{
    public function testTheListsTakeNoArgumentsAndRefuseAny(): void
    {
        $action = new ListOptionsAction($this->createStub(ReleaseFormatsProvider::class));

        self::assertSame([], Arguments::schema($action->schema())['properties']);
        $this->expectException(ToolArgumentsException::class);
        $this->expectExceptionMessage('Unknown fields: types');

        Arguments::parse('{"types": []}', $action->schema());
    }

    public function testListCategoriesAnswersTheTreeDepthFirstWithTheProductionsOfEachSubtree(): void
    {
        $service = $this->createStub(CategoryManageService::class);
        $service->method('getTree')->willReturn([
            new CategoryNodeDto(id: 1, parentId: null, level: 0, title: 'Games', titles: [1 => 'Games', 2 => 'Игры'], prods: 3, prodsTotal: 40, subCategories: 1),
            new CategoryNodeDto(id: 2, parentId: 1, level: 1, title: 'Arcade', titles: [1 => 'Arcade'], prods: 37, prodsTotal: 37, subCategories: 0),
        ]);
        $action = new ListCategoriesAction($service);

        self::assertInstanceOf(ReadAction::class, $action);
        self::assertSame('list_categories', $action->name());
        self::assertEquals(new CategoriesDto([
            new CategoryDto(id: 1, parentId: null, level: 0, title: 'Games', prods: 40),
            new CategoryDto(id: 2, parentId: 1, level: 1, title: 'Arcade', prods: 37),
        ]), $this->read($action, '{}'));
    }

    public function testListHardwareAnswersTheCodesByGroupWithTheirNames(): void
    {
        $catalog = $this->createStub(HardwareCatalogService::class);
        $catalog->method('getGroupedCodes')->willReturn(['computers' => ['zx48', 'zx128'], 'sound' => ['ay']]);
        $catalog->method('getLabels')->willReturn([
            'zx48' => ['name' => 'ZX Spectrum 48K', 'shortName' => '48K'],
            'zx128' => ['name' => 'ZX Spectrum 128K', 'shortName' => '128K'],
            'ay' => ['name' => 'AY-3-8910', 'shortName' => 'AY'],
        ]);
        $action = new ListHardwareAction($catalog);

        self::assertSame('list_hardware', $action->name());
        self::assertEquals(new HardwareListDto([
            new HardwareGroupDto('computers', [new HardwareOptionDto('zx48', 'ZX Spectrum 48K'), new HardwareOptionDto('zx128', 'ZX Spectrum 128K')]),
            new HardwareGroupDto('sound', [new HardwareOptionDto('ay', 'AY-3-8910')]),
        ]), $this->read($action, '{}'));
    }

    public function testListOptionsAnswersTheFixedValueListsTheWritesAndSearchesAccept(): void
    {
        $formats = $this->createStub(ReleaseFormatsProvider::class);
        $formats->method('getReleaseFormats')->willReturn(['tap', 'trd']);
        $action = new ListOptionsAction($formats);

        $options = $this->read($action, '{}');

        self::assertSame('list_options', $action->name());
        self::assertSame(array_column(LegalStatus::cases(), 'value'), $options->legalStatuses);
        self::assertSame(ReleaseTypes::getAllValues(), $options->releaseTypes);
        self::assertSame(['tap', 'trd'], $options->releaseFormats);
        self::assertSame(LanguageCodes::ALL, $options->languages);
        self::assertSame(GroupTypes::LIST, $options->groupTypes);
        self::assertSame(AuthorshipRoles::LIST, $options->authorRoles);
        self::assertSame(GroupMemberRoles::LIST, $options->memberRoles);
    }

    public function testFindTagsAnswersTheTagsOfThePhraseWithinTheLimitKeptUpToAHundred(): void
    {
        $tags = [new FoundTagDto(1, 'Games', '', 3000)];
        $dbal = $this->createMock(TagSearchDbal::class);
        $dbal->expects(self::exactly(3))->method('find')
            ->with('game', self::callback(static fn(int $limit): bool => in_array($limit, [5, 20, 100], true)))
            ->willReturn($tags);
        $action = new FindTagsAction($dbal);

        self::assertSame('find_tags', $action->name());
        self::assertSame(['phrase'], Arguments::schema($action->schema())['required']);
        self::assertEquals(new FoundTagsDto($tags), $this->read($action, '{"phrase": "game", "limit": 5}'));
        $this->read($action, '{"phrase": "game"}');
        $this->read($action, '{"phrase": "game", "limit": 1000}');
    }

    private function read(ReadAction $action, string $json): mixed
    {
        return $action->read(Arguments::parse($json, $action->schema()));
    }
}
