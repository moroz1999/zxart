<?php

class deleteFileShared extends structureElementAction
{
    protected $loggable = true;

    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $language = false;

        if ($controller->getParameter('language')) {
            $language = intval($controller->getParameter('language'));
        }

        if ($fileField = $controller->getParameter('file')) {
            //workaround for old-standard field names
            if ($fileField == 'image' && $structureElement->originalName) {
                $fileNameField = 'originalName';
            } else {
                $fileNameField = $fileField . "Name";
                if (!$structureElement->$fileNameField) {
                    $fileNameField = $fileField . 'OriginalName';
                }
            }

            foreach ($structureElement->getAllDataChunks() as $langId => $chunks) {
                if ($language === $langId || $language === false) {
                    if (isset($chunks[$fileField]) && isset($chunks[$fileNameField])) {
                        $chunks[$fileField]->deleteExtraData();
                        $structureElement->setValue($fileField, '', $langId);
                        $structureElement->setValue($fileNameField, '', $langId);
                    }
                }
            }
            $structureElement->persistElementData();
        }

        $controller->restart($structureElement->getUrl('showPublicForm'));
    }
}