<?php

class jsonElementDataApplication extends controllerApplication
{
    use DbLoggableApplication;

    public $rendererName = 'json';
    protected $applicationName = 'jsonElementData';
    protected $mode = 'public';

    public function initialize()
    {
        $controller = controller::getInstance();
        $configManager = $controller->getConfigManager();
        $this->startSession($this->mode, $configManager->get('main.publicSessionLifeTime'));

        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->startDbLogging();
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable();

        $response = new ajaxResponse();
        $languagesManager = $this->getService(LanguagesManager::class);

        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->getService(ConfigManager::class)->get('main.rootMarkerPublic'),
                'configActions' => false,
            ],
            true
        );
        $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
        $status = 'fail';
        $preset = $controller->getParameter('preset');
        $baseElement = null;
        if ($id = $controller->getParameter('elementId')) {
            $baseElement = $structureManager->getElementById($id);
        } else {
            $baseElement = $structureManager->getCurrentElement();
        }

        if ($baseElement){
            if ($baseElement instanceof JsonDataProvider) {
                $response->setResponseData('elementData', $baseElement->getElementData($preset));
                $status = 'success';
            }
        }

        $this->renderer->assign('responseStatus', $status);
        $this->renderer->assign('responseData', $response->responseData);

        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
        }

        $this->renderer->setCacheControl('no-cache');
        $this->renderer->display();
        $this->saveDbLog();
    }

    public function getUrlName()
    {
        return '';
    }
}