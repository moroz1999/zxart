<?php

class routeApplication extends controllerApplication
{
    protected $applicationName = 'route';
    public $rendererName = 'smarty';
    public $requestParameters = [];

    public function initialize()
    {
        set_time_limit(60 * 60);
        $this->startSession('route');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $renderer = $this->getService('renderer');
        $renderer->endOutputBuffering();

        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
            ],
            true
        );
        if (!($language = $controller->getParameter('lang'))) {
            $language = 'eng';
        }

        $languagesManager = $this->getService('LanguagesManager');
        $languagesManager->setCurrentLanguageCode($language);

        if ($importId = (int)$controller->getParameter('importId')) {
            if ($importOrigin = $controller->getParameter('importOrigin')) {
                if ($type = $controller->getParameter('type')) {
                    $db = $this->getService('db');
                    if ($elementId = $db->table('import_origin')
                        ->select('elementId')
                        ->where('importId', '=', $importId)
                        ->where('importOrigin', '=', $importOrigin)
                        ->where('type', '=', $type)
                        ->limit(1)->value('elementId')
                    ) {
                        if ($element = $structureManager->getElementById($elementId)) {
                            $controller->redirect($element->getUrl());
                        }
                    }
                }
            }
        }
        if ($id = (int)$controller->getParameter('id')){
            if ($element = $structureManager->getElementById($id)) {
                $controller->redirect($element->getUrl());
            }
        }
    }

    public function getUrlName()
    {
        return '';
    }
}

