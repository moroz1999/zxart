<?php
declare(strict_types=1);

namespace ZxArt\Rss;

use commentElement;
use structureElement;

class CommentRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var commentElement $element */
        $parentElement = $element->getParentElement();
        $user = $element->getUser();
        $userName = $user ? $user->userName : 'Anonymous';

        $title = sprintf('%s (%s)', $parentElement ? $parentElement->title : '', $userName);
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
            link: (string)$link,
            description: $description,
            content: '',
            date: $rssDate,
            guid: md5((string)$element->guid . $rssDate),
        );
    }
}
