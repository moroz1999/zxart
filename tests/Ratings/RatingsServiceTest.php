<?php

declare(strict_types=1);

namespace ZxArt\Tests\Ratings;

use Cache;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use LanguagesManager;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use structureElement;
use structureManager;
use userElement;
use ZxArt\Comments\CommentAuthorDto;
use ZxArt\Ratings\Dto\ElementRatingsListDto;
use ZxArt\Ratings\Dto\RecentRatingDto;
use ZxArt\Ratings\Dto\RecentRatingsListDto;
use ZxArt\Ratings\RatingsService;
use ZxArt\Urls\EntityUrlResolver;
use ZxArtItem;

#[AllowMockObjectsWithoutExpectations]
class RatingsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private LanguagesManager&MockObject $languagesManager;
    private Cache&MockObject $cache;
    private Connection&MockObject $db;
    private EntityUrlResolver $entityUrlResolver;
    private RatingsService $service;

    private const int LANGUAGE_ID = 1;
    private const string CACHE_KEY = 'recent_ratings_' . self::LANGUAGE_ID;

    protected function setUp(): void
    {
        // urlFor() falls back to the element's own URL, which is what the element
        // mocks below provide
        $this->entityUrlResolver = $this->createMock(EntityUrlResolver::class);
        $this->entityUrlResolver->method('urlFor')
            ->willReturnCallback(static fn(structureElement $element): string => (string)$element->getUrl());
        // a user is linked to the page of the author they are connected to
        $this->entityUrlResolver->method('urlForUser')
            ->willReturnCallback(static function (userElement $user): ?string {
                $author = $user->getAuthorElement();
                return $author instanceof structureElement ? (string)$author->getUrl() : null;
            });
        $this->structureManager = $this->createMock(structureManager::class);
        $this->languagesManager = $this->createMock(LanguagesManager::class);
        $this->languagesManager->method('getCurrentLanguageId')->willReturn(self::LANGUAGE_ID);
        $this->cache = $this->createMock(Cache::class);
        $this->db = $this->createMock(Connection::class);

        $this->service = new RatingsService(
            $this->entityUrlResolver,
            $this->structureManager,
            $this->languagesManager,
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
            ->with(self::CACHE_KEY)
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
            ->with(self::CACHE_KEY)
            ->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')
            ->with('votes_history')
            ->willReturn($queryBuilder);

        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();
        $queryBuilder->method('offset')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 5, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Cool Prod', '/prod/20/');
        $userMock = $this->createUserElementMock('Alice', '/author/10/', ['supporter']);

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
            ->with(self::CACHE_KEY, $this->isInstanceOf(RecentRatingsListDto::class), 300);

        $result = $this->service->getRecentRatings();

        $this->assertCount(1, $result->items);
        $this->assertSame('Alice', $result->items[0]->user->name);
        $this->assertSame('/author/10/', $result->items[0]->user->url);
        $this->assertSame(['supporter'], $result->items[0]->user->badges);
        $this->assertSame('5', $result->items[0]->rating);
        $this->assertSame('Cool Prod', $result->items[0]->targetTitle);
        $this->assertSame('/prod/20/', $result->items[0]->targetUrl);
    }

    public function testUserWithoutAuthorHasNoUrl(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();
        $queryBuilder->method('offset')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 5, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Cool Prod', '/prod/20/');
        $userMock = $this->createAuthorlessUserElementMock('Nobody');

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
        $this->assertSame('Nobody', $result->items[0]->user->name);
        $this->assertNull($result->items[0]->user->url);
    }

    public function testSkipsElementsWithVotingDenied(): void
    {
        $this->cache->method('get')->willReturn(null);

        $queryBuilder = $this->createMock(Builder::class);
        $this->db->method('table')->willReturn($queryBuilder);
        $queryBuilder->method('select')->willReturnSelf();
        $queryBuilder->method('orderBy')->willReturnSelf();
        $queryBuilder->method('limit')->willReturnSelf();
        $queryBuilder->method('offset')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 5, 'date' => 1700000000],
            (object)['id' => 2, 'userId' => 10, 'elementId' => 30, 'value' => 3, 'date' => 1700000001],
        ]));

        $deniedTarget = $this->createTargetElementMock(true, 'Denied', '/denied/');
        $allowedTarget = $this->createTargetElementMock(false, 'Allowed', '/allowed/');
        $userMock = $this->createUserElementMock('Bob', '/author/10/', []);

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
        $queryBuilder->method('offset')->willReturnSelf();

        $queryBuilder->method('get')->willReturn(collect([
            (object)['id' => 1, 'userId' => 10, 'elementId' => 20, 'value' => 0, 'date' => 1700000000],
        ]));

        $targetElement = $this->createTargetElementMock(false, 'Prod', '/prod/20/');
        $userMock = $this->createUserElementMock('Charlie', '/author/10/', []);

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
            ->with(6)
            ->willReturnSelf();
        $queryBuilder->expects($this->once())
            ->method('offset')
            ->with(0)
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
        $queryBuilder->method('offset')->willReturnSelf();

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
        $queryBuilder->method('offset')->willReturnSelf();

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

        $user1 = $this->createUserElementMock('Alice', '/author/10/', ['supporter']);
        $user2 = $this->createUserElementMock('Bob', '/author/11/', []);

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

    /**
     * The recent-ratings list uses the user's own URL; per-element ratings link to
     * the user's connected author page instead, so both are stubbed.
     */
    /** A user linked to an author: the author's page is the user's public page. */
    private function createUserElementMock(string $userName, string $authorUrl, array $badges): userElement&MockObject
    {
        $author = $this->createMock(structureElement::class);
        $author->method('getUrl')->willReturn($authorUrl);

        $user = $this->createMock(userElement::class);
        $user->method('getTitle')->willReturn($userName);
        $user->method('getAuthorElement')->willReturn($author);
        $user->method('getBadgetTypes')->willReturn($badges);

        return $user;
    }

    /** A user with no author has no page to link to. */
    private function createAuthorlessUserElementMock(string $userName): userElement&MockObject
    {
        $user = $this->createMock(userElement::class);
        $user->method('getTitle')->willReturn($userName);
        $user->method('getAuthorElement')->willReturn(null);
        $user->method('getBadgetTypes')->willReturn([]);

        return $user;
    }
}
