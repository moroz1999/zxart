<?php

class zxtuneApplication extends controllerApplication
{
    protected $applicationName = 'zxtune';
    protected $mode = 'public';
    public $rendererName = 'smarty';
    /**
     * @var ConfigManager
     */
    protected $configManager;

    public $requestParameters = [
        'action',
        'types',
        'page',
        'query',
        'mode',
        'resultsLimit',
        'filters',
    ];

    /**
     * @return void
     */
    public function initialize()
    {
        $this->mode = 'public';
        $this->configManager = $this->getService(ConfigManager::class);

        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->configManager->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService(LanguagesManager::class);
        if ($controller->getParameter('language')) {
            $languagesManager->setCurrentLanguageCode($controller->getParameter('language'));
        }
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $action = null;
        if ($controller->getParameter('action')) {
            $action = $controller->getParameter('action');
        }

        $parameters = $controller->getParameters();
        $resultTypes = null;
        $queryParameters = null;
        $exportType = false;
        $order = false;

        if (isset($parameters['start'])) {
            $limitStart = intval($parameters['start']);
        } else {
            $limitStart = 0;
        }

        if (isset($parameters['limit'])) {
            $limitQuantity = intval($parameters['limit']);
        } else {
            $limitQuantity = 5000;
        }

        if ($action == 'authors') {
            $resultTypes = ['author'];
            $exportType = 'author';
            $queryParameters = [
                'authorOfItemType' => 'authorMusic',
            ];
            if (isset($parameters['authorId'])) {
                $queryParameters = [
                    'authorId' => $parameters['authorId'],
                ];
            }
            $order = ['title' => 'asc'];
        } elseif ($action == 'parties') {
            $resultTypes = ['party'];
            $exportType = 'party';

            $order = ['title' => 'asc'];

            $queryParameters = [
                'partyOfItemType' => 'partyMusic',
            ];
            if (isset($parameters['partyId'])) {
                $queryParameters['partyId'] = $parameters['partyId'];
            }
        } elseif ($action == 'tunes') {
            $resultTypes = ['zxMusic'];
            $exportType = 'zxMusic';

            $queryParameters = [
                'zxMusicPlayable' => true,
            ];

            if (isset($parameters['authorId'])) {
                $queryParameters['authorId'] = $parameters['authorId'];
            }
            if (isset($parameters['partyId'])) {
                $queryParameters['partyId'] = $parameters['partyId'];
            }
            if (isset($parameters['tuneId'])) {
                $queryParameters['zxMusicId'] = $parameters['tuneId'];
            }
            $order = ['title' => 'asc'];
        } elseif ($action == 'search') {
            $resultTypes = ['zxMusic'];
            $exportType = 'zxMusic';
            $queryParameters = [
                'zxMusicPlayable' => true,
            ];
            if (isset($parameters['query'])) {
                $queryParameters['zxMusicSearch'] = $parameters['query'];
            }
            $order = ['title' => 'asc'];
        } elseif ($action == 'topTunes') {
            $resultTypes = ['zxMusic'];
            $exportType = 'zxMusic';

            $queryParameters = [
                'zxMusicPlayable' => true,
            ];

            $order = ['votes' => 'desc'];
        } elseif ($action == 'formats') {
            $resultTypes = ['zxMusic'];
            $exportType = 'zxMusic';
            $queryParameters = [
                'zxMusicPlayable' => true,
            ];

            if (isset($parameters['formatType'])) {
                $queryParameters['zxMusicFormatGroup'] = $parameters['formatType'];
            }

            $order = ['votes' => 'desc'];
        } elseif ($action == 'play') {
            if (isset($parameters['tuneId'])) {
                if ($element = $structureManager->getElementById((int)$parameters['tuneId'])) {
                    $controller->redirect($element->getUrl() . 'autoplay:1/', 302);
                    exit;
                }
            }

            $order = ['votes' => 'desc'];
        }

        $status = 'fail';
        if ($resultTypes && $queryParameters) {
            $apiQueriesManager = $this->getService(ApiQueriesManager::class);
            $result = [];
            if ($apiQuery = $apiQueriesManager->getQuery()) {
                $apiQuery->setFiltrationParameters($queryParameters);
                $apiQuery->setExportType($exportType); // objects to output
                $apiQuery->setLimit($limitQuantity);
                $apiQuery->setOrder($order);
                $apiQuery->setStart($limitStart);
                $apiQuery->setResultTypes($resultTypes); // object types to query

                if ($result = $apiQuery->getQueryResult()) {
                    $status = 'success';
                }
            }

            $this->renderer->assign('responseData', $result);
        }
        $this->renderer->assign('responseStatus', $status);
        $this->renderer->setContentDisposition('inline');
        $this->renderer->setContentType('application/xml');
        $this->renderer->setCacheControl('no-cache');
        $this->renderer->template = $controller->getProjectPath() . 'templates/zxtune/index.tpl';

        $this->renderer->display();
    }

    public function getUrlName()
    {
        return '';
    }
}

