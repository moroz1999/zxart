<?php
declare(strict_types=1);


namespace ZxArt\Authors\Dbal;

use Illuminate\Database\Connection;
use JsonException;
use RuntimeException;
use structureManager;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Shared\DatabaseTable;
use ZxArt\Shared\EntityType;

final class AuthorshipDbal
{
    public function __construct(
        protected Connection       $db,
        protected structureManager $structureManager,
    )
    {

    }

    public function getAuthorsInfo(int|string $elementId, EntityType $type): array
    {
        $info = [];
        $sort = [];
        if ($records = $this->getElementAuthorsRecords($elementId, $type)
        ) {
            foreach ($records as $record) {
                if ($authorElement = $this->structureManager->getElementById($record['authorId'])) {
                    $record['authorElement'] = $authorElement;
                    $sort[] = $authorElement->getTitle();
                    $info[] = $record;
                }
            }
            array_multisort($sort, SORT_ASC, $info);
        }
        return $info;
    }

    public function getElementAuthorsRecords(int|string $elementId, ?EntityType $type = null): array
    {
        $query = $this->db
            ->table('authorship')
            ->select('id', 'authorId', 'startDate', 'endDate', 'roles', 'type')
            ->where('elementId', '=', $elementId);
        if ($type !== null) {
            $query->where('type', '=', $type->value);
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

    public function getAuthorshipInfo(int $authorId, EntityType $type): array
    {
        if ($records = $this->getAuthorshipRecords($authorId, $type)) {
            foreach ($records as $key => &$record) {
                if ($element = $this->structureManager->getElementById($record['elementId'])) {
                    $record[$type->value . 'Element'] = $element;
                } else {
                    unset($records[$key]);
                }
            }
        }
        return $records;
    }

    public function getAuthorshipRecords(int $authorId, ?EntityType $type = null): array
    {
        $query = $this->db
            ->table('authorship')
            ->select('elementId', 'startDate', 'endDate', 'roles')
            ->where('authorId', '=', $authorId);
        if ($type !== null) {
            $query->where('type', '=', $type->value);
        }
        if ($records = $query->get()) {
            /** @var array<string, mixed> $record */
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

                $record['roles'] = $this->readStoredRoles(
                    (string)$record['roles'],
                    (int)$record['elementId'],
                    $authorId,
                    $type
                );
            }
        }
        return $records;
    }

    /**
     * @throws JsonException
     */
    public function addAuthorship(int $elementId, int|string $authorId, EntityType $type, array $roles = [], int|string|false $startDate = 0, int|string|false $endDate = 0): void
    {
        if ($existingRecord = $this->db
            ->table('authorship')
            ->select('roles')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type->value)
            ->first()
        ) {
            $existingRoles = $this->readStoredRoles((string)$existingRecord['roles'], $elementId, $authorId, $type);
            $allRoles = array_unique(array_merge($roles, $existingRoles));
            array_filter($allRoles, fn($role) => $role !== 'unknown');
            $this->updateRoles($elementId, (int)$authorId, $type->value, $allRoles, (int)$startDate, (int)$endDate);
        } else {
            $this->insertRoles($elementId, (int)$authorId, $type->value, $roles, (int)$startDate, (int)$endDate);
        }
    }

    /**
     * The authors of an element, or the members of a group, in the order they
     * were added.
     *
     * @return AuthorshipDto[]
     */
    public function findByElement(int $elementId, EntityType $type): array
    {
        return $this->findRecords('elementId', $elementId, $type);
    }

    /**
     * What an author took part in, of one type, in the order it was added.
     *
     * @return AuthorshipDto[]
     */
    public function findByAuthor(int $authorId, EntityType $type): array
    {
        return $this->findRecords('authorId', $authorId, $type);
    }

    /**
     * @return AuthorshipDto[]
     */
    private function findRecords(string $column, int $id, EntityType $type): array
    {
        /** @var list<array{elementId: int|string, authorId: int|string, roles: string|null, startDate: int|string|null, endDate: int|string|null}> $rows */
        $rows = $this->db
            ->table(DatabaseTable::Authorship->value)
            ->select(['elementId', 'authorId', 'roles', 'startDate', 'endDate'])
            ->where($column, '=', $id)
            ->where('type', '=', $type->value)
            ->orderBy('id')
            ->get();

        return array_map(fn(array $row) => new AuthorshipDto(
            elementId: (int)$row['elementId'],
            authorId: (int)$row['authorId'],
            roles: $this->readStoredRoleNames((string)$row['roles'], (int)$row['elementId'], $row['authorId'], $type),
            startDate: (int)$row['startDate'],
            endDate: (int)$row['endDate'],
        ), $rows);
    }

    /**
     * @return list<string>
     */
    private function readStoredRoleNames(string $storedRoles, int $elementId, int|string $authorId, EntityType $type): array
    {
        /** @var array<int|string> $roles */
        $roles = $this->readStoredRoles($storedRoles, $elementId, $authorId, $type);

        return array_values(array_map('strval', $roles));
    }

    /**
     * The `roles` column holds a JSON list, except in old rows where it is an
     * empty string standing for "no roles". Anything else there is broken data,
     * and a bare "Syntax error" from json_decode says nothing about which
     * record has to be repaired.
     *
     * @return array<array-key, mixed>
     */
    private function readStoredRoles(string $storedRoles, int $elementId, int|string $authorId, ?EntityType $type): array
    {
        if ($storedRoles === '') {
            return [];
        }

        try {
            /** @var mixed $decodedRoles */
            $decodedRoles = json_decode($storedRoles, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new RuntimeException(
                sprintf(
                    'Authorship of element %d and author %s (%s) holds unreadable roles: "%s"',
                    $elementId,
                    $authorId,
                    $type?->value ?? 'any type',
                    $storedRoles
                ),
                0,
                $exception
            );
        }

        return is_array($decodedRoles) ? $decodedRoles : [];
    }

    /**
     * @throws JsonException
     */
    public function saveAuthorship(int $elementId, int|string $authorId, EntityType $type, array $roles = [], int|string|false $startDate = 0, int|string|false $endDate = 0): void
    {
        if ($this->db
            ->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type->value)
            ->first()
        ) {
            $roles = array_filter($roles, fn($role) => $role !== 'unknown');
            $this->updateRoles($elementId, (int)$authorId, $type->value, $roles, (int)$startDate, (int)$endDate);
        } else {
            $this->insertRoles($elementId, (int)$authorId, $type->value, $roles, (int)$startDate, (int)$endDate);
        }
    }

    private function insertRoles(int $elementId, int $authorId, string $type, array $roles, int $startDate, int $endDate): void
    {
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

    /**
     * Makes the author's record on the element hold exactly these roles. A date
     * left `null` keeps the stored one (0 on a new record); 0 clears it.
     *
     * @param list<string> $roles
     * @throws JsonException
     */
    public function setAuthorship(int $elementId, int $authorId, EntityType $type, array $roles, ?int $startDate, ?int $endDate): void
    {
        $data = ['roles' => json_encode(array_values(array_unique($roles)), JSON_THROW_ON_ERROR)];
        if ($startDate !== null) {
            $data['startDate'] = $startDate;
        }
        if ($endDate !== null) {
            $data['endDate'] = $endDate;
        }
        $record = $this->db->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type->value);
        if ($record->exists()) {
            $record->update($data);
            return;
        }
        $this->db->table('authorship')->insert($data + [
            'elementId' => $elementId,
            'authorId' => $authorId,
            'type' => $type->value,
            'startDate' => 0,
            'endDate' => 0,
        ]);
    }

    public function deleteAuthorship(int $elementId, $authorId, EntityType $type): bool
    {
        if ($this->db
            ->table('authorship')
            ->where('elementId', '=', $elementId)
            ->where('authorId', '=', $authorId)
            ->where('type', '=', $type->value)
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

    private function updateRoles(int $elementId, int $authorId, string $type, array $roles, int $startDate, int $endDate): void
    {
        if ($roles === []) {
            $roles = ['unknown'];
        }
        $updatedRoles = array_values($roles);

        $data = [
            'roles' => json_encode($updatedRoles, JSON_THROW_ON_ERROR),
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
            ->where('type', '=', $type)
            ->update($data);
    }

}