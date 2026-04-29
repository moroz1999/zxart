<?php
define("QUERY_PARAMETERS_SEPARATOR", ':');

use App\Paths\PathsManager;
use DI\Container;
use DI\ContainerBuilder;

class controller
{
    /**
     * @var controllerApplication
     */
    private $application;
    private $urlApplicationName;
    private $applicationName;
    private $defaultApplicationName = 'public';

    private $requestParameters = [];
    public $requestURI = [];
    public $requestedPath = [];
    public $requestedFile = false;
    private $protocol;
    public $domainName;
    public $directoryName;
    public $scriptName;
    public $domainURL;
    public $baseURL;
    public $rootURL;
    public $pathURL;
    public $fullURL;
    public $visitorIP;
    public $fullParametersURL;
    /**
     * @var ConfigManager
     */
    public $configManager;

    private $formData = [];
    private $forceDebug = false;
    private $debugMode = null;
    private static self $instance;
    private $enabledPlugins = [];
    /**
     * @var PathsManager
     */
    private $pathsManager;
    public $redirectDeprecatedParameters = false;
    private Container $container;

    public static function getInstance(?string $configurationFile = null): self
    {
        if (!isset(self::$instance)) {
            //sometimes during controller::_construct instance is asked already twice, so we have to make it instantly not null
            $controller = new self($configurationFile);
            self::$instance = $controller;
        }
        return self::$instance;
    }

    private function __construct($projectConfigPath)
    {
        ob_start();
        $corePath = __DIR__ . '/';
        include_once($corePath . "/App/Paths/PathsManager.php");
        include_once($corePath . "ConfigManager.php");
        include_once($corePath . "Config.class.php");
        include_once($corePath . "AutoLoadManager.php");
        $this->configManager = new ConfigManager();
        $this->pathsManager = new PathsManager();
        if ($projectPathsConfig = $this->configManager->getConfigFromPath($projectConfigPath . 'paths.php')) {
            if ($tricksterPath = $projectPathsConfig->get('trickster')) {
                if ($projectMainConfig = $this->configManager->getConfigFromPath($projectConfigPath . 'main.php')) {
                    if ($plugins = $projectMainConfig->get('enabledPlugins')) {
                        foreach (array_reverse($plugins) as $key => $dir) {
                            if ($key === 'project') {
                                $this->configManager->addSource(ROOT_PATH . $dir . 'config/', true);
                            } else {
                                $this->configManager->addSource(ROOT_PATH . $tricksterPath . $dir . 'config/');
                            }
                        }
                    }
                }
            }
        }


        $mainConfig = $this->configManager->getConfig('main');
        $pathsConfig = $this->configManager->getConfig('paths');
        $this->pathsManager->setConfig($pathsConfig);

        $composerClassLoaderPath = ROOT_PATH . $pathsConfig->get('psr0Classes') . 'autoload.php';
        if (is_file($composerClassLoaderPath)) {
            include_once($composerClassLoaderPath);
        }
        //autoloadmanager should be loaded after composer's autoload to be included into beginning of autoloaders stack
        new AutoLoadManager();

        $this->checkPlugins();
        ini_set("log_errors_max_len", 0);
        ini_set("pcre.backtrack_limit", 10000000);
        ini_set("memory_limit", "512M");

        //log all errors, but never display them
        set_error_handler([$this, 'catchError']);
        register_shutdown_function([$this, 'exitHandler']);
        if ($reporting = $mainConfig->get('errorReporting')) {
            ini_set("error_reporting", $reporting);
        }
        ini_set("display_errors", 0);

        mb_internal_encoding("UTF-8");
        date_default_timezone_set($mainConfig->get('timeZone'));
        $this->parseRequestParameters();

        if ($this->getDebugMode()) {
            ini_set("max_execution_time", 10 * 60);
        } else {
            ini_set("max_execution_time", 10);
        }
    }

    private function checkPlugins()
    {
        if ($enabledPluginsInfo = $this->configManager->get('main.enabledPlugins')) {
            $this->enabledPlugins = array_keys($enabledPluginsInfo);
            foreach ($enabledPluginsInfo as $pluginName => $pluginPath) {
                if ($pluginName === 'project') {
                    $this->addIncludePath(ROOT_PATH . $pluginPath);
                } else {
                    $this->addIncludePath(ROOT_PATH . $this->pathsManager->getRelativePath('trickster') . $pluginPath);
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getEnabledPlugins()
    {
        return $this->enabledPlugins;
    }

    private function parseRequestParameters()
    {
        $this->domainName = $_SERVER['HTTP_HOST'] ?? '';
        if ($this->isSsl()) {
            $this->protocol = 'https://';
        } else {
            $this->protocol = 'http://';
        }
        $this->domainURL = $this->configManager->get('main.protocol') . $this->domainName;
        $this->directoryName = trim(trim(dirname($_SERVER['SCRIPT_NAME']), '/'), '\\');
        $this->scriptName = basename($_SERVER['SCRIPT_NAME']);
        if (!empty($_SERVER['REMOTE_ADDR'])) {
            $this->visitorIP = $_SERVER['REMOTE_ADDR'];
        }

        //get the request array
        if (!empty($_SERVER["REQUEST_URI"])) {
            $this->requestURI = $this->parseRequestURI($_SERVER["REQUEST_URI"]);
        }
    }

    public function setProtocol($protocol)
    {
        $this->protocol = $protocol;
        $this->domainURL = $this->protocol . $this->domainName;
    }

    private function isSsl()
    {
        if (isset($_SERVER['HTTPS'])) {
            if (strtolower($_SERVER['HTTPS']) === 'on') {
                return true;
            }
            if ($_SERVER['HTTPS'] == '1') {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == '443')) {
            return true;
        }
        return false;
    }

    public function getApplication()
    {
        if ($this->application === null) {
            $applicationName = $this->getApplicationName();

            $this->requestedPath = $this->requestURI;
            $this->parseFormData();
            $this->detectFileName();

            // baseURL must be available before make() so DI factories can use it
            if ($this->directoryName != '' && $this->directoryName != '/') {
                $this->baseURL = $this->domainURL . '/' . $this->directoryName . '/';
            } else {
                $this->baseURL = $this->domainURL . '/';
            }

            $className = 'ZxArt\Controllers\\' . $this->toPascalCase($applicationName);
            if (!class_exists($className)) {
                $className = $this->applicationName . 'Application';
            }
            $this->application = $this->getContainer()->get($className);
            $this->application->setPathsManager($this->pathsManager);
            $this->requestParameters = $this->findRequestParameters($this->requestedPath);
            $this->urlApplicationName = $this->application->getUrlName();

            // rootURL depends on urlApplicationName which requires the application instance
            if ($this->urlApplicationName) {
                $this->rootURL = $this->baseURL . $this->urlApplicationName . '/';
            } else {
                $this->rootURL = $this->baseURL;
            }
            if ($this->requestedPath) {
                $this->pathURL = $this->rootURL . implode('/', $this->requestedPath) . '/';
            } else {
                $this->pathURL = $this->rootURL;
            }
            if ($this->requestURI) {
                $this->fullURL = $this->rootURL . implode('/', $this->requestURI) . '/';
            } else {
                $this->fullURL = $this->rootURL;
            }

            if ($imploded = $this->getParametersString(true)) {
                $this->fullParametersURL = $this->fullURL . $imploded;
            } else {
                $this->fullParametersURL = $this->fullURL;
            }

            $this->application->initialize();


            if ($this->redirectDeprecatedParameters) {
                if (empty($this->requestParameters['filename'])) {
                    $cssFileName = $this->requestedFile;
                } else {
                    $cssFileName = '';
                }
                $this->redirect($this->baseURL . $this->urlApplicationName . '/' . $this->getParametersString(true) . $cssFileName, '301');
            }
        }
        return $this->application;
    }

    public function dispatch(): mixed
    {
        $return = null;
        $returnForbidden = false;
        try {
            if ($application = $this->getApplication()) {
                if ($application instanceof ApplicationCacheInterface && $application->canServeCache()) {
                    $return = $this->$application();
                } else {
                    $return = $application->execute($this);
                    $application->executeEnd();
                }
            } else {
                $returnForbidden = true;
            }
        } catch (Exception $exception) {
            ErrorLog::getInstance()->logMessage('controller', $exception->getMessage() . "\n" . $exception->getTraceAsString());
        }

        if ($returnForbidden) {
            header('HTTP/1.0 403 Forbidden');
            exit;
        }
        return $return;
    }


    public function getDebugMode()
    {
        if ($this->debugMode === null) {
            if ($this->forceDebug) {
                $this->debugMode = true;
            } elseif (
                str_contains($this->domainName, 'localhost') ||
                str_contains($this->domainName, '.local') ||
                str_contains($this->domainName, '.loc') ||
                !str_contains($this->domainName, '.')
            ) {
                $this->debugMode = true;
            } else {
                $this->debugMode = false;
            }
        }
        return $this->debugMode;
    }

    public function getParametersString($encoded = false)
    {
        $imploded = "";
        foreach ($this->requestParameters as $key => $value) {
            if (!is_array($value)) {
                if ($encoded) {
                    $imploded .= $key . ":" . urlencode($value) . "/";
                } else {
                    $imploded .= $key . ":" . $value . "/";
                }
            }
        }
        return $imploded;
    }

    private function detectFileName()
    {
        if ($this->requestedPath) {
            $lastElement = end($this->requestedPath);
            if (stripos($lastElement, '.') !== false && stripos($lastElement, ':') === false) {
                $this->requestedFile = $lastElement;
            }
        }
    }

    private function toPascalCase(string $name): string
    {
        return str_replace('-', '', ucwords($name, '-'));
    }

    private function detectApplication()
    {
        if ($this->requestURI) {
            $applicationName = reset($this->requestURI);

            $className = '\ZxArt\Controllers\\' . $this->toPascalCase($applicationName);
            if (class_exists($className)) {
                $this->applicationName = $applicationName;
                return;
            }

            $fileDirectory = $this->pathsManager->getRelativePath('applications');
            $fileName = $this->pathsManager->getIncludeFilePath($fileDirectory . $this->toPascalCase($applicationName) . '.php');
            if (!$fileName) {
                $fileName = $this->pathsManager->getIncludeFilePath($fileDirectory . $applicationName . '.class.php');
            }
            if ($fileName) {
                $this->applicationName = $applicationName;
                array_shift($this->requestURI);
            }
        }
        if (!$this->applicationName) {
            $this->applicationName = $this->defaultApplicationName;
        }
    }

    private function findRequestParameters(&$requestURI)
    {
        //fill found parameters with $_GET and $_POST values.
        //$_REQUEST is not needed, because it contains cookies
        //POST has higher priority
        $foundParameters = [];
        if (isset($_POST)) {
            $foundParameters += $_POST;
        }
        if (isset($_GET)) {
            $foundParameters += $_GET;
        }
        //search for parameters divided with standard separator (colon?)
        foreach ($requestURI as $key => &$requestURIPart) {
            if (str_contains($requestURIPart, QUERY_PARAMETERS_SEPARATOR)) {
                $strings = explode(QUERY_PARAMETERS_SEPARATOR, $requestURIPart);
                if (!isset($foundParameters[$strings[0]])) {
                    $foundParameters[$strings[0]] = $strings[1];
                }
                unset($requestURI[$key]);
            }
        }

        return $foundParameters;
    }

    private function parseRequestURI($requestURI)
    {
        $requestString = urldecode($requestURI);

        //strip current subdirectory from request string if working not from the root directory on server
        if ($this->directoryName != '') {
            if (str_starts_with($requestURI, $this->directoryName)) {
                $requestString = substr($requestString, strlen($this->directoryName));
            } else {
                if (str_starts_with($requestURI, '/' . $this->directoryName)) {
                    $requestString = substr($requestString, strlen('/' . $this->directoryName));
                }
            }
        }

        //strip 'index.php' from request string if there was one
        $requestString = str_replace($this->scriptName, '', $requestString);

        //strip all GET parameters
        if ($position = strpos($requestString, '?')) {
            $requestString = substr_replace($requestString, '', $position);
        }

        //clean request string from possibly empty elements
        $requestArray = explode('/', $requestString);
        foreach ($requestArray as $key => &$name) {
            if (strlen(trim($name)) == 0) {
                unset($requestArray[$key]);
            }
        }
        return array_values($requestArray);
    }

    public function restart($newURL = null)
    {
        if (is_null($newURL)) {
            $newURL = $this->fullURL;
        }

        if ($this->domainURL != '') {
            if (str_starts_with($newURL, $this->domainURL)) {
                $newURL = substr($newURL, strlen($this->domainURL));
            }
        }
        if ($this->directoryName != '') {
            if (str_starts_with($newURL, $this->directoryName)) {
                $newURL = substr($newURL, strlen($this->directoryName));
            }
        }

        $_SERVER["REQUEST_URI"] = $newURL;

        $_POST = [];
        $_FILES = [];
        $_GET = [];

        $this->parseRequestParameters();
        $this->application = null;
        unset($this->container);
        $this->dispatch();

        exit();
    }

    public function redirect($newURL, $statusCode = '302')
    {
        if (is_null($newURL)) {
            $newURL = $this->fullURL;
        }
        $httpResponse = CmsHttpResponse::getInstance();
        $httpResponse->setStatusCode($statusCode);
        $httpResponse->setLocation($newURL);
        $httpResponse->sendHeaders();
        exit();
    }

    private function parseFormData()
    {
        $formData = [];
        if (isset($_FILES['formData'])) {
            foreach ($_FILES['formData'] as $fileProperty => $elementsList) {
                foreach ($elementsList as $elementId => $elementData) {
                    foreach ($elementData as $propertyName => $propertyValue) {
                        if (is_array($propertyValue)) {
                            $languageId = $propertyName;
                            foreach ($propertyValue as $fieldName => $fieldValue) {
                                $formData[$elementId][$languageId][$fieldName][$fileProperty] = $fieldValue;
                            }
                        } else {
                            $fieldName = $propertyName;
                            $fieldValue = $propertyValue;
                            $formData[$elementId][$fieldName][$fileProperty] = $fieldValue;
                        }
                    }
                }
            }
        }
        if (isset($_POST['formData'])) {
            foreach ($_POST['formData'] as $elementId => $elementData) {
                foreach ($elementData as $fieldName => $fieldValue) {
                    if (isset($formData[$elementId][$fieldName]) && is_array($formData[$elementId][$fieldName])) {
                        $formData[$elementId][$fieldName] = array_merge($formData[$elementId][$fieldName], $fieldValue);
                    } else {
                        $formData[$elementId][$fieldName] = $fieldValue;
                    }
                }
            }
        }

        $this->formData = $formData;
    }

    public function getElementFormData($elementId)
    {
        if (!is_numeric($elementId)) {
            foreach ($this->formData as $key => &$data) {
                if (!is_numeric($key)) {
                    return $data;
                }
            }
        } elseif (isset($this->formData[$elementId])) {
            return $this->formData[$elementId];
        }
        return false;
    }

    public function setApplicationName($applicationName)
    {
        $this->applicationName = $applicationName;
    }

    public function getApplicationName()
    {
        if (!$this->applicationName) {
            $this->detectApplication();
        }
        return $this->applicationName;
    }

    public function setDirectoryName($directoryName)
    {
        $this->directoryName = $directoryName;
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        return $this->requestParameters;
    }

    /**
     * @param $parameterName
     * @return mixed
     */
    public function getParameter($parameterName)
    {
        $value = false;
        if (isset($this->requestParameters[$parameterName])) {
            $value = $this->requestParameters[$parameterName];
        }
        return $value;
    }

    /**
     * @param string $includePath
     */
    public function addIncludePath($includePath)
    {
        $this->pathsManager->addIncludePath($includePath);
    }

    /**
     * @param $projectPath
     * @deprecated
     */
    public function setProjectPath($projectPath)
    {
        $this->addIncludePath($projectPath);
    }

    /**
     * @deprecated
     */
    public function getProjectPath()
    {
        $includePaths = $this->pathsManager->getIncludePaths();
        return end($includePaths);
    }

    /**
     * @return mixed
     * @deprecated since 04 2016
     */
    public function getIncludePaths()
    {
        return $this->pathsManager->getIncludePaths();
    }

    /**
     * @param $filePath
     * @return bool|string
     * @deprecated since 04 2016, use PathsManager
     */
    public function getIncludeFilePath($filePath)
    {
        return $this->pathsManager->getIncludeFilePath($filePath);
    }

    public function catchError($level, $message, $file, $line)
    {
        $currentErrorLevel = error_reporting();
        if ($currentErrorLevel & $level) {
            if (!str_contains($file, 'illuminate')) {
                ErrorLog::getInstance()->logMessage($file . ":" . $line, $message, $level);
            }
        }
        // Don't execute PHP internal error handler
        return true;
    }

    public function exitHandler()
    {
        if ($error = error_get_last()) {
            $this->catchError($error["type"], $error["message"], $error["file"], $error["line"]);
        }
    }

    public function setApplication($applicationName)
    {
        $this->applicationName = $applicationName;
    }

    /**
     * @return array
     */
    public function getRequestedPath()
    {
        return $this->requestedPath;
    }

    public function getConfigManager()
    {
        return $this->configManager;
    }

    public function getPathsManager()
    {
        return $this->pathsManager;
    }

    public function getPathTo($pathName)
    {
        $result = '';
        if ($path = $this->configManager->get("paths.$pathName")) {
            $result = ROOT_PATH . $path;
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function reRegisterElement($oldId, $newId)
    {
        if (!is_numeric($oldId)) {
            foreach ($this->formData as $key => &$data) {
                if (!is_numeric($key)) {
                    $this->formData[$newId] = $this->formData[$key];
                    unset($this->formData[$key]);
                }
            }
        } elseif (isset($this->formData[$oldId])) {
            $this->formData[$newId] = $this->formData[$oldId];
            unset($this->formData[$oldId]);
        }
    }

    public function getContainer(): Container
    {
        if (!isset($this->container)) {
            $pathsManager = $this->getPathsManager();
            $paths = $pathsManager->getIncludePaths();
            $coreFolder = $pathsManager->getRelativePath('core');
            $definitions = [];
            foreach ($paths as $path) {
                $definitionsPath = $path . $coreFolder . 'di-definitions.php';
                if (is_file($definitionsPath)) {
                    $definitions[] = include($definitionsPath);
                }
            }
            $definitions = array_merge(...$definitions);
            $containerBuilder = new ContainerBuilder();
            $compilationPath = $this->getDebugMode() ? null : $pathsManager->getPath('diCache');
            if ($compilationPath && !is_dir($compilationPath)) {
                mkdir($compilationPath, $this->configManager->get('paths.defaultCachePermissions'), true);
            }
            if ($compilationPath && is_dir($compilationPath)) {
               $containerBuilder->enableCompilation($compilationPath);
            }
            $containerBuilder->addDefinitions($definitions);
            $container = $containerBuilder->build();
            $this->container = $container;
        }
        return $this->container;
    }
}