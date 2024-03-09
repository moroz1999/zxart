<?php

/**
 * Class ZxArtItemUploadFormElement
 *
 * @property int[]|null $author
 * @property int|null party
 */
abstract class ZxArtItemUploadFormElement extends structureElement
{
    public function getAuthorIds()
    {
        return $this->author;
    }

    public function getPartyId()
    {
        return $this->party;
    }
}