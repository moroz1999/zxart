<?php

trait LettersElementsListProviderTrait
{
    /**
     * @return letterElement[]|bool
     */
    protected function getLetterElements()
    {
        /**
         * @var languagesManager $languagesManager
         */
        if (!empty($this->languagesManager)) {
            $languagesManager = $this->languagesManager;
        } else {
            $languagesManager = $this->getService('LanguagesManager');
        }

        /**
         * @var structureManager $structureManager
         */
        if (!empty($this->structureManager)) {
            $structureManager = $this->structureManager;
        } else {
            $structureManager = $this->getService('structureManager');
        }

        /**
         * @var ConfigManager $configManager
         */
        if (!empty($this->configManager)) {
            $configManager = $this->configManager;
        } else {
            $configManager = $this->getService('ConfigManager');
        }

        if ($structureManager->getRootElementMarker() == $configManager->get('main.rootMarkerAdmin')) {
            if ($lettersMenu = $structureManager->getElementByMarker($this->getLettersListMarker('admin'))) {
                return $structureManager->getElementsChildren($lettersMenu->id, 'container');
            }
        } elseif ($lettersMenu = $structureManager->getElementByMarker(
            $this->getLettersListMarker('public'),
            $languagesManager->getCurrentLanguageId()
        )) {
            return $structureManager->getElementsChildren($lettersMenu->id, 'container', []);
        }
        return false;
    }

    protected function getLetterId($title)
    {
        $letterId = false;
        if ($firstLetter = substr(TranslitHelper::convert(mb_strtolower(mb_substr(trim($title), 0, 1))), 0, 1)) {
            if ($letters = $this->getLetterElements()) {
                foreach ($letters as $letter) {
                    if (!$letterId && $letter->title == '#') {
                        $letterId = $letter->id;
                    }
                    if ($letter->structureName == $firstLetter) {
                        $letterId = $letter->id;
                        break;
                    }
                }
            }
        }
        return $letterId;
    }

    abstract protected function getLettersListMarker($type);
}