<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

require_once __DIR__ . '/../Doubles/Elements/ZxProdElementSpy.php';

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\Elements\ElementFields;
use ZxArt\Tests\Doubles\Elements\ZxProdElementSpy;

final class ElementFieldsTest extends TestCase
{
    public function testAssignsGivenValuesAndSkipsNulls(): void
    {
        $element = new ZxProdElementSpy();

        ElementFields::assign($element, ['title' => 'Dizzy', 'year' => null, 'groups' => [], 'compo' => '']);

        self::assertSame(['title' => 'Dizzy', 'groups' => [], 'compo' => ''], $element->values);
    }

    public function testFlagsAreStoredAsCheckboxValues(): void
    {
        $element = new ZxProdElementSpy();

        ElementFields::assign($element, ['denyVoting' => true, 'denyComments' => false]);

        self::assertSame(['denyVoting' => 1, 'denyComments' => 0], $element->values);
    }
}
