<?php

class deleteImageShared extends structureElementAction
{
    protected $loggable = true;

    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $this->logError('Deprecated action used: ' . $structureElement->structureType . ' deleteImage');

        $language = null;
        if ($controller->getParameter('language')) {
            $language = intval($controller->getParameter('language'));
        }

        $imageType = "";
        if ($controller->getParameter('imageType')) {
            $imageType = $controller->getParameter('imageType');
        }

        if ($imageType != "") {
            $imageFieldName = $imageType;
            $nameFieldName = $imageType . 'OriginalName';
        } else {
            $imageFieldName = 'image';
            $nameFieldName = 'originalName';
        }

        foreach ($structureElement->getAllDataChunks() as $langId => $chunks) {
            if ($language === $langId || $language === null) {
                if (isset($chunks[$imageFieldName])) {
                    $dataChunk = $chunks[$imageFieldName];
                    $dataChunk->deleteExtraData();
                    $structureElement->setValue($imageFieldName, '', $langId);
                }
                if (isset($chunks[$nameFieldName])) {
                    $structureElement->setValue($nameFieldName, '', $langId);
                }
            }
        }
        $structureElement->persistElementData();

        $controller->restart($structureElement->URL);
    }
}