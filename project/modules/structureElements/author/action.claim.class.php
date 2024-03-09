<?php

class claimAuthor extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $user = $this->getService('user');
        $renderer = $this->getService('renderer');

        if (!$user->authorId && $user->authorId != $structureElement->id) {
            $settings = $this->getService('settingsManager')->getSettingsList();

            $emailDispatcher = new EmailDispatcher();
            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
            $newDispatchment->setFromName($user->userName);
            $newDispatchment->setFromEmail($user->email);
            $newDispatchment->registerReceiver($settings['default_sender_email'], $settings['default_sender_name']);
            $newDispatchment->setSubject('Author claimed: ' . $structureElement->title);
            $newDispatchment->setData(
                [
                    'userId' => $user->id,
                    'userName' => $user->userName,
                    'userEmail' => $user->email,
                    'authorId' => $structureElement->id,
                    'author' => $structureElement->title,
                    'approvalUrl' => $structureElement->getFormActionURL(
                        ) . 'id:' . $structureElement->id . '/action:approveClaim/userId:' . $user->id . '/',
                ]
            );
            $newDispatchment->setReferenceId($structureElement->id);
            $newDispatchment->setType('authorClaim');
            $claimRequestResult = false;
            if ($emailDispatcher->startDispatchment($newDispatchment)) {
                $claimRequestResult = true;
            }
        } else {
            $claimRequestResult = false;
        }
        $structureElement->setViewName('claimed');
        $renderer->assign('claimRequestResult', $claimRequestResult);
    }
}