<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Previews;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\Previews\CreditChanges;
use ZxArt\Assistant\Tools\Previews\Mentions;
use ZxArt\Authors\Dto\AuthorshipDto;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityRefDto;
use ZxArt\Shared\References\EntityRefsReader;

final class CreditChangesTest extends TestCase
{
    public function testANewMemberShowsItselfItsRolesAndItsDates(): void
    {
        self::assertEquals(
            [
                new FieldChangeDto('author', null, new EntityMentionDto(EntityType::Author, 7)),
                new FieldChangeDto('roles', null, ['coder']),
                new FieldChangeDto('startDate', null, '1994-05-01'),
            ],
            $this->creditChanges()->set([], 7, ['coder'], (int)strtotime('1994-05-01 12:00'), 0),
        );
    }

    public function testAMemberAlreadyThereShowsOnlyWhatChanges(): void
    {
        $authorships = [new AuthorshipDto(42, 7, ['coder'], (int)strtotime('1994-05-01 12:00'), 0)];

        self::assertEquals(
            [new FieldChangeDto('endDate', null, '1999-12-31')],
            $this->creditChanges()->set($authorships, 7, ['coder'], null, (int)strtotime('1999-12-31 12:00')),
        );
    }

    public function testARemovedCreditShowsTheAuthorAndRolesGoing(): void
    {
        self::assertEquals(
            [
                new FieldChangeDto('author', new EntityMentionDto(EntityType::Author, 7), null),
                new FieldChangeDto('roles', ['coder'], null),
            ],
            $this->creditChanges()->remove([new AuthorshipDto(42, 7, ['coder'], 0, 0)], 7),
        );
    }

    private function creditChanges(): CreditChanges
    {
        $reader = $this->createStub(EntityRefsReader::class);
        $reader->method('read')->willReturn([new EntityRefDto(EntityType::Author, 7, 'Coder')]);

        return new CreditChanges(new Mentions($reader));
    }
}
