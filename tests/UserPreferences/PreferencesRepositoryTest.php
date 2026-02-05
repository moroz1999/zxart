<?php

declare(strict_types=1);

namespace ZxArt\Tests\UserPreferences;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\TestCase;
use ZxArt\UserPreferences\Domain\Preference;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;

class PreferencesRepositoryTest extends TestCase
{
    public function testFindByCodeReturnsPreferenceWhenFound(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('first')->willReturn([
            'id' => 1,
            'code' => 'theme',
            'type' => 'string',
        ]);

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new PreferencesRepository($db);
        $preference = $repository->findByCode(PreferenceCode::THEME);

        $this->assertInstanceOf(Preference::class, $preference);
        $this->assertSame(1, $preference->id);
        $this->assertSame(PreferenceCode::THEME, $preference->code);
        $this->assertSame('string', $preference->type);
    }

    public function testFindByCodeReturnsNullWhenNotFound(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('first')->willReturn(null);

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new PreferencesRepository($db);
        $preference = $repository->findByCode(PreferenceCode::THEME);

        $this->assertNull($preference);
    }

    public function testFindAllReturnsAllPreferences(): void
    {
        $builder = $this->createMock(Builder::class);
        $builder->method('get')->willReturn(collect([
            ['id' => 1, 'code' => 'theme', 'type' => 'string'],
        ]));

        $db = $this->createMock(Connection::class);
        $db->method('table')->willReturn($builder);

        $repository = new PreferencesRepository($db);
        $preferences = $repository->findAll();

        $this->assertCount(1, $preferences);
        $this->assertInstanceOf(Preference::class, $preferences[0]);
        $this->assertSame(PreferenceCode::THEME, $preferences[0]->code);
    }
}
