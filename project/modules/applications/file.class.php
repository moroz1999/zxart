<?php

class fileApplication extends controllerApplication
{
    protected $applicationName = 'file';
    protected $id;
    protected $fileName;
    protected $mode;
    public $rendererName = 'fileReader';

    public function initialize()
    {
        $configManager = $this->getService('ConfigManager');
        $this->startSession('public', $configManager->get('main.publicSessionLifeTime'));
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->processRequestParameters();
        $filePath = $this->getService('PathsManager')->getPath('uploads') . $this->id;
        if (strpos($this->id, '/') === false && strpos($this->id, '\\') === false && is_file($filePath)) {
            if ($this->mode == 'view') {
                $this->renderer->setContentDisposition('inline');
            } else {
                $this->renderer->setContentDisposition('attachment');
            }
            $this->renderer->setContentType("application/octet-stream");
            $this->renderer->assign('filePath', $filePath);
            if ($this->fileName) {
                $this->fileName = str_ireplace('_', ' ', $this->fileName);
            }
            $httpResponse = httpResponse::getInstance();
            $httpResponse->setAccessControlAllowOrigin('*');

            $this->renderer->assign('fileName', $this->fileName);
            $this->renderer->display();
        } else {
            $this->renderer->fileNotFound();
        }
    }

    public function processRequestParameters()
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('id')) {
            $this->id = $controller->getParameter('id');
        }
        if ($controller->getParameter('filename')) {
            $this->fileName = $controller->getParameter('filename');
        }
        if ($controller->getParameter('mode')) {
            $this->mode = $controller->getParameter('mode');
        }
    }

    public function deprecatedParametersRedirection()
    {
        return true;
    }
}

