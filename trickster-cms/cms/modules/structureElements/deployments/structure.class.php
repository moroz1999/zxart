<?php

class deploymentsElement extends structureElement
{
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_generic';
    public $defaultActionName = 'show';
    public $role = 'container';
    protected $updatesDeployments;
    protected $error = '';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
    }

    protected function getTabsList()
    {
        return [
            'show',
            'showUpdates',
        ];
    }

    public function getUpdates()
    {
        if ($this->updatesDeployments === null) {
            $this->updatesDeployments = [];
            try {
                $deploymentManager = $this->getService(DeploymentManager::class);
                $configManager = $this->getService(ConfigManager::class);
                $updatesApi = $this->getService(UpdatesApi::class);
                $installedDeployments = $configManager->get('deployment.deployments');
                $installedPlugins = [];
                foreach ($installedDeployments as $deployment) {
                    $installedPlugins[$deployment['type']] = true;
                }
                $types = array_keys($installedPlugins);
                $this->updatesDeployments = $updatesApi->getDeployments($types);
                foreach ($this->updatesDeployments as $key => $update) {
                    if ($deploymentManager->isVersionInstalled($update->type, $update->version)) {
                        unset($this->updatesDeployments[$key]);
                    }
                }
            } catch (Exception $e) {
                $this->error = $e->getMessage();
            }
        }
        return $this->updatesDeployments;
    }

    public function installUpdates()
    {
        $updates = $this->getUpdates();
        if (!$updates) {
            return true;
        }
        $deploymentManager = $this->getService(DeploymentManager::class);
        $deploymentManager->clearPendingDeployments();
        try {
            $updatesApi = $this->getService(UpdatesApi::class);
            foreach ($updates as $update) {
                $path = $updatesApi->downloadDeployment($update->id);
                $deploymentManager->addPendingDeployment($update->type, $update->version, $path);
            }
            $deploymentManager->installPendingDeployments();
            $this->updatesDeployments = [];
            $updatesApi->updateNotify();
        } catch (Exception $e) {
            $this->error = $e->getMessage();
            return false;
        }
        return true;
    }

    public function getError()
    {
        return $this->error;
    }
}
