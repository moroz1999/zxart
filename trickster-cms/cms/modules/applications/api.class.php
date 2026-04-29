<?php

class apiApplication extends controllerApplication
{
    use DbLoggableApplication;

    protected $applicationName = 'api';
    protected $mode = 'public';
    public $rendererName = 'json';

    public function initialize()
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('mode')) {
            $mode = $controller->getParameter('mode');
            if ($mode == 'admin') {
                $this->mode = 'admin';
            } else {
                $this->mode = 'public';
            }
        }
        $configManager = $controller->getConfigManager();
        if ($this->mode == 'admin') {
            $this->startSession($this->mode, $configManager->get('main.adminSessionLifeTime'));
        }
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->startDbLogging();

        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(true, false, false);

        $currentElement = false;

        if ($this->mode !== 'admin') {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST");
            header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

            $structureManager = $this->getService('publicStructureManager');
            $languagesManager = $this->getService(LanguagesManager::class);
            if ($controller->requestedPath) {
                $currentElement = $structureManager->getCurrentElement();
            } elseif ($controller->getParameter('language')) {
                $languagesManager->setCurrentLanguageCode($controller->getParameter('language'));
                $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
                $structureManager->getElementByPath([$languagesManager->getCurrentLanguageCode()]);
            }
        }

        $preset = 'api';
        if ($controller->getParameter('preset')) {
            $preset = $controller->getParameter('preset');
        }

        $response = new ajaxResponse();
        $response->setPreset($preset);

        $status = 'fail';

        if ($currentElement) {
            $status = 'success';

            $response->setResponseElements($currentElement->structureType, [$currentElement]);

            $this->renderer->assign("totalAmount", 1);
            $this->renderer->assign("start", 0);
            $this->renderer->assign("limit", 1);
        } else {
            /** @var ApiQueriesManager $apiQueriesManager * */
            $apiQueriesManager = $this->getService(ApiQueriesManager::class);
            $uri = $controller->getParametersString();

            if ($apiQuery = $apiQueriesManager->getQueryFromString($uri)) {
                $status = 'success';

                if ($result = $apiQuery->getQueryResult()) {
                    foreach ($apiQuery->getResultTypes() as $type) {
                        $response->setResponseElements($type, $result[$type]);
                    }
                }
                $this->renderer->assign("totalAmount", $result['totalAmount']);
                $this->renderer->assign("start", $result['start']);
                $this->renderer->assign("limit", $result['limit']);
            }
        }

        $this->renderer->assign('responseData', $response->responseData);
        $this->renderer->assign('responseStatus', $status);

        $this->renderer->setCacheControl('no-cache');
        $this->renderer->display();
        $this->saveDbLog();
    }

    public function getUrlName()
    {
        if ($this->mode === 'admin') {
            return 'admin';
        }

        return '';
    }
}