<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use newsElement;
use structureElement;

class NewsRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var newsElement $element */
        $description = '';
        if ($element->image) {
            $description .= sprintf(
                '<a href="%s"><img style="border:none" src="%simage/type:rssImage/id:%s/filename:%s" alt="%s"/></a>',
                $element->getUrl(),
                $element->getService('controller')->baseURL,
                $element->image,
                $element->originalName,
                htmlspecialchars($element->title)
            );
        }
        $description .= $element->introduction;

        $timeStamp = strtotime($element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: (string)$element->title,
            link: (string)$element->URL,
            description: $description,
            content: '',
            date: $rssDate,
            guid: (string)$element->id,
        );
    }
}
