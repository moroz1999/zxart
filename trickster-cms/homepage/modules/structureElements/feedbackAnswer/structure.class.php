<?php

class feedbackAnswerElement extends dynamicGroupFieldsStructureElement
{
    public $dataResourceName = 'module_feedback_answer';
    protected $allowedTypes = [''];
    public $defaultActionName = 'show';
    public $role = 'content';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    public function getFeedbackForm()
    {
        $structureManager = $this->getService('structureManager');
        return $structureManager->getElementsFirstParent($this->id);
    }

    public function getAdminReport()
    {
        $result = [];
        $form = $this->getFeedbackForm();
        $groups = [];
        if ($form) {
            $groups = $form->getCustomFieldsGroups();
        }
        $genericValues = $this->getGenericValues();
        $files = $this->getFiles();

        $groupsInfo = [];
        foreach ($groups as $groupElement) {
            $groupInfo = [
                'title' => $groupElement->title,
                'fields' => [],
            ];
            foreach ($groupElement->getFormFields() as $field) {
                $fieldInfo = [
                    'title' => $field->title,
                    'type' => $field->fieldType,
                ];
                if ($field->fieldType != 'fileinput') {
                    if (!empty($genericValues[$field->id])) {
                        $fieldInfo['value'] = $genericValues[$field->id];
                    } else {
                        $fieldInfo['value'] = '';
                    }
                } else {
                    if (!empty($files)) {
                        foreach ($files as $file) {
                            $item['originalName'] = $file['originalName'];
                            $item['link'] = $file['link'];
                            $fieldInfo['fileInput'][] = $item;
                        }
                    }
                }
                $groupInfo['fields'][] = $fieldInfo;
            }
            $groupsInfo[] = $groupInfo;
        }
        $result['groups'] = $groupsInfo;
        return $result;
    }

    public function getGenericValues()
    {
        $result = [];
        $collection = persistableCollection::getInstance("module_feedback_answer_values");
        $conditions = [
            [
                "answerId",
                "=",
                $this->id,
            ],
        ];
        $records = $collection->conditionalLoad(['fieldId', 'value'], $conditions, [], []);
        $records = $records ? $records : [];

        $structureManager = $this->getService('structureManager');

        foreach ($records as $record) {
            $children = $structureManager->getElementsChildren($record['fieldId']);

            if (!empty($children)) {
                foreach ($children as $child) {
                    //redo the check to check against id-s $structureManager->getElementsParents($child->id);
                    if ($record['value'] == $child->title) {
                        $result[$record['fieldId']][$child->id] = [
                            'checked' => true,
                            'name' => $child->title,
                        ];
                    } else {
                        if (!isset($result[$record['fieldId']][$child->id])) {
                            $result[$record['fieldId']][$child->id] = [
                                'checked' => false,
                                'name' => $child->title,
                            ];
                        }
                    }
                }
            } else {
                $result[$record['fieldId']] = $record['value'];
            }
        }
        return $result;
    }

    public function getFiles()
    {
        $result = [];
        /**
         * @var structureManager $structureManager
         */
        $structureManager = $this->getService('structureManager');
        $connectedFiles = $structureManager->getElementsChildren($this->id, null, 'feedbackAnswerFile');
        if(!empty($connectedFiles)) {
            foreach ($connectedFiles as $fileElement) {
                if(!empty($fileElement)) {
                    $item['originalName'] = $fileElement->getFileName();
                    $item['link'] = $fileElement->getDownloadUrl();
                    $result[] = $item;
                }
            }
        }
        return $result;
    }

    public function addGenericValue($fieldId, $fieldValue)
    {
        $collection = persistableCollection::getInstance('module_feedback_answer_values');
        $dataObject = $collection->getEmptyObject();
        $dataObject->answerId = $this->id;
        $dataObject->fieldId = $fieldId;
        $dataObject->value = $fieldValue;
        $dataObject->persist();
    }

    public function addFile($fieldId, $fileInfo)
    {
        $collection = persistableCollection::getInstance('module_feedback_answer_files');
        $dataObject = $collection->getEmptyObject();
        $dataObject->answerId = $this->id;
        $dataObject->fieldId = $fieldId;
        $dataObject->originalName = $fileInfo['originalName'];
        $dataObject->storageName = $fileInfo['storageName'];
        $dataObject->persist();
    }

    public function deleteElementData()
    {
        $collection = persistableCollection::getInstance('module_feedback_answer_values');
        $searchFields = ['answerId' => $this->id];
        $records = $collection->load($searchFields);
        foreach ($records as $record) {
            $record->delete();
        }
        $collection = persistableCollection::getInstance('module_feedback_answer_files');
        $searchFields = ['answerId' => $this->id];
        $records = $collection->load($searchFields);
        foreach ($records as $record) {
            $record->delete();
        }
        parent::deleteElementData();
    }

    public function getInheritedCustomFieldsList(){}
}

