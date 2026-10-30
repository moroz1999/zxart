<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use authorAliasElement;
use authorElement;
use groupAliasElement;
use groupElement;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasCardDto;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorCardDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Services\AuthorAliasCardReader;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorAliasFieldsReader;
use ZxArt\Authors\Services\AuthorCardReader;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Authors\Services\AuthorDetailsService;
use ZxArt\Authors\Services\AuthorFieldsReader;
use ZxArt\Groups\Dto\GroupAliasCardDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupCardDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupAliasCardReader;
use ZxArt\Groups\Services\GroupAliasDataService;
use ZxArt\Groups\Services\GroupAliasFieldsReader;
use ZxArt\Groups\Services\GroupCardReader;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Groups\Services\GroupDetailsService;
use ZxArt\Groups\Services\GroupFieldsReader;
use ZxArt\Prods\Dto\ProdCardDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdCardReader;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Prods\Services\ProdFieldsReader;
use ZxArt\Releases\Dto\ReleaseCardDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\FoundReleasesReader;
use ZxArt\Releases\Services\ReleaseCardReader;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Shared\Dto\AliasDto;
use ZxArt\Shared\Dto\CreditDto;
use ZxArt\Shared\Dto\MembershipDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityRefDto;
use zxProdElement;
use zxReleaseElement;

/**
 * The cards the assistant's `get_*` tools answer: the editable fields as the
 * writes take them, the titles of what they name, and what else an edit needs.
 */
#[RunTestsInSeparateProcesses]
final class EntityCardsTest extends EntityWriteTestCase
{
    private const int CATEGORY_DEMO = 92159;
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_WARSAW = 47452;
    private const int COUNTRY_UNITED_KINGDOM = 47542;
    private const int CITY_LONDON = 47543;

    public function testEachCardHoldsTheFieldsTheTitlesTheyNameAndTheLinks(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive'], ['zxRelease', 'publicReceive'], ['group', 'publicReceive']]);
        $authors = $this->container->get(AuthorDataService::class);
        $coderId = $authors->create(new AuthorFieldsDto(title: 'Goldcard Coder', countryId: self::COUNTRY_POLAND, cityId: self::CITY_WARSAW))->id;
        $ghostId = $this->container->get(AuthorAliasDataService::class)
            ->create(new AuthorAliasFieldsDto(title: 'Goldcard Ghost', authorId: $coderId, startDate: $this->date('01.01.1990')))->id;
        $labelId = $authors->create(new AuthorFieldsDto(title: 'Goldcard Label'))->id;
        $groups = $this->container->get(GroupDataService::class);
        $divisionId = $groups->create(new GroupFieldsDto(title: 'Goldcard Division'))->id;
        $guildId = $groups->create(new GroupFieldsDto(
            title: 'Goldcard Guild',
            countryId: self::COUNTRY_UNITED_KINGDOM,
            cityId: self::CITY_LONDON,
            subGroupIds: [$divisionId],
        ))->id;
        $crewId = $this->container->get(GroupAliasDataService::class)->create(new GroupAliasFieldsDto(title: 'Goldcard Crew', groupId: $guildId))->id;
        $groups->setMember($guildId, $coderId, ['coder'], $this->date('01.01.1991'), 0);
        $prods = $this->container->get(ProdDataService::class);
        $demoId = $prods->create(new ProdFieldsDto(
            title: 'Goldcard Demo',
            year: 1999,
            categoryIds: [self::CATEGORY_DEMO],
            groupIds: [$guildId],
            publisherIds: [$labelId],
        ))->id;
        $sagaId = $prods->create(new ProdFieldsDto(title: 'Goldcard Saga', categoryIds: [self::CATEGORY_DEMO], seriesProdIds: [$demoId]))->id;
        $packId = $prods->create(new ProdFieldsDto(title: 'Goldcard Pack', categoryIds: [self::CATEGORY_DEMO], compilationItemIds: [$demoId]))->id;
        $prods->setMember($demoId, $ghostId, ['code']);
        $releases = $this->container->get(ReleaseDataService::class);
        $tapeId = $releases->create(new ReleaseFieldsDto(
            title: 'Goldcard Tape',
            year: 2001,
            releaseType: ReleaseTypes::original,
            releaseFormats: ['tap'],
            publisherIds: [$guildId],
            prodId: $demoId,
        ))->id;
        $releases->setMember($tapeId, $coderId, ['release']);
        $coder = new EntityRefDto(EntityType::Author, $coderId, 'Goldcard Coder');
        $ghost = new EntityRefDto(EntityType::AuthorAlias, $ghostId, 'Goldcard Ghost');
        $guild = new EntityRefDto(EntityType::Group, $guildId, 'Goldcard Guild');
        $demo = new EntityRefDto(EntityType::Prod, $demoId, 'Goldcard Demo');

        self::assertEquals(new ProdCardDto(
            id: $demoId,
            fields: $this->container->get(ProdFieldsReader::class)->read($this->element($demoId, zxProdElement::class)),
            refs: [$this->ref(EntityType::Category, self::CATEGORY_DEMO), $guild, new EntityRefDto(EntityType::Author, $labelId, 'Goldcard Label')],
            authors: [new CreditDto($ghost, ['code'])],
            releases: $this->container->get(FoundReleasesReader::class)->read([$tapeId]),
            files: [],
            inSeries: [new EntityRefDto(EntityType::Prod, $sagaId, 'Goldcard Saga')],
            inCompilations: [new EntityRefDto(EntityType::Prod, $packId, 'Goldcard Pack')],
        ), $this->container->get(ProdCardReader::class)->read($demoId));

        self::assertEquals(new ReleaseCardDto(
            id: $tapeId,
            fields: $this->container->get(ReleaseFieldsReader::class)->read($this->element($tapeId, zxReleaseElement::class)),
            refs: [$guild, $demo],
            authors: [new CreditDto($coder, ['release'])],
            files: [],
            fileName: '',
            fileSize: null,
            prodHardware: [],
            prodLanguages: [],
        ), $this->container->get(ReleaseCardReader::class)->read($tapeId));

        $coderElement = $this->element($coderId, authorElement::class);
        self::assertEquals(new AuthorCardDto(
            id: $coderId,
            fields: $this->container->get(AuthorFieldsReader::class)->read($coderElement),
            refs: [$this->ref(EntityType::Country, self::COUNTRY_POLAND), $this->ref(EntityType::City, self::CITY_WARSAW)],
            aliases: [new AliasDto($ghostId, 'Goldcard Ghost', $this->date('01.01.1990'), 0)],
            memberships: [new MembershipDto($guild, ['coder'], $this->date('01.01.1991'), 0)],
            counters: $this->container->get(AuthorDetailsService::class)->getCounters($coderElement),
        ), $this->container->get(AuthorCardReader::class)->read($coderId));

        $ghostElement = $this->element($ghostId, authorAliasElement::class);
        self::assertEquals(new AuthorAliasCardDto(
            id: $ghostId,
            fields: $this->container->get(AuthorAliasFieldsReader::class)->read($ghostElement),
            refs: [$coder],
            memberships: [],
            counters: $this->container->get(AuthorDetailsService::class)->getCounters($ghostElement),
        ), $this->container->get(AuthorAliasCardReader::class)->read($ghostId));

        $guildElement = $this->element($guildId, groupElement::class);
        self::assertEquals(new GroupCardDto(
            id: $guildId,
            fields: $this->container->get(GroupFieldsReader::class)->read($guildElement),
            refs: [
                $this->ref(EntityType::Country, self::COUNTRY_UNITED_KINGDOM),
                $this->ref(EntityType::City, self::CITY_LONDON),
                new EntityRefDto(EntityType::Group, $divisionId, 'Goldcard Division'),
            ],
            members: [new MembershipDto($coder, ['coder'], $this->date('01.01.1991'), 0)],
            parentGroups: [],
            aliases: [new AliasDto($crewId, 'Goldcard Crew', 0, 0)],
            counters: $this->container->get(GroupDetailsService::class)->getCounters($guildElement),
        ), $this->container->get(GroupCardReader::class)->read($guildId));
        self::assertEquals([$guild], $this->container->get(GroupCardReader::class)->read($divisionId)->parentGroups);

        $crewElement = $this->element($crewId, groupAliasElement::class);
        self::assertEquals(new GroupAliasCardDto(
            id: $crewId,
            fields: $this->container->get(GroupAliasFieldsReader::class)->read($crewElement),
            refs: [$guild],
            members: [],
            counters: $this->container->get(GroupDetailsService::class)->getCounters($crewElement),
        ), $this->container->get(GroupAliasCardReader::class)->read($crewId));
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @return T
     */
    private function element(int $id, string $class): object
    {
        $element = $this->structureManager->getElementById($id);
        self::assertInstanceOf($class, $element);

        return $element;
    }

    private function ref(EntityType $type, int $id): EntityRefDto
    {
        $element = $this->structureManager->getElementById($id);
        self::assertNotNull($element);

        return new EntityRefDto($type, $id, html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }
}
