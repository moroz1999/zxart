<?php

declare(strict_types=1);

namespace ZxArt\Tests\Comments;

use Cache;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ZxArt\Comments\CommentDto;
use ZxArt\Comments\LatestCommentsCache;

#[AllowMockObjectsWithoutExpectations]
final class LatestCommentsCacheTest extends TestCase
{
    private Cache&MockObject $cache;
    private LatestCommentsCache $latestCommentsCache;

    protected function setUp(): void
    {
        $this->cache = $this->createMock(Cache::class);
        $languagesManager = $this->createMock(LanguagesManager::class);
        $languagesManager->method('getLanguagesIdsMap')->willReturn(['eng' => 2105, 'rus' => 930, 'spa' => 84102]);

        $this->latestCommentsCache = new LatestCommentsCache($this->cache, $languagesManager);
    }

    public function testStoresListUnderLanguageKey(): void
    {
        $comments = [new CommentDto(1, null, '', 'text', 'text', '', false, false)];

        $this->cache->expects($this->once())
            ->method('set')
            ->with('latest_comments_eng', $comments, 300);

        $this->latestCommentsCache->set('eng', $comments);
    }

    public function testReturnsStoredList(): void
    {
        $comments = [new CommentDto(1, null, '', 'text', 'text', '', false, false)];
        $this->cache->method('get')->with('latest_comments_rus')->willReturn($comments);

        $this->assertSame($comments, $this->latestCommentsCache->get('rus'));
    }

    public function testReturnsNullOnMiss(): void
    {
        $this->cache->method('get')->willReturn(null);

        $this->assertNull($this->latestCommentsCache->get('rus'));
    }

    public function testIgnoresUnknownLanguage(): void
    {
        $this->cache->expects($this->never())->method('get');
        $this->cache->expects($this->never())->method('set');

        $this->latestCommentsCache->set('xyz', []);
        $this->assertNull($this->latestCommentsCache->get('xyz'));
    }

    public function testClearDropsEveryLanguage(): void
    {
        $deleted = [];
        $this->cache->method('delete')->willReturnCallback(function (string $key) use (&$deleted) {
            $deleted[] = $key;
            return null;
        });

        $this->latestCommentsCache->clear();

        $this->assertSame(['latest_comments_eng', 'latest_comments_rus', 'latest_comments_spa'], $deleted);
    }
}
