<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use countryElement;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Search\Services\EntitySearchService;
use ZxArt\Shared\EntityType;

/**
 * The phrase search over the six entity types, run on entities the test
 * wrote in the same process.
 */
#[RunTestsInSeparateProcesses]
final class EntitySearchTest extends EntityWriteTestCase
{
    private const int COUNTRY_POLAND = 47450;
    private const int COUNTRY_UNITED_KINGDOM = 47542;

    public function testEveryTypeIsFoundWithItsYearAndQualifier(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(AuthorDataService::class)->create(
            new AuthorFieldsDto(title: 'Goldsearch Writer', realName: 'Ivan Petrov', countryId: self::COUNTRY_POLAND),
        )->id;
        $authorAliasId = $this->container->get(AuthorAliasDataService::class)->create(
            new AuthorAliasFieldsDto(title: 'Goldsearch Wanderer', authorId: $authorId),
        )->id;
        $groupId = $this->container->get(GroupDataService::class)->create(
            new GroupFieldsDto(title: 'Goldsearch Guild', countryId: self::COUNTRY_UNITED_KINGDOM),
        )->id;
        $groupAliasId = $this->container->get(GroupAliasDataService::class)->create(
            new GroupAliasFieldsDto(title: 'Goldsearch Crew', groupId: $groupId),
        )->id;
        $prodId = $this->container->get(ProdDataService::class)->create(
            new ProdFieldsDto(title: 'Goldsearch Demo', year: 1999, groupIds: [$groupId]),
        )->id;
        $releaseId = $this->container->get(ReleaseDataService::class)->create(
            new ReleaseFieldsDto(title: 'Goldsearch Tape', year: 2001, prodId: $prodId),
        )->id;

        self::assertEquals([
            new FoundEntityDto(EntityType::Prod, $prodId, 'Goldsearch Demo', 1999, 'Goldsearch Guild'),
            new FoundEntityDto(EntityType::Release, $releaseId, 'Goldsearch Tape', 2001, 'Goldsearch Demo'),
            new FoundEntityDto(EntityType::Author, $authorId, 'Goldsearch Writer', null, 'Ivan Petrov, ' . $this->countryTitle(self::COUNTRY_POLAND)),
            new FoundEntityDto(EntityType::AuthorAlias, $authorAliasId, 'Goldsearch Wanderer', null, 'Goldsearch Writer'),
            new FoundEntityDto(EntityType::Group, $groupId, 'Goldsearch Guild', null, $this->countryTitle(self::COUNTRY_UNITED_KINGDOM)),
            new FoundEntityDto(EntityType::GroupAlias, $groupAliasId, 'Goldsearch Crew', null, 'Goldsearch Guild'),
        ], $this->search()->search('Goldsearch', [], 20));
    }

    public function testTheClosestTitlesComeFirstWhateverTheirType(): void
    {
        $this->signInAsEditor();
        $containingId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'The Goldsearch Demo'))->id;
        $startingId = $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldsearch Megademo'))->id;
        $exactId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldsearch'))->id;

        self::assertSame(
            [[EntityType::Author, $exactId], [EntityType::Prod, $startingId], [EntityType::Prod, $containingId]],
            array_map(static fn(FoundEntityDto $found): array => [$found->type, $found->id], $this->search()->search('goldsearch', [], 20)),
        );
    }

    public function testOnlyTheRequestedTypesAreSearchedAndAliasesStandApart(): void
    {
        $this->signInAsEditor();
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldsearch Writer'))->id;
        $aliasId = $this->container->get(AuthorAliasDataService::class)->create(
            new AuthorAliasFieldsDto(title: 'Goldsearch Wanderer', authorId: $authorId),
        )->id;
        $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: 'Goldsearch Demo'));

        self::assertEquals(
            [new FoundEntityDto(EntityType::AuthorAlias, $aliasId, 'Goldsearch Wanderer', null, 'Goldsearch Writer')],
            $this->search()->search('Goldsearch', [EntityType::AuthorAlias], 20),
        );
    }

    public function testTheLimitCapsTheResults(): void
    {
        $this->signInAsEditor();
        foreach (['Goldsearch One', 'Goldsearch Two', 'Goldsearch Three'] as $title) {
            $this->container->get(ProdDataService::class)->create(new ProdFieldsDto(title: $title));
        }

        self::assertCount(2, $this->search()->search('Goldsearch', [EntityType::Prod], 2));
    }

    public function testAnEmptyPhraseFindsNothing(): void
    {
        self::assertSame([], $this->search()->search('  ', [], 20));
    }

    public function testATypeOutsideTheSixIsRefused(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->search()->search('Goldsearch', [EntityType::Picture], 20);
    }

    private function countryTitle(int $countryId): string
    {
        $country = $this->structureManager->getElementById($countryId);
        self::assertInstanceOf(countryElement::class, $country);

        return html_entity_decode((string)$country->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    private function search(): EntitySearchService
    {
        return $this->container->get(EntitySearchService::class);
    }

    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [
            ['zxProd', 'publicReceive'],
            ['zxRelease', 'publicReceive'],
            ['group', 'publicReceive'],
        ]);
    }
}
