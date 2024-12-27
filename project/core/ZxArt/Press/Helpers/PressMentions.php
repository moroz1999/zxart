<?php

namespace ZxArt\Press\Helpers;
use pressArticleElement;

/**
 * @property pressArticleElement[] $mentions
 */
trait PressMentions
{
    public function getPressMentions(): array
    {
        $allArticles = $this->mentions;
        usort($allArticles, static function ($a, $b) {
            $press1 = $a->getParent();
            $press2 = $b->getParent();
            if ($a->year === $b->year) {
                if ($press1->id === $press2->id) {
                    return strcmp($a->title, $b->title);
                }
                return strcmp($press1->title, $press2->title);
            }
            return $a->year - $b->year;
        });
        return $allArticles;
    }
}