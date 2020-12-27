<?php

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
     * @var languagesManager
     */
    protected $languagesManager;

    /**
     * @param structureManager $structureManager
     */
    public function setStructureManager($structureManager)
    {
        $this->structureManager = $structureManager;
    }

    /**
     * @param languagesManager $languagesManager
     */
    public function setLanguagesManager($languagesManager)
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
        if (is_null($this->sectionId)) {
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
        if (is_null($this->typesIds)) {
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

    public function getAuthorLinkTypes()
    {
        $sectionType = $this->getArtItemsType();
        if ($sectionType == 'graphics') {
            $types = ['authorPicture'];
        } elseif ($sectionType == 'music') {
            $types = ['authorMusic'];
        } else {
            $types = ['authorPicture', 'authorMusic'];
        }

        return $types;
    }

    public function getPartyLinkTypes()
    {
        $types = ['partyMusic'];
        $sectionType = $this->getArtItemsType();
        if ($sectionType == 'graphics') {
            $types = 'partyPicture';
        } else {
            if ($sectionType == 'music') {
                $types = 'partyMusic';
            } else {
                if ($sectionType == 'all') {
                    $types = 'partyMusic';
                }
            }
        }
        return $types;
    }
}
