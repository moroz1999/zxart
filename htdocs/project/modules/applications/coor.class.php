<?php

use Illuminate\Database\Capsule\Manager;

class coorApplication extends controllerApplication
{
    protected $applicationName = 'coor';
    public $rendererName = 'smarty';
    /**
     * @var structureManager
     */
    protected $structureManager;
    protected $key;

    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        ini_set("memory_limit", "2048M");
        ini_set("max_execution_time", 60 * 60 * 5);
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();
        if (!($language = $controller->getParameter('lang'))) {
            $language = 'eng';
        }
        $configManager = $this->getService('ConfigManager');
        $this->key = $configManager->get('main.ga_key');
        /**
         * @var languagesManager $languagesManager
         */
        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode($language, $configManager->get('main.rootMarkerAdmin'));
        $this->structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $configManager->get('main.rootMarkerAdmin'),
            ],
            true
        );

        if ($countries = $this->structureManager->getElementsByType('country')) {
            foreach ($countries as $counter => $country) {
                if ($country->latitude == '0.00') {
                    if ($record = $this->queryGooglePlacesApi($country->getValue('title', 2105), '')) {
                        $country->latitude = $record[0];
                        $country->longitude = $record[1];
                        $country->persistElementData();
                        echo $counter . ' success ' . $country->title;
                    } else {
                        echo $counter . ' fail ' . $country->title;
                    }
                    echo "<br/>";
                    flush();
                }
            }
        }
        if ($cities = $this->structureManager->getElementsByType('city')) {
            foreach ($cities as $counter => $city) {
                if (!$city->getValue('title', 84102) || ($city->getValue('title', 930) == $city->getValue(
                            'title',
                            84102
                        ))) {
                    $city->setValue('title', $city->getValue('title', 2105), 84102);
                    $city->persistElementData();
                }

                if ($city->latitude == '0.00') {
                    if ($country = $city->getFirstParentElement()) {
                        if ($record = $this->queryGooglePlacesApi(
                            $country->getValue('title', 2105),
                            $city->getValue('title', 2105)
                        )) {
                            $city->latitude = $record[0];
                            $city->longitude = $record[1];
                            $city->persistElementData();
                            echo $counter . ' success ' . $country->title . ' ' . $city->title;
                        } else {
                            echo $counter . ' fail ' . $country->title . ' ' . $city->title;
                        }
                        echo "<br/>";
                        flush();
                    }
                }
            }
        }
    }

    public function getUrlName()
    {
        return '';
    }

    protected function queryGooglePlacesApi($country, $city)
    {
        $coordinates = false;
        $queryString = $country . ', ';
        $queryString .= $city;

        $requestString = 'https://maps.google.com/maps/api/geocode/json?address=' . urlencode(
                $queryString
            ) . '&sensor=false&key=' . $this->key;
        if (function_exists('curl_init')) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);

            curl_setopt($curl, CURLOPT_URL, $requestString);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);


            $response = curl_exec($curl);

            if ($response !== false) {
                if ($data = json_decode($response, true)) {
                    if (isset($data['results'])) {
                        if ($firstResponse = reset($data['results'])) {
                            if (isset($firstResponse['geometry']['location'])) {
                                if (($lat = $firstResponse['geometry']['location']['lat']) && ($lng = $firstResponse['geometry']['location']['lng'])) {
                                    $coordinates = [$lat, $lng];
                                }
                            }
                        }
                    }
                }
            }
            curl_close($curl);
        }

        return $coordinates;
    }
}