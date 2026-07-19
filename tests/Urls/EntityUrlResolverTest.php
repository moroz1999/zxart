<?php

declare(strict_types=1);

namespace Tests\Urls;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Shared\StructureType;
use ZxArt\Urls\EntityUrlResolver;

class EntityUrlResolverTest extends TestCase
{
    #[DataProvider('sectionUrls')]
    public function testLegacySectionTypeResolvesToSpaUrl(StructureType $structureType, string $expectedUrl): void
    {
        $resolver = new EntityUrlResolver();

        self::assertSame($expectedUrl, $resolver->resolveByType($structureType->value, 1));
    }

    public static function sectionUrls(): iterable
    {
        yield [StructureType::AuthorsCatalogue, '/authors'];
        yield [StructureType::GroupsCatalogue, '/groups'];
        yield [StructureType::PartiesCatalogue, '/parties'];
        yield [StructureType::ZxProdCategoriesCatalogue, '/prods'];
        yield [StructureType::PicturesCatalogue, '/pictures'];
        yield [StructureType::MusicCatalogue, '/music'];
        yield [StructureType::CountriesList, '/geo'];
        yield [StructureType::Stats, '/stats'];
        yield [StructureType::CommentsList, '/comments'];
        yield [StructureType::Feedback, '/feedback'];
        yield [StructureType::Registration, '/register'];
        yield [StructureType::UserPlaylists, '/playlists'];
    }
}
