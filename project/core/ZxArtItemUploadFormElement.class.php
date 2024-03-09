<?php

/**
 * Class ZxArtItemUploadFormElement
 *
 * @property int[]|null $author
 * @property int|null party
 */
abstract class ZxArtItemUploadFormElement extends structureElement
{
    /**
     * @return int[]|null
     *
     * @psalm-return array<int>|null
     */
    public function getAuthorIds(): array|null
    {
        return $this->author;
    }

    public function getPartyId()
    {
        return $this->party;
    }
}