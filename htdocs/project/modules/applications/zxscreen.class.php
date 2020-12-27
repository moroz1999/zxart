<?php

class zxscreenApplication extends controllerApplication
{
    protected $width;
    protected $height;
    protected $id;
    protected $fileName;
    protected $type;
    protected $mode;
    protected $palette;
    protected $border;
    protected $zoom;
    protected $rotation;
    protected $originalName;
    protected $download;
    public $rendererName = 'zxScreen';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->processRequestParameters();

        $filePath = $this->getService('PathsManager')->getPath('uploads') . $this->id;
        if (!is_file($filePath)) {
            $filePath = $this->getService('PathsManager')->getPath('releases') . $this->id;
        }
        if (is_file($filePath)) {
            $this->renderer->assign('filename', $filePath);
            $this->renderer->assign('type', $this->type);
            $this->renderer->assign('mode', $this->mode);
            $this->renderer->assign('palette', $this->palette);
            $this->renderer->assign('border', $this->border);
            $this->renderer->assign('zoom', $this->zoom);
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

    public function processRequestParameters()
    {
        $controller = controller::getInstance();
        $this->id = $controller->getParameter('id');
        $this->fileName = $controller->getParameter('fileName');
        $this->type = $controller->getParameter('type');
        $this->mode = $controller->getParameter('mode');
        $this->palette = $controller->getParameter('pal');
        $border = $controller->getParameter('border');
        if (!is_numeric($border)) {
            $border = null;
        }
        $this->border = $border;

        $this->zoom = $controller->getParameter('zoom');
        $this->rotation = $controller->getParameter('rotation');
        $this->download = (boolean)$controller->getParameter('download');
    }
}