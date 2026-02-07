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

        $this->assertCount(20, $preferences);
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

        $this->assertCount(20, $preferences);
        $themePreference = $this->findPreferenceByCode($preferences, 'theme');
        $this->assertNotNull($themePreference);
        $this->assertSame('dark', $themePreference->value);
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

        $this->assertCount(20, $preferences);
        $themePreference = $this->findPreferenceByCode($preferences, 'theme');
        $this->assertNotNull($themePreference);
        $this->assertSame('light', $themePreference->value);
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

        $this->assertCount(20, $preferences);
        $themePreference = $this->findPreferenceByCode($preferences, 'theme');
        $this->assertNotNull($themePreference);
        $this->assertSame('dark', $themePreference->value);
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

        $this->assertCount(20, $preferences);
        $themePreference = $this->findPreferenceByCode($preferences, 'theme');
        $this->assertNotNull($themePreference);
        $this->assertSame('light', $themePreference->value);
    }

    public function testValidateHomepageOrderAcceptsValidModuleIds(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_ORDER, 'newProds,newPictures,newTunes');
        $this->assertSame('newProds,newPictures,newTunes', $result);
    }

    public function testValidateHomepageOrderAcceptsEmptyString(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_ORDER, '');
        $this->assertSame('', $result);
    }

    public function testValidateHomepageOrderRejectsInvalidModuleId(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_ORDER, 'newProds,invalidModule');
    }

    public function testValidateHomepageDisabledAcceptsValidModuleIds(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_DISABLED, 'supportProds,unvotedTunes');
        $this->assertSame('supportProds,unvotedTunes', $result);
    }

    public function testValidateLimitAcceptsValidInteger(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT, '15');
        $this->assertSame('15', $result);
    }

    public function testValidateLimitRejectsZero(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT, '0');
    }

    public function testValidateLimitRejectsAboveMax(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT, '51');
    }

    public function testValidateLimitRejectsNonNumeric(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_LIMIT, 'abc');
    }

    public function testValidateMinRatingAcceptsValidFloat(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING, '3.5');
        $this->assertSame('3.5', $result);
    }

    public function testValidateMinRatingAcceptsZero(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING, '0');
        $this->assertSame('0', $result);
    }

    public function testValidateMinRatingAcceptsFive(): void
    {
        $validator = new PreferenceValidator();
        $result = $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING, '5');
        $this->assertSame('5', $result);
    }

    public function testValidateMinRatingRejectsAboveFive(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING, '5.1');
    }

    public function testValidateMinRatingRejectsNegative(): void
    {
        $validator = new PreferenceValidator();
        $this->expectException(InvalidPreferenceValueException::class);
        $validator->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_MIN_RATING, '-1');
    }

    /**
     * @param PreferenceDto[] $preferences
     */
    private function findPreferenceByCode(array $preferences, string $code): ?PreferenceDto
    {
        foreach ($preferences as $preference) {
            if ($preference->code === $code) {
                return $preference;
            }
        }
        return null;
    }
}
