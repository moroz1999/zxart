<?php

class WosManager extends errorLogger
{
    protected $maxTime;
    protected $counter = 0;
    protected $maxCounter;
    protected $minCounter = 0;
//    protected $debugEntry = 37772;

    /**
     * @var ProdsManager
     */
    protected $prodsManager;
    /**
     * @var AuthorsManager
     */
    protected $authorsManager;
    /**
     * @var GroupsManager
     */
    protected $groupsManager;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    /**
     * @var Config
     */
    protected $zxdbConfig;
    /**
     * @var \Illuminate\Database\MySqlConnection
     */
    protected $zxdb;
    protected $archiveLink = 'http://www.worldofspectrum.org/pub/';
    protected $archive2Link = 'https://archive.org/download/World_of_Spectrum_June_2017_Mirror/World%20of%20Spectrum%20June%202017%20Mirror.zip/World%20of%20Spectrum%20June%202017%20Mirror/';
    protected $wosFilesPath;
    protected $releaseFileTypes;
    protected $releaseTypes;
    protected $inlayFileTypes;
    protected $ayFileTypes;
    protected $mapFileTypes;
    protected $infoFileTypes;
    protected $adFileTypes;
    protected $categories;
    protected $origin = 'zxdb';
    protected $releasesInfo = [];
    protected $legalStatuses = [
        'D' => 'forbidden',
        'd' => 'insales',
        'A' => 'unknown',
        '?' => 'mia',
        'N' => 'unreleased',
        'R' => 'recovered',
    ];
    protected $minMachines = [
        "24" => "atm",
        "14" => "pentagon128",
        "16" => "samcoupe",
        "15" => "scorpion",
        "17" => "sinclairql",
        "11" => "timex2048",
        "12" => "timex2068",
        "25" => "zxevolution",
        "7" => "zx128+2",
        "10" => "zx128+2b",
        "8" => "zx128+3",
        "5" => "zx128",
        "1" => "zx16",
        "2" => "zx16",
        "3" => "zx48",
        "4" => "zx48",
        "27" => "zxnext",
        "26" => "zxuno",
        "18" => "zx80",
        "21" => "zx8116",
        "19" => "zx811",
        "20" => "zx812",
        "22" => "zx8132",
        "23" => "zx8164",
    ];

    protected $roles = [
        "C" => "code",
        "D" => "gamedesign",
        "G" => "graphics",
        "A" => "illustrating",
        "V" => "leveldesign",
        "S" => "loading_screen",
        "T" => "localization",
        "M" => "music",
        "X" => "sfx",
        "W" => "story",
    ];

    protected $optionalMachines = [
        "9" => "zx128+3",
        "13" => "timex2068",
        "6" => "zx128",
        "4" => "zx128",
        "2" => "zx48",
    ];
    protected $featureGroups = [
        "9003" => "cursor",
        "9001" => "int2_1",
        "9002" => "int2_2",
        "9004" => "kempston",
        "9006" => "zxpand",
        "1025" => "ay",
        "101" => "cheetah",
        "102" => "specdrum",
    ];
    protected $languages = [
        "be" => ["be"],
        "bs" => ["bs"],
        "ca" => ["ca"],
        "cs" => ["cs"],
        "da" => ["da"],
        "de" => ["de"],
        "el" => ["el"],
        "en" => ["en"],
        "eo" => ["eo"],
        "es" => ["es"],
        "eu" => ["eu"],
        "fi" => ["fi"],
        "fr" => ["fr"],
        "gl" => ["gl"],
        "hr" => ["hr"],
        "hu" => ["hu"],
        "is" => ["is"],
        "it" => ["it"],
        "la" => ["la"],
        "lt" => ["lt"],
        "lv" => ["lv"],
        "m-" => ["fr"],
        "nl" => ["nl"],
        "no" => ["no"],
        "pl" => ["pl"],
        "pt" => ["pt"],
        "ro" => ["ro"],
        "ru" => ["ru"],
        "sh" => ["sh"],
        "sk" => ["sk"],
        "sl" => ["sl"],
        "sr" => ["sr"],
        "sv" => ["sv"],
        "tr" => ["tr"],
        "y-" => ["sh"],
        "?r" => ["bs", "hr", "sr"],
        "?l" => ["ca", "en", "it", "es"],
        "?0" => ["hr", "en"],
        "?1" => ["cz", "en"],
        "?n" => ["cz", "en", "it", "pl", "ru", "es"],
        "?m" => ["cz", "en", "ru", "sk"],
        "?a" => ["cz", "en", "sk"],
        "?2" => ["nl", "en"],
        "?o" => ["en", "eo, es"],
        "?k" => ["en", "fr"],
        "?b" => ["en", "fr", "de"],
        "?s" => ["en", "fr", "de", "it", "pt", "ru", "es", "sv"],
        "?q" => ["en", "fr", "de", "it", "pt", "es"],
        "?c" => ["en", "fr", "de", "it", "es"],
        "?e" => ["en", "fr", "es"],
        "?3" => ["en", "de"],
        "?h" => ["en", "de", "hu", "ru"],
        "?d" => ["en", "de", "it", "pt", "es"],
        "?i" => ["en", "de", "it", "es"],
        "?u" => ["en", "hu"],
        "?t" => ["en", "it"],
        "?p" => ["en", "it", "pl", "ru", "es"],
        "?4" => ["en", "pl"],
        "?f" => ["en", "pl", "ru", "es"],
        "?5" => ["en", "pt"],
        "?6" => ["en", "ru"],
        "?g" => ["en", "ru", "es"],
        "?7" => ["en", "sk"],
        "?8" => ["en", "es"],
        "?j" => ["la", "es"],
        "?9" => ["es", "ca"],
    ];

    public function __construct()
    {
        $this->wosFilesPath = ROOT_PATH . 'wosfiles/';
        $this->releaseTypes = [
            '?' => 'unknown',
            'u' => 'unknown',
            'O' => 'original',
            'o' => 'original',
            'R' => 'rerelease',
            'r' => 'rerelease',
            'H' => 'crack',
            '-' => 'mia',
            'B' => 'corrupted',
            'i' => 'incomplete',
            'C' => 'compilation',
            'c' => 'compilation',
        ];
        $this->releaseFileTypes = [
            8, //tape
            10, //snapshot
            11,//disk image
            17, //Computer/ZX Interface 2 cartridge ROM image dump
            18, //DOCK cartridge ROM image dump
            19, //ZX81 archive file
            20,//Archive file
            21,//Covertape version
            22,//BUGFIX tape image
            47,
        ];
        $this->inlayFileTypes = [
            4,
            5,
            6,
            7,
            67,
        ];
        //        $this->ayFileTypes = [
        //            23,
        //        ];
        $this->mapFileTypes = [
            31,
        ];
        $this->infoFileTypes = [
            28,
            29,
        ];
        $this->adFileTypes = [
            37,
            42,
            43,
            44,
            45,
            51,
            52,
            59,
            60,
        ];
        $this->categories = [
            '1' => true,
            '2' => true,
            '3' => true,
            '4' => true,
            '5' => true,
            '6' => true,
            '7' => true,
            '8' => true,
            '9' => true,
            '10' => true,
            '11' => true,
            '12' => true,
            '13' => true,
            '14' => true,
            '15' => true,
            '16' => true,
            '17' => true,
            '18' => true,
            '19' => true,
            '20' => true,
            '21' => true,
            '22' => true,
            '23' => true,
            '24' => true,
            '25' => true,
            '26' => true,
            '27' => true,
            '28' => true,
            '29' => true,
            '30' => true,
            '31' => true,
            '32' => true,
            '33' => true,
            '34' => true,
            '35' => true,
            '36' => true,
            '37' => true,
            '38' => true,
            '39' => true,
            '40' => true,
            '41' => true,
            '42' => true,
            '43' => true,
            '44' => true,
            '45' => true,
            '46' => true,
            '47' => true,
            '48' => true,
            '49' => true,
            '50' => true,
            '51' => true,
            '52' => true,
            '53' => true,
            '54' => true,
            '55' => true,
            '56' => true,
            '57' => true,
            '58' => true,
            '59' => true,
            '60' => true,
            '61' => true,
            '62' => true,
            '63' => true,
            '64' => true,
            '65' => true,
            '66' => true,
            '67' => true,
            '68' => true,
            '69' => true,
            '70' => true,
            '71' => true,
            '72' => true,
            '73' => true,
            '74' => true,
            '75' => true,
            '76' => true,
            '77' => true,
            '78' => true,
            '79' => true,
            '80' => true,
            '82' => true,
            '83' => true,
            '110' => true,
            '111' => true,
            '112' => true,
            '113' => true,
            '114' => true,
        ];
    }

    /**
     * @param \Illuminate\Database\MySqlConnection $zxdbConfig
     */
    public function setZxdbConfig($zxdbConfig)
    {
        $this->zxdbConfig = $zxdbConfig;
        $this->makeZxdb();
    }

    /**
     * @param AuthorsManager $authorsManager
     */
    public function setAuthorsManager($authorsManager)
    {
        $this->authorsManager = $authorsManager;
    }

    /**
     * @param GroupsManager $groupsManager
     */
    public function setGroupsManager($groupsManager)
    {
        $this->groupsManager = $groupsManager;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager)
    {
        $this->countriesManager = $countriesManager;
    }

    /**
     * @param mixed $prodsManager
     */
    public function setProdsManager(ProdsManager $prodsManager)
    {
        $this->prodsManager = $prodsManager;
        $this->prodsManager->setForceUpdateYoutube(true);
//        $this->prodsManager->setUpdateExistingProds(true);
//        $this->prodsManager->setForceUpdateAuthors(true);
//        $this->prodsManager->setForceUpdateTitles(true);
//        $this->prodsManager->setForceUpdateCategories(true);
//        $this->prodsManager->setForceUpdatePublishers(true);
//        $this->prodsManager->setForceUpdateGroups(true);
//        $this->prodsManager->setForceUpdateImages(true);
    }

    public function importAll()
    {
        $this->maxTime = time() + 60 * 28;
        if (is_file($this->getStatusPath())) {
            $this->minCounter = (int)file_get_contents($this->getStatusPath());
        }
        $this->importCountries();
        $this->importZxdbProds();
    }

    public function importCountries()
    {
        if ($countries = $this->zxdb->table('countries')->select('*')->get()) {
            foreach ($countries as $key => $country) {
                $this->countriesManager->importCountry(
                    [
                        'id' => $country['id'],
                        'title' => $country['text'],
                    ],
                    $this->origin
                );
                $this->markProgress(
                    'country ' . $key . '/' . count($countries) . ' imported ' . $country['id'] . ' ' . $country['text']
                );
            }
        }
    }

    public function importZxdbProds()
    {
        if ($entries = $this->zxdb->table('entries')->select('*')->get()) {
            foreach ($entries as $entry) {
                $this->counter++;
                if ($this->counter < $this->minCounter) {
                    continue;
                }

                if (!empty($this->debugEntry) && $entry['id'] != $this->debugEntry) {
                    continue;
                }
                if (isset($this->categories[$entry['genretype_id']])) {
                    $prodInfo = [
                        'title' => $entry['title'],
                        'year' => '',
                        'legalStatus' => '',
                        'id' => $entry['id'],
                        'language' => [],
                        'categories' => [$entry['genretype_id']],
                        'images' => [],
                        'labels' => [],
                        'authors' => [],
                        'publishers' => [],
                        'groups' => [],
                        'releases' => [],
                        'compilations' => [],
                    ];

                    if ($entry['language_id']) {
                        if (isset($this->languages[$entry['language_id']])) {
                            $prodInfo['language'] = $this->languages[$entry['language_id']];
                        }
                    }
                    if (!empty($entry['availabletype_id'])) {
                        if (isset($this->legalStatuses[$entry['availabletype_id']])) {
                            $prodInfo['legalStatus'] = $this->legalStatuses[$entry['availabletype_id']];
                        }
                    }

                    if ($records = $this->zxdb->table('compilations')
                        ->select('entry_id')
                        ->where('compilation_id', '=', $entry['id'])
                        ->orderBy('prog_seq', 'asc')
                        ->get()
                    ) {
                        foreach ($records as $record) {
                            $prodInfo['compilations'][] = $record['entry_id'];
                        }
                    }
                    if ($publishers = $this->zxdb->table('publishers')
                        ->select('*')
                        ->where('entry_id', '=', $entry['id'])
                        ->where('release_seq', '=', 0)
                        ->orderBy('publisher_seq', 'asc')
                        ->get()
                    ) {
                        foreach ($publishers as $publisher) {
                            $labelId = $publisher['label_id'];
                            $labelInfo = $this->gatherLabelsInfo($prodInfo['labels'], $labelId);
                            if ($labelInfo) {
                                $prodInfo['publishers'][] = $labelInfo['id'];
                            }
                        }
                    }
                    $query = $this->zxdb->table('authors')
                        ->select(['authors.label_id', 'roles.roletype_id'])
                        ->leftJoin(
                            'roles',
                            function ($join) {
                                $join->on('authors.entry_id', '=', 'roles.entry_id')
                                    ->on('authors.label_id', '=', 'roles.label_id');
                            }
                        )
                        ->where('authors.entry_id', '=', $entry['id'])
                        ->orderBy('author_seq', 'asc');
                    if ($authors = $query->get()
                    ) {
                        foreach ($authors as $author) {
                            $labelId = $author['label_id'];
                            $labelInfo = $this->gatherLabelsInfo($prodInfo['labels'], $labelId);
                            if ($labelInfo) {
                                if ($labelInfo['isPerson']) {
                                    if (isset($this->roles[$author['roletype_id']])) {
                                        $prodInfo['authors'][$labelInfo['id']] = [$this->roles[$author['roletype_id']]];
                                    } else {
                                        $prodInfo['authors'][$labelInfo['id']] = [];
                                    }
                                } elseif ($labelInfo['isGroup']) {
                                    $prodInfo['groups'][] = $labelInfo['id'];
                                }
                            }
                        }
                    }
                    if ($rows = $this->zxdb->table('authors')
                        ->select('*')
                        ->where('entry_id', '=', $entry['id'])
                        ->where('team_id', '>', 0)
                        ->groupBy('team_id')
                        ->get()) {
                        foreach ($rows as $row) {
                            $prodInfo['groups'][] = $row['team_id'];
                        }
                    }
                    if ($rows = $this->zxdb->table('webrefs')
                        ->where('entry_id', '=', $entry['id'])
                        ->where('website_id', '=', 16)
                        ->limit(1)
                        ->get()) {
                        foreach ($rows as $row) {
                            if (stripos($row['link'], 'https://youtu.be/') === 0) {
                                $prodInfo['youtubeId'] = substr($row['link'], strlen('https://youtu.be/'));
                            }
                        }
                    }

                    $this->getReleasesInfo($entry);
                    foreach ($this->releasesInfo[$entry['id']] as $releaseInfo) {
                        if ($releaseInfo['year'] && (!$prodInfo['year'] || $releaseInfo['year'] < $prodInfo['year'])) {
                            $prodInfo['year'] = $releaseInfo['year'];
                        }
                    }
                    //distribut all images across prod object and appropriate releases
                    if ($downloads = $this->zxdb->table('downloads')
                        ->select('*')
                        ->where('entry_id', '=', $entry['id'])
                        ->orderBy('release_seq', 'asc')
                        ->get()) {
                        foreach ($downloads as $download) {
                            if ($releaseInfo = &$this->releasesInfo[$download['entry_id']][$download['release_seq']]) {
                                if (in_array($download['filetype_id'], $this->inlayFileTypes)) {
                                    $releaseInfo['inlayImages'][] = $this->getArchiveLink($download['file_link']);
                                } elseif (in_array($download['filetype_id'], $this->infoFileTypes)) {
                                    $releaseInfo['infoFiles'][] = $this->getArchiveLink($download['file_link']);
                                } elseif (in_array($download['filetype_id'], $this->adFileTypes)) {
                                    $releaseInfo['adFiles'][] = $this->getArchiveLink($download['file_link']);
                                }
                            }
                            unset($releaseInfo);
                            if ($download['filetype_id'] == '1') {
                                $prodInfo['images'][] = $this->getArchiveLink($download['file_link']);
                            } elseif ($download['filetype_id'] == '2') {
                                $prodInfo['images'][] = $this->getArchiveLink($download['file_link']);
                            } elseif (in_array($download['filetype_id'], $this->mapFileTypes)) {
                                $prodInfo['maps'][] = $this->getArchiveLink($download['file_link']);
                            }
                        }
                    }
                    //compile complete list of all releases - all files in all formats separately, plus all releases without downloads
                    $unusedReleases = $this->releasesInfo[$entry['id']];
                    foreach ($downloads as $download) {
                        if (in_array($download['filetype_id'], $this->releaseFileTypes)) {
                            if ($releaseInfo = $this->releasesInfo[$download['entry_id']][$download['release_seq']]) {
                                if ($download['language_id'] != $entry['language_id']) {
                                    if (isset($this->languages[$download['language_id']])) {
                                        $releaseInfo['language'] = $this->languages[$download['language_id']];
                                    }
                                }
                                $releaseInfo['fileUrl'] = $this->getArchiveLink($download['file_link'], true);
                                $releaseInfo['id'] .= '_' . basename($download['file_link']);
                                $releaseInfo['id'] = md5($releaseInfo['id']);
                                if (!empty($download['comments'])) {
                                    $releaseInfo['version'] = $download['comments'];
                                }
                                if (isset($this->releaseTypes[$download['sourcetype_id']])) {
                                    $releaseInfo['releaseType'] = $this->releaseTypes[$download['sourcetype_id']];
                                }
                                if (empty($download['machinetype_id'])) {
                                    $download['machinetype_id'] = $entry['machinetype_id'];
                                }
                                if (isset($this->minMachines[$download['machinetype_id']])) {
                                    $releaseInfo['hardwareRequired'] = [$this->minMachines[$download['machinetype_id']]];
                                }
                                if (isset($this->optionalMachines[$download['machinetype_id']])) {
                                    $releaseInfo['hardwareRequired'][] = $this->optionalMachines[$download['machinetype_id']];
                                }

                                if (isset($unusedReleases[$download['release_seq']])) {
                                    unset($unusedReleases[$download['release_seq']]);
                                } else {
                                    //this is not the first time we use this release as a separate release, so we should not duplicate the inlay files.
                                    unset($releaseInfo['inlayImages']);
                                    unset($releaseInfo['adFiles']);
                                    unset($releaseInfo['infoFiles']);
                                }
                                $prodInfo['releases'][] = $releaseInfo;
                            }
                        }
                    }
                    foreach ($unusedReleases as $unusedRelease) {
                        $unusedRelease['id'] = md5($unusedRelease['id']);
                        $prodInfo['releases'][] = $unusedRelease;
                    }

                    if ($this->prodsManager->importProd($prodInfo, $this->origin)) {
                        $this->markProgress(
                            'prod ' . $this->counter . '/' . count(
                                $entries
                            ) . ' imported ' . $prodInfo['id'] . ' ' . $prodInfo['title']
                        );
                    } else {
                        $this->markProgress('prod failed ' . $prodInfo['id'] . ' ' . $prodInfo['title']);
                    }
                }
                file_put_contents($this->getStatusPath(), $this->counter);

                if (($this->maxCounter && ($this->counter >= $this->maxCounter)) || time() >= $this->maxTime) {
                    break;
                }
            }
        }
    }

    protected function getReleasesInfo($entry)
    {
        $entryId = $entry['id'];
        if ($releases = $this->zxdb->table('releases')
            ->select('*')
            ->where('entry_id', '=', $entryId)
            ->get()) {
            foreach ($releases as $release) {
                $release_seq = $release['release_seq'];
                $releaseInfo = [
                    'title' => $entry['title'],
                    'year' => $release['release_year'],
                    'language' => [],
                    'hardwareRequired' => [],
                    'images' => [],
                    'inlayImages' => [],
                    'infoFiles' => [],
                    'fileUrl' => false,
                    'version' => '',
                ];

                $releaseInfo['id'] = $release['entry_id'] . '_' . $release['release_seq'];

                if ($controls = $this->zxdb->table('members')
                    ->select('tag_id')
                    ->where('entry_id', '=', $entryId)
                    ->get()) {
                    foreach ($controls as $control) {
                        if (isset($this->featureGroups[$control['tag_id']])) {
                            $releaseInfo['hardwareRequired'][] = $this->featureGroups[$control['tag_id']];
                        }
                    }
                }
                if ($publishers = $this->zxdb->table('publishers')
                    ->select('*')
                    ->where('entry_id', '=', $entry['id'])
                    ->where('release_seq', '=', $release['release_seq'])
                    ->orderBy('publisher_seq', 'asc')
                    ->get()
                ) {
                    foreach ($publishers as $publisher) {
                        $labelId = $publisher['label_id'];
                        $labelInfo = $this->gatherLabelsInfo($releaseInfo['labels'], $labelId);
                        if ($labelInfo) {
                            $releaseInfo['publishers'][] = $labelInfo['id'];
                        }
                    }
                }
                $this->releasesInfo[$entryId][$release_seq] = $releaseInfo;
            }
        }
    }

    protected function getArchiveLink($link, $archive = false)
    {
        if (stripos($link, 'zxdb') !== false) {
            return 'https://spectrumcomputing.co.uk/' . $link;
        } else {
            if (substr($link, 0, 5) == '/pub/') {
                $link = substr($link, 5);
            }
            if ($archive) {
                return $this->archive2Link . $link;
            } else {
                return $this->archiveLink . $link;
            }
        }
    }

    protected function gatherLabelsInfo(&$infoIndex, $labelId, $isGroup = false, $isPerson = false, $isAlias = false)
    {
        if (!isset($infoIndex[$labelId])) {
            $infoIndex[$labelId] = [];
            if ($label = $this->zxdb->table('labels')->select('*')->where('id', '=', $labelId)->limit(1)->first()) {
                $infoIndex[$labelId] = [
                    'id' => $label['id'],
                    'title' => $label['name'],
                    'countryId' => $label['country_id'],
                    'isGroup' => $isGroup,
                    'isPerson' => $isPerson,
                    'isAlias' => $isAlias,
                ];

                if ($label['labeltype_id'] === '+') {
                    $infoIndex[$labelId]['isPerson'] = true;
                } elseif ($label['labeltype_id'] === '-') {
                    $infoIndex[$labelId]['isPerson'] = true;
                    $infoIndex[$labelId]['isAlias'] = true;
                } elseif ($label['labeltype_id'] !== null) {
                    $infoIndex[$labelId]['isGroup'] = true;
                }

                if (!$infoIndex[$labelId]['isGroup'] && ($team = $this->zxdb->table('authors')
                        ->select('*')
                        ->where('team_id', '=', $labelId)
                        ->limit(1)
                        ->first())) {
                    $infoIndex[$labelId]['isGroup'] = true;
                }

//                if ($label['owner_id'] !== null) {
//
//                }
                if (($label['from_id'] !== null) && ($label['from_id'] != $label['owner_id'])) {
                    if ($fromInfo = $this->gatherLabelsInfo(
                        $infoIndex,
                        $label['from_id'],
                        $infoIndex[$labelId]['isGroup'],
                        $infoIndex[$labelId]['isPerson']
                    )) {
                        if ($fromInfo['isPerson']) {
                            $infoIndex[$labelId]['isPerson'] = true;
                            $infoIndex[$labelId]['authorId'] = $fromInfo['id'];
                        } elseif ($fromInfo['isGroup']) {
                            $infoIndex[$labelId]['isGroup'] = true;
                            $infoIndex[$labelId]['groupId'] = $fromInfo['id'];
                        }
                        $infoIndex[$labelId]['isAlias'] = true;
                    }
                }

                if ($rows = $this->zxdb->table('authors')
                    ->select('*')
                    ->where('label_id', '=', $labelId)
                    ->where('team_id', '>', 0)
                    ->groupBy('team_id')
                    ->get()) {
                    foreach ($rows as $row) {
                        if ($teamInfo = $this->gatherLabelsInfo($infoIndex, $row['team_id'], true, false)) {
                            $infoIndex[$labelId]['groups'][] = $teamInfo['id'];
                        }
                    }
                    $infoIndex[$labelId]['isPerson'] = true;
                }
            }
        }
        return $infoIndex[$labelId];
    }

    protected function makeZxdb()
    {
        if ($this->zxdb === null) {
            $manager = new Illuminate\Database\Capsule\Manager();
            $manager->addConnection(
                [
                    'driver' => 'mysql',
                    'host' => $this->zxdbConfig->get('mysqlHost'),
                    'database' => $this->zxdbConfig->get('mysqlDatabase'),
                    'username' => $this->zxdbConfig->get('mysqlUser'),
                    'password' => $this->zxdbConfig->get('mysqlPassword'),
                    'charset' => 'utf8',
                    'collation' => 'utf8_unicode_ci',
                ],
                'zxdb'
            );
            $manager->setFetchMode(PDO::FETCH_ASSOC);
            $this->zxdb = $manager->getConnection('zxdb');
        }
    }

    protected function markProgress($text)
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo $text . ' ' . sprintf("%.2f", $endTime - $previousTime) . '<br/>';
        flush();
        file_put_contents(ROOT_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }

    protected function getStatusPath()
    {
        return ROOT_PATH . 'wos.txt';
    }
}