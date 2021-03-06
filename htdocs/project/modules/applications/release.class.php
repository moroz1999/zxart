<?php

class releaseApplication extends controllerApplication
{
    use CrawlerFilterTrait;

    protected $applicationName = 'release';
    protected $id;
    protected $fileName;
    protected $mode;
    public $rendererName = 'fileReader';

    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
        return !$this->isCrawlerDetected();
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

        $this->processRequestParameters();

        if ($element = $structureManager->getElementById($this->id)) {
            $filePath = $this->getService('PathsManager')->getPath('releases') . $element->file;
            if (strpos($this->id, '/') === false && strpos($this->id, '\\') === false && is_file($filePath)) {
                if ($element->structureType == 'zxRelease') {
                    $db = $this->getService('db');
                    if ($controller->getParameter('play')) {
                        $db->table('module_zxrelease')->where('id', '=', $element->id)->limit(1)->increment('plays');
                    } else {
                        $db->table('module_zxrelease')->where('id', '=', $element->id)->limit(1)->increment(
                            'downloads'
                        );
                    }
                }
                if ($this->mode == 'view') {
                    $this->renderer->setContentDisposition('inline');
                } else {
                    $this->renderer->setContentDisposition('attachment');
                }

                $this->renderer->assign('filePath', $filePath);
                $this->renderer->assign('fileName', $element->fileName);
                $this->renderer->display();
            } else {
                $this->renderer->fileNotFound();
            }
        } else {
            $this->logError('Release download element is not loaded: ' . $this->id);
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
}

