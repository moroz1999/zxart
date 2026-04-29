<?php

class ajaxApplication extends controllerApplication
{
    protected $applicationName = 'ajax';
    public $rendererName = 'json';

    public function initialize()
    {
        $this->startSession('public', $this->getService(ConfigManager::class)->get('main.publicSessionLifeTime'));
        $this->createRenderer();
    }

    public function execute($controller)
    {
        if (isset($_SERVER['HTTP_ORIGIN'])) {
            header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
            header('Access-Control-Allow-Credentials: true');
            header('Access-Control-Max-Age: 86400');    // cache for 1 day
        }
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
                header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
            }
            if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
                header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
            }
        } else {
            /**
             * @var Cache $cache
             */
            $cache = $this->getService(Cache::class);
            $cache->enable();

            $currentElement = false;
            $this->renderer->assign('responseData', []);

            if ($controller->getParameter('id')) {
                //todo: replace with $controller->rootURL and test.
                $structureManager = $this->getService('structureManager');

                $this->processRequestParameters();

                $languagesManager = $this->getService(LanguagesManager::class);
                $elementId = $controller->getParameter('id');

                if (is_numeric($elementId)) {
                    $currentElement = $structureManager->getElementById($elementId, $languagesManager->getCurrentLanguageId());
                } else {
                    $currentElement = $structureManager->getCurrentElement();
                }
            }
            if ($currentElement) {
                if ($this->renderer->getAttribute('responseStatus') === false) {
                    $this->renderer->assign('responseStatus', 'success');
                }
                $this->renderer->display();
            } else {
                $this->renderer->assign('responseStatus', 'fail');
                $this->renderer->fileNotFound();
            }
        }
    }

    public function processRequestParameters()
    {
        $structureManager = $this->getService('structureManager');
        $controller = controller::getInstance();

        if ($controller->getParameter('type')) {
            if ($controller->getParameter('action')) {
                $requestedPath = implode('/', $controller->requestedPath) . '/';
                $structureManager->newElementParameters[$requestedPath]['action'] = $controller->getParameter('action');
                $structureManager->newElementParameters[$requestedPath]['type'] = $controller->getParameter('type');

                if ($controller->getParameter('linkType')) {
                    $structureManager->setNewElementLinkType($controller->getParameter('linkType'));
                }
            }
        } elseif ($controller->getParameter('action') && $controller->getParameter('id')) {
            $structureManager->customActions[$controller->getParameter('id')] = $controller->getParameter('action');
        }
    }

    public function getUrlName()
    {
        return '';
    }
}