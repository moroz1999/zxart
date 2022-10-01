<?php

trait AuthorshipProviderTrait
{
    public function getAuthorsInfo($type, $roles = null)
    {
        $result = [];
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        if ($info = $authorsManager->getAuthorsInfo($this->id, $type)) {
            if (!$roles) {
                $result = $info;
            } else {
                foreach ($info as $item) {
                    if ($item['roles'] && !array_diff($roles, $item['roles'])) {
                        $result[] = $item;
                    }
                }
            }
        }
        return $result;
    }

    public function getAuthorsRecords($type)
    {
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        return $authorsManager->getElementAuthorsRecords($this->id, $type);
    }

    public function getAuthorshipInfo($type)
    {
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        return $authorsManager->getAuthorshipInfo($this->id, $type);
    }

    public function getAuthorshipRecords($type)
    {
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        return $authorsManager->getAuthorshipRecords($this->id, $type);
    }

    public function getAuthorRoles()
    {
        return [
            'unknown',
            'code',
            'gamedesign',
            'leveldesign',
            'release',
            'music',
            'sfx',
            'organizing',
            'direction',
            'support',
            'testing',
            'graphics',
            '3dmodels',
            'design',
            'logo',
            'font',
            'adaptation',
            'loading_screen',
            'intro_code',
            'intro_graphics',
            'intro_music',
            'ascii',
            'illustrating',
            'tools',
            'localization',
            'concept',
            'story',
            'text',
            'editing',
            'translation',
            'video',
            'guest',
        ];
    }
}