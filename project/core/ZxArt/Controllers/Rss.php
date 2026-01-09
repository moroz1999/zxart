<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controllerApplication;
use ZxArt\Rss\CommentRssTransformer;
use ZxArt\Rss\RssRenderer;
use ZxArt\Rss\ZxMusicRssTransformer;
use ZxArt\Rss\ZxPictureRssTransformer;

class Rss extends controllerApplication
{
    public $rendererName = 'smarty';

    public function initialize(): void
    {
        // Не используем стандартный рендерер через createRenderer
        $this->createRenderer();

    }

    public function execute($controller): void
    {
        $configManager = $this->getService('ConfigManager');
        $structureManager = $this->getService('structureManager', [
            'rootUrl' => $controller->baseURL,
            'rootMarker' => $configManager->get('main.rootMarkerPublic'),
        ], true);

        $languagesManager = $this->getService('LanguagesManager');
        $languageId = $languagesManager->getCurrentLanguageId();

        $rssConfig = $configManager->getConfig('rss');
        $types = $rssConfig->getMerged('types');

        $limit = 200;
        $elements = $structureManager->getElementsByType($types, $languageId, ['dateCreated' => '0'], $limit);

        $transformers = [
            'zxPicture' => new ZxPictureRssTransformer(),
            'zxMusic' => new ZxMusicRssTransformer(),
            'comment' => new CommentRssTransformer(),
        ];

        $rssDtos = [];
        if ($elements) {
            foreach ($elements as $element) {
                if ($element->hidden) {
                    continue;
                }
                
                $transformer = $transformers[$element->structureType] ?? null;
                if ($transformer) {
                    $rssDtos[] = $transformer->transform($element);
                }
            }
        }

        $renderer = new RssRenderer();
        $rssXml = $renderer->render(
            'ZxArt RSS',
            (string)$controller->baseURL,
            'Latest updates from ZxArt',
            $rssDtos
        );

        header('Content-Type: text/html; charset=utf-8');
        echo $rssXml;
    }
}
