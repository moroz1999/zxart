<?php

class userPlaylistsElement extends structureElement
{
    public $dataResourceName = 'module_generic';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected $userPlaylists;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields): void
    {
    }

    public function getPlaylists()
    {
        if ($this->userPlaylists == null) {
            $this->userPlaylists = [];
            $user = $this->getService(user::class);

            $linksManager = $this->getService('linksManager');
            if ($idList = $linksManager->getConnectedIdList($user->id, 'structure', 'parent')) {
                $structureManager = $this->getService('structureManager');
                $this->userPlaylists = $structureManager->getElementsByIdList($idList, $this->getId(), true);
            }
            $this->childrenList = $this->userPlaylists;
        }
        return $this->userPlaylists;
    }

    /**
     * @return false|string
     */
    public function getJson(): string|false
    {
        $result = [];
        if ($playlists = $this->getPlaylists()) {
            foreach ($playlists as $playlist) {
                $result[] = $playlist->getElementData();
            }
        }
        return json_encode($result);
    }
}


