<?php

use App\Paths\PathsManager;

class cssUniterRendererPlugin extends rendererPlugin
{
    use ResourceUniterTrait;
    protected $useDataUri = false;
    protected $resources = [];
    protected $cachePath = '';
    /**
     * @var lessc
     */
    protected $lessCompiler;
    protected $preferredOrder = ['text/css'];
    protected $variables = [];

    public function init()
    {
        $pathsManager = $this->getService(PathsManager::class);
        $this->cachePath = $pathsManager->getPath('cssCache');
        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->maxAge = 365 * 60 * 60 * 24;
        $this->httpResponse->setCacheControl('public');
        $this->preferredEncodings = [
            'gzip',
            'deflate',
            'identity',
        ];
        $this->lessCompiler = new lessc;
    }

    public function fetch()
    {
        $this->renderContent();
        return $this->contentText;
    }

    protected function renderContent()
    {
        if ($this->cacheNeedsUpdating()) {
            $allFilesContent = "";
            $this->lessCompiler->setVariables($this->variables);
            // JOIN FILES
            foreach ($this->resources as &$resource) {
                // todo: move functionality to application, without resource array
                $file = $resource['filePath'] . $resource['fileName'];
                //merge files adding newline to avoid problems with commented lines
                $allFilesContent .= file_get_contents($file) . "\n";
            }

            // COMPILE WITH LESS
            // todo: move functionality to application, outside of CssUniter class
            $this->lessCompiler->registerFunction("getImageUrl", function ($arg) {
                list($type, $delimiter, $values) = $arg;
                /**
                 * @var DesignThemesManager $designThemesManager
                 */
                $designThemesManager = $this->getService(DesignThemesManager::class);
                if ($theme = $designThemesManager->getCurrentTheme()) {
                    return [$type, $delimiter, [$theme->getImageUrl(reset($values))]];
                } else {
                    return "";
                }
            });
            $this->lessCompiler->registerFunction("getFontUrl", function ($arg) {
                list($type, $delimiter, $values) = $arg;
                /**
                 * @var DesignThemesManager $designThemesManager
                 */
                $designThemesManager = $this->getService(DesignThemesManager::class);
                if ($theme = $designThemesManager->getCurrentTheme()) {
                    return [$type, $delimiter, [$theme->getFontUrl(reset($values))]];
                } else {
                    return "";
                }
            });

            // SVG parameters
            $this->lessCompiler->registerFunction('encodeSvgContent', function ($arg) {
                list($type, $delimiter, $values) = $arg;
                list($arg1, $arg2, $arg3, $arg4) = $values;
                return self::getSVGContent(end($arg1), end($arg2), end($arg3), end($arg4));
            });

            // trimColorHash for colors css keys
            $this->lessCompiler->registerFunction('trimColorHash', function ($arg) {
                list($type, $color) = $arg;

                if ($type == 'raw_color') {
                    return ltrim($color, "#");
                } else {
                    $this->logError("Wrong arguments and type:  type is $type, argument is $color");
                }
                return '';
            });

            // min max are Less features not supported by this compiler
            $this->lessCompiler->registerFunction('min', function ($arg) {
                list($type, $delimiter, $values) = $arg;
                return min($values);
            });
            $this->lessCompiler->registerFunction('max', function ($arg) {
                list($type, $delimiter, $values) = $arg;
                return max($values);
            });
            try {
                $allFilesContent = $this->lessCompiler->compile($allFilesContent);
            } catch (Exception $exception) {
                $this->logError($exception->getMessage());
            }

            // MINIFY
            $compressor = new \tubalmartin\CssMin\Minifier();
            $compressor->removeImportantComments();
            $compressor->setLineBreakPosition(1000);

            // Compress the CSS code!
            $allFilesContent = $compressor->run($allFilesContent);

            // IMG to BASE64 converter
            $expression = '/url\([\'"](([- \w.\/:]+)\.(jpg|jpeg|png|gif|svg|woff2))[\'"]\)/i';

            $parts = [];
            preg_match_all($expression, $allFilesContent, $parts);
            $files = isset($parts[1]) ? array_unique($parts[1]) : [];
            $baseURL = controller::getInstance()->baseURL;
            foreach ($files as $file) {
                $filePath = stripos($file, $baseURL) !== false
                    ? str_ireplace($baseURL, ROOT_PATH, $file)
                    : ROOT_PATH . $file;
                if (!is_file($filePath)) {
                    $this->logError('CSS image missing:' . $filePath);
                    continue;
                }
                $filesize = filesize($filePath);
                if (!$filesize || $filesize > 1000 * 1000) {
                    // skip files over 1MB for sanity
                    continue;
                }
                $parts = explode('.', $file);
                $extension = strtoupper(array_pop($parts));
                if ($extension === 'SVG') {
                    $mime = 'image/svg+xml';
                } elseif ($extension === 'WOFF2') {
                    $mime = 'font/woff2;charset=utf-8';
                } else {
                    $sizeInfo = getimagesize($filePath);
                    $mime = $sizeInfo['mime'];
                }
                if (empty($mime)) {
                    $this->logError('Could not figure out MIME:' . $filePath);
                    continue;
                }
                $fileContent = file_get_contents($filePath);
                if ($extension === 'SVG') {
                    $uri = 'data:' . $mime . ',' . self::encodeSvg($fileContent);
                } else {
                    $uri = 'data:' . $mime . ';base64,' . base64_encode($fileContent);
                }
                $allFilesContent = str_replace($file, $uri, $allFilesContent);
            }

            file_put_contents($this->getCacheFilePath(), $allFilesContent);

            $this->contentText = $allFilesContent;
        } else {
            $this->contentText = file_get_contents($this->getCacheFilePath());
        }
    }

    protected function cacheNeedsUpdating()
    {
        if ($this->cacheNeedsUpdating !== null) {
            return $this->cacheNeedsUpdating;
        }
        $path = $this->getCacheFilePath();
        if (!is_file($path) || !$this->resources) {
            $this->cacheNeedsUpdating = true;
            return true;
        }
        foreach ($this->resources as &$resource) {
            $resourcePath = $resource['filePath'] . $resource['fileName'];
            if (is_file($resourcePath) && $this->getCacheFileLastModTime() < filemtime($resourcePath)) {
                $this->cacheNeedsUpdating = true;
                return true;
            }
        }
        $this->cacheNeedsUpdating = false;
        return false;
    }

    protected static function encodeSvg($input)
    {
        // https://codepen.io/tigt/post/optimizing-svgs-in-data-uris
        return str_replace([
            '%20',
            '%2F',
            '%3D',
            '%3A',
        ], [
            ' ',
            '/',
            '=',
            ':',
        ], rawurlencode($input));
    }

    /**
     * @param array $variables
     */
    public function setVariables(array $variables)
    {
        $this->variables = $variables;
    }

    protected function generateCacheFileName()
    {
        $fileString = '';
        if (count($this->resources)) {
            foreach ($this->resources as &$resource) {
                $file = $resource['filePath'] . $resource['fileName'];
                $fileString .= $file;
                $fileString .= filesize($file);
                $fileString .= filemtime($file);
            }
        }
        if ($this->useDataUri) {
            $fileString .= '_datauri';
        }
        $this->cacheFileName = md5($fileString);
    }

    protected function getSVGContent($svgFile, $fill = false, $stroke = false, $subfolder = false)
    {
        $svgFileContent = '';
        /**
         * @var DesignThemesManager $designThemesManager
         */

        $designThemesManager = $this->getService(DesignThemesManager::class);
        if ($subfolder != "false") {
            $svgFile = $subfolder . "/" . $svgFile;
        }
        if ($filePath = $designThemesManager->getCurrentTheme()->getImagePath($svgFile . '.svg',)) {

            if (!is_file($filePath)) {
                $this->logError('CSS image missing:' . $filePath);
            }

            if ($svgContent = file_get_contents($filePath)) {
                if ($fill != "false") {
                    $svgContent = str_replace('<svg', '<svg fill="' . $fill . '"', $svgContent);
                }
                if ($stroke != "false") {
                    $svgContent = str_replace('<svg', '<svg stroke="' . $stroke . '"', $svgContent);
                }

                $svgFileContent = 'data:image/svg+xml,' . self::encodeSvg($svgContent);
            }
        }
        return $svgFileContent;
    }
}
