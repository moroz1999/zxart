<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;
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
            htmlspecialchars((string)$element->title),
            $authorsHtml
        );

        if ($mp3Path = (string)$element->getMp3FilePath()) {
            if ($mp3Path !== '') {
                $description .= sprintf(
                    '<audio controls><source src="%s" type="audio/mpeg"></audio>',
                    $mp3Path
                );
            }
        }

        $timeStamp = strtotime($element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: (string)$element->title,
            link: (string)$element->URL,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5($element->guid . $rssDate),
        );
    }
}
