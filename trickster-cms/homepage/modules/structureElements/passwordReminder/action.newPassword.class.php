<?php

class newPasswordPasswordReminder extends structureElementAction
{
    /**
     * @param passwordReminderElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $translationsManager = $this->getService(translationsManager::class);
        $db = $this->getService('db');
        $userEmail = $controller->getParameter('email');

        // get user by email
        $result = $db->table('module_user')->where('email', '=', $userEmail)->take(1)->get();
        if (count($result) > 0) {
            $user = array_shift($result);
            if (isset($user['id']) && $user['id'] != 0) {
                // check hash
                $structureElementsRows = $db->table('structure_elements')->where('id', '=', $user['id'])->take(1)->get();
                $userStructureElement = array_shift($structureElementsRows);
                $genuineHash = isset($userStructureElement['id']) ? md5($userStructureElement['id'] . $userStructureElement['dateModified']) : false;
                if ($genuineHash && $controller->getParameter('key') === $genuineHash) {
                    $parameters = $controller->getParameters();
                    if (isset($parameters['formData'])) {
                        $formData = array_shift($parameters['formData']);
                        $this->validated = $this->validated && ($formData['newpassword'] && ($formData['newpassword'] === $formData['newpasswordrepeat']));
                        if ($this->validated) {
                            // set new password
                            $passwordHash = password_hash($formData['newpassword'], PASSWORD_DEFAULT);
                            $userUpdate = $db->table('module_user')
                                ->where('id', $userStructureElement['id'])
                                ->update(['password' => $passwordHash]);
                            $structureElementUpdate = $db->table('structure_elements')
                                ->where('id', $userStructureElement['id'])
                                ->update(['dateModified' => time()]);

                            // email notification
                            $emailDispatcher = $this->getService(EmailDispatcher::class);
                            $newDispatchment = $emailDispatcher->getEmptyDispatchment();
                            $settings = $this->getService(settingsManager::class)->getSettingsList();
                            $newDispatchment->setFromName(isset($settings['default_sender_name']) ? $settings['default_sender_name'] : "");
                            $newDispatchment->setFromEmail(isset($settings['default_sender_email']) ? $settings['default_sender_email'] : "");
                            $newDispatchment->setSubject($translationsManager->getTranslationByName("email.passwordchanged_subject") . ' ' . $controller->rootURL);
                            $newDispatchment->setData($user);
                            $newDispatchment->setDataLifeTime(60);
                            $newDispatchment->setReferenceId($structureElement->id);
                            $newDispatchment->setType("password");
                            $newDispatchment->registerReceiver($userEmail, null);
                            $emailDispatcher->startDispatchment($newDispatchment);

                            $structureElement->resultMessage = $translationsManager->getTranslationByName('passwordreminder.passwordchanged');
                            $structureElement->executeAction('show');
                        } else {
                            // not validated
                            $structureElement->errorMessage = $translationsManager->getTranslationByName('passwordreminder.passwordsnotmatch');
                            $structureElement->setViewName('newPassword');
                        }
                    } else {
                        // empty form
                        $structureElement->setViewName('newPassword');
                    }
                } else {
                    $structureElement->executeAction('show');
                }
            }
        }
    }

    public function setValidators(&$validators)
    {
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['newpassword', 'newpasswordrepeat'];
    }
}

