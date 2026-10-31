<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Tools\Previews;

use ZxArt\Assistant\Tools\Dto\FieldChangeDto;
use ZxArt\Authors\Dto\AuthorshipDto;

/**
 * The rows a preview shows for a credit or a membership set or removed: the
 * author when it comes or goes, and its roles.
 *
 * @psalm-api
 */
final readonly class CreditChanges
{
    public function __construct(
        private Mentions $mentions,
    ) {
    }

    /**
     * @param AuthorshipDto[] $authorships the element's credits now
     * @param list<string> $roles
     * @param ?int $startDate a membership's start, null to keep it
     * @param ?int $endDate a membership's end, null to keep it
     * @return list<FieldChangeDto>
     */
    public function set(array $authorships, int $authorId, array $roles, ?int $startDate = null, ?int $endDate = null): array
    {
        $current = $this->find($authorships, $authorId);
        $changes = [];
        if ($current === null) {
            $changes[] = new FieldChangeDto('author', null, $this->mentions->one($authorId));
        }
        if ($current?->roles !== $roles) {
            $changes[] = new FieldChangeDto('roles', $current?->roles, $roles);
        }
        foreach (['startDate' => [$current?->startDate, $startDate], 'endDate' => [$current?->endDate, $endDate]] as $field => [$before, $after]) {
            $beforeDay = Dates::day($before ?? 0);
            $afterDay = $after === null ? $beforeDay : Dates::day($after);
            if ($afterDay !== $beforeDay) {
                $changes[] = new FieldChangeDto($field, $beforeDay, $afterDay);
            }
        }

        return $changes;
    }

    /**
     * @param AuthorshipDto[] $authorships the element's credits now
     * @return list<FieldChangeDto>
     */
    public function remove(array $authorships, int $authorId): array
    {
        return [
            new FieldChangeDto('author', $this->mentions->one($authorId), null),
            new FieldChangeDto('roles', $this->find($authorships, $authorId)?->roles, null),
        ];
    }

    /**
     * @param AuthorshipDto[] $authorships
     */
    private function find(array $authorships, int $authorId): ?AuthorshipDto
    {
        foreach ($authorships as $authorship) {
            if ($authorship->authorId === $authorId) {
                return $authorship;
            }
        }

        return null;
    }
}
