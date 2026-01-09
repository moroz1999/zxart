<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;
use zxProdElement;

class ZxProdRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var zxProdElement $element */
        $description = '';
        $imageUrl = (string)$element->getImageUrl(0);
        if ($imageUrl !== '') {
            $description .= sprintf(
                '<a href="%s"><img style="border:none" src="%s" alt="%s"/></a>',
                $element->getUrl(),
                $imageUrl,
                htmlspecialchars((string)$element->title)
            );
        }
        $description .= $element->getTextContent();

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
