<?php

trait CrawlerFilterTrait
{
    protected function isCrawlerDetected()
    {
        $CrawlerDetect = new Jaybizzle\CrawlerDetect\CrawlerDetect;
        return $CrawlerDetect->isCrawler('');
    }
}