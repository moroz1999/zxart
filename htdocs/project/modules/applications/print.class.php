<?php

class printApplication extends controllerApplication
{
    public $rendererName = 'zxScreen';
    use CrawlerFilterTrait;

    public function initialize()
    {
        $this->createRenderer();
        return !$this->isCrawlerDetected();
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
            /**
             * @var zxPictureElement $zxPictureElement
             */
            if ($zxPictureElement = $structureManager->getElementById($id)) {
                $zxImageConverter = new \ZxImage\Converter();
                $zxImageConverter->setGigascreenMode('mix');
                $zxImageConverter->setRotation($zxPictureElement->rotation);
                $zxImageConverter->setBorder(false);
                $zxImageConverter->setZoom(1);
                $zxImageConverter->setType($zxPictureElement->type);
                $zxImageConverter->setCacheEnabled(true);
                $zxImageConverter->setCachePath($this->getService('PathsManager')->getPath('zxCache'));
                $zxImageConverter->setPath($this->getService('PathsManager')->getPath('uploads') . $id);
                if ($zxImageConverter->getBinary()) {
                    $filePath = $zxImageConverter->getCacheFileName();

                    $dpi = 300;
                    $inchCm = 2.54;
                    $canvasPaddingCm = 0.3;
                    $canvasWCm = 29.7;
                    $canvasHCm = 21;
                    $textPaddingCm = $canvasPaddingCm * 2.5;
                    $fontSizeCm = 0.5;

                    $imageWCm = $canvasWCm - $canvasPaddingCm * 2;
                    $imageHCm = $canvasHCm - $canvasPaddingCm * 2 - $textPaddingCm;
                    $textTopCm = $canvasPaddingCm + $imageHCm + ($textPaddingCm - $fontSizeCm) / 2;

                    $canvasWPx = round($canvasWCm / $inchCm * $dpi);
                    $canvasHPx = round($canvasHCm / $inchCm * $dpi);
                    $imageWPx = round($imageWCm / $inchCm * $dpi);
                    $imageHPx = round($imageHCm / $inchCm * $dpi);
                    $textPaddingPx = round($textPaddingCm / $inchCm * $dpi);
                    $canvasPaddingPx = round($canvasPaddingCm / $inchCm * $dpi);
                    $textTopPx = round($textTopCm / $inchCm * $dpi);
                    $fontSizePx = round($fontSizeCm / $inchCm * $dpi);
                    $fontPath = ROOT_PATH . 'project/fonts/Carlito-Regular.ttf';
                    $text = '';
                    $authors = [];
                    foreach ($zxPictureElement->getAuthorsList() as $author) {
                        $authorText = '';
                        $authorText .= $author->title;
                        if ($author->title != $author->realName) {
                            $authorText .= '/' . $author->realName;
                        }

                        if ($city = $author->getCityTitle()) {
                            $authorText .= '/' . $city;
                        }
                        if ($country = $author->getCountryTitle()) {
                            $authorText .= '/' . $country;
                        }
                        $authors[] = $authorText;
                    }
                    $text .= html_entity_decode(implode(', ', $authors), ENT_QUOTES) . " ";

                    if ($zxPictureElement->year) {
                        $text .= $zxPictureElement->year . " ";
                    }
                    if ($text) {
                        $text .= ' - ';
                    }
                    $text .= $zxPictureElement->title;

                    $fileName = $zxPictureElement->getFileName('original', true, false);

                    if (is_file($filePath)) {
                        $configManager = $this->getService('ConfigManager');
                        $pathsManager = $this->getService('PathsManager');

                        $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
                        $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
                        $imageProcess->registerImage('canvas', $filePath);
                        $imageProcess->registerFilter(
                            'aspectedResize',
                            'width=' . $imageWPx . ', height=' . $imageHPx . ', interpolation=' . IMG_NEAREST_NEIGHBOUR
                        );
                        $imageProcess->registerFilter(
                            'crop',
                            'width=' . $imageWPx . ', height=' . ($imageHPx + $textPaddingPx) . ', color=ffffff, valign=top, halign=center'
                        );
                        $imageProcess->registerFilter(
                            'crop',
                            'width=' . $canvasWPx . ', height=' . $canvasHPx . ', color=ffffff, valign=center, halign=center'
                        );
                        $imageProcess->registerFilter(
                            'text',
                            'fontFile=' . $fontPath . ', color=000000, align=center, left=' . $canvasPaddingPx . ', right=' . $canvasPaddingPx . ', top=' . $textTopPx . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                                $text
                            )
                        );
                        $imageProcess->registerExport('canvas', 'png', ROOT_PATH . $fileName);
                        $imageProcess->executeProcess();
                        header('Content-type: image/png');
                        header('Content-Disposition: attachment; filename="' . $fileName . '"');
                        echo file_get_contents(ROOT_PATH . $fileName);
                        unlink(ROOT_PATH . $fileName);
                        exit;
                    }
                }
            }
        }
    }
}
