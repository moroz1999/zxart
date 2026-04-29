<?php

use App\Paths\PathsManager;

class exportShared extends structureElementAction
{
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $exportData = [];

        foreach ($structureElement->getContentList() as $element) {
            $data = $element->getExportData();
            $exportData[] = $data;
        }
        $renderer = $this->getService(renderer::class);
        $renderer->assign('exportData', $exportData);

        // language id to code
        $languageCodes = [];
        $languagesManager = $this->getService(LanguagesManager::class);
        $languagesList = $languagesManager->getLanguagesList();
        foreach ($languagesList as $languagesItem) {
            $languageCodes[$languagesItem->id] = $languagesItem->iso6393;
        }
        $renderer->assign('languagesList', $languageCodes);
        $path = $this->getService(PathsManager::class)->getPath('trickster');
        $renderer->setTemplatesFolder($path . 'cms/templates/xml');
        $renderer->setTemplate('xml.export.tpl');
        $renderer->setCacheControl('no-cache');
        $renderer->setContentDisposition('attachment');
        $renderer->setContentType('application/xml');
        $renderer->display();
        exit;
    }
}


