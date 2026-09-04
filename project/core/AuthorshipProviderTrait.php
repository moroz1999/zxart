<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

/**
 * Authorship an element takes part in, both as the work and as the person.
 *
 * Every read is keyed by the element id, so a form draft is answered with
 * nothing: it is not persisted yet, and its transient identifier is a structure
 * path that casts to 0 - the id of no element.
 */
trait AuthorshipProviderTrait
{
    public function getAuthorsInfo($type, $roles = null)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
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
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipRepository->getElementAuthorsRecords($this->id, $entityType);
    }

    public function getAuthorshipInfo($type)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipRepository->getAuthorshipInfo($this->getId(), $entityType);
    }

    public function getAuthorshipRecords($type)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
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
            $authorElement = $item['authorElement'] ?? null;
            if ($authorElement instanceof structureElement) {
                $result[] = [
                    'id' => $authorElement->getId(),
                    'structureType' => $authorElement->structureType,
                    'title' => html_entity_decode($authorElement->getTitle(), ENT_QUOTES),
                    'roles' => $item['roles']
                ];
            }
        }
        return $result;
    }
}
