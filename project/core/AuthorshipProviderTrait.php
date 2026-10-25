<?php

use ZxArt\Authors\AuthorshipRoles;
use ZxArt\Authors\Dbal\AuthorshipDbal;
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
        $authorshipDbal = $this->getService(AuthorshipDbal::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        if ($info = $authorshipDbal->getAuthorsInfo($this->id, $entityType)) {
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
        $authorshipDbal = $this->getService(AuthorshipDbal::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipDbal->getElementAuthorsRecords($this->id, $entityType);
    }

    public function getAuthorshipInfo($type)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        $authorshipDbal = $this->getService(AuthorshipDbal::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipDbal->getAuthorshipInfo($this->getId(), $entityType);
    }

    public function getAuthorshipRecords($type)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        $authorshipDbal = $this->getService(AuthorshipDbal::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        return $authorshipDbal->getAuthorshipRecords($this->getId(), $entityType);
    }

    /**
     * @return string[]
     */
    public function getAuthorRoles(): array
    {
        return AuthorshipRoles::LIST;
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
