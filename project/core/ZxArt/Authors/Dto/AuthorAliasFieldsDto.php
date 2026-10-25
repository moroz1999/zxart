<?php

declare(strict_types=1);

namespace ZxArt\Authors\Dto;

/**
 * Editable fields of an author alias. `null` leaves a field as it is; dates are
 * timestamps, 0 clearing them.
 */
final readonly class AuthorAliasFieldsDto
{
    /**
     * @param int|null $authorId the author the alias belongs to; another one moves it
     * @param int|null $startDate when the author started using the alias
     * @param int|null $endDate when the author stopped using it
     */
    public function __construct(
        public ?string $title = null,
        public ?int $authorId = null,
        public ?int $startDate = null,
        public ?int $endDate = null,
        public ?bool $displayInMusic = null,
        public ?bool $displayInGraphics = null,
    ) {
    }
}
