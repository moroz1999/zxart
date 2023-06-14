<?php

class randomTuneApplication extends controllerApplication
{
    protected $applicationName = 'randomTune';
    public $rendererName = 'json';
    public $requestParameters = [
        'type',
    ];

    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService('Cache');
        $cache->enable();

        $this->renderer->assign('responseData', []);

        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
            ],
            true
        );

        $languagesManager = $this->getService('LanguagesManager');
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
        $randomElement = false;
        /**
         * @var ApiQueriesManager $apiQueriesManager
         */
        $apiQueriesManager = $this->getService('ApiQueriesManager');
        $averageVote = $this->getService('ConfigManager')->get('zx.averageVote');
        $order = ['votes' => 'rand'];
        $parameters = [];
        $type = $controller->getParameter('type');
        if ($type == 'randomgood') {
            $parameters = [
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'games') {
            $parameters = [
                'zxMusicGame' => true,
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'demoscene') {
            $parameters = [
                'zxMusicMinPartyPlace' => 1000,
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'ay') {
            $parameters = [
                'zxMusicFormatGroup' => ['ay', 'aycovox', 'aydigitalay', 'ts'],
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'beeper') {
            $parameters = [
                'zxMusicFormatGroup' => ['beeper', 'aybeeper'],
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'exotic') {
            $parameters = [
                'zxMusicFormatGroup' => ['digitalbeeper', 'tsfm', 'fm', 'digitalay', 'saa'],
                'zxMusicMinRating' => $averageVote + 0.2,
            ];
        } elseif ($type == 'discover') {
            $currentUser = $this->getService('user');

            $parameters = [
                'zxMusicNotVotedBy' => [$currentUser->id],
                'zxMusicBestVotes' => 100,
            ];
        } elseif ($type == 'underground') {
            $parameters = [
                'zxMusicLessListened' => 1000,
                'zxMusicBestVotes' => 500,
            ];
        } elseif ($type == 'lastyear') {
            $currentYear = date('Y');
            $currentMonth = date('m');

            if ($currentMonth < 3) {
                $parameters = [
                    'zxMusicYear' => [$currentYear - 1, $currentYear],
                    'zxMusicMinRating' => $averageVote + 0.2,
                ];
            } else {
                $parameters = [
                    'zxMusicYear' => [$currentYear],
                    'zxMusicMinRating' => $averageVote + 0.2,
                ];
            }
        }
        $parameters['zxMusicPlayable'] = true;
        $query = $apiQueriesManager->getQuery();
        $query->setFiltrationParameters($parameters);
        $query->setExportType('zxMusic');
        $query->setOrder($order);
        $query->setStart(0);
        $query->setLimit(1);
        if ($result = $query->getQueryResult()) {
            $randomElement = reset($result['zxMusic']);
        }


        if ($randomElement) {
            $renderer = $this->getService('renderer');
            if ($renderer instanceof rendererPluginAppendInterface) {
                $renderer->appendResponseData('zxMusic', $randomElement->getElementData());
            }

            if ($this->renderer->getAttribute('responseStatus') === false) {
                $this->renderer->assign('responseStatus', 'success');
            }
            $this->renderer->display();
        } else {
            $this->renderer->assign('responseStatus', 'fail');
            $this->renderer->fileNotFound();
        }
    }

    public function getUrlName()
    {
        return '';
    }
}
