<?php

/**
 * Class zxProdsUploadFormElement
 *
 * @property string $title
 * @property string $prodTitle
 * @property string $legalStatus
 * @property int $party
 * @property int $partyplace
 * @property string $compo
 * @property int[] $categories
 * @property int[] $publishers
 * @property int[] $groups
 * @property string $year
 * @property string $description
 * @property int $denyVoting
 * @property int $denyComments
 * @property [] $files
 */
class zxProdsUploadFormElement extends ZxArtItemUploadFormElement
{
    use AuthorshipProviderTrait;
    use PartyElementProviderTrait;
    use CategoryElementsSelectorProviderTrait;
    use DemoCompoTypesProvider;
    use LanguageCodesProviderTrait;

    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'batchUploadForm';
    public $role = 'container';

    protected $partiesList;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['prodTitle'] = 'text';

        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['publishers'] = 'numbersArray';
        $moduleStructure['groups'] = 'numbersArray';
        $moduleStructure['categories'] = 'numbersArray';
        $moduleStructure['year'] = 'text';
        $moduleStructure['youtubeId'] = 'text';
        $moduleStructure['description'] = 'pre';

        $moduleStructure['categories'] = 'numbersArray';
        $moduleStructure['publishers'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxProdPublishers',
                'role' => 'child',
            ],
        ];
        $moduleStructure['groups'] = [
            'ConnectedElements',
            [
                'linkType' => 'zxProdGroups',
                'role' => 'child',
            ],
        ];
        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['description'] = 'pre';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';

        $moduleStructure['addAuthor'] = 'text';
        $moduleStructure['addAuthorRole'] = 'array';

        $moduleStructure['legalStatus'] = 'text';
        $moduleStructure['file'] = 'files';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    public function getConnectedCategoriesIds()
    {
        if ($this->connectedCategoriesIds === null) {
            $this->connectedCategoriesIds = [];
            if ($element = $this->getFirstParentElement()) {
                if ($element->structureType == 'zxProdCategory') {
                    $this->connectedCategoriesIds = [$element->id];
                }
            }
        }
        return $this->connectedCategoriesIds;
    }

    public function getSupportedLanguageCodes()
    {
        return $this->language;
    }
}
