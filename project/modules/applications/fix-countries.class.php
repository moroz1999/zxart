<?php
//
//use Illuminate\Database\Capsule\Manager;
//
//class fixApplication extends controllerApplication
//{
//    protected $applicationName = 'fix';
//    public $rendererName = 'smarty';
//    protected $structureManager;
//    private $path = PUBLIC_PATH . 'fixcountries.txt';
//    private $log = PUBLIC_PATH . 'fixcountries.log';
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
//            $this->fixCountries();
//        }
//    }
//
//
//    private function loadIds()
//    {
//
//        $lastId = $this->getLastId();
//
//        /**
//         * @var \Illuminate\Database\Connection $db
//         */
//        $db = $this->getService('db');
//        $result = $db->table('module_author')
//            ->orderBy('id')
//            ->where('id', '>', $lastId)
//            ->distinct()
//            ->get(['id']);
//        return array_column($result, 'id');
//
//    }
//
//    private function getLastId()
//    {
//        if (is_file($this->path)) {
//            return (int)file_get_contents($this->path);
//        }
//        return 0;
//    }
//
//    private function fixCountries()
//    {
//        $ids = $this->loadIds();
//        if ($ids) {
//            foreach ($ids as $id) {
//                /**
//                 * @var authorElement $author
//                 */
//                $author = $this->structureManager->getElementById($id);
//                if ($author) {
//                    $old = $author->getCountryTitle();
//                    if (!$author->checkCountry()) {
//                        $string = $author->getImportOriginId('zxdb') . ' ';
//                        $string .= $author->title . ' ';
//                        $string .= $author->realName . ' ';
//                        $string .= $old . ' ';
//
//                        $author->persistElementData();
//
//                        $string .= $author->getCountryTitle() . ' ';
//                        $string .= $author->getCityTitle() . ' ';
//                        $string .= "\n";
//                        file_put_contents($this->log, $string, FILE_APPEND);
//                        echo $string . '<br>';
//                        flush();
//                    }
//                } else {
//                    echo 'failed author ' . $id . '<br>';
//                }
//                file_put_contents($this->path, $id);
//            }
//        }
//    }
//
//    public function getUrlName()
//    {
//        return '';
//    }
//}