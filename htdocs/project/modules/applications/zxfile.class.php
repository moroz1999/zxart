<?php

class zxfileApplication extends controllerApplication
{
    use CrawlerFilterTrait;

    protected $applicationName = 'zxfile';
    protected $id;
    protected $fileName;
    protected $fileId;
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
        /**
         * @var zxReleaseElement $element
         */
        if ($element = $structureManager->getElementById($this->id)) {
            /**
             * @var ZxParsingManager $zxParsingManager
             */
            $zxParsingManager = $this->getService('ZxParsingManager');
            $file = false;
            if ($this->fileId) {
                $file = $zxParsingManager->extractFile($element->getFilePath(), $this->fileId);
            }
            if ($file) {
                header('Content-type: application/octet-stream');
                header('Content-Disposition: attachment; filename="' . $file->getItemName() . '"');
                echo $file->getContent();
            } else {
                $this->renderer->fileNotFound();
            }
        }
    }

    public function processRequestParameters()
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('id')) {
            $this->id = $controller->getParameter('id');
        }
        if ($controller->getParameter('fileId')) {
            $this->fileId = $controller->getParameter('fileId');
        }
        if ($controller->getParameter('filename')) {
            $this->fileName = $controller->getParameter('filename');
        }
    }
}

