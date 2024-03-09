<?php

trait ReleasesProvider
{
    /**
     * @psalm-return list{0?: mixed,...}
     */
    public function getReleasesInfo(): array
    {
        $data = [];
        $releases = $this->getReleases();
        foreach ($releases as $release) {
            $data[] = $release->getElementData('list');
        }
        return $data;
    }
}