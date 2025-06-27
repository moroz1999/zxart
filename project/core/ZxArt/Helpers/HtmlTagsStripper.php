<?php
declare(strict_types=1);

namespace ZxArt\Helpers;

use DOMDocument;
use DOMXPath;

final class HtmlTagsStripper
{
    /**
     * Creates a DOMDocument instance from an HTML string.
     *
     * @param string|null $string The input HTML string.
     * @return DOMDocument The DOMDocument object.
     */
    private static function createDom(?string $string = null): DOMDocument
    {
        $dom = new DOMDocument;
        $dom->encoding = 'UTF-8';
        $dom->recover = true;
        $dom->substituteEntities = true;
        $dom->strictErrorChecking = false;
        $dom->formatOutput = false;
        @$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?><div>' . $string.'</div>');
        $dom->normalizeDocument();
        $container = $dom->getElementsByTagName('div')->item(0);

        $container = $container->parentNode->removeChild($container);
        while ($dom->firstChild) {
            $dom->removeChild($dom->firstChild);
        }
        while ($container->firstChild) {
            $dom->appendChild($container->firstChild);
        }

        return $dom;
    }

    /**
     * Removes specified HTML tags but keeps their inner content.
     *
     * @param string $string The input HTML string.
     * @param array $tags List of tags to remove while keeping their content.
     * @return string HTML content without the specified tags.
     */
    private static function unwrapTags(string $string, array $tags): string
    {
        $dom = self::createDom($string);
        $result = '';

        $xpath = new DOMXPath($dom);
        foreach ($tags as $tag) {
            $elements = $xpath->query("//{$tag}");
            if ($elements) {
                foreach ($elements as $element) {
                    $fragment = $dom->createDocumentFragment();
                    while ($element->firstChild) {
                        $fragment->appendChild($element->firstChild);
                    }
                    $element->parentNode->replaceChild($fragment, $element);
                }
            }
        }

        foreach ($dom->childNodes as $node) {
            $result .= $dom->saveHTML($node);
        }

        return $result;
    }

    /**
     * Strips specified HTML tags but retains their inner text.
     *
     * @param string $text The input HTML string.
     * @param array $tags The list of tags to remove.
     * @return string The cleaned HTML string.
     */
    public static function stripTags(string $text, array $tags): string
    {
        return self::unwrapTags($text, $tags);
    }
}
