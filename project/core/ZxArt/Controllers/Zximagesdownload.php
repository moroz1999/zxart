<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;

class Zximagesdownload extends controllerApplication
{
    private const string KEY_VALUE_SEPARATOR = '=';
    private const string PARAMETER_SEPARATOR = ';';

    protected $id;
    protected $type;
    protected $mode;
    protected $palette;
    protected $border;
    protected $zoom;
    protected $rotation;

    public $rendererName = 'zxScreen';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->processRequestParameters($controller);

        if ($this->id === null) {
            $this->renderer->fileNotFound();
            return;
        }

        $pathsManager = $this->pathsManager;
        $filePath = $pathsManager->getPath('uploads') . $this->id;

        if (!is_file($filePath)) {
            $filePath = $pathsManager->getPath('releases') . $this->id;
        }

        if (!is_file($filePath)) {
            $this->renderer->fileNotFound();
            return;
        }

        $this->renderer->assign('path', $filePath);
        $this->renderer->assign('type', $this->type);
        $this->renderer->assign('mode', $this->mode);
        $this->renderer->assign('palette', $this->palette);
        $this->renderer->assign('border', $this->border);
        $this->renderer->assign('zoom', $this->zoom);
        $this->renderer->assign('rotation', $this->rotation);
        $this->renderer->assign('cacheEnabled', false);

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

        if ($zxPictureElement = $structureManager->getElementById($this->id)) {
            $fileName = $zxPictureElement->getFileName('image', true, false);
            if ($this->zoom !== 1) {
                $fileName .= "_{$this->zoom}x";
            }
            $this->renderer->assign('fileName', $fileName);
        }

        $this->renderer->display();
    }

    private function processRequestParameters($controller): void
    {
        $paramsString = $controller->getRequestedPath()[1] ?? '';
        if (!is_string($paramsString)) {
            $paramsString = '';
        }

        $id = null;
        $type = null;
        $mode = 'mix';
        $palette = null;
        $border = null;
        $zoom = 1;
        $rotation = 1;

        if ($paramsString !== '') {
            $rawPairs = explode(self::PARAMETER_SEPARATOR, $paramsString);

        foreach ($rawPairs as $rawPair) {
            if ($rawPair === '') {
                continue;
            }

            $keyValue = explode(self::KEY_VALUE_SEPARATOR, $rawPair, 2);
            if (count($keyValue) !== 2) {
                continue;
            }

            [$key, $value] = $keyValue;

                switch ($key) {
                    case 'id':
                        $id = is_numeric($value) ? (int)$value : null;
                        break;
                    case 'type':
                        $type = $value;
                        break;
                    case 'mode':
                        $mode = $value;
                        break;
                    case 'pal':
                        $palette = $value;
                        break;
                    case 'border':
                        $border = is_numeric($value) ? (int)$value : null;
                        break;
                    case 'zoom':
                        $zoom = (is_numeric($value) && (int)$value > 0) ? (int)$value : 1;
                        break;
                    case 'rotation':
                        $rotation = (is_numeric($value) && (int)$value > 0) ? (int)$value : 1;
                        break;
        }
            }
        }

        $this->id = $id;
        $this->type = $type;
        $this->mode = $mode;
        $this->palette = $palette;
        $this->border = $border;
        $this->zoom = $zoom;
        $this->rotation = $rotation;
    }
}
