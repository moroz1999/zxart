<?php

declare(strict_types=1);

namespace ZxArt\Tests\FileParsing;

use Illuminate\Database\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxArt\FileParsing\ZxParsingItemContainer;
use ZxArt\FileParsing\ZxParsingManager;

/**
 * The disk systems zx-files reads without an extension to go by: GDOS on an MGT or IMG
 * image, MDOS on a D40 or D80 one. Both are dumps with no header, built here as the
 * smallest disk each format allows.
 */
#[CoversClass(ZxParsingManager::class)]
#[CoversClass(ZxParsingItemContainer::class)]
final class ZxParsingDiskContainersTest extends TestCase
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

    public function testGDosDiskExposesItsCatalogue(): void
    {
        $program = str_repeat("\x0A", 120);
        $path = $this->writeTemporaryFile('demo.mgt', $this->gDosDisk('AUTOLOAD', $program));

        $structure = $this->manager()->parseFileStructure($path, 'demo.mgt');

        $this->assertSame('mgt', $structure[0]->getType());

        $items = $structure[0]->getItems();
        $this->assertCount(1, $items);
        $this->assertSame('AUTOLOAD', $items[0]->getItemName());
        // The nine byte GDOS header belongs to the catalogue, not to the file.
        $this->assertSame($program, $items[0]->getContent());
        $this->assertSame('zx_basic', $items[0]->getInternalType());
    }

    public function testMDosDiskExposesItsCatalogue(): void
    {
        $program = str_repeat("\x0A", 200);
        $path = $this->writeTemporaryFile('demo.d80', $this->mDosDisk('LOADER', $program));

        $structure = $this->manager()->parseFileStructure($path, 'demo.d80');

        $this->assertSame('d80', $structure[0]->getType());

        $items = $structure[0]->getItems();
        $this->assertCount(1, $items);
        // MDOS shows the type letter as an extension.
        $this->assertSame('LOADER.P', $items[0]->getItemName());
        $this->assertSame($program, $items[0]->getContent());
        $this->assertSame('zx_basic', $items[0]->getInternalType());
    }

    /** The same image under either drive's name: D40 reaches 40 tracks, D80 eighty. */
    public function testD40ImageIsReadAsAD80One(): void
    {
        $path = $this->writeTemporaryFile('demo.d40', $this->mDosDisk('LOADER', 'listing'));

        $structure = $this->manager()->parseFileStructure($path, 'demo.d40');

        $this->assertSame('d80', $structure[0]->getType());
        $this->assertCount(1, $structure[0]->getItems());
    }

    public function testUnreadableDiskYieldsNoFiles(): void
    {
        $path = $this->writeTemporaryFile('broken.mgt', str_repeat("\x00", 204800));

        $structure = $this->manager()->parseFileStructure($path, 'broken.mgt');

        $this->assertSame('mgt', $structure[0]->getType());
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
     * A single sided forty track +D disk holding one BASIC program in the first data
     * sector: the catalogue on track 0, the file on track 4.
     */
    private function gDosDisk(string $name, string $program): string
    {
        $sectorLength = 512;
        $sectorsPerTrack = 10;
        $directoryTracks = 4;
        $image = str_repeat("\x00", 40 * $sectorsPerTrack * $sectorLength);

        $header = "\x00" . pack('vvvv', strlen($program), 0x5CCB, strlen($program), 10);
        // One bit per data sector, and the file holds the first of them.
        $bitmap = "\x01" . str_repeat("\x00", 194);
        $entry = chr(1)
            . str_pad(substr($name, 0, 10), 10)
            . pack('n', 1)
            . chr($directoryTracks) . chr(1)
            . $bitmap
            . "\x00" . $header . str_repeat("\x00", 36);

        $image = substr_replace($image, $entry, 0, strlen($entry));

        // 510 bytes of file, then the pointer to the next sector: a pair of zeroes ends it.
        $sector = str_pad($header . $program, 510, "\x00") . "\x00\x00";

        return substr_replace($image, $sector, $directoryTracks * $sectorsPerTrack * $sectorLength, $sectorLength);
    }

    /**
     * A single sided ten track Didaktik disk holding one BASIC program in the first data
     * sector: the boot sector, five sectors of FAT, eight of catalogue, then the file.
     */
    private function mDosDisk(string $name, string $program): string
    {
        $sectorLength = 512;
        $firstDataSector = 14;
        $image = str_repeat("\x00", 10 * 10 * $sectorLength);

        $boot = str_repeat("\x00", 177)
            . chr(0) . chr(10) . chr(10)
            . str_repeat("\x00", 12)
            . str_pad('MDOS DISK', 10)
            . pack('v', 0x1234)
            . 'SDOS';
        $image = substr_replace($image, $boot, 0, strlen($boot));

        // The FAT entry of the file's only sector ends it and says how much of it is file.
        $entry = 0xE00 + strlen($program);
        $offset = $sectorLength + intdiv($firstDataSector, 2) * 3;
        $image[$offset] = chr($entry & 0xFF);
        $image[$offset + 1] = chr(($entry >> 8) << 4);

        $record = 'P'
            . str_pad(substr($name, 0, 10), 10, "\x00")
            . pack('vvvv', strlen($program), 10, strlen($program), $firstDataSector)
            . "\x00\x00\x00"
            . str_repeat("\xE5", 10);
        $image = substr_replace($image, $record, 6 * $sectorLength, strlen($record));

        return substr_replace(
            $image,
            str_pad($program, $sectorLength, "\x00"),
            $firstDataSector * $sectorLength,
            $sectorLength,
        );
    }
}
