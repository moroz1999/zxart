<?php

declare(strict_types=1);

namespace ZxArt\FileParsing;

use Override;
use ZxFiles\Binary\Binary;
use ZxFiles\ContainerFormat;
use ZxFiles\Directory;
use ZxFiles\Exception\ZxFilesException;
use ZxFiles\File;
use ZxFiles\Mgt\MgtFile;
use ZxFiles\Text\Charset;
use ZxFiles\Text\TextDecoder;
use ZxFiles\ZxSpectrum\GDos\GDosFileInfo;
use ZxFiles\ZxSpectrum\GDos\GDosFileType;
use ZxFiles\ZxSpectrum\IsDos\IsDosFile;
use ZxFiles\ZxSpectrum\MDos\MDosFile;
use ZxFiles\ZxSpectrum\MDos\MDosFileType;

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

    /**
     * A TAR is a distribution tree: what it holds are files that were put there
     * side by side. Every other format this class reads is a disk or a tape, and
     * what it holds is that medium's contents.
     */
    #[Override] public function holdsSeparateFiles(): bool
    {
        return $this->format === ContainerFormat::Tar;
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
            $item->setContent($this->fileContent($file));
            $item->setParentMd5((string)$parent->getMd5());
            $item->setItemName($this->fileName($file));
            $internalType = $this->internalType($file);
            if ($internalType !== null) {
                $item->setInternalType($internalType);
            }
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
     * GDOS and SAMDOS keep a nine byte header in front of a file, repeating what the
     * catalogue already records, so the item holds the file without it — which is also what
     * makes a SCREEN$ the 6912 bytes it is. Every other system stores the file as it is.
     */
    private function fileContent(File $file): string
    {
        return $file instanceof MgtFile ? $file->payload() : $file->contents();
    }

    /**
     * What a file is, when its catalogue says so and its name cannot: GDOS records no
     * extension at all, and MDOS writes the type letter as one. SAM BASIC is deliberately
     * left out — it is not written in ZX Spectrum tokens and would not list.
     */
    private function internalType(File $file): ?string
    {
        $isGDosBasic = $file instanceof MgtFile
            && $file->info instanceof GDosFileInfo
            && $file->info->type === GDosFileType::BasicProgram;
        $isMDosBasic = $file instanceof MDosFile && $file->type === MDosFileType::BasicProgram;

        return $isGDosBasic || $isMDosBasic ? 'zx_basic' : null;
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
