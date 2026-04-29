<?php

class sitemapApplication extends controllerApplication
{
    protected $applicationName = 'sitemap';
    public $rendererName = 'smarty';
    protected $config;

    public function initialize()
    {
        $configManager = $this->getService(ConfigManager::class);
        $this->startSession('public', $configManager->get('main.publicSessionLifeTime'));
        $this->config = $configManager->getConfig('sitemap');
        $this->createRenderer();
    }

    public function execute($controller)
    {
        /**
         * @var Cache $cache
         */
        $cache = $this->getService(Cache::class);
        $cache->enable(true, false, false);

        $languagesManager = $this->getService(LanguagesManager::class);

        $mapItems = [];
        $languages = $languagesManager->getLanguagesList();
        foreach ($languages as &$languageInfo) {
            $languagesManager->setCurrentLanguageCode($languageInfo->iso6393);
            $languageId = $languageInfo->id;

            $structureManager = $this->getService('publicStructureManager');

            $structureManager->setRequestedPath([$languageInfo->iso6393]);
            $languageElement = $structureManager->getElementById($languageId);
            if (!$languageElement->hidden) {
                if (isset($GLOBALS['sitemapTypes'])) {
                    $types = $GLOBALS['sitemapTypes']; // deprecated since 2016.03
                } else {
                    $types = $this->config->get('types');
                }
                $types = $types ?: [
                    'folder',
                    'category',
                    'product',
                    'service',
                ];
                foreach ($types as &$type) {
                    if ($elements = $structureManager->getElementsByType($type, $languageId)) {
                        foreach ($elements as $element) {
                            if (!$element->hidden && ($type != 'product' || ($element->inactive == '0' && $element->isPurchasable()))
                            ) {
                                $mapItem = [];
                                $mapItem['lastmod'] = date('Y-m-d', strtotime($element->dateModified));
                                $mapItem['loc'] = $element->getUrl();
                                $mapItem['priority'] = '0.5';

                                $mapItems[] = $mapItem;
                            }
                        }
                    }
                }
            }
        }

        $this->renderer->assign('controller', $controller);
        $this->renderer->assign('mapItems', $mapItems);
        $path = $this->pathsManager->getPath('trickster');
        $this->renderer->setTemplatesFolder($path . 'cms/templates/xml');
        $this->renderer->setContentType('text/xml');
        $this->renderer->template = 'sitemap.index.tpl';
        $this->renderer->display();
    }

    public function getUrlName()
    {
        return '';
    }
}


