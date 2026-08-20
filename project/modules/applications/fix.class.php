<?php

use App\Users\CurrentUserService;
use Illuminate\Database\Connection;
use ZxArt\Authors\Constants;
use ZxArt\LinkTypes;
use ZxArt\Prods\Repositories\ProdsRepository;
use ZxArt\Prods\Services\ProdHardwareMigrationService;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\ZxProdCategories\CategoryIds;

class fixApplication extends controllerApplication
{
    protected $applicationName = 'fix';
    public $rendererName = 'smarty';
    /**
     * @var structureManager
     */
    protected $structureManager;
    /**
     * @var Connection
     */
    protected $db;
    private $log = PUBLIC_PATH . 'zxChip.log';
    private $idLog = PUBLIC_PATH . 'zxChipIds.log';
    private const int CATEGORY_MAGAZINE = 92179;
    private const int CATEGORY_NEWSPAPER = 92182;

    /**
     * @return void
     */
    public function initialize()
    {
        $this->createRenderer();
        $this->db = $this->getService('db');
    }

    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 1160);
        $renderer = $this->getService(renderer::class);
        $renderer->endOutputBuffering();

        $currentUserService = $this->getService(CurrentUserService::class);
        $user = $currentUserService->getCurrentUser();
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->structureManager = $this->getService('adminStructureManager');
            /**
             * @var LanguagesManager $languagesManager
             */
            $languagesManager = $this->getService(LanguagesManager::class);
            $languagesManager->setCurrentLanguageCode('eng');

            // One-off jobs are selected by ?job=, so a run has to be asked for
            // explicitly instead of being whatever the file was last edited to do.
            $job = (string)($controller->getParameter('job') ?: '');
            match ($job) {
                'hardware-autofill' => $this->autofillReleaseHardware(),
                'prod-hardware-migrate' => $this->migrateProdHardware(),
                '' => $this->fixReleases(),
                default => print('unknown job: ' . htmlspecialchars($job) . '<br>'),
            };
//            $this->addCategoryToQueue(92183, QueueType::AI_SEO, QueueStatus::STATUS_TODO, 5000);
//            $this->addCategoryToQueue(92534, QueueType::AI_INTRO, QueueStatus::STATUS_TODO, 5000);
//            $this->addCategoryToQueue(204819, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);
        }
    }

    /**
     * Moves the shared hardware of each production out of its releases and onto
     * the production itself, leaving only release-specific deviations behind.
     *
     * `/fix/job:prod-hardware-migrate/` — `dry:1` prints the plan without
     * writing, `offset:N` / `limit:N` work through the catalogue in batches
     * (53 000 productions will not fit in one request).
     *
     * **Must not be run before the import rerouting is deployed.** Import
     * de-duplication compares hardware to decide whether an incoming production
     * already exists; once the releases are stripped it has to compare the
     * aggregated set instead, or every later import creates duplicate
     * productions. See the plan's D.7.
     *
     * Idempotent: a production that already carries its own hardware is skipped
     * unless `force:1` is given.
     */
    private function migrateProdHardware(): void
    {
        $controller = $this->getService(controller::class);
        $isDryRun = (bool)$controller->getParameter('dry');
        $isForced = (bool)$controller->getParameter('force');
        $offset = (int)($controller->getParameter('offset') ?: 0);
        $limit = (int)($controller->getParameter('limit') ?: 1000);

        $migrationService = $this->getService(ProdHardwareMigrationService::class);

        $ids = $this->db->table(ProdsRepository::TABLE)
            ->orderBy('id')
            ->offset($offset)
            ->limit($limit)
            ->pluck('id');

        echo 'prod hardware migration' . ($isDryRun ? ' (dry run)' : '') . ': '
            . count($ids) . ' productions from offset ' . $offset . '<br>';

        $changed = 0;
        $counter = 0;
        foreach ($ids as $id) {
            $counter++;
            /** @var zxProdElement|null $prod */
            $prod = $this->structureManager->getElementById((int)$id);
            if ($prod === null) {
                continue;
            }
            if (!$isForced && $prod->hardwareRequired !== []) {
                continue;
            }

            $plan = $migrationService->plan($prod);
            if ($plan === null) {
                continue;
            }

            $changed++;
            echo $counter . '/' . count($ids) . ' <a href="/prod/' . $id . '" target="_blank">' . $id . '</a> prod: '
                . implode(', ', $plan['prod']);
            foreach ($plan['releases'] as $releaseId => $remaining) {
                echo ' | release ' . $releaseId . ' -> ' . ($remaining === [] ? '(none)' : implode(', ', $remaining));
            }
            echo '<br>';

            if ($isDryRun) {
                continue;
            }

            $prod->hardwareRequired = $plan['prod'];
            $prod->persistElementData();
            foreach ($prod->getReleasesList() as $release) {
                $releaseId = $release->getPersistedId();
                if (!array_key_exists($releaseId, $plan['releases'])) {
                    continue;
                }
                $release->hardwareRequired = $plan['releases'][$releaseId];
                $release->persistElementData();
                $this->structureManager->clearElementCache($releaseId);
            }
            $this->structureManager->clearElementCache((int)$id);
        }

        echo 'done: ' . $changed . ' of ' . count($ids) . ' productions '
            . ($isDryRun ? 'would change' : 'changed')
            . '. Next offset: ' . ($offset + $limit) . '<br>';
    }

    /**
     * Backfills the hardware every release's format implies.
     *
     * This is the only thing that applies {@see ReleaseHardwareAutofillService}:
     * saving a release derives nothing from its format. Additive and idempotent,
     * so re-running is safe and a batch can be repeated.
     *
     * `/fix/job:hardware-autofill/` — supports `dry:1` to print the diff without
     * writing, and `offset:N` / `limit:N` to work through the catalogue in
     * batches (a full pass is ~85 000 element loads and will not fit in one request).
     */
    private function autofillReleaseHardware(): void
    {
        $controller = $this->getService(controller::class);
        $isDryRun = (bool)$controller->getParameter('dry');
        $offset = (int)($controller->getParameter('offset') ?: 0);
        $limit = (int)($controller->getParameter('limit') ?: 2000);

        // only releases that have a format at all: the rules read nothing else
        $ids = $this->db->table('module_zxrelease_format')
            ->distinct()
            ->orderBy('elementId')
            ->offset($offset)
            ->limit($limit)
            ->pluck('elementId');

        echo 'hardware autofill' . ($isDryRun ? ' (dry run)' : '') . ': '
            . count($ids) . ' releases from offset ' . $offset . '<br>';

        $changed = 0;
        $counter = 0;
        foreach ($ids as $id) {
            $counter++;
            /** @var zxReleaseElement|null $release */
            $release = $this->structureManager->getElementById((int)$id);
            if ($release === null) {
                continue;
            }

            // goes through the element, so the rules see the same effective
            // hardware the release itself resolves
            $additions = $release->getHardwareAutofillAdditions();
            if ($additions === []) {
                continue;
            }

            $changed++;
            echo $counter . '/' . count($ids) . ' <a href="/release/' . $id . '" target="_blank">' . $id . '</a> +'
                . implode(', ', $additions) . '<br>';

            if (!$isDryRun) {
                $release->hardwareRequired = [...$release->hardwareRequired, ...$additions];
                $release->persistElementData();
                $this->structureManager->clearElementCache((int)$id);
            }
        }

        echo 'done: ' . $changed . ' of ' . count($ids) . ' releases '
            . ($isDryRun ? 'would change' : 'changed')
            . '. Next offset: ' . ($offset + $limit) . '<br>';
    }
//92177
//92183
//92534
//244858
//244880
//204819 - demoscene
//202588 - compilation

    private function fixDisconnectedImages(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $result = $this->db->table('module_zxpicture')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');

        foreach ($ids as $id) {
            /**
             * @var zxPictureElement $picture
             */
            $picture = $this->structureManager->getElementById($id);
            if ($picture === null) {
                $linksManager->linkElements(Constants::UNKNOWN_ID, $id, LinkTypes::AUTHOR_PICTURE->value);
                echo "restored <a href='/route/id:$id' target='_blank'>" . $id . "</a><br>";
            }
        }
    }

    private function fixProdInvalidImages()
    {
        $result = $this->db->table('module_zxprod')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            if ($counter < 33466) {
                $counter++;
                continue;
            }
            /**
             * @var zxProdElement $prod
             */
            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                echo $prod->getPersistedId() . ' ' . $prod->getTitle() . '<br>';
                $this->deleteInvalidImages($prod, 'connectedFile');
                $this->deleteInvalidImages($prod, 'inlayFilesSelector');
                $this->deleteInvalidImages($prod, 'mapFilesSelector');
                $this->deleteInvalidImages($prod, 'rzx');
                $releases = $prod->getReleasesList();
                foreach ($releases as $release) {
                    $this->deleteInvalidImages($release, 'screenshotsSelector');
                    $this->deleteInvalidImages($release, 'inlayFilesSelector');
                    $this->deleteInvalidImages($release, 'infoFilesSelector');
                    $this->deleteInvalidImages($release, 'adFilesSelector');
                    $this->structureManager->clearElementCache($release->id);
                }
                $this->structureManager->clearElementCache($prod->id);
            }
            $counter++;
//            if ($counter > 10) {
//                break;
//            }
            flush();
        }
    }

    private function deleteInvalidImages(zxProdElement|zxReleaseElement $element, $propertyName)
    {
        $md5s = [];
        $prodImages = $element->getFilesList($propertyName);
        foreach ($prodImages as $prodImage) {
            $filePath = $prodImage->getFilePath();
            if (!is_file($filePath) || filesize($filePath) === 0) {
                echo 'deleted missing image element ' . $prodImage->getPersistedId() . ' ' . $propertyName . '<br>';
                $prodImage->deleteElementData();
                continue;
            }

            $md5 = md5(file_get_contents($filePath));
            $existing = isset($md5s[$md5]);
            if ($existing) {
                echo 'deleted duplicated image element ' . $prodImage->getPersistedId() . ' ' . $propertyName . '<br>';
                $prodImage->deleteElementData();
                continue;
            }
            $md5s[$md5] = true;
        }
    }


    private function miscTemp(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $ids = $linksManager->getConnectedIdList(CategoryIds::MISC->value, LinkTypes::ZX_PROD_CATEGORY->value, 'parent');
        foreach ($ids as $id) {
            $parentIds = $linksManager->getConnectedIdList($id, LinkTypes::ZX_PROD_CATEGORY->value, 'child');
            foreach ($parentIds as $parentId) {
                if ($parentId !== CategoryIds::MISC->value) {
                    echo "INSERT IGNORE INTO `engine_structure_links` (`parentStructureId`, `childStructureId`, `type`) VALUES ($parentId, $id, '" . LinkTypes::ZX_PROD_CATEGORY->value . "'); <br>";
                }
            }
        }
    }

    private function fixMissingCategories(): void
    {
        $linksManager = $this->getService(linksManager::class);

        $records = $this->db->table('module_zxprod')->whereNotIn('id',
            static function ($query) {
                $query->from('structure_links')->where('type', '=', LinkTypes::ZX_PROD_CATEGORY->value)->select('childStructureId');
            },
        )->get();
        foreach ($records as $record) {
            $linksManager->linkElements(CategoryIds::MISC->value, $record['id'], LinkTypes::ZX_PROD_CATEGORY->value);
        }
    }

    private function fixZx81(): void
    {
        $apiQueriesManager = $this->getService(ApiQueriesManager::class);
//        $filters = ['zxReleaseHardware' => ["zx80",
//            "zx8116",
//            "zx811",
//            "zx812",
//            "zx8132",
//            "zx8164",]
//        ];
        $filters = [];
        $apiQuery = $apiQueriesManager->getQuery()
            ->setExportType('zxRelease')
            ->setFiltrationParameters($filters);

        $dbQuery = $apiQuery->getExportFilteredQuery();
        $records = $dbQuery->get(['id']);
        $ids = array_column($records, 'id');
        $counter = 0;
        $count = count($ids);
        foreach ($ids as $id) {
            if ($id < 343149) {
                continue;
            }
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            $release = $this->structureManager->getElementById($id);

            if (!$release) {
                echo 'failed ' . $id . '<br>';
                continue;
            }
            $release->updateFileStructure();
            echo 'fixed ' . $id . ' ' . $release->getTitle() . '<br>';

            $counter++;
            flush();
        }

    }

    private function addCategoryToQueue(int $categoryId, QueueType $queueType, QueueStatus $status, ?int $limit = null): void
    {
        /**
         * @var zxProdCategoryElement $category
         */
        $category = $this->structureManager->getElementById($categoryId);
        $subcategoriesIds = [];
        $category->getSubCategoriesTreeIds($subcategoriesIds);
        $filters = [LinkTypes::ZX_PROD_CATEGORY->value => $subcategoriesIds];
        /**
         * @var ApiQueriesManager $apiQueriesManager
         */
        $apiQueriesManager = $this->getService(ApiQueriesManager::class);
        $apiQuery = $apiQueriesManager->getQuery()
            ->setExportType('zxProd')
            ->setFiltrationParameters($filters);
        $dbQuery = $apiQuery->getExportFilteredQuery();
        if (!$dbQuery) {
            throw new RuntimeException('No db query provided');
        }
        if ($limit !== null) {
            $dbQuery->limit($limit);
        }
        $ids = $dbQuery
            ->whereNotIn('id', function ($query) use ($queueType) {
                $query->from('queue')
                    ->select('elementId')
                    ->where('type', '=', $queueType->value);
            })
            ->pluck('id');
        $records = array_map(function ($id) use ($queueType, $status) {
            return [
                'elementId' => $id,
                'type' => $queueType->value,
                'status' => $status->value,
            ];
        }, $ids);
        if (count($records)) {
            $this->db->table('queue')->insert($records);
            echo 'Inserted category ' . $category->getTitle() . ' into queue. Records: ' . count($records) . '<br>';
        }
    }

    private function fixProds(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $result = $this->db->table('module_zxprod')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {

            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if (!$prod) {
                $linksManager->linkElements(418662, $id, LinkTypes::ZX_PROD_CATEGORY->value);
                echo 'fixed' . $id . '<br>';
            } else {
                echo 'exists' . $id . '<br>';
            }
            $counter++;
            flush();
        }
    }

    private function fixCompilations(): void
    {
        $result = $this->db->table('structure_links')
            ->where('type', '=', LinkTypes::COMPILATION->value)
            ->groupBy('parentStructureId')
            ->get(['parentStructureId']);
        $ids = array_column($result, 'parentStructureId');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                $prod->persistElementData();
                echo 'updated' . $id . ' ' . $prod->getTitle() . '<br>';
            } else {
                echo 'not found ' . $id . '<br>';
            }
            $counter++;
            flush();
        }
    }

    private function fixSeries(): void
    {
        $result = $this->db->table('structure_links')
            ->where('type', '=', LinkTypes::SERIES->value)
            ->groupBy('parentStructureId')
            ->get(['parentStructureId']);
        $ids = array_column($result, 'parentStructureId');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                $prod->persistElementData();
                echo 'updated' . $id . ' ' . $prod->getTitle() . '<br>';
            } else {
                echo 'not found ' . $id . '<br>';
            }
            $counter++;
            flush();
        }
    }

    private function showErrors(): void
    {
        $result = $this->db->table('structure_links')
            ->whereIn('type', [LinkTypes::SERIES->value])
            ->groupBy('parentStructureId')
            ->get(['parentStructureId']);
        $ids = array_column($result, 'parentStructureId');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            /**
             * @var zxProdElement $prod
             */
            $prod = $this->structureManager->getElementById($id);
            if ($prod && $prod->getReleasesList()) {
                echo $counter . ' ' . round(100 * $counter / $count) . '% ';

                echo 'not series ' . $id . ' ' . $prod->getTitle() . '<br>';
            }
            $counter++;
            flush();
        }
    }

    /**
     * @return void
     */
    private function fixReleases()
    {

        $result = $this->db->table('structure_elements')
            ->where('structureType', '=', 'zxRelease')
            ->where('dateCreated', '>', 1772882080)
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $delete = false;
            /** @var zxReleaseElement $release */
            $release = $this->structureManager->getElementById($id);
            if (!$release) {
                echo 'release is missing ' . $id . '<br>';
                $release = $this->structureManager->getElementById($id, null, true);
                if ($release) {
                    $release->deleteElementData();
                }
            }
//            } else {
//                $filePath = $release->getFilePath();
//                $fileName = $release->getFileName();
//                if ($filePath && $fileName) {
//                    if (is_file($filePath)) {
//                        if ((filesize($filePath) <= 20)) {
//                            $delete = true;
//                        }
//                        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip') {
//                            $zip = new \ZipArchive();
//                            if ($zip->open($filePath) === true) {
//                                $zip->close();
//                            } else {
//                                $delete = true;
//                            }
//                        }
//
//                        if ($delete) {
//                            echo 'delete ' . $release->getPersistedId() . ' ' . $release->getTitle() . '<br>';
//                            $release->deleteElementData();
//                        }
//                    } else {
//                        echo 'file missing <a href="/route/id:' . $release->getPersistedId() . '">' . $release->getTitle() . '</a> ' . $filePath . '<br>';
//                    }
//                }
//            }
            $counter++;
            flush();
            if ($counter > 10000) {
                exit;
            }
        }
    }

    private function fixPress(): void
    {
        $prodsService = $this->getService(ProdsService::class);

        $linksManager = $this->getService(linksManager::class);
        $name = 'Nicron';
        $replacement = 'Nicron issue';
        $result = $this->db->table(ProdsRepository::TABLE)
            ->where('title', 'like', $name . ' #%')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $prod = $this->structureManager->getElementById($id);
            if ($prod === null) {
                echo 'failed ' . $id . "<br>";
                continue;
            }
            $split = explode('#', $prod->title);

            $result = $this->db->table(ProdsRepository::TABLE)
                ->where('title', 'like', $name . ' ' . $split[1])
                ->orWhere('title', 'like', $name . ' ' . (int)$split[1])
                ->orWhere('title', 'like', $name . ' #' . (int)$split[1])
                ->orWhere('title', 'like', $replacement . ' #' . $split[1])
                ->orWhere('title', 'like', $replacement . ' 0' . $split[1])
                ->orWhere('title', 'like', $replacement . ' ' . $split[1])
                ->orWhere('title', 'like', $replacement . ' ' . (int)$split[1])
                ->orderBy('id')
                ->first(['id']);

            $prod2 = $this->structureManager->getElementById($result['id']);
            if ($prod2) {
                $linksManager->unLinkElements(CategoryIds::MISC->value, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
                $linksManager->unLinkElements(self::CATEGORY_MAGAZINE, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
                $linksManager->linkElements(self::CATEGORY_NEWSPAPER, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
//            $linksManager->linkElements(self::CATEGORY_MAGAZINE, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);


                $prod2->title = $prod->title;

                $prodsService->joinDeleteZxProd($prod2->id, $prod->id, false);
            } else {
                echo 'failed to join press ' . $id . ' ' . $prod->getTitle() . "<br>";
            }

            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod && $prod2) {
                echo 'fixed ' . $prod->title . ' ' . $prod2->title . '<br>';
            }
            $counter++;
            flush();
        }
    }

    private function fixDemoCategories(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $demoId = CategoryIds::DEMOS->value;
        $megaDemoId = CategoryIds::MEGADEMO->value;
        $trackmoId = CategoryIds::TRACKMO->value;
        $linkType = LinkTypes::ZX_PROD_CATEGORY->value;
        $ids = $linksManager->getConnectedIdList($demoId, $linkType, 'parent');
        foreach ($ids as $prodId) {
            $categoryIdsMap = $linksManager->getConnectedIdIndex($prodId, $linkType, 'child');
            if (isset($categoryIdsMap[$megaDemoId]) || isset($categoryIdsMap[$trackmoId])) {
                $linksManager->unLinkElements($demoId, $prodId, $linkType);
                echo 'fixed ' . $prodId . ' ' . '<br>';
            }
        }
    }

    private function fixMisc(): void
    {
        echo 'fixMisc <br>';
        $linksManager = $this->getService(linksManager::class);

        $ids = $linksManager->getConnectedIdList(CategoryIds::MISC->value, LinkTypes::ZX_PROD_CATEGORY->value, 'parent');

        foreach ($ids as $prodId) {
            $prod = $this->structureManager->getElementById($prodId);
            echo 'fixed ' . $prod->title . ' ' . '<br>';

            $prod->checkAndPersistCategories();
        }
    }

    private function fixPressCategories(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $name = 'Outlet issue';
        $result = $this->db->table(ProdsRepository::TABLE)
            ->where('title', 'like', $name . '%')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $prod = $this->structureManager->getElementById($id);
            if ($prod === null) {
                echo 'failed ' . $id . "<br>";
                continue;
            }

            $linksManager->unLinkElements(CategoryIds::COMPILATION_GAMES->value, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
            $linksManager->linkElements(CategoryIds::PRESS_MAGAZINES->value, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);

            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                echo 'fixed ' . $prod->title . ' ' . '<br>';
            }
            $counter++;
            flush();
        }
    }


    private function deletePress(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $result = $this->db->table('module_pressarticle')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {

            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                $prod->deleteElementData();
                echo 'deleted' . $id . '<br>';
            }
            $counter++;
            flush();
        }
    }

    private function deleteProds(): void
    {
        $linksManager = $this->getService(linksManager::class);
        $result = $this->db->table(ProdsRepository::TABLE)
            ->where('id', '>=', 453563)
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {

            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                $prod->deleteElementData();
                echo 'deleted' . $id . '<br>';
            }
            $counter++;
            flush();
        }
    }

    /**
     * @psalm-param 'demo collection'|'zx chip'|'zx tunes' $term
     *
     * @psalm-return list<mixed>
     */
    private function loadIds(string $term): array
    {
        $result = $this->db->table(ProdsRepository::TABLE)
            ->orderBy('id')
            ->where('title', 'like', '%' . $term . '%')
            ->get(['id']);
        return array_column($result, 'id');

    }

    private function fixZxChip(): void
    {
        $ids = $this->loadIds('zx chip');
        $ids = array_merge($ids, $this->loadIds('zx tunes'));
        if ($ids) {
            foreach ($ids as $id) {
                /**
                 * @var zxProdElement $prod
                 */
                $prod = $this->structureManager->getElementById($id);
                if ($prod) {
                    $releases = $prod->getReleasesList();
                    /**
                     * @var zxReleaseElement $release
                     */
                    foreach ($releases as $key => $release) {
                        copy($release->getFilePath(), ROOT_PATH . 'temporary/zxchip/' . $release->fileName);
                    }
                    $string = $prod->getImportOriginId('zxdb') . ' ';
                    $string .= $prod->getImportOriginId('3a') . ' ';
                    $string .= $prod->title . ' ';

                    $string .= "\n";
                    file_put_contents($this->log, $string, FILE_APPEND);
                    file_put_contents($this->idLog, $prod->getImportOriginId('zxdb') . ',', FILE_APPEND);
                    echo $string . '<br>';
                    flush();
                    $prod->deleteElementData();

                } else {
                    echo 'failed prod ' . $id . '<br>';
                }
            }
        }
    }

    private function fixWlodek(): void
    {
        $ids = $this->loadIds('demo collection');
        if ($ids) {
            foreach ($ids as $id) {
                /**
                 * @var zxProdElement $prod
                 */
                $prod = $this->structureManager->getElementById($id);
                if ($prod) {
                    $releases = $prod->getReleasesList();
                    /**
                     * @var zxReleaseElement $release
                     */
                    foreach ($releases as $key => $release) {
                        copy($release->getFilePath(), ROOT_PATH . 'temporary/wlodek/' . $release->fileName);
                    }
                    $string = $prod->getImportOriginId('zxdb') . ' ';
                    $string .= $prod->getImportOriginId('3a') . ' ';
                    $string .= $prod->title . ' ';

                    $string .= "\n";
                    file_put_contents($this->log, $string, FILE_APPEND);
                    file_put_contents($this->idLog, $prod->getImportOriginId('zxdb') . ',', FILE_APPEND);
                    echo $string . '<br>';
                    flush();
                    $prod->deleteElementData();

                } else {
                    echo 'failed prod ' . $id . '<br>';
                }
            }
        }
    }

    public
    function getUrlName()
    {
        return '';
    }
}



