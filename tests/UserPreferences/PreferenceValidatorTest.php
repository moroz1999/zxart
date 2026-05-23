<?php

declare(strict_types=1);

namespace ZxArt\Tests\UserPreferences;

use PHPUnit\Framework\TestCase;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceCodeException;
use ZxArt\UserPreferences\Domain\Exception\InvalidPreferenceValueException;
use ZxArt\UserPreferences\Domain\PreferenceCode;
use ZxArt\UserPreferences\PreferenceValidator;

final class PreferenceValidatorTest extends TestCase
{
    private PreferenceValidator $sut;

    protected function setUp(): void
    {
        $this->sut = new PreferenceValidator();
    }

    // --- validateCode ---

    public function testValidCodeReturnsEnum(): void
    {
        $this->assertSame(PreferenceCode::THEME, $this->sut->validateCode('theme'));
    }

    public function testInvalidCodeThrows(): void
    {
        $this->expectException(InvalidPreferenceCodeException::class);
        $this->sut->validateCode('does_not_exist');
    }

    // --- theme ---

    public function testValidThemeAccepted(): void
    {
        $this->assertSame('dark', $this->sut->validateValue(PreferenceCode::THEME, 'dark'));
    }

    public function testInvalidThemeThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::THEME, 'rainbow');
    }

    // --- startYearOffset ---

    public function testStartYearOffsetZeroAccepted(): void
    {
        $this->assertSame('0', $this->sut->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR, '0'));
    }

    public function testStartYearOffsetMaxAccepted(): void
    {
        $this->assertSame('10', $this->sut->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR, '10'));
    }

    public function testStartYearOffsetAboveMaxThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR, '11');
    }

    public function testStartYearOffsetNegativeThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::HOMEPAGE_NEW_PRODS_START_YEAR, '-1');
    }

    // --- pictureMode ---

    public function testPictureModeFlickerAccepted(): void
    {
        $this->assertSame('flicker', $this->sut->validateValue(PreferenceCode::PICTURE_MODE, 'flicker'));
    }

    public function testPictureModeInvalidThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::PICTURE_MODE, 'rainbow');
    }

    // --- binary flag ---

    public function testBinaryFlagZeroAccepted(): void
    {
        $this->assertSame('0', $this->sut->validateValue(PreferenceCode::PICTURE_BORDER, '0'));
    }

    public function testBinaryFlagOneAccepted(): void
    {
        $this->assertSame('1', $this->sut->validateValue(PreferenceCode::PICTURE_HIDDEN, '1'));
    }

    public function testBinaryFlagInvalidThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::PICTURE_BORDER, '2');
    }

    // --- language ---

    public function testLanguageCodeTwoLettersAccepted(): void
    {
        $this->assertSame('en', $this->sut->validateValue(PreferenceCode::LANGUAGE, 'en'));
    }

    public function testLanguageCodeThreeLettersAccepted(): void
    {
        $this->assertSame('rus', $this->sut->validateValue(PreferenceCode::LANGUAGE, 'rus'));
    }

    public function testLanguageCodeWithHyphenAndSuffixAccepted(): void
    {
        $this->assertSame('en-gb', $this->sut->validateValue(PreferenceCode::LANGUAGE, 'en-gb'));
    }

    public function testLanguageCodeUppercaseThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::LANGUAGE, 'EN');
    }

    public function testLanguageCodeTooShortThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::LANGUAGE, 'e');
    }

    public function testLanguageCodeWithDigitsThrows(): void
    {
        $this->expectException(InvalidPreferenceValueException::class);
        $this->sut->validateValue(PreferenceCode::LANGUAGE, 'en1');
    }
}
