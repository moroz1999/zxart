<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Shared\EntityType;

trait AuthorshipPersister
{
    public function persistAuthorship(EntityType $entityType): void
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $existingRecords = $authorshipRepository->getElementAuthorsRecords($this->id, $entityType);

        $rolesInfo = $this->getValue('addAuthorRole');
        $startDates = $this->getValue('addAuthorStartDate');
        $endDates = $this->getValue('addAuthorEndDate');
        if ($addAuthorId = $this->getValue('addAuthor')) {
            $authorshipRepository->saveAuthorship(
                $this->getPersistedId(),
                $addAuthorId,
                $entityType,
                $rolesInfo['new'] ?? [],
                $startDates['new'] ?? 0,
                $endDates['new'] ?? 0
            );
            unset($rolesInfo['new']);
            unset($startDates['new']);
            unset($endDates['new']);
        }
        $info = [];
        if ($rolesInfo) {
            foreach ($rolesInfo as $authorId => $roles) {
                $info[$authorId]['roles'] = $roles;
            }
        }
        if ($startDates) {
            foreach ($startDates as $authorId => $startDate) {
                $info[$authorId]['startDate'] = $startDate;
            }
        }
        if ($endDates) {
            foreach ($endDates as $authorId => $endDate) {
                $info[$authorId]['endDate'] = $endDate;
            }
        }
        $info = $authorshipRepository->checkDuplicates($info);
        foreach ($info as $authorId => $item) {
            $roles = !empty($item['roles']) ? $item['roles'] : ['unknown'];
            $startDate = !empty($item['startDate']) ? strtotime($item['startDate']) : 0;
            $endDate = !empty($item['endDate']) ? strtotime($item['endDate']) : 0;
            $authorshipRepository->saveAuthorship(
                $this->getPersistedId(),
                $authorId,
                $entityType,
                $roles,
                $startDate,
                $endDate
            );
        }
        foreach ($existingRecords as $record) {
            if (!isset($info[$record['authorId']])) {
                $authorshipRepository->deleteAuthorship($this->getId(), $record['authorId'], $entityType);
            }
        }
    }

    /**
     * Inverse of {@see persistAuthorship}: persists what one author takes part
     * in - the groups they belong to, the productions they worked on. The
     * author form submits them per element id under `add<Type>Role`,
     * `add<Type>StartDate` and `add<Type>EndDate`. Records the form no longer
     * carries are deleted; those of the author's aliases are untouched.
     */
    public function persistMemberships(EntityType $entityType): void
    {
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $authorId = $this->getPersistedId();
        $existingRecords = $authorshipRepository->getAuthorshipRecords($authorId, $entityType);

        $prefix = 'add' . ucfirst($entityType->value);
        $rolesInfo = $this->getValue($prefix . 'Role');
        $startDates = $this->getValue($prefix . 'StartDate');
        $endDates = $this->getValue($prefix . 'EndDate');

        $info = [];
        if ($rolesInfo) {
            foreach ($rolesInfo as $elementId => $roles) {
                $info[$elementId]['roles'] = $roles;
            }
        }
        if ($startDates) {
            foreach ($startDates as $elementId => $startDate) {
                $info[$elementId]['startDate'] = $startDate;
            }
        }
        if ($endDates) {
            foreach ($endDates as $elementId => $endDate) {
                $info[$elementId]['endDate'] = $endDate;
            }
        }
        foreach ($info as $elementId => $item) {
            $roles = !empty($item['roles']) ? $item['roles'] : ['unknown'];
            $startDate = !empty($item['startDate']) ? strtotime($item['startDate']) : 0;
            $endDate = !empty($item['endDate']) ? strtotime($item['endDate']) : 0;
            $authorshipRepository->saveAuthorship(
                (int)$elementId,
                $authorId,
                $entityType,
                $roles,
                $startDate,
                $endDate
            );
        }
        foreach ($existingRecords as $record) {
            if (!isset($info[$record['elementId']])) {
                $authorshipRepository->deleteAuthorship((int)$record['elementId'], $authorId, $entityType);
            }
        }
    }
}
