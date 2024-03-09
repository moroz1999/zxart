<?php

class zxFileScreenApplication extends controllerApplication
{
    protected $width;
    protected $height;
    protected $id;
    protected $fileName;
    protected $type;
    protected $mode;
    protected $border;
    protected $size;
    protected $rotation;
    protected $originalName;
    protected $download;
    protected $fileId;
    public $rendererName = 'zxScreen';

    public function initialize()
    {
        $this->createRenderer();
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
                $this->renderer->assign('fileContents', $file->getContent());
                $this->renderer->assign('type', $this->type);
                $this->renderer->assign('mode', $this->mode);
                $this->renderer->assign('border', $this->border);
                $this->renderer->assign('rotation', $this->rotation);
                if ($this->download) {
                    $this->renderer->setContentDisposition('attachment');
                } else {
                    $this->renderer->setContentDisposition('inline');
                }

                $this->renderer->assign('fileName', $this->fileName);
                $this->renderer->display();
            } else {
                $this->renderer->fileNotFound();
            }
        }
    }

    public function processRequestParameters()
    {
        $controller = controller::getInstance();
        $this->fileName = $controller->getParameter('fileName');
        $this->type = $controller->getParameter('type');
        $this->mode = $controller->getParameter('mode');
        $this->border = $controller->getParameter('border');
        $this->rotation = $controller->getParameter('rotation');
        $this->download = (boolean)$controller->getParameter('download');
        $this->fileId = (int)$controller->getParameter('fileId');
        $this->id = (int)$controller->getParameter('id');
    }
}

