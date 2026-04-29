<?php

class TranslationsFilesParser
{
    protected $usedCodesIndex = [];

    /**
     * @return array
     */
    public function getUsedCodesIndex()
    {
        return $this->usedCodesIndex;
    }

    public function searchFilesInPath($filesPath, $expression)
    {
        if (is_dir($filesPath)) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($filesPath), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $fileInfo) {
                if (is_file($path = $fileInfo->getPathname())) {
                    if ($content = file_get_contents($path)) {
                        if (preg_match_all($expression, $content, $matches)) {
                            if (isset($matches[1])) {
                                foreach ($matches[1] as $number => &$translationCode) {
                                    if (isset($this->usedCodesIndex[$translationCode])) {
                                        $info = $this->usedCodesIndex[$translationCode];
                                    } else {
                                        $info = [];
                                        $info['code'] = $translationCode;
                                        $info['files'] = [];
                                    }
                                    $info['files'][$path] = true;
                                    if (isset($matches[2][$number]) && !empty($matches[2][$number])) {
                                        $info['dynamic'] = $matches[2][$number];
                                    } elseif (isset($matches[3][$number]) && !empty($matches[3][$number])) {
                                        $info['dynamic'] = $matches[3][$number];
                                    } else {
                                        $info['dynamic'] = false;
                                    }
                                    $this->usedCodesIndex[$translationCode] = $info;
                                }
                            }
                        }
                    }
                }
            }
        }
    }
}