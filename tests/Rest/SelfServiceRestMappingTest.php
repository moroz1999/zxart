<?php

declare(strict_types=1);

namespace ZxArt\Tests\Rest;

use PHPUnit\Framework\TestCase;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Parties\Dto\PartyDto;
use ZxArt\Parties\Dto\PartyLocationItemDto;
use ZxArt\Parties\Rest\PartyRestDto;
use ZxArt\Playlists\Dto\PlaylistDto;
use ZxArt\Playlists\Rest\PlaylistRestDto;
use ZxArt\Press\Dto\PressDetailsDto;
use ZxArt\Press\Dto\PressMentionDto;
use ZxArt\Press\Dto\PressPublicationDto;
use ZxArt\Press\Dto\PressTagDto;
use ZxArt\Press\Rest\PressDetailsRestDto;
use ZxArt\Press\Rest\PressMentionRestDto;
use ZxArt\Press\Rest\PressPublicationRestDto;
use ZxArt\Press\Rest\PressTagRestDto;
use ZxArt\Registration\Dto\RegistrationResultDto;
use ZxArt\Registration\Rest\RegistrationResultRestDto;
use ZxArt\Users\Dto\PasswordReminderResultDto;
use ZxArt\Users\Dto\UserProfileDto;
use ZxArt\Users\Rest\PasswordReminderResultRestDto;
use ZxArt\Users\Rest\UserProfileRestDto;

final class SelfServiceRestMappingTest extends TestCase
{
    private ObjectMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new ObjectMapper();
    }

    public function testSimpleSelfServiceDtosMapToRestDtos(): void
    {
        self::assertInstanceOf(
            PasswordReminderResultRestDto::class,
            $this->mapper->map(new PasswordReminderResultDto(true, 'ok'), PasswordReminderResultRestDto::class),
        );
        self::assertInstanceOf(
            RegistrationResultRestDto::class,
            $this->mapper->map(new RegistrationResultDto(true, 'ok'), RegistrationResultRestDto::class),
        );
        self::assertInstanceOf(
            PlaylistRestDto::class,
            $this->mapper->map(new PlaylistDto(1, 'List', 2, 3, 4), PlaylistRestDto::class),
        );
        self::assertInstanceOf(
            UserProfileRestDto::class,
            $this->mapper->map(new UserProfileDto('user', 'user@example.com'), UserProfileRestDto::class),
        );
    }

    public function testPartyLocationsMapToRestDtos(): void
    {
        $source = new PartyDto(
            1,
            'Party',
            '/party/1',
            '2026',
            '/image.png',
            new PartyLocationItemDto('Country', '/geo/country/1'),
            new PartyLocationItemDto('City', '/geo/city/2'),
        );

        $result = $this->mapper->map($source, PartyRestDto::class);

        self::assertInstanceOf(PartyRestDto::class, $result);
        self::assertSame('Country', $result->country?->title);
        self::assertSame('City', $result->city?->title);
    }

    public function testPressCollectionsMapToRestDtos(): void
    {
        $mention = new PressMentionDto(1, 'Author', '/author/1');
        $publication = new PressPublicationDto(3, 'Magazine #1', '/prod/3', 1995, '/cover.png', [$mention]);
        $source = new PressDetailsDto(
            2,
            'Magazine #1: Article',
            'Article',
            '/press/2',
            null,
            null,
            null,
            [new PressTagDto('News', '/tags/news')],
            [$mention],
            [],
            [],
            [],
            [],
            [],
            [],
            $publication,
        );

        $result = $this->mapper->map($source, PressDetailsRestDto::class);

        self::assertInstanceOf(PressDetailsRestDto::class, $result);
        self::assertInstanceOf(PressTagRestDto::class, $result->tags[0]);
        self::assertInstanceOf(PressMentionRestDto::class, $result->authors[0]);
        self::assertInstanceOf(PressPublicationRestDto::class, $result->publication);
        self::assertInstanceOf(PressMentionRestDto::class, $result->publication->articles[0]);
    }
}
