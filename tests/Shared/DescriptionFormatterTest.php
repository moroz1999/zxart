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

    public function testDecodeKeepsPreformattedBlocksOfFormattedText(): void
    {
        // a listing inside a formatted description is part of the content
        $description = "<p>Issue 3</p>\n<pre>LINE 1\n  LINE 2</pre>";

        $result = $this->formatter->decode($description);

        $this->assertSame($description, $result);
    }
}
