<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use PHPUnit\Framework\TestCase;
use ZxArt\Releases\ItchIo\ItchIoGameUrl;

class ItchIoGameUrlTest extends TestCase
{
    public function testKeepsCreatorAndGameOnly(): void
    {
        $game = ItchIoGameUrl::tryFrom('http://RetroSouls.itch.io/yazzie-junior-zx-spectrum/devlog/1?x=1');

        $this->assertSame('https://retrosouls.itch.io/yazzie-junior-zx-spectrum', $game?->gameUrl);
        $this->assertSame('https://retrosouls.itch.io/yazzie-junior-zx-spectrum/file/42', $game->getFileEndpoint(42));
    }

    public function testRejectsCreatorPageWithoutGame(): void
    {
        $this->assertNull(ItchIoGameUrl::tryFrom('https://retrosouls.itch.io/'));
    }

    public function testRejectsItchIoItselfAndOtherSites(): void
    {
        $this->assertNull(ItchIoGameUrl::tryFrom('https://itch.io/games/tag-zx-spectrum'));
        $this->assertNull(ItchIoGameUrl::tryFrom('https://example.com/game'));
        $this->assertNull(ItchIoGameUrl::tryFrom(''));
    }
}
