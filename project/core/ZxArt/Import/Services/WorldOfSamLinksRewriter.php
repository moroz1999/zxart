<?php
declare(strict_types=1);


namespace ZxArt\Import\Services;

use DOMDocument;
use DOMElement;
use DOMXPath;

final class WorldOfSamLinksRewriter
{
    public function rewriteLinks(string $html): string
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        libxml_use_internal_errors(true);

        // Wrap into full HTML to avoid libxml quirks with fragments.
        $wrapped = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"></head><body>'
            . $html
            . '</body></html>';

        $dom->loadHTML($wrapped);

        $xpath = new DOMXPath($dom);

        $nodeList = $xpath->query('//a');
        if ($nodeList !== false && $nodeList->length > 0) {
            $anchors = [];
            foreach ($nodeList as $node) {
                if ($node instanceof DOMElement) {
                    $anchors[] = $node;
                }
            }

            foreach ($anchors as $a) {
                $href = trim($a->getAttribute('href'));

                // Keep absolute HTTP(S) and protocol-relative links as-is.
                if ($href !== '' && preg_match('~^(?:[a-z][a-z0-9+\-.]*://|//)~i', $href) === 1) {
                    continue;
                }

                $path = (parse_url($href, PHP_URL_PATH) ?? $href);

                if ($path !== '' && $path[0] !== '/') {
                    $path = '/' . $path;
                }

                // /products/{slug} or /index.php/products/{slug}
                if (preg_match('~^/(?:index\.php/)?products/([^/?#]+)$~', $path, $m) === 1) {
                    $a->setAttribute('href', '/route/type:prod/importOrigin:worldofsam/importId:' . $m[1]);
                    continue;
                }

                // /people/{slug} or /index.php/people/{slug}
                if (preg_match('~^/(?:index\.php/)?people/([^/?#]+)$~', $path, $m) === 1) {
                    $a->setAttribute('href', '/route/importOrigin:worldofsam/importId:' . $m[1]);
                    continue;
                }

                // Everything else -> <span> with children preserved, attributes dropped.
                $this->convertToSpan($dom, $a);
            }
        }

        $tables = $xpath->query('//table');
        if ($tables !== false && $tables->length > 0) {
            foreach ($tables as $table) {
                if (!$table instanceof DOMElement) {
                    continue;
                }
                $table->setAttribute('class', 'table_component');
            }
        }
        // Return inner HTML of <body> only (preserves "fragment" shape)
        $body = $dom->getElementsByTagName('body')->item(0);
        $result = '';

        if ($body !== null) {
            foreach ($body->childNodes as $child) {
                $result .= $dom->saveHTML($child);
            }
        }

        libxml_clear_errors();
        return $result;
    }

    /**
     * Replace <a> with <span>, preserving child nodes and dropping attributes.
     */
    private function convertToSpan(DOMDocument $dom, DOMElement $a): void
    {
        $span = $dom->createElement('span');
        while ($a->firstChild !== null) {
            $span->appendChild($a->firstChild);
        }
        $a->parentNode?->replaceChild($span, $a);
    }

}