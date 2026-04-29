<?php

class TranslationsCodesComparer
{
    protected $filePaths = [];
    protected $expressions = [];
    /**
     * @var TranslationsFilesParser
     */
    protected $parser;
    protected $unusedTranslations;
    protected $emptyTranslations;
    protected $parseComplete = false;
    protected $translationsIndex = [];

    /**
     * @param array $translationsIndex
     */
    public function setTranslationsIndex($translationsIndex)
    {
        $this->translationsIndex = $translationsIndex;
        $this->unusedTranslations = null;
        $this->emptyTranslations = null;
    }

    public function __construct()
    {
        $this->parser = new TranslationsFilesParser;
        $this->expressions = [
            'smarty' => "#{translations\s+name=['\"]+([a-z0-9._\-]+)\.*([\$a-z0-9_\-]*)['\"]+([^}]*)}#i",
            'javascript' => "#translationsLogics.get\(\s*['\"]+\s*([a-z0-9._\-]+)\s*['\"]+\s*\+*\s*([\.a-z0-9_]*)\s*\)#i",
            'php' => "#->getTranslationByName\(\s*['\"]+([\$a-z0-9._\-]+)['\"]+\s*\.*\s*([\$a-z]*)\s*\,*\s*[\$a-z]*\)#i",
        ];
    }

    public function addFilePath($path, $expression)
    {
        $this->filePaths[] = ['path' => $path, 'expression' => $expression];
    }

    public function getUnusedTranslationCodes()
    {
        if ($this->unusedTranslations === null) {
            $this->unusedTranslations = [];
            if ($usedCodesIndex = $this->getUsedCodesIndex()) {
                foreach ($this->translationsIndex as $code => &$value) {
                    $found = false;
                    foreach ($usedCodesIndex as &$info) {
                        if ($info['dynamic']) {
                            if (stripos($code, $info['code']) !== false) {
                                $found = true;
                                break;
                            }
                        } else {
                            if ($code == $info['code']) {
                                $found = true;
                                break;
                            }
                        }
                    }
                    if (!$found) {
                        $this->unusedTranslations[] = $code;
                    }
                }
            }
        }
        return $this->unusedTranslations;
    }

    public function getEmptyTranslationsCodes()
    {
        if ($this->emptyTranslations === null) {
            $this->emptyTranslations = [];
            if ($usedCodesIndex = $this->getUsedCodesIndex()) {
                foreach ($usedCodesIndex as &$info) {
                    if ($info['dynamic']) {
                        $translated = false;
                        foreach ($this->translationsIndex as $code => &$value) {
                            if (stripos($code, $info['code']) !== false) {
                                $translated = true;
                                break;
                            }
                        }
                        if (!$translated) {
                            $this->emptyTranslations[] = $info['code'];
                        }
                    } else {
                        if (!isset($this->translationsIndex[$info['code']]) || empty($this->translationsIndex[$info['code']])) {
                            $this->emptyTranslations[] = $info['code'];
                        }
                    }
                }
            }
        }
        return $this->emptyTranslations;
    }

    protected function getUsedCodesIndex()
    {
        if (!$this->parseComplete) {
            foreach ($this->filePaths as &$pathInfo) {
                if (isset($this->expressions[$pathInfo['expression']])) {
                    $this->parser->searchFilesInPath($pathInfo['path'], $this->expressions[$pathInfo['expression']]);
                }
            }
            $this->parseComplete = true;
        }
        return $this->parser->getUsedCodesIndex();
    }
}

