<?php

declare(strict_types=1);

namespace ZxArt\Tests\Assistant\Tools\Previews;

use PHPUnit\Framework\TestCase;
use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Assistant\Tools\Previews\FieldChanges;
use ZxArt\Assistant\Tools\Previews\Mentions;
use ZxArt\Authors\Dto\AuthorFieldsDto;
use ZxArt\Groups\Dto\GroupFieldsDto;
use ZxArt\Prods\Dto\ProdFieldsDto;
use ZxArt\Prods\LegalStatus;
use ZxArt\Shared\EntityType;
use ZxArt\Shared\References\EntityMentionDto;
use ZxArt\Shared\References\EntityRefDto;
use ZxArt\Shared\References\EntityRefsReader;

final class FieldChangesTest extends TestCase
{
    public function testOnlyTheGivenFieldsThatDifferAreRows(): void
    {
        $before = new ProdFieldsDto(title: 'Demo', year: 1999, description: 'Old');
        $patch = new ProdFieldsDto(title: 'Demo', description: 'New');

        self::assertEquals(
            [new FieldChangeDto('description', 'Old', 'New')],
            $this->fieldChanges()->diff($before, $patch),
        );
    }

    public function testAnEnumShowsItsValue(): void
    {
        self::assertEquals(
            [new FieldChangeDto('legalStatus', null, 'allowed')],
            $this->fieldChanges()->diff(new ProdFieldsDto(), new ProdFieldsDto(legalStatus: LegalStatus::allowed)),
        );
    }

    public function testIdsAreMentionsOfWhatTheyName(): void
    {
        $before = new ProdFieldsDto(partyId: 0, groupIds: [10]);
        $patch = new ProdFieldsDto(partyId: 30, groupIds: [10, 20]);

        self::assertEquals(
            [
                new FieldChangeDto('partyId', null, new EntityMentionDto(EntityType::Party, 30)),
                new FieldChangeDto(
                    'groupIds',
                    [new EntityMentionDto(EntityType::Group, 10)],
                    [new EntityMentionDto(EntityType::Group, 10), new EntityMentionDto(EntityType::GroupAlias, 20)],
                ),
            ],
            $this->fieldChanges()->diff($before, $patch),
        );
    }

    public function testAnIdOfNothingStaysANumber(): void
    {
        self::assertEquals(
            [new FieldChangeDto('seriesProdIds', [], [99])],
            $this->fieldChanges()->diff(new ProdFieldsDto(seriesProdIds: []), new ProdFieldsDto(seriesProdIds: [99])),
        );
    }

    public function testWithoutABeforeEveryGivenFieldIsNew(): void
    {
        self::assertEquals(
            [new FieldChangeDto('title', null, 'Demo'), new FieldChangeDto('year', null, 1999)],
            $this->fieldChanges()->diff(null, new ProdFieldsDto(title: 'Demo', year: 1999)),
        );
    }

    public function testAnExternalIdIsNoMention(): void
    {
        self::assertEquals(
            [new FieldChangeDto('zxTunesId', 0, 10)],
            $this->fieldChanges()->diff(new AuthorFieldsDto(zxTunesId: 0), new AuthorFieldsDto(zxTunesId: 10)),
        );
    }

    public function testADateShowsAsADay(): void
    {
        self::assertEquals(
            [new FieldChangeDto('startDate', null, '1994-05-01'), new FieldChangeDto('endDate', '1999-12-31', null)],
            $this->fieldChanges()->diff(
                new GroupFieldsDto(startDate: 0, endDate: (int)strtotime('1999-12-31 12:00')),
                new GroupFieldsDto(startDate: (int)strtotime('1994-05-01 12:00'), endDate: 0),
            ),
        );
    }

    private function fieldChanges(): FieldChanges
    {
        $refs = [
            10 => new EntityRefDto(EntityType::Group, 10, 'Guild'),
            20 => new EntityRefDto(EntityType::GroupAlias, 20, 'Gld'),
            30 => new EntityRefDto(EntityType::Party, 30, 'Party'),
        ];
        $reader = $this->createStub(EntityRefsReader::class);
        $reader->method('read')->willReturnCallback(
            static fn(array $ids): array => array_values(array_filter(array_map(static fn(int $id) => $refs[$id] ?? null, $ids))),
        );

        return new FieldChanges(new Mentions($reader));
    }
}
