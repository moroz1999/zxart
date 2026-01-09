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
        $elementTitle = html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authorTitle = html_entity_decode((string)$author->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $authors[] = sprintf('<a href="%s">%s</a>', $author->getUrl(), htmlspecialchars($authorTitle));
        }
        $authorsHtml = implode(', ', $authors);

        $description = sprintf(
            '<div style="margin-bottom: 10px"><a href="%s"><img style="border:none; max-width: 100%%; height: auto;" src="%s" alt="%s"/></a></div>' .
            '<div><strong>Title:</strong> <a href="%s">%s</a></div>' .
            '<div><strong>Authors:</strong> %s</div>',
            $element->getUrl(),
            $element->getImageUrl(1, 0),
            htmlspecialchars($elementTitle),
            $element->getUrl(),
            htmlspecialchars($elementTitle),
            $authorsHtml
        );

        $timeStamp = strtotime((string)$element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: $elementTitle,
            link: (string)$element->URL,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5($element->guid . $rssDate),
        );
    }
}
