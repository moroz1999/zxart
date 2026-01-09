<?php
declare(strict_types=1);

namespace ZxArt\Rss\Transformers;

use structureElement;
use ZxArt\Rss\RssDto;
use ZxArt\Rss\RssTransformerInterface;
use zxMusicElement;

class ZxMusicRssTransformer implements RssTransformerInterface
{
    public function transform(structureElement $element): RssDto
    {
        /** @var zxMusicElement $element */
        $elementTitle = html_entity_decode((string)$element->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $authors = [];
        foreach ($element->getAuthorsList() as $author) {
            $authorTitle = html_entity_decode((string)$author->title, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $authors[] = sprintf('<a href="%s">%s</a>', $author->getUrl(), htmlspecialchars($authorTitle));
        }
        $authorsHtml = implode(', ', $authors);

        $description = sprintf(
            '<div style="margin-bottom: 5px"><strong>Title:</strong> <a href="%s">%s</a></div>' .
            '<div style="margin-bottom: 10px"><strong>Authors:</strong> %s</div>',
            $element->getUrl(),
            htmlspecialchars($elementTitle),
            $authorsHtml
        );

        if ($mp3Path = (string)$element->getMp3FilePath()) {
            if ($mp3Path !== '') {
                $description .= sprintf(
                    '<div style="margin-top: 10px"><audio controls style="width: 100%%"><source src="%s" type="audio/mpeg"></audio></div>',
                    $mp3Path
                );
            }
        }

        $timeStamp = strtotime($element->dateCreated);
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
