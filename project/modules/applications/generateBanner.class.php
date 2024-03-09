<?php

class generateBannerApplication extends controllerApplication implements ApplicationCacheInterface
{
    public $rendererName = 'zxScreen';
    use CrawlerFilterTrait;
    use ExpiringApplicationCacheTrait;

//    public function cacheExists()
//    {
//        return false;
//    }

    public function initialize()
    {
        $this->createRenderer();
        return !$this->isCrawlerDetected();
    }

    /**
     * @return string
     *
     * @psalm-return 'image/png'
     */
    public function getContentType()
    {
        return 'image/png';
    }

    /**
     * @return void
     */
    public function execute($controller)
    {
        $structureManager = $this->getService(
            'structureManager',
            [
                'rootUrl' => $controller->rootURL,
                'rootMarker' => $this->getService('ConfigManager')->get('main.rootMarkerPublic'),
            ],
            true
        );

        if (!($lang = $controller->getParameter('lang'))) {
            $lang = 'eng';
        }
        if (!($type = $controller->getParameter('type'))) {
            $type = 'bmp';
        }

        /**
         * @var BannerGenerator $bannerGenerator
         */
        $bannerGenerator = $this->getService('BannerGenerator');

        $content = false;
        if ($type == 'bmp') {
            $content = $bannerGenerator->generateMonthBestPicture($lang);
        } elseif ($type == 'bmt') {
            $content = $bannerGenerator->generateMonthBestTune($lang);
        }
        if ($content) {
            $this->createCache($content);

            header('Content-type: ' . $this->getContentType());
            header('Content-Disposition: inline');

            echo $content;
        }
    }

    /**
     * @return int
     *
     * @psalm-return 3600
     */
    public function getCacheExpirationTime()
    {
        return 1 * 60 * 60;
    }
}