<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

require_once __DIR__ . '/../Doubles/Time/FakeClock.php';

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use ZxArt\Releases\ItchIo\ItchIoThrottle;
use ZxArt\Tests\Doubles\Time\FakeClock;

class ItchIoThrottleTest extends TestCase
{
    public function testTheFirstRequestGoesAtOnce(): void
    {
        $clock = new FakeClock();

        (new ItchIoThrottle($clock))->beforeRequest();

        self::assertSame([], $clock->sleeps);
    }

    public function testTheNextRequestWaitsWhatIsLeftOfASecond(): void
    {
        $clock = new FakeClock();
        $throttle = new ItchIoThrottle($clock);

        $throttle->beforeRequest();
        $clock->advance(0.25);
        $throttle->beforeRequest();

        self::assertSame([0.75], $clock->sleeps);
    }

    public function testARequestASecondLaterGoesAtOnce(): void
    {
        $clock = new FakeClock();
        $throttle = new ItchIoThrottle($clock);

        $throttle->beforeRequest();
        $clock->advance(1.5);
        $throttle->beforeRequest();

        self::assertSame([], $clock->sleeps);
    }

    /**
     * @return iterable<string, array{string, float}>
     */
    public static function refusals(): iterable
    {
        yield 'seconds asked' => ['3', 3.0];
        yield 'nothing asked' => ['', 5.0];
        yield 'a date asked' => ['Wed, 21 Oct 2026 07:28:00 GMT', 5.0];
        yield 'too long asked' => ['120', 10.0];
    }

    #[DataProvider('refusals')]
    public function testARefusalWaitsWhatItAsksForUpToTenSeconds(string $retryAfter, float $expected): void
    {
        $clock = new FakeClock();

        (new ItchIoThrottle($clock))->afterRefusal($retryAfter);

        self::assertSame([$expected], $clock->sleeps);
    }
}
