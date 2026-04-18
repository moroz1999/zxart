<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

trait AuthorshipProviderTrait
{
    public function getAuthorsInfo($type, $roles = null)
    {
        $result = [];
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        if ($info = $authorshipRepository->getAuthorsInfo($this->id, $entityType)) {
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
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipRepository->getElementAuthorsRecords($this->id, $entityType);
    }

    public function getAuthorshipInfo($type)
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipRepository->getAuthorshipInfo($this->getId(), $entityType);
    }

    public function getAuthorshipRecords($type)
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipRepository->getAuthorshipRecords($this->getId(), $entityType);
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