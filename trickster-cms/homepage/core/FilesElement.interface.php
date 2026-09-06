<?php

/**
 * An element with multi-file selectors: screenshots, inlays, maps and the like.
 * Implemented by {@see FilesElementTrait}.
 */
interface FilesElementInterface
{
    /**
     * @return string[]
     */
    public function getFileSelectorPropertyNames();

    /**
     * The uploads submitted for one selector, as PHP described them. They stay
     * staged in the upload cache until whoever received the form clears them.
     *
     * @return list<array<array-key, mixed>>
     */
    public function getSubmittedUploads(string $propertyName): array;
}
