<?php

class createMissingAdminTranslations extends structureElementAction
{
    /**
     * @param adminTranslationsElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($missingTranslationCodes = $structureElement->searchMissingTranslations()) {
            foreach ($missingTranslationCodes as &$code) {
                $strings = explode('.', $code);
                if ((count($strings) > 1) && ($groupName = reset($strings)) && ($translationCode = end($strings))) {
                    if ($groupElement = $structureElement->getGroupByCode($groupName, true)) {
                        $structureElement->createTranslation($translationCode, $groupElement->id, []);
                    }
                }
            }
        }
        $controller->redirect($structureElement->URL . 'incomplete:1/');
    }
}