<?php

trait LdJsonProvider
{
    public function getLdJsonScriptHtml()
    {
        $tagText = '';
        if ($ldJsonData = $this->getLdJsonScriptData()) {
            $tagText = '<script type="application/ld+json">' . json_encode($ldJsonData) . '</script>';
        }
        return $tagText;
    }

    abstract public function getLdJsonScriptData();
}