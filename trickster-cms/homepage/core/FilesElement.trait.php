<?php

use App\Paths\PathsManager;

/**
 * Class FilesElementTrait
 *
 */
trait FilesElementTrait
{
    //todo: restore this after PHP7.3 fix on Zone
//    use CacheOperatingElement;

    /**
     * @var fileElement[][]
     */
    protected $filesList;

    protected function initialize()
    {
        foreach ($this->getFileSelectorPropertyNames() as $name) {
            $this->moduleFields[$name] = 'files';
        }
        return true;
    }

    public function getFileSelectorPropertyNames()
    {
        return ['connectedFile'];
    }

    /**
     * @param string $propertyName
     * @return fileElement[]
     */
    public function getFilesList($propertyName = 'connectedFile'): array
    {
        if (!isset($this->filesList[$propertyName])) {
            $cache = $this->getElementsListCache($propertyName, 1200);
            if (($this->filesList[$propertyName] = $cache->load()) === null) {
                $this->filesList[$propertyName] = [];
                /**
                 * @var structureManager $structureManager
                 */
                $structureManager = $this->getService('structureManager');
                $this->filesList[$propertyName] = $structureManager->getElementsChildren(
                    $this->getFilesParentElementId(),
                    null,
                    $this->getConnectedFileType($propertyName)
                );
                $cache->save($this->filesList[$propertyName]);
            }

        }

        return $this->filesList[$propertyName] ?? [];
    }

    /**
     * @param string $propertyName
     * @param string $appName
     * @return fileElement[]
     */
    public function getFilesUrlList($propertyName = 'connectedFile', $appName = 'file')
    {
        $urlList = [];
        foreach ($this->getFilesList($propertyName) as $fileElement) {
            $urlList[] = $fileElement->getDownloadUrl('download', $appName);
        }
        return $urlList;
    }

    /**
     * @param fileElement $file
     * @param string $propertyName
     */
    public function appendFileToList(fileElement $file, $propertyName = 'connectedFile')
    {
        if (!isset($this->filesList[$propertyName])) {
            $this->filesList[$propertyName] = [];
        }
        $this->filesList[$propertyName][] = $file;
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public function getConnectedFileType($propertyName = 'connectedFile')
    {
        return $propertyName;
    }

    /**
     * @param string $propertyName
     * @return string
     */
    public function getUploadedFilesPath($propertyName = 'connectedFile')
    {
        /**
         * @var PathsManager $pathsManager
         */
        $pathsManager = $this->getService(PathsManager::class);
        return $pathsManager->getPath('uploads');
    }

    public function getFilesParentElementId(): int
    {
        return $this->getId();
    }

    public function getFileUploadSuccessUrl()
    {
        return $this->URL . 'id:' . $this->id . '/action:showFiles/';
    }

    public function isPrivilegesSettingRequired()
    {
        return false;
    }

    protected function getPropertyName($type = 'connectedFile')
    {
        return 'connectedFile';
    }
}