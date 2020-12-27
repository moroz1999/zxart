<?php

use Illuminate\Database\Capsule\Manager;

class translateAuthorsApplication extends controllerApplication
{
    protected $applicationName = 'translateAuthors';
    public $rendererName = 'smarty';
    protected $structureManager;

    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
    }

//trnsl.1.1.20200503T165357Z.60ddacfa86209235.bbd3d40e736251c63fd736ee2e10b0120b7215ff
    public function execute($controller)
    {
        exit;
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 60 * 60 * 5);
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();
        if (!($language = $controller->getParameter('lang'))) {
            $language = 'eng';
        }

        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode($language);
        $configManager = $this->getService('ConfigManager');
        $this->structureManager = $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
//        $groupsManager = $this->getService('GroupsManager');

//        $currentElement = $structureManager->getCurrentElement();

        $db = $this->getService('db');
        $counter = 0;
        if ($lettersMenu = $structureManager->getElementByMarker(
            'authorsmenu',
            $languagesManager->getCurrentLanguageId()
        )) {
            if ($letters = $structureManager->getElementsChildren($lettersMenu->id, 'container', [])) {
                foreach ($letters as $letter) {
                    if ($authors = $letter->getAuthorsList()) {
                        foreach ($authors as $author) {
                            $query = $db->table('module_group')->where('title', 'like', $author->title . '%')->select(
                                ['id', 'title']
                            )->distinct();
                            if ($records = $query->get()) {
                                echo $counter . ' <a href="' . $author->URL . '">' . $author->title . '</a> ';
                                foreach ($records as $record) {
                                    if ($group = $structureManager->getElementById($record['id'])) {
                                        echo ' <a href="' . $group->URL . '">' . $group->title . '</a> ';
                                    }
                                }
                                echo '<br>';
                                $counter++;
                                flush();
                                if ($counter > 30) {
                                    exit;
                                }
                            }
                        }
                    }
                    break;
                }
            }
        }
    }

    protected function makeZxdb()
    {
        if ($this->zxdb === null) {
            $zxdbConfig = $this->getService('ConfigManager')->getConfig('zxdb');

            $manager = new Illuminate\Database\Capsule\Manager();
            $manager->addConnection(
                [
                    'driver' => 'mysql',
                    'host' => $zxdbConfig->get('mysqlHost'),
                    'database' => $zxdbConfig->get('mysqlDatabase'),
                    'username' => $zxdbConfig->get('mysqlUser'),
                    'password' => $zxdbConfig->get('mysqlPassword'),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ],
                'zxdb'
            );
            $manager->setFetchMode(PDO::FETCH_ASSOC);
            $this->zxdb = $manager->getConnection('zxdb');
        }
    }

    public function getUrlName()
    {
        return '';
    }
}