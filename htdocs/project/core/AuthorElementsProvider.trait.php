<?php

trait AuthorElementsProviderTrait
{
    protected $authors;
    protected $realAuthors;
    use CacheOperatingElement;

    /**
     * returns list of authors and aliases directly connected to zxItem
     *
     * @return authorElement[]|authorAliasElement[]
     */
    public function getAuthorsList()
    {
        if ($this->authors === null) {
            $cache = $this->getElementsListCache('al', 60 * 60);
            if (($this->authors = $cache->load()) === false) {
                $structureManager = $this->getService('structureManager');
                $this->authors = [];
                if ($authorsList = $this->getAuthorIds()) {
                    foreach ($authorsList as $authorId) {
                        if ($author = $structureManager->getElementById($authorId)) {
                            $this->authors[] = $author;
                        }
                    }
                }
                $cache->save($this->authors);
            }
        }
        return $this->authors;
    }


    /**
     * returns list of all authors connected to zxItem directly or through aliases
     *
     * @return authorElement[]
     */
    public function getRealAuthorsList()
    {
        if ($this->realAuthors === null) {
            $this->realAuthors = [];
            foreach ($this->getAuthorsList() as $author) {
                if ($author->structureType == 'author') {
                    $this->realAuthors[] = $author;
                } elseif (($author->structureType == 'authorAlias') && ($realAuthor = $author->getAuthorElement())) {
                    $this->realAuthors[] = $realAuthor;
                }
            }
        }
        return $this->realAuthors;
    }

    abstract public function getAuthorIds();
}