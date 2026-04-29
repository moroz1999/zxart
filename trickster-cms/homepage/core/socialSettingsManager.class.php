<?php

class socialSettingsManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $socialMediaUrls;
    private static $instance = null;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function __construct()
    {
        self::$instance = $this;
    }

    public function getSocialMediaUrls()
    {
        if ($this->socialMediaUrls === null) {
            $settingsManager = $this->getService(settingsManager::class);
            $settings = $settingsManager->getSettingsList();
            $this->socialMediaUrls = [
                'facebook' => "",
                'twitter' => "",
                'youtube' => "",
            ];
            foreach ($this->socialMediaUrls as $key => &$value) {
                if (isset($settings[$key . "_url"])) {
                    $this->socialMediaUrls[$key] = $settings[$key . "_url"];
                }
            }
        }
        return $this->socialMediaUrls;
    }
}


