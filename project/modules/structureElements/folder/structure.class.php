<?php

/**
 * Class folderElement
 *
 * @property string $title
 * @property string $columns
 * @property string $image
 * @property string $originalName
 */
class folderElement extends menuDependantStructureElement implements
    ConfigurableLayoutsProviderInterface,
    ColumnsTypeProvider,
    BreadcrumbsInfoProvider
{
    use ConfigurableLayoutsProviderTrait;
    use LettersElementsListProviderTrait;

    public $dataResourceName = 'module_folder';
    public $defaultActionName = 'show';
    public $role = 'container';

    /**
     * @return string[]
     *
     * @psalm-return list{'showFullList', 'showForm', 'showSeoForm', 'showLayoutForm', 'showPositions', 'showPrivileges', 'showLanguageForm'}
     */
    protected function getTabsList()
    {
        return [
            'showFullList',
            'showForm',
            'showSeoForm',
            'showLayoutForm',
            'showPositions',
            'showPrivileges',
            'showLanguageForm',
        ];
    }

    public function getTextContent()
    {
        if (is_null($this->textContent)) {
            $this->textContent = "";
            if ($contentElements = $this->getContentList()) {
                // TODO add getContentList method
                foreach ($contentElements as $contentElement) {
                    if ($contentElement->title) {
                        $this->textContent .= $contentElement->title . ".";
                    }

                    if ($contentElement->introduction) {
                        $this->textContent .= " " . $contentElement->introduction . " ";
                        $this->textContent .= $contentElement->content ? " " . $contentElement->content : "";
                    } else {
                        $this->textContent .= $contentElement->content ? " " . $contentElement->content : " " . $contentElement->title;
                    }
                }
            }
        }
        return $this->textContent;
    }

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['metaTitle'] = 'text';
        $moduleStructure['h1'] = 'text';
        $moduleStructure['metaDescription'] = 'text';
        $moduleStructure['canonicalUrl'] = 'url';
        $moduleStructure['metaDenyIndex'] = 'checkbox';
        $moduleStructure['columns'] = 'text';
        $moduleStructure['externalUrl'] = 'url';

        $moduleStructure['formRelativesInput'] = 'array';
        $moduleStructure['hidden'] = 'checkbox';

        $moduleStructure['colorLayout'] = 'text';
    }

    /**
     * @return structureElement[]
     * @deprecated historically, use getContentElements instead
     */
    public function getContentList()
    {
        return $this->getContentElements('structure');
    }

    /**
     * @param null|string $types
     *
     * @psalm-param 'structure'|null $types
     *
     * @return structureElement[]
     *
     * @psalm-return array<structureElement>
     */
    public function getContentElements(string|null $types = null): array
    {
        return $this->getChildrenList('content', $types, null, true);
    }

    public function getParent()
    {
        return $this->getService('structureManager')->getElementsFirstParent($this->id);
    }

    public function getSubMenuList($linkType = [])
    {
        $subMenus = [];

        $structureManager = $this->getService('structureManager');
        $childrenList = $structureManager->getElementsChildren($this->id, 'container', $linkType, null, true);

        foreach ($childrenList as $child) {
            if (!$child->hidden) {
                $subMenus[] = $child;
            }
        }
        return $subMenus;
    }

    /**
     * @return string
     */
    public function getColumnsType()
    {
        return $this->columns;
    }

    /**
     * Get allowed children structure elements type according to settings, current user's privileges and selected type
     *
     * @param string $currentAction
     * @return string[]
     */
    public function getAllowedTypes($currentAction = 'showFullList')
    {
        if ($this->allowedTypes === null) {
            /**
             * @var ConfigManager $configManager
             */
            $configManager = $this->getService('ConfigManager');
            $this->allowedTypes = $configManager->getMerged('folder-allowedTypes.content');
        }
        return parent::getAllowedTypes($currentAction);
    }

    /**
     * @psalm-param 'admin'|'public' $type
     */
    protected function getLettersListMarker(string $type)
    {
        return $this->structureMarker;
    }

    public function getLettersSelectorInfo()
    {
        if ($this->lettersSelectorInfo === null) {
            $this->lettersSelectorInfo = [];

            if ($structureManager = $this->getService('structureManager')) {
                foreach ($structureManager->getElementsChildren($this->id) as $letter) {
                    if ($letter->structureType == 'letter') {
                        $letter->setViewName('games');
                        $letter->template = 'letter.games.tpl';
                        $this->lettersSelectorInfo[] = [
                            'url' => $letter->getUrl(),
                            'title' => $letter->title,
                        ];
                    }
                }
            }
        }
        return $this->lettersSelectorInfo;
    }

    public function isBreadCrumb(): bool
    {
        $structureManager = $this->getService('structureManager');
        $languagesManager = $this->getService('languagesManager');

        if ($currentLanguage = $structureManager->getElementById(
            $languagesManager->getCurrentLanguageId()
        )){
            $firstPageElement = $currentLanguage->getFirstPageElement();
            return $firstPageElement->id !== $this->id;
        }

        return true;
    }

    public function getBreadcrumbsTitle(): string
    {
        return $this->title;
    }

    public function getBreadcrumbsUrl(): string
    {
        return $this->URL;
    }
}