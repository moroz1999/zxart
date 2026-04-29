<?php

use App\Paths\PathsManager;

class receiveSimpleSetting extends structureElementAction
{
    protected $loggable = true;

    public function execute(
        &$structureManager,
        &$controller,
        &$structureElement
    ) {
        if ($this->validated) {
            $structureElement->prepareActualData();

            $configManager = $this->getService(ConfigManager::class);
            $colorsConfig = $configManager->getConfig('colors');
            if ($colorsConfig) {
                $colors = $colorsConfig->getLinkedData();
                $settingName = $structureElement->getFormValue('structureName');
                if (isset($colors[$settingName])) {
                    if (!$structureElement->value) {
                        $structureElement->value = $colors[$structureElement->structureName];
                    } elseif (strpos($structureElement->value, '#') === false) {
                        $structureElement->value = '#' . $structureElement->value;
                    }
                    $cachePath = $this->getService(PathsManager::class)
                        ->getPath('cssCache');
                    if ($handler = opendir($cachePath)) {
                        while (($fileName = readdir($handler)) !== false) {
                            $filePath = $cachePath . $fileName;
                            if ($fileName === '.' || $fileName === '..' || $fileName === '_marker'
                                || !is_file($filePath)
                            ) {
                                continue;
                            } else {
                                unlink($filePath);
                            }
                        }
                        closedir($handler);
                    }
                }
            }

            $structureElement->persistElementData();
            $settingsManager = $this->getService(settingsManager::class);
            $settingsManager->generateSettingsFile();
            $controller->redirect($structureElement->URL);
        }
        $structureElement->executeAction("showForm");
    }

    public function setExpectedFields(&$expectedFields)
    {
        $expectedFields = ['structureName', 'value'];
    }

    public function setValidators(&$validators)
    {
        $validators['structureName'][] = 'notEmpty';
    }
}
