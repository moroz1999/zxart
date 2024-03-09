<?php

class monthlyImageApplication extends controllerApplication implements ApplicationCacheInterface
{
    public $rendererName = 'zxScreen';
    use CrawlerFilterTrait;
    use ExpiringApplicationCacheTrait;

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

        /**
         * @var BannerGenerator $bannerGenerator
         */
        $bannerGenerator = $this->getService('BannerGenerator');

        $content = $bannerGenerator->generateBestPicture($lang);
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