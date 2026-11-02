<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Assistant;

require_once __DIR__ . '/../Entities/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Assistant\Tools\Catalogues\CategoriesDto;
use ZxArt\Assistant\Tools\Catalogues\CategoryDto;
use ZxArt\Assistant\Tools\Catalogues\FoundTagsDto;
use ZxArt\Assistant\Tools\Catalogues\HardwareGroupDto;
use ZxArt\Assistant\Tools\Catalogues\HardwareListDto;
use ZxArt\Assistant\Tools\Catalogues\HardwareOptionDto;
use ZxArt\Assistant\Tools\Catalogues\OptionsDto;
use ZxArt\Assistant\Tools\Search\FoundProdsDto;
use ZxArt\Assistant\Tools\Search\FoundAuthorsDto;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\Dto\FoundAuthorDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Groups\Dto\GroupCardDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Assistant\Tools\Search\FoundEntitiesDto;
use ZxArt\Assistant\Tools\JsonSchemaGenerator;
use ZxArt\Assistant\Tools\ToolRegistry;
use ZxArt\Assistant\Tools\ToolRequestBuilder;
use ZxArt\Groups\UseCases\CreateGroupCommand;
use ZxArt\Groups\UseCases\CreateGroupHandler;
use ZxArt\Prods\Dto\FoundProdDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Prods\UseCases\SetProdMemberCommand;
use ZxArt\Prods\UseCases\SetProdMemberHandler;
use ZxArt\Search\Dto\FoundEntityDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\Http\HttpRequestRefusedException;
use ZxArt\Shared\Images\ImageRefusedException;
use ZxArt\Tests\Integration\Entities\EntityWriteTestCase;

/**
 * A tool call as the assistant makes it: the tool resolved by name from the
 * container's registry, its arguments built from JSON.
 */
#[RunTestsInSeparateProcesses]
final class ToolCallTest extends EntityWriteTestCase
{
    private const int CATEGORY_DEMO = 92159;

    public function testASearchCallFindsWhatTheTestWrote(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Demo', year: 1999)))->id;

        $tool = $this->container->get(ToolRegistry::class)->get('search');
        $request = $this->container->get(ToolRequestBuilder::class)->build(
            $tool->requestClass(),
            '{"phrase": "Goldsearch", "types": ["prod"]}',
        );

        self::assertEquals(
            new FoundEntitiesDto([new FoundEntityDto(EntityType::Prod, $prodId, 'Goldsearch Demo', 1999, '')]),
            $tool->execute($request),
        );
    }

    public function testASearchAuthorsCallTakesANestedSoftwareFilterFromJson(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS], [['zxProd', 'publicReceive']]);
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldsearch Coder')))->id;
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Demo', year: 1999, legalStatus: LegalStatus::allowed)))->id;
        $this->container->get(SetProdMemberHandler::class)->handle(new SetProdMemberCommand($prodId, $authorId, ['code']));

        $tool = $this->container->get(ToolRegistry::class)->get('search_authors');
        $request = $this->container->get(ToolRequestBuilder::class)->build(
            $tool->requestClass(),
            '{"filter": {"name": "Goldsearch", "prodLink": "made", "prods": {"title": "goldsearch demo", "legalStatuses": ["allowed"]}}, "limit": 5}',
        );
        $result = $tool->execute($request);

        self::assertInstanceOf(FoundAuthorsDto::class, $result);
        self::assertSame(1, $result->total);
        self::assertSame([$authorId], array_map(static fn(FoundAuthorDto $author): int => $author->id, $result->results));
    }

    public function testTheCataloguesAnswerWithoutInput(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);

        $categories = $this->call('list_categories', '{}');
        $hardware = $this->call('list_hardware', '{}');
        $options = $this->call('list_options', '{}');

        self::assertInstanceOf(CategoriesDto::class, $categories);
        self::assertContains(self::CATEGORY_DEMO, array_map(static fn(CategoryDto $category): int => $category->id, $categories->categories));
        self::assertInstanceOf(HardwareListDto::class, $hardware);
        $codes = array_merge(...array_map(
            static fn(HardwareGroupDto $group): array => array_map(static fn(HardwareOptionDto $item): string => $item->code, $group->items),
            $hardware->groups,
        ));
        self::assertContains('zx48', $codes);
        self::assertInstanceOf(OptionsDto::class, $options);
        self::assertContains('tap', $options->releaseFormats);
    }

    public function testATagFoundByItsNameNarrowsTheProductionSearch(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Goldsearch Demo', tags: ['Goldtag Shimmer'])))->id;

        $tags = $this->call('find_tags', '{"phrase": "goldtag shim"}');
        self::assertInstanceOf(FoundTagsDto::class, $tags);
        self::assertCount(1, $tags->tags);
        $prods = $this->call('search_prods', '{"filter": {"tagIds": [' . $tags->tags[0]->id . ']}}');

        self::assertInstanceOf(FoundProdsDto::class, $prods);
        self::assertSame([$prodId], array_map(static fn(FoundProdDto $prod): int => $prod->id, $prods->results));
    }

    public function testAGetCallAnswersTheCardOfTheId(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldsearch Guild')))->id;

        $card = $this->call('get_group', '{"id": ' . $groupId . '}');

        self::assertInstanceOf(GroupCardDto::class, $card);
        self::assertSame($groupId, $card->id);
        self::assertSame('Goldsearch Guild', $card->fields->title);
    }

    public function testFetchUrlRefusesAnAddressInsideTheNetwork(): void
    {
        $this->expectException(HttpRequestRefusedException::class);

        $this->call('fetch_url', '{"url": "http://127.0.0.1/"}');
    }

    public function testViewImageRefusesAnIdThatIsNoFile(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $groupId = $this->container->get(CreateGroupHandler::class)->handle(new CreateGroupCommand(new GroupFieldsDto(title: 'Goldsearch Guild')))->id;
        $this->expectException(ImageRefusedException::class);

        $this->call('view_image', '{"fileId": ' . $groupId . '}');
    }

    public function testEveryToolHasASchemaForItsRequest(): void
    {
        $generator = $this->container->get(JsonSchemaGenerator::class);
        $names = [];
        foreach ($this->container->get(ToolRegistry::class)->all() as $tool) {
            self::assertSame('object', $generator->forClass($tool->requestClass())['type'], $tool->name());
            $names[] = $tool->name();
        }

        self::assertContains('set_group_alias_member', $names);
        self::assertContains('delete_release_file', $names);
    }

    private function call(string $toolName, string $arguments): object
    {
        $tool = $this->container->get(ToolRegistry::class)->get($toolName);

        return $tool->execute($this->container->get(ToolRequestBuilder::class)->build($tool->requestClass(), $arguments));
    }
}
