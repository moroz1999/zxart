<?php

declare(strict_types=1);

namespace ZxArt\Tests\Shared;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;
use Symfony\Component\Serializer\SerializerInterface;
use ZxArt\Authors\Dto\AuthorAliasCreateDto;
use ZxArt\Feedback\Dto\FeedbackRequestDto;
use ZxArt\Playlists\Dto\PlaylistRenameRequestDto;
use ZxArt\Radio\Dto\RadioNextTuneRequestDto;
use ZxArt\Registration\Dto\RegistrationRequestDto;
use ZxArt\Shared\Serializer\RequestDenormalizerFactory;
use ZxArt\Tunes\Dto\TunePlayRequestDto;
use ZxArt\UserPreferences\Dto\PreferencesUpdateRequestDto;
use ZxArt\UserPreferences\Dto\PreferenceDto;
use ZxArt\Users\Dto\LoginRequestDto;
use ZxArt\Users\Dto\PasswordChangeRequestDto;
use ZxArt\Users\Dto\PasswordReminderRequestDto;
use ZxArt\Users\PasswordReminderAction;

final class RequestDtoSerializerTest extends TestCase
{
    private SerializerInterface $serializer;

    protected function setUp(): void
    {
        $this->serializer = RequestDenormalizerFactory::create();
    }

    public function testDeserializesFlatRequestDtos(): void
    {
        $login = $this->deserialize(
            '{"userName":"user","password":"secret","remember":true}',
            LoginRequestDto::class,
        );
        $feedback = $this->deserialize(
            '{"name":"User","email":"user@example.com","message":"Hello"}',
            FeedbackRequestDto::class,
        );
        $passwordChange = $this->deserialize(
            '{"currentPassword":"old","password":"new","passwordRepeat":"new"}',
            PasswordChangeRequestDto::class,
        );
        $alias = $this->deserialize(
            '{"authorId":4,"title":"Alias","startDate":"1990","endDate":"2000","displayInMusic":true,"displayInGraphics":false}',
            AuthorAliasCreateDto::class,
        );
        $playlist = $this->deserialize('{"id":3,"title":"Favourites"}', PlaylistRenameRequestDto::class);
        $tunePlay = $this->deserialize('{"tuneId":8,"context":"radio"}', TunePlayRequestDto::class);

        self::assertTrue($login->remember);
        self::assertSame('user@example.com', $feedback->email);
        self::assertSame('new', $passwordChange->password);
        self::assertSame(4, $alias->authorId);
        self::assertSame(3, $playlist->id);
        self::assertSame(8, $tunePlay->tuneId);
    }

    public function testDeserializesEnumAndOptionalRegistrationFields(): void
    {
        $passwordReminder = $this->deserialize(
            '{"action":"reset","email":"user@example.com","key":"token","password":"new","passwordRepeat":"new"}',
            PasswordReminderRequestDto::class,
        );
        $registration = $this->deserialize(
            '{"userName":"user","email":"user@example.com","password":"secret","passwordRepeat":"secret","firstName":"Ada"}',
            RegistrationRequestDto::class,
        );

        self::assertSame(PasswordReminderAction::Reset, $passwordReminder->action);
        self::assertSame('Ada', $registration->firstName);
        self::assertNull($registration->company);
    }

    public function testDeserializesNestedDtoCollections(): void
    {
        $preferences = $this->deserialize(
            '{"preferences":[{"code":"theme","value":"dark"}]}',
            PreferencesUpdateRequestDto::class,
        );
        $radio = $this->deserialize(
            '{"criteria":{"minRating":4.5,"yearsInclude":[2020,2021],"formatGroupsInclude":["ay"]}}',
            RadioNextTuneRequestDto::class,
        );

        self::assertInstanceOf(PreferenceDto::class, $preferences->preferences[0]);
        self::assertSame('dark', $preferences->preferences[0]->value);
        self::assertSame(4.5, $radio->criteria?->minRating);
        self::assertSame([2020, 2021], $radio->criteria?->yearsInclude);
        self::assertSame([], $radio->criteria?->countriesInclude);
    }

    public function testRejectsMalformedNestedDtoFields(): void
    {
        $this->expectException(SerializerException::class);
        $this->deserialize(
            '{"preferences":[{"code":42,"value":"dark"}]}',
            PreferencesUpdateRequestDto::class,
        );
    }

    /**
     * @template T of object
     * @param class-string<T> $requestClass
     * @return T
     */
    private function deserialize(string $body, string $requestClass): object
    {
        return $this->serializer->deserialize($body, $requestClass, 'json');
    }
}
