<?php

class sxgApplication extends controllerApplication
{
    public $rendererName = 'zxScreen';

    public function initialize()
    {
        $this->createRenderer();
    }

    public function execute($controller)
    {
        if ($id = (int)$controller->getParameter('id')) {
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

            if ($imageElement = $structureManager->getElementById($id)) {
                $zxImageConverter = new \ZxImage\Converter();
                $zxImageConverter->setGigascreenMode('mix');
                $zxImageConverter->setRotation($imageElement->rotation);
                $zxImageConverter->setBorder(false);
                $zxImageConverter->setZoom(1);
                $zxImageConverter->setType($imageElement->type);
                $zxImageConverter->setCacheEnabled(true);
                $zxImageConverter->setCachePath($this->getService('PathsManager')->getPath('zxCache'));
                $zxImageConverter->setPath($this->getService('PathsManager')->getPath('uploads') . $id);
                if ($zxImageConverter->getBinary()) {
                    $filePath = $zxImageConverter->getCacheFileName();

                    $gdObject = false;
                    if (is_file($filePath)) {
                        $mime = $zxImageConverter->getResultMime();
                        switch ($mime) {
                            case 'image/jpeg':
                                $gdObject = imagecreatefromjpeg($filePath);
                                break;
                            case 'image/gif':
                                $gdObject = imagecreatefromgif($filePath);
                                break;
                            case 'image/png':
                                $gdObject = imagecreatefrompng($filePath);
                                break;
                        }
                    }
                    if ($gdObject) {

                        $image = new Sxg\Image();
                        $image->setColorFormat($image::SXG_COLOR_FORMAT_256);
                        $image->setPaletteType($image::SXG_PALETTE_FORMAT_PWM);
                        $image->importFromGd($gdObject);
                        http_response_code(200);
                        header('Content-type: image/sxg');
                        header('Content-disposition: inline; filename="' . $id . '.sxg"');
                        echo $image->getSxgData();
                    }
                }
            }
        }
    }
}
