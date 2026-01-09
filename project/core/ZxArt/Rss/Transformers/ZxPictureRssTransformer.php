<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;
use zxPictureElement;

class ZxPictureRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var zxPictureElement $element */
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authors[] = sprintf('<a href="%s">%s</a>', $author->getUrl(), $author->title);
        }
        $authorsHtml = implode(', ', $authors);

        $description = sprintf(
            '<div style="margin-bottom: 10px"><a href="%s"><img style="border:none; max-width: 100%%; height: auto;" src="%s" alt="%s"/></a></div>' .
            '<div><strong>Title:</strong> <a href="%s">%s</a></div>' .
            '<div><strong>Authors:</strong> %s</div>',
            $element->getUrl(),
            $element->getImageUrl(1, 0),
            htmlspecialchars((string)$element->title),
            $element->getUrl(),
            htmlspecialchars((string)$element->title),
            $authorsHtml
        );

        $timeStamp = strtotime((string)$element->dateCreated);
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
