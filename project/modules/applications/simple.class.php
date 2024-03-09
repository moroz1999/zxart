<?php

class simpleApplication extends publicApplication implements ThemeCodeProviderInterface
{
    protected $applicationName = 'simple';
    protected $protocolRedirection = false;

    public function initialize()
    {
        parent::initialize();
        $this->themeCode = 'simple';
        $this->controller->setProtocol('http://');
    }

    public function getUrlName()
    {
        return 'simple';
    }
}
