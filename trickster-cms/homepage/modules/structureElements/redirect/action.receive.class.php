<?php

class receiveRedirect extends structureElementAction
{
    /**
     * @param redirectElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($this->validated) {
            $structureElement->structureName = $structureElement->sourceUrl;
            $structureElement->persistElementData();

            $db = $this->getService('db');
            $query = $db->table('404_log')->where('redirectionId', '=', 0);
            if ($structureElement->partialMatch) {
                $query->where('errorUrl', 'LIKE', "%" . $structureElement->sourceUrl . "%");
            } else {
                $query->where('errorUrl', '=', $structureElement->sourceUrl);
            }
            $query->update(['redirectionId' => $structureElement->id]);

            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            "sourceUrl",
            "partialMatch",
            "destinationUrl",
            "destinationElementId",
        ];
    }
}