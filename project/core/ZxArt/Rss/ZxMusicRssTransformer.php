<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use structureElement;
use zxMusicElement;

class ZxMusicRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var zxMusicElement $element */
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = sprintf('<a href="%s">%s</a>', $author->getUrl(), $author->title);
        }
        $authorsHtml = implode(', ', $authors);

        $description = sprintf(
            '<div><a href="%s">%s</a> by %s</div>',
            $element->getUrl(),
            htmlspecialchars($element->title),
            $authorsHtml
        );

        if ($mp3Path = $element->getMp3FilePath()) {
            $description .= sprintf(
                '<audio controls><source src="%s" type="audio/mpeg"></audio>',
                $mp3Path
            );
        }

        $timeStamp = strtotime($element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: (string)$element->title,
            link: (string)$element->URL,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5((string)$element->guid . $rssDate),
        );
    }
}
