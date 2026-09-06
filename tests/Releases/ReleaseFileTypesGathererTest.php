<?php

declare(strict_types=1);

namespace ZxArt\Tests\Releases;

use Illuminate\Database\Connection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ZxArt\FileParsing\ZxParsingItem;
use ZxArt\FileParsing\ZxParsingManager;
use ZxArt\Releases\Services\ReleaseFileTypesGatherer;
use ZxArt\Releases\Services\ReleaseFormatsProvider;

/**
 * Which parsed files count as the formats a release was published in.
 *
 * The catalogue of a disk or a tape does not: a TR-DOS entry ending in `.o` or a
 * +D name reading as `.mgt` is that image's own content, and reading it as a
 * format is what filed a plain disk under three of them.
 */
#[CoversClass(ReleaseFileTypesGatherer::class)]
final class ReleaseFileTypesGathererTest extends TestCase
{
    private ReleaseFileTypesGatherer $gatherer;
    private ZxParsingManager $manager;

    protected function setUp(): void
    {
        $this->gatherer = new ReleaseFileTypesGatherer(new ReleaseFormatsProvider());
        $this->manager = new ZxParsingManager($this->createStub(Connection::class));
    }

    public function testADiskImageIsTheOnlyFormatItsCatalogueCanName(): void
    {
        $disk = $this->item('mgt', 'game.mgt');
        $disk->addItem($this->item('file', 'LOADER.O'));
        $disk->addItem($this->item('file', 'SCREEN.MGT'));

        $this->assertSame(['mgt'], $this->gather([$disk]));
    }

    public function testATapeImageIsNotOpenedEither(): void
    {
        $tape = $this->item('tap', 'game.tap');
        $tape->addItem($this->item('file', 'part2.tap'));

        $this->assertSame(['tap'], $this->gather([$tape]));
    }

    public function testAnArchiveNamesEveryFormatItHolds(): void
    {
        $archive = $this->item('zip', 'game.zip');
        $archive->addItem($this->item('trd', 'game.trd'));
        $archive->addItem($this->item('tap', 'game.tap'));

        $this->assertSame(['trd', 'tap'], $this->gather([$archive]));
    }

    public function testADiskInsideAnArchiveIsStillNotOpened(): void
    {
        $archive = $this->item('zip', 'game.zip');
        $disk = $this->item('trd', 'game.trd');
        $disk->addItem($this->item('file', 'boot.o'));
        $archive->addItem($disk);

        $this->assertSame(['trd'], $this->gather([$archive]));
    }

    public function testAFolderInsideAnArchiveIsWalked(): void
    {
        $archive = $this->item('zip', 'game.zip');
        $folder = $this->item('folder', 'disks');
        $folder->addItem($this->item('scl', 'side-a.scl'));
        $archive->addItem($folder);

        $this->assertSame(['scl'], $this->gather([$archive]));
    }

    /** A TAR is a distribution tree, so what it holds are files of their own. */
    public function testATarIsWalkedLikeAnArchive(): void
    {
        $tar = $this->item('tar', 'boot.tar');
        $tar->addItem($this->item('spg', 'game.spg'));

        $this->assertSame(['tar', 'spg'], $this->gather([$tar]));
    }

    /**
     * @param ZxParsingItem[] $items
     * @return list<string>
     */
    private function gather(array $items): array
    {
        return array_values(array_map(
            static fn(ZxParsingItem $item): string => $item->getItemExtension(),
            $this->gatherer->gatherReleaseFiles($items),
        ));
    }

    private function item(string $type, string $name): ZxParsingItem
    {
        $item = $this->manager->createItem($type);
        $item->setItemName($name);
        $item->setMd5(md5($name));

        return $item;
    }
}
