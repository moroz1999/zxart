<?php

class recalculateApplication extends controllerApplication
{
    protected $applicationName = 'admin';
    public $rendererName = 'smarty';
    protected $structureManager;
    public $requestParameters = [
        'id',
        'type',
        'action',
        'view',
        'start',
        'end',
        'types',
        'language',
        'categoryId',
        'number',
        'page',
        'plugin',
    ];

    public function initialize()
    {
        $this->startSession('admin');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", "7200");
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $user = $this->getService('user');
        if ($userId = $user->checkUser('crontab', null, true)) {
            $user->switchUser($userId);
            $this->structureManager = $structureManager = $this->getService(
                'structureManager',
                [
                    'rootUrl' => $controller->rootURL,
                    'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerAdmin'),
                ],
                true
            );

            foreach ($this->getAuthorsList() as $key => $author) {
                echo $key . ' ' . $author->id . ' ' . $author->title . ' start ' . memory_get_usage() . '<br/>';
                //                $author->recalculatePicturesData();
                //                $author->recalculateMusicData();
                $author->recalculateAuthorData();
                echo $author->graphicsRating . ' ' . $author->musicRating . '<br/>';
                flush();
//                exit;
            }
        }
    }

    /**
     * @return authorElement[]
     */
    protected function getAuthorsList()
    {
        $list = [];
        if ($authorsElement = $this->structureManager->getElementByMarker('authors')) {
            if ($letters = $authorsElement->getChildrenList()) {
                foreach ($letters as $letter) {
                    if ($authors = $letter->getChildrenList()) {
                        $list = array_merge($list, $authors);
                    }
                }
            }
        }
        return $list;
    }
}

