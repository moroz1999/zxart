<?php

class zxnetApplication extends controllerApplication
{
    protected $applicationName = 'zxnet';
    public $rendererName = 'smarty';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
            ],
            true
        );
        $languagesManager = $this->getService('LanguagesManager');
        if ($controller->getParameter('l')) {
            $languagesManager->setCurrentLanguageCode($controller->getParameter('l'));
        }
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);

        $action = $controller->getParameter('a');
        $search = $controller->getParameter('s');
        $output = $controller->getParameter('o');
        $format = $controller->getParameter('f');

        if ($page = $controller->getParameter('p')) {
            $page = intval($page) - 1;
        } else {
            $page = 0;
        }
        if ($pageAmount = $controller->getParameter('l')) {
            $pageAmount = intval($pageAmount);
        }

        if ($pageAmount < 1 || $pageAmount > 1000) {
            $pageAmount = 100;
        }

        $queryParameters = [];
        $exportType = false;
        $resultTypes = [];
        $sorting = [];
        if ($action == 'm') {
            $resultTypes = ['zxMusic'];
            $exportType = 'zxMusic';

            if ($output == 'r') {
                $sorting = ['votes' => 'desc'];
            } elseif ($output == 'p') {
                $sorting = ['plays' => 'desc'];
            } elseif ($output == 'w') {
                $sorting = ['place' => 'asc', 'votes' => 'desc'];
                $queryParameters['zxMusicMinPartyPlace'] = 3;
            } elseif ($output == 'y') {
                $currentYear = date('Y');
                $currentMonth = date('m');

                if ($currentMonth < 3) {
                    $queryParameters['zxMusicYear'] = [$currentYear - 1, $currentYear];
                } else {
                    $queryParameters['zxMusicYear'] = [$currentYear];
                }
                $sorting = ['votes' => 'desc'];
            } else {
                $sorting = ['date' => 'desc'];
            }

            if (!$format) {
                $queryParameters['zxMusicFormat'] = ['pt2', 'pt3'];
            } else {
                $queryParameters['zxMusicFormat'] = [$format];
            }

            if ($search) {
                $queryParameters['zxMusicSearch'] = $search;
            }
        }
        if (!$action || $action == 'g') {
            $resultTypes = ['zxPicture'];
            $exportType = 'zxPicture';

            $typeParameter = $controller->getParameter('t');
            if ($typeParameter == 'giga') {
                $pictureType = ['gigascreen', 'mg8'];
            } elseif ($typeParameter == 'color') {
                $pictureType = ['flash', 'gigascreen', 'mg8', 'sam4', 'lowresgs', 'ulaplus', 'stellar', 'nxi'];
            } elseif ($typeParameter == 'multi') {
                $pictureType = ['multicolor', 'multicolor4', 'timex81', 'bmc4', 'mc', 'mlt', 'mg1', 'mg2', 'mg4'];
            } elseif ($typeParameter == 'pixel') {
                $pictureType = ['tricolor', 'sam4', 'zxevo', 'sxg', 'nxi'];
            } elseif ($typeParameter == 'lowres') {
                $pictureType = ['attributes', 'lowresgs', 'stellar'];
            } else {
                $pictureType = 'standard';
            }

            if ($output == 'r') {
                $sorting = ['votes' => 'desc'];
            } elseif ($output == 'p') {
                $sorting = ['views' => 'desc'];
            } elseif ($output == 'w') {
                $sorting = ['place' => 'asc', 'votes' => 'desc'];
                $queryParameters['zxPictureMinPartyPlace'] = 3;
            } elseif ($output == 'y') {
                $currentYear = date('Y');
                $currentMonth = date('m');

                if ($currentMonth < 3) {
                    $queryParameters['zxPictureYear'] = [$currentYear - 1, $currentYear];
                } else {
                    $queryParameters['zxPictureYear'] = [$currentYear];
                }
                $sorting = ['votes' => 'desc'];
            } else {
                $sorting = ['date' => 'desc'];
            }
            $queryParameters['zxPictureType'] = $pictureType;

            if ($search) {
                $queryParameters1 = $queryParameters;
                $queryParameters1['zxPictureSearch'] = $search;
                $queryParameters2 = $queryParameters;
                $queryParameters2['authorSearch'] = $search;
                $queryParameters = [$queryParameters1, $queryParameters2];
            } else {
                $queryParameters = [$queryParameters];
            }
        }
        $start = $page * $pageAmount;
        $result = [];

        if ($resultTypes) {
            $apiQueriesManager = $this->getService('ApiQueriesManager');
            if ($apiQuery = $apiQueriesManager->getQuery()) {
                $apiQuery->setOptimized(true);
                $apiQuery->setFiltrationParameters($queryParameters);
                $apiQuery->setExportType($exportType); // objects to output
                $apiQuery->setLimit($pageAmount);
                $apiQuery->setOrder($sorting);
                $apiQuery->setStart($start);
                $apiQuery->setResultTypes($resultTypes); // object types to query

                $result = $apiQuery->getQueryResult();
            }
        }
        header('Content-type: text/plain; charset=UTF-8');
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            echo $this->generateText($result, $action);
        }
    }

    protected function generateText($data, $action)
    {
        $response = "";
        if ($action == 'm') {
            $number = $data['start'];
            foreach ($data['zxMusic'] as $zxMusicElement) {
                $response .= html_entity_decode($number . '.' . $zxMusicElement->title, ENT_QUOTES) . "\n";
                $response .= "http://zxart.ee/file/id:" . $zxMusicElement->id . "/filename:" . $zxMusicElement->id . $zxMusicElement->getFileExtension(
                        'original'
                    ) . "\n";
                $response .= $zxMusicElement->year . "\n";
                $response .= html_entity_decode($zxMusicElement->getAuthorNamesString(), ENT_QUOTES) . "\n";
                $cities = [];
                foreach ($zxMusicElement->getAuthorsList() as $author) {
                    if ($city = $author->getCityTitle()) {
                        $cities[] = $city;
                    }
                }
                $response .= html_entity_decode(implode(', ', $cities), ENT_QUOTES) . "\n";
                $number++;
            }
        } elseif ($action == 'g') {
            $number = $data['start'];
            foreach ($data['zxPicture'] as $zxPictureElement) {
                $response .= html_entity_decode($number . '.' . $zxPictureElement->title, ENT_QUOTES) . "\n";
                if ($zxPictureElement->type == 'standard' || $zxPictureElement->type == 'sxg') {
                    $response .= "http://zxart.ee/file/id:" . $zxPictureElement->id . "/filename:" . $zxPictureElement->id . $zxPictureElement->getFileExtension(
                            'original'
                        ) . "\n";
                } else {
                    $response .= "http://zxart.ee/sxg/id:" . $zxPictureElement->id . "/filename:" . $zxPictureElement->id . ".sxg\n";
                }
                $response .= $zxPictureElement->year . "\n";
                $response .= html_entity_decode($zxPictureElement->getAuthorNamesString(), ENT_QUOTES) . "\n";
                $cities = [];
                foreach ($zxPictureElement->getAuthorsList() as $author) {
                    if ($city = $author->getCityTitle()) {
                        $cities[] = $city;
                    }
                }
                $response .= html_entity_decode(implode(', ', $cities), ENT_QUOTES) . "\n";
                $number++;
            }
        }
        $response = TranslitHelper::convert($response);
        return $response;
    }

    public function getUrlName()
    {
        return '';
    }
}

