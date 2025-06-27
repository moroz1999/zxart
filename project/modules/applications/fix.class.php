<?php

use Illuminate\Database\Connection;
use ZxArt\Authors\Constants;
use ZxArt\LinkTypes;
use ZxArt\Prods\Repositories\ProdsRepository;
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
    private const CATEGORY_MAGAZINE = 92179;
    private const CATEGORY_NEWSPAPER = 92182;

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
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->structureManager = $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')],
            );
            /**
             * @var LanguagesManager $languagesManager
             */
            $languagesManager = $this->getService('LanguagesManager');
            $languagesManager->setCurrentLanguageCode('eng');
            $this->fixDisconnectedImages();
//            $this->addCategoryToQueue(92183, QueueType::AI_SEO, QueueStatus::STATUS_TODO, 5000);
//            $this->addCategoryToQueue(92534, QueueType::AI_INTRO, QueueStatus::STATUS_TODO, 5000);
//            $this->addCategoryToQueue(204819, QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);
        }
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
        /**
         * @var linksManager $linksManager
         */
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
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $result = $this->db->table('module_zxprod')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            if ($counter < 35845) {
                $counter++;
                continue;
            }
            /**
             * @var zxProdElement $prod
             */
            $prod = $this->structureManager->getElementById($id);
            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                echo $prod->getId() . ' ' . $prod->getTitle() . '<br>';
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
                echo 'deleted missing image element ' . $prodImage->getId() . ' ' . $propertyName . '<br>';
                $prodImage->deleteElementData();
                continue;
            }

            $md5 = md5(file_get_contents($filePath));
            $existing = isset($md5s[$md5]);
            if ($existing) {
                echo 'deleted duplicated image element ' . $prodImage->getId() . ' ' . $propertyName . '<br>';
                $prodImage->deleteElementData();
                continue;
            }
            $md5s[$md5] = true;
        }
    }


    private function miscTemp(): void
    {
        /**
         * @var linksManager $linksManager
         */
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
        /**
         * @var linksManager $linksManager
         */
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
        $apiQueriesManager = $this->getService('ApiQueriesManager');
        $filters = ['zxReleaseHardware' => ["zx80",
            "zx8116",
            "zx811",
            "zx812",
            "zx8132",
            "zx8164",]];

        $apiQuery = $apiQueriesManager->getQuery()
            ->setExportType('zxRelease')
            ->setFiltrationParameters($filters);

        $dbQuery = $apiQuery->getExportFilteredQuery();
        $records = $dbQuery->get(['id']);
        $ids = array_column($records, 'id');
        $counter = 0;
        $count = count($ids);
        foreach ($ids as $id) {
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
        $apiQueriesManager = $this->getService('ApiQueriesManager');
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
        /**
         * @var linksManager $linksManager
         */
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
            ->where('dateCreated', '>', 1674768000)
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
                echo 'release missing ' . $id . '<br>';
            } else {
                $filePath = $release->getFilePath();
                $fileName = $release->getFileName();
                if ($filePath && $fileName) {
                    if (is_file($filePath)) {
                        if ((filesize($filePath) <= 20)) {
                            $delete = true;
                        }
                        if (pathinfo($fileName, PATHINFO_EXTENSION) === 'zip') {
                            $zip = new ZipArchive();
                            if ($zip->open($filePath) === true) {
                                $zip->close();
                            } else {
                                $delete = true;
                            }
                        }

                        if ($delete) {
                            echo 'delete ' . $release->getId() . ' ' . $release->getTitle() . '<br>';
                            $release->deleteElementData();
                        }
                    } else {
                        echo 'file missing <a href="/route/id:' . $release->getId() . '">' . $release->getTitle() . '</a> ' . $filePath . '<br>';
                    }
                }
            }
            $counter++;
            flush();
            if ($counter > 30000) {
                exit;
            }
        }
    }

    private function fixPress(): void
    {
        $prodsManager = $this->getService(ProdsService::class);

        /**
         * @var linksManager $linksManager
         */
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

                $prodsManager->joinDeleteZxProd($prod2->id, $prod->id, false);
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
        /**
         * @var linksManager $linksManager
         */
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
        /**
         * @var linksManager $linksManager
         */
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
        /**
         * @var linksManager $linksManager
         */
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

            $linksManager->unLinkElements(CategoryIds::MISC->value, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
            $linksManager->unLinkElements(self::CATEGORY_MAGAZINE, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
            $linksManager->linkElements(self::CATEGORY_NEWSPAPER, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);
//            $linksManager->linkElements(self::CATEGORY_MAGAZINE, $prod->id, LinkTypes::ZX_PROD_CATEGORY->value);

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
        /**
         * @var linksManager $linksManager
         */
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
        /**
         * @var linksManager $linksManager
         */
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