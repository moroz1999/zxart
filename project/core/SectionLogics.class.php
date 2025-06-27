<?php

use ZxArt\LinkTypes;

class SectionLogics
{
    protected $sectionType;
    protected $sectionId;
    protected $typesIds;
    /**
     * @var structureManager
     */
    protected $structureManager;
    /**
     * @var LanguagesManager
     */
    protected $languagesManager;

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager($structureManager): void
    {
        $this->structureManager = $structureManager;
    }

    public function setLanguagesManager($languagesManager): void
    {
        $this->languagesManager = $languagesManager;
    }

    public function __construct()
    {
    }

    public function getArtItemsType()
    {
        if ($this->sectionType === null) {
            if ($currentLanguage = $this->structureManager->getElementById(
                $this->languagesManager->getCurrentLanguageId()
            )) {
                if ($section = $currentLanguage->getCurrentSection()) {
                    $this->sectionType = $section->marker;
                }
            }
        }
        return $this->sectionType;
    }

    public function getCurrentSectionId()
    {
        if ($this->sectionId === null) {
            $this->sectionId = false;
            if ($currentLanguageElement = $this->structureManager->getElementById(
                $this->languagesManager->getCurrentLanguageId()
            )
            ) {
                if ($sectionElement = $currentLanguageElement->getCurrentSection()) {
                    $this->sectionId = $sectionElement->id;
                }
            }
        }
        return $this->sectionId;
    }

    public function getSectionsInfo()
    {
        if ($this->typesIds === null) {
            $this->typesIds = [];
            if ($currentLanguageElement = $this->structureManager->getElementById(
                $this->languagesManager->getCurrentLanguageId()
            )
            ) {
                if ($sectionElements = $currentLanguageElement->getSectionsList()) {
                    foreach ($sectionElements as $sectionElement) {
                        $this->typesIds[$sectionElement->marker] = $sectionElement->id;
                    }
                }
            }
        }
        return $this->typesIds;
    }

    public function getSectionIdByType($marker)
    {
        $info = $this->getSectionsInfo();
        if (isset($info[$marker])) {
            return $info[$marker];
        }
        return false;
    }

    /**
     * @return string[]
     */
    public function getAuthorLinkTypes(): array
    {
        $sectionType = $this->getArtItemsType();
        if ($sectionType === 'graphics') {
            $types = [LinkTypes::AUTHOR_PICTURE->value];
        } elseif ($sectionType === 'music') {
            $types = ['authorMusic'];
        } else {
            $types = [LinkTypes::AUTHOR_PICTURE->value, 'authorMusic'];
        }

        return $types;
    }

    /**
     * @return string|string[]
     *
     * @psalm-return 'partyMusic'|'partyPicture'|list{'partyMusic'}
     */
    public function getPartyLinkTypes(): array|string
    {
        $types = ['partyMusic'];
        $sectionType = $this->getArtItemsType();
        if ($sectionType === 'graphics') {
            $types = 'partyPicture';
        } elseif ($sectionType === 'music') {
            $types = 'partyMusic';
        } elseif ($sectionType === 'all') {
            $types = 'partyMusic';

        }

        return $types;
    }
}
