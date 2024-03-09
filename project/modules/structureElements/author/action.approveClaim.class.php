<?php

class approveClaimAuthor extends structureElementAction
{
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $claimApproved = false;
        $claimResultSent = false;
        $userElement = false;

        if (is_numeric($userId = $controller->getParameter('userId'))) {
            if ($userElement = $structureManager->getElementById($userId, null, true)) {
                $userElement->prepareActualData();
                $userElement->changeConnectedAuthor($structureElement->id);
                $userElement->persistElementData();
                $claimApproved = true;
            }
        }
        $settings = $this->getService('settingsManager')->getSettingsList();
        $translationsManager = $this->getService('translationsManager');

        if ($claimApproved) {
            if ($userElement) {
                $emailDispatcher = new EmailDispatcher();
                $newDispatchment = $emailDispatcher->getEmptyDispatchment();
                $newDispatchment->setFromName($settings['default_sender_name']);
                $newDispatchment->setFromEmail($settings['default_sender_email']);
                $newDispatchment->registerReceiver($userElement->email, $userElement->userName);
                $newDispatchment->setSubject($translationsManager->getTranslationByName('author.claimresult_subject'));
                $newDispatchment->setData(
                    [
                        'resultText' => $translationsManager->getTranslationByName('author.claimresult_success'),
                        'author' => $structureElement->title,
                    ]
                );
                $newDispatchment->setReferenceId($structureElement->id);
                $newDispatchment->setType('authorClaimResult');
                if ($emailDispatcher->startDispatchment($newDispatchment)) {
                    $claimResultSent = true;
                }
            }
        }
        $renderer = $this->getService('renderer');
        $renderer->assign('claimResultSent', $claimResultSent);
        $renderer->assign('claimApproved', $claimApproved);
        $structureElement->setViewName('claimApproved');
    }
}


