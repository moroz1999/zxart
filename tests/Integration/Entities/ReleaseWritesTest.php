<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\Services\AuthorDataService;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Services\GroupDataService;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Services\ProdDataService;
use ZxArt\Releases\Dbal\ReleasesRepository;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Exception\ReleaseDataException;
use ZxArt\Releases\Services\ReleaseDataService;
use ZxArt\Releases\Services\ReleaseFieldsReader;
use ZxArt\Releases\ReleaseTypes;
use zxReleaseElement;

/**
 * Releases written through {@see ReleaseDataService}, and the legacy clone
 * action the release page still runs. Creating a release per uploaded file is
 * covered with the other uploads.
 */
#[RunTestsInSeparateProcesses]
final class ReleaseWritesTest extends EntityWriteTestCase
{
    private const string TABLE = 'module_zxrelease';
    private const int COUNTRY_POLAND = 47450;
    private const array COLUMNS = ['title', 'year', 'version', 'description', 'releaseType', 'denyVoting', 'denyComments'];

    public function testCreateStoresEveryFieldAndLinkUnderItsProd(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();

        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;

        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $releaseId, self::COLUMNS));
        self::assertSame($this->everyLinkStored($references), $this->storedLinks($releaseId));
        self::assertSame('goldmaster-tape', $this->structureName($releaseId));
        self::assertSame($this->userId(), (int)$this->storedRow(self::TABLE, $releaseId, ['userId'])['userId']);
    }

    public function testCreateLetsTheCreatorKeepEditingAndIsLogged(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();

        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;

        self::assertSame(
            ['zxRelease.clone', 'zxRelease.deleteFile', 'zxRelease.publicDelete', 'zxRelease.publicReceive', 'zxRelease.showPublicForm'],
            $this->allowedOnElement($releaseId),
        );
        self::assertSame(['publicAdd'], $this->loggedActions($releaseId));
    }

    public function testTheCreatorMayUpdateTheReleaseRightAway(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(version: '1.1'));

        self::assertSame('1.1', $this->storedRow(self::TABLE, $releaseId, ['version'])['version']);
    }

    public function testCreateForAProdNothingHasLoadedYet(): void
    {
        $this->signInAsRegisteredUser();
        $prodId = (int)$this->db->table('module_zxprod')->min('id');

        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId))->id;

        self::assertSame($prodId, $this->structureParent($releaseId)['id']);
    }

    public function testCreateWithoutAProdOrForWhatIsNotAProdWritesNothing(): void
    {
        $this->signInAsRegisteredUser();

        foreach ([
            new ReleaseFieldsDto(title: 'Goldmaster Tape'),
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: self::COUNTRY_POLAND),
        ] as $fields) {
            try {
                $this->releaseDataService()->create($fields);
                self::fail('A release needs its production');
            } catch (ReleaseDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testCreateWithAWrongReferenceWritesNothing(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();
        $createdBefore = $this->newElementIds();

        foreach ([
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId'], releaseFormats: ['no-such-format']),
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId'], hardware: ['no-such-hardware']),
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId'], languages: ['xx']),
            new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId'], publisherIds: [self::COUNTRY_POLAND]),
        ] as $fields) {
            try {
                $this->releaseDataService()->create($fields);
                self::fail('A wrong reference must be rejected');
            } catch (ReleaseDataException $exception) {
                self::assertSame(400, $exception->getStatusCode());
            }
        }
        self::assertSame([], $this->releaseRows(array_diff($this->newElementIds(), $createdBefore)));
        // the create runs in one transaction, so not even the structure record is left
        self::assertSame($createdBefore, $this->newElementIds());
    }

    public function testCreateIsForbiddenWithoutThePrivilege(): void
    {
        $this->signIn([self::PUBLIC_VISITORS]);
        $prodId = (int)$this->db->table('module_zxprod')->min('id');
        $this->structureManager->getElementById($prodId);

        try {
            $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $prodId));
            self::fail('Creating must need zxRelease.publicAdd');
        } catch (ReleaseDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame([], $this->newElementIds());
    }

    public function testUpdateChangesTheGivenFieldsAndKeepsTheOthers(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(
            year: 2001,
            releaseType: ReleaseTypes::crack,
            denyVoting: false,
            releaseFormats: ['trd'],
            publisherIds: [$references['otherPublisherId']],
        ));

        self::assertSame(
            array_replace($this->everyFieldStored(), ['year' => 2001, 'releaseType' => 'crack', 'denyVoting' => 0]),
            $this->storedRow(self::TABLE, $releaseId, self::COLUMNS),
        );
        self::assertSame(
            array_replace($this->everyLinkStored($references), ['formats' => ['trd'], 'publishers' => [$references['otherPublisherId']]]),
            $this->storedLinks($releaseId),
        );
        self::assertSame(['publicAdd', 'publicReceive'], $this->loggedActions($releaseId));
    }

    public function testUpdateClearsWithEmptyValuesListsAndZero(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(
            year: 0,
            version: '',
            description: '',
            releaseFormats: [],
            languages: [],
            hardware: [],
            publisherIds: [],
        ));

        self::assertSame(
            array_replace($this->everyFieldStored(), ['year' => 0, 'version' => '', 'description' => '']),
            $this->storedRow(self::TABLE, $releaseId, self::COLUMNS),
        );
        self::assertSame(
            array_replace($this->everyLinkStored($references), ['formats' => [], 'languages' => [], 'hardware' => [], 'publishers' => []]),
            $this->storedLinks($releaseId),
        );
    }

    public function testUpdateMovesTheReleaseToAnotherProd(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(prodId: $references['otherProdId']));

        self::assertSame([$references['otherProdId']], $this->linkedParents($releaseId, 'structure'));
    }

    public function testMovingToAProdWithoutThePrivilegeChangesNothing(): void
    {
        $this->signIn([self::PUBLIC_VISITORS], [['zxRelease', 'publicReceive']]);
        $releaseId = (int)$this->db->table(self::TABLE)->min('id');
        $prodIds = $this->linkedParents($releaseId, 'structure');
        $otherProdId = (int)$this->db->table('module_zxprod')->whereNotIn('id', $prodIds)->min('id');

        try {
            $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(prodId: $otherProdId));
            self::fail('Moving must need zxRelease.publicAdd on the target');
        } catch (ReleaseDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($prodIds, $this->linkedParents($releaseId, 'structure'));
    }

    public function testUpdateIsForbiddenWithoutThePrivilegeOnTheRelease(): void
    {
        $this->signInAsRegisteredUser();
        $releaseId = (int)$this->db->table(self::TABLE)->min('id');
        $stored = $this->db->table(self::TABLE)->where('id', '=', $releaseId)->get();

        try {
            $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(version: '1.1'));
            self::fail('Updating must need zxRelease.publicReceive');
        } catch (ReleaseDataException $exception) {
            self::assertSame(403, $exception->getStatusCode());
        }
        self::assertSame($stored, $this->db->table(self::TABLE)->where('id', '=', $releaseId)->get());
    }

    public function testUpdateStoresTheAuthorsAndPortalIds(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;
        $this->ageModificationTime($releaseId);

        $this->releaseDataService()->update(
            $releaseId,
            new ReleaseFieldsDto(),
            authors: [new AuthorshipInputDto($authorId, ['release', 'adaptation'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '6001')],
        );

        self::assertSame(
            [['roles' => ['release', 'adaptation'], 'startDate' => 0, 'endDate' => 0]],
            $this->authorshipRecords($releaseId, $authorId, 'release'),
        );
        self::assertSame([['origin' => 'zxdb', 'importId' => '6001']], $this->storedImportOrigins($releaseId));
        self::assertGreaterThan(1, $this->modificationTime($releaseId));
    }

    public function testUpdateReplacesTheListsGivenAndKeepsTheOnesLeftOut(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;
        $this->releaseDataService()->update(
            $releaseId,
            new ReleaseFieldsDto(),
            authors: [new AuthorshipInputDto($authorId, ['release'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '6001')],
        );

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(version: '1.1'), importOrigins: []);

        self::assertSame([['roles' => ['release'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($releaseId, $authorId, 'release'));
        self::assertSame([], $this->storedImportOrigins($releaseId));

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(), authors: []);

        self::assertSame([], $this->authorshipRecords($releaseId, $authorId, 'release'));
    }

    public function testAWrongRoleRefusesTheWholeUpdate(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;

        $this->assertRefused(400, ReleaseDataException::class, fn() => $this->releaseDataService()->update(
            $releaseId,
            new ReleaseFieldsDto(version: '1.1'),
            authors: [new AuthorshipInputDto($authorId, ['coder'])],
            importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '6001')],
        ));

        self::assertSame(['version' => ''], $this->storedRow(self::TABLE, $releaseId, ['version']));
        self::assertSame([], $this->authorshipRecords($releaseId, $authorId, 'release'));
        self::assertSame([], $this->storedImportOrigins($releaseId));
    }

    public function testSetMemberAddsTheAuthorAndMovesTheModificationTime(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->ageModificationTime($releaseId);

        $this->releaseDataService()->setMember($releaseId, $authorId, ['release']);

        self::assertSame([['roles' => ['release'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($releaseId, $authorId, 'release'));
        self::assertGreaterThan(1, $this->modificationTime($releaseId));
    }

    public function testACreatedReleaseIsFoundByItsProductionAndByAPartOfItsTitle(): void
    {
        $this->signInAsRegisteredUser();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create(new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: $references['prodId']))->id;
        $releases = $this->container->get(ReleasesRepository::class);

        self::assertSame([$releaseId], $releases->findIdsByProdId($references['prodId']));
        self::assertContains($releaseId, $releases->findIdsByTitle('GOLDMASTER TAP', 50));
        // a wildcard in the phrase is taken literally: unescaped, "_" would match the space
        self::assertNotContains($releaseId, $releases->findIdsByTitle('Goldmaster_Tape', 50));
    }

    public function testReadAnswersWhatCreateAndUpdateWrote(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;
        $reader = $this->container->get(ReleaseFieldsReader::class);

        self::assertEquals($this->everyField($references), $reader->read($this->release($releaseId)));

        $this->releaseDataService()->update($releaseId, new ReleaseFieldsDto(
            releaseFormats: ['trd'],
            languages: [],
            publisherIds: [$references['otherPublisherId']],
            prodId: $references['otherProdId'],
        ));

        self::assertEquals(
            new ReleaseFieldsDto(...array_replace(get_object_vars($this->everyField($references)), [
                'releaseFormats' => ['trd'],
                'languages' => [],
                'publisherIds' => [$references['otherPublisherId']],
                'prodId' => $references['otherProdId'],
            ])),
            $reader->read($this->release($releaseId)),
        );
    }

    public function testCloneCopiesTheFieldsLinksAndAuthorsUnderTheSameProd(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;
        $authorId = $this->container->get(AuthorDataService::class)->create(new AuthorFieldsDto(title: 'Goldmaster Writer'))->id;
        $this->releaseDataService()->setMember($releaseId, $authorId, ['release']);

        $cloneId = $this->releaseDataService()->cloneRelease($releaseId)->id;

        self::assertNotSame($releaseId, $cloneId);
        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $cloneId, self::COLUMNS));
        self::assertSame($this->everyLinkStored($references), $this->storedLinks($cloneId));
        self::assertSame([['roles' => ['release'], 'startDate' => 0, 'endDate' => 0]], $this->authorshipRecords($cloneId, $authorId, 'release'));
        self::assertSame(
            ['zxRelease.clone', 'zxRelease.deleteFile', 'zxRelease.publicDelete', 'zxRelease.publicReceive', 'zxRelease.showPublicForm'],
            $this->allowedOnElement($cloneId),
        );
        self::assertContains('clone', $this->loggedActions($releaseId));
    }

    public function testTheCloneActionMakesTheSameCopy(): void
    {
        $this->signInAsEditor();
        $references = $this->createReferences();
        $releaseId = $this->releaseDataService()->create($this->everyField($references))->id;
        $releasesBefore = $this->releaseRows($this->newElementIds());

        $this->submitEditForm($releaseId, [], 'clone');

        $newReleaseIds = array_values(array_diff(
            array_column($this->releaseRows($this->newElementIds()), 'id'),
            array_column($releasesBefore, 'id'),
        ));
        self::assertCount(1, $newReleaseIds);
        $cloneId = (int)$newReleaseIds[0];
        self::assertSame($this->everyFieldStored(), $this->storedRow(self::TABLE, $cloneId, self::COLUMNS));
        self::assertSame($this->everyLinkStored($references), $this->storedLinks($cloneId));
    }

    private function signInAsRegisteredUser(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
    }

    /**
     * A registered user who may also edit every release.
     */
    private function signInAsEditor(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxRelease', 'publicReceive']]);
    }

    /**
     * Productions and publishers for the release to refer to.
     *
     * @return array{prodId: int, otherProdId: int, publisherId: int, otherPublisherId: int}
     */
    private function createReferences(): array
    {
        $prods = $this->container->get(ProdDataService::class);
        $groups = $this->container->get(GroupDataService::class);

        return [
            'prodId' => $prods->create(new ProdFieldsDto(title: 'Goldmaster Demo'))->id,
            'otherProdId' => $prods->create(new ProdFieldsDto(title: 'Goldmaster Intro'))->id,
            'publisherId' => $groups->create(new GroupFieldsDto(title: 'Goldmaster Publishing'))->id,
            'otherPublisherId' => $groups->create(new GroupFieldsDto(title: 'Goldmaster Press'))->id,
        ];
    }

    /**
     * @param array{prodId: int, otherProdId: int, publisherId: int, otherPublisherId: int} $references
     */
    private function everyField(array $references): ReleaseFieldsDto
    {
        return new ReleaseFieldsDto(
            title: 'Goldmaster Tape',
            year: 1999,
            version: '1.0',
            description: 'The tape of the golden master',
            releaseType: ReleaseTypes::original,
            releaseFormats: ['tap', 'tzx'],
            languages: ['en', 'ru'],
            hardware: ['zx128', 'ay'],
            publisherIds: [$references['publisherId']],
            prodId: $references['prodId'],
            denyVoting: true,
            denyComments: true,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function everyFieldStored(): array
    {
        return [
            'title' => 'Goldmaster Tape',
            'year' => 1999,
            'version' => '1.0',
            'description' => 'The tape of the golden master',
            'releaseType' => 'original',
            'denyVoting' => 1,
            'denyComments' => 1,
        ];
    }

    /**
     * @param array{prodId: int, otherProdId: int, publisherId: int, otherPublisherId: int} $references
     * @return array<string, list<int|string>>
     */
    private function everyLinkStored(array $references): array
    {
        return [
            'prod' => [$references['prodId']],
            'formats' => ['tap', 'tzx'],
            'languages' => ['en', 'ru'],
            'hardware' => ['ay', 'zx128'],
            'publishers' => [$references['publisherId']],
        ];
    }

    /**
     * What the release is linked with, each list sorted.
     *
     * @return array<string, list<int|string>>
     */
    private function storedLinks(int $releaseId): array
    {
        $formats = array_map('strval', $this->db->table(self::TABLE . '_format')->where('elementId', '=', $releaseId)->pluck('value'));
        sort($formats);
        $languages = array_map('strval', $this->db->table('zxitem_language')->where('elementId', '=', $releaseId)->pluck('value'));
        sort($languages);
        $hardware = array_map('strval', $this->db->table(self::TABLE . '_hw_required')
            ->join('hardware', 'hardware.id', '=', self::TABLE . '_hw_required.hardwareId')
            ->where(self::TABLE . '_hw_required.elementId', '=', $releaseId)
            ->pluck('hardware.code'));
        sort($hardware);

        return [
            'prod' => $this->linkedParents($releaseId, 'structure'),
            'formats' => $formats,
            'languages' => $languages,
            'hardware' => $hardware,
            'publishers' => $this->linkedParents($releaseId, 'zxReleasePublishers'),
        ];
    }

    private function userId(): int
    {
        return (int)$this->db->table('module_user')->where('userName', '=', 'crontab')->value('id');
    }

    /**
     * @param array<int> $elementIds
     * @return list<array<string, mixed>>
     */
    private function releaseRows(array $elementIds): array
    {
        return $elementIds === [] ? [] : $this->db->table(self::TABLE)->whereIn('id', array_values($elementIds))->get();
    }

    private function release(int $releaseId): zxReleaseElement
    {
        $release = $this->structureManager->getElementById($releaseId);
        self::assertInstanceOf(zxReleaseElement::class, $release);

        return $release;
    }

    private function releaseDataService(): ReleaseDataService
    {
        return $this->container->get(ReleaseDataService::class);
    }
}
