<?php

class showLanguageFormShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $formRelativesInfo = [];
            $foreignRelatives = $this->getForeignRelatives($structureElement);

            $languagesIndex = [];
            $marker = $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
            $languages = $this->getService(LanguagesManager::class)->getLanguagesList($marker);
            foreach ($languages as $i => &$language) {
                if ($languageElement = $structureManager->getElementById($language->id)) {
                    if ($languageElement->requested) {
                        unset($languages[$i]);
                    } else {
                        $formRelativesInfo[$language->iso6393] = null;
                        foreach ($foreignRelatives as &$relative) {
                            if ($structureManager->checkElementInParent($relative->id, $languageElement->id)) {
                                $formRelativesInfo[$language->iso6393] = $relative;
                                break;
                            }
                        }
                    }
                    $languagesIndex[$language->iso6393] = $language;
                }
            }

            $structureElement->setTemplate('shared.content.tpl');
            $renderer = $this->getService(renderer::class);
            $renderer->assign('contentSubTemplate', 'component.form.tpl');
            $renderer->assign('form', $structureElement->getForm('language'));
            $renderer->assign('formRelativesInfo', $formRelativesInfo);
            $renderer->assign('languagesIndex', $languagesIndex);
            $renderer->assign('action', 'receiveLanguageForm');
        }
    }

    protected function getForeignRelatives($structureElement)
    {
        $structureManager = $this->getService('structureManager');

        // TODO: somehow get elements in their real language without this workaround
        foreach ($this->getService(LanguagesManager::class)->getLanguagesIdList() as $languageId) {
            $structureManager->getElementsFlatTree($languageId);
        }
        $relatives = [];

        $connectedIds = $this->getService(linksManager::class)
            ->getConnectedIdList($structureElement->id, 'foreignRelative', 'parent');
        foreach ($connectedIds as &$connectedId) {
            if ($element = $structureManager->getElementById($connectedId)) {
                $relatives[] = $element;
            }
        }
        return $relatives;
    }
}