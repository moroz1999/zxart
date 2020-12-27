<?php

class showLanguage extends structureElementAction
{
    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param languageElement $structureElement
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $languagesManager = $this->getService('LanguagesManager');
        $currentLanguageId = $languagesManager->getCurrentLanguageId();
        $renderer = $this->getService('renderer');

        if ($elements = ($structureManager->getElementsByType('userPlaylists', $currentLanguageId))) {
            $playlistsElement = reset($elements);
            $renderer->assign('playlistsElement', $playlistsElement);
        }

        if (($structureElement->requested || $structureElement->id == $currentLanguageId) && ($controller->getApplication(
                ) instanceof publicApplication)) {
            $user = $this->getService('user');

            $renderer = $this->getService('renderer');
            $renderer->assign('currentLanguage', $structureElement);
            $currentMainMenu = $structureElement->getCurrentMainMenu();
            $renderer->assign('currentMainMenu', $currentMainMenu);

            if ($commentsElements = $structureManager->getElementsByType('commentsList', $currentLanguageId)) {
                $renderer->assign('commentsElement', $commentsElements[0]);
            }
            if ($currentSection = $structureElement->getCurrentSection()) {
                $renderer->assign('sections', $structureElement->getSectionsList());
            }
            $renderer->assign('mainMenu', $structureElement->getMainMenuElements());
            if ($currentElement = $structureManager->getCurrentElement()) {
                $renderer->assign('currentElement', $currentElement);
            }
            $renderer->assign('firstPageElement', $structureElement->getFirstPageElement());

            $renderer->assign('currentMainMenu', $structureElement->getCurrentMainMenu());

            $currentLayout = 'layout.default.tpl';
            $renderer->assign('currentLayout', $currentLayout);
            $renderer->assign('currentMode', $this->getService('PicturesModesManager')->getModeInfo());
            if ($searchElements = $structureManager->getElementsByType('detailedSearch', $structureElement->id)) {
                foreach ($searchElements as $searchElement) {
                    if ($searchElement->items == 'music') {
                        $renderer->assign('musicDetailedSearchElement', $searchElement);
                    } elseif ($searchElement->items == 'graphics') {
                        $renderer->assign('picturesDetailedSearchElement', $searchElement);
                    }
                }
            }


            $settingsManager = $this->getService('settingsManager');
            $settings = $settingsManager->getSettingsList($structureElement->id);
            $renderer->assign('settings', $settings);

            //todo: remove global variable and implement same functionality for each required structure element (product, order ...)
            $selectedCurrencyItem = false;
            if (class_exists("CurrencySelector")) {
                $currencySelector = $this->getService('CurrencySelector');
                $selectedCurrencyItem = $currencySelector->getSelectedCurrencyItem();
            }
            $renderer->assign('selectedCurrencyItem', $selectedCurrencyItem);

            $renderer->assign('currentLayout', $currentLayout);
        }
        $structureElement->setViewName('show');
    }
}
