<?php

use App\Paths\PathsManager;

/**
 * Class DeploymentManager
 *
 * Controlling class for operating deployment packages
 *
 * TODO: deployment logging
 * TODO: namespaces???
 */
class DeploymentManager extends errorLogger implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $config;
    protected $directory = '';
    protected $incomingDirectory = '';
    protected $lastAttemptedInstall;

    public function __construct()
    {
        $configManager = $this->getService(ConfigManager::class);
        $this->config = $configManager->getConfig('deployment');
        if ($this->config->isEmpty() && defined('CONFIGURATION_PATH') && is_file(CONFIGURATION_PATH . 'configuration_deployment.php')) {
            // deprecated since 2016.03
            $deprecatedConfig = $configManager->getConfigFromPath(CONFIGURATION_PATH . 'configuration_deployment.php');
            $this->config->setData($deprecatedConfig->getData());
        }
    }

    public function installDeployment(Deployment $deployment)
    {
        $this->lastAttemptedInstall = $deployment;
        if ($this->isDeploymentInstallable($deployment)) {
            foreach ($deployment->getProcedures() as $procedure) {
                $procedure->validate();
            }
            foreach ($deployment->getProcedures() as $procedure) {
                $procedure->run();
            }
            $this->config->set('currentVersion', $deployment->getVersion());
            $deployments = $this->config->get('deployments');
            $deployments[] = [
                'type' => $deployment->getType(),
                'version' => $deployment->getVersion(),
                'description' => $deployment->getDescription(),
            ];
            $this->config->set('deployments', $deployments);
            $this->config->save();
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
            if (function_exists('opcache_reset')) {
                opcache_reset();
            }
        } elseif ($this->isDeploymentInstalled($deployment)) {
            throw new Exception('Deployment already installed!');
        } else {
            throw new Exception('requirements not met! Missing versions: '
                . $this->compileVersionsNamesList($this->getMissingVersions($deployment)));
        }
    }

    public function isDeploymentInstallable(Deployment $deployment)
    {
        return !$this->isDeploymentInstalled($deployment) && !$this->getMissingVersions($deployment);
    }

    public function getMissingVersions(Deployment $deployment)
    {
        $result = [];
        $requiredVersions = $deployment->getRequiredVersions();
        if ($requiredVersions) {
            foreach ($requiredVersions as &$versionInfo) {
                if (!$this->isVersionInstalled($versionInfo['type'], $versionInfo['version'])) {
                    $result[] = $versionInfo;
                }
            }
        }
        return $result;
    }

    public function isDeploymentInstalled(Deployment $deployment)
    {
        return $this->isVersionInstalled($deployment->getType(), $deployment->getVersion());
    }

    public function isVersionInstalled($type, $version)
    {
        if ($installedDeployments = $this->config->get('deployments')) {
            foreach ($installedDeployments as &$deploymentInfo) {
                if ($deploymentInfo['version'] == $version && $deploymentInfo['type'] == $type) {
                    return true;
                }
            }
        }
        return false;
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setDirectory($directory)
    {
        $this->directory = $directory;
    }

    public function setIncomingDirectory($directory)
    {
        $this->incomingDirectory = $directory;
    }

    public function getLocalDeployments()
    {
        $result = [];
        $this->extractIncomingDeployments();
        foreach (new DirectoryIterator($this->directory) as $fileInfo) {
            if (!$fileInfo->isFile()) {
                continue;
            }
            if (strtolower($fileInfo->getExtension()) == 'zip') {
                $result[] = $this->readDeploymentArchive($fileInfo->getRealPath());
            }
        }
        return $result;
    }

    public function getDeploymentData($type, $version)
    {
        if ($deployments = $this->getLocalDeployments()) {
            foreach ($deployments as &$deployment) {
                if ($deployment->getType() == $type && $deployment->getVersion() == $version) {
                    return $deployment;
                }
            }
        }
        return false;
    }

    public function readDeploymentArchive($archivePath)
    {
        $result = null;
        if (is_file($archivePath)) {
            $result = new Deployment();
            $this->instantiateContext($result);
            $result->setArchivePath($archivePath);
        } else {
            throw new Exception('Deployment archive not found!');
        }
        return $result;
    }

    /**
     * @deprecated - do we still need this? may be something would be useful in auto-update, review and decide
     */
    public function extractIncomingDeployments()
    {
        //        if (!is_dir($this->incomingDirectory)) {
        //            return;
        //        }
        //        if (!is_dir($this->directory)) {
        //            mkdir($this->directory);
        //        }
        //        foreach (new DirectoryIterator($this->incomingDirectory) as $fileInfo) {
        //            if ($fileInfo->isDot() || $fileInfo->isDir()) {
        //                continue;
        //            }
        //            if (!is_file($this->directory . $fileInfo->getBasename('.zip'))) {
        //                $zip = new ZipArchive();
        //                if ($zip->open($this->incomingDirectory . $fileInfo->getBasename()) === true) {
        //                    $zip->extractTo($this->directory);
        //                    $zip->close();
        //                    unlink($fileInfo->getRealPath());
        //                } else {
        //                    $this->logError('Failed to extract deployment ' . $fileInfo->getBasename());
        //                }
        //            }
        //        }
    }

    public function clearPendingDeployments()
    {
        $this->config->set('pending', []);
        $this->config->save();
    }

    public function addPendingDeployment($type, $version, $path)
    {
        $deployments = (array)$this->config->get('pending');
        $deployments[] = [
            'type' => $type,
            'version' => $version,
            'path' => $path,
        ];
        $this->config->set('pending', $deployments);
        $this->config->save();
    }

    public function hasPendingDeployments()
    {
        return !!$this->config->get('pending');
    }

    public function installPendingDeployment()
    {
        $pending = (array)$this->config->get('pending');
        if (!$pending) {
            return;
        }
        $success = false;
        foreach ($pending as $key => $deploymentInfo) {
            $deployment = $this->readDeploymentArchive($deploymentInfo['path']);
            if (!$this->isDeploymentInstallable($deployment)) {
                continue;
            }
            $this->installDeployment($deployment);
            unset($pending[$key]);
            $success = true;
            break;
        }
        if (!$success) {
            throw new Exception('None of the pending deployments were installable');
        }
        $this->config->set('pending', $pending);
        $this->config->save();
    }

    public function installPendingDeployments()
    {
        $controller = $this->getService(controller::class);
        while ($pendingDeployments = $this->config->get('pending')) {
            ini_set('default_socket_timeout', 256);
            $context = null;
            if (strpos($controller->baseURL, '.dev.artweb.ee') !== false) {
                $auth = base64_encode('client:demo');
                $header = ["Authorization: Basic $auth"];
                $context = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'header' => $header,
                    ],
                ]);
            }
            $result = file_get_contents($controller->baseURL . 'deploy/installpending/', null, $context);
            $error = '';
            if ($result === 'success') {
                $this->config->refresh();
            } elseif ($result === false) {
                $requestError = error_get_last();
                $error = 'HTTP request to self failed. Error: ' . $requestError['message'];
            } else {
                $error = 'Error during deployment install: ' . $result;
            }
            if ($error !== '') {
                throw new Exception($error);
            }
        }
    }

    public function compileVersionsNamesList($versions)
    {
        $strings = [];
        foreach ($versions as &$versionInfo) {
            $strings[] = $versionInfo['type'] . ' (' . $versionInfo['version'] . ')';
        }
        return implode(', ', $strings);
    }

    /**
     * @return Deployment
     */
    public function getLastAttemptedInstall()
    {
        return $this->lastAttemptedInstall;
    }
}

class DeploymentConfig
{
    protected $modified = false;
    protected $currentVersion = '';
    protected $deployments = [];
    protected $file = '';
    protected $enabled = false;

    public function __construct($file)
    {
        $this->file = $file;
        $this->readFile();
    }

    public function __destruct()
    {
        if ($this->modified) {
            $this->writeFile();
        }
    }

    protected function readFile()
    {
        if (file_exists($this->file)) {
            $data = include $this->file;
            if ($data) {
                $this->currentVersion = $data['currentVersion'];
                $this->deployments = $data['deployments'];
                $this->enabled = $data['enabled'];
            }
        }
    }

    protected function writeFile()
    {
        $data = [
            'currentVersion' => $this->currentVersion,
            'deployments' => $this->deployments,
            'enabled' => $this->enabled,
        ];
        file_put_contents($this->file, '<?php

use App\Paths\PathsManager; return ' . var_export($data, true) . ';');
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($this->file);
        }
    }

    public function getEnabled()
    {
        return $this->enabled;
    }

    public function setEnabled($enabled = true)
    {
        $this->modified = true;
        $this->enabled = $enabled;
    }

    public function getCurrentVersion()
    {
        return $this->currentVersion;
    }

    public function setCurrentVersion($newVersion)
    {
        $this->modified = true;
        $this->currentVersion = $newVersion;
    }

    public function addDeployment($type, $deploymentInfo)
    {
        $this->modified = true;
        $this->deployments[] = ['type' => $type, 'version' => $deploymentInfo];
    }

    public function getDeployments()
    {
        return $this->deployments;
    }
}

/**
 * Class Deployment
 *
 * Class to parse a single deployment package XML into separated procedures.
 */
class Deployment implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    /**
     *  Default file name of deployment XML descriptor
     */
    const FILE_NAME = 'deployment.xml';
    /**
     * @var string - a path to package's ZIP archive in file system
     */
    protected $archivePath;
    /**
     * @var string - Short description of package, parsed from XML
     */
    protected $description;
    /**
     * @var string - file name of deployment XML descriptor
     */
    protected $fileName = '';
    /**
     * @var DeploymentProcedure[] - List of deployment procedure object for this deployment
     */
    protected $procedures = [];
    protected $version = '';
    protected $requiredVersions = [];
    protected $type = '';
    protected $xmlParsed = false;

    /**
     *
     */
    public function __construct()
    {
        $this->fileName = self::FILE_NAME;
    }

    /**
     * @return \DeploymentProcedure[]
     */
    public function getProcedures()
    {
        if (!$this->xmlParsed) {
            $this->parseFile();
        }
        return $this->procedures;
    }

    public function getDescription()
    {
        if (!$this->xmlParsed) {
            $this->parseFile();
        }
        return $this->description;
    }

    public function getVersion()
    {
        if (!$this->xmlParsed) {
            $this->parseFile();
        }
        return $this->version;
    }

    public function getType()
    {
        if (!$this->xmlParsed) {
            $this->parseFile();
        }
        return $this->type;
    }

    public function getRequiredVersions()
    {
        if (!$this->xmlParsed) {
            $this->parseFile();
        }
        return $this->requiredVersions;
    }

    public function getName()
    {
        $result = '';
        foreach (explode('/', $this->archivePath) as $part) {
            if ($part) {
                $result = $part;
            }
        }
        return $result;
    }

    /**
     * @param string $archivePath
     */
    public function setArchivePath($archivePath)
    {
        $this->archivePath = $archivePath;
    }

    public function getArchivePath()
    {
        return $this->archivePath;
    }

    protected function parseFile()
    {
        // TODO: some kind of xml validation?
        $result = false;
        $xmlFileContents = '';
        if (is_file($this->archivePath)) {
            $zipArchive = new ZipArchive();
            if ($zipArchive->open($this->archivePath)) {
                $xmlFileContents = $zipArchive->getFromName($this->fileName);
            }
            if ($xmlFileContents === false) {
                throw new Exception($zipArchive->getStatusString());
            }
            $xml = simplexml_load_string($xmlFileContents);
            $this->description = $this->processXmlNodeValue($xml->description);
            if (isset($xml->version)) {
                $this->version = $this->processXmlNodeValue($xml->version);
            }
            if (isset($xml->requiredVersions)) {
                foreach ($xml->requiredVersions->children() as $versionNode) {
                    $this->requiredVersions[] = [
                        'type' => $this->processXmlNodeValue($versionNode->type),
                        'version' => $this->processXmlNodeValue($versionNode->version),
                    ];
                }
            }
            if (isset($xml->type)) {
                $this->type = $this->processXmlNodeValue($xml->type);
            }
            if (isset($xml->procedures)) {
                foreach ($xml->procedures->children() as $procedureNode) {
                    $procedureClass = $procedureNode->getName() . 'DeploymentProcedure';
                    if (class_exists($procedureClass)) {
                        $newProcedure = new $procedureClass($procedureNode);
                        $newProcedure->setDeployment($this);
                        $this->instantiateContext($newProcedure);
                        $this->procedures[] = $newProcedure;
                    } else {
                        throw new Exception('Deployment procedure class "' . $procedureClass . '" is missing. ' . $this->type . ' ' . $this->version);
                    }
                }
            }
            $result = true;
        }
        $this->xmlParsed = true;
        return $result;
    }

    /**
     * @param $input
     * @return string
     */
    public static function processXmlNodeValue($input)
    {
        return trim((string)$input);
    }
}

/**
 * Class DeploymentProcedure
 *
 * Abstract
 */
abstract class DeploymentProcedure implements DependencyInjectionContextInterface
{
    use DependencyInjectionContextTrait;
    protected $deployment;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
    }

    public function setDeployment(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }

    /**
     * @return string
     */
    public abstract function validate();

    public abstract function run();
}

/**
 * Class SqlQueryDeploymentProcedure
 */
class SqlQueryDeploymentProcedure extends DeploymentProcedure
{
    /**
     * @var string - SQL query text
     */
    protected $query = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        foreach ($xmlNode->children() as $node) {
            if ($node->getName() == 'query') {
                $this->query = Deployment::processXmlNodeValue($node);
            }
        }
    }

    public function validate()
    {
        //todo: implement
    }

    /**
     * @return array|bool|mysqli_result
     */
    public function run()
    {
        $db = $this->getService('db');
        if ($this->query) {
            return $db->select($this->query);
        }
        return false;
    }
}

/**
 * Class SqlTableColumnDeploymentProcedure
 */
class SqlTableColumnDeploymentProcedure extends DeploymentProcedure
{
    use XmlPropertyMapper;
    /**
     * @var
     */
    protected $tableName = '';
    protected $columnName = '';
    protected $columnType = '';
    protected $columnNull = false;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->parseXmlNode($xmlNode);
        if ($this->columnNull == 'false') {
            $this->columnNull = false;
        }
    }

    public function validate()
    {
        if (!$this->tableName || !$this->columnName || !$this->columnType) {
            throw new Exception('Mandatory parameter is missing');
        }
    }

    /**
     * @return array|bool|mysqli_result
     */
    public function run()
    {
        if (!$this->checkColumn()) {
            $db = $this->getService('db');

            $query = "ALTER TABLE `" . $this->tableName . "` ADD `" . $this->columnName . "` " . $this->columnType;
            if (!$this->columnNull) {
                $query .= ' NOT NULL';
            }
            $query .= ';';
            return $db->statement($query);
        }
        return true;
    }

    protected function checkColumn()
    {
        $db = $this->getService('db');
        $query = "SHOW COLUMNS FROM `" . $this->tableName . "` LIKE '" . $this->columnName . "'";

        return $db->select($query);
    }
}

class PhpScriptDeploymentProcedure extends DeploymentProcedure
{
    protected $path = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->path = Deployment::processXmlNodeValue($xmlNode->attributes()->path);
    }

    public function validate()
    {
        $zipArchive = new ZipArchive();
        if ($zipArchive->open($this->deployment->getArchivePath())) {
            if (!$this->path || $zipArchive->locateName($this->path) === false) {
                throw new Exception('Bad path');
            }
        }
    }

    /**
     * @return array|bool|mysqli_result
     */
    public function run()
    {
        $structureManager = $this->getService('structureManager');

        $zipArchive = new ZipArchive();
        if ($zipArchive->open($this->deployment->getArchivePath())) {
            $tempFile = tmpfile();
            $tempFileMeta = stream_get_meta_data($tempFile);
            file_put_contents($tempFileMeta['uri'], $zipArchive->getFromName($this->path));
            include $tempFileMeta['uri'];
            return true;
        }
        return false;
    }
}

class CopyFilesDeploymentProcedure extends DeploymentProcedure
{
    use XmlPropertyMapper;
    protected $sourceFolder = '';
    protected $rewrite = '1';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->parseXmlNode($xmlNode);
        //old short format support, which didn't contain any subnodes with options
        if (!$this->sourceFolder) {
            $this->sourceFolder = Deployment::processXmlNodeValue($xmlNode);
        }
    }

    public function validate()
    {
        $zipArchive = new ZipArchive();
        if ($zipArchive->open($this->deployment->getArchivePath())) {
            if (!$this->sourceFolder || $zipArchive->locateName($this->sourceFolder) === false) {
                throw new Exception('Bad path');
            }
        }
    }

    public function run()
    {
        $zipArchive = new ZipArchive();
        if ($zipArchive->open($this->deployment->getArchivePath())) {
            $this->extractRecursive($zipArchive, $this->sourceFolder, ROOT_PATH);
        }
    }

    protected function extractRecursive($zipArchive, $sourceSubDirectory, $targetPath)
    {
        $entries = [];
        for ($i = 0; $i < $zipArchive->numFiles; $i++) {
            $zipEntry = $zipArchive->getNameIndex($i);
            $entries[] = $zipEntry;
            // Skip files not in $source
            if (strpos($zipEntry, $sourceSubDirectory) !== 0) {
                continue;
            }
            $directory = substr($zipEntry, -1) === '/';
            // Strip source part from filename
            $targetNamePart = substr($zipEntry, strlen($sourceSubDirectory));
            if ($targetNamePart) {
                if ($directory) {
                    if (is_dir($targetPath . $targetNamePart) === false) {
                        mkdir($targetPath . $targetNamePart, 0777, true);
                    }
                } elseif ($this->rewrite != '0' || !is_file($targetPath . $targetNamePart)) {
                    $pathInfo = pathinfo($targetNamePart);
                    // Create the directories if necessary
                    if ($pathInfo['dirname'] != '.') {
                        $targetFolder = $targetPath . $pathInfo['dirname'] . '/';
                        if (!is_dir($targetFolder)) {
                            mkdir($targetFolder, 0777, true);
                        }
                    }
                    $fpr = $zipArchive->getStream($zipEntry);
                    $fpw = fopen($targetPath . $targetNamePart, 'w');
                    while ($data = fread($fpr, 1024)) {
                        fwrite($fpw, $data);
                    }
                    fclose($fpr);
                    fclose($fpw);
                }
            }
        }
    }
}

class DeleteFileDeploymentProcedure extends DeploymentProcedure
{
    protected $filesDirectoryName = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->relativeFilePath = ROOT_PATH . Deployment::processXmlNodeValue($xmlNode);
    }

    public function validate()
    {
        //        if (!is_file($this->relativeFilePath)) {
        //            throw new Exception('Bad path');
        //        }
    }

    public function run()
    {
        if (is_file($this->relativeFilePath)) {
            unlink($this->relativeFilePath);
        }
    }
}

class MoveFileDeploymentProcedure extends DeploymentProcedure
{
    protected $relativeFromPath = '';
    protected $relativeToPath = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->relativeFromPath)) {
            $this->relativeFromPath = ROOT_PATH . Deployment::processXmlNodeValue($xmlNode->relativeFromPath);
        }
        if (!empty($xmlNode->relativeToPath)) {
            $this->relativeToPath = ROOT_PATH . Deployment::processXmlNodeValue($xmlNode->relativeToPath);
        }
    }

    public function validate()
    {
        if (!$this->relativeToPath) {
            throw new Exception('MoveFileDeploymentProcedure: empty relativeToPath path');
        }
        if (!$this->relativeFromPath) {
            throw new Exception('MoveFileDeploymentProcedure: empty relativeFromPath path');
        }
    }

    public function run()
    {
        if (is_file($this->relativeFromPath)) {
            $dir = dirname($this->relativeToPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            if (is_dir($dir)) {
                rename($this->relativeFromPath, $this->relativeToPath);
            }
        }
    }
}

class DeleteDirDeploymentProcedure extends DeploymentProcedure
{
    protected $directory = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->directory = ROOT_PATH . Deployment::processXmlNodeValue($xmlNode);
    }

    public function validate()
    {
    }

    public function run()
    {
        if (is_dir($this->directory) === false) {
            return;
        }
        self::removeDir($this->directory);
    }

    protected static function removeDir($dir)
    {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object === '.' || $object === '..') {
                continue;
            }
            $path = $dir . '/' . $object;
            if (is_dir($path) === true) {
                self::removeDir($path);
                continue;
            }
            unlink($path);
        }
        rmdir($dir);
    }
}

class EditFileDeploymentProcedure extends DeploymentProcedure
{
    use XmlPropertyMapper;
    protected $file = '';
    protected $path = '';
    protected $search = '';
    protected $replace = '';
    protected $append = false;
    protected $ifNotContains = false;

    /**
     * @param SimpleXMLElement $xmlNode
     * @throws Exception
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->parseXmlNode($xmlNode);
    }

    public function validate()
    {
        if (!$this->append && !$this->search) {
            throw new Exception('Missing arguments: append or search');
        }

        if (!$this->file && (!$this->path || !is_dir(ROOT_PATH . $this->path))) {
            throw new Exception('Path does not exist!');
        } elseif (!$this->path && (!$this->file || !is_file(ROOT_PATH . $this->file))) {
            throw new Exception('File does not exist! - ' . $this->file);
        }
    }

    public function run()
    {
        if ($this->file) {
            if ($this->append) {
                $this->appendFileContents($this->file);
            } else {
                $this->replaceFileContents($this->file);
            }
        } elseif ($this->path) {
            $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->path));
            foreach ($objects as $object) {
                if (is_file($object->getPathname())) {
                    if ($this->append) {
                        $this->appendFileContents($object->getPathname());
                    } else {
                        $this->replaceFileContents($object->getPathname());
                    }
                }
            }
        }
    }

    protected function replaceFileContents($file)
    {
        $fileContents = file_get_contents(ROOT_PATH . $file);

        if ($this->ifNotContains === false || $this->ifNotContains === '' || strpos($fileContents, $this->ifNotContains) === false) {
            if (strpos($fileContents, $this->search) !== false) {
                file_put_contents(ROOT_PATH . $file, str_replace($this->search, $this->replace, $fileContents));
            }
        }
    }

    protected function appendFileContents($file)
    {
        if ($fileContents = file_get_contents(ROOT_PATH . $file)) {
            if ($this->ifNotContains === false || $this->ifNotContains === '' || strpos($fileContents, $this->ifNotContains) === false) {
                if (($appendPosition = strrpos($fileContents, '?>')) !== false) {
                    $fileContents = substr_replace($fileContents, $this->append, $appendPosition, 0);
                } else {
                    $fileContents .= $this->append;
                }
                file_put_contents(ROOT_PATH . $file, $fileContents);
            }
        }
    }
}

/**
 * Class AddElementDeploymentProcedure
 */
class AddElementDeploymentProcedure extends DeploymentProcedure
{
    use ImageWriterTrait;
    use ImageDetectionTrait;
    /**
     * @var string
     */
    protected $parentId;
    protected $parentMarker = '';
    protected $parentPath = '';
    /**
     * @var DeploymentElementInfo
     */
    protected $elementInfo;

    /**
     * @param SimpleXMLElement $xmlNode
     * @throws Exception
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->parentMarker)) {
            $this->parentMarker = Deployment::processXmlNodeValue($xmlNode->parentMarker);
        } elseif (isset($xmlNode->parentId)) {
            $this->parentId = (int)Deployment::processXmlNodeValue($xmlNode->parentId);
        } elseif (isset($xmlNode->parentPath)) {
            $this->parentPath = Deployment::processXmlNodeValue($xmlNode->parentPath);
        } else {
            throw new Exception('Bad XML, no parent identifier');
        }
        $this->elementInfo = new DeploymentElementInfo($xmlNode);
    }

    /**
     * @param DeploymentElementInfo $elementInfo
     * @param $destinationElementId
     */
    protected function importElement(DeploymentElementInfo $elementInfo, $destinationElementId)
    {
        $structureManager = $this->getService('structureManager');
        if ($destinationElementId == 0 || $structureManager->getElementById($destinationElementId)) {
            $element = $this->getSiblingWithSameName($elementInfo, $destinationElementId);
            if (!$element) {
                // TODO: we cannot hardcode action name here. should we store it in xml? or should we find a default action out?
                $element = $structureManager->createElement($elementInfo->type, 'show', $destinationElementId);
            }
            if ($element) {
                $element->prepareActualData();
                $languagesManager = $this->getService(LanguagesManager::class);
                $languages = $languagesManager->getLanguagesMap($element->languagesParentElementMarker);
                $fieldsInfoToImport = [];
                foreach ($elementInfo->fieldsData as $key => $value) {
                    if (is_array($value)) {
                        $languageCode = $key;
                        if (isset($languages[$languageCode])) {
                            $languageId = $languages[$languageCode]->id;
                            $fieldsInfoToImport[$languageId] = $value;
                        }
                    } elseif ($key == 'image') {
                        $this->writeImage($element->id, $value);

                        $element->image = $element->id;
                        $element->originalName = $value;
                    } elseif ($imageFieldData = $this->detectImageField($elementInfo, $key)) {
                        $fileName = $element->id . $imageFieldData['postfix'];
                        $this->writeImage($fileName, $value);

                        $element->{$key} = $fileName;
                        $element->{$imageFieldData['originalName']} = $value;
                    } elseif ($value instanceof idByMarkerWriter) {
                        $fieldsInfoToImport[$key] = $value->getId($structureManager);
                    } elseif ($value instanceof idByPathWriter) {
                        $fieldsInfoToImport[$key] = $value->getId($structureManager);
                    } else {
                        $fieldsInfoToImport[$key] = $value;
                    }
                }
                if ($elementInfo->marker) {
                    $fieldsInfoToImport['marker'] = $elementInfo->marker;
                    $elementInfo->fieldNames[] = 'marker';
                }
                if ($elementInfo->structureName) {
                    $element->structureName = $elementInfo->structureName;
                }
                $element->importExternalData($fieldsInfoToImport, $elementInfo->fieldNames);
                $element->persistElementData();
                if ($elementInfo->type == 'language' || $elementInfo->type == 'root') {
                    $languagesManager = $this->getService(LanguagesManager::class);
                    $languagesManager->reset();
                }
                $linksManager = $this->getService(linksManager::class);
                foreach ($elementInfo->links as $link) {
                    $path = $link['targetPath'];
                    $pathParths = array_filter(explode('/', $path), 'strlen');
                    if ($pathParths) {
                        $targetElement = $structureManager->getElementByPath($pathParths);
                        $targetId = $targetElement ? $targetElement->id : 0;
                        if ($targetId) {
                            if ($link['role'] == 'parent') {
                                $linksManager->linkElements($element->id, $targetId, $link['type']);
                            } else {
                                $linksManager->linkElements($targetId, $element->id, $link['type']);
                            }
                        }
                    }
                }

                if ($elementInfo->options) {
                    foreach ($elementInfo->options as $optionName => $optionValue) {
                        switch ($optionName) {
                            case 'removeStructureLink':
                                {
                                    $linksManager->unLinkElements($element->id, $destinationElementId);
                                }
                                break;
                        }
                    }
                }

                foreach ($elementInfo->children as $childInfo) {
                    $this->importElement($childInfo, $element->id);
                }

                if ($elementInfo->position) {
                    if ($link = $linksManager->getLink($destinationElementId, $element->id)) {
                        $link->position = $elementInfo->position;
                        $link->persist();
                    }
                }
            }
        }
    }

    protected function getSiblingWithSameName($elementInfo, $destinationElementId)
    {
        $structureManager = $this->getService('structureManager');
        $element = false;
        if ($elementInfo->structureName) {
            if ($parent = $structureManager->getElementById($destinationElementId)) {
                foreach ($parent->getChildrenList() as $parentChild) {
                    if ($parentChild->structureName == $elementInfo->structureName) {
                        $element = $parentChild;
                    }
                }
            }
        }

        return $element;
    }

    public function validate()
    {
        //todo: implement instead of making this in run
    }

    public function run()
    {
        $structureManager = $this->getService('structureManager');
        $structureManager->setPrivilegeChecking(false);
        if (!is_numeric($this->parentId) && !empty($this->parentMarker)) {
            $this->parentId = $structureManager->getElementIdByMarker($this->parentMarker);
        }
        if (!$this->parentId && $this->parentPath) {
            $pathParths = array_filter(explode('/', $this->parentPath), 'strlen');
            if ($pathParths) {
                $parentElement = $structureManager->getElementByPath($pathParths);
                if ($parentElement) {
                    $this->parentId = $parentElement->id;
                }
            }
        }
        //        if (!$this->parentId && $this->parentId !== 0) {
        //            throw new Exception('Unable to determine parent ID');
        //        }
        $this->importElement($this->elementInfo, $this->parentId);
    }
}

class idByMarkerWriter
{
    private $marker;

    public function __construct($marker)
    {
        $this->marker = $marker;
    }

    public function getId($structureManager)
    {
        return $structureManager->getElementIdByMarker($this->marker);
    }
}

class idByPathWriter
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    public function getId($structureManager)
    {
        $element = $structureManager->getElementByPath(array_filter(explode('/', $this->path), 'strlen'));
        if ($element) {
            return $element->id;
        }
        return false;
    }
}

/**
 * Class DeploymentElementInfo
 * Serves as a holder of element type, fields, children info.
 */
class DeploymentElementInfo
{
    /**
     * @var string
     */
    public $type = '';
    /**
     * @var string
     */
    public $marker = '';
    /**
     * @var string
     */
    public $position = '';
    /**
     * @var string
     */
    public $structureName = '';
    /**
     * @var array
     */
    public $fieldNames = [];
    /**
     * @var array
     */
    public $fieldsData = [];
    /**
     * @var array
     */
    public $children = [];
    /**
     * @var array
     */
    public $links = [];
    /**
     * @var array
     */
    public $options = [];
    /**
     * @var array
     */
    public $imageAttributes = [];

    /**
     * DeploymentElementInfo constructor.
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->fields)) {
            foreach ($xmlNode->fields->children() as $fieldNode) {
                $fieldName = '';
                $fieldLanguageCode = '';
                $writeElementIdByMarker = false;
                $writeElementIdByPath = false;
                foreach ($fieldNode->attributes() as $key => $value) {
                    switch ($key) {
                        case 'name':
                            $fieldName = Deployment::processXmlNodeValue($value);
                            break;
                        case 'languageCode':
                            $fieldLanguageCode = Deployment::processXmlNodeValue($value);
                            break;
                        case 'writeElementIdByMarker':
                            $writeElementIdByMarker = Deployment::processXmlNodeValue($value);
                            break;
                        case 'writeElementIdByPath':
                            $writeElementIdByMarker = Deployment::processXmlNodeValue($value);
                            break;
                    }
                }
                //search for fields that might have images
                //example: in xml: postfix="_photo" it means that filename will be 1694_photo; originalName="photoOriginalName" means that image name will be written to photoOriginalName field
                $attributes = $fieldNode->attributes();
                if (isset($attributes['postfix'])) {
                    $this->imageAttributes[$fieldName]['postfix'] = Deployment::processXmlNodeValue($attributes['postfix']);
                }
                if (isset($attributes['originalName'])) {
                    $this->imageAttributes[$fieldName]['originalName'] = Deployment::processXmlNodeValue($attributes['originalName']);
                }

                if ($fieldName) {
                    $this->fieldNames[] = $fieldName;
                    $fieldValue = Deployment::processXmlNodeValue($fieldNode);
                    if ($fieldLanguageCode !== '') {
                        if (!isset($this->fieldsData[$fieldLanguageCode])) {
                            $this->fieldsData[$fieldLanguageCode] = [];
                        }
                        $this->fieldsData[$fieldLanguageCode][$fieldName] = $fieldValue;
                    } elseif ($writeElementIdByMarker) {
                        $this->fieldsData[$fieldName] = new idByMarkerWriter($fieldValue);
                    } elseif ($writeElementIdByPath) {
                        $this->fieldsData[$fieldName] = new idByPathWriter($fieldValue);
                    } else {
                        $this->fieldsData[$fieldName] = $fieldValue;
                    }
                }
            }
            $this->fieldNames = array_unique($this->fieldNames);
        }
        if (!empty($xmlNode->type)) {
            $this->type = Deployment::processXmlNodeValue($xmlNode->type);
        }
        if (!empty($xmlNode->marker)) {
            $this->marker = Deployment::processXmlNodeValue($xmlNode->marker);
        }
        if (!empty($xmlNode->position)) {
            $this->position = Deployment::processXmlNodeValue($xmlNode->position);
        }
        if (!empty($xmlNode->structureName)) {
            $this->structureName = Deployment::processXmlNodeValue($xmlNode->structureName);
        }
        if (!empty($xmlNode->children)) {
            foreach ($xmlNode->children->children() as $childElementXml) {
                $this->children[] = new self($childElementXml);
            }
        }
        if (!empty($xmlNode->options)) {
            foreach ($xmlNode->options->children() as $optionNode) {
                $optionValue = Deployment::processXmlNodeValue($optionNode);
                $optionName = '';
                foreach ($optionNode->attributes() as $key => $value) {
                    if ($key == 'name') {
                        $optionName = Deployment::processXmlNodeValue($value);
                        break;
                    }
                }

                if ($optionName) {
                    $this->options[$optionName] = $optionValue;
                }
            }
        }
        if (!empty($xmlNode->links)) {
            foreach ($xmlNode->links->children() as $linkNode) {
                $linkInfo = [
                    'role' => 'parent',
                    'type' => 'structure',
                    'targetPath' => '',
                ];
                if (isset($linkNode['role'])) {
                    $linkInfo['role'] = (string)$linkNode['role'];
                }
                if (isset($linkNode['type'])) {
                    $linkInfo['type'] = (string)$linkNode['type'];
                }
                $linkInfo['targetPath'] = Deployment::processXmlNodeValue($linkNode);
                $this->links[] = $linkInfo;
            }
        }
    }
}

/**
 * Class ModifyElementDeploymentProcedure
 */
class ModifyElementDeploymentProcedure extends DeploymentProcedure
{
    use ImageWriterTrait;
    use ImageDetectionTrait;
    /**
     * @var array
     */
    protected $fieldNames = [];
    /**
     * @var array
     */
    protected $fieldsData = [];
    /**
     * @var int
     */
    protected $targetId = 0;
    /**
     * @var string
     */
    protected $targetMarker = '';
    protected $elementInfo;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->fields)) {
            foreach ($xmlNode->fields->children() as $fieldNode) {
                $fieldName = '';
                $fieldLanguageCode = '';
                foreach ($fieldNode->attributes() as $key => $value) {
                    switch ($key) {
                        case 'name':
                            $fieldName = Deployment::processXmlNodeValue($value);
                            break;
                        case 'languageCode':
                            $fieldLanguageCode = Deployment::processXmlNodeValue($value);
                            break;
                    }
                }
                if ($fieldName) {
                    $this->fieldNames[] = $fieldName;
                    $fieldValue = Deployment::processXmlNodeValue($fieldNode);
                    if ($fieldLanguageCode) {
                        if (!isset($this->fieldsData[$fieldLanguageCode])) {
                            $this->fieldsData[$fieldLanguageCode] = [];
                        }
                        $this->fieldsData[$fieldLanguageCode][$fieldName] = $fieldValue;
                    } else {
                        $this->fieldsData[$fieldName] = $fieldValue;
                    }
                }
            }
            $this->fieldNames = array_unique($this->fieldNames);
        }
        if (isset($xmlNode->targetId)) {
            $this->targetId = (int)Deployment::processXmlNodeValue($xmlNode->targetId);
        }
        if (!empty($xmlNode->targetMarker)) {
            $this->targetMarker = Deployment::processXmlNodeValue($xmlNode->targetMarker);
        }

        $this->elementInfo = new DeploymentElementInfo($xmlNode);
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        $structureManager = $this->getService('structureManager');
        $structureManager->setPrivilegeChecking(false);
        $targetId = $this->targetId;
        if (!$this->targetId && $this->targetMarker) {
            $targetId = $structureManager->getElementIdByMarker($this->targetMarker);
        }
        $targetElement = $structureManager->getElementById($targetId);
        if ($targetElement) {
            $targetElement->prepareActualData();
            $languagesManager = $this->getService(LanguagesManager::class);
            $languages = $languagesManager->getLanguagesMap($targetElement->languagesParentElementMarker);
            $fieldsInfoToImport = [];
            foreach ($this->fieldsData as $key => $value) {
                if (is_array($value)) {
                    $languageCode = $key;
                    if (isset($languages[$languageCode])) {
                        $languageId = $languages[$languageCode]->id;
                        $fieldsInfoToImport[$languageId] = $value;
                    }
                } elseif ($key == 'image') {
                    $this->writeImage($targetElement->id, $value);

                    $targetElement->image = $targetElement->id;
                    $targetElement->originalName = $value;
                } elseif ($imageFieldData = $this->detectImageField($this->elementInfo, $key)) {
                    $fileName = $targetElement->id . $imageFieldData['postfix'];
                    $this->writeImage($fileName, $value);

                    $targetElement->{$key} = $fileName;
                    $targetElement->{$imageFieldData['originalName']} = $value;
                } else {
                    $fieldsInfoToImport[$key] = $value;
                }
            }
            $targetElement->importExternalData($fieldsInfoToImport, $this->fieldNames);
            $targetElement->persistElementData();
        }
    }
}

/**
 * Class AddUserGroupDeploymentProcedure
 */
class AddUserGroupDeploymentProcedure extends DeploymentProcedure
{
    /**
     * @var string
     */
    protected $name = '';
    /**
     * @var string
     */
    protected $marker = '';
    /**
     * @var string
     */
    protected $description = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->name)) {
            $this->name = Deployment::processXmlNodeValue($xmlNode->name);
        }
        if (!empty($xmlNode->marker)) {
            $this->marker = Deployment::processXmlNodeValue($xmlNode->marker);
        }
        if (!empty($xmlNode->description)) {
            $this->description = Deployment::processXmlNodeValue($xmlNode->description);
        }
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if ($this->marker && $this->name) {
            $structureManager = $this->getService('structureManager');
            $structureManager->setPrivilegeChecking(false);
            $usersElementId = $structureManager->getElementIdByMarker('userGroups');

            if ($usersElementId) {
                $element = $structureManager->createElement('userGroup', 'show', $usersElementId);
                $element->prepareActualData();
                $element->groupName = $this->name;
                $element->structureName = $this->name;
                $element->marker = $this->marker;
                $element->description = $this->description;
                $element->persistElementData();
            }
        }
    }
}

/**
 * Class AddUserDeploymentProcedure
 *
 * Imports a new user, connects user to previously existing user groups
 */
class AddUserDeploymentProcedure extends DeploymentProcedure
{
    /**
     * User name
     * @var string
     */
    protected $username = '';
    /**
     * User password in plain text
     * @var string
     */
    protected $password = '';
    /**
     * List of user groups to connect user to. User
     * @var array
     */
    protected $groups = [];

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->username)) {
            $this->username = Deployment::processXmlNodeValue($xmlNode->username);
        }
        if (!empty($xmlNode->password)) {
            $this->password = Deployment::processXmlNodeValue($xmlNode->password);
        }
        if (!empty($xmlNode->groups)) {
            foreach ($xmlNode->groups->children() as $groupNode) {
                $this->groups[] = Deployment::processXmlNodeValue($groupNode);
            }
        }
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if ($this->username) {
            $structureManager = $this->getService('structureManager');
            $structureManager->setPrivilegeChecking(false);

            if ($usersElementId = $structureManager->getElementIdByMarker('users')) {
                $element = $structureManager->createElement('user', 'show', $usersElementId);
                $element->prepareActualData();
                $element->password = $this->password;
                $element->structureName = $this->username;
                $element->userName = $this->username;
                $dataChunk = $element->getDataChunk('password');
                if ($dataChunk instanceof ElementStorageValueHolderInterface) {
                    $dataChunk->setElementStorageValue($this->password);
                }
                $element->persistElementData();

                foreach ($this->groups as $groupMarker) {
                    $groupId = $structureManager->getElementIdByMarker($groupMarker);
                    if ($groupId) {
                        $collection = persistableCollection::getInstance('structure_links');
                        $linksObject = $collection->getEmptyObject();
                        $linksObject->childStructureId = $element->id;
                        $linksObject->parentStructureId = $groupId;
                        $linksObject->type = 'userRelation';
                        $linksObject->persist();
                    }
                }
            }
        }
    }
}

trait XmlPropertyMapper
{
    protected function parseXmlNode(SimpleXMLElement $xmlNode)
    {
        if ($propertiesList = get_object_vars($this)) {
            foreach ($propertiesList as $property => $value) {
                if (isset($xmlNode->$property)) {
                    $this->$property = Deployment::processXmlNodeValue($xmlNode->$property);
                }
            }
        }
    }
}

/**
 * Class AddUserPrivilegeDeploymentProcedure
 */
class AddUserPrivilegeDeploymentProcedure extends DeploymentProcedure
{
    use XmlPropertyMapper;
    protected $userGroupMarker;
    protected $targetElementMarker;
    protected $moduleGroupMarker;
    protected $moduleType;
    protected $action;
    protected $privilege;
    protected $targetElementStructureName;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->parseXmlNode($xmlNode);
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if ($this->moduleType && $this->action && $this->privilege) {
            $targetElementId = false;
            if ($this->targetElementMarker) {
                $structureManager = $this->getService('structureManager');
                $targetElementId = $structureManager->getElementIdByMarker($this->targetElementMarker);
            }
            $userGroupId = false;
            if ($this->userGroupMarker) {
                $structureManager = $this->getService('structureManager');
                $userGroupId = $structureManager->getElementIdByMarker($this->userGroupMarker);
            }
            if ($userGroupId && $targetElementId) {
                $privilegesManager = $this->getService(privilegesManager::class);
                $privilegesManager->setPrivilege($userGroupId, $targetElementId, $this->moduleType, $this->action, $this->privilege);
            }
        }
    }
}

class AddUserPrivilegesDeploymentProcedure extends DeploymentProcedure
{
    use XmlPropertyMapper;
    protected $userGroupMarker;
    protected $targetElementMarker;
    protected $moduleGroupMarker;
    protected $moduleType;
    protected $parsedActions = [];
    protected $privilege;
    protected $targetElementStructureName;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->parseXmlNode($xmlNode);
        if (!empty($xmlNode->actions)) {
            foreach ($xmlNode->actions->children() as $privilegeNode) {
                $this->parsedActions[] = Deployment::processXmlNodeValue($privilegeNode);
            }
        }
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if ($this->moduleType && $this->parsedActions && $this->privilege) {
            $targetElementId = false;
            if ($this->targetElementMarker) {
                $structureManager = $this->getService('structureManager');
                $targetElementId = $structureManager->getElementIdByMarker($this->targetElementMarker);
            } elseif ($this->targetElementStructureName) {
                $db = $this->getService('db');
                $records = $db->table('structure_elements')
                    ->select('id')
                    ->where('structureType', '=', 'user')
                    ->where('structureName', '=', $this->targetElementStructureName)
                    ->get();
                if ($records) {
                    $targetElementId = $records[0]['id'];
                }
            }
            $userGroupId = false;
            if ($this->userGroupMarker) {
                $structureManager = $this->getService('structureManager');
                $userGroupId = $structureManager->getElementIdByMarker($this->userGroupMarker);
            }
            if ($userGroupId && $targetElementId) {
                $privilegesManager = $this->getService(privilegesManager::class);
                foreach ($this->parsedActions as $action) {
                    $privilegesManager->setPrivilege($userGroupId, $targetElementId, $this->moduleType, $action, $this->privilege);
                }
            }
        }
    }
}

/**
 * Class AddTranslationDeploymentProcedure
 */
class AddTranslationDeploymentProcedure extends DeploymentProcedure
{
    protected $type = '';
    protected $name = '';
    protected $group = '';
    protected $valueType;
    protected $values = [];
    protected $updateExisting = false;

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        $this->updateExisting = !((string)$xmlNode['updateExisting'] === 'no');
        if (!empty($xmlNode->type)) {
            $this->type = Deployment::processXmlNodeValue($xmlNode->type);
        }
        if (!empty($xmlNode->code)) {
            $code = Deployment::processXmlNodeValue($xmlNode->code);
            $parts = explode('.', $code);
            if (count($parts) == 2) {
                $this->group = $parts[0];
                $this->name = $parts[1];
            }
        }
        if (!empty($xmlNode->valueType)) {
            $this->valueType = Deployment::processXmlNodeValue($xmlNode->valueType);
        }
        if (!empty($xmlNode->values)) {
            foreach ($xmlNode->values->children() as $valueNode) {
                if (!empty($valueNode->attributes()->languageCode)) {
                    if ($languageCode = Deployment::processXmlNodeValue($valueNode->attributes()->languageCode)) {
                        $this->values[$languageCode] = Deployment::processXmlNodeValue($valueNode);
                    }
                }
            }
        }
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if (!$this->name || !$this->group || !$this->values) {
            return;
        }
        $structureManager = $this->getService('structureManager');
        $structureManager->setPrivilegeChecking(false);

        $translationsMarker = $this->type == 'adminTranslation'
            ? 'adminTranslations'
            : 'public_translations';

        if ($translationsElement = $structureManager->getElementByMarker($translationsMarker)) {
            if ($this->valueType == 'text') {
                $valueField = 'valueText';
            } elseif ($this->valueType == 'textarea') {
                $valueField = 'valueTextarea';
            } else {
                $valueField = 'valueHtml';
            };
            $fieldsData = [];
            $languagesGroupName = $this->type == 'adminTranslation'
                ? 'adminLanguages'
                : $this->getService(ConfigManager::class)->get('main.rootMarkerPublic');
            $languagesManager = $this->getService(LanguagesManager::class);

            foreach ($this->values as $languageCode => &$value) {
                if ($languageObject = $languagesManager->checkLanguageCode($languageCode, $languagesGroupName)) {
                    $fieldsData[$languageObject->id][$valueField] = $value;
                }
            }
            if ($fieldsData) {
                $fieldsData['structureName'] = $this->name;
                $fieldsData['valueType'] = $this->valueType;
                $groupElement = $translationsElement->getGroupByCode($this->group, true);

                if ($groupElement) {
                    $existingTranslation = $groupElement->findTranslation($this->name);
                    $translationToUpdate = false;
                    if (!$existingTranslation) {
                        $translationToUpdate = $structureManager->createElement($this->type, null, $groupElement->id);
                    } elseif ($this->updateExisting) {
                        $translationToUpdate = $existingTranslation;
                    }
                    if ($translationToUpdate) {
                        $translationToUpdate->prepareActualData();
                        $translationToUpdate->importExternalData($fieldsData, [
                            'structureName',
                            'valueType',
                            $valueField,
                        ]);
                        $translationToUpdate->persistElementData();
                    }
                }
            }
        }
    }
}

/**
 * Class DeleteTranslationDeploymentProcedure
 */
class DeleteTranslationDeploymentProcedure extends DeploymentProcedure
{
    protected $type = '';
    protected $group = '';
    protected $name = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->type)) {
            $this->type = Deployment::processXmlNodeValue($xmlNode->type);
        }
        if (!empty($xmlNode->code)) {
            $code = Deployment::processXmlNodeValue($xmlNode->code);
            $parts = explode('.', $code);
            if (count($parts) == 2) {
                $this->group = $parts[0];
                $this->name = $parts[1];
            }
        }
    }

    public function validate()
    {
        if (!$this->name || !$this->group) {
            throw new Exception('Bad DeleteTranslation code');
        }
    }

    public function run()
    {
        $structureManager = $this->getService('structureManager');
        $structureManager->setPrivilegeChecking(false);

        $translationsMarker = $this->type == 'adminTranslation'
            ? 'adminTranslations'
            : 'public_translations';

        /**
         * @var translationsElement $translationsElement
         */
        if ($translationsElement = $structureManager->getElementByMarker($translationsMarker)) {
            $groupElement = $translationsElement->getGroupByCode($this->group, true);
            if ($groupElement) {
                $groupElement->deleteTranslation($this->name);
            }
        }
    }
}

/**
 * Class GenerateTranslationsDeploymentProcedure
 */
class GenerateTranslationsDeploymentProcedure extends DeploymentProcedure
{
    protected $type = '';

    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->type)) {
            $this->type = Deployment::processXmlNodeValue($xmlNode->type);
        }
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        if ($this->type) {
            $translationsManager = $this->getService(translationsManager::class);
            $translationsManager->generateTranslationsFile($this->type);
        }
    }
}

/**
 * Class UpdateComposerDeploymentProcedure
 */
class UpdateComposerDeploymentProcedure extends DeploymentProcedure
{
    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
    }

    public function validate()
    {
    }

    public function run()
    {
        shell_exec('composer update 2>&1');
    }
}

/**
 * Class ClearLiveUserSessionsDeploymentProcedure
 */
class ClearLiveUserSessionsDeploymentProcedure extends DeploymentProcedure
{
    /**
     * @param SimpleXMLElement $xmlNode
     */
    public function __construct(SimpleXMLElement $xmlNode)
    {
    }

    public function validate()
    {
        //todo: implement
    }

    public function run()
    {
        $path = $this->getService(PathsManager::class)->getPath('sessionsCache');
        if (is_dir($path) === false) {
            return;
        }
        foreach (new DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }
    }
}

class LinksDeploymentProcedure extends DeploymentProcedure
{
    private $links;

    public function __construct(SimpleXMLElement $xmlNode)
    {
        if (!empty($xmlNode->children())) {
            foreach ($xmlNode->children() as $link) {
                $linkData = [
                    'type' => Deployment::processXmlNodeValue($link->type),
                ];
                if ($link->parentMarker) {
                    $linkData['parentMarker'] = Deployment::processXmlNodeValue($link->parentMarker);
                } else {
                    $linkData['parentPath'] = Deployment::processXmlNodeValue($link->parentPath);
                }
                if ($link->childMarker) {
                    $linkData['childMarker'] = Deployment::processXmlNodeValue($link->childMarker);
                } else {
                    $linkData['childPath'] = Deployment::processXmlNodeValue($link->childPath);
                }
                $this->links[] = $linkData;
            }
        }
    }

    public function validate()
    {
    }

    public function run()
    {
        $linksManager = $this->getService(linksManager::class);
        $structureManager = $this->getService('structureManager');
        foreach ($this->links as $link) {
            if (isset($link['parentMarker'])) {
                $parentId = $structureManager->getElementIdByMarker($link['parentMarker']);
            } elseif ($parent = $structureManager->getElementByPath(array_filter(explode('/', $link['parentPath']), 'strlen'))) {
                $parentId = $parent->id;
            }
            if ($parentId) {
                if (isset($link['childMarker'])) {
                    $childId = $structureManager->getElementIdByMarker($link['childMarker']);
                } elseif ($child = $structureManager->getElementByPath(array_filter(explode('/', $link['childPath']), 'strlen'))) {
                    $childId = $child->id;
                }

                if ($childId) {
                    $linksManager->linkElements($parentId, $childId, $link['type']);
                }
            }
        }
    }
}

trait ImageWriterTrait
{
    private function writeImage($imageName, $imageOriginalName)
    {
        $zipArchive = new ZipArchive();
        $uploadsPath = $this->getService(PathsManager::class)->getPath('uploads');
        if ($zipArchive->open($this->deployment->getArchivePath())) {
            for ($i = 0; $i < $zipArchive->numFiles; $i++) {
                $zipEntry = $zipArchive->getNameIndex($i);

                if (strpos($zipEntry, 'images/') !== 0) {
                    continue;
                }

                $targetNamePart = substr($zipEntry, strlen('images/'));
                if ($targetNamePart && ($targetNamePart == $imageOriginalName)) {
                    $fpr = $zipArchive->getStream($zipEntry);
                    $fpw = fopen($uploadsPath . $imageName, 'w');
                    while ($data = fread($fpr, 1024)) {
                        fwrite($fpw, $data);
                    }
                    fclose($fpr);
                    fclose($fpw);
                }
            }
        }
    }
}

trait ImageDetectionTrait
{
    private function detectImageField(DeploymentElementInfo $elementInfo, $fieldName)
    {
        if (isset($elementInfo->imageAttributes[$fieldName]['postfix']) && isset($elementInfo->imageAttributes[$fieldName]['originalName'])) {
            return [
                'postfix' => $elementInfo->imageAttributes[$fieldName]['postfix'],
                'originalName' => $elementInfo->imageAttributes[$fieldName]['originalName'],
            ];
        }
        return false;
    }
}
