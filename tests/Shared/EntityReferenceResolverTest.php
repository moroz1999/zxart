<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use authorElement;
use groupAliasElement;
use groupElement;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use ZxArt\Hardware\Dto\HardwareItemDto;
use ZxArt\Hardware\HardwareCatalogService;
use ZxArt\Shared\References\EntityReferenceResolver;
use ZxArt\Shared\References\InvalidReferenceException;

final class EntityReferenceResolverTest extends TestCase
{
    public function testLoadsElementsOfTheAllowedTypesInTheGivenOrder(): void
    {
        $group = $this->createStub(groupElement::class);
        $alias = $this->createStub(groupAliasElement::class);

        $elements = $this->createResolver([5 => $group, 6 => $alias])
            ->elements([6, 5], [groupElement::class, groupAliasElement::class], 'groups');

        self::assertSame([$alias, $group], $elements);
    }

    public function testElementOfAnotherTypeIsInvalid(): void
    {
        $resolver = $this->createResolver([5 => $this->createStub(authorElement::class)]);

        $this->expectException(InvalidReferenceException::class);
        $this->expectExceptionMessage('Element 5 cannot be used as groups');
        $resolver->elements([5], [groupElement::class], 'groups');
    }

    public function testMissingElementIsInvalid(): void
    {
        $this->expectException(InvalidReferenceException::class);
        $this->createResolver()->elements([5], [groupElement::class], 'groups');
    }

    public function testElementLoadsOneElement(): void
    {
        $group = $this->createStub(groupElement::class);

        self::assertSame($group, $this->createResolver([5 => $group])->element(5, [groupElement::class], 'group'));
    }

    public function testKnownHardwareCodesPass(): void
    {
        $this->createResolver()->assertHardwareCodes(['zx48']);
        $this->addToAssertionCount(1);
    }

    public function testUnknownHardwareCodeIsInvalid(): void
    {
        $this->expectException(InvalidReferenceException::class);
        $this->expectExceptionMessage('Unknown hardware code amiga');
        $this->createResolver()->assertHardwareCodes(['zx48', 'amiga']);
    }

    public function testKnownLanguageCodesPass(): void
    {
        $this->createResolver()->assertLanguageCodes(['en', 'ru']);
        $this->addToAssertionCount(1);
    }

    public function testUnknownLanguageCodeIsInvalid(): void
    {
        $this->expectException(InvalidReferenceException::class);
        $this->createResolver()->assertLanguageCodes(['xx']);
    }

    /**
     * @param array<int, structureElement> $elements
     */
    private function createResolver(array $elements = []): EntityReferenceResolver
    {
        $structureManager = $this->createStub(structureManager::class);
        $structureManager->method('getElementById')->willReturnCallback(
            static fn(int $id): ?structureElement => $elements[$id] ?? null,
        );
        $hardware = $this->createStub(HardwareCatalogService::class);
        $hardware->method('getItemByCode')->willReturnCallback(
            fn(string $code): ?HardwareItemDto => $code === 'zx48' ? $this->createStub(HardwareItemDto::class) : null,
        );

        return new EntityReferenceResolver($structureManager, $hardware);
    }
}
