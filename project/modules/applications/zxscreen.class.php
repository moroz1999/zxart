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

    /**
     * @return void
     */
    public function initialize()
    {
        $this->createRenderer();
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $this->processRequestParameters();
        $filePath = $this->getService('PathsManager')->getPath('uploads') . $this->id;
        if (!is_file($filePath)) {
            $filePath = $this->getService('PathsManager')->getPath('releases') . $this->id;
        }
        if (is_file($filePath)) {
            $this->renderer->assign('path', $filePath);
            $this->renderer->assign('type', $this->type);
            $this->renderer->assign('mode', $this->mode);
            $this->renderer->assign('palette', $this->palette);
            $this->renderer->assign('border', $this->border);
            $this->renderer->assign('zoom', $this->zoom);
            $this->renderer->assign('rotation', $this->rotation);
            if (!in_array($this->zoom, [1, 2, 3]) || $this->download || $this->type === 'hidden') {
                $this->renderer->assign('cacheEnabled', false);
            } else {
                $this->renderer->assign('cacheEnabled', true);
            }

            if ($this->download) {
                $this->renderer->setContentDisposition('attachment');
                $structureManager = $this->getService(
                    'structureManager',
                    [
                        'rootUrl' => $controller->rootURL,
                        'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
                    ],
                    true
                );

                $languagesManager = $this->getService('LanguagesManager');
                $structureManager->setRequestedPath([$languagesManager->getCurrentLanguageCode()]);
                /**
                 * @var zxPictureElement $zxPictureElement
                 */
                if ($zxPictureElement = $structureManager->getElementById($this->id)) {
                    $fileName = $zxPictureElement->getFileName('image', true, false);
                    if ($this->zoom !== 1) {
                        $fileName .= "_{$this->zoom}x";
                    }
                    $this->renderer->assign('fileName', $fileName);
                }
            } else {
                $this->renderer->setContentDisposition('inline');
            }

            $this->renderer->display();
        } else {
            $this->renderer->fileNotFound();
        }
    }

    public function processRequestParameters(): void
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

        $this->zoom = (int)$controller->getParameter('zoom');
        if (!$this->zoom) {
            $this->zoom = 1;
        }
        $this->rotation = $controller->getParameter('rotation');
        $this->download = (boolean)$controller->getParameter('download');
    }
}