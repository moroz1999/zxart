<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use PHPUnit\Framework\TestCase;
use ZxArt\Comments\CommentContentPurifier;

class CommentContentPurifierTest extends TestCase
{
    private CommentContentPurifier $purifier;

    protected function setUp(): void
    {
        $this->purifier = new CommentContentPurifier();
    }

    public function testKeepsTextAndDropsTags(): void
    {
        $this->assertSame('wide text', $this->purifier->purify('<img src="x.png">wide <b>text</b>'));
        $this->assertSame('nested text', $this->purifier->purify('<div onclick="x()">nested <span>text</span></div>'));
    }

    public function testDropsScriptAndStyleTogetherWithTheirText(): void
    {
        $this->assertSame('after', $this->purifier->purify('<script>alert(1)</script>after'));
        $this->assertSame('styled', $this->purifier->purify('<style>body{}</style>styled'));
    }

    public function testEscapesBareAngleBrackets(): void
    {
        $this->assertSame('5 &lt; 7', $this->purifier->purify('5 < 7'));
    }

    /** Line breaks carry the paragraph structure — the storage chunk turns them into `<br>`. */
    public function testKeepsLineBreaks(): void
    {
        $this->assertSame("line1\nline2", $this->purifier->purify("line1\nline2"));
    }

    /** URLs stay bare so that `commentElement::linkifyHtml()` owns link creation. */
    public function testKeepsUrlsUnlinked(): void
    {
        $this->assertSame(
            'see http://zxart.ee/prod/1',
            $this->purifier->purify('see http://zxart.ee/prod/1'),
        );
    }
}
