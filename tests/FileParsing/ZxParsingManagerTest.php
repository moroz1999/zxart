<?php

declare(strict_types=1);

namespace ZxArt\Tests\FileParsing;

use Illuminate\Database\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxArt\FileParsing\ZxParsingItem;
use ZxArt\FileParsing\ZxParsingItemContainer;
use ZxArt\FileParsing\ZxParsingManager;

#[CoversClass(ZxParsingManager::class)]
#[CoversClass(ZxParsingItemContainer::class)]
final class ZxParsingManagerTest extends TestCase
{
    /** @var list<string> */
    private array $paths = [];

    protected function tearDown(): void
    {
        foreach ($this->paths as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }
        $this->paths = [];
    }

    public function testTapeContainerExposesItsFiles(): void
    {
        $data = str_repeat("\x01", 32);
        $path = $this->writeTemporaryFile('game.tap', $this->tape('LOADER', 0, $data));

        $structure = $this->manager()->parseFileStructure($path, 'game.tap');

        $this->assertCount(1, $structure);
        $this->assertSame('tap', $structure[0]->getType());

        $items = $structure[0]->getItems();
        $this->assertCount(1, $items);
        $this->assertSame('LOADER.B', $items[0]->getItemName());
        $this->assertSame($data, $items[0]->getContent());
    }

    public function testArchiveContainerNestsItsDirectories(): void
    {
        $path = $this->writeTemporaryFile('release.tar', $this->archive([
            'readme.txt' => 'hello',
            'demo/loader.bas' => 'listing',
        ]));

        $structure = $this->manager()->parseFileStructure($path, 'release.tar');

        $this->assertSame('tar', $structure[0]->getType());

        $items = $structure[0]->getItems();
        $this->assertSame(['readme.txt', 'demo'], array_map(
            static fn(ZxParsingItem $item): ?string => $item->getItemName(),
            $items,
        ));
        $this->assertSame('file', $items[0]->getType());
        $this->assertSame('folder', $items[1]->getType());

        $nested = $items[1]->getItems();
        $this->assertCount(1, $nested);
        $this->assertSame('loader.bas', $nested[0]->getItemName());
        $this->assertSame('listing', $nested[0]->getContent());
    }

    public function testUnreadableContainerYieldsNoFiles(): void
    {
        $path = $this->writeTemporaryFile('broken.trd', str_repeat("\x00", 1024));

        $structure = $this->manager()->parseFileStructure($path, 'broken.trd');

        $this->assertSame('trd', $structure[0]->getType());
        $this->assertSame([], $structure[0]->getItems());
    }

    private function manager(): ZxParsingManager
    {
        return new ZxParsingManager($this->createStub(Connection::class));
    }

    private function writeTemporaryFile(string $name, string $contents): string
    {
        $path = sys_get_temp_dir() . '/' . uniqid('zxparsing', true) . '-' . $name;
        file_put_contents($path, $contents);
        $this->paths[] = $path;

        return $path;
    }

    /**
     * A TAP holding one ROM header block and the data block it announces.
     */
    private function tape(string $name, int $type, string $data): string
    {
        $header = chr($type)
            . str_pad(substr($name, 0, 10), 10)
            . pack('vvv', strlen($data), 10, strlen($data));

        return $this->tapeBlock("\x00" . $header) . $this->tapeBlock("\xFF" . $data);
    }

    private function tapeBlock(string $payload): string
    {
        $checksum = 0;
        foreach (str_split($payload) as $byte) {
            $checksum ^= ord($byte);
        }

        return pack('v', strlen($payload) + 1) . $payload . chr($checksum);
    }

    /**
     * A ustar archive with one 512 byte header per entry.
     *
     * @param array<string, string> $files
     */
    private function archive(array $files): string
    {
        $archive = '';
        foreach ($files as $path => $contents) {
            $archive .= $this->archiveHeader($path, strlen($contents))
                . str_pad($contents, (int)ceil(strlen($contents) / 512) * 512, "\x00");
        }

        return $archive . str_repeat("\x00", 1024);
    }

    private function archiveHeader(string $path, int $size): string
    {
        $header = str_pad($path, 100, "\x00")
            . str_pad('0000644', 8, "\x00")
            . str_pad('0000000', 8, "\x00")
            . str_pad('0000000', 8, "\x00")
            . str_pad(sprintf('%011o', $size), 12, "\x00")
            . str_pad('00000000000', 12, "\x00")
            . '        '
            . '0'
            . str_repeat("\x00", 100)
            . "ustar\x0000";
        $header = str_pad($header, 512, "\x00");

        $checksum = 0;
        foreach (str_split($header) as $byte) {
            $checksum += ord($byte);
        }

        return substr_replace($header, sprintf('%06o', $checksum) . "\x00 ", 148, 8);
    }
}
