<?php

interface ApplicationCacheInterface
{
    public function canServeCache();

    public function cacheExists();

    public function createCache($content);

    public function serveCache();

    public function clearCache();
}