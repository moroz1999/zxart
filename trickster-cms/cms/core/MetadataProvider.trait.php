<?php

trait MetadataProviderTrait
{
    protected $textContent;

    public function getMetaTitle()
    {
        if ($this->metaTitle) {
            return $this->metaTitle;
        }

        if (method_exists($this, 'getTemplatedMetaTitle')) {
            if ($templatedMetaTitle = $this->getTemplatedMetaTitle()) {
                return $templatedMetaTitle;
            }
        }

        return $this->title;
    }

    public function getH1()
    {
        if ($this->h1) {
            return $this->h1;
        }

        if (method_exists($this, 'getTemplatedH1')) {
            if ($templatedH1 = $this->getTemplatedH1()) {
                return $templatedH1;
            }
        }

        return $this->title;
    }

    public function getMetaDescription(): string
    {
        if ($this->metaDescription) {
            return $this->metaDescription;
        }

        $metaDescription = $this->getTextContent();
        $metaDescription = str_ireplace('<br />', ' <br />', html_entity_decode($metaDescription, ENT_QUOTES, 'UTF-8'));
        $metaDescription = strip_tags($metaDescription);
        return htmlspecialchars(mb_substr($metaDescription, 0, 325), ENT_QUOTES, 'UTF-8');
    }

    public function getMetaKeywords()
    {
        $metaKeywords = $this->getTextContent();
        $metaKeywords = strip_tags(html_entity_decode($metaKeywords, ENT_QUOTES, 'UTF-8'));
        // strip everything that isn't a letter, # or space
        $metaKeywords = preg_replace("/[^\d\pL\s]+/u", "", $metaKeywords);
        $metaKeywords = mb_strtolower($metaKeywords);
        $this->filterKeywords($metaKeywords);

        return $metaKeywords;
    }

    public function getCanonicalUrl()
    {
        if ($this->canonicalUrl) {
            return $this->canonicalUrl;
        }
        return $this->URL;
    }

    public function getMetaDenyIndex()
    {
        if ($this->metaDenyIndex) {
            return $this->metaDenyIndex;
        }
        return false;
    }

    protected function filterKeywords(&$metaKeywords)
    {
        $keyWordsList = explode(" ", $metaKeywords);
        $keyWordsList = array_unique($keyWordsList);

        foreach ($keyWordsList as $key => &$word) {
            if (is_numeric($word) || mb_strlen($word, 'UTF-8') <= 3) {
                unset($keyWordsList[$key]);
            }
        }
        array_splice($keyWordsList, 20);
        $metaKeywords = implode(",", $keyWordsList);
    }

    public function getTextContent()
    {
        if (is_null($this->textContent)) {
            $this->textContent = "";
            if ($this->introduction) {
                $this->textContent = $this->introduction . " ";
                $this->textContent .= $this->content ? $this->content : "";
            } else {
                $this->textContent .= $this->content ? $this->content : $this->title;
            }
        }
        return $this->textContent;
    }
}