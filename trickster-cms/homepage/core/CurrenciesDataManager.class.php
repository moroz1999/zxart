<?php

// deprecated since 2016.03
class CurrenciesDataManager extends errorLogger
{
    private $currenciesData;

    public function setCurrenciesData($currenciesData)
    {
        $this->currenciesData = $currenciesData;
    }

    public function getCurrenciesData()
    {
        return $this->currenciesData;
    }

    public function generateConfigFile()
    {
        if (is_array($this->currenciesData)) {
            $configManager = controller::getInstance()->getConfigManager();
            $config = $configManager->getConfig('currencies');
            $config->set('list', $this->currenciesData);
            $config->save();
        } else {
            $this->logError("CurrenciesData is not an array");
        }
    }
}

