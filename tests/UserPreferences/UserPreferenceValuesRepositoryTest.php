<?php

declare(strict_types=1);

namespace ZxArt\Tests\UserPreferences;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\UserPreferences\Domain\UserPreferenceValue;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;

class UserPreferenceValuesRepositoryTest extends TestCase
{
    public function testFindByUserIdReturnsValuesForUser(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('get')->willReturn(collect([
            (object)['user_id' => 5, 'preference_id' => 1, 'value' => 'dark'],
        ]));

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new UserPreferenceValuesRepository($db);
        $values = $repository->findByUserId(5);

        $this->assertCount(1, $values);
        $this->assertInstanceOf(UserPreferenceValue::class, $values[0]);
        $this->assertSame(5, $values[0]->userId);
        $this->assertSame(1, $values[0]->preferenceId);
        $this->assertSame('dark', $values[0]->value);
    }

    public function testFindByUserIdReturnsEmptyArrayWhenNoValues(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('get')->willReturn(collect([]));

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new UserPreferenceValuesRepository($db);
        $values = $repository->findByUserId(5);

        $this->assertSame([], $values);
    }

    public function testUpsertInsertsNewValue(): void
    {
        $updateOrInsertCalled = false;
        $updateOrInsertArgs = [];

        $db = $this->createMock(Connection::class);
        $db->method('table')
            ->willReturnCallback(function ($table) use (&$updateOrInsertCalled, &$updateOrInsertArgs) {
                $builder = new class($updateOrInsertCalled, $updateOrInsertArgs) {
                    public function __construct(
                        private bool &$updateOrInsertCalled,
                        private array &$updateOrInsertArgs
                    ) {}

                    public function updateOrInsert(array $attributes, array $values): bool
                    {
                        $this->updateOrInsertCalled = true;
                        $this->updateOrInsertArgs = [$attributes, $values];
                        return true;
                    }
                };
                return $builder;
            });

        $repository = new UserPreferenceValuesRepository($db);
        $repository->upsert(5, 1, 'dark');

        $this->assertTrue($updateOrInsertCalled);
        $this->assertSame(
            [['user_id' => 5, 'preference_id' => 1], ['value' => 'dark']],
            $updateOrInsertArgs
        );
    }
}
