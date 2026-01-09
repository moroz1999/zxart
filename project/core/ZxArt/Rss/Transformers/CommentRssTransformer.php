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

        $title = sprintf('%s (%s)', $parentElement ? (string)$parentElement->title : '', $userName);
        $link = $parentElement ? $parentElement->getUrl() : '';

        $description = sprintf(
            '<table><tr><td><div style="font-weight: bold">%s:</div><div>%s</div></td></tr></table>',
            htmlspecialchars($userName),
            $element->content
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
