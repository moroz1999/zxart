<?php


trait DataResponseConverterFactory
{
    /**
     * @param string $type
     * @return DataResponseConverter|bool
     */
    protected function getConverter($type)
    {
        $converter = false;

        $className = $type . 'DataResponseConverter';
        $pathsManager = controller::getInstance()->getPathsManager();
        $fileDirectory = $pathsManager->getRelativePath('DataResponseConverters');
        if ($filePath = $pathsManager->getIncludeFilePath($fileDirectory . $type . '.class.php')) {
            include_once($filePath);
            $converter = new $className();
        }
        return $converter;
    }

}