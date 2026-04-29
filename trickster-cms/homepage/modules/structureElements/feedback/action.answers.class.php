<?php

class answersFeedback extends structureElementAction
{
    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        if ($structureElement->final) {
            $structureElement->setTemplate('shared.content.tpl');
            $renderer = renderer::getInstance();
            $renderer->assign('contentSubTemplate', 'feedback.answers.tpl');
        }
    }
}


