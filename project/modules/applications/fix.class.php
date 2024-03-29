<?php

use Illuminate\Database\Capsule\Manager;

class fixApplication extends controllerApplication
{
    protected $applicationName = 'fix';
    public $rendererName = 'smarty';
    protected $structureManager;
    private $log = PUBLIC_PATH . 'zxChip.log';
    private $idLog = PUBLIC_PATH . 'zxChipIds.log';

    /**
     * @return void
     */
    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        exit;
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
            $this->fixReleases();
//            $this->deleteProds();
//            $this->fixPress();
//            $this->deletePress();
//            $this->fixProds();
//            $this->fixZxChip();
//            $this->fixWlodek();
        }
    }

    private function fixProds(): void
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $result = $db->table('module_zxprod')
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
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $result = $db->table('structure_elements')
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
                            if ($zip->open($filePath) === TRUE) {
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
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        /**
         * @var linksManager $linksManager
         */
        $result = $db->table('module_zxprod')
            ->where('title', 'like', 'Erotic #%')
            ->orderBy('id')
            ->get(['id']);
        $ids = array_column($result, 'id');
        $count = count($ids);
        $counter = 0;
        foreach ($ids as $id) {
            $prod = $this->structureManager->getElementById($id);
            if (!$prod) {
                echo 'failed ' . $id;
            }
            $split = explode('#', $prod->title);

            $result = $db->table('module_zxprod')
                ->where('title', 'like', 'Erotic ' . $split[1])
                ->orWhere('title', 'like', 'Erotic ' . (int)$split[1])
                ->orderBy('id')
                ->first(['id']);

            $prod2 = $this->structureManager->getElementById($result['id']);
            if ($prod2) {
                $prod2->title = $prod->title;

                $prodsManager->joinDeleteZxProd($prod2->id, $prod->id, false);
            }

            echo $counter . ' ' . round(100 * $counter / $count) . '% ';
            if ($prod) {
                echo 'fixed' . $prod->title . ' ' . $prod2->title . '<br>';
            }
            $counter++;
            flush();
        }
    }


    private function deletePress(): void
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $result = $db->table('module_pressarticle')
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
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        /**
         * @var linksManager $linksManager
         */
        $linksManager = $this->getService(linksManager::class);
        $result = $db->table('module_zxprod')
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
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $result = $db->table('module_zxprod')
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