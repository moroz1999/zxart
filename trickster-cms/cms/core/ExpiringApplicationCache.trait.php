<?php

trait ExpiringApplicationCacheTrait
{
    use StandardApplicationCacheTrait;

    public function canServeCache()
    {
        if (!$this->cacheExists()) {
            return false;
        }
        return true;
    }

    protected function getCacheFilename()
    {
        if ($this->cacheFileName === null) {
            $this->cacheFileName = md5(controller::getInstance()->fullParametersURL);
        }
        return $this->cacheFileName;
    }
}