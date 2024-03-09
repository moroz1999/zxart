<?php

class generateBannerLinkApplication extends controllerApplication
{
    public $rendererName = 'zxScreen';
    use CrawlerFilterTrait;

    public function initialize()
    {
        $this->createRenderer();
        return !$this->isCrawlerDetected();
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
        $bannerGenerator = $this->getService('BannerGenerator');
        /**
         * @var zxArtItem $element
         * @var BannerGenerator $bannerGenerator
         */
        if ($type == 'bmp') {
            if ($element = $bannerGenerator->getMonthBest('zxPicture', $lang)) {
                $controller->redirect($element->getUrl());
            }
        } elseif ($type == 'bmt') {
            if ($element = $bannerGenerator->getMonthBest('zxMusic', $lang)) {
                $controller->redirect($element->getUrl());
            }
        }
    }

    public function getUrlName()
    {
        return '';
    }
}

