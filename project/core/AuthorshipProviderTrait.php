<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;

trait AuthorshipProviderTrait
{
    public function getAuthorsInfo($type, $roles = null)
    {
        $result = [];
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        if ($info = $authorshipRepository->getAuthorsInfo($this->id, $type)) {
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
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        return $authorshipRepository->getElementAuthorsRecords($this->id, $type);
    }

    public function getAuthorshipInfo($type)
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        return $authorshipRepository->getAuthorshipInfo($this->getId(), $type);
    }

    public function getAuthorshipRecords($type)
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        return $authorshipRepository->getAuthorshipRecords($this->getId(), $type);
    }

    /**
     * @return string[]
     */
    public function getAuthorRoles(): array
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