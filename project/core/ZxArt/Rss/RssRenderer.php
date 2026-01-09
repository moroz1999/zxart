<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use XMLWriter;

class RssRenderer
{
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
            $xml->writeElement('title', $item->title);
            $xml->writeElement('link', $item->link);
            
            $xml->startElement('description');
            $xml->writeCdata($item->description);
            $xml->endElement();

            if ($item->content) {
                $xml->startElement('content');
                $xml->writeCdata($item->content);
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
