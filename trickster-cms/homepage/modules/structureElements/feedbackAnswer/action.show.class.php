<?php

class showFeedbackAnswer extends structureElementAction
{
    /**
     * @param feedbackAnswerElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = renderer::getInstance();
            $renderer->assign('contentSubTemplate', 'feedbackAnswer.show.tpl');
        }
        $structureElement->setViewName('show');
    }
}