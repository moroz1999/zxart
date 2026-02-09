<?php
declare(strict_types=1);

namespace ZxArt\Controllers;

use controller;
use controllerApplication;
use LanguagesManager;
use structureManager;
use ZxArt\Rss\RssRenderer;
use ZxArt\Rss\Transformers\CommentRssTransformer;
use ZxArt\Rss\Transformers\ZxMusicRssTransformer;
use ZxArt\Rss\Transformers\ZxPictureRssTransformer;
use ZxArt\Rss\Transformers\ZxProdRssTransformer;
use ZxArt\Rss\Transformers\ZxReleaseRssTransformer;

class Rss extends controllerApplication
{
    public $rendererName = 'smarty';

    public function __construct(
        controller $controller,
        string $applicationName,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
    ) {
        parent::__construct($controller, $applicationName);
    }

    public function initialize(): void
    {
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();

        $types = [
            'comment',
            'zxPicture',
            'zxMusic',
            'zxProd',
            'zxRelease',
        ];

        $limit = 100;
        $elements = $this->structureManager->getElementsByType(
            $types,
            $languageId,
            ['dateCreated' => '0'],
            $limit
        );

        $transformers = [
            'zxPicture' => new ZxPictureRssTransformer(),
            'zxMusic' => new ZxMusicRssTransformer(),
            'comment' => new CommentRssTransformer(),
            'zxProd' => new ZxProdRssTransformer(),
            'zxRelease' => new ZxReleaseRssTransformer(),
        ];

        $rssDtos = [];
        if ($elements) {
            /** @var \structureElement $element */
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
            'ZX-Art RSS',
            (string)$controller->baseURL,
            'Latest updates from ZxArt',
            $rssDtos
        );

        header('Content-Type: application/rss+xml; charset=utf-8');
        header('Content-Disposition: inline');
        echo $rssXml;
    }

    public function getUrlName()
    {
        return '';
    }
}
