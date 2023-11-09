<?php

class publicReceivePressArticle extends structureElementAction
{
    protected $loggable = true;

    /**
     * @param structureManager $structureManager
     * @param controller $controller
     * @param pressArticleElement $structureElement
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        if ($this->validated) {
            if ($structureElement->structureName == '') {
                $structureElement->structureName = $structureElement->title;
            }
            $structureElement->persistElementData();

            if ($parentElement = $structureElement->getFirstParentElement()){
                $linksManager = $this->getService('linksManager');
                $linksManager->unLinkElements($parentElement->id, $structureElement->id, 'structure');
                $linksManager->linkElements($parentElement->id, $structureElement->id, 'prodArticle');
            }

            $controller->redirect($structureElement->URL);
        }

        $structureElement->setViewName('form');
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = [
            'title',
            'externalLink',
            'introduction',
            'content',
            'allowComments',
            'authors',
        ];
    }

    public function setValidators(&$validators)
    {
    }
}

