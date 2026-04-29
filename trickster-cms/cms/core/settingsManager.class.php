<?php

use App\Paths\PathsManager;

class settingsManager implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $settingsList;
    private string $cachePath;
    private $fileName = 'settings.php';


    public function __construct()
    {
    }

    public function getSettingsList()
    {
        if (!isset($this->settingsList)) {
            $this->loadSettingsList();
        }

        return $this->settingsList;
    }

    public function getSetting($name)
    {
        if ($this->settingsList === null) {
            $this->loadSettingsList();
        }
        if (isset($this->settingsList[$name])) {
            return $this->settingsList[$name];
        }
        return false;
    }
    private function getCachePath()
    {
        if (!isset($this->cachePath)){
            $this->cachePath = $this->getService(PathsManager::class)->getPath('settingsCache');;
        }
        return $this->cachePath;
    }
    protected function loadSettingsList()
    {
        $settingsList = [];

        $filePath = $this->getCachePath() . $this->fileName;
        if (file_exists($filePath)) {
            include $filePath;
        } else {
            $this->generateSettingsFile();
        }
        $this->settingsList = $settingsList;
    }

    /**
     * Generate settings data, cache files
     */
    public function generateSettingsFile()
    {
        /**
         * @var [] $allData array
         * @var string $fileName settings file name
         */
        $allData = [];

        //needed for installer app's CSS generation.
        try {
            /**
             * Get data and push to $allData array
             */
            $db = $this->getService('db');
            $query = $db->table('module_simplesetting')
                ->leftJoin('structure_elements', 'module_simplesetting.id', '=', 'structure_elements.id')
                ->leftJoin('module_language', 'module_simplesetting.id', '=', 'module_language.id')
                ->select('structureName', 'value');

            if ($querySettings = $query->get()) {
                foreach ($querySettings as $setting) {
                    $allData[$setting['structureName']] = $setting['value'];
                }
                $this->settingsList = $allData;
            }
            $this->getService(PathsManager::class)->ensureDirectory($this->getCachePath());

            /**
             * Create cache files with settings data
             */
            $filePath = $this->getCachePath() . $this->fileName;
            $text = $this->generateSettingsText($allData);
            file_put_contents($filePath, $text);
        } catch (Exception $exception) {

        }
    }

    protected function generateSettingsText($languageData)
    {
        $text = '<?php

use App\Paths\PathsManager; $settingsList = array(';
        foreach ($languageData as $name => &$value) {
            $text .= '"' . $name . '"=>"' . $value . '",';
        }
        $text .= '); ?>';

        return $text;
    }
}



