<?php

abstract class socialPluginElement extends structureElement
    implements specialFieldsElementInterface
{
    use specialFieldsElementTrait;
    public $languagesParentElementMarker = 'adminLanguages';
    public $dataResourceName = 'module_social_plugin';
    protected $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $api;

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['icon'] = 'image';
        $moduleStructure['iconOriginalName'] = 'text';
        $moduleStructure['data'] = 'text';
        foreach ($this->getSpecialFields() as $fieldName => $specialField) {
            $moduleStructure[$fieldName] = $specialField['format'];
        }
    }

    protected function setMultiLanguageFields(&$multiLanguageFields)
    {
        $multiLanguageFields[] = 'title';
        foreach ($this->getSpecialFields() as $fieldName => $specialField) {
            if ($specialField['multiLanguage']) {
                $multiLanguageFields[] = $fieldName;
            }
        }
    }

    public abstract function getApiClass();

    public function getName()
    {
        return strtolower(str_replace('SocialPluginElement', '', get_class($this)));
    }

    public function getActions()
    {
        return [
            'registration',
            'login',
            'sharing',
            'like',
            'post',
            'widget',
        ];
    }

    public function getConnectionUrl()
    {
        return $this->getSocialActionUrl('connect');
    }

    public function getLoginUrl()
    {
        return $this->getSocialActionUrl('login');
    }

    final public function getApi()
    {
        if ($this->api === null) {
            $class = $this->getApiClass();
            $this->api = new $class();
            $data = $this->getSpecialData();
            $credentials = $data ? reset($data) : [];
            $credentials = $credentials ? array_map('trim', $credentials) : [];
            $this->api->setCredentials($credentials);
        }
        return $this->api;
    }

    public function getSocialActionUrl($action, $returnUrl = '')
    {
        $controller = $this->getService(controller::class);
        $returnUrl = $returnUrl ?: $controller->fullParametersURL;
        return $controller->baseURL . "social/action:$action/plugin:" . $this->id
            . '/?return=' . rawurlencode($returnUrl);
    }
}


