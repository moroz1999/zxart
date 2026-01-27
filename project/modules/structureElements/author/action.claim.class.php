<?php

use App\Users\CurrentUser;

class claimAuthor extends structureElementAction
{
    /**
     * @return void
     */
    public function execute(&$structureManager, &$controller, &$structureElement)
    {
        $user = $this->getService(CurrentUser::class);
        $renderer = $this->getService('renderer');

        if (!$user->authorId && $user->authorId != $structureElement->getId()) {
            $settings = $this->getService('settingsManager')->getSettingsList();

            $emailDispatcher = new EmailDispatcher();
            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
            $fromName = isset($settings['default_sender_name']) ? $settings['default_sender_name'] : 'noreply';
            $fromEmail = isset($settings['default_sender_email']) ? $settings['default_sender_email'] : 'noreply@noreply.com';

            $newDispatchment->setFromName($fromName);
            $newDispatchment->setFromEmail($fromEmail);
            $newDispatchment->registerReceiver($settings['default_sender_email'], $settings['default_sender_name']);
            $newDispatchment->setSubject('Author claimed: ' . $structureElement->title);
            $newDispatchment->setData(
                [
                    'userId' => $user->id,
                    'userName' => $user->userName,
                    'userEmail' => $user->email,
                    'authorId' => $structureElement->getId(),
                    'author' => $structureElement->title,
                    'approvalUrl' => $structureElement->getFormActionURL(
                        ) . 'id:' . $structureElement->getId() . '/action:approveClaim/userId:' . $user->id . '/',
                ]
            );
            $newDispatchment->setReferenceId($structureElement->getId());
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