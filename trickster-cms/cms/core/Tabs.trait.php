<?php

use App\Paths\PathsManager;

trait TabsTrait
{
    /**
     * @var Tab[]
     */
    protected $tabs;
    protected $tabsTemplate;
    protected $controller;

    public function getTabs()
    {
        if ($this->tabs === null) {
            $this->initTabs();
        }
        return $this->tabs;
    }

    /**
     * @var Tab $tab
     * @param bool $order
     */
    protected function addTab($tab, $order = false)
    {
        $privileges = $this->getPrivileges();
        if ($this->hasActualStructureInfo() && isset($privileges[$tab->getAction()])) {
            $this->removeTab($tab->getName());
            if (isset($privileges[$tab->getAction()])) {
                if ($order !== false && $order <= count($this->tabs)) {
                    array_splice($this->tabs, $order, 0, [$tab]);
                } else {
                    $this->tabs[] = $tab;
                }
            }
        }
    }

    protected function hasTab($tabName)
    {
        if ($this->tabs) {
            foreach ($this->tabs as $tab) {
                if ($tab->getName() == $tabName) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function removeTab($tabName)
    {
        if ($this->tabs) {
            foreach ($this->tabs as $key => $tab) {
                if ($tab->getName() == $tabName) {
                    unset($this->tabs[$key]);
                }
            }
        }
    }

    protected function initTabs()
    {
        foreach ($this->getTabsList() as $tabName) {
            $this->addTabByName($tabName);
        }

        if (method_exists($this, 'setTabs')) {
            //todo: remove after 10.2020
            $this->logError('Deprecated method called ' . __CLASS__ . '::setTabs');
        }
    }

    protected function getTabsList()
    {
        return [
            'showFullList',
            'showForm',
            'showLayoutForm',
            'showPositions',
            'showPrivileges',
        ];
    }

    protected function addTabByName($tabName, $order = false)
    {
        if ($tab = $this->getTabObject($tabName)) {
            $this->addTab($tab, $order);
        }
    }

    //get tab from current tabs list, you can edit its translation or something else
    protected function getTab($tabName)
    {
        if ($this->tabs) {
            foreach ($this->tabs as $tab) {
                if ($tab->getName() == $tabName) {
                    return $tab;
                }
            }
        }

        return false;
    }

    //get tab object, not from current tabs list, you can add it using addTab
    protected function getTabObject($tabName)
    {
        $tab = false;
        /**
         * @var PathsManager $pathsManager
         */
        $pathsManager = $this->getService(PathsManager::class);
        $path = $pathsManager->getRelativePath('tabs');
        if ($filePath = $pathsManager->getIncludeFilePath($path . $tabName . '.class.php')) {
            $className = $tabName . 'Tab';
            if (!class_exists($className) && is_file($filePath)) {
                include_once($filePath);
            }
            if (class_exists($className)) {
                /**
                 * @var Tab $tab
                 */
                $tab = new $className();
                $tab->setStructureElement($this);
                $tab->setName($tabName);
                $tab->setLabel('tab.' . $tabName);
            }
        }

        return $tab;
    }

    /**
     * @return mixed
     *
     * @deprecated
     * todo: remove after 10.2020
     */
    public function getTabsTemplate($log = true)
    {
        if ($log) {
            $this->logError('Deprecated method used ' . __CLASS__ . '::getTabsTemplate');
        }
        if (is_null($this->tabs)) {
            $this->initTabs();
        }
        return $this->tabsTemplate;
    }

    /**
     * @param $tabsTemplate
     *
     * @deprecated
     */
    public function setTabsTemplate($tabsTemplate)
    {
        //todo: remove after 10.2020
        $this->logError('Deprecated method used ' . __CLASS__ . '::setTabsTemplate');

        $this->tabsTemplate = $tabsTemplate;
    }
}