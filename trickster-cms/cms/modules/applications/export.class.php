<?php

class exportApplication extends controllerApplication
{
    protected $applicationName = 'export';
    public $rendererName = 'smarty';
    protected $xml;
    protected $links;
    protected $nonMarkeredElementsIds = [];
    protected $ignoredNames = [
        'originalName',
        'photoOriginalName',
        'logoImageOriginalName',
        'backgroundImageOriginalName',
    ];
    protected $imageNames = [
        'image' => '',
        'photo' => [
            'postfix' => '_photo',
            'originalName' => 'photoOriginalName',
        ],
        'logoImage' => [
            'postfix' => 'logoImage',
            'originalName' => 'logoImageOriginalName',
        ],
        'backgroundImage' => [
            'postfix' => 'backgroundImage',
            'originalName' => 'backgroundImageOriginalName',
        ],
    ];
    protected $loadedElementsMarkers = [];
    protected $existingLinks = [];
    protected $modifications = [];
    protected $sourceElementsIds = [];
    protected $exportDir = '';
    protected $floorsNeedingEditing = [];
    /**@var structureManager */
    protected $structureManager;

    public function initialize()
    {
        set_time_limit(60 * 60);
        $this->startSession('admin', $this->getService(ConfigManager::class)->get('main.adminSessionLifeTime'));
        $this->createRenderer();
        $xml = new SimpleXMLElement('<?xml version="1.0"?><procedures></procedures>');
        $this->xml = $xml->addChild('procedures');
    }

    public function execute($controller)
    {
        $this->structureManager = $this->getService('adminStructureManager');
        $this->structureManager->setPrivilegeChecking(false);
        $this->structureManager->buildRequestedPath();

        if ($id = $controller->getParameter('id')) {
            $pathsManager = $this->pathsManager;
            $this->exportDir = $pathsManager->getPath('temporary') . 'exports/' . time() . '/';
            $dirMade = $pathsManager->ensureDirectory($this->exportDir);
            if (!$dirMade) {
                throw new Exception('Could not create export dir!');
            }

            $this->sourceElementsIds = explode(',', $id);
            foreach ($this->sourceElementsIds as $elementId) {
                if ($element = $this->structureManager->getElementById($elementId)) {
                    if (!$element->marker) {
                        $this->nonMarkeredElementsIds[] = $element->id;
                    }
                    $this->writeChildren($element);
                }
            }
            if ($this->links) {
                $linksXml = $this->xml->addChild('Links');
                foreach ($this->links as $link) {
                    $linkXml = $linksXml->addChild('link');
                    if (isset($link['parentMarker'])) {
                        $linkXml->addChild('parentMarker', $link['parentMarker']);
                    } else {
                        $linkXml->addChild('parentPath', $link['parentPath']);
                    }
                    if (isset($link['childMarker'])) {
                        $linkXml->addChild('childMarker', $link['childMarker']);
                    } else {
                        $linkXml->addChild('childPath', $link['childPath']);
                    }
                    $linkXml->addChild('type', $link['type']);
                }
            }
            $this->writeModifications();
            $this->writeFloorMapsFixes();
            if ($controller->getParameter('copyEditorUploads')) {
                $path = $pathsManager->getPath('editorUploads');
                if (is_dir($path)) {
                    $destination = $this->exportDir . 'files_to_mirror/userfiles/';
                    $pathsManager->ensureDirectory($destination);
                    static::copyDir($path, $destination);
                    $this->xml->addChild('CopyFiles', 'files_to_mirror/');
                }
            }
            $proceduresXml = $this->xml->asXML();
            $deploymentXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<deployment>
	<version>1.0.0</version>
	<type></type>
	<requiredVersions></requiredVersions>
	<description>Content export</description>
	<revision></revision>
	$proceduresXml
</deployment>
XML;
            $this->writeFile('deployment.xml', $deploymentXml);
            header("Content-type: text/xml");
            echo $deploymentXml;
        }
    }

    protected function writeFile($name, $contents)
    {
        file_put_contents($this->exportDir . $name, $contents);
    }

    protected function writeChildren($element)
    {
        $links = $this->getChildLinks($element->id);
        foreach ($links as $childLink) {
            if ($child = $this->structureManager->getElementById($childLink['childStructureId'])) {
                $this->generateChildXml($child, $element, $childLink['type']);
            }
        }
        foreach ($links as $linkInfo) {
            $type = $linkInfo['type'];
            $childId = $linkInfo['childStructureId'];
            $parentId = $element->id;
            $linkId = "$type-$childId-$parentId";
            if (!empty($this->existingLinks[$linkId])) {
                continue;
            }
            if ($child = $this->structureManager->getElementById($linkInfo['childStructureId'])) {
                $link = [
                    'type' => $linkInfo['type'],
                ];

                if ($this->isElementMarkerable($element)) {
                    $link['parentMarker'] = $this->getElementMarker($element);
                } else {
                    $link['parentPath'] = $element->structurePath;
                }

                if ($this->isElementMarkerable($child)) {
                    $link['childMarker'] = $this->getElementMarker($child);
                } else {
                    $link['childPath'] = $child->structurePath;
                }

                $this->links[] = $link;
                $this->existingLinks[$linkId] = true;
            }
        }
    }

    protected function generateChildXml($element, $parent, $linkType)
    {
        if (in_array($this->getElementMarker($element), $this->loadedElementsMarkers)) {
            return;
        }
        $marker = $this->getElementMarker($element);
        $elementXml = $this->xml->addChild('AddElement');
        $elementXml->addChild('type', $element->structureType);
        $elementXml->addChild('structureName', $element->structureName);
        $elementXml->addChild('marker', $marker);
        $this->loadedElementsMarkers[] = $marker;

        if ($element->structureType === 'floor' && is_array($element->getNodesInfo())) {
            $this->floorsNeedingEditing[] = $marker;
        }

        if (!in_array($parent->id, $this->nonMarkeredElementsIds)) {
            $elementXml->addChild('parentMarker', $this->getElementMarker($parent));
        } else {
            $elementXml->addChild('parentPath', $parent->structurePath);
        }
        $fieldsXml = $elementXml->addChild('fields');
        $multiLanguageFields = $element->getMultiLanguageFields();
        $singleLanguageData = [];
        $multiLanguageData = [];

        foreach ($element->getModuleData() as $languageId => $fields) {
            foreach ($fields as $fieldName => $fieldValue) {
                $chunk = $element->getDataChunk($fieldName);
                if ($chunk instanceof dateDataChunk) {
                    $fieldValue = $chunk->getFormValue();
                }
                if (isset($multiLanguageFields[$fieldName])) {
                    $this->getLanguageCode($languageId);
                    $multiLanguageData[$fieldName][$this->getLanguageCode($languageId)] = $fieldValue;
                } else {
                    $singleLanguageData[$fieldName] = $fieldValue;
                }
            }
        }
        foreach ($singleLanguageData as $name => $value) {
            if (in_array($name, $this->ignoredNames) || $value === null) {
                continue;
            }
            if ($name == 'fixedId' && $value) {
                $valueMarker = $this->isElementMarkerable($value) ? $this->getElementMarker($value) : '';
                $this->modifications[] = [
                    'targetMarker' => $marker,
                    'field' => $name,
                    'valueId' => $value,
                    'valueMarker' => $valueMarker,
                ];
                continue;
            }
            if (isset($this->imageNames[$name])) {
                if ($name == 'image') {
                    $originalName = 'originalName';
                } else {
                    $originalName = $name . 'OriginalName';
                }
                $pathsManager = $this->pathsManager;
                $uploadsPath = $pathsManager->getPath('uploads');
                $deploymentImagesPath = $this->exportDir . 'images/';
                $pathsManager->ensureDirectory($deploymentImagesPath);
                copy(
                    $uploadsPath . $value,
                    $deploymentImagesPath . $value
                );
                rename(
                    $deploymentImagesPath . $value,
                    $deploymentImagesPath . $element->getValue($originalName)
                );
                $fieldXml = $fieldsXml->addChild('field', $element->getValue($originalName));
                $fieldXml->addAttribute('postfix', $this->imageNames[$name]['postfix']);
                $fieldXml->addAttribute('originalName', $this->imageNames[$name]['originalName']);
            } else {
                $fieldXml = $fieldsXml->addChild('field', $value);
            }
            $fieldXml->addAttribute('name', $name);
        }
        foreach ($multiLanguageData as $name => $languages) {
            foreach ($languages as $languageCode => $value) {
                $fieldXml = $fieldsXml->addChild('field', $value);
                $fieldXml->addAttribute('name', $name);
                $fieldXml->addAttribute('languageCode', $languageCode);
            }
        }
        //        foreach ($this->getNonStructureParentLinks($element->id) as $linkInfo) {
        //            $type = $linkInfo['type'];
        //            $childId = $element->id;
        //            $parentId = $linkInfo['parentStructureId'];
        //            $linkId = "$type-$childId-$parentId";
        //            if (!empty($this->existingLinks[$linkId])) {
        //                continue;
        //            }
        //            if ($child = $this->structureManager->getElementById($linkInfo['parentStructureId'])) {
        //                $link = array(
        //                    'type' => $linkInfo['type'],
        //                );
        //
        //                if ($this->isElementMarkerable($child)) {
        //                    $link['parentMarker'] = $this->getElementMarker($child);
        //                } else {
        //                    $link['parentPath'] = $child->structurePath;
        //                }
        //
        //                if ($this->isElementMarkerable($element)) {
        //                    $link['childMarker'] = $this->getElementMarker($element);
        //                } else {
        //                    $link['childPath'] = $element->structurePath;
        //                }
        //
        //                $this->links[] = $link;
        //                $this->existingLinks[$linkId] = true;
        //            }
        //        }
        if ($linkType != 'structure') {
            $options = [
                'removeStructureLink' => true,
            ];
            $optionsXml = $elementXml->addChild('options');
            foreach ($options as $optionName => $optionValue) {
                $optionXml = $optionsXml->addChild('option', $optionValue);
                $optionXml->addAttribute('name', $optionName);
            }
        } else {
            $childId = $element->id;
            $parentId = $parent->id;
            $linkId = "structure-$childId-$parentId";
            $this->existingLinks[$linkId] = true;
        }
        $this->writeChildren($element);
    }

    protected function writeFloorMapsFixes()
    {
        if (!$this->floorsNeedingEditing) {
            return;
        }
        $script = "<?php

use App\Paths\PathsManager;\n";
        $script .= '$floorMarkers = ' . var_export($this->floorsNeedingEditing, true) . ";\n";
        $script .= <<<'PHP'
$structureManager = $this->getService('structureManager');
foreach ($floorMarkers as $marker) {
    $element = $structureManager->getElementByMarker($marker);
    if (!$element) {
        continue;
    }
    $nodesInfo = $element->getNodesInfo();
    $rooms = isset($nodesInfo['room']) ? $nodesInfo['room'] : [];
    $newInfo = [];
    foreach ($rooms as $id => $data) {
        $objectMarker = "room$id";
        $newId = $structureManager->getElementIdByMarker($objectMarker);
        if ($newId) {
            $newInfo[$newId] = $data;
        }
    }
    $nodesInfo['room'] = $newInfo;

    $icons = isset($nodesInfo['icon']) ? $nodesInfo['icon'] : [];
    $newInfo = [];
    foreach ($icons as $id => $data) {
        $objectMarker = "icon$id";
        $newId = $structureManager->getElementIdByMarker($objectMarker);
        if ($newId) {
            $newInfo[$newId] = $data;
        }
    }
    $nodesInfo['icon'] = $newInfo;
    $element->setNodesInfo($nodesInfo);
}

PHP;
        $this->writeFile('floormapsfixes.php', $script);
        $procedure = $this->xml->addChild('PhpScript');
        $procedure->addAttribute('path', 'floormapsfixes.php');
    }

    protected function writeModifications()
    {
        if (!$this->modifications) {
            return;
        }
        $script = "<?php

use App\Paths\PathsManager;\n";
        $script .= '$modifications = ' . var_export($this->modifications, true) . ";\n";
        $script .= <<<'PHP'
$structureManager = $this->getService('structureManager');
foreach ($modifications as $modification) {
    $target = $structureManager->getElementByMarker($modification['targetMarker']);
    if (!$target) {
        throw new Exception('bad target marker!');
    }
    $relatedId = $structureManager->getElementIdByMarker($modification['valueMarker']);
    if (!$relatedId) {
        throw new Exception('bad related marker!');
    }
    $field = $modification['field'];
    $target->$field = $relatedId;
    $target->persistElementData();
}

PHP;
        $this->writeFile('modifications.php', $script);
        $procedure = $this->xml->addChild('PhpScript');
        $procedure->addAttribute('path', 'modifications.php');

        //        foreach ($this->modifications as $modification) {
        //            $elementXml = $this->xml->addChild('ModifyElement');
        //            $elementXml->addChild('targetMarker', $modification['targetMarker']);
        //            $fieldsXml = $elementXml->addChild('fields');
        //            $name = $modification['field'];
        //            if ($modification['valueMarker']) {
        //                $fieldXml = $fieldsXml->addChild('field', $modification['valueMarker']);
        //                $fieldXml->addAttribute('writeElementIdByMarker', true);
        //                $fieldXml->addAttribute('name', $name);
        //            } elseif ($valueElement = $this->structureManager->getElementById($modification['valueId'])) {
        //                $value = $valueElement->structurePath;
        //                $fieldXml = $fieldsXml->addChild('field', $value);
        //                $fieldXml->addAttribute('writeElementIdByPath', true);
        //                $fieldXml->addAttribute('name', $name);
        //            }
        //        }
    }

    protected function getLanguageCode($languageId)
    {
        $languagesManager = $this->getService(LanguagesManager::class);

        $publicLanguages = $languagesManager->getLanguagesList($this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic'));
        $adminLanguages = $languagesManager->getLanguagesList('adminLanguages');
        $languageCode = '';
        foreach ($adminLanguages as $lang) {
            if ($lang->id == $languageId) {
                $languageCode = $lang->iso6393;
                break;
            }
        }
        if (!$languageCode) {
            foreach ($publicLanguages as $lang) {
                if ($lang->id == $languageId) {
                    $languageCode = $lang->iso6393;
                    break;
                }
            }
        }

        return $languageCode;
    }

    protected function getElementMarker($elementOrId)
    {
        if ($elementOrId instanceof structureElement) {
            $element = $elementOrId;
        } else {
            $element = $this->structureManager->getElementById($elementOrId);
        }
        if ($element->marker) {
            return $element->marker;
        } else {
            $marker = $element->structureType . $element->id;
            //            $element->marker = $marker;
            return $marker;
        }
    }

    protected function isElementMarkerable($elementOrId)
    {
        $id = $elementOrId;
        if ($elementOrId instanceof structureElement) {
            $id = $elementOrId->id;
        }
        if (in_array($id, $this->nonMarkeredElementsIds)) {
            return false;
        }

        return true;
    }

    protected function getChildLinks($elementId)
    {
        $db = $this->getService('db');
        $sql = $db->table('structure_links');
        $sql->where('parentStructureId', '=', $elementId);
        $sql->orderBy('position', 'asc');
        return $sql->get();
    }

    protected function getNonStructureParentLinks($elementId)
    {
        $db = $this->getService('db');
        $sql = $db->table('structure_links');
        $sql->where('childStructureId', '=', $elementId);
        $sql->where('type', '<>', 'structure');
        return $sql->get();
    }

    protected static function copyDir($source, $target)
    {
        if (!is_dir($source)) {//it is a file, do a normal copy
            copy($source, $target);
            return;
        }
        //it is a folder, copy its files & sub-folders
        @mkdir($target);
        $d = dir($source);
        $navFolders = ['.', '..'];
        while (false !== ($fileEntry = $d->read())) {//copy one by one
            //skip if it is navigation folder . or ..
            if (in_array($fileEntry, $navFolders)) {
                continue;
            }
            //do copy
            $s = "$source/$fileEntry";
            $t = "$target/$fileEntry";
            static::copyDir($s, $t);
        }
        $d->close();
    }
}


