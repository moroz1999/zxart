<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use PHPUnit\Framework\TestCase;
use ZxArt\Shared\DescriptionFormatter;

final class DescriptionFormatterTest extends TestCase
{
    private DescriptionFormatter $formatter;

    protected function setUp(): void
    {
        $this->formatter = new DescriptionFormatter();
    }

    public function testDecodeResolvesNestedHtmlEntities(): void
    {
        $description = 'Author&amp;#039;s &amp;amp; notes';

        $result = $this->formatter->decode($description);

        $this->assertSame("Author's & notes", $result);
    }

    public function testDecodeRemovesPreTagsAndPreservesContent(): void
    {
        $description = "<pre class=\"legacy\">Line 1\nLine 2</pre>";

        $result = $this->formatter->decode($description);

        $this->assertSame("Line 1\nLine 2", $result);
    }
}
