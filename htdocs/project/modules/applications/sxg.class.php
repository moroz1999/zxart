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
//                    $width = 360;
//                    $height = 288;
//                    $image->setWidth($width);
//                    $image->setHeight($height);

                        $image = new Sxg\Image();
//                        $palette = [
//                            0x000000,
//                            0x0000ff,
//                            0xff0000,
//                            0xff00ff,
//
//                            0x00ff00,
//                            0x00ffff,
//                            0xffff00,
//                            0xffffff,
//
//                            0x000000,
//                            0x0000cd,
//                            0xcd0000,
//                            0xcd00cd,
//
//                            0x00cd00,
//                            0x00cdcd,
//                            0xcdcd00,
//                            0xcdcdcd,
//                        ];
//                        $image->setRgbPalette($palette);
                        $image->setColorFormat($image::SXG_COLOR_FORMAT_256);
                        $image->setPaletteType($image::SXG_PALETTE_FORMAT_PWM);
                        $image->importFromGd($gdObject);
                        http_response_code(200);
                        header('Content-type: image/sxg');
                        header('Content-disposition: inline; filename="' . $id . '.sxg"');
                        echo $image->getSxgData();
//                        if (file_put_contents(ROOT_PATH . 'sxg.sxg', $image->getSxgData())) {
//                            $zxImageConverter = new \ZxImage\Converter();
//                            $zxImageConverter->setSize(2);
//                            $zxImageConverter->setType('sxg');
//                            $zxImageConverter->setCacheEnabled(false);
//                            $zxImageConverter->setPath(ROOT_PATH . 'sxg.sxg');
//                            header('Content-type: image/png');
//
//                            echo $zxImageConverter->getBinary();
//
//                        }
                    }
                }
            }
        }
    }
}
