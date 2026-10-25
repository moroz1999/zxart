<?php

declare(strict_types=1);

namespace ZxArt\Releases\Dto;

use ZxArt\Prods\Dto\ProdSearchFilterDto;
use ZxArt\Releases\ReleaseTypes;

/**
 * What a release search narrows by; every filter given must hold. A list
 * holds when any of its values does, except the authors: all of them must be
 * credited unless `anyAuthor` is set. An author or a group takes in its
 * aliases.
 *
 * @psalm-api
 */
final readonly class ReleaseSearchFilterDto
{
    /**
     * @param int[] $prodIds releases of these productions
     * @param ReleaseTypes[] $releaseTypes
     * @param string[] $formats
     * @param string[] $hardware hardware codes the release requires
     * @param string[] $languages language codes of the release
     * @param int[] $publisherIds authors or groups that published the release
     * @param int[] $authorIds credited on the release itself
     * @param string[] $authorRoles the roles the authors, or anyone when no author is given, are credited with
     */
    public function __construct(
        public ?string $title = null,
        /** a part of the release file's name */
        public ?string $fileName = null,
        /** of the release file or of any file inside it */
        public ?string $md5 = null,
        public array $prodIds = [],
        /** releases of the productions the nested search finds */
        public ?ProdSearchFilterDto $prods = null,
        public ?int $yearFrom = null,
        public ?int $yearTo = null,
        public array $releaseTypes = [],
        public array $formats = [],
        public array $hardware = [],
        public array $languages = [],
        public array $publisherIds = [],
        public array $authorIds = [],
        public bool $anyAuthor = false,
        public array $authorRoles = [],
    ) {
    }
}
