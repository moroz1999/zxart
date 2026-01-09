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
                '<div style="margin-bottom: 10px"><a href="%s"><img style="border:none; max-width: 100%%; height: auto;" src="%s" alt="%s"/></a></div>',
                $element->getUrl(),
                $imageUrl,
                htmlspecialchars((string)$element->title)
            );
        }
        $description .= sprintf(
            '<div style="margin-bottom: 5px"><strong>Title:</strong> <a href="%s">%s</a></div>',
            $element->getUrl(),
            htmlspecialchars((string)$element->title)
        );
        $textContent = $element->getTextContent();
        if ($textContent !== '') {
            $description .= sprintf('<div style="margin-top: 10px;">%s</div>', $textContent);
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
