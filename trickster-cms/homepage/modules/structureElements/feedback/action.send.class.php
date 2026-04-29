<?php

use App\Logging\EventsLog;
use App\Paths\PathsManager;

class sendFeedback extends structureElementAction
{
    use AjaxFormTrait;
    protected $loggable = true;

    /**
     * @param feedbackElement $structureElement
     */
    public function execute(structureManager $structureManager, controller $controller, structureElement $structureElement): void
    {
        $translationsManager = $this->getService(translationsManager::class);

        if ($this->validated && $this->validateAjaxRequest()) {
            $settings = $this->getService(settingsManager::class)->getSettingsList();
            $subject = $structureElement->title;

            $data = [
                'groups' => [],
                'heading' => $subject,
            ];

            $emailToCheck = false;

            $fullName = false;
            $company = false;
            $firstName = false;
            $lastName = false;
            $fromName = isset($settings['default_sender_name']) ? $settings['default_sender_name'] : 'noreply';
            $fromEmail = isset($settings['default_sender_email']) ? $settings['default_sender_email'] : 'noreply@noreply.com';

            if (!$structureElement->destination) {
                $receiverEmail = isset($settings['default_sender_email']) ? $settings['default_sender_email'] : false;
            } else {
                $receiverEmail = $structureElement->destination;
            }

            $files = [];

            $answerElementTitle = '';
            $answerFieldValues = [];
            $answerFiles = [];

            foreach ($structureElement->getCustomFieldsGroups() as $groupElement) {
                $groupInfo = [
                    'title' => $groupElement->title,
                    'formFields' => [],
                ];
                foreach ($groupElement->getFormFields() as $formField) {
                    if ($formField->fieldType == 'fileinput') {
                        $fileInputFieldName = $formField->fieldName;
                        if ($dataChunk = $structureElement->getDataChunk($fileInputFieldName)) {
                            $formFiles = $dataChunk->getStorageValue();
                            if (is_array($formFiles) && !empty($formFiles)) {
                                foreach ($formFiles as $file) {
                                    $answerFiles[$formField->id][] = [
                                        'originalName' => $file['name'],
                                        'tmp_name' => $file['tmp_name'],
                                    ];
                                }
                            }
                        }
                    } else {
                        $fieldName = $formField->fieldName;
                        $value = $structureElement->$fieldName;
                        if ($formField->fieldType == 'dateInput') {
                            // value from this input may be in YYYY-MM-DD format
                            // see also: feedbackElement::getCustomFieldsList workaround
                            $parts = explode('-', $value);
                            if (count($parts) === 3) {
                                $value = implode('.', array_reverse($parts));
                            }
                        }
                        $fieldInfo = [
                            'fieldName' => $fieldName,
                            'fieldTitle' => $formField->title,
                            'fieldType' => $formField->fieldType,
                            'fieldValue' => $value,
                        ];
                        $groupInfo['formFields'][] = $fieldInfo;

                        $answerFieldValues[$formField->id] = $value;

                        if ($formField->autocomplete == 'fullName') {
                            $fullName = $value;
                        } elseif ($formField->autocomplete == 'company') {
                            $company = $value;
                        } elseif ($formField->autocomplete == 'firstName') {
                            $firstName = $value;
                        } elseif ($formField->autocomplete == 'lastName') {
                            $lastName = $value;
                        } elseif ($formField->autocomplete == 'phone') {
                            $phone = $value;
                        } elseif ($formField->autocomplete == 'email') {
                            $emailToCheck = $value;
                        }
                    }
                }
                $data['groups'][] = $groupInfo;
            }
            $visitorManager = $this->getService(VisitorsManager::class);
            if ($visitor = $visitorManager->getCurrentVisitor()) {
                if ($firstName && $lastName) {
                    $visitor->firstName = $firstName;
                    $visitor->lastName = $lastName;
                    $fromName = $firstName . ' ' . $lastName;
                } elseif ($fullName) {
                    $visitor->firstName = $fullName;
                    $fromName = $fullName;
                } elseif ($company) {
                    $fromName = $company;
                }
                $visitor->email = $fromEmail;
                $visitor->phone = $phone;
                $visitorManager->updateVisitor($visitor);
            }
            $spamChecker = $this->getService(SpamChecker::class);
            if ($emailToCheck && !$spamChecker->checkEmail($emailToCheck)) {
                $structureElement->errorMessage = $translationsManager->getTranslationByName('feedback.emailsendingfailed');
            } else {
                $answerElement = $structureManager->createElement('feedbackAnswer', 'show', $structureElement->id,
                    false, 'feedbackAnswer');

                if ($answerElement) {
                    $answerElement->prepareActualData();
                    $answerElement->title = $answerElementTitle;
                    $answerElement->persistElementData();
                    if ($visitor) {
                        $event = new Event();
                        $event->setType('feedback');
                        $event->setVisitorId($visitor->id);
                        $event->setElementId($answerElement->id);
                        $eventLogger = $this->getService(EventsLog::class);
                        $eventLogger->saveEvent($event);
                    }
                    foreach ($answerFieldValues as $fieldId => $fieldValue) {
                        if (is_array($fieldValue)) {
                            foreach ($fieldValue as $value) {
                                $answerElement->addGenericValue($fieldId, $value);
                            }
                        } else {
                            $answerElement->addGenericValue($fieldId, $fieldValue);
                        }
                    }

                    $pathsManager = $this->getService(PathsManager::class);
                    $uploadsPath = $pathsManager->getPath('uploads');
                    $cachePath = $pathsManager->getPath('uploadsCache');
                    $files = [];
                    foreach ($answerFiles as $fieldId => $fileInfo) {
                        if (is_array($fileInfo)) {
                            foreach ($fileInfo as $file) {
                                $temporaryFile = $cachePath . basename($file['tmp_name']);
                                $fileElement = $structureManager->createElement('file', 'show', $answerElement->getPersistedId(),
                                    false, 'feedbackAnswerFile');
                                $fileElement->file = $fileElement->getPersistedId();
                                $fileElement->fileName = $file['originalName'];
                                $fileElement->title = $file['originalName'];
                                $fileElement->persistElementData();
                                copy($temporaryFile, $uploadsPath . $fileElement->file);
                                unlink($temporaryFile);
                                $files[] = $fileElement;
                            }
                        }
                    }
                    $emailDispatcher = $this->getService(EmailDispatcher::class);
                    $newDispatchment = $emailDispatcher->getEmptyDispatchment();
                    $newDispatchment->setFromName($fromName);
                    $newDispatchment->setFromEmail($fromEmail);
                    $newDispatchment->setSubject($subject);
                    $newDispatchment->setData($data);
                    if ($files) {
                        foreach ($files as $file) {
                            $newDispatchment->registerAttachment($uploadsPath . $file->id, $file->getFileName());
                        }
                    }
                    $newDispatchment->setReferenceId($structureElement->id);
                    $newDispatchment->setType('feedback');
                    $newDispatchment->registerReceiver($receiverEmail, null);

                    if ($emailDispatcher->startDispatchment($newDispatchment)) {
                        $structureElement->resultMessage = $translationsManager->getTranslationByName('feedback.emailsendingsuccess');
                        $this->ajaxFormSuccess = true;
                    } else {
                        $structureElement->errorMessage = $translationsManager->getTranslationByName('feedback.emailsendingfailed');
                    }
                }
            }
        }
        $this->sendAjaxFormResponse($structureElement);
    }

    public function getExtraModuleFields()
    {
        return $this->structureElement->getCustomModuleFields();
    }

    public function setValidators(&$validators)
    {
        $validators = $this->structureElement->getCustomValidators();
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = array_merge($expectedFields, $this->structureElement->getCustomExpectedFields());
    }
}
