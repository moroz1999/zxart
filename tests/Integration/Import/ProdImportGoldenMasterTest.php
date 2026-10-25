<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Import;

require_once __DIR__ . '/ImportGoldenMasterTestCase.php';

use partyElement;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use structureManager;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\Labels\Label;
use ZxArt\Import\Prods\Dto\ArticleDTO;
use ZxArt\Import\Prods\Dto\FileWithAuthorDTO;
use ZxArt\Import\Prods\Dto\PartyRefDTO;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Shared\EntityType;
use ZxArt\ZxProdCategories\CategoryIds;

/**
 * Productions and releases through {@see ProdsService::importProd()}.
 */
#[RunTestsInSeparateProcesses]
final class ProdImportGoldenMasterTest extends ImportGoldenMasterTestCase
{
    private const string FILES_URL = 'https://golden-master.invalid/files/';
    private const int PARTY_YEAR_ELEMENT_1999 = 2250;

    public function testNewProdWithReleasesLabelsFilesAndArticles(): void
    {
        $this->createParty('Golden Master Party');
        $this->mapCategory('gm-cat-demo', CategoryIds::DEMOS);
        $this->mapCategory('gm-cat-mega', CategoryIds::MEGADEMO);

        $this->prodsService()->importProd($this->fullProd(), ImportOrigin::Pouet);

        $this->assertMatchesSnapshot();
    }

    public function testNewProdWithoutCategoriesGoesToMisc(): void
    {
        $this->prodsService()->importProd(
            new ProdImportDTO(id: 'gm-bare', title: 'Goldmaster Bare Intro Thing', year: 1993),
            ImportOrigin::Pouet,
        );

        $this->assertMatchesSnapshot();
    }

    public function testNewReleaseFromALocalFile(): void
    {
        $service = $this->prodsService();
        $service->importProd(
            new ProdImportDTO(
                id: 'gm-local',
                title: 'Goldmaster Local Copy',
                year: 1991,
                directCategories: [CategoryIds::GAMES->value],
                releases: [
                    new ReleaseImportDTO(
                        id: 'gm-local-r1',
                        title: 'Goldmaster Local Copy',
                        filePath: __DIR__ . '/fixtures/files/beta.tap',
                        md5: 'known',
                    ),
                ],
            ),
            ImportOrigin::Vtrdos,
        );

        $this->assertMatchesSnapshot();
    }

    public function testReimportWithoutFlagsKeepsTheProdAndItsReleases(): void
    {
        $this->createParty('Golden Master Party');
        $this->mapCategory('gm-cat-demo', CategoryIds::DEMOS);
        $this->mapCategory('gm-cat-mega', CategoryIds::MEGADEMO);
        $this->prodsService()->importProd($this->fullProd(), ImportOrigin::Pouet);

        $this->prodsService()->importProd($this->changedProd(), ImportOrigin::Pouet);

        $this->assertMatchesSnapshot();
    }

    public function testReimportOfExistingProdsAndReleasesFillsOnlyWhatIsEmpty(): void
    {
        $this->createParty('Golden Master Party');
        $this->mapCategory('gm-cat-demo', CategoryIds::DEMOS);
        $this->mapCategory('gm-cat-mega', CategoryIds::MEGADEMO);
        $this->mapCategory('gm-cat-game', CategoryIds::GAMES);
        $this->prodsService()->importProd($this->fullProd(), ImportOrigin::Pouet);

        $service = $this->prodsService();
        $service->setUpdateExistingProds(true);
        $service->setUpdateExistingReleases(true);
        $service->importProd($this->changedProd(), ImportOrigin::Pouet);

        $this->assertMatchesSnapshot();
    }

    public function testReimportWithEveryForceFlagOverwrites(): void
    {
        $this->createParty('Golden Master Party');
        $this->mapCategory('gm-cat-demo', CategoryIds::DEMOS);
        $this->mapCategory('gm-cat-mega', CategoryIds::MEGADEMO);
        $this->mapCategory('gm-cat-game', CategoryIds::GAMES);
        $this->prodsService()->importProd($this->fullProd(), ImportOrigin::Pouet);

        $service = $this->prodsService();
        $service->setUpdateExistingProds(true);
        $service->setUpdateExistingReleases(true);
        $service->setForceUpdateYear(true);
        $service->setForceUpdateYoutube(true);
        $service->setForceUpdateExternalLink(true);
        $service->setForceUpdateCategories(true);
        $service->setForceUpdateImages(true);
        $service->setForceUpdateTitles(true);
        $service->setForceUpdateAuthors(true);
        $service->setForceUpdateGroups(true);
        $service->setForceUpdatePublishers(true);
        $service->setForceUpdateReleaseAuthors(true);
        $service->setForceUpdateReleasePublishers(true);
        $service->setForceUpdateReleaseType(true);
        $service->setForceUpdateReleaseFiles(true);
        $service->setAddImages(true);
        $service->importProd($this->changedProd(), ImportOrigin::Pouet);

        $this->assertMatchesSnapshot();
    }

    public function testProdKnownUnderAnotherOriginGetsThisOriginsId(): void
    {
        $this->prodsService()->importProd(
            new ProdImportDTO(id: 'gm-zxdb-1', title: 'Goldmaster Crossover', year: 1990),
            ImportOrigin::Zxdb,
        );

        $service = $this->prodsService();
        $service->setUpdateExistingProds(true);
        $service->importProd(
            new ProdImportDTO(
                id: 'gm-pouet-1',
                title: 'Goldmaster Crossover',
                description: 'Found by the zxdb id.',
                year: 1990,
                ids: [ImportOrigin::Zxdb->value => 'gm-zxdb-1'],
                importIds: [ImportOrigin::Demozoo->value => 'gm-dzoo-1'],
            ),
            ImportOrigin::Pouet,
        );

        $this->assertMatchesSnapshot();
    }

    public function testProdFoundByItsTitleAndYear(): void
    {
        $this->prodsService()->importProd(
            new ProdImportDTO(id: 'gm-title-1', title: 'Goldmaster Same Title', year: 1994),
            ImportOrigin::Zxdb,
        );

        $service = $this->prodsService();
        $service->setUpdateExistingProds(true);
        $service->importProd(
            new ProdImportDTO(
                id: 'gm-title-2',
                title: 'Goldmaster Same Title',
                altTitle: 'Same Title Alt',
                year: 1994,
                origin: ImportOrigin::Vtrdos,
            ),
            ImportOrigin::Vtrdos,
        );

        $this->assertMatchesSnapshot();
    }

    public function testCompilationAndSeriesLinkTheirImportedItems(): void
    {
        $service = $this->prodsService();
        $service->importProd(new ProdImportDTO(id: 'gm-item-1', title: 'Goldmaster Item One', year: 1992), ImportOrigin::Zxdb);
        $service->importProd(
            new ProdImportDTO(
                id: 'gm-item-2',
                title: 'Goldmaster Item Two',
                year: 1992,
                releases: [new ReleaseImportDTO(id: 'gm-item-2-r1', title: 'Goldmaster Item Two', year: 1992)],
            ),
            ImportOrigin::Zxdb,
        );
        $service->importProd(
            new ProdImportDTO(
                id: 'gm-compilation',
                title: 'Goldmaster Compilation',
                year: 1993,
                directCategories: [CategoryIds::COMPILATION_GAMES->value],
                compilationItems: ['gm-item-1', 'gm-item-2-r1', 'gm-missing', null],
                seriesProdIds: ['gm-item-1', 'gm-item-2', 'gm-missing'],
            ),
            ImportOrigin::Zxdb,
        );

        $this->assertMatchesSnapshot();
    }

    private function prodsService(): ProdsService
    {
        return $this->container->get(ProdsService::class);
    }

    private function fullProd(): ProdImportDTO
    {
        return new ProdImportDTO(
            id: 'gm-full',
            title: 'Goldmaster Demo, The',
            altTitle: 'Goldmaster Alt',
            description: 'A demo made for the golden master.',
            htmlDescription: false,
            htmlInstructions: false,
            instructions: 'Press SPACE.',
            languages: ['en', 'ru'],
            hardwareRequired: ['zx128', 'ay', 'zx128'],
            legalStatus: LegalStatus::allowed,
            youtubeId: 'gm-youtube-1',
            externalLink: 'https://example.invalid/goldmaster',
            compo: 'demo',
            year: 1999,
            importIds: [ImportOrigin::Demozoo->value => 'gm-dzoo-full'],
            labels: [
                new Label(id: 'gm-coder', name: 'Goldmaster Coder', realName: 'Gold Master', countryName: 'Poland', isAlias: false, isPerson: true, isGroup: false),
                new Label(id: 'gm-coder-alias', name: 'Gm Coder Alias', isAlias: true, isPerson: true, isGroup: false, authorId: 'gm-coder'),
                new Label(id: 'gm-group', name: 'Goldmaster Crew', countryName: 'Poland', isAlias: false, isPerson: false, isGroup: true, abbreviation: 'GMC', website: 'https://crew.invalid/'),
                new Label(id: 'gm-label', name: 'Goldmaster Label', cityName: 'Warsaw'),
            ],
            authorRoles: ['gm-coder' => ['code'], 'gm-coder-alias' => ['graphics', 'music'], 'gm-missing' => ['code']],
            groups: ['gm-group', 'gm-missing'],
            publishers: ['gm-group', 'gm-label'],
            undetermined: ['gm-group' => [], 'gm-label' => ['support']],
            party: new PartyRefDTO(title: 'Golden Master Party', website: 'https://party.invalid/', year: 1999, place: 3),
            directCategories: [CategoryIds::DEMOS->value],
            categories: ['gm-cat-demo', 'gm-cat-mega', 'gm-cat-unknown'],
            images: [self::FILES_URL . 'screen-1.png', self::FILES_URL . 'screen-2.png'],
            maps: [new FileWithAuthorDTO(self::FILES_URL . 'map.png', 'Map Maker')],
            inlayImages: [self::FILES_URL . 'inlay.jpg'],
            rzx: [new FileWithAuthorDTO(self::FILES_URL . 'playthrough.rzx', 'Player')],
            articles: [new ArticleDTO(title: 'Goldmaster Review', introduction: 'Intro', externalLink: 'https://press.invalid/review', content: '<p>Review text</p>')],
            releases: [
                new ReleaseImportDTO(
                    id: 'gm-full-r1',
                    title: 'Goldmaster Demo, The',
                    year: 1999,
                    languages: ['en'],
                    version: '1.0',
                    releaseType: 'original',
                    fileUrl: self::FILES_URL . 'alpha.tap',
                    description: 'The party version.',
                    hardwareRequired: ['zx128', 'ay'],
                    labels: [new Label(id: 'gm-cracker', name: 'Goldmaster Cracker', isAlias: false, isPerson: true, isGroup: false)],
                    authors: ['gm-cracker' => ['release']],
                    publishers: ['gm-group'],
                    undetermined: ['gm-label' => ['release'], 'gm-coder' => ['adaptation']],
                    images: [self::FILES_URL . 'release-screen.png'],
                    inlayImages: [self::FILES_URL . 'release-inlay.jpg'],
                    infoFiles: [self::FILES_URL . 'manual.txt'],
                    adFiles: [self::FILES_URL . 'advert.jpg'],
                ),
                new ReleaseImportDTO(
                    id: 'gm-full-r2',
                    title: 'Goldmaster Demo 128',
                    year: 2000,
                    releaseType: 'mod',
                    fileUrl: self::FILES_URL . 'alpha-128.tap',
                    fileName: 'goldmaster128.tap',
                    md5: 'known',
                ),
            ],
        );
    }

    /**
     * The same production as {@see self::fullProd()} as the source shows it
     * later: every field changed, one release more.
     */
    private function changedProd(): ProdImportDTO
    {
        return new ProdImportDTO(
            id: 'gm-full',
            title: 'Goldmaster Demo Final',
            altTitle: 'Goldmaster Alt 2',
            description: 'A changed description.',
            htmlDescription: true,
            htmlInstructions: true,
            instructions: 'Press ENTER.',
            languages: ['de'],
            hardwareRequired: ['pentagon128'],
            legalStatus: LegalStatus::forbidden,
            youtubeId: 'gm-youtube-2',
            externalLink: 'https://example.invalid/goldmaster-2',
            compo: 'wild',
            year: 2001,
            labels: [
                new Label(id: 'gm-coder', name: 'Goldmaster Coder Renamed', realName: 'Gold Master Second', countryName: 'Russia', isAlias: false, isPerson: true, isGroup: false),
                new Label(id: 'gm-musician', name: 'Goldmaster Musician', isAlias: false, isPerson: true, isGroup: false),
                new Label(id: 'gm-group-2', name: 'Goldmaster Second Crew', isAlias: false, isPerson: false, isGroup: true),
            ],
            authorRoles: ['gm-coder' => ['code', 'design'], 'gm-musician' => ['music']],
            groups: ['gm-group-2'],
            publishers: ['gm-group-2'],
            party: new PartyRefDTO(title: 'Golden Master Party', year: 1999, place: 1),
            directCategories: [CategoryIds::TRACKMO->value],
            categories: ['gm-cat-game'],
            images: [self::FILES_URL . 'screen-2.png', self::FILES_URL . 'map.png'],
            maps: [new FileWithAuthorDTO(self::FILES_URL . 'screen-1.png')],
            inlayImages: [self::FILES_URL . 'advert.jpg'],
            rzx: [new FileWithAuthorDTO(self::FILES_URL . 'playthrough.rzx', 'Player')],
            articles: [new ArticleDTO(title: 'Goldmaster Second Review', introduction: '', externalLink: null, content: 'Second')],
            releases: [
                new ReleaseImportDTO(
                    id: 'gm-full-r1',
                    title: 'Goldmaster Demo Final',
                    year: 2001,
                    languages: ['de'],
                    version: '2.0',
                    releaseType: 'mod',
                    fileUrl: self::FILES_URL . 'beta.tap',
                    description: 'A changed release.',
                    hardwareRequired: ['pentagon128'],
                    labels: [new Label(id: 'gm-musician', name: 'Goldmaster Musician', isAlias: false, isPerson: true, isGroup: false)],
                    authors: ['gm-musician' => ['music']],
                    publishers: ['gm-group-2'],
                    images: [self::FILES_URL . 'screen-1.png'],
                    inlayImages: [self::FILES_URL . 'inlay.jpg'],
                    infoFiles: [self::FILES_URL . 'manual.txt'],
                    adFiles: [],
                ),
                new ReleaseImportDTO(
                    id: 'gm-full-r3',
                    title: 'Goldmaster Demo Fixed',
                    year: 2002,
                    releaseType: 'corrupted',
                    fileUrl: self::FILES_URL . 'beta.tap',
                    md5: 'known',
                ),
            ],
        );
    }

    private function createParty(string $title): void
    {
        $party = $this->container->get(structureManager::class)
            ->createElement('party', 'show', self::PARTY_YEAR_ELEMENT_1999);
        self::assertInstanceOf(partyElement::class, $party);
        $party->title = $title;
        $party->structureName = $title;
        $party->persistElementData();
    }

    private function mapCategory(string $importId, CategoryIds $category): void
    {
        $this->mapImportId($category->value, $importId, ImportOrigin::Pouet, EntityType::Category);
    }
}
