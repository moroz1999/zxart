<?php
declare(strict_types=1);


namespace ZxArt\Press\Helpers;

use LanguagesManager;
use pressArticleElement;
use ZxArt\Helpers\HtmlTagsStripper;
use ZxArt\Press\Repositories\PressArticleRepository;
use ZxArt\Strings\LanguageDetector;

final class AiTextContent
{
    public function __construct(
        private PressArticleRepository $repository,
        private LanguageDetector       $languageDetector,
        private LanguagesManager       $languagesManager,
    )
    {
    }

    public function getOriginalTextContent($articleId, bool $stripImages = true): ?string
    {
        $content = $this->repository->getOriginalContent($articleId);
        return $this->process($content, $stripImages);
    }

    public function getAITextContent(pressArticleElement $article, bool $stripImages = true): ?string
    {
        $original = $this->getOriginalTextContent($article->id);
        $languageCode = $this->languageDetector->detectLanguage($original);
        $languageId = $this->languagesManager->getLanguageId($languageCode);

        return $this->process($article->getValue('content', $languageId) ?? '', $stripImages);
    }

    private function process(string $content, bool $stripImages = true): ?string
    {
        if ($content === '' || $content === null) {
            return null;
        }
        $stripTags = $stripImages ? ['div', 'p', 'span', 'img', 'br'] : ['div', 'p', 'span', 'br'];

        $content = HtmlTagsStripper::stripTags($content, $stripTags);
        $content = str_replace(["\n\r", "\r\n", "\r"], "\n", $content);

        $content = html_entity_decode($content);
        return str_replace(["-\n", "\r"], '', $content);
    }
}