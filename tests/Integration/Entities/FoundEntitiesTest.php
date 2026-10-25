<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Services\FoundAuthorsReader;
use ZxArt\Groups\Dto\FoundGroupDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\FoundGroupsReader;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Prods\Dto\FoundProdDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\FoundProdsReader;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Releases\Dto\FoundReleaseDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;

/**
 * The summaries the assistant's typed searches answer, read for entities the
 * test wrote.
 */
#[RunTestsInSeparateProcesses]
final class FoundEntitiesTest extends EntityWriteTestCase
{
    private const int CATEGORY_DEMO = 92159;
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int CITY_LONDON = 47543;

    public function testEachSummaryNamesWhatTellsTheEntityApart(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive'], ['zxRelease', 'publicReceive'], ['group', 'publicReceive']]);
        $authors = $this->container->get(AuthorDataService::class);
        $coderId = $authors->create(new AuthorFieldsDto(
            title: 'Goldfound Coder',
            realName: 'Ivan Petrov',
            countryId: self::COUNTRY_POLAND,
            cityId: self::CITY_WARSAW,
        ))->id;
        $ghostId = $this->container->get(AuthorAliasDataService::class)->create(new AuthorAliasFieldsDto(title: 'Goldfound Ghost', authorId: $coderId))->id;
        $labelId = $authors->create(new AuthorFieldsDto(title: 'Goldfound Label'))->id;
        $groups = $this->container->get(GroupDataService::class);
        $guildId = $groups->create(new GroupFieldsDto(
            title: 'Goldfound Guild',
            abbreviation: 'GFG',
            type: 'scene',
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
        ))->id;
        $crewId = $this->container->get(GroupAliasDataService::class)->create(new GroupAliasFieldsDto(title: 'Goldfound Crew', groupId: $guildId))->id;
        $groups->setMember($guildId, $coderId, ['coder']);
        $prods = $this->container->get(ProdDataService::class);
        $demoId = $prods->create(new ProdFieldsDto(
            title: 'Goldfound Demo',
            year: 1999,
            categoryIds: [self::CATEGORY_DEMO],
            groupIds: [$guildId],
            publisherIds: [$labelId],
        ))->id;
        $prods->setMember($demoId, $ghostId, ['code']);
        $releases = $this->container->get(ReleaseDataService::class);
        $tapeId = $releases->create(new ReleaseFieldsDto(
            title: 'Goldfound Tape',
            year: 2001,
            releaseType: ReleaseTypes::original,
            releaseFormats: ['tap'],
            publisherIds: [$guildId],
            prodId: $demoId,
        ))->id;
        $releases->setMember($tapeId, $coderId, ['release']);
        $coder = new EntityRefDto(EntityType::Author, $coderId, 'Goldfound Coder');
        $guild = new EntityRefDto(EntityType::Group, $guildId, 'Goldfound Guild');

        self::assertEquals([new FoundProdDto(
            id: $demoId,
            title: 'Goldfound Demo',
            year: 1999,
            authors: [new CreditDto(new EntityRefDto(EntityType::AuthorAlias, $ghostId, 'Goldfound Ghost'), ['code'])],
            groups: [$guild],
            publishers: [new EntityRefDto(EntityType::Author, $labelId, 'Goldfound Label')],
            categories: [$this->ref(EntityType::Category, self::CATEGORY_DEMO)],
        )], $this->container->get(FoundProdsReader::class)->read([$demoId]));
        self::assertEquals([new FoundReleaseDto(
            id: $tapeId,
            title: 'Goldfound Tape',
            year: 2001,
            prod: new EntityRefDto(EntityType::Prod, $demoId, 'Goldfound Demo'),
            releaseType: 'original',
            formats: ['tap'],
            fileName: '',
            authors: [new CreditDto($coder, ['release'])],
            publishers: [$guild],
        )], $this->container->get(FoundReleasesReader::class)->read([$tapeId]));
        self::assertEquals([new FoundAuthorDto(
            id: $coderId,
            title: 'Goldfound Coder',
            realName: 'Ivan Petrov',
            country: $this->ref(EntityType::Country, self::COUNTRY_POLAND),
            city: $this->ref(EntityType::City, self::CITY_WARSAW),
            aliases: [new EntityRefDto(EntityType::AuthorAlias, $ghostId, 'Goldfound Ghost')],
            groups: [$guild],
        )], $this->container->get(FoundAuthorsReader::class)->read([$coderId]));
        self::assertEquals([new FoundGroupDto(
            id: $guildId,
            title: 'Goldfound Guild',
            abbreviation: 'GFG',
            type: 'scene',
            country: $this->ref(EntityType::Country, self::COUNTRY_UNITED_KINGDOM),
            city: $this->ref(EntityType::City, self::CITY_LONDON),
            aliases: [new EntityRefDto(EntityType::GroupAlias, $crewId, 'Goldfound Crew')],
        )], $this->container->get(FoundGroupsReader::class)->read([$guildId]));
    }

    public function testTheSummariesKeepTheOrderOfTheIds(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $groups = $this->container->get(GroupDataService::class);
        $firstId = $groups->create(new GroupFieldsDto(title: 'Goldfound Alpha'))->id;
        $secondId = $groups->create(new GroupFieldsDto(title: 'Goldfound Beta'))->id;

        $found = $this->container->get(FoundGroupsReader::class)->read([$secondId, $firstId]);

        self::assertSame([$secondId, $firstId], array_map(static fn(FoundGroupDto $group): int => $group->id, $found));
    }

    private function ref(EntityType $type, int $id): EntityRefDto
    {
        $element = $this->structureManager->getElementById($id);
        self::assertNotNull($element);

        return new EntityRefDto($type, $id, html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
