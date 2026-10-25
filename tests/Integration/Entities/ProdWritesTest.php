<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\Services\AuthorAliasDataService;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Prods\Exception\ProdDataException;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Prods\Services\ProdFieldsReader;
use ZxArt\Prods\Services\ProdSearchService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\ReleaseTypes;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\ZxProdCategories\CategoryIds;
use zxProdElement;

/**
 * Productions written through {@see ProdDataService}. The batch upload, which
 * needs uploaded files, is covered with the other uploads.
 */
#[RunTestsInSeparateProcesses]
final class ProdWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_zxprod';
    private const int CATEGORY_DEMO = 92159;
    private const int CATEGORY_INTRO = 92163;
    private const int COUNTRY_POLAND = 47450;
    private const int CITY_VORONEZH = 47233;
    /** Chaos Constructions 1999. */
    private const int PARTY = 2564;
    private const int PARTY_YEAR = 1999;
    private const array COLUMNS = [
        'title', 'altTitle', 'year', 'description', 'htmlDescription', 'instructions', 'htmlInstructions',
        'legalStatus', 'externalLink', 'youtubeId', 'party', 'partyplace', 'compo', 'denyVoting', 'denyComments',
    ];

    public function testCreateStoresEveryFieldAndLinkUnderItsFirstCategory(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();

        $prodId = $this->prodDataService()->create($this->everyField($references))->id;

        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $prodId, self::COLUMNS));
        self::assertSame($this->everyLinkStored($references), $this->storedLinks($prodId));
        self::assertSame(self::CATEGORY_DEMO, $this->structureParent($prodId)['id']);
        self::assertSame('goldmaster-demo', $this->structureName($prodId));
        self::assertSame($this->userId(), (int)$this->storedRow(self::TABLE, $prodId, ['userId'])['userId']);
    }

    public function testCreateQueuesTheAiTextsAndThePostAndSkipsTheAiCategories(): void
    {
        $this->signInAsRegisteredUser();

        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO]))->id;

        self::assertSame([
            QueueType::AI_CATEGORIES_TAGS->value => QueueStatus::STATUS_SKIP->value,
            QueueType::AI_INTRO->value => QueueStatus::STATUS_TODO->value,
            QueueType::AI_SEO->value => QueueStatus::STATUS_TODO->value,
            QueueType::SOCIAL_POST->value => QueueStatus::STATUS_TODO->value,
        ], $this->queued($prodId));
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();

        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        self::assertSame([
            'file.delete', 'file.receive',
            'zxProd.deleteAuthor', 'zxProd.deleteFile', 'zxProd.publicDelete', 'zxProd.publicReceive',
            'zxProd.receiveFiles', 'zxProd.showPublicForm', 'zxProd.submitTags',
            'zxRelease.clone', 'zxRelease.deleteAuthor', 'zxRelease.deleteFile', 'zxRelease.publicDelete',
            'zxRelease.publicReceive', 'zxRelease.showPublicForm', 'zxRelease.submitTags',
        ], $this->allowedOnElement($prodId));
        self::assertSame(['batchUpload'], $this->loggedActions($prodId));
    }

    public function testTheCreatorMayUpdateTheProdRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $this->prodDataService()->update($prodId, new ProdFieldsDto(altTitle: 'Demo Of Goldmaster'));

        self::assertSame('Demo Of Goldmaster', $this->storedRow(self::TABLE, $prodId, ['altTitle'])['altTitle']);
    }

    public function testCreateWithoutCategoriesGoesToMisc(): void
    {
        $this->signInAsRegisteredUser();

        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        self::assertSame(CategoryIds::MISC->value, $this->structureParent($prodId)['id']);
    }

    public function testAMissingYearIsThePartysYear(): void
    {
        $this->signInAsRegisteredUser();

        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo', partyId: self::PARTY))->id;

        self::assertSame(self::PARTY_YEAR, $this->storedRow(self::TABLE, $prodId, ['year'])['year']);
    }

    public function testCreateWithoutATitleIsRejected(): void
    {
        $this->signInAsRegisteredUser();

        try {
            $this->prodDataService()->create(new ProdFieldsDto(title: ''));
            self::fail('A production needs a title');
        } catch (ProdDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateWithAWrongReferenceWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        foreach ([
            new ProdFieldsDto(title: 'Goldmaster Demo', partyId: self::COUNTRY_POLAND),
            new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::COUNTRY_POLAND]),
            new ProdFieldsDto(title: 'Goldmaster Demo', groupIds: [self::COUNTRY_POLAND]),
            new ProdFieldsDto(title: 'Goldmaster Demo', hardware: ['no-such-hardware']),
            new ProdFieldsDto(title: 'Goldmaster Demo', languages: ['xx']),
        ] as $fields) {
            try {
                $this->prodDataService()->create($fields);
                self::fail('A wrong reference must be rejected');
            } catch (ProdDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame([], $this->prodRows());
        // the create runs in one transaction, so not even the structure record is left
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);

        try {
            $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'));
            self::fail('Creating must need zxProdsUploadForm.batchUpload');
        } catch (ProdDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $prodId = $this->prodDataService()->create($this->everyField($references))->id;

        $this->prodDataService()->update($prodId, new ProdFieldsDto(
            year: 2001,
            legalStatus: LegalStatus::forbidden,
            denyComments: false,
            hardware: ['zx128'],
            groupIds: [$references['otherGroupId']],
        ));

        self::assertSame(
            array_replace($this->everyFieldStored(), ['year' => 2001, 'legalStatus' => 'forbidden', 'denyComments' => 0]),
            $this->storedRow(self::TABLE, $prodId, self::COLUMNS),
        );
        self::assertSame(
            array_replace($this->everyLinkStored($references), ['hardware' => ['zx128'], 'groups' => [$references['otherGroupId']]]),
            $this->storedLinks($prodId),
        );
        self::assertSame(['batchUpload', 'publicReceive'], $this->loggedActions($prodId));
    }

    public function testUpdateClearsWithEmptyValuesListsAndZero(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $prodId = $this->prodDataService()->create($this->everyField($references))->id;

        $this->prodDataService()->update($prodId, new ProdFieldsDto(
            altTitle: '',
            description: '',
            instructions: '',
            externalLink: '',
            youtubeId: '',
            partyId: 0,
            partyPlace: 0,
            compo: '',
            languages: [],
            hardware: [],
            groupIds: [],
            publisherIds: [],
            tags: [],
            compilationItemIds: [],
        ));

        self::assertSame(
            array_replace($this->everyFieldStored(), [
                'altTitle' => '',
                'description' => '',
                'instructions' => '',
                'externalLink' => '',
                'youtubeId' => '',
                'party' => 0,
                'partyplace' => 0,
                'compo' => '',
            ]),
            $this->storedRow(self::TABLE, $prodId, self::COLUMNS),
        );
        self::assertSame(
            array_replace($this->everyLinkStored($references), [
                'languages' => [],
                'hardware' => [],
                'party' => [],
                'groups' => [],
                'publishers' => [],
                'tags' => [],
                'compilationItems' => [],
            ]),
            $this->storedLinks($prodId),
        );
    }

    public function testChangedCategoriesQueueTheAiTextsAgain(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO]))->id;
        $this->db->table('queue')->where('elementId', '=', $prodId)->update(['status' => QueueStatus::STATUS_SUCCESS->value]);

        $this->prodDataService()->update($prodId, new ProdFieldsDto(categoryIds: [self::CATEGORY_INTRO]));

        self::assertSame([self::CATEGORY_INTRO], $this->linkedParents($prodId, 'zxProdCategory'));
        self::assertSame([
            QueueType::AI_CATEGORIES_TAGS->value => QueueStatus::STATUS_SKIP->value,
            QueueType::AI_INTRO->value => QueueStatus::STATUS_TODO->value,
            QueueType::AI_SEO->value => QueueStatus::STATUS_TODO->value,
            QueueType::SOCIAL_POST->value => QueueStatus::STATUS_SUCCESS->value,
        ], $this->queued($prodId));
    }

    public function testAProdWithSeriesPartsAndNoReleasesIsASeries(): void
    {
        $this->signInAsEditor();
        $partId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo 2'))->id;
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Saga', categoryIds: [self::CATEGORY_DEMO]))->id;

        $this->prodDataService()->update($prodId, new ProdFieldsDto(seriesProdIds: [$partId]));

        self::assertSame([$partId], $this->linkedChildren($prodId, 'series'));
        self::assertSame([CategoryIds::SERIES->value], $this->linkedParents($prodId, 'zxProdCategory'));
    }

    public function testMiscIsDroppedWhenAnotherCategoryIsGiven(): void
    {
        $this->signInAsEditor();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $this->prodDataService()->update($prodId, new ProdFieldsDto(categoryIds: [CategoryIds::MISC->value, self::CATEGORY_DEMO]));

        self::assertSame([self::CATEGORY_DEMO], $this->linkedParents($prodId, 'zxProdCategory'));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheProd(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $prodId)->get();

        try {
            $this->prodDataService()->update($prodId, new ProdFieldsDto(altTitle: 'Demo Of Goldmaster'));
            self::fail('Updating must need zxProd.publicReceive');
        } catch (ProdDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $prodId)->get());
    }

    public function testUpdateStoresTheAuthorsAndPortalIds(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $aliasId = $this->container->get(AuthorAliasDataService::class)
            ->create(new AuthorAliasFieldsDto(title: 'Goldmaster Ghost', authorId: $this->createAuthor('Goldmaster Painter')))->id;
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $this->ageModificationTime($prodId);

        $this->prodDataService()->update(
            $prodId,
            new ProdFieldsDto(),
            authors: [new AuthorshipInputDto($authorId, ['code', 'music']), new AuthorshipInputDto($aliasId, ['graphics'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '5001'), new ImportOriginDto(ImportOrigin::Vtrdos, 'gm-demo')],
        );

        self::assertSame([['roles' => ['code', 'music'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertSame([['roles' => ['graphics'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $aliasId, 'prod'));
        self::assertSame(
            [['origin' => 'vt', 'importId' => 'gm-demo'], ['origin' => 'zxdb', 'importId' => '5001']],
            $this->storedImportOrigins($prodId),
        );
        self::assertGreaterThan(1, $this->modificationTime($prodId));
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $otherAuthorId = $this->createAuthor('Goldmaster Painter');
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $this->prodDataService()->update(
            $prodId,
            new ProdFieldsDto(),
            authors: [new AuthorshipInputDto($authorId, ['code'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '5001')],
        );

        $this->prodDataService()->update($prodId, new ProdFieldsDto(altTitle: 'Gm Demo'), authors: [new AuthorshipInputDto($otherAuthorId)]);

        self::assertSame([], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertSame([['roles' => ['unknown'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $otherAuthorId, 'prod'));
        self::assertSame([['origin' => 'zxdb', 'importId' => '5001']], $this->storedImportOrigins($prodId));
        self::assertSame(['altTitle' => 'Gm Demo'], $this->storedRow(self::TABLE, $prodId, ['altTitle']));
    }

    public function testAWrongAuthorRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $authorId = $this->createAuthor('Goldmaster Writer');
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;

        $this->assertRefused(400, ProdDataException::class, fn() => $this->prodDataService()->update(
            $prodId,
            new ProdFieldsDto(altTitle: 'Gm Demo'),
            authors: [new AuthorshipInputDto($authorId, ['code']), new AuthorshipInputDto($references['groupId'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '5001')],
        ));

        self::assertSame(['altTitle' => ''], $this->storedRow(self::TABLE, $prodId, ['altTitle']));
        self::assertSame([], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertSame([], $this->storedImportOrigins($prodId));
    }

    public function testJoinMovesTheReleasesAndAuthorsAndDeletesTheJoinedProduction(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $joinedId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Intro', year: 1997))->id;
        $releaseId = $this->container->get(ReleaseDataService::class)
            ->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $joinedId))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->addAuthorship($authorId, $joinedId, 'prod');

        $this->prodDataService()->join($mainId, $joinedId);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame($mainId, $this->structureParent($releaseId)['id']);
        self::assertContains(['authorId' => $authorId, 'elementId' => $mainId, 'type' => 'prod'], $this->authorshipRows($authorId));
        self::assertSame(1997, $this->storedRow(self::TABLE, $mainId, ['year'])['year']);
        self::assertSame(['batchUpload', 'join'], $this->loggedActions($mainId));
    }

    public function testJoinOfTheReleasesOnlyMovesNothingElseOfTheJoinedProduction(): void
    {
        $this->signInAsJoiner();
        $mainId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $joinedId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Intro', year: 1997))->id;
        $releaseId = $this->container->get(ReleaseDataService::class)
            ->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $joinedId))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->addAuthorship($authorId, $joinedId, 'prod');

        $this->prodDataService()->join($mainId, $joinedId, true);

        self::assertFalse($this->elementExists($joinedId));
        self::assertSame($mainId, $this->structureParent($releaseId)['id']);
        self::assertNotContains(['authorId' => $authorId, 'elementId' => $mainId, 'type' => 'prod'], $this->authorshipRows($authorId));
        self::assertSame(0, $this->storedRow(self::TABLE, $mainId, ['year'])['year']);
    }

    public function testJoinIsForbiddenWithoutThePrivilegeAndJoinsNothing(): void
    {
        $this->signInAsRegisteredUser();
        $mainId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $joinedId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Intro'))->id;

        try {
            $this->prodDataService()->join($mainId, $joinedId);
            self::fail('Joining must need zxProd.join');
        } catch (ProdDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertTrue($this->elementExists($joinedId));
    }

    public function testSetMemberAddsTheAuthorThenReplacesItsRolesAndMovesTheModificationTime(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->ageModificationTime($prodId);

        $this->prodDataService()->setMember($prodId, $authorId, ['code', 'music']);
        $this->prodDataService()->setMember($prodId, $authorId, ['graphics']);

        self::assertSame([['roles' => ['graphics'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($prodId, $authorId, 'prod'));
        self::assertGreaterThan(1, $this->modificationTime($prodId));
        self::assertSame(['batchUpload', 'publicReceive', 'publicReceive'], $this->loggedActions($prodId));
    }

    public function testSetMemberOfWhatIsNotAnAuthorWritesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id;
        $groupId = $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;

        try {
            $this->prodDataService()->setMember($prodId, $groupId, ['code']);
            self::fail('A group is no author of a production');
        } catch (ProdDataException $exception) {
            self::assertSame(400, $exception->getStatusCode());
        }
        self::assertSame([], $this->authorshipRecords($prodId, $groupId, 'prod'));
    }

    public function testSearchCombinesTheCatalogueFiltersWithTitleYearsAndGroup(): void
    {
        $this->signInAsRegisteredUser();
        $groupId = $this->container->get(GroupDataService::class)->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id;
        $demoId = $this->prodDataService()->create(new ProdFieldsDto(
            title: 'Goldmaster Demo',
            year: 1990,
            legalStatus: LegalStatus::allowed,
            categoryIds: [self::CATEGORY_DEMO],
            groupIds: [$groupId],
        ))->id;
        $introId = $this->prodDataService()->create(new ProdFieldsDto(
            title: 'Goldmaster Intro',
            year: 1995,
            categoryIds: [self::CATEGORY_INTRO],
        ))->id;
        $this->container->get(ReleaseDataService::class)->create(new ReleaseFieldsDto(
            title: 'Goldmaster Tape',
            prodId: $introId,
            releaseType: ReleaseTypes::original,
            releaseFormats: ['tap'],
        ));
        $search = $this->container->get(ProdSearchService::class);

        self::assertSame([$demoId, $introId], $search->search(new ProdSearchFilterDto(title: 'goldmaster'), 0, 10)->ids);
        self::assertSame([$demoId], $search->search(new ProdSearchFilterDto(title: 'goldmaster', categoryIds: [self::CATEGORY_DEMO]), 0, 10)->ids);
        self::assertSame([$introId], $search->search(new ProdSearchFilterDto(title: 'goldmaster', yearFrom: 1991), 0, 10)->ids);
        self::assertSame([$introId], $search->search(new ProdSearchFilterDto(title: 'goldmaster', formats: ['tap'], releaseTypes: [ReleaseTypes::original]), 0, 10)->ids);
        self::assertSame([$demoId], $search->search(new ProdSearchFilterDto(legalStatuses: [LegalStatus::allowed], groupIds: [$groupId]), 0, 10)->ids);
        $page = $search->search(new ProdSearchFilterDto(title: 'goldmaster'), 1, 1);
        self::assertSame(2, $page->total);
        self::assertSame([$introId], $page->ids);
    }

    public function testTheSearchFindsByCoAuthorsTheirRolesAndTheirCity(): void
    {
        $this->signInAsEditor();
        $coderId = $this->container->get(AuthorDataService::class)
            ->create(new AuthorFieldsDto(title: 'Goldmaster Coder', cityId: self::CITY_VORONEZH))->id;
        $musicianId = $this->createAuthor('Goldmaster Musician');
        $demoId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO]))->id;
        $introId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Intro', categoryIds: [self::CATEGORY_INTRO]))->id;
        $this->prodDataService()->setMember($demoId, $coderId, ['code']);
        $this->prodDataService()->setMember($demoId, $musicianId, ['music']);
        $this->prodDataService()->setMember($introId, $musicianId, ['music', 'graphics']);
        $search = $this->container->get(ProdSearchService::class);
        $find = static fn(ProdSearchFilterDto $filter): array => $search->search($filter, 0, 10)->ids;

        self::assertSame([$demoId], $find(new ProdSearchFilterDto(title: 'goldmaster', authorIds: [$coderId, $musicianId])));
        self::assertSame([$demoId, $introId], $find(new ProdSearchFilterDto(title: 'goldmaster', authorIds: [$coderId, $musicianId], anyAuthor: true)));
        self::assertSame([$introId], $find(new ProdSearchFilterDto(title: 'goldmaster', authorRoles: ['graphics'])));
        self::assertSame([$demoId], $find(new ProdSearchFilterDto(title: 'goldmaster', cityIds: [self::CITY_VORONEZH])));
    }

    public function testASearchRepeatedAfterACreateSeesTheNewProduction(): void
    {
        $this->signInAsRegisteredUser();
        $search = $this->container->get(ProdSearchService::class);
        $demoId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Demo', categoryIds: [self::CATEGORY_DEMO]))->id;
        $filter = new ProdSearchFilterDto(title: 'goldmaster', categoryIds: [self::CATEGORY_DEMO]);
        self::assertSame([$demoId], $search->search($filter, 0, 10)->ids);

        $otherId = $this->prodDataService()->create(new ProdFieldsDto(title: 'Goldmaster Megademo', categoryIds: [self::CATEGORY_DEMO]))->id;

        self::assertSame([$demoId, $otherId], $search->search($filter, 0, 10)->ids);
    }

    public function testReadAnswersWhatCreateAndUpdateWrote(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $prodId = $this->prodDataService()->create($this->everyField($references))->id;
        $reader = $this->container->get(ProdFieldsReader::class);

        self::assertEquals($this->everyFieldRead($references), $reader->read($this->prod($prodId)));

        $this->prodDataService()->update($prodId, new ProdFieldsDto(
            groupIds: [$references['otherGroupId']],
            categoryIds: [self::CATEGORY_INTRO],
            tags: [],
            languages: [],
        ));

        self::assertEquals(
            new ProdFieldsDto(...array_replace(get_object_vars($this->everyFieldRead($references)), [
                'groupIds' => [$references['otherGroupId']],
                'categoryIds' => [self::CATEGORY_INTRO],
                'tags' => [],
                'languages' => [],
            ])),
            $reader->read($this->prod($prodId)),
        );
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every production.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
    }

    /**
     * A registered user who may also join any productions.
     */
    private function signInAsJoiner(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'join']]);
    }

    /**
     * Groups and productions for the production to refer to.
     *
     * @return array{groupId: int, otherGroupId: int, publisherId: int, itemId: int}
     */
    private function createReferences(): array
    {
        $groups = $this->container->get(GroupDataService::class);
        $prods = $this->prodDataService();

        return [
            'groupId' => $groups->create(new GroupFieldsDto(title: 'Goldmaster Guild'))->id,
            'otherGroupId' => $groups->create(new GroupFieldsDto(title: 'Goldmaster Union'))->id,
            'publisherId' => $groups->create(new GroupFieldsDto(title: 'Goldmaster Publishing'))->id,
            'itemId' => $prods->create(new ProdFieldsDto(title: 'Goldmaster Intro'))->id,
        ];
    }

    /**
     * @param array{groupId: int, otherGroupId: int, publisherId: int, itemId: int} $references
     */
    private function everyField(array $references): ProdFieldsDto
    {
        return new ProdFieldsDto(
            title: 'Goldmaster Demo',
            altTitle: 'Gm Demo',
            year: 1999,
            description: 'A demo of the golden master',
            htmlDescription: true,
            instructions: 'Press any key',
            htmlInstructions: false,
            legalStatus: LegalStatus::insales,
            externalLink: 'https://demo.invalid/',
            youtubeId: 'gmYoutube01',
            partyId: self::PARTY,
            partyPlace: 3,
            compo: 'demo',
            denyVoting: true,
            denyComments: true,
            languages: ['en', 'ru'],
            hardware: ['zx48', 'ay'],
            categoryIds: [self::CATEGORY_DEMO, self::CATEGORY_INTRO],
            groupIds: [$references['groupId']],
            publisherIds: [$references['publisherId']],
            tags: ['goldmaster-tag-one', 'goldmaster-tag-two'],
            compilationItemIds: [$references['itemId']],
        );
    }

    /**
     * {@see self::everyField()} as it reads back: the tags capitalised, every
     * list set.
     *
     * @param array{groupId: int, otherGroupId: int, publisherId: int, itemId: int} $references
     */
    private function everyFieldRead(array $references): ProdFieldsDto
    {
        return new ProdFieldsDto(...array_replace(get_object_vars($this->everyField($references)), [
            'tags' => ['Goldmaster-Tag-One', 'Goldmaster-Tag-Two'],
            'seriesProdIds' => [],
        ]));
    }

    private function prod(int $prodId): zxProdElement
    {
        $prod = $this->structureManager->getElementById($prodId);
        self::assertInstanceOf(zxProdElement::class, $prod);

        return $prod;
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStored(): array
    {
        return [
            'title' => 'Goldmaster Demo',
            'altTitle' => 'Gm Demo',
            'year' => 1999,
            'description' => 'A demo of the golden master',
            'htmlDescription' => 1,
            'instructions' => 'Press any key',
            'htmlInstructions' => 0,
            'legalStatus' => 'insales',
            'externalLink' => 'https://demo.invalid/',
            'youtubeId' => 'gmYoutube01',
            'party' => self::PARTY,
            'partyplace' => 3,
            'compo' => 'demo',
            'denyVoting' => 1,
            'denyComments' => 1,
        ];
    }

    /**
     * @param array{groupId: int, otherGroupId: int, publisherId: int, itemId: int} $references
     * @return array<string, list<int|string>>
     */
    private function everyLinkStored(array $references): array
    {
        return [
            'languages' => ['en', 'ru'],
            'hardware' => ['ay', 'zx48'],
            'categories' => [self::CATEGORY_DEMO, self::CATEGORY_INTRO],
            'party' => [self::PARTY],
            'groups' => [$references['groupId']],
            'publishers' => [$references['publisherId']],
            'tags' => ['Goldmaster-Tag-One', 'Goldmaster-Tag-Two'],
            'compilationItems' => [$references['itemId']],
            'seriesProds' => [],
        ];
    }

    /**
     * What the production is linked with, each list sorted.
     *
     * @return array<string, list<int|string>>
     */
    private function storedLinks(int $prodId): array
    {
        $languages = array_map('strval', $this->db->table('zxitem_language')->where('elementId', '=', $prodId)->pluck('value'));
        sort($languages);
        $hardware = array_map('strval', $this->db->table(self::TABLE . '_hw_required')
            ->join('hardware', 'hardware.id', '=', self::TABLE . '_hw_required.hardwareId')
            ->where(self::TABLE . '_hw_required.elementId', '=', $prodId)
            ->pluck('hardware.code'));
        sort($hardware);
        $tags = array_map('strval', $this->db->table('structure_links')
            ->join('module_tag', 'module_tag.id', '=', 'structure_links.parentStructureId')
            ->where('structure_links.childStructureId', '=', $prodId)
            ->where('structure_links.type', '=', 'tagLink')
            ->distinct()
            ->pluck('module_tag.title'));
        sort($tags);

        return [
            'languages' => $languages,
            'hardware' => $hardware,
            'categories' => $this->linkedParents($prodId, 'zxProdCategory'),
            'party' => $this->linkedParents($prodId, 'partyProd'),
            'groups' => $this->linkedParents($prodId, 'zxProdGroups'),
            'publishers' => $this->linkedParents($prodId, 'zxProdPublishers'),
            'tags' => $tags,
            'compilationItems' => $this->linkedChildren($prodId, 'compilation'),
            'seriesProds' => $this->linkedChildren($prodId, 'series'),
        ];
    }

    /**
     * @return array<string, int> queue type => status, sorted by type
     */
    private function queued(int $prodId): array
    {
        $queued = array_map('intval', $this->db->table('queue')->where('elementId', '=', $prodId)->pluck('status', 'type'));
        ksort($queued);

        return $queued;
    }

    private function userId(): int
    {
        return (int)$this->db->table('module_user')->where('userName', '=', 'crontab')->value('id');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function prodRows(): array
    {
        $newIds = $this->newElementIds();

        return $newIds === [] ? [] : $this->db->table(self::TABLE)->whereIn('id', $newIds)->get();
    }

    private function createAuthor(string $title): int
    {
        return $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: $title))->id;
    }

    private function prodDataService(): ProdDataService
    {
        return $this->container->get(ProdDataService::class);
    }
}
