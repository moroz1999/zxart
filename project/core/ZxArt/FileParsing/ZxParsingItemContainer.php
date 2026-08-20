<?php

declare(strict_types=1);

namespace ZxArt\FileParsing;

use Override;
use ZxFiles\Binary\Binary;
use ZxFiles\ContainerFormat;
use ZxFiles\Directory;
use ZxFiles\Exception\ZxFilesException;
use ZxFiles\File;
use ZxFiles\Text\Charset;
use ZxFiles\Text\TextDecoder;
use ZxFiles\ZxSpectrum\IsDos\IsDosFile;

/**
 * A disk, tape or archive image read through zx-files. Every container format the library
 * supports goes through this one class: the format decides which parser is used and how the
 * item is labelled, the result is always a tree of files and folders.
 */
final class ZxParsingItemContainer extends ZxParsingItem
{
    public function __construct(
        ZxParsingManager $zxParsingManager,
        private readonly ContainerFormat $format,
    ) {
        parent::__construct($zxParsingManager);
    }

    #[Override] public function getType(): string
    {
        return $this->format->value;
    }

    #[Override] protected function parse(): void
    {
        $this->items = [];

        $content = $this->getContent();
        if ($content === null || $content === '') {
            return;
        }

        $reader = $this->zxParsingManager->getContainerReader();
        $binary = Binary::fromString($content);
        // The extension only says what the file claims to be, so it is passed as a hint and
        // the library decides which parser actually recognises the bytes.
        if (!($parser = $reader->detect($binary, $this->format))) {
            return;
        }

        try {
            $root = $reader->read($binary, $parser)->root();
        } catch (ZxFilesException) {
            return;
        }

        $this->addDirectory($root, $this);
    }

    /**
     * Disk systems are flat and put everything in the root; archives such as TAR carry
     * paths of their own, which become folder items.
     */
    private function addDirectory(Directory $directory, ZxParsingItem $parent): void
    {
        foreach ($directory->files as $file) {
            $item = new ZxParsingItemFile($this->zxParsingManager);
            $item->setContent($file->contents());
            $item->setParentMd5((string)$parent->getMd5());
            $item->setItemName($this->fileName($file));
            $parent->addItem($item);
        }

        foreach ($directory->directories as $subDirectory) {
            $item = new ZxParsingItemFolder($this->zxParsingManager);
            $item->setParentMd5((string)$parent->getMd5());
            $item->setItemName($subDirectory->name);
            $parent->addItem($item);

            $this->addDirectory($subDirectory, $item);
        }
    }

    /**
     * Names are stored exactly as recorded on the media, so they are not UTF-8 to begin
     * with. IS-DOS wrote its catalogues in CP866 and the library offers the converted name
     * beside the raw one; everything else that is not already UTF-8 is read as the ZX
     * Spectrum character set, which is what the machine itself would have displayed.
     */
    private function fileName(File $file): string
    {
        if ($file instanceof IsDosFile) {
            return $file->displayName;
        }

        if (mb_check_encoding($file->fullName, 'UTF-8')) {
            return $file->fullName;
        }

        return (new TextDecoder())->toUtf8($file->fullName, Charset::Sinclair);
    }
}
