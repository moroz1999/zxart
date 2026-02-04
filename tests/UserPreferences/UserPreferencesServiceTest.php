<?php

declare(strict_types=1);

namespace ZxArt\Tests\UserPreferences;

use App\Users\CurrentUser;
use PHPUnit\Framework\TestCase;
use ZxArt\UserPreferences\DefaultUserPreferencesProvider;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceCodeException;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceValueException;
use ZxArt\UserPreferences\Domain\Preference;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\Domain\UserPreferenceValue;
use ZxArt\UserPreferences\Dto\PreferenceDto;
use ZxArt\UserPreferences\PreferenceValidator;
use ZxArt\UserPreferences\Repositories\PreferencesRepository;
use ZxArt\UserPreferences\Repositories\UserPreferenceValuesRepository;
use ZxArt\UserPreferences\UserPreferencesService;

class UserPreferencesServiceTest extends TestCase
{
    private function createUserMock(bool $isAuthorized = false, ?int $userId = null): CurrentUser
    {
        $user = $this->getMockBuilder(CurrentUser::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['isAuthorized', '__destruct', 'writeStorage'])
            ->getMock();

        $user->method('isAuthorized')->willReturn($isAuthorized);
        $user->storage = [];

        if ($userId !== null) {
            $user->id = $userId;
        }

        return $user;
    }

    public function testGetAllPreferencesReturnsDefaultsForAnonymousUser(): void
    {
        $user = $this->createUserMock(false);

        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $preferences = $service->getAllPreferences();

        $this->assertCount(1, $preferences);
        $this->assertInstanceOf(PreferenceDto::class, $preferences[0]);
        $this->assertSame('theme', $preferences[0]->code);
        $this->assertSame('light', $preferences[0]->value);
    }

    public function testGetAllPreferencesReturnsMergedPreferencesForLoggedInUser(): void
    {
        $user = $this->createUserMock(true, 5);

        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $preferencesRepo->method('findAll')->willReturn([
            new Preference(1, PreferenceCode::THEME, 'string'),
        ]);

        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);
        $valuesRepo->method('findByUserId')->with(5)->willReturn([
            new UserPreferenceValue(5, 1, 'dark'),
        ]);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $preferences = $service->getAllPreferences();

        $this->assertCount(1, $preferences);
        $this->assertSame('theme', $preferences[0]->code);
        $this->assertSame('dark', $preferences[0]->value);
    }

    public function testGetAllPreferencesUsesDefaultWhenUserHasNoValue(): void
    {
        $user = $this->createUserMock(true, 5);

        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $preferencesRepo->method('findAll')->willReturn([
            new Preference(1, PreferenceCode::THEME, 'string'),
        ]);

        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);
        $valuesRepo->method('findByUserId')->with(5)->willReturn([]);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $preferences = $service->getAllPreferences();

        $this->assertCount(1, $preferences);
        $this->assertSame('theme', $preferences[0]->code);
        $this->assertSame('light', $preferences[0]->value);
    }

    public function testSetPreferenceThrowsExceptionForInvalidCode(): void
    {
        $user = $this->createUserMock();
        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $this->expectException(InvalidPreferenceCodeException::class);
        $service->setPreference('invalid_code', 'value');
    }

    public function testSetPreferenceThrowsExceptionForInvalidValue(): void
    {
        $user = $this->createUserMock();
        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $this->expectException(InvalidPreferenceValueException::class);
        $service->setPreference('theme', 'invalid_value');
    }

    public function testSetPreferenceSavesValueForLoggedInUser(): void
    {
        $user = $this->createUserMock(true, 5);

        $preferencesRepo = $this->createMock(PreferencesRepository::class);
        $preferencesRepo->method('findByCode')
            ->with(PreferenceCode::THEME)
            ->willReturn(new Preference(1, PreferenceCode::THEME, 'string'));
        $preferencesRepo->method('findAll')->willReturn([
            new Preference(1, PreferenceCode::THEME, 'string'),
        ]);

        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);
        $valuesRepo->expects($this->once())
            ->method('upsert')
            ->with(5, 1, 'dark');
        $valuesRepo->method('findByUserId')->willReturn([
            new UserPreferenceValue(5, 1, 'dark'),
        ]);

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $preferences = $service->setPreference('theme', 'dark');

        $this->assertCount(1, $preferences);
        $this->assertSame('dark', $preferences[0]->value);
    }

    public function testSetPreferenceDoesNotSaveForAnonymousUser(): void
    {
        $user = $this->createUserMock(false);

        $preferencesRepo = $this->createMock(PreferencesRepository::class);

        $valuesRepo = $this->createMock(UserPreferenceValuesRepository::class);
        $valuesRepo->expects($this->never())->method('upsert');

        $service = new UserPreferencesService(
            $user,
            $preferencesRepo,
            $valuesRepo,
            new DefaultUserPreferencesProvider(),
            new PreferenceValidator(),
        );

        $preferences = $service->setPreference('theme', 'dark');

        $this->assertCount(1, $preferences);
        $this->assertSame('light', $preferences[0]->value);
    }
}
