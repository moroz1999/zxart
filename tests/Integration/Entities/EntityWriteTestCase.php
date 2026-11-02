<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

use App\Users\CurrentUser;
use App\Users\CurrentUserService;
use Cache;
use controller;
use DI\Container;
use Illuminate\Database\Connection;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use renderer;
use RuntimeException;
use structureManager;
use ZxArt\Import\Dbal\ImportOriginsDbal;
use ZxArt\Forms\FormCreateService;
use ZxArt\Forms\FormCreateType;

/**
 * Writes of an entity through its use cases and through the legacy forms,
 * checked against what the database then holds.
 *
 * The rows are read back directly: the structure manager, the links manager
 * and the collections keep what they loaded in memory, so the element a test
 * loads again would be the one it wrote, and another process does not see the
 * uncommitted transaction.
 *
 * Each test must run in its own process — a subclass carries
 * #[RunTestsInSeparateProcesses], which is not inherited — and in a transaction
 * that is rolled back. It runs as the `crontab` user, signed in through
 * {@see self::signIn()} with the groups and privileges it needs; the rollback
 * takes them back.
 */
abstract class EntityWriteTestCase extends TestCase
{
    protected const int PUBLIC_VISITORS = 487;
    /** Among others, adds authors, groups, their aliases, productions and releases. */
    protected const int REGISTERED_USERS = 2474;

    protected Container $container;
    protected Connection $db;
    protected structureManager $structureManager;
    private CurrentUser $user;
    private int $userId = 0;
    private int $lastElementId = 0;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = controller::getInstance()->getContainer();
        $this->db = $this->container->get(Connection::class);

        // the forms answer `{id}` instead of redirecting when the renderer is json
        renderer::getFactory()->setContainer($this->container);
        renderer::createInstance('json');
        $this->container->get(Cache::class)->enable(false, false, true);
        $this->user = $this->container->get(CurrentUserService::class)->getCurrentUser();
        $this->userId = (int)$this->user->checkUser('crontab', null, true);
        if ($this->userId === 0) {
            throw new RuntimeException('The crontab user is missing from the local database');
        }

        $this->db->beginTransaction();
        $this->lastElementId = (int)$this->db->table('structure_elements')->max('id');
    }

    #[\Override]
    protected function tearDown(): void
    {
        $this->db->rollBack();
        if ($this->db->table('structure_elements')->where('id', '>', $this->lastElementId)->exists()) {
            throw new RuntimeException('The test committed its writes: elements above ' . $this->lastElementId . ' remain');
        }
    }

    /**
     * Signs the user in as a member of the groups only, with the actions
     * allowed on the public root, and so on everything below it. The CMS works
     * a user's privileges out for each element as it loads it, so a test signs
     * in before it loads anything; the public root is then loaded, as a
     * request's path loads it.
     *
     * @param list<int> $groupIds
     * @param list<array{string, string}> $privilegesOnRoot structure type, action
     */
    protected function signIn(array $groupIds, array $privilegesOnRoot = []): void
    {
        $this->db->table('structure_links')
            ->where('childStructureId', '=', $this->userId)
            ->where('type', '=', 'userRelation')
            ->delete();
        foreach ($groupIds as $groupId) {
            $this->db->table('structure_links')->insert([
                'parentStructureId' => $groupId,
                'childStructureId' => $this->userId,
                'type' => 'userRelation',
            ]);
        }
        $rootId = (int)$this->db->table('structure_elements')->where('marker', '=', 'public_root')->value('id');
        $privilegesManager = $this->container->get(privilegesManager::class);
        foreach ($privilegesOnRoot as [$structureType, $action]) {
            $privilegesManager->setPrivilege($this->userId, $rootId, $structureType, $action, 'allow');
        }
        $this->user->switchUser($this->userId);

        // as in a public request: the services resolved after it work on the
        // public structure
        $this->structureManager = $this->container->get('publicStructureManager');
        $this->structureManager->getElementById($rootId);
    }

    /**
     * The add form as `/formdata/` submits it.
     *
     * @param array<array-key, mixed> $fields
     */
    protected function submitAddForm(FormCreateType $formType, array $fields, ?int $parentId = null): int
    {
        $element = $this->container->get(FormCreateService::class)
            ->submit($formType, null, controller::getInstance(), $fields, $parentId);

        return $element->getId();
    }

    /**
     * The element's row of a module table. A table keeps a row per language;
     * the fields a test checks are the same in each, so they must all agree.
     *
     * @param list<string> $columns
     * @return array<string, mixed>
     */
    protected function storedRow(string $table, int $elementId, array $columns): array
    {
        $rows = $this->db->table($table)->where('id', '=', $elementId)->distinct()->get($columns);
        self::assertCount(1, $rows, $table . ' holds differing rows for element ' . $elementId);

        return $rows[0];
    }

    /**
     * Per language, the value of a field translated per language.
     *
     * @return array<int, mixed> language id => value
     */
    protected function storedPerLanguage(string $table, int $elementId, string $column): array
    {
        $values = $this->db->table($table)->where('id', '=', $elementId)->pluck($column, 'languageId');
        ksort($values);

        return $values;
    }

    /**
     * The element that holds this one in the structure.
     *
     * @return array{id: int, structureType: string, structureName: string}
     */
    protected function structureParent(int $elementId): array
    {
        $parents = $this->db->table('structure_links')
            ->join('structure_elements', 'structure_elements.id', '=', 'structure_links.parentStructureId')
            ->where('structure_links.childStructureId', '=', $elementId)
            ->where('structure_links.type', '=', 'structure')
            ->get(['structure_elements.id', 'structure_elements.structureType', 'structure_elements.structureName']);
        self::assertCount(1, $parents, 'element ' . $elementId . ' must have one structure parent');

        return [
            'id' => (int)$parents[0]['id'],
            'structureType' => (string)$parents[0]['structureType'],
            'structureName' => (string)$parents[0]['structureName'],
        ];
    }

    /**
     * The letter element that holds an author, a group or their alias.
     *
     * @return array{structureType: string, structureName: string}
     */
    protected function letterOf(int $elementId): array
    {
        $parent = $this->structureParent($elementId);
        unset($parent['id']);

        return $parent;
    }

    protected function structureName(int $elementId): string
    {
        return (string)$this->db->table('structure_elements')->where('id', '=', $elementId)->value('structureName');
    }

    /**
     * @return list<int> the elements linked below this one by the link type, sorted
     */
    protected function linkedChildren(int $parentId, string $linkType): array
    {
        $ids = array_map('intval', $this->db->table('structure_links')
            ->where('parentStructureId', '=', $parentId)
            ->where('type', '=', $linkType)
            ->pluck('childStructureId'));
        sort($ids);

        return $ids;
    }

    /**
     * @return list<int> the elements this one is linked below by the link type, sorted
     */
    protected function linkedParents(int $childId, string $linkType): array
    {
        $ids = array_map('intval', $this->db->table('structure_links')
            ->where('childStructureId', '=', $childId)
            ->where('type', '=', $linkType)
            ->pluck('parentStructureId'));
        sort($ids);

        return $ids;
    }

    /**
     * A date as the forms store it: the timestamp of its midnight.
     */
    protected function date(string $date): int
    {
        return (int)strtotime($date);
    }

    /**
     * The actions the current user is allowed on the element itself.
     *
     * @return list<string> "type.action", sorted
     */
    protected function allowedOnElement(int $elementId): array
    {
        $rows = $this->db->table('privilege_relations')
            ->where('elementId', '=', $elementId)
            ->where('userId', '=', $this->userId)
            ->where('type', '=', 1)
            ->get(['module', 'action']);
        $allowed = array_map(static fn(array $row): string => $row['module'] . '.' . $row['action'], $rows);
        sort($allowed);

        return $allowed;
    }

    /**
     * @return list<string> the actions logged on the element, oldest first
     */
    protected function loggedActions(int $elementId): array
    {
        return array_values(array_map(
            'strval',
            $this->db->table('actions_log')->where('elementId', '=', $elementId)->orderBy('id')->pluck('action'),
        ));
    }

    /**
     * @return list<int> the elements created since the test began
     */
    protected function elementExists(int $elementId): bool
    {
        return $this->db->table('structure_elements')->where('id', '=', $elementId)->exists();
    }

    /**
     * @return list<array{authorId: int, elementId: int, type: string}>
     */
    protected function authorshipRows(int $authorId): array
    {
        return array_map(
            static fn(array $row): array => ['authorId' => (int)$row['authorId'], 'elementId' => (int)$row['elementId'], 'type' => (string)$row['type']],
            $this->db->table('authorship')->where('authorId', '=', $authorId)->orderBy('id')->get(['authorId', 'elementId', 'type']),
        );
    }

    /**
     * @return list<array{roles: mixed, startDate: int, endDate: int}>
     */
    protected function authorshipRecords(int $elementId, int $authorId, string $type): array
    {
        return array_map(
            static fn(array $row): array => [
                'roles' => json_decode((string)$row['roles'], true),
                'startDate' => (int)$row['startDate'],
                'endDate' => (int)$row['endDate'],
            ],
            $this->db->table('authorship')
                ->where('elementId', '=', $elementId)
                ->where('authorId', '=', $authorId)
                ->where('type', '=', $type)
                ->get(['roles', 'startDate', 'endDate']),
        );
    }

    /**
     * Moves the stored modification time back, so that a write is seen to move it.
     */
    protected function ageModificationTime(int $elementId): void
    {
        $this->db->table('structure_elements')->where('id', '=', $elementId)->update(['dateModified' => 1]);
    }

    protected function modificationTime(int $elementId): int
    {
        return (int)$this->db->table('structure_elements')->where('id', '=', $elementId)->value('dateModified');
    }

    protected function addAuthorship(int $authorId, int $elementId, string $type): void
    {
        $this->db->table('authorship')->insert([
            'authorId' => $authorId,
            'elementId' => $elementId,
            'roles' => '[]',
            'startDate' => 0,
            'endDate' => 0,
            'type' => $type,
        ]);
    }

    /**
     * @return list<array{origin: string, importId: string}> the portal ids the element carries, sorted
     */
    protected function storedImportOrigins(int $elementId): array
    {
        return $this->container->get(ImportOriginsDbal::class)->getElementOrigins($elementId);
    }

    /**
     * Fails the test unless the write throws; the caller then checks nothing
     * was written.
     *
     * @param class-string<\Throwable> $exceptionClass
     */
    protected function assertRefused(int $statusCode, string $exceptionClass, callable $write): void
    {
        try {
            $write();
        } catch (\Throwable $exception) {
            self::assertInstanceOf($exceptionClass, $exception, $exception->getMessage());
            self::assertSame($statusCode, $exception->getStatusCode(), $exception->getMessage());
            return;
        }
        self::fail('The write must be refused with ' . $statusCode);
    }

    protected function newElementIds(): array
    {
        return array_map(
            'intval',
            $this->db->table('structure_elements')->where('id', '>', $this->lastElementId)->orderBy('id')->pluck('id'),
        );
    }
}
