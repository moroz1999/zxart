<?php
declare(strict_types=1);


namespace ZxArt\Authors\Repositories;

use Illuminate\Database\Connection;
use JsonException;
use \structureManager;

final class AuthorshipRepository
{
    public function __construct(
        protected Connection       $db,
        protected structureManager $structureManager,
    )
    {

    }

    public function getAuthorsInfo(int|string $elementId, string $type): array
    {
        $info = [];
        if ($records = $this->getElementAuthorsRecords($elementId, $type)
        ) {
            foreach ($records as $record) {
                if ($authorElement = $this->structureManager->getElementById($record['authorId'])) {
                    $record['authorElement'] = $authorElement;
                    $info[] = $record;
                }
            }
        }
        return $info;
    }

    public function getElementAuthorsRecords(int|string $elementId, $type = null): array
    {
        $query = $this->db
            ->table('authorship')
            ->select('id', 'authorId', 'startDate', 'endDate', 'roles')
            ->where('elementId', '=', $elementId);
        if ($type) {
            $query->where('type', '=', $type);
        }

        if ($records = $query->get()) {
            foreach ($records as &$record) {
                if ($record['startDate']) {
                    $record['startDate'] = date('d.m.Y', $record['startDate']);
                } else {
                    $record['startDate'] = '';
                }
                if ($record['endDate']) {
                    $record['endDate'] = date('d.m.Y', $record['endDate']);
                } else {
                    $record['endDate'] = '';
                }

                $record['roles'] = json_decode($record['roles'], true);
                if (!$record['roles']) {
                    $record['roles'] = ['unknown'];
                }
            }
        }
        return $records;
    }

    public function getAuthorshipInfo(int $authorId, string $type): array
    {
        if ($records = $this->getAuthorshipRecords($authorId, $type)) {
            foreach ($records as $key => &$record) {
                if ($element = $this->structureManager->getElementById($record['elementId'])) {
                    $record[$type . 'Element'] = $element;
                } else {
                    unset($records[$key]);
                }
            }
        }
        return $records;
    }

    public function getAuthorshipRecords(int $authorId, $type = null): array
    {
        $query = $this->db
            ->table('authorship')
            ->select('elementId', 'startDate', 'endDate', 'roles')
            ->where('authorId', '=', $authorId);
        if ($type) {
            $query->where('type', '=', $type);
        }
        if ($records = $query->get()) {
            foreach ($records as &$record) {
                if ($record['startDate']) {
                    $record['startDate'] = date('d.m.Y', $record['startDate']);
                } else {
                    $record['startDate'] = '';
                }
                if ($record['endDate']) {
                    $record['endDate'] = date('d.m.Y', $record['endDate']);
                } else {
                    $record['endDate'] = '';
                }

                $record['roles'] = json_decode($record['roles'], true);
            }
        }
        return $records;
    }

    /**
     * @throws JsonException
     */
    public function checkAuthorship(int $elementId, int|string $authorId, string $type, array $roles = [], int|string|false $startDate = 0, int|string|false $endDate = 0): void
    {
        if ($existingRecord = $this->db
            ->table('authorship')
            ->select('roles')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type)
            ->first()
        ) {
            $existingRoles = json_decode($existingRecord['roles'], true, 512, JSON_THROW_ON_ERROR);

            $data = [
                'roles' => json_encode(
                    array_values(
                        array_unique(
                            array_merge($roles, $existingRoles)
                        )
                    ), JSON_THROW_ON_ERROR),
            ];
            if ($startDate) {
                $data['startDate'] = $startDate;
            }
            if ($endDate) {
                $data['endDate'] = $endDate;
            }
            $this->db
                ->table('authorship')
                ->where('elementId', '=', $elementId)
                ->where('authorId', '=', $authorId)
                ->update($data);
        } else {
            $data = [
                'elementId' => $elementId,
                'type' => $type,
                'authorId' => $authorId,
                'roles' => json_encode(array_unique($roles), JSON_THROW_ON_ERROR),
            ];
            if ($startDate) {
                $data['startDate'] = $startDate;
            }
            if ($endDate) {
                $data['endDate'] = $endDate;
            }

            $this->db
                ->table('authorship')
                ->insert($data);
        }
    }

    public function deleteAuthorship(int $elementId, $authorId, string $type): bool
    {
        if ($this->db
            ->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type)
            ->delete()
        ) {
            return true;
        }

        return false;
    }

    public function moveAuthorship(int $newElementId, array $recordIds): void
    {
        $this->db
            ->table('authorship')
            ->whereIn('id', $recordIds)
            ->update(
                [
                    'elementId' => $newElementId,
                ]
            );
    }

    public function checkDuplicates(array $info): array
    {
        if ($info && $records = $this->db
                ->table('module_authoralias')
                ->whereIn('id', array_keys($info))
                ->get(['id', 'authorId'])) {
            $foundAuthors = [];
            foreach ($records as $record) {
                if (isset($foundAuthors[$record['authorId']])) {
                    //this is not the only alias of same author within list, let's delete it
                    unset($info[$record['id']]);
                } else {
                    $foundAuthors[$record['authorId']] = true;
                    if (isset($info[$record['authorId']])) {
                        //main author should be removed if there is appropriate alias in list
                        if (!empty($info[$record['authorId']]['roles'])) {
                            $info[$record['id']]['roles'] = $info[$record['authorId']]['roles'];
                        }
                        unset($info[$record['authorId']]);
                    }
                }
            }
        }
        return $info;
    }

}