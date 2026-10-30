<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

use ZxArt\Prods\ProdFileKind;
use ZxArt\Releases\ReleaseFileKind;
use ZxArt\Shared\Dto\ListedFileDto;
use zxProdElement;
use zxReleaseElement;

/**
 * Every file list of a production or a release, as one list.
 *
 * @psalm-api
 */
final readonly class ListedFilesReader
{
    public function __construct(
        private AttachedFilesReader $attachedFilesReader,
    ) {
    }

    /**
     * @param list<ProdFileKind>|list<ReleaseFileKind> $kinds
     * @return list<ListedFileDto> list by list, in the order the kinds are given
     */
    public function read(zxProdElement|zxReleaseElement $element, array $kinds): array
    {
        $files = [];
        foreach ($kinds as $kind) {
            foreach ($this->attachedFilesReader->read($element, $kind) as $file) {
                $files[] = new ListedFileDto($kind->value, $file->id, $file->fileName);
            }
        }

        return $files;
    }
}
