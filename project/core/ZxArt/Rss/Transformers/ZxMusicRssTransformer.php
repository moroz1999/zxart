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
            '<div style="margin-bottom: 5px"><strong>Title:</strong> <a href="%s">%s</a></div>' .
            '<div style="margin-bottom: 10px"><strong>Authors:</strong> %s</div>',
            $element->getUrl(),
            htmlspecialchars((string)$element->title),
            $authorsHtml
        );

        if ($mp3Path = (string)$element->getMp3FilePath()) {
            if ($mp3Path !== '') {
                $description .= sprintf(
                    '<div style="margin-top: 10px"><audio controls style="width: 100%%"><source src="%s" type="audio/mpeg"></audio></div>',
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
