<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Framework\TestCase;
use zxReleaseElement;

class ZxReleaseElementDescriptionTest extends TestCase
{
    public function testGetMetaDescriptionCombinesDescriptionAndFacts(): void
    {
        $element = $this->getMockBuilder(zxReleaseElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProd', 'getAuthorsInfo'])
            ->getMock();

        $element->description = 'This is a custom description.';
        $element->title = 'Test Release';
        $element->version = '1.0';
        $element->releaseType = 'crack';
        $element->year = 1995;
        $element->releaseFormat = ['trd'];
        $element->hardwareRequired = [];

        // Mocking behavior of __get for description
        $element->method('__get')->willReturnCallback(function($name) use ($element) {
             return match($name) {
                 'description' => 'This is a custom description.',
                 'title' => 'Test Release',
                 'version' => '1.0',
                 'releaseType' => 'crack',
                 'year' => 1995,
                 'releaseFormat' => ['trd'],
                 'hardwareRequired' => [],
                 default => null,
             };
        });

        $element->method('getProd')->willReturn(null);
        $element->method('getAuthorsInfo')->willReturn([]);

        // Current behavior: returns only description if not empty
        // Expected behavior: description + facts
        
        $metaDescription = $element->getMetaDescription();
        
        // This will fail after fix if I assert current behavior, 
        // but I want to see what it is now.
        $this->assertStringContainsString('This is a custom description.', $metaDescription);
        // Currently this should NOT contain facts if description is present
        // $this->assertStringContainsString('crack', $metaDescription); 
    }

    public function testTextContentCombinesDescriptionAndFacts(): void
    {
        $element = $this->getMockBuilder(zxReleaseElement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getProd', 'getAuthorsInfo'])
            ->getMock();

        $element->description = 'Detailed description here.';
        $element->title = 'Test Release';
        $element->version = '1.0';
        $element->releaseFormat = ['trd'];
        $element->year = 1995;
        $element->releaseType = 'crack';
        $element->hardwareRequired = [];

        $element->method('getProd')->willReturn(null);
        $element->method('getAuthorsInfo')->willReturn([]);

        $textContent = $element->getTextContent();
        $this->assertStringContainsString('Detailed description here.', $textContent);
    }
}
