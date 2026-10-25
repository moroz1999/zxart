<?php

declare(strict_types=1);

namespace ZxArt\Forms;

use structureElement;

/**
 * The values a legacy element form submitted, read from the element's storage
 * once the form has validated them, in the type the field holds.
 *
 * A field the form submits is read even when it is empty — the form is the
 * whole state of the fields it carries, so an emptied field clears the value.
 * A field it does not submit is `null`: nothing is known about it.
 *
 * The storage holds strings for scalar fields and lists of strings or numbers,
 * with empty entries an emptied multi-select leaves behind, for list fields.
 */
final readonly class SubmittedFormValues
{
    /**
     * @param string[] $submittedFields the action's expected fields
     */
    public function __construct(
        private structureElement $form,
        private array $submittedFields,
    ) {
    }

    public function text(string $field): ?string
    {
        return $this->isSubmitted($field) ? (string)$this->form->getValue($field) : null;
    }

    public function number(string $field): ?int
    {
        return $this->isSubmitted($field) ? (int)$this->form->getValue($field) : null;
    }

    public function flag(string $field): ?bool
    {
        return $this->isSubmitted($field) ? (int)$this->form->getValue($field) === 1 : null;
    }

    /**
     * @return list<int>|null element ids
     */
    public function ids(string $field): ?array
    {
        if (!$this->isSubmitted($field)) {
            return null;
        }
        $ids = array_map(intval(...), (array)$this->form->getValue($field));

        return array_values(array_filter($ids, static fn(int $id): bool => $id > 0));
    }

    /**
     * @return list<string>|null codes or titles
     */
    public function codes(string $field): ?array
    {
        if (!$this->isSubmitted($field)) {
            return null;
        }
        $codes = array_map(strval(...), (array)$this->form->getValue($field));

        return array_values(array_filter($codes, static fn(string $code): bool => $code !== ''));
    }

    private function isSubmitted(string $field): bool
    {
        return in_array($field, $this->submittedFields, true);
    }
}
