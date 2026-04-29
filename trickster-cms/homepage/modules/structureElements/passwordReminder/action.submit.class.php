<?php

class submitPasswordReminder extends structureElementAction
{
    /**
     * @param passwordReminderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $db = $this->getService('db');
        if ($this->validated) {
            $result = $db->table('module_user')->where('email', '=', $structureElement->email)->take(1)->get();
            if (count($result) > 0) {
                foreach ($result as $user) {
                    // generate hash for link
                    $result = $db->table('structure_elements')->where('id', '=', $user['id'])->take(1)->get();
                    $userStructureElement = array_shift($result);
                    $hash = md5($userStructureElement['id'] . $userStructureElement['dateModified']);
                    $user['link'] = $controller->pathURL .
                        'id:' . $controller->getParameter('id') .
                        '/action:newPassword' .
                        '/email:' . $user['email'] .
                        '/key:' . $hash . '/';

                    $translationsManager = $this->getService(translationsManager::class);
                    $emailDispatcher = $this->getService(EmailDispatcher::class);
                    $newDispatchment = $emailDispatcher->getEmptyDispatchment();
                    $settings = $this->getService(settingsManager::class)->getSettingsList();
                    $newDispatchment->setFromName(isset($settings['default_sender_name']) ? $settings['default_sender_name'] : "");
                    $newDispatchment->setFromEmail(isset($settings['default_sender_email']) ? $settings['default_sender_email'] : "");
                    $newDispatchment->setSubject($translationsManager->getTranslationByName("email.passwordreminder_subject"));
                    $newDispatchment->setData($user);
                    $newDispatchment->setDataLifeTime(60);
                    $newDispatchment->setReferenceId($structureElement->id);
                    $newDispatchment->setType("password");
                    $newDispatchment->registerReceiver($structureElement->email, null);

                    if ($emailDispatcher->startDispatchment($newDispatchment)) {
                        $structureElement->resultMessage = $structureElement->successMessage;
                    } else {
                        $structureElement->errorMessage = $structureElement->failMessage;
                    }
                }
            } else {
                $structureElement->errorMessage = $structureElement->failMessage;
            }
        }
        $structureElement->executeAction('show');
        $structureElement->setViewName('form');
    }

    public function setValidators(&$validators)
    {
        $validators['email'][] = 'email';
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['email'];
    }
}

