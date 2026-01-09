<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;
use zxReleaseElement;

class ZxReleaseRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var zxReleaseElement $element */
        $elementTitle = html_entity_decode((string)$element->getMetaTitle(), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $description = '';
        $imageUrl = (string)$element->getImageUrl(0);
        if ($imageUrl !== '') {
            $description .= sprintf(
                '<div style="margin-bottom: 10px"><a href="%s"><img style="border:none; max-width: 100%%; height: auto;" src="%s" alt="%s"/></a></div>',
                $element->getUrl(),
                $imageUrl,
                htmlspecialchars($elementTitle)
            );
        }
        $description .= sprintf(
            '<div style="margin-bottom: 5px"><strong>Title:</strong> <a href="%s">%s</a></div>',
            $element->getUrl(),
            htmlspecialchars($elementTitle)
        );
        $textContent = $element->getTextContent();
        if ($textContent !== '') {
            $description .= sprintf('<div style="margin-top: 10px;">%s</div>', $textContent);
        }

        $timeStamp = strtotime($element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: 'Release: ' . $elementTitle,
            link: (string)$element->URL,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5($element->guid . $rssDate),
        );
    }
}
