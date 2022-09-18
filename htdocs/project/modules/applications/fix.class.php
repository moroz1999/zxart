<?php

use Illuminate\Database\Capsule\Manager;

class fixApplication extends controllerApplication
{
    protected $applicationName = 'fix';
    public $rendererName = 'smarty';
    protected $structureManager;
    private $log = ROOT_PATH . 'zxChip.log';
    private $idLog = ROOT_PATH . 'zxChipIds.log';

    public function initialize()
    {
        $this->createRenderer();
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
            $this->fixZxChip();
        }
    }


    private function loadIds($term)
    {
        /**
         * @var \Illuminate\Database\Connection $db
         */
        $db = $this->getService('db');
        $result = $db->table('module_zxprod')
            ->orderBy('id')
            ->where('title', 'like', '%'.$term.'%')
            ->get(['id']);
        return array_column($result, 'id');

    }

    private function fixZxChip()
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

    public function getUrlName()
    {
        return '';
    }
}