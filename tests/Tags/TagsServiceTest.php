<?php

declare(strict_types=1);

namespace ZxArt\Tests\Tags;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use privilegesManager;
use structureElement;
use structureManager;
use ZxArt\Tags\Exception\TagsException;
use ZxArt\Tags\TagsService;
use zxPictureElement;

#[AllowMockObjectsWithoutExpectations]
class TagsServiceTest extends TestCase
{
    private structureManager&MockObject $structureManager;
    private privilegesManager&MockObject $privilegesManager;
    private TagsService $service;

    protected function setUp(): void
    {
        $this->structureManager = $this->createMock(structureManager::class);
        $this->privilegesManager = $this->createMock(privilegesManager::class);
        $this->service = new TagsService($this->structureManager, $this->privilegesManager);
    }

    public function testGetTagsReturnsSelectedAndSuggestedTags(): void
    {
        $element = new TestTagsPictureElement(
            testTagsList: [
                new TestTagElement(10, 'Demo &amp; Fun'),
            ],
            testSuggestedTags: [
                new TestTagElement(10, 'Demo &amp; Fun'),
                new TestTagElement(11, 'Scene', 'Top &amp; bright'),
            ],
        );

        $this->structureManager->method('getElementById')->with(42)->willReturn($element);
        $this->privilegesManager->method('checkPrivilegesForAction')->with(42, 'submitTags', 'zxPicture')->willReturn(true);

        $result = $this->service->getTags(42);

        $this->assertSame(42, $result->elementId);
        $this->assertCount(1, $result->tags);
        $this->assertSame('Demo & Fun', $result->tags[0]->title);
        $this->assertCount(1, $result->suggestedTags);
        $this->assertSame(11, $result->suggestedTags[0]->id);
        $this->assertSame('Scene', $result->suggestedTags[0]->title);
        $this->assertSame('Top & bright', $result->suggestedTags[0]->description);
    }

    public function testSaveTagsReturnsUpdatedTagsWhenStructureManagerReusesSameElementInstance(): void
    {
        $element = new TestTagsPictureElement(
            testTagsList: [
                new TestTagElement(31, 'Legacy'),
            ],
            testSuggestedTags: [
                new TestTagElement(31, 'Legacy'),
                new TestTagElement(41, 'Fresh'),
            ],
        );
        $element->setAvailableTagsByTitle(
            [
                'demo' => new TestTagElement(21, 'Demo'),
                'another' => new TestTagElement(22, 'Another'),
            ],
        );
        $element->setSuggestedTags(
            [
                new TestTagElement(21, 'Demo'),
                new TestTagElement(22, 'Another'),
                new TestTagElement(41, 'Fresh'),
            ],
        );

        $this->structureManager->expects($this->once())
            ->method('getElementById')
            ->with(42)
            ->willReturn($element);

        $this->privilegesManager->expects($this->once())
            ->method('checkPrivilegesForAction')
            ->with(42, 'submitTags', 'zxPicture')
            ->willReturn(true);

        $this->structureManager->expects($this->once())
            ->method('clearElementCache')
            ->with(42);

        $result = $this->service->saveTags(42, [
            ['title' => '  Demo '],
            ['title' => 'demo'],
            ['title' => ' Another'],
            ['title' => ''],
            ['other' => 'ignored'],
            'invalid',
        ]);

        $this->assertSame(['Demo', 'Another'], $element->updateTagsFromListTitles);
        $this->assertTrue($element->persistElementDataCalled);
        $this->assertCount(2, $result->tags);
        $this->assertSame('Demo', $result->tags[0]->title);
        $this->assertSame('Another', $result->tags[1]->title);
        $this->assertCount(1, $result->suggestedTags);
        $this->assertSame('Fresh', $result->suggestedTags[0]->title);
    }

    public function testGetTagsThrowsForbiddenWhenPrivilegeMissing(): void
    {
        $element = new TestTagsPictureElement();
        $this->structureManager->method('getElementById')->with(42)->willReturn($element);
        $this->privilegesManager->method('checkPrivilegesForAction')->with(42, 'submitTags', 'zxPicture')->willReturn(false);

        $this->expectException(TagsException::class);
        $this->expectExceptionMessage('Forbidden');

        $this->service->getTags(42);
    }
}

final class TestTagElement
{
    public function __construct(
        private int $id,
        public string $title,
        public ?string $description = null,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }
}

final class TestTagsPictureElement extends zxPictureElement
{
    public string $structureType = 'zxPicture';
    public string $tagsText = '';
    public bool $persistElementDataCalled = false;
    /** @var string[] */
    public array $updateTagsFromListTitles = [];
    /** @var array<string, TestTagElement> */
    private array $availableTagsByTitle = [];
    /** @var array<int, object>|null */
    private ?array $cachedTagsList = null;

    /**
     * @param array<int, object> $tagsList
     * @param array<int, object> $suggestedTags
     */
    public function __construct(
        private array $testTagsList = [],
        private array $testSuggestedTags = [],
    ) {
    }

    /**
     * @return array<int, object>
     */
    public function getTagsList(): array
    {
        if ($this->cachedTagsList === null) {
            $this->cachedTagsList = $this->testTagsList;
        }

        return $this->cachedTagsList;
    }

    /**
     * @return array<int, object>
     */
    public function getSuggestedTags(): array
    {
        return $this->testSuggestedTags;
    }

    public function updateTagsFromList(array $tagNames): void
    {
        $this->updateTagsFromListTitles = array_values(array_filter(array_map('trim', $tagNames), static fn(string $name) => $name !== ''));
        $updatedTags = [];

        foreach ($this->updateTagsFromListTitles as $title) {
            $normalizedTitle = mb_strtolower($title);
            if (isset($this->availableTagsByTitle[$normalizedTitle])) {
                $updatedTags[] = $this->availableTagsByTitle[$normalizedTitle];
            }
        }

        $this->testTagsList = $updatedTags;
        $this->resetTagsCache();
    }

    public function persistElementData(): void
    {
        $this->persistElementDataCalled = true;
    }

    /**
     * @param array<string, TestTagElement> $availableTagsByTitle
     */
    public function setAvailableTagsByTitle(array $availableTagsByTitle): void
    {
        $this->availableTagsByTitle = $availableTagsByTitle;
    }

    /**
     * @param array<int, object> $suggestedTags
     */
    public function setSuggestedTags(array $suggestedTags): void
    {
        $this->testSuggestedTags = $suggestedTags;
    }

    public function resetTagsCache(): void
    {
        $this->cachedTagsList = null;
    }
}
