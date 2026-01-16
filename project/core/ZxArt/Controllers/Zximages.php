<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use RuntimeException;
use ZxArt\ZxScreen\ZxPictureParametersDto;
use ZxArt\ZxScreen\ZxPictureUrlHelper;

class Zximages extends controllerApplication
{
    private const string KEY_VALUE_SEPARATOR = '=';
    private const string PARAMETER_SEPARATOR = ';';
    private const string DIRECTORY = 'zximages';

    public $rendererName = 'zxScreen';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $paramsString = $controller->getRequestedPath()[1] ?? null;
        $params = $this->buildRequestParametersDto($paramsString);

        $pathsManager = $this->pathsManager;
        $filePath = $pathsManager->getPath('uploads') . $params->id;

        if (!is_file($filePath)) {
            $filePath = $pathsManager->getPath('releases') . $params->id;
        }

        if (!is_file($filePath)) {
            $this->renderer->fileNotFound();
            return;
        }
        $cacheName = ZxPictureUrlHelper::getFileName($params);
        $cacheDir = PUBLIC_PATH . self::DIRECTORY . '/';
        if (!is_dir($cacheDir)) {
            if (!mkdir($cacheDir, 0777, true) && !is_dir($cacheDir)) {
                throw new RuntimeException(sprintf('Directory "%s" was not created', $cacheDir));
            }
        }
        $this->renderer->assign('path', $filePath);
        $this->renderer->assign('type', $params->type);
        $this->renderer->assign('mode', $params->mode);
        $this->renderer->assign('palette', $params->palette);
        $this->renderer->assign('border', $params->border);
        $this->renderer->assign('zoom', $params->zoom);
        $this->renderer->assign('rotation', $params->rotation ?? 1);
        $this->renderer->assign('cacheEnabled', true);
        $this->renderer->assign('cacheFileName', $cacheDir . $cacheName);
        $this->renderer->setContentDisposition('inline');
        $this->renderer->display();
    }

    private function buildRequestParametersDto(string $parametersString): ZxPictureParametersDto
    {
        $id = null;
        $type = null;
        $mode = 'mix';
        $palette = null;
        $border = null;
        $zoom = 1;
        $rotation = null;

        if ($parametersString !== '') {
            $rawPairs = explode(self::PARAMETER_SEPARATOR, $parametersString);

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
                        $zoom = is_numeric($value) ? (int)$value : null;
                        break;
                    case 'rotation':
                        $rotation = is_numeric($value) ? (int)$value : null;;
                        break;
                }
            }
        }

        return new ZxPictureParametersDto(
            type: $type,
            zoom: $zoom,
            id: $id,
            border: $border,
            rotation: $rotation,
            mode: $mode,
            palette: $palette
        );
    }
}