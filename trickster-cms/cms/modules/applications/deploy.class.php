<?php

class deployApplication extends controllerApplication
{
    protected $applicationName = 'deploy';
    public $rendererName = 'smarty';
    /**
     * @var DesignTheme
     */
    protected $theme;
    /**
     * @var DeploymentManager
     */
    protected $deploymentManager;
    /**
     * @var controller
     */
    protected $controller;
    /**
     * @var structureManager
     */
    protected $structureManager;

    public function initialize()
    {
        set_time_limit(60 * 60);
        $this->createRenderer();
        $this->deploymentManager = $this->getService(DeploymentManager::class);
        $this->structureManager = $this->getService('structureManager');
        $this->structureManager->setPrivilegeChecking(false);
        $this->controller = controller::getInstance();
    }

    public function execute($controller)
    {
        $action = 'index';
        foreach ($controller->requestedPath as $action) {
            break;
        }
        $configManager = $this->getService(ConfigManager::class);
        $config = $configManager->getConfig('deployment');
        if ($config->isEmpty() && defined('CONFIGURATION_PATH') && is_file(CONFIGURATION_PATH . 'configuration_deployment.php')) {
            $config = $configManager->getConfigFromPath(CONFIGURATION_PATH . 'configuration_deployment.php');
        }
        $allowed = $config->get('enabled');
        if ($allowed && method_exists($this, 'action' . $action)) {
            $this->renderer->assign('action', $action);
            call_user_func([$this, 'action' . $action]);
        } else {
            echo '404';
            //            $this->renderer->fileNotFound();
        }
    }

    protected function actionIndex()
    {
        $this->setupTheme();
        $this->renderer->assign('controller', $this->controller);
        $this->renderer->assign('theme', $this->theme);
        $this->renderer->assign('deploymentManager', $this->deploymentManager);
        $deployments = $this->deploymentManager->getLocalDeployments();
        $this->renderer->assign('deployments', $deployments);
        $this->renderer->setCacheControl('no-cache');
        $this->renderer->template = $this->theme->template('index.tpl');
        $this->renderer->display();
    }

    protected function actionInstall()
    {
        $type = $this->controller->getParameter('type');
        $version = $this->controller->getParameter('version');
        $deployment = $this->deploymentManager->getDeploymentData($type, $version);
        if ($deployment) {
            try {
                $this->deploymentManager->installDeployment($deployment);
                $resultMessage = 'Deployment success!';
            } catch (Exception $e) {
                $resultMessage = $e->getMessage();
            }
        } else {
            $resultMessage = 'Deployment not found';
        }
        $this->renderer->assign('resultMessage', $resultMessage);
        $this->actionIndex();
    }

    protected function actionNew()
    {
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method == 'POST') {
            $dom = new DOMDocument();
            $dom->encoding = 'utf-8';
            $deploymentElement = $dom->createElement('deployment');
            $dom->appendChild($deploymentElement);

            $versionElement = $deploymentElement->appendChild($dom->createElement('version'));
            $versionElement->appendChild($dom->createTextNode($this->controller->getParameter('version')));
            $requiresElement = $deploymentElement->appendChild($dom->createElement('requiredVersions'));
            if ($parameter = $this->controller->getParameter('requires')) {
                $requiredVersions = explode(',', $parameter);
                foreach ($requiredVersions as $version) {
                    $element = $requiresElement->appendChild($dom->createElement('version'));
                    $element->appendChild($dom->createTextNode($version));
                }
            }
            $deploymentElement->appendChild($dom->createElement('description', $this->controller->getParameter('description')));
            $proceduresElement = $deploymentElement->appendChild($dom->createElement('procedures'));
            if ($parameter = $this->controller->getParameter('markers')) {
                $markers = explode(',', $parameter);
                foreach ($markers as $marker) {
                    $marker = trim($marker);
                    $element = null;
                    if ($marker) {
                        $elementId = $this->structureManager->getElementIdByMarker($marker);
                        if ($elementId) {
                            $element = $this->structureManager->getElementById($elementId);
                        }
                    }
                    if ($element) {
                        foreach ($element->getChildrenList() as $child) {
                            $procudureElement = $proceduresElement->appendChild($dom->createElement('AddElement'));
                            $procudureElement->appendChild($dom->createElement('parentMarker', $marker));
                            $this->getElementDeploymentInfo($dom, $procudureElement, $child);
                        }
                    }
                }
            }
            if ($parameter = $this->controller->getParameter('markers')) {
                $markers = explode(',', $parameter);
                foreach ($markers as $marker) {
                    $marker = trim($marker);
                    $element = null;
                    if ($marker) {
                        $elementId = $this->structureManager->getElementIdByMarker($marker);
                        if ($elementId) {
                            $element = $this->structureManager->getElementById($elementId);
                        }
                    }
                    if ($element) {
                        $procudureElement = $proceduresElement->appendChild($dom->createElement('AddUserGroup'));
                        $procudureElement->appendChild($dom->createElement('name', $element->structureName));
                        $procudureElement->appendChild($dom->createElement('description', $element->groupName));
                        $procudureElement->appendChild($dom->createElement('marker', $marker));
                    }
                }
            }
            if ($parameter = $this->controller->getParameter('privileges')) {
                $moduleTypes = explode(',', $parameter);
                foreach ($moduleTypes as $moduleType) {
                    $moduleType = trim($moduleType);
                    $this->getPrivilegesDeploymentInfo($dom, $proceduresElement, $moduleType);
                }
            }
            $this->getTranslationsDeploymentInfo($dom, $proceduresElement
                , $this->controller->getParameter('translations'));
            $this->getTranslationsDeploymentInfo($dom, $proceduresElement
                , $this->controller->getParameter('adminTranslations'), true);
            $xml = $dom->saveXML();

            header("Content-type: text/xml");
            header("Pragma: no-cache");
            echo $xml;
            exit;
        }
        $this->actionIndex();
    }

    protected function getPrivilegesDeploymentInfo(DOMDocument $dom, DOMElement $proceduresElement, $moduleName)
    {
        $db = $this->getService('db');
        $privilegesIndex = [];
        $records = $db->table('privilege_relations')->where('module', '=', $moduleName)->get();
        foreach ($records as $record) {
            $userId = $record['userId'];
            $elementId = $record['elementId'];
            if (!isset($privilegesIndex[$userId])) {
                $privilegesIndex[$userId] = [];
            }
            if (!isset($privilegesIndex[$userId][$elementId])) {
                $privilegesIndex[$userId][$elementId] = [];
            }
            $privilegesIndex[$userId][$elementId][] = $record['action'];
        }
        foreach ($privilegesIndex as $userId => $elementsIndex) {
            $userGroup = $this->structureManager->getElementById($userId);
            if (!$userGroup || !$userGroup->marker) {
                continue;
            }
            foreach ($elementsIndex as $elementId => $actions) {
                $element = $this->structureManager->getElementById($elementId);
                if (!$element || !$element->marker) {
                    continue;
                }
                $procedureElement = $proceduresElement->appendChild($dom->createElement('AddUserPrivileges'));
                $procedureElement->appendChild($dom->createElement('userGroupMarker', $userGroup->marker));
                $procedureElement->appendChild($dom->createElement('targetElementMarker', $element->marker));
                $procedureElement->appendChild($dom->createElement('privilege', 'allow'));
                $actionsElement = $procedureElement->appendChild($dom->createElement('actions'));
                foreach ($actions as $action) {
                    $actionsElement->appendChild($dom->createElement('action', $action));
                }
            }
        }
    }

    protected function getTranslationsDeploymentInfo(
        DOMDocument $dom,
        DOMElement $proceduresElement,
        $parameter,
        $admin = false
    ) {
        $groupNames = explode(',', $parameter);
        if (!$groupNames) {
            return;
        }
        $element = null;
        $groupsIndex = [];
        $translationsMarker = $admin ? 'adminTranslations' : 'public_translations';
        $elementId = $this->structureManager->getElementIdByMarker($translationsMarker);
        if ($elementId) {
            $element = $this->structureManager->getElementById($elementId);
            foreach ($element->getChildrenList() as $translationGroup) {
                $groupsIndex[$translationGroup->title] = $translationGroup;
            }
        }
        $languagesMarker = $admin ? 'adminLanguages' : $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
        $type = $admin ? 'adminTranslation' : 'translation';
        $languagesList = $this->getService(LanguagesManager::class)->getLanguagesList($languagesMarker);
        foreach ($groupNames as $groupName) {
            if (!isset($groupsIndex[$groupName])) {
                continue;
            }
            $translationGroup = $groupsIndex[$groupName];
            foreach ($translationGroup->getChildrenList() as $translation) {
                $procudureElement = $proceduresElement->appendChild($dom->createElement('AddTranslation'));
                $procudureElement->appendChild($dom->createElement('type', $type));
                $procudureElement->appendChild($dom->createElement('code'
                    , "$groupName.$translation->structureName"));
                $procudureElement->appendChild($dom->createElement('valueType', $translation->valueType));
                $valuesElement = $procudureElement->appendChild($dom->createElement('values'));
                $values = $translation->getTranslationData();
                foreach ($values[$translation->getCode()] as $langId => $translationString) {
                    $valueElement = $valuesElement->appendChild($dom->createElement('value', $translationString));
                    $languageCode = '';
                    foreach ($languagesList as $lang) {
                        if ($lang->id == $langId) {
                            $languageCode = $lang->iso6393;
                            break;
                        }
                    }
                    $valueElement->setAttribute('languageCode', $languageCode);
                }
            }
        }
    }

    protected function getElementDeploymentInfo(DOMDocument $dom, DOMNode $elementNode, structureElement $element)
    {
        $elementNode->appendChild($dom->createElement('type', $element->structureType));
        $fieldsNode = $elementNode->appendChild($dom->createElement('fields'));

        $moduleData = $element->getModuleData();
        $multiLanguageFields = $element->getMultiLanguageFields();
        $languagesManager = $this->getService(LanguagesManager::class);
        $languagesIndex = [];
        $languagesList = $languagesManager->getLanguagesList($this->getService(ConfigManager::class)
            ->get('main.rootMarkerPublic'));
        $languagesList = array_merge($languagesList, $languagesManager->getLanguagesList('adminLanguages'));
        foreach ($languagesList as $language) {
            $languagesIndex[$language->id] = $language->iso6393;
        }
        $addedFields = [];
        foreach ($moduleData as $languageId => $values) {
            foreach ($values as $key => $value) {
                if (!$value || (!isset($multiLanguageFields[$key]) && isset($addedFields[$key]))) {
                    continue;
                }
                if ($value) {
                    $moduleDataField = $fieldsNode->appendChild($dom->createElement('field', $value));
                    $moduleDataField->setAttribute('name', $key);
                    $addedFields[$key] = true;
                    if (isset($multiLanguageFields[$key]) && $languageId != 0 && isset($languagesIndex[$languageId])) {
                        $moduleDataField->setAttribute('languageCode', $languagesIndex[$languageId]);
                    }
                }
            }
        }
        if ($children = $element->getChildrenList()) {
            $childrenNode = $elementNode->appendChild($dom->createElement('children'));
            foreach ($children as $child) {
                $childNode = $childrenNode->appendChild($dom->createElement('child'));
                $this->getElementDeploymentInfo($dom, $childNode, $child);
            }
        }
    }

    protected function actionUpload()
    {
    }

    protected function actionInstallPending()
    {
        $result = 'fail';
        try {
            if ($this->deploymentManager->hasPendingDeployments()) {
                $this->deploymentManager->installPendingDeployment();
                $result = 'success';
            }
        } catch (Throwable $e) {
            // Executed only in PHP 7, will not match in PHP 5
            $result = $e->getMessage();
        } catch (Exception $e) {
            // Executed only in PHP 5, will not be reached in PHP 7
            $result = $e->getMessage();
        }
        $lastDeployment = $this->deploymentManager->getLastAttemptedInstall();
        if ($result !== 'success' && $lastDeployment !== null) {
            $result .= ' / Last deployment attempted to install: ' . $lastDeployment->getType() . ' ' . $lastDeployment->getVersion();
        }
        echo $result;
    }

    protected function setupTheme()
    {
        $designThemesManager = $this->getService(DesignThemesManager::class);
        $designThemesManager->setCurrentThemeCode($this->applicationName);
        $this->theme = $designThemesManager->getCurrentTheme();
    }
}

