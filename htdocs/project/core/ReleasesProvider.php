<?php

trait ReleasesProvider
{
    public function getReleasesInfo()
    {
        $data = [];
        $releases = $this->getReleases();
        foreach ($releases as $release) {
            $data[] = $release->getElementData('list');
        }
        return $data;
    }
}