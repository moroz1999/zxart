<?php

use App\Users\CurrentUserService;

class showLanguage extends structureElementAction
{
    /**
     * @param languageElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $languagesManager = $this->getService(LanguagesManager::class);
        $currentLanguageId = $languagesManager->getCurrentLanguageId();
        if (($structureElement->requested || $structureElement->id == $currentLanguageId) && ($controller->getApplication() instanceof publicApplication)) {
            $currentUserService = $this->getService(CurrentUserService::class);
            $user = $currentUserService->getCurrentUser();

            $renderer = $this->getService(renderer::class);
            $renderer->assign('currentLanguage', $structureElement);
            $currentMainMenu = $structureElement->getCurrentMainMenu();
            $renderer->assign('currentMainMenu', $currentMainMenu);

            $renderer->assign('mainMenu', $structureElement->getMainMenuElements());
            if ($currentElement = $structureManager->getCurrentElement()) {
                $renderer->assign('currentElement', $currentElement);
            }
            $renderer->assign('firstPageElement', $structureElement->getFirstPageElement());

            $renderer->assign('currentMainMenu', $structureElement->getCurrentMainMenu());

            $currentLayout = 'layout.default.tpl';
            $renderer->assign('currentLayout', $currentLayout);

            $settingsManager = $this->getService(settingsManager::class);
            $settings = $settingsManager->getSettingsList($structureElement->id);
            $renderer->assign('settings', $settings);

            //todo: remove global variable and implement same functionality for each required structure element (product, order ...)
            $selectedCurrencyItem = false;
            if (class_exists("CurrencySelector")) {
                $currencySelector = $this->getService(CurrencySelector::class);
                $selectedCurrencyItem = $currencySelector->getSelectedCurrencyItem();
            }
            $renderer->assign('selectedCurrencyItem', $selectedCurrencyItem);

            $renderer->assign('currentLayout', $currentLayout);
        }
        $structureElement->setViewName('show');
    }
}



