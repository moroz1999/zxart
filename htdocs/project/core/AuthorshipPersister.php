<?php

trait AuthorshipPersister
{
    public function persistAuthorship($type)
    {
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        $existingRecords = $authorsManager->getElementAuthorsRecords($this->id, $type);

        $rolesInfo = $this->getValue('addAuthorRole');
        $startDates = $this->getValue('addAuthorStartDate');
        $endDates = $this->getValue('addAuthorEndDate');
        if ($addAuthorId = $this->getValue('addAuthor')) {
            $authorsManager->checkAuthorship(
                $this->getId(),
                $addAuthorId,
                $type,
                json_encode($rolesInfo['new']),
                $startDates['new'],
                $endDates['new']
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
        $info = $authorsManager->checkDuplicates($info);
        foreach ($info as $authorId => $item) {
            $roles = !empty($item['roles']) ? $item['roles'] : ['unknown'];
            $startDate = !empty($item['startDate']) ? strtotime($item['startDate']) : 0;
            $endDate = !empty($item['endDate']) ? strtotime($item['endDate']) : 0;
            $authorsManager->checkAuthorship(
                $this->getId(),
                $authorId,
                $type,
                json_encode($roles),
                $startDate,
                $endDate
            );
        }
        foreach ($existingRecords as $record) {
            if (!isset($info[$record['authorId']])) {
                $authorsManager->deleteAuthorship($this->id, $record['authorId'], $type);
            }
        }
    }
}