<?php

use Illuminate\Database\Connection;
use ZxArt\Ai\TranslatorService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;

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
    private const CATEGORY_MISC = 92188;

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
        ini_set("max_execution_time", 60);
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);

            $this->structureManager = $this->getService(
                'structureManager',
                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
            );
            /**
             * @var LanguagesManager $languagesManager
             */
            $languagesManager = $this->getService('LanguagesManager');
            $languagesManager->setCurrentLanguageCode('eng');
//            $this->fixReleases();
//            $this->deleteProds();
//            $this->fixPress();
            $this->fixPressCategories();
//            $this->deletePress();
//            $this->fixProds();
//            $this->fixZxChip();
//            $this->fixWlodek();
//            $this->fixZx81();
//            $this->addCategoryToQueue(92505, QueueStatus::STATUS_TODO, null);
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

    private function addCategoryToQueue(int $categoryId, QueueStatus $status, ?int $limit = null): void
    {
        /**
         * @var zxProdCategoryElement $category
         */
        $category = $this->structureManager->getElementById($categoryId);
        $subcategoriesIds = [];
        $category->getSubCategoriesTreeIds($subcategoriesIds);
        $filters = ['zxProdCategory' => $subcategoriesIds];
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
            ->whereNotIn('id', function ($query) {
                $query->from('queue')
                    ->select('elementId')
                    ->where('type', '=', QueueType::AI_CATEGORIES_TAGS->value);
            })
            ->pluck('id');
        $records = array_map(function ($id) use ($status) {
            return [
                'elementId' => $id,
                'type' => QueueType::AI_CATEGORIES_TAGS->value,
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
                $linksManager->linkElements(418662, $id, 'zxProdCategory');
                echo 'fixed' . $id . '<br>';
            } else {
                echo 'exists' . $id . '<br>';
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
        $prodsManager = $this->getService('ProdsManager');

        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $name = 'Nicron';
        $replacement = 'Nicron issue';
        $result = $this->db->table('module_zxprod')
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

            $result = $this->db->table('module_zxprod')
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
                $linksManager->unLinkElements(self::CATEGORY_MISC, $prod->id, 'zxProdCategory');
                $linksManager->unLinkElements(self::CATEGORY_MAGAZINE, $prod->id, 'zxProdCategory');
                $linksManager->linkElements(self::CATEGORY_NEWSPAPER, $prod->id, 'zxProdCategory');
//            $linksManager->linkElements(self::CATEGORY_MAGAZINE, $prod->id, 'zxProdCategory');


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

    private function fixPressCategories(): void
    {
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $name = 'Nicron';
        $replacement = 'Nicron issue';
        $result = $this->db->table('module_zxprod')
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

            $linksManager->unLinkElements(self::CATEGORY_MISC, $prod->id, 'zxProdCategory');
            $linksManager->unLinkElements(self::CATEGORY_MAGAZINE, $prod->id, 'zxProdCategory');
            $linksManager->linkElements(self::CATEGORY_NEWSPAPER, $prod->id, 'zxProdCategory');
//            $linksManager->linkElements(self::CATEGORY_MAGAZINE, $prod->id, 'zxProdCategory');

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
        $result = $this->db->table('module_zxprod')
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
        $result = $this->db->table('module_zxprod')
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

    public function getUrlName()
    {
        return '';
    }
}