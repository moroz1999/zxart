<?php

class BannerGenerator
{
    /**
     * @var structureManager
     */
    protected $structureManager;
    /**
     * @var ApiQueriesManager
     */
    protected $apiQueriesManager;
    /**
     * @var LanguagesManager
     */
    protected $languagesManager;
    /**
     * @var pathsManager
     */
    protected $pathsManager;
    /**
     * @var translationsManager
     */
    protected $translationsManager;
    /**
     * @var configManager
     */
    protected $configManager;

    /**
     * @param pathsManager $pathsManager
     */
    public function setPathsManager($pathsManager): void
    {
        $this->pathsManager = $pathsManager;
    }

    /**
     * @param translationsManager $translationsManager
     */
    public function setTranslationsManager($translationsManager): void
    {
        $this->translationsManager = $translationsManager;
    }

    /**
     * @param configManager $configManager
     */
    public function setConfigManager($configManager): void
    {
        $this->configManager = $configManager;
    }

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager($structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    /**
     * @param ApiQueriesManager $apiQueriesManager
     */
    public function setApiQueriesManager($apiQueriesManager): void
    {
        $this->apiQueriesManager = $apiQueriesManager;
    }

    public function setLanguagesManager($languagesManager): void
    {
        $this->languagesManager = $languagesManager;
    }

    /**
     * @psalm-param 'zxMusic'|'zxPicture' $type
     */
    public function getMonthBest(string $type, $language)
    {
        $this->languagesManager->setCurrentLanguageCode($language);
        $this->structureManager->setRequestedPath([$this->languagesManager->getCurrentLanguageCode()]);

        $element = false;
        $parameters = [
            $type . 'AddedDays' => 30,
        ];
        $query = $this->apiQueriesManager->getQuery();
        $query->setFiltrationParameters($parameters);
        $query->setExportType($type);
        $query->setOrder(['votes' => 'desc']);
        $query->setStart(0);
        $query->setLimit(1);
        if ($result = $query->getQueryResult()) {
            $element = reset($result[$type]);
        }
        return $element;
    }

    /**
     * @return false|string
     */
    public function generateMonthBestPicture($lang): string|false
    {
        /**
         * @var zxPictureElement $zxPictureElement
         */
        if ($zxPictureElement = $this->getMonthBest('zxPicture', $lang)) {
            $id = $zxPictureElement->id;

            $zxImageConverter = new \ZxImage\Converter();
            $zxImageConverter->setGigascreenMode('mix');
            $zxImageConverter->setRotation($zxPictureElement->rotation);
            $zxImageConverter->setBorder(false);
            $zxImageConverter->setZoom(1);
            $zxImageConverter->setType($zxPictureElement->type);
            $zxImageConverter->setCacheEnabled(true);
            $zxImageConverter->setCachePath($this->pathsManager->getPath('zxCache'));
            $zxImageConverter->setPath($this->pathsManager->getPath('uploads') . $id);
            if ($zxImageConverter->getBinary()) {
                $filePath = $zxImageConverter->getCacheFileName();

                $canvasWPx = 240;
                $canvasHPx = 320;
                $padding = 10;
                $imageWPx = $canvasWPx - $padding * 2;
                $imageHPx = 192 - $padding * 2;
                $fontSizePx = 12;
                $fontPath = PUBLIC_PATH . 'fonts/Carlito-Regular.ttf';
                $logoPath = PUBLIC_PATH . 'images/public/logo.png';

                $translationsManager = $this->translationsManager;
                $text = $translationsManager->getTranslationByName("banner.picture_of_month", 'public_translations');
                $text2 = $zxPictureElement->title;
                if (mb_strlen($text2) > 30) {
                    $text2 = mb_substr($text2, 0, 30) . '..';
                }


                $text3 = html_entity_decode($zxPictureElement->getAuthorNamesString(), ENT_QUOTES);
                if (mb_strlen($text3) > 30) {
                    $text3 = mb_substr($text3, 0, 30) . '..';
                }

                $fileName = $zxPictureElement->getFileName('original', true, false);

                if (is_file($filePath)) {
                    $configManager = $this->configManager;
                    $pathsManager = $this->pathsManager;

                    $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
                    $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
                    $imageProcess->registerImage('canvas', $filePath);
                    $imageProcess->registerImage('logo', $logoPath);
                    $imageProcess->registerFilter(
                        'crop',
                        'width=' . $imageWPx . ', height=' . ($imageHPx) . ', valign=center, halign=center',
                        'canvas'
                    );
                    $imageProcess->registerFilter(
                        'crop',
                        'width=' . $canvasWPx . ', height=' . ($canvasHPx - $padding) . ', color=333333, valign=bottom, halign=center',
                        'canvas'
                    );
                    $imageProcess->registerFilter(
                        'crop',
                        'width=' . $canvasWPx . ', height=' . $canvasHPx . ', color=333333, valign=top, halign=center',
                        'canvas'
                    );
                    $imageProcess->registerFilter('merge', 'top=10', 'canvas', 'canvas', 'logo');
                    $imageProcess->registerFilter(
                        'text',
                        'fontFile=' . $fontPath . ', color=bbbbbb, align=center, left=' . 0 . ', right=' . 0 . ', top=' . 78 . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                            $text
                        ),
                        'canvas'
                    );
                    $imageProcess->registerFilter(
                        'text',
                        'fontFile=' . $fontPath . ', color=ffffff, align=center, left=' . 0 . ', right=' . 0 . ', top=' . 96 . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                            $text2
                        ),
                        'canvas'
                    );
                    $imageProcess->registerFilter(
                        'text',
                        'fontFile=' . $fontPath . ', color=ffffff, align=center, left=' . 0 . ', right=' . 0 . ', top=' . 118 . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                            $text3
                        ),
                        'canvas'
                    );
                    $imageProcess->registerExport('canvas', 'png', ROOT_PATH . $fileName);
                    $imageProcess->executeProcess();
                    $content = file_get_contents(ROOT_PATH . $fileName);
                    unlink(ROOT_PATH . $fileName);
                    return $content;
                }
            }
        }
        return false;
    }

    /**
     * @return false|string
     */
    public function generateBestPicture($lang): string|false
    {
        /**
         * @var zxPictureElement $zxPictureElement
         */
        if ($zxPictureElement = $this->getMonthBest('zxPicture', $lang)) {
            $id = $zxPictureElement->id;

            $zxImageConverter = new \ZxImage\Converter();
            $zxImageConverter->setGigascreenMode('mix');
            $zxImageConverter->setRotation($zxPictureElement->rotation);
            $zxImageConverter->setBorder(false);
            $zxImageConverter->setZoom(1);
            $zxImageConverter->setType($zxPictureElement->type);
            $zxImageConverter->setCacheEnabled(true);
            $zxImageConverter->setCachePath($this->pathsManager->getPath('zxCache'));
            $zxImageConverter->setPath($this->pathsManager->getPath('uploads') . $id);
            if ($zxImageConverter->getBinary()) {
                $filePath = $zxImageConverter->getCacheFileName();

                $fileName = $zxPictureElement->getFileName('original', true, false);

                if (is_file($filePath)) {
                    $configManager = $this->configManager;
                    $pathsManager = $this->pathsManager;

                    $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
                    $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
                    $imageProcess->registerImage('canvas', $filePath);
                    $imageProcess->registerFilter('aspectedResize', 'width=' . 154, 'canvas');
                    $imageProcess->registerExport('canvas', 'png', ROOT_PATH . $fileName);
                    $imageProcess->executeProcess();
                    $content = file_get_contents(ROOT_PATH . $fileName);
                    unlink(ROOT_PATH . $fileName);
                    return $content;
                }
            }
        }
        return false;
    }

    /**
     * @return false|string
     */
    public function generateMonthBestTune($lang): string|false
    {
        /**
         * @var zxMusicElement $zxMusicElement
         */
        if ($zxMusicElement = $this->getMonthBest('zxMusic', $lang)) {
            $canvasWPx = 240;
            $canvasHPx = 320;
            $padding = 20;

            $fontSizePx = 12;
            $lineHeight = 2;
            $fontPath = PUBLIC_PATH . 'fonts/Carlito-Regular.ttf';
            $logoPath = PUBLIC_PATH . 'images/logo.png';
            $playPath = PUBLIC_PATH . 'images/play.png';

            $translationsManager = $this->translationsManager;
            $text = $translationsManager->getTranslationByName("banner.tune_of_month", 'public_translations');
            $texts = [];
            $text2 = $zxMusicElement->title;
            if (mb_strlen($text2) > 30) {
                $text2 = mb_substr($text2, 0, 30) . '..';
            }
            $texts[] = $text2;

            $text3 = html_entity_decode($zxMusicElement->getAuthorNamesString(), ENT_QUOTES);
            if (mb_strlen($text3) > 30) {
                $text3 = mb_substr($text3, 0, 30) . '..';
            }
            $texts[] = $text3;

            if ($zxMusicElement->getPartyId()) {
                $party = $zxMusicElement->getPartyElement();
                $text4 = $zxMusicElement->partyplace . ' at ' . html_entity_decode($party->title);
                if (mb_strlen($text3) > 30) {
                    $text4 = mb_substr($text4, 0, 30) . '..';
                }
                $texts[] = $text4;
            }

            $fileName = $zxMusicElement->getFileName('original', true, false);

            $configManager = $this->configManager;
            $pathsManager = $this->pathsManager;

            $imageProcess = new \ImageProcess\ImageProcess($pathsManager->getPath('imagesCache'));
            $imageProcess->setImagesCaching(true);
            $imageProcess->setDefaultCachePermissions($configManager->get('paths.defaultCachePermissions'));
            $imageProcess->registerImage('canvas', $logoPath);
            $imageProcess->registerImage('play', $playPath);

            $imageProcess->registerFilter(
                'crop',
                'width=' . $canvasWPx . ', height=' . ($canvasHPx - $padding) . ', color=333333, valign=top, halign=center',
                'canvas'
            );
            $imageProcess->registerFilter(
                'crop',
                'width=' . $canvasWPx . ', height=' . $canvasHPx . ', color=333333, valign=bottom, halign=center',
                'canvas'
            );
            $imageProcess->registerFilter(
                'text',
                'fontFile=' . $fontPath . ', color=bbbbbb, align=center, left=' . 0 . ', right=' . 0 . ', top=' . 100 . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                    $text
                ),
                'canvas'
            );
            foreach ($texts as $key => $row) {
                $imageProcess->registerFilter(
                    'text',
                    'fontFile=' . $fontPath . ', color=ffffff, align=center, left=' . 0 . ', right=' . 0 . ', top=' . (100 + $fontSizePx * $lineHeight * ($key + 1)) . ', fontSize=' . $fontSizePx . ', text=' . urlencode(
                        $row
                    ),
                    'canvas'
                );
            }
            $imageProcess->registerFilter(
                'merge',
                'top=' . (100 + $fontSizePx * $lineHeight * (count($texts) + 2)),
                'canvas',
                'canvas',
                'play'
            );
            $imageProcess->registerExport('canvas', 'png', ROOT_PATH . $fileName);
            $imageProcess->executeProcess();
            $content = file_get_contents(ROOT_PATH . $fileName);
            unlink(ROOT_PATH . $fileName);
            return $content;
        }

        return false;
    }

}