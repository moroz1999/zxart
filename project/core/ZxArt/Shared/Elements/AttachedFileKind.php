<?php

declare(strict_types=1);

namespace ZxArt\Shared\Elements;

/**
 * A list of files an element keeps — screenshots, inlays, maps — and what a
 * file fetched into it may be.
 */
interface AttachedFileKind
{
    /**
     * The element's file list, which is also the link type of its files.
     */
    public function propertyName(): string;

    public function maxBytes(): int;

    /**
     * @param string $contentType detected from the file's bytes
     */
    public function accepts(string $contentType): bool;
}
