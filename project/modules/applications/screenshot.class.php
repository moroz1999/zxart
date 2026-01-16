<?php

class screenshotApplication extends controllerApplication
{
    use CrawlerFilterTrait;

    protected $applicationName = 'screenshot';
    protected $id;
    protected $mode;
    public $rendererName = 'fileReader';

    /**
     * @return void
     */
    public function initialize()
    {
        $this->startSession('public');
        $this->createRenderer();
        set_time_limit(5);
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService('Cache');
        $cache->enable(true, true, true);

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
            $filePath = $this->pathsManager->getPath('releases') . $element->file;
            if (strpos($this->id, '/') === false && strpos($this->id, '\\') === false && is_file($filePath)) {
                $this->renderer->setContentDisposition('inline');
                $this->renderer->setContentType(mime_content_type($filePath));
                $this->renderer->assign('filePath', $filePath);
                $this->renderer->assign('fileName', $element->fileName);
                $this->renderer->display();
            } else {
                $this->renderer->fileNotFound();
            }
        } else {
            $this->logError('Release screenshot is not loaded: ' . $this->id);
            $this->renderer->fileNotFound();
        }
    }

    public function processRequestParameters(): void
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('id')) {
            $this->id = (int)$controller->getParameter('id');
        }
    }
}


