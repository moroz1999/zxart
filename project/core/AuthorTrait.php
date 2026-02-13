<?php

use Illuminate\Database\Connection;
use Symfony\Component\ObjectMapper\ObjectMapper;
use ZxArt\Tunes\Rest\TuneRestDto;
use ZxArt\Tunes\TunesTransformer;

trait AuthorTrait
{
    protected $years = [];
    protected $worksList;
    protected $linksInfo;

    public function getYearsWorks($type = 'authorPicture')
    {
        if (!isset($this->years[$type])) {
            $this->years[$type] = [];

            if ($works = $this->getWorksList([$type])) {
                $sort = [];
                foreach ($works as $work) {
                    $sort[] = strtolower($work->title);
                }
                array_multisort($sort, SORT_ASC, $works);

                foreach ($works as $work) {
                    $this->years[$type][$work->year][] = $work;
                }

                krsort($this->years[$type]);
            }
        }

        return $this->years[$type];
    }

    /**
     * @return array<int, array{year: int, tunes: array}>
     */
    public function getTunesByYearData(): array
    {
        $yearsWorks = $this->getYearsWorks('authorMusic');
        if (!$yearsWorks) {
            return [];
        }

        $transformer = $this->getService(TunesTransformer::class);
        $mapper = new ObjectMapper();
        $result = [];

        foreach ($yearsWorks as $year => $tunes) {
            $items = [];
            foreach ($tunes as $tune) {
                if ($tune instanceof zxMusicElement) {
                    $dto = $transformer->toDto($tune);
                    $items[] = $mapper->map($dto, TuneRestDto::class);
                }
            }
            $result[] = [
                'year' => (int)$year,
                'tunes' => $items,
            ];
        }

        return $result;
    }

    public function checkParentLetter(): void
    {
        $linksManager = $this->getService('linksManager');
        $letterId = $this->getLetterId($this->title);

        if ($links = $linksManager->getElementsLinks($this->getId())) {
            $link = reset($links);
            if ($link->parentStructureId != $letterId) {
                $linksManager->unLinkElements($link->parentStructureId, $this->id);
                $linksManager->linkElements($letterId, $this->id);
                $structureManager = $this->getService('structureManager');
                $structureManager->regenerateStructureInfo($this);
            }
        }
    }

    /**
     * @return string
     *
     * @psalm-return 'authors'|'authorsmenu'
     */
    protected function getLettersListMarker(string $type)
    {
        if ($type === 'admin') {
            return 'authors';
        } else {
            return 'authorsmenu';
        }
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
            $this->linksInfo = [];
            $translationsManager = $this->getService('translationsManager');

            if ($this->is3aDenied()) {
                $types = ['zxdb', 'pouet', 's4e', 'worldofsam'];
            } else {
                $types = ['3a', 'zxdb', 'pouet', 's4e', 'worldofsam'];
            }

            /**
             * @var Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->id)
                ->whereIn('importOrigin', $types);
            if ($rows = $query->get()) {
                /**
                 * Psalm type hint for database rows structure
                 * @var iterable<array{importId:string, importOrigin:string}> $rows
                 */
                foreach ($rows as $row) {
                    if ($row['importOrigin'] === 'zxdb') {
                        $this->linksInfo[] = [
                            'type' => 'sc',
                            'image' => 'icon_sc.png',
                            'name' => $translationsManager->getTranslationByName('links.link_sc'),
                            'url' => 'https://spectrumcomputing.co.uk/index.php?cat=999&label_id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === '3a') {
                        $this->linksInfo[] = [
                            'type' => '3a',
                            'image' => 'icon_3a.png',
                            'name' => $translationsManager->getTranslationByName('links.link_3a'),
                            'url' => 'https://zxaaa.net/view_demos.php?a=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'vt') {
                        $this->linksInfo[] = [
                            'type' => 'vt',
                            'image' => 'icon_vt.png',
                            'name' => $translationsManager->getTranslationByName('links.link_vt'),
                            'url' => 'https://vtrd.in/release.php?r=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'pouet') {
                        $this->linksInfo[] = [
                            'type' => 'pouet',
                            'image' => 'icon_pouet.png',
                            'name' => $translationsManager->getTranslationByName('links.link_pouet'),
                            'url' => 'https://www.pouet.net/user.php?who=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 's4e') {
                        $this->linksInfo[] = [
                            'type' => 's4e',
                            'image' => 'icon_s4e.png',
                            'name' => $translationsManager->getTranslationByName('links.link_s4e'),
                            'url' => 'https://spectrum4ever.org/fulltape.php?go=releases&id=' . $row['importId'] . '&by=cracker',
                            'id' => $row['importId'],
                        ];
                    } elseif ($row['importOrigin'] === 'worldofsam') {
                        $this->linksInfo[] = [
                            'type' => 'worldofsam',
                            'image' => 'icon_worldofsam.png',
                            'name' => $translationsManager->getTranslationByName('links.link_worldofsam'),
                            'url' => 'https://www.worldofsam.org/people/' . $row['importId'],
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
                    'url' => 'https://speccy.info/' . $this->wikiLink,
                    'id' => $this->wikiLink,
                ];
            }
            if ($this->zxTunesId) {
                $this->linksInfo[] = [
                    'type' => 'zxt',
                    'image' => 'icon_zxt.png',
                    'name' => $translationsManager->getTranslationByName('links.link_zxt'),
                    'url' => 'https://zxtunes.com/author.php?id=' . $this->zxTunesId,
                    'id' => $this->zxTunesId,
                ];
            }
            if ($this->artCityId) {
                $this->linksInfo[] = [
                    'type' => 'ac',
                    'image' => 'icon_ac.png',
                    'name' => $translationsManager->getTranslationByName('links.link_ac'),
                    'url' => 'https://artcity.bitfellas.org/index.php?a=artist&id=' . $this->artCityId,
                    'id' => $this->artCityId,
                ];
            }
        }
        if ($this->structureType === 'author') {
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

    abstract public function getWorksList(array $types);

    abstract public function getGroupsList();

    public function getProdsInfo(): array
    {
        $prodsInfo = [];
        foreach ($this->getProds() as $prod) {
            $prodsInfo[] = $prod->getElementData('list');
        }
        return $prodsInfo;
    }

    public function getProdsAmount(): int
    {
        return count($this->getProds());
    }
}
