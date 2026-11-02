<?php

declare(strict_types=1);

namespace ZxArt\Tests\Integration\Entities;

require_once __DIR__ . '/EntityWriteTestCase.php';

use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Authors\UseCases\CreateAuthorCommand;
use ZxArt\Authors\UseCases\CreateAuthorHandler;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\UseCases\CreateProdCommand;
use ZxArt\Prods\UseCases\CreateProdHandler;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityMentionLookup;
use ZxArt\Shared\References\MentionedEntityDto;

/**
 * Mentions of entities the test wrote, looked up in the same process.
 */
#[RunTestsInSeparateProcesses]
final class EntityMentionLookupTest extends EntityWriteTestCase
{
    public function testMentionsAnswerTheTitlesAndPagesOfWhatExists(): void
    {
        $this->signIn([self::PUBLIC_VISITORS, self::REGISTERED_USERS]);
        $prodId = $this->container->get(CreateProdHandler::class)->handle(new CreateProdCommand(new ProdFieldsDto(title: 'Rock & Roll')))->id;
        $authorId = $this->container->get(CreateAuthorHandler::class)->handle(new CreateAuthorCommand(new AuthorFieldsDto(title: 'Goldmaster Writer')))->id;

        self::assertEquals([
            "prod:$prodId" => new MentionedEntityDto('Rock & Roll', "/prod/$prodId"),
            "author:$authorId" => new MentionedEntityDto('Goldmaster Writer', "/author/$authorId"),
        ], $this->container->get(EntityMentionLookup::class)->lookup([
            new EntityMentionDto(EntityType::Prod, $prodId),
            new EntityMentionDto(EntityType::Author, $authorId),
            new EntityMentionDto(EntityType::Prod, $authorId),
        ]));
    }
}
