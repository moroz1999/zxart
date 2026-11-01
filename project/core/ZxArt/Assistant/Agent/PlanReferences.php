<?php

declare(strict_types=1);

namespace ZxArt\Assistant\Agent;

use stdClass;

/**
 * `{"$ref": N}` in the arguments of a plan's action: the id that action N of the
 * same plan creates, counting from 1.
 */
final readonly class PlanReferences
{
    /**
     * @return list<int> the actions referred to, ascending
     */
    public function find(mixed $arguments): array
    {
        $found = [];
        $this->walk($arguments, static function (int $action) use (&$found): ?int {
            $found[$action] = $action;
            return null;
        });
        sort($found);

        return array_values($found);
    }

    /**
     * @param callable(int): int $idOf the id that action N created
     */
    public function resolve(mixed $arguments, callable $idOf): mixed
    {
        return $this->walk($arguments, $idOf);
    }

    /**
     * @param callable(int): ?int $visit
     */
    private function walk(mixed $value, callable $visit): mixed
    {
        if ($value instanceof stdClass) {
            $properties = get_object_vars($value);
            if (array_keys($properties) === ['$ref'] && is_int($properties['$ref'])) {
                return $visit($properties['$ref']);
            }
            $walked = new stdClass();
            foreach ($properties as $name => $property) {
                $walked->{$name} = $this->walk($property, $visit);
            }

            return $walked;
        }
        if (is_array($value)) {
            return array_map(fn(mixed $item): mixed => $this->walk($item, $visit), $value);
        }

        return $value;
    }
}
