<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Authors\Dto\AuthorAliasFieldsDto;
use ZxArt\Authors\Dto\AuthorAliasSaveRequestDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\AuthorSaveRequestDto;
use ZxArt\Authors\Dto\AuthorshipInputDto;
use ZxArt\Authors\Dto\MembershipInputDto;
use ZxArt\Groups\Dto\GroupAliasFieldsDto;
use ZxArt\Groups\Dto\GroupAliasSaveRequestDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Groups\Dto\GroupSaveRequestDto;
use ZxArt\Import\Dto\ImportOriginDto;
use ZxArt\Import\ImportOrigin;
use ZxArt\Prods\Dto\ProdBatchUploadRequestDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\Dto\ProdSaveRequestDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Releases\Dto\ReleaseCreateFromFilesRequestDto;
use ZxArt\Releases\Dto\ReleaseFieldsDto;
use ZxArt\Releases\Dto\ReleaseSaveRequestDto;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;

/**
 * The JSON part of an entity create or update request, denormalized the way the
 * data controllers read it. A list left out of the body stays `null` and keeps
 * what the entity holds; an empty list clears it.
 */
final class EntitySaveRequestDtoTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = RequestDenormalizerFactory::create();
    }

    public function testAnAuthorRequestCarriesTheFieldsRealNamesListsAndImageRemoval(): void
    {
        $request = $this->deserialize(AuthorSaveRequestDto::class, [
            'fields' => ['title' => 'Goldmaster Writer', 'countryId' => 47450, 'denyVoting' => true],
            'realNames' => ['1' => 'Johann', '2' => 'Иоганн'],
            'groups' => [['elementId' => 10, 'roles' => ['coder'], 'startDate' => 662688000, 'endDate' => 0]],
            'prods' => [['elementId' => 20, 'roles' => ['music']]],
            'importOrigins' => [['origin' => 'zxdb', 'importId' => '9001']],
            'removeImage' => true,
        ]);

        self::assertEquals(
            new AuthorSaveRequestDto(
                fields: new AuthorFieldsDto(title: 'Goldmaster Writer', countryId: 47450, denyVoting: true),
                realNames: [1 => 'Johann', 2 => 'Иоганн'],
                groups: [new MembershipInputDto(10, ['coder'], 662688000, 0)],
                prods: [new MembershipInputDto(20, ['music'])],
                importOrigins: [new ImportOriginDto(ImportOrigin::Zxdb, '9001')],
                removeImage: true,
            ),
            $request,
        );
    }

    public function testListsLeftOutStayNullAndEmptyListsStayEmpty(): void
    {
        $request = $this->deserialize(AuthorSaveRequestDto::class, ['fields' => [], 'groups' => []]);

        self::assertEquals(new AuthorFieldsDto(), $request->fields);
        self::assertSame([], $request->groups);
        self::assertNull($request->prods);
        self::assertNull($request->importOrigins);
        self::assertNull($request->realNames);
        self::assertFalse($request->removeImage);
    }

    public function testAnAuthorAliasRequest(): void
    {
        $request = $this->deserialize(AuthorAliasSaveRequestDto::class, [
            'fields' => ['title' => 'Wandering Goldmaster', 'authorId' => 4],
            'prods' => [['elementId' => 20]],
        ]);

        self::assertEquals(
            new AuthorAliasSaveRequestDto(
                fields: new AuthorAliasFieldsDto(title: 'Wandering Goldmaster', authorId: 4),
                prods: [new MembershipInputDto(20)],
            ),
            $request,
        );
    }

    public function testAGroupRequest(): void
    {
        $request = $this->deserialize(GroupSaveRequestDto::class, [
            'fields' => ['title' => 'Goldmaster Guild', 'subGroupIds' => [5, 6]],
            'members' => [['authorId' => 4, 'roles' => ['coder'], 'startDate' => 1, 'endDate' => 2]],
            'importOrigins' => [],
            'removeImage' => true,
        ]);

        self::assertEquals(
            new GroupSaveRequestDto(
                fields: new GroupFieldsDto(title: 'Goldmaster Guild', subGroupIds: [5, 6]),
                members: [new AuthorshipInputDto(4, ['coder'], 1, 2)],
                importOrigins: [],
                removeImage: true,
            ),
            $request,
        );
    }

    public function testAGroupAliasRequest(): void
    {
        $request = $this->deserialize(GroupAliasSaveRequestDto::class, [
            'fields' => ['title' => 'Goldmaster Crew', 'groupId' => 5],
            'members' => [['authorId' => 4]],
        ]);

        self::assertEquals(
            new GroupAliasSaveRequestDto(
                fields: new GroupAliasFieldsDto(title: 'Goldmaster Crew', groupId: 5),
                members: [new AuthorshipInputDto(4)],
            ),
            $request,
        );
    }

    public function testAProductionRequestAndABatchUploadRequest(): void
    {
        $authors = [['authorId' => 4, 'roles' => ['code']]];

        self::assertEquals(
            new ProdSaveRequestDto(
                fields: new ProdFieldsDto(title: 'Goldmaster Demo', legalStatus: LegalStatus::allowed, categoryIds: [92159]),
                authors: [new AuthorshipInputDto(4, ['code'])],
                importOrigins: [new ImportOriginDto(ImportOrigin::Vtrdos, 'gm-demo')],
            ),
            $this->deserialize(ProdSaveRequestDto::class, [
                'fields' => ['title' => 'Goldmaster Demo', 'legalStatus' => 'allowed', 'categoryIds' => [92159]],
                'authors' => $authors,
                'importOrigins' => [['origin' => 'vt', 'importId' => 'gm-demo']],
            ]),
        );
        self::assertEquals(
            new ProdBatchUploadRequestDto(
                fields: new ProdFieldsDto(year: 1999),
                authors: [new AuthorshipInputDto(4, ['code'])],
            ),
            $this->deserialize(ProdBatchUploadRequestDto::class, ['fields' => ['year' => 1999], 'authors' => $authors]),
        );
    }

    public function testAReleaseRequest(): void
    {
        $request = $this->deserialize(ReleaseSaveRequestDto::class, [
            'fields' => ['title' => 'Goldmaster Tape', 'prodId' => 7, 'version' => '1.0'],
            'authors' => [['authorId' => 4, 'roles' => ['release']]],
            'removeFile' => true,
        ]);

        self::assertEquals(
            new ReleaseSaveRequestDto(
                fields: new ReleaseFieldsDto(title: 'Goldmaster Tape', prodId: 7, version: '1.0'),
                authors: [new AuthorshipInputDto(4, ['release'])],
                removeFile: true,
            ),
            $request,
        );
    }

    public function testAReleasesFromFilesRequestCarriesNoPortalIds(): void
    {
        $request = $this->deserialize(ReleaseCreateFromFilesRequestDto::class, [
            'fields' => ['prodId' => 7],
            'authors' => [['authorId' => 4]],
        ]);

        self::assertEquals(
            new ReleaseCreateFromFilesRequestDto(
                fields: new ReleaseFieldsDto(prodId: 7),
                authors: [new AuthorshipInputDto(4)],
            ),
            $request,
        );
    }

    public function testAnUnknownPortalIsRejected(): void
    {
        $this->assertRejectedFor(
            ImportOrigin::class,
            ProdSaveRequestDto::class,
            ['fields' => [], 'importOrigins' => [['origin' => 'nowhere', 'importId' => '1']]],
        );
    }

    public function testAMembershipWithoutAnElementIsRejected(): void
    {
        $this->assertRejectedFor('elementId', AuthorSaveRequestDto::class, ['fields' => [], 'groups' => [['roles' => ['coder']]]]);
    }

    /**
     * @param class-string $class
     * @param array<string, mixed> $body
     */
    private function assertRejectedFor(string $reason, string $class, array $body): void
    {
        try {
            $this->deserialize($class, $body);
        } catch (SerializerException $exception) {
            self::assertStringContainsString($reason, $exception->getMessage());
            return;
        }
        self::fail('The body must be rejected');
    }

    /**
     * @template T of object
     * @param class-string<T> $class
     * @param array<string, mixed> $body
     * @return T
     */
    private function deserialize(string $class, array $body): object
    {
        $request = $this->serializer->deserialize(json_encode($body, JSON_THROW_ON_ERROR), $class, 'json');
        self::assertInstanceOf($class, $request);

        return $request;
    }
}
