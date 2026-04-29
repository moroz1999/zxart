<?php

use App\Paths\PathsManager;

class feedbackElement extends dynamicGroupFieldsStructureElement
{
    use ConfigurableLayoutsProviderTrait;

    public $dataResourceName = 'module_feedback';
    protected $allowedTypes = ['formFieldsGroup'];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $answers;

    public function getFormAllowedTypes()
    {
        return $this->allowedTypes;
    }

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['destination'] = 'text';
        $moduleStructure['content'] = 'html';
        $moduleStructure['buttonTitle'] = 'text';
        $moduleStructure['role'] = 'text';

        $moduleStructure['layout'] = 'text';
        $moduleStructure['colorLayout'] = 'text';
    }
// children, getElementsChildren
/*
        $parentElementId,
        $allowedRoles = null,
        $linkTypes = 'structure',
        $allowedTypes = null,
        $restrictLinkTypes = false
*/

    protected function getTabsList()
    {
        return [
            'showForm',
            'showPositions',
            'showAnswers',
            'showLayoutForm',
        ];
    }

    // workaround
    // this disables date validation in mobile so that input type="date" could be used,
    // (value from date input is in format YYYY-MM-DD, validDateValidator expects DD.MM.YYYY)
    // TODO: make validDateValidator accept both or make a new validator?
    public function getCustomFieldsList()
    {
        if ($this->customFieldsList === null) {
            $this->customFieldsList = $this->getInheritedCustomFieldsList();
        }
        return $this->customFieldsList;
    }

    // end workaround

    public function getProductField()
    {
        $productField = false;
        if ($fields = $this->getCustomFieldsList()) {
            foreach ($fields as $field) {
                if ($field->autocomplete == "product") {
                    $productField = $field;
                    break;
                }
            }
        }
        return $productField;
    }

    public function setProductId($productId)
    {
        if ($productField = $this->getProductField()) {
            $structureManager = $this->getService('structureManager');
            if ($product = $structureManager->getElementById($productId)) {
                $this->setFormValue($productField->fieldName, $product->title . ' (' . $product->code . ')');
            }
        }
    }

    public function getAnswers()
    {
        if ($this->answers === null) {
            $this->answers = [];
            $structureManager = $this->getService('structureManager');
            foreach ($this->getAnswerIds() as $id) {
                $this->answers[] = $structureManager->getElementById($id);
            }
        }
        return $this->answers;
    }

    public function getAnswerIds()
    {
        return $this->getService(linksManager::class)->getConnectedIdList($this->id, 'feedbackAnswer', 'parent');
    }

    public function getExportArchive()
    {
        $result = '';
        $answers = $this->getAnswers();
        if (!$answers) {
            return $result;
        }
        $exportId = uniqid();
        $dir = $this->getService(PathsManager::class)->getPath('temporary');
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $dir = $dir . 'feedback_exports/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $exportsDir = $dir;
        $dir = $exportsDir . $exportId . '/';
        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $workspaceDir = $dir;

        $groups = $this->getCustomFieldsGroups();
        $fieldsIds = [];
        $header = [];

        $structureManager = $this->getService('structureManager');

        foreach ($groups as $groupElement) {
            foreach ($groupElement->getFormFields() as $field) {
                if ($field->structureType == 'formSelect') {
                    $children = $structureManager->getElementsChildren($field->id);
                    foreach ($children as $child) {
                        $fieldsIds[] = $child->id;
                        $header[$field->title . ': ' . $child->title] = 'string';
                    }
                } else {
                    $fieldsIds[] = $field->id;
                    $header[$field->title] = 'string';
                }
            }
        }
        $excelFile = 'export.xlsx';
        $writer = new XLSXWriter();
        $writer->writeSheetHeader('Sheet1', $header);

        $zipPath = $exportsDir . $exportId . '.zip';
        $zip = new ZipArchive;
        $zip->open($zipPath, ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE);

        $zippedStuff = [$excelFile];
        $pathsManager = $this->getService(PathsManager::class);
        foreach ($this->getAnswers() as $answer) {
            $answerValues = $answer->getGenericValues();
            $answerValuesNew = [];
            foreach ($answerValues as $key => $answerValue) {
                if (is_array($answerValue)) {
                    foreach ($answerValue as $key => $value) {
                        if ($value['checked'] == true) {
                            $answerValuesNew[$key] = '1';
                        } else {
                            $answerValuesNew[$key] = '';
                        }
                    }
                } else {
                    $answerValuesNew[$key] = $answerValue;
                }
                $answerValues = $answerValuesNew;
            }
            $answerFiles = $answer->getFiles();
            $answerList = [];

            foreach ($fieldsIds as $fieldId) {
                $fieldValue = '';
                if (isset($answerFiles[$fieldId])) {
                    $fileInfo = $answerFiles[$fieldId];
                    $filePath = $pathsManager->getPath('uploads') . $fileInfo['storageName'];
                    if (file_exists($filePath)) {
                        $originalName = $fileInfo['originalName'];
                        $originalName = TranslitHelper::convert($originalName);
                        $pathInfo = pathinfo($originalName);
                        $fileName = $pathInfo['filename'];
                        $extension = isset($pathInfo['extension']) ? $pathInfo['extension'] : '';
                        $newName = $originalName;
                        for ($i = 0; in_array($newName, $zippedStuff); ++$i) {
                            $newName = $fileName . '_' . $i;
                            if ($extension !== '') {
                                $newName .= '.' . $extension;
                            }
                        }
                        $fieldValue = $newName;
                        $zippedStuff[] = $newName;
                        $zip->addFile($filePath, $newName);
                    }
                } elseif (isset($answerValues[$fieldId])) {
                    $fieldValue = $answerValues[$fieldId];
                }
                $answerList[] = $fieldValue;
            }
            $writer->writeSheetRow('Sheet1', $answerList);
        }
        $writer->writeToFile($workspaceDir . $excelFile);
        $zip->addFile($workspaceDir . $excelFile, $excelFile);
        $zip->close();
        unlink($workspaceDir . $excelFile);
        rmdir($workspaceDir);
        return $zipPath;
    }

    public function getAnswersTable()
    {
        $groups = $this->getCustomFieldsGroups();
        $header = [];

        $fields = [];
        $columnLimit = 10;
        $i = 0;
        foreach ($groups as $groupElement) {
            foreach ($groupElement->getFormFields() as $field) {
                $fields[$field->id] = $field;
                $header[] = $field->title;
                if (++$i == $columnLimit) {
                    break;
                }
            }
        }

        $answersInfo = [];
        foreach ($this->getAnswers() as $answer) {
            $answerInfo = ['fields' => [], 'element' => $answer];
            $answerValues = $answer->getGenericValues();
            $answerFiles = $answer->getFiles();

            foreach ($fields as $fieldId => $field) {
                $fieldInfo = [
                    'type' => $field->fieldType,
                ];
                if ($field->fieldType == 'fileinput') {
                    if (!empty($answerFiles)) {
                        $fieldInfoArray = [];
                        foreach ($answerFiles as $file) {
                            $filesInfo['originalName'] = $file['originalName'];
                            $filesInfo['link'] = $file['link'];
                            $fieldInfo['files'][] = $filesInfo;
                        }

                    }
                }
                else {
                    if (!empty($answerValues[$field->id])) {
                        $fieldInfo['value'] = $answerValues[$field->id];
                    } else {
                        $fieldInfo['value'] = '';
                    }
                }
                $answerInfo['fields'][] = $fieldInfo;
            }
            $answersInfo[] = $answerInfo;
        }
        return ['header' => $header, 'answers' => $answersInfo];
    }

    public function getHiddenFieldsData()
    {
        $outputData = [];

        foreach ($this->getCustomFieldsGroups() as $group) {
            foreach ($group->getFormFields() as $field) {
                if ($field->fieldType == 'select') {
                    $selectData = [];
                    foreach ($field->getOptionsList() as $option) {
                        if ($hiddenFields = $option->getFieldsToBeHidden()) {
                            $optionFields = [];
                            foreach ($hiddenFields as $hiddenField) {
                                $optionFields[] = $hiddenField['id'];
                            }
                            $selectData[] = [
                                'optionValue' => $option->title,
                                'fields' => $optionFields,
                            ];
                        }
                    }
                    $outputData[] = [
                        'selectId' => $field->id,
                        'selectionType' => $field->getSelectionType(),
                        'options' => $selectData,
                    ];
                }
            }
        }

        return $outputData;
    }

    /**
     * @return array
     */
    public function getInheritedCustomFieldsList()
    {
        if (is_null($this->customFieldsList)) {
            $this->customFieldsList = [];
            if ($groups = $this->getInheritedCustomFieldsGroups()) {
                foreach ($groups as $group) {
                    if ($fields = $group->getFormFields()) {
                        foreach ($fields as &$field) {
                            $this->customFieldsList[] = $field;
                        }
                    }
                }
            }
        }
        return $this->customFieldsList;

    }


    /**
     * @return array
     */
    protected function getInheritedCustomFieldsGroups()
    {
            $structureManager = $this->getService('structureManager');
            /**
             * @var feedbackElement $this
             */
            if ($groups = $structureManager->getElementsChildren($this->id, null, 'structure', $this->allowedTypes)) {
                foreach ($groups as $group) {
                    if ($group->getFormFields()) {
                        $this->customFieldsGroups[] = $group;
                    }
                }
            }
        return $this->customFieldsGroups;
    }

}


