<?php
//
//use Illuminate\Database\Capsule\Manager;
//
//class fixPlusApplication extends controllerApplication
//{
//    protected $applicationName = 'fixPlus';
//    public $rendererName = 'smarty';
//    protected $structureManager;
//    private $path = ROOT_PATH . 'fixcountries.txt';
//    private $log = ROOT_PATH . 'fixcountries.log';
//
//    public function initialize()
//    {
//        $this->createRenderer();
//    }
//
//    public function execute($controller)
//    {
//        ini_set("memory_limit", "2048M");
//        ini_set("max_execution_time", 60);
//        $renderer = $this->getService('renderer');
//        $renderer->endOutputBuffering();
//
//        $user = $this->getService('user');
//        if ($userId = $user->checkUser('crontab', null, true)) {
//            $user->switchUser($userId);
//
//            $this->structureManager = $this->getService(
//                'structureManager',
//                ['rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin')]
//            );
//            /**
//             * @var LanguagesManager $languagesManager
//             */
//            $languagesManager = $this->getService('LanguagesManager');
//            $languagesManager->setCurrentLanguageCode('eng');
//            $this->fixPlus();
//        }
//    }
//
//
//    private function loadIds()
//    {
//        /**
//         * @var \Illuminate\Database\Connection $db
//         */
//        $db = $this->getService('db');
//        $result = $db->table('module_zxprod')
//            ->orderBy('id')
//            ->where('title', 'like', '%+%')
//            ->distinct()
//            ->get(['id']);
//        return array_column($result, 'id');
//    }
//
//    private function fixPlus()
//    {
//        $ids = $this->loadIds();
//        if ($ids) {
//            foreach ($ids as $id) {
//                /**
//                 * @var zxProdElement $prod
//                 */
//                $prod = $this->structureManager->getElementById($id);
//                if ($prod) {
//                    foreach ($prod->getFileSelectorPropertyNames() as $propertyName) {
//                        $usedNames = [];
//                        foreach ($prod->getFilesList($propertyName) as $file) {
//                            $fileName = $file->getFileName();
//                            if (!isset($usedNames[$fileName])) {
//                                $usedNames[$fileName] = true;
//                            } else {
//                                echo 'deleted prod screen ' . $fileName . '<br>';
//                                $file->deleteElementData();
//                            }
//                        }
//                    }
//                    foreach ($prod->getReleasesList() as $releaseElement) {
//                        foreach ($releaseElement->getFileSelectorPropertyNames() as $propertyName) {
//                            $usedNames = [];
//                            foreach ($releaseElement->getFilesList($propertyName) as $file) {
//                                $fileName = $file->getFileName();
//                                if (!isset($usedNames[$fileName])) {
//                                    $usedNames[$fileName] = true;
//                                } else {
//                                    echo 'deleted prod screen ' . $fileName . '<br>';
//                                    $file->deleteElementData();
//                                }
//                            }
//                        }
//                    }
//                } else {
//                    echo 'failed prod ' . $id . '<br>';
//                }
//                file_put_contents($this->path, $id);
////                exit;
//            }
//        }
//    }
//
//    public function getUrlName()
//    {
//        return '';
//    }
//}