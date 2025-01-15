<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;

trait AuthorshipPersister
{
    public function persistAuthorship($type): void
    {
        /**
         * @var AuthorshipRepository $authorshipRepository
         */
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $existingRecords = $authorshipRepository->getElementAuthorsRecords($this->id, $type);

        $rolesInfo = $this->getValue('addAuthorRole');
        $startDates = $this->getValue('addAuthorStartDate');
        $endDates = $this->getValue('addAuthorEndDate');
        if ($addAuthorId = $this->getValue('addAuthor')) {
            $authorshipRepository->saveAuthorship(
                $this->getId(),
                $addAuthorId,
                $type,
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
                $this->getId(),
                $authorId,
                $type,
                $roles,
                $startDate,
                $endDate
            );
        }
        foreach ($existingRecords as $record) {
            if (!isset($info[$record['authorId']])) {
                $authorshipRepository->deleteAuthorship($this->id, $record['authorId'], $type);
            }
        }
    }
}