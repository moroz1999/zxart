<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use commentElement;
use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;

class CommentRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var commentElement $element */
        $parentElement = $element->getInitialTarget();
        $user = $element->getUserElement();
        $userName = $user ? (string)$user->userName : '';
        $userName = html_entity_decode($userName, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $parentTitle = $parentElement ? (string)$parentElement->title : '';
        $parentTitle = html_entity_decode($parentTitle, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $title = sprintf('%s (%s)', $parentTitle, $userName);
        $link = $parentElement ? $parentElement->getUrl() : '';

        $content = (string)$element->content;
        $content = html_entity_decode($content, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $description = sprintf(
            '<div style="border-left: 4px solid #ccc; padding-left: 10px; margin-bottom: 10px;">' .
            '<div style="font-weight: bold; margin-bottom: 5px;">%s:</div>' .
            '<div>%s</div>' .
            '</div>' .
            '<div style="font-size: 0.9em;"><strong>In response to:</strong> <a href="%s">%s</a></div>',
            htmlspecialchars($userName),
            nl2br(htmlspecialchars($content)),
            $link,
            htmlspecialchars($parentTitle)
        );

        $timeStamp = strtotime($element->dateCreated);
        $rssDate = date(DATE_RFC822, $timeStamp);

        return new RssDto(
            title: $title,
            link: $link,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5($element->guid . $rssDate),
        );
    }
}
