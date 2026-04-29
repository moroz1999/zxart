<?php

abstract class DesignTheme extends errorLogger
{
    use ImageUrlGenerator;

    protected $inheritedThemes = [];
    protected $cssResourcesIdIndex = [];
    protected $javascriptResourcesIdIndex = [];
    protected $cssResources;
    protected $javascriptResources;
    protected $templateResources;
    protected $cssFiles;
    protected $javascriptFiles;
    protected $cssPath;
    protected $javascriptPath;
    protected $javascriptPaths;
    protected $javascriptUrl;
    protected $imagesUrl;
    protected $fontsUrl;
    protected $imagesFolder;
    protected $fontsFolder;
    protected $imagesPath;
    protected $fontsPath;
    protected $templatesFolder;
    protected $code;
    protected $extraFolder;
    protected $imagesPaths;
    protected $fontsPaths;
    /**
     * @var DesignThemesManager
     */
    protected $designThemesManager;

    /**
     * @param DesignThemesManager $designThemesManager
     * @param $code
     */
    public function __construct($designThemesManager, $code)
    {
        $this->code = $code;
        $this->designThemesManager = $designThemesManager;
        $this->initialize();
    }

    /**
     * This method is called after the creation of DesignTheme object.
     * Method's purpose is to define all required paths and file lists for the design theme.
     * @abstract
     * @return
     */
    abstract function initialize();

    public function setExtraFolder($extraFolder)
    {
        $this->extraFolder = $extraFolder;
    }

    /**
     * Returns array of CSS resources, including inherited CSS resources.
     * Each CSS resource info consists of filename, file path, images folder to use for images and unique hash
     * @return array
     */
    public function getCssResources()
    {
        if (is_null($this->cssResources)) {
            $this->cssResources = [];
            $this->loadCssResources();
        }
        return $this->cssResources;
    }

    protected function loadCssResources()
    {
        //traverse in reversed order to maintain true CSS priorities
        $inheritedThemes = array_reverse($this->inheritedThemes);
        foreach ($inheritedThemes as &$themeCode) {
            if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                if (!is_null($this->extraFolder)) {
                    $theme->setExtraFolder($this->extraFolder);
                }
                $this->cssResources = array_merge($this->cssResources, $theme->getCssResources());
                foreach ($this->cssResources as &$resourceInfo) {
                    $this->cssResourcesIdIndex[$resourceInfo['id']] = true;
                }
            }
        }

        $filesList = $this->getOwnCSSFilesList();
        $imagesPath = $this->getImagesPath();
        $imagesUrl = $this->getImagesUrl();
        $fontsUrl = $this->getFontsUrl();
        $imagesFolder = $this->getImagesFolder();
        $fontsFolder = $this->getFontsFolder();
        foreach ($filesList as &$file) {
            $info = [
                'filePath' => $this->cssPath,
                'fileName' => $file,
                'imagesPath' => $imagesPath,
                'imagesUrl' => $imagesUrl,
                'fontsUrl' => $fontsUrl,
                'imagesFolder' => $imagesFolder,
                'fontFolder' => $fontsFolder,
                'id' => md5($this->cssPath . $file . $imagesFolder),
            ];
            if (!isset($this->cssResourcesIdIndex[$info['id']])) {
                $this->cssResourcesIdIndex[$info['id']] = true;
                $this->cssResources[] = $info;
            }
        }
    }

    public function getCssResource($fileName)
    {
        $result = false;
        if ($resources = $this->getCssResources()) {
            foreach ($resources as &$resource) {
                if ($resource['fileName'] == $fileName) {
                    $result = $resource;
                }
            }
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getJavascriptResources()
    {
        if (is_null($this->javascriptResources)) {
            $this->javascriptResources = [];
            $inheritedThemes = array_reverse($this->inheritedThemes);
            foreach ($inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    $this->javascriptResources = array_merge($this->javascriptResources, $theme->getJavascriptResources());
                    foreach ($this->javascriptResources as &$resourceInfo) {
                        $this->javascriptResourcesIdIndex[$resourceInfo['id']] = true;
                    }
                }
            }
            if (is_array($this->javascriptFiles)) {
                foreach ($this->javascriptFiles as &$file) {
                    $info = [
                        'filePath' => $this->getJavascriptPath(),
                        'fileUrl' => $this->getJavascriptUrl(),
                        'fileName' => $file,
                        'id' => $file,
                    ];
                    if (!isset($this->javascriptResourcesIdIndex[$info['id']])) {
                        $this->javascriptResourcesIdIndex[$info['id']] = true;
                        $this->javascriptResources[] = $info;
                    } else {
                        foreach ($this->javascriptResources as $key => &$resourceInfo) {
                            if ($resourceInfo['id'] == $info['id']) {
                                $this->javascriptResources[$key] = $info;
                                break;
                            }
                        }
                    }
                }
            }
        }

        return $this->javascriptResources;
    }

    /**
     * Returns list of template folders paths including the inherited folders from parent objects in themes' chain
     * @return array
     */
    public function getTemplateResources()
    {
        if (is_null($this->templateResources)) {
            $this->templateResources = [];
            if (!is_null($this->templatesFolder)) {
                $this->templateResources[] = $this->templatesFolder;
            }

            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    $this->templateResources = array_merge($this->templateResources, $theme->getTemplateResources());
                }
            }
        }
        return $this->templateResources;
    }

    /**
     * Returns images folder of current theme. If it's undefined, then it's taken form inherited themes
     *
     * @return string|null
     */
    public function getImagesFolder()
    {
        if ($this->imagesFolder === null) {
            $this->imagesFolder = false;
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($folder = $theme->getImagesFolder()) {
                        $this->imagesFolder = $folder;
                        break;
                    }
                }
            }
        }
        return $this->imagesFolder;
    }

    /**
     * Returns fonts folder of current theme. If it's undefined, then it's taken form inherited themes
     *
     * @return string|null
     */
    public function getFontsFolder()
    {
        if ($this->fontsFolder === null) {
            $this->fontsFolder = false;
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($folder = $theme->getFontsFolder()) {
                        $this->fontsFolder = $folder;
                        break;
                    }
                }
            }
        }
        return $this->fontsFolder;
    }

    public function getImageDataUri($imageFileName)
    {
        $result = '';
        $imagesFolder = $this->getImagesFolder();
        $imagePath = $imagesFolder . $imageFileName;
        if (file_exists($imagePath)) {
            $sizeInfo = getimagesize($imagePath);
            $result = 'data:' . $sizeInfo['mime'] . ';base64,' . base64_encode(file_get_contents($imagePath));
        }
        return $result;
    }

    /**
     * Returns absolute path to images folder.
     * If own path is null, then the inherited images path is used
     *
     * @return string|boolean
     */
    public function getImagesPath()
    {
        return $this->imagesPath;
    }

    /**
     * Returns absolute URL to images folder.
     * If own path is null, then the inherited images path is used
     *
     * @return string|boolean
     */
    public function getImagesUrl()
    {
        if ($this->imagesUrl === null) {
            $this->imagesUrl = false;
            if ($folder = $this->getImagesFolder()) {
                $controller = controller::getInstance();
                $this->imagesUrl = $controller->baseURL . $folder;
            }
        }
        return $this->imagesUrl;
    }

    /**
     * Returns absolute URL to fonts folder.
     * If own path is null, then the inherited fonts path is used
     *
     * @return string|boolean
     */
    public function getFontsUrl()
    {
        if ($this->fontsUrl === null) {
            $this->fontsUrl = false;
            if ($folder = $this->getFontsFolder()) {
                $controller = controller::getInstance();
                $this->fontsUrl = $controller->baseURL . $folder;
            }
        }
        return $this->fontsUrl;
    }

    /**
     * Returns relative path to javascript files.
     * If own path is null, then the inherited javascript path is used
     *
     * @return string|boolean
     */
    public function getJavascriptPath()
    {
        if (is_null($this->javascriptPath)) {
            $this->javascriptPath = false;
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($path = $theme->getJavascriptPath()) {
                        $this->javascriptPath = $path;
                        break;
                    }
                }
            }
        }
        return $this->javascriptPath;
    }

    /**
     * Returns array of self and inherited paths to javascript files.
     * If own path is null, then the inherited javascript path is used
     *
     * @return string[]
     */
    public function getJavascriptPaths()
    {
        if (is_null($this->javascriptPaths)) {
            $this->javascriptPaths = [];
            if (!is_null($this->javascriptPath)) {
                $this->javascriptPaths[] = $this->getJavascriptPath();
            }
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    $this->javascriptPaths = array_merge($this->javascriptPaths, $theme->getJavascriptPaths());
                }
            }
        }
        return $this->javascriptPaths;
    }

    /**
     * Returns relative url to javascript files.
     * If own url is null, then the inherited javascript url is used
     *
     * @return string|boolean
     */
    public function getJavascriptUrl()
    {
        if (is_null($this->javascriptUrl)) {
            $this->javascriptUrl = false;
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($url = $theme->getJavascriptUrl()) {
                        $this->javascriptUrl = $url;
                        break;
                    }
                }
            }
        }
        return $this->javascriptUrl;
    }

    /**
     * Returns relative path to templates folder.
     * If own path is null, then the inherited templates path is used
     *
     * @return string|boolean
     */
    public function getTemplatesFolder()
    {
        if (is_null($this->templatesFolder)) {
            $this->templatesFolder = false;
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($folder = $theme->getTemplatesFolder()) {
                        $this->templatesFolder = $folder;
                        break;
                    }
                }
            }
        }
        return $this->templatesFolder;
    }

    /**
     * Returns an array of CSS files of this theme to combine the overall CSS resources list
     * If cssFilesList is null, then the whole contents of CSS directory will be returned
     *
     * @return string[]
     */
    protected function getOwnCSSFilesList()
    {
        if (is_null($this->cssFiles)) {
            $this->cssFiles = [];
            if (is_dir($this->cssPath)) {
                $directoryContents = scandir($this->cssPath);
                foreach ($directoryContents as &$contentFile) {
                    $extension = strtolower(pathinfo($contentFile, PATHINFO_EXTENSION));
                    if ($extension == 'css' || $extension == 'less') {
                        $this->cssFiles[] = $contentFile;
                    }
                }
            }

            if (!is_null($this->extraFolder)) {
                $extraFolderCssPath = $this->cssPath . $this->extraFolder . "/";
                if (is_dir($extraFolderCssPath)) {
                    $directoryContents = scandir($extraFolderCssPath);
                    foreach ($directoryContents as &$contentFile) {
                        $extension = strtolower(pathinfo($contentFile, PATHINFO_EXTENSION));
                        if ($extension == 'css' || $extension == 'less') {
                            $this->cssFiles[] = $this->extraFolder . '/' . $contentFile;
                        }
                    }
                }
            }
        }
        return $this->cssFiles;
    }

    /**
     * Searches for the file path in inherited template resoures chain and returns a full path
     *
     * @param string $template
     * @param boolean $mayFail
     * @return bool|string
     */
    public function template($template, $mayFail = false)
    {
        if ($path = $this->templateExists($template)) {
            return $path;
        }
        if ($mayFail === false) {
            $this->logError("Template file is missing: " . $template);
        }
        if ($path = $this->templateExists('default.tpl')) {
            return $path;
        }
        return false;
    }

    /**
     * Returns uncompiled template source
     *
     * @param string $template
     * @param boolean $mayFail
     * @return bool|string
     */
    public function getTemplateSource($template, $json = false)
    {
        if ($path = $this->template($template)) {
            if ($content = file_get_contents($path)) {
                if ($json) {
                    return json_encode($content);
                } else {
                    return $content;
                }
            }
        }
        return false;
    }

    public function templateExists($template)
    {
        if ($resources = $this->getTemplateResources()) {
            foreach ($resources as &$resource) {
                $templatePath = $resource . $template;
                if (is_file($templatePath)) {
                    return $templatePath;
                }
            }
        }
        return false;
    }

    /**
     * Returns list of image folders paths including the inherited folders from parent objects in themes' chain
     * @return array
     */
    public function getImagesPaths()
    {
        if (is_null($this->imagesPaths)) {
            $this->imagesPaths = [];
            if (!is_null($this->imagesPath)) {
                $this->imagesPaths[] = $this->imagesPath;
            }
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    $this->imagesPaths = array_merge($this->imagesPaths, $theme->getImagesPaths());
                }
            }
        }
        return $this->imagesPaths;
    }

    /**
     * Returns list of font folders paths including the inherited folders from parent objects in themes' chain
     * @return array
     */
    public function getFontsPaths()
    {
        if (is_null($this->fontsPaths)) {
            $this->fontsPaths = [];
            if (!is_null($this->fontsPath)) {
                $this->fontsPaths[] = $this->fontsPath;
            }
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    $this->fontsPaths = array_merge($this->fontsPaths, $theme->getFontsPaths());
                }
            }
        }
        return $this->fontsPaths;
    }

    /**
     * Searches for the file URL in inherited template resoures chain and returns a full URL
     *
     * @param string $fileName
     * @param bool $recursion
     * @param bool $required
     * @return string
     */
    public function getImageUrl($fileName, $recursion = false, $required = true)
    {
        if (is_file($this->imagesPath . $fileName)) {
            return $this->getImagesUrl() . $fileName;
        }
        foreach ($this->getImagesPaths() as $item) {
            if (is_file($item . $fileName)) {
                $controller = controller::getInstance();
                $imageUrl = str_replace(ROOT_PATH, $controller->baseURL, $item . $fileName);
                return $imageUrl;
            }
        }
        foreach ($this->inheritedThemes as &$themeCode) {
            if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                if ($imageUrl = $theme->getImageUrl($fileName, true, $required)) {
                    return $imageUrl;
                }
            }
        }
        if (!$recursion && $required) {
            $this->logError("Image file is missing: " . $fileName);
        }
        return "";
    }

    /**
     * Searches for the file URL in inherited template resoures chain and returns a full URL
     *
     * @param string $fileName
     * @param bool $recursion
     * @param bool $required
     * @return string
     */
    public function getFontUrl($fileName, $recursion = false, $required = true)
    {
        if (is_file($this->fontsPath . $fileName)) {
            return $this->getFontsUrl() . $fileName;
        }
        foreach ($this->getFontsPaths() as $item) {
            if (is_file($item . $fileName)) {
                $controller = controller::getInstance();
                $fontUrl = str_replace(ROOT_PATH, $controller->baseURL, $item . $fileName);
                return $fontUrl;
            }
        }
        foreach ($this->inheritedThemes as &$themeCode) {
            if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                if ($fontUrl = $theme->getFontUrl($fileName, true, $required)) {
                    return $fontUrl;
                }
            }
        }
        if (!$recursion && $required) {
            $this->logError("Font file is missing: " . $fileName);
        }
        return "";
    }

    /**
     * Searches for the file path in inherited template resoures chain and returns a full path
     *
     * @param string $fileName
     * @param bool $recursion
     * @return string
     */
    public function getImagePath($fileName, $recursion = false)
    {
        if (is_file($this->imagesPath . $fileName)) {
            return $this->imagesPath . $fileName;
        } else {
            foreach ($this->inheritedThemes as &$themeCode) {
                if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                    if ($imageUrl = $theme->getImagePath($fileName, true)) {
                        return $imageUrl;
                    }
                }
            }
        }
        if (!$recursion) {
            $this->logError("Image file is missing: " . $fileName);
        }
        return "";
    }

    public function getCode()
    {
        return $this->code;
    }

    public function getInheritedThemes()
    {
        return $this->inheritedThemes;
    }

    public function getCssFoldersList()
    {
        $cssFolders = [];

        foreach ($this->inheritedThemes as &$themeCode) {
            if ($theme = $this->designThemesManager->getTheme($themeCode)) {
                $cssFolders = array_merge($cssFolders, $theme->getCssFoldersList());
            }
        }

        $cssFolders[] = $this->cssPath;
        return $cssFolders;
    }

    public function appendCssResourceFromTheme($resourceName, $themeName)
    {
        if ($theme = $this->designThemesManager->getTheme($themeName)) {
            if ($resource = $theme->getCssResource($resourceName)) {
                if (!isset($this->cssResourcesIdIndex[$resource['id']])) {
                    $this->cssResourcesIdIndex[$resource['id']] = true;
                    $this->cssResources[] = $resource;
                }
            }
        }
    }

    protected function appendCssResourceFromInheritedThemes($resourceName, $themeName)
    {
        if ($theme = $this->designThemesManager->getTheme($themeName)) {
            foreach ($theme->getInheritedThemes() as $inheritedTheme) {
                $this->appendCssResourceFromInheritedThemes($resourceName, $inheritedTheme);
                $this->appendCssResourceFromTheme($resourceName, $inheritedTheme);
            }
        }
    }

    public function getFavicon()
    {
        if (file_exists(PUBLIC_PATH . "favicon.ico")) {
            return controller::getInstance()->baseURL . "favicon.ico";
        } else {
            if (!file_exists(PUBLIC_PATH . "images/favicon.png")) {
                $pallets = [
                    [
                        'bg' => [229, 31, 0],
                        'color' => [255, 255, 255],
                    ],
                    [
                        'bg' => [196, 1, 162],
                        'color' => [255, 255, 255],
                    ],
                    [
                        'bg' => [12, 55, 238],
                        'color' => [255, 255, 255],
                    ],
                    [
                        'bg' => [12, 198, 226],
                        'color' => [0, 0, 0],
                    ],
                    [
                        'bg' => [40, 219, 74],
                        'color' => [219, 60, 40],
                    ],
                    [
                        'bg' => [255, 255, 255],
                        'color' => [0, 0, 0],
                    ],
                ];
                $pallet = $pallets[array_rand($pallets)];

                if (!$letter = strtoupper(substr(basename(dirname(ROOT_PATH)), 0, 1))) {
                    $letter = 'A';
                };

                $newImageFile = fopen(PUBLIC_PATH . "images/favicon.png", "w");
                $newImage = imagecreatetruecolor(16, 16);
                $background = imagecolorallocate($newImage, $pallet['bg'][0], $pallet['bg'][1], $pallet['bg'][2]);
                $color = imagecolorallocate($newImage, $pallet['color'][0], $pallet['color'][1], $pallet['color'][2]);
                imagefill($newImage, 0, 0, $background);
                imagestring($newImage, 8, 4, 0, $letter, $color);
                imagepng($newImage, $newImageFile);
                imagedestroy($newImage);
                fclose($newImageFile);
            }
            return controller::getInstance()->baseURL . "project/images/favicon.png";
            //            return $this->getImageUrl("icons/favicon.ico");
        }
    }

}
