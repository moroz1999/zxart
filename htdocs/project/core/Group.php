<?php

trait Group
{
    protected $linksInfo;

    public function getReleases()
    {
        return $this->publishedReleases;
    }

    public function getAuthorsInfo($type)
    {
        /**
         * @var AuthorsManager $authorsManager
         */
        $authorsManager = $this->getService('AuthorsManager');
        $info = $authorsManager->getAuthorsInfo($this->id, $type);
        $sort = [];
        foreach ($info as $item) {
            $sort[] = $item['authorElement']->getTitle();
        }
        array_multisort($sort, SORT_ASC, $info);
        return $info;
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
            $this->linksInfo = [];
            /**
             * @var translationsManager $translationsManager
             */
            $translationsManager = $this->getService('translationsManager');

            $types = ['3a', 'zxdb', 's4e'];


            /**
             * @var \Illuminate\Database\Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->id)
                ->whereIn('importOrigin', $types);
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    if ($row['importOrigin'] == 'zxdb') {
                        $this->linksInfo[] = [
                            'type' => 'sc',
                            'image' => 'icon_sc.png',
                            'name' => $translationsManager->getTranslationByName('links.link_sc'),
                            'url' => 'http://spectrumcomputing.co.uk/index.php?cat=999&label_id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] == '3a') {
                        $this->linksInfo[] = [
                            'type' => '3a',
                            'image' => 'icon_3a.png',
                            'name' => $translationsManager->getTranslationByName('links.link_3a'),
                            'url' => 'https://zxaaa.net/view_demos.php?a=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    }elseif ($row['importOrigin'] == 's4e') {
                        $this->linksInfo[] = [
                            'type' => 's4e',
                            'image' => 'icon_s4e.png',
                            'name' => $translationsManager->getTranslationByName('links.link_s4e'),
                            'url' => 'https://zxaaa.net/view_demos.php?a=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    }
                }
            }


            if ($this->wikiLink) {
                $this->linksInfo[] = [
                    'type' => 'swiki',
                    'image' => 'icon_swiki.png',
                    'name' => $translationsManager->getTranslationByName('links.link_swiki'),
                    'url' => 'http://speccy.info/' . $this->wikiLink,
                    'id' => $this->wikiLink,
                ];
            }
        }
        if ($this->structureType == 'group') {
            if ($aliasElements = $this->getAliasElements()) {
                /**
                 * @var authorAliasElement $aliasElement
                 */
                foreach ($aliasElements as $aliasElement) {
                    $this->linksInfo = array_merge($this->linksInfo, $aliasElement->getLinksInfo());
                }
            }
        }
        return $this->linksInfo;
    }

    public function getAuthorRoles()
    {
        return [
            'coder',
            'cracker',
            'graphician',
            'hardware',
            'musician',
            'organizer',
            'support',
            'tester',
            'gamedesigner',
            'unknown',
        ];
    }

    public function getProdsInfo(): array
    {
        $prodsInfo = [];
        foreach ($this->getGroupProds() as $prod) {
            $prodsInfo[] = $prod->getElementData('list');
        }
        return $prodsInfo;
    }

    public function getProdsAmount(): int
    {
        return count($this->getGroupProds());
    }
}
