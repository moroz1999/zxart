<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use XMLWriter;

class RssRenderer
{
    /**
     * Removes characters that are invalid in XML 1.0.
     * Valid: #x9 | #xA | #xD | [#x20-#xD7FF] | [#xE000-#xFFFD] | [#x10000-#x10FFFF]
     */
    private function sanitizeForXml(string $text): string
    {
        return preg_replace(
            '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}\x{10000}-\x{10FFFF}]/u',
            '',
            $text
        ) ?? $text;
    }

    /**
     * @param RssDto[] $items
     */
    public function render(string $title, string $link, string $description, array $items): string
    {
        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'utf-8');
        $xml->startElement('rss');
        $xml->writeAttribute('version', '2.0');
        $xml->startElement('channel');

        $xml->writeElement('title', $title);

        $xml->writeElement('link', $link);
        $xml->writeElement('description', $description);
        $xml->writeElement('language', 'en'); // Или можно вынести в параметры
        $xml->writeElement('ttl', '60');

        foreach ($items as $item) {
            $xml->startElement('item');

            $xml->startElement('title');
            $xml->writeCdata($this->sanitizeForXml($item->title));
            $xml->endElement();

            $xml->writeElement('link', $item->link);

            $xml->startElement('description');
            $xml->writeCdata($this->sanitizeForXml($item->description));
            $xml->endElement();

            if ($item->content) {
                $xml->startElement('content');
                $xml->writeCdata($this->sanitizeForXml($item->content));
                $xml->endElement();
            }

            $xml->writeElement('pubDate', $item->date);
            $xml->writeElement('guid', $item->guid);
            $xml->endElement();
        }

        $xml->endElement();
        $xml->endElement();
        $xml->endDocument();

        return $xml->outputMemory();
    }
}
