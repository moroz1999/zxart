<?php

declare(strict_types=1);

namespace ZxArt\Comments;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Removes markup from submitted comment content.
 *
 * Comment content is plain text: links are produced on output by
 * `commentElement::linkifyHtml()` and line breaks by the storage chunk, so
 * submitted tags carry no meaning. Text inside removed tags is kept; text of
 * script and style elements is dropped with them.
 */
final class CommentContentPurifier
{
    private ?HTMLPurifier $purifier = null;

    public function purify(string $content): string
    {
        return $this->getPurifier()->purify($content);
    }

    private function getPurifier(): HTMLPurifier
    {
        if ($this->purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('HTML.AllowedElements', []);
            // Nothing element-specific to cache when every element is removed.
            $config->set('Cache.DefinitionImpl', null);

            $this->purifier = new HTMLPurifier($config);
        }

        return $this->purifier;
    }
}
