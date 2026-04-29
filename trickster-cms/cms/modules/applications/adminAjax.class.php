<?php

class adminAjaxApplication extends controllerApplication
{
    protected $applicationName = 'adminAjax';
    public $rendererName = 'json';

    public function initialize()
    {
        $this->startSession('admin', $this->getService(ConfigManager::class)->get('main.adminSessionLifeTime'));
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(false, false, true);
        
        $currentElement = false;
        $this->renderer->assign('responseStatus', 'fail');
        $this->renderer->assign('responseData', []);

        if ($controller->getParameter('id')) {
            $structureManager = $this->getService('adminStructureManager');
            $this->processRequestParameters();

            $languagesManager = $this->getService(LanguagesManager::class);
            if ($controller->requestedPath) {
                $structureManager->setRequestedPath($controller->requestedPath);
            } else {
                $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
            }

            $elementId = $controller->getParameter('id');

            if (is_numeric($elementId)) {
                $currentElement = $structureManager->getElementById($elementId);
            } else {
                $currentElement = $structureManager->getCurrentElement($controller->requestedPath);
            }

            if ($currentElement) {
                $this->renderer->assign('responseStatus', 'success');
                $this->renderer->display();
            }
        }

        if (!$currentElement) {
            $this->renderer->fileNotFound();
        }
    }

    public function processRequestParameters()
    {
        $structureManager = $this->getService('adminStructureManager');
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
        return 'admin';
    }
}