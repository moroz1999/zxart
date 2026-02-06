<?php

declare(strict_types=1);

namespace ZxArt\Tests\Ratings;

use Cache;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureManager;
use userElement;
use ZxArt\Comments\CommentAuthorDto;
use ZxArt\Ratings\Dto\ElementRatingsListDto;
use ZxArt\Ratings\Dto\RecentRatingDto;
use ZxArt\Ratings\Dto\RecentRatingsListDto;
use ZxArt\Ratings\RatingsService;
use ZxArtItem;

class RatingsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private Cache&MockObject $cache;
    private Connection&MockObject $db;
    private RatingsService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->cache = $this->createMock(Cache::class);
        $this->db = $this->createMock(Connection::class);

        $this->service = new RatingsService(
            $this->structureManager,
            $this->cache,
            $this->db,
        );
    }

    public function testReturnsRecentRatingsFromCacheOnHit(): void
    {
        $cachedDto = new RecentRatingsListDto([
            new RecentRatingDto(
                user: new CommentAuthorDto(name: 'TestUser', url: '/user/1/', badges: []),
                rating: '5',
                targetTitle: 'Test Prod',
                targetUrl: '/prod/1/',
            ),
        ]);

        $this->cache->method('get')
            ->with('recent_ratings')
            ->willReturn($cachedDto);

        $this->db->expects($this->never())->method('table');

        $result = $this->service->getRecentRatings();

        $this->assertCount(1, $result->items);
        $this->assertSame('TestUser', $result->items[0]->user->name);
        $this->assertSame('5', $result->items[0]->rating);
    }

    public function testLoadsFromDbAndStoresCacheOnMiss(): void
    {
        $this->cache->method('get')
            ->with('recent_ratings')
            ->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')
            ->with('votes_history')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 5, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Cool Prod', '/prod/20/');
        $userMock = $this->createUserElementMock('Alice', '/user/10/', ['supporter']);

        $this->structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($targetElement, $userMock) {
                if ($id === 20) {
                    return $targetElement;
                }
                if ($id === 10) {
                    return $userMock;
                }
                return null;
            });

        $this->cache->expects($this->once())
            ->method('set')
            ->with('recent_ratings', $this->isInstanceOf(RecentRatingsListDto::class), 300);

        $result = $this->service->getRecentRatings();

        $this->assertCount(1, $result->items);
        $this->assertSame('Alice', $result->items[0]->user->name);
        $this->assertSame('/user/10/', $result->items[0]->user->url);
        $this->assertSame(['supporter'], $result->items[0]->user->badges);
        $this->assertSame('5', $result->items[0]->rating);
        $this->assertSame('Cool Prod', $result->items[0]->targetTitle);
        $this->assertSame('/prod/20/', $result->items[0]->targetUrl);
    }

    public function testSkipsElementsWithVotingDenied(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 5, 'date' => 1700000000],
            (object)['id' => 2, 'userId' => 10, 'elementId' => 30, 'value' => 3, 'date' => 1700000001],
        ]));

        $deniedTarget = $this->createTargetElementMock(true, 'Denied', '/denied/');
        $allowedTarget = $this->createTargetElementMock(false, 'Allowed', '/allowed/');
        $userMock = $this->createUserElementMock('Bob', '/user/10/', []);

        $this->structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($deniedTarget, $allowedTarget, $userMock) {
                if ($id === 20) {
                    return $deniedTarget;
                }
                if ($id === 30) {
                    return $allowedTarget;
                }
                if ($id === 10) {
                    return $userMock;
                }
                return null;
            });

        $result = $this->service->getRecentRatings();

        $this->assertCount(1, $result->items);
        $this->assertSame('Allowed', $result->items[0]->targetTitle);
    }

    public function testValueZeroConvertedToX(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 0, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Prod', '/prod/20/');
        $userMock = $this->createUserElementMock('Charlie', '/user/10/', []);

        $this->structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($targetElement, $userMock) {
                if ($id === 20) {
                    return $targetElement;
                }
                if ($id === 10) {
                    return $userMock;
                }
                return null;
            });

        $result = $this->service->getRecentRatings();

        $this->assertCount(1, $result->items);
        $this->assertSame('x', $result->items[0]->rating);
    }

    public function testRespectsLimit(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();

        $queryBuilder->expects($this->once())
            ->method('limit')
            ->with(5)
            ->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([]));

        $result = $this->service->getRecentRatings(5);

        $this->assertCount(0, $result->items);
    }

    public function testSkipsVotesWithMissingTargetElement(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 999, 'value' => 5, 'date' => 1700000000],
        ]));

        $this->structureManager->method('getElementById')->willReturn(null);

        $result = $this->service->getRecentRatings();

        $this->assertCount(0, $result->items);
    }

    public function testSkipsVotesWithMissingUserElement(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 999, 'elementId' => 20, 'value' => 4, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Some Prod', '/prod/20/');

        $this->structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($targetElement) {
                if ($id === 20) {
                    return $targetElement;
                }
                return null;
            });

        $result = $this->service->getRecentRatings();

        $this->assertCount(0, $result->items);
    }

    public function testGetElementRatingsReturnsVotesForElement(): void
    {
        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')
            ->with('votes_history')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 50, 'value' => 5, 'date' => 1700000000],
            (object)['id' => 2, 'userId' => 11, 'elementId' => 50, 'value' => 3, 'date' => 1699999000],
        ]));

        $user1 = $this->createUserElementMock('Alice', '/user/10/', ['supporter']);
        $user2 = $this->createUserElementMock('Bob', '/user/11/', []);

        $this->structureManager->method('getElementById')
            ->willReturnCallback(function (int $id) use ($user1, $user2) {
                if ($id === 10) {
                    return $user1;
                }
                if ($id === 11) {
                    return $user2;
                }
                return null;
            });

        $result = $this->service->getElementRatings(50);

        $this->assertInstanceOf(ElementRatingsListDto::class, $result);
        $this->assertCount(2, $result->items);
        $this->assertSame('Alice', $result->items[0]->user->name);
        $this->assertSame('5', $result->items[0]->rating);
        $this->assertSame('Bob', $result->items[1]->user->name);
        $this->assertSame('3', $result->items[1]->rating);
    }

    public function testGetElementRatingsSkipsMissingUsers(): void
    {
        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 999, 'elementId' => 50, 'value' => 5, 'date' => 1700000000],
        ]));

        $this->structureManager->method('getElementById')->willReturn(null);

        $result = $this->service->getElementRatings(50);

        $this->assertCount(0, $result->items);
    }

    public function testGetElementRatingsReturnsEmptyForNoVotes(): void
    {
        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('where')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([]));

        $result = $this->service->getElementRatings(50);

        $this->assertCount(0, $result->items);
    }

    private function createTargetElementMock(bool $votingDenied, string $title, string $url): ZxArtItem&MockObject
    {
        $element = $this->createMock(ZxArtItem::class);

        $element->method('getTitle')->willReturn($title);
        $element->method('isVotingDenied')->willReturn($votingDenied);
        $element->method('getUrl')->willReturn($url);

        return $element;
    }

    private function createUserElementMock(string $userName, string $url, array $badges): userElement&MockObject
    {
        $user = $this->createMock(userElement::class);

        $user->method('getTitle')->willReturn($userName);
        $user->method('getUrl')->willReturn($url);
        $user->method('getBadgetTypes')->willReturn($badges);

        return $user;
    }
}
