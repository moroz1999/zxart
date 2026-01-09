<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use structureElement;
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
            '<a href="%s"><img style="border:none" src="%s" alt="%s"/></a><div><a href="%s">%s</a> by %s</div>',
            $element->getUrl(),
            $element->getImageUrl(1, 0),
            htmlspecialchars($element->title),
            $element->getUrl(),
            htmlspecialchars($element->title),
            $authorsHtml
        );

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
