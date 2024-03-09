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
            'release',
            'adaptation',
            'restoring',
            'music',
            'sfx',
            'support',
            'testing',
            'graphics',
            'loading_screen',
            'intro_code',
            'intro_graphics',
            'intro_music',
            'organizing',
            'direction',
            '3dmodels',
            'design',
            'logo',
            'font',
            'ascii',
            'illustrating',
            'tools',
            'localization',
            'concept',
            'gamedesign',
            'leveldesign',
            'story',
            'text',
            'editing',
            'translation',
            'video',
            'guest',
        ];
    }

    public function getShortAuthorship(string $type): array
    {
        $result = [];
        foreach ($this->getAuthorsInfo($type) as $item) {
            if ($item['authorElement']) {
                $result[] = [
                    'title' => html_entity_decode($item['authorElement']->getTitle(), ENT_QUOTES),
                    'url' => $item['authorElement']->getUrl(),
                    'roles' => $item['roles']
                ];
            }
        }
        return $result;
    }
}