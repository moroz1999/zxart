<?php

use ImageProcess\SourceFileException;

class imageApplication extends controllerApplication
{
    protected $applicationName = 'image';
    protected $width;
    protected $height;
    protected $id;
    protected $fileName = '';
    protected $layoutType;
    protected $angle;
    protected $multiplier;
    public $rendererName = 'imageProcess';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        $this->processRequestParameters();
        $imagePreset = $this->getService(ConfigManager::class)->get("images-desktop.{$this->layoutType}");
        if (isset($imagePreset['path'])) {
            $originalFilePath = $this->pathsManager->getPath($imagePreset['path']) . $this->id;
        } else {
            $originalFilePath = $this->pathsManager->getPath('uploads') . $this->id;
        }
        $result = false;
        if (is_file($originalFilePath)) {
            if (str_ends_with(strtolower($this->fileName), '.svg')) {
                $result = true;
                $this->renderer->assign('registerImage', [
                    'source',
                    $originalFilePath,
                ]);
                $this->renderer->assign('registerExport', [null, 'svg']);

                $this->renderer->setContentDisposition('inline');
                $this->renderer->display();
            } else {
                $this->renderer->assign('registerImage', [
                    'source',
                    $originalFilePath,
                ]);
                if (!empty($imagePreset['images'])) {
                    foreach ($imagePreset['images'] as $imageInfo) {
                        $this->renderer->assign('registerImage', $imageInfo);
                    }
                }
                if (!empty($imagePreset['filters'])) {
                    foreach ($imagePreset['filters'] as $filter) {
                        if (isset($filter[1])) {
                            $filter[1] = $this->applyMultiplier($filter[1]);
                        }
                        $this->renderer->assign('registerFilter', $filter);
                    }
                }

                if (!empty($imagePreset['format'])) {
                    /**
                     * @var requestHeadersManager $requestHeadersManager
                     */
                    $requestHeadersManager = $this->getService(requestHeadersManager::class);
                    if ($contentTypes = $requestHeadersManager->getAcceptedTypes()) {
                        if (isset($contentTypes['image/webp']) && $requestHeadersManager->getBrowserType() !== 'Safari') {
                            $imagePreset['format'][1] = 'webp';
                        }
                    }
                    $this->renderer->assign('registerExport', $imagePreset['format']);
                } else {
                    $this->renderer->assign('registerExport', null);
                }
                $this->renderer->setContentDisposition('inline');
                try {
                    $this->renderer->display();
                    $result = true;
                } catch (SourceFileException $e) {
                    $this->logError($controller->fullURL . ' ' . $e->getMessage());
                    $this->renderer->fileNotFound();
                }
            }
        }

        if (!$result) {
            $this->renderer->fileNotFound();
        }
    }

    public function processRequestParameters()
    {
        $controller = controller::getInstance();
        if ($controller->getParameter('id')) {
            $this->id = $controller->getParameter('id');
        }
        if ($controller->getParameter('width')) {
            $this->width = $controller->getParameter('width');
        }
        if ($controller->getParameter('height')) {
            $this->height = $controller->getParameter('height');
        }
        if ($controller->getParameter('angle')) {
            $this->angle = $controller->getParameter('angle');
        }
        $this->multiplier = (float)$controller->getParameter('multiplier');
        if ($this->multiplier > 3) {
            $this->multiplier = 3;
        }
        if ($controller->getParameter('filename')) {
            $this->fileName = $controller->getParameter('filename');
        } elseif (!empty($controller->requestedPath)) {
            $this->fileName = last($controller->requestedPath);
        }
        if ($controller->getParameter('type')) {
            $this->layoutType = $controller->getParameter('type');
        }
    }

    protected function applyMultiplier($filterValue)
    {
        if ($this->multiplier && ($this->multiplier > 1)) {
            //preparse filter values
            if (!is_array($filterValue)) {
                $parameterStrings = explode(',', $filterValue);
                $filterValue = [];
                foreach ($parameterStrings as $parameterString) {
                    if ($string = explode('=', $parameterString)) {
                        $filterValue[trim($string[0])] = trim($string[1]);
                    }
                }
            }
            foreach ($filterValue as $name => $value) {
                if ($name == 'width' || $name == 'height') {
                    $filterValue[$name] = $value * $this->multiplier;
                }
            }
        }
        return $filterValue;
    }
}

