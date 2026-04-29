<?php

use App\Paths\PathsManager;

/**
 * Class smartyRendererPlugin
 *
 * @property Smarty $renderingEngine
 */
class smartyRendererPlugin extends rendererPlugin
{
    public $template = null;
    protected $contentText = null;
    protected $contentRead = false;
    protected $contentDisposition = 'inline';
    /**
     * @var translationsManager
     */
    protected $translationsManager;
    /**
     * @var structureManager
     */
    protected $structureManager;

    public function init()
    {
        $pathsManager = $this->getService(PathsManager::class);

        $this->requestHeadersManager = $this->getService(requestHeadersManager::class);
        $this->httpResponse = CmsHttpResponse::getInstance();

        $this->httpResponse->setCharset('UTF-8');

        $this->renderingEngine = new Smarty();
        $this->renderingEngine->error_reporting = E_ALL;
        $this->translationsManager = $this->getService(translationsManager::class);

        $this->renderingEngine->registerPlugin('function', 'translations', [
            $this,
            'getTranslation',
        ]);
        $this->renderingEngine->registerPlugin('function', 'logMessage', [
            $this,
            'smartyLogMessage',
        ]);
        $this->renderingEngine->registerPlugin('function', 'element', [
            $this,
            'getElement',
        ]);

        $this->renderingEngine->registerPlugin('block', 'stripdomspaces', [
            self::class,
            'stripDomSpaces',
        ]);
        $this->renderingEngine->registerFilter('pre', [
            $this,
            'filterTemplateSpaces',
        ]);
        $this->renderingEngine->registerPlugin('modifier', 'is_null', 'is_null');
        $this->renderingEngine->registerPlugin('modifier', 'urldecode', 'urldecode');
        $this->renderingEngine->registerPlugin('modifier', 'strtolower', 'strtolower');
        $this->renderingEngine->registerPlugin('modifier', 'method_exists', 'method_exists');
        $this->renderingEngine->registerPlugin('modifier', 'is_numeric', 'is_numeric');
        $path = $pathsManager->getPath('trickster');
        $this->setTemplatesFolder($path . 'cms/templates/');
        $this->setCompileFolder($pathsManager->getPath('templatesCache'));

        $this->preferredEncodings = [
            'gzip',
            'deflate',
            'identity',
        ];
        $this->maxAge = 60 * 5;
    }

    public function getTranslation($params, $smarty)
    {
        if (isset($params['name'])) {
            if (isset($params['required'])) {
                $required = $params['required'];
            } else {
                $required = true;
            }
            if (isset($params['loggable'])) {
                $loggable = $params['loggable'];
            } else {
                $loggable = true;
            }
            if (isset($params['section'])) {
                $section = $params['section'];
            } else {
                $section = null;
            }

            $name = $params['name'];
            if (($text = $this->translationsManager->getTranslationByName($name, $section, $required, $loggable)) !== null) {
                unset($params['name']);
                foreach ($params as $key => $value) {
                    $text = str_replace("%" . $key, $value, $text);
                }
                return $text;
            }
        }
        return '{translations error}';
    }

    public function getElement($params, $smarty)
    {
        if (isset($params['id'])) {
            if (isset($smarty->smarty->tpl_vars['theme'])) {
                /**
                 * @var DesignTheme $currentTheme
                 */
                if ($currentTheme = $smarty->smarty->tpl_vars['theme']->value) {
                    $this->structureManager = $this->getService('structureManager');
                    if ($element = $this->structureManager->getElementById($params['id'])) {
                        if ($templateFileName = $element->getTemplate()) {
                            if ($path = $currentTheme->template($templateFileName)) {
                                $smarty->smarty->assign('element', $element);
                                return $smarty->smarty->fetch($path);
                            }
                        }
                    }
                }
            }
        }
        return false;
    }

    public function smartyLogMessage($params, $smarty)
    {
        if (isset($params['message'])) {
            $this->logError($params['message']);
        }
    }

    public function fetch()
    {
        $content = "";
        try {
            $content = $this->renderingEngine->fetch($this->template);
        } catch (SmartyException $e) {
            $this->logError("Smarty Exception: " . $e->getMessage());
        }
        return $content;
    }

    public function assign($attributeName, $value)
    {
        $this->renderingEngine->assign($attributeName, $value);
    }

    protected function getEtag()
    {
        $this->renderContent();
        return md5($this->contentText);
    }

    protected function getContentLength()
    {
        return strlen($this->contentText);
    }

    protected function getContentTextPart()
    {
        if (!$this->contentRead) {
            $this->contentRead = true;
            return $this->contentText;
        }
        return false;
    }

    protected function getContentType()
    {
        $contentTypes = $this->requestHeadersManager->getAcceptedTypes();

        $preferredOrder = [
            'text/html',
        ];
        $selectedType = $this->selectHTTPParameter($preferredOrder, $contentTypes, '*/*');

        $userAgent = $this->requestHeadersManager->getUserAgent();
        $userAgentVersion = $this->requestHeadersManager->getUserAgentVersion();

        if ($userAgent == 'MSIE' && $userAgentVersion < 9) {
            return 'text/html';
        } else {
            return $selectedType;
        }
    }

    public function getContentDisposition()
    {
        return $this->contentDisposition;
    }

    protected function renderContent()
    {
        if ($this->contentText === null) {
            $this->contentText = $this->fetch();
        }
    }

    protected function compress($encoding)
    {
        if ($encoding == 'gzip') {
            $this->contentText = $this->gzip($this->contentText);
        }
    }

    public function setTemplatesFolder($folder)
    {
        $this->renderingEngine->compile_id = md5($folder);
        $this->renderingEngine->template_dir = $folder;
    }

    public function setCompileFolder($folder)
    {
        if (!file_exists($folder)) {
            $cachePermissions = $this->getService(ConfigManager::class)->get('paths.defaultCachePermissions');
            mkdir($folder, $cachePermissions, true);
        }
        $this->renderingEngine->setCompileDir($folder);
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

    public static function stripDomSpaces($params, $content, Smarty_Internal_Template $template, &$repeat)
    {
        if (!$repeat) {
            $content = preg_replace('/([}>])\s+([{<])/u', '$1$2', $content);
        }
        return $content;
    }

    public function filterTemplateSpaces($tpl_source, Smarty_Internal_Template $template)
    {
        if (!$this->debugMode) {
            $tpl_source = preg_replace('/^\s+|\s+$/u', '', $tpl_source);
            $tpl_source = preg_replace('/([}>])\s+([{<])/u', '$1 $2', $tpl_source);
            $tpl_source = preg_replace('/\s+/u', ' ', $tpl_source);
        } else {
            // don't minify output, it's hard to debug when everything's on 1 line
        }
        return $tpl_source;
    }
}
