<?php

class resizeZxProd extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param zxProdElement $structureElement
     * @return mixed|void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $pathsManager = $this->getService('PathsManager');
        $configManager = $this->getService('ConfigManager');
        if ($images = $structureElement->getFilesList('connectedFile')) {
            foreach ($images as $image) {
                $filePath = $structureElement->getUploadedFilesPath() . $image->id;
                if (is_file($filePath)) {
                    $info = getimagesize($filePath);
                    $width = $info[0];
                    if ($width > 500) {
                        $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
                        $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
                        $imageProcess->registerImage('canvas', $filePath);
                        $imageProcess->registerFilter(
                            'aspectedResize',
                            'width=' . $width / 2 . ', interpolation=' . IMG_NEAREST_NEIGHBOUR
                        );
                        $imageProcess->registerExport('canvas', null, $filePath);
                        $imageProcess->executeProcess();
                    }
                }
            }
        }
        $structureElement->setViewName('details');

    }
}