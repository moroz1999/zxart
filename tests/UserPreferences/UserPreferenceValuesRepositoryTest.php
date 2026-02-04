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
        $upsertCalled = false;
        $upsertArgs = [];

        $db = $this->createMock(Connection::class);
        $db->method('table')
            ->willReturnCallback(function ($table) use (&$upsertCalled, &$upsertArgs) {
                $builder = new class($upsertCalled, $upsertArgs) {
                    public function __construct(
                        private bool &$upsertCalled,
                        private array &$upsertArgs
                    ) {}

                    public function upsert(array $values, array $uniqueBy, array $update): void
                    {
                        $this->upsertCalled = true;
                        $this->upsertArgs = [$values, $uniqueBy, $update];
                    }
                };
                return $builder;
            });

        $repository = new UserPreferenceValuesRepository($db);
        $repository->upsert(5, 1, 'dark');

        $this->assertTrue($upsertCalled);
        $this->assertSame(
            [[['user_id' => 5, 'preference_id' => 1, 'value' => 'dark']], ['user_id', 'preference_id'], ['value']],
            $upsertArgs
        );
    }
}
