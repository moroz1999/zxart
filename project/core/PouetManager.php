<?php

use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Groups\Services\GroupsService;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\ZxProdCategories\CategoryIds;

/**
 * todo: re-implement import operations
 */
class PouetManager extends errorLogger
{
    protected $timeLimit = 60 * 29;
    protected $maxTime;
    protected $maxCounter = 1000;
    protected $maxId = 0;
    protected $debugEntry;
    protected $ignore = [];
    protected $counter = 0;
    /**
     * @var \Illuminate\Database\Connection
     */
    protected $db;
    protected $categories;
    protected $platforms = [
        'ZX Spectrum' => 'zx48',
        'SAM Coupé' => 'samcoupe',
        'ZX Enhanced' => '',
        'ZX-81' => 'zx811',
    ];
    protected $roles = [
        'all' => 'unknown',
        'code and direction' => 'code',
        'add code' => 'code',
        'coder' => 'code',
        'scroll code' => 'code',
        'all wired code' => 'code',
        'coding' => 'code',
        'code. graphics' => 'code',
        'code etc.' => 'code',
        'code' => 'code',
        'zx2 code' => 'code',
        'fpga' => 'code',
        'add. code' => 'code',
        'party coding' => 'code',
        'agd parts of code' => 'code',
        'hacking' => 'code',
        'x86 hack' => 'code',
        'loader' => 'code',
        'assembler coding' => 'code',
        'additional code' => 'code',
        'fix' => 'code',
        'original code' => 'code',
        'z80 code' => 'code',
        'x86 code' => 'code',
        'coding help' => 'code',
        'original idea/code' => 'concept',
        'code (part 1)' => 'code',
        'code (part 2)' => 'code',
        'code (part 3)' => 'code',
        'code (part 4)' => 'code',
        'code (part 5)' => 'code',
        'scroller coding' => 'code',
        'intro code' => 'intro_code',
        'intro coding' => 'intro_code',
        'hype intro coding' => 'intro_code',
        'intro' => 'intro_code',
        'directing' => 'direction',
        'direction' => 'direction',
        'jingles for hype and album intros' => 'music',
        '1-bit / beeper music' => 'music',
        'all 18 tracks' => 'music',
        'original music' => 'music',
        'music' => 'music',
        'msx' => 'music',
        'musics' => 'music',
        'last pattern' => 'music',
        'gfx (skull)' => 'graphics',
        'gfx conversions' => 'graphics',
        'art' => 'graphics',
        'pixels' => 'graphics',
        'main gfx' => 'graphics',
        'some gfx' => 'graphics',
        'grapchics' => 'graphics',
        'grafics' => 'graphics',
        'graphics' => 'graphics',
        'gfx' => 'graphics',
        'additional graphics' => 'graphics',
        '3d' => '3dmodels',
        '3d graphics' => '3dmodels',
        '2d' => 'graphics',
        'design support' => 'design',
        'additional design' => 'design',
        'demo design' => 'design',
        'graphics (design)' => 'design',
        'design' => 'design',
        'ui design' => 'design',
        'ux/ui' => 'design',
        'organizer' => 'organizing',
        'other (organizing)' => 'organizing',
        'other (guest part)' => 'guest',
        'editor' => 'editing',
        'co-editor' => 'editing',
        'story' => 'story',
        'script' => 'story',
        'txt' => 'text',
        'lyrics' => 'story',
        'text)' => 'text',
        'scrolltext' => 'text',
        'text' => 'text',
        'texts' => 'text',
        'other (text)' => 'text',
        'other (text' => 'text',
        'nothinп' => 'concept',
        'but ideas' => 'concept',
        'algorithms' => 'concept',
        'idea)' => 'concept',
        'idea' => 'concept',
        'nothing' => 'concept',
        'concept' => 'concept',
        'other (idea' => 'concept',
        'other (idea)' => 'concept',
        'other (concept)' => 'concept',
        'ideas' => 'concept',
        'gamedev' => 'gamedesign',
        'level editor' => 'tools',
        'tools' => 'tools',
        'player' => 'tools',
        'levels' => 'leveldesign',
        'maps' => 'leveldesign',
        'some music' => 'music',
        'chiptune music' => 'music',
        'music (cover)' => 'music',
        'digital music' => 'music',
        'digital sound' => 'music',
        'graphics (logo)' => 'logo',
        'logos' => 'logo',
        'logo' => 'logo',
        'original logo' => 'logo',
        'other' => 'unknown',
        'code (additional)' => 'code',
        'basic' => 'code',
        'some code' => 'code',
        'rework of rotatrix' => 'code',
        'characters in border' => 'code',
        'gigascreen simulation' => 'code',
        'support' => 'support',
        'audio' => 'sfx',
        'sound' => 'sfx',
        'fx' => 'sfx',
        'sfx' => 'sfx',
        'noise' => 'sfx',
        'samples' => 'sfx',
        'other (fonts)' => 'font',
        'fonts' => 'font',
        'font' => 'font',
        'video' => 'video',
        'video montage' => 'video',
        'video editing' => 'video',
        'intro music' => 'intro_music',
        'intro picture' => 'intro_graphics',
        'linking' => 'code',
        'main code' => 'code',
        'ay music' => 'music',
        'beeper music' => 'music',
        'graphic' => 'graphics',
        'fullscreen picture' => 'graphics',
        'end pic.' => 'graphics',
        'anims' => 'graphics',
        'animaion' => 'graphics',
        'animation' => 'graphics',
        '2d animation' => 'graphics',
        '2d graphics' => 'graphics',
        'graphics conversion' => 'graphics',
        'grafix' => 'graphics',
        'title screen' => 'graphics',
        'compilation' => 'support',
        'party spirit support' => 'support',
        'supplement' => 'support',
        'specscii gfx' => 'specscii',
        'ascii art' => 'ascii',
        'ascii' => 'ascii',
        'asci' => 'ascii',
        'graphics (ascii artistry)' => 'ascii',
        'bug hunting' => 'testing',
        'digital cover design' => 'illustrating',
        'translation' => 'localization',
        'english language help' => 'localization',
        'choreography' => 'organizing',
        'organisation' => 'organizing',
        'photo' => 'organizing',
        'photos' => 'organizing',
        'fixing' => 'adaptation',
        'remixing' => 'adaptation',
        'game port' => 'adaptation',
        'adaptation' => 'adaptation',
        'everything' => 'code',
        'beer director' => 'support',
        'code optimization' => 'code',
        'optimisations' => 'code',
        'maintainer' => 'support',
        'music (original)' => 'music',
        'technical assistance' => 'support',
    ];

    protected $compos = [
        'combined oldskool demo/intro' => 'demo',
        'zx enhanced demo' => 'enhanced',
        'combined demo/intro' => 'demo',
        'megademo' => 'demo',
        'zx demo' => 'demo',
        '8bit demo' => 'demo',
        'lowend demo' => 'demo',
        'oldskool demo' => 'demo',
        'alternative demo' => 'demo',
        'combined demo' => 'demo',
        'lowend intro' => 'intro',
        'oldskool intro' => 'intro',
        'zx intro' => 'intro',
        '4k intro' => 'intro',
        'hi-end procedural gfx' => 'procedural',
        'oldskool procedural gfx' => 'procedural',
        '1k procedural gfx' => 'procedural1k',
        '4k procedural gfx' => 'procedural4k',
        'combined 256b' => 'intro256',
        'oldskool 128b intro' => 'intro128',
        'oldskool 128b' => 'intro128',
        'oldskool 256b intro' => 'intro256',
        'oldskool 256b' => 'intro256',
        'pc 256b' => 'intro256',
        'combined 128b' => 'intro128',
        'oldskool 32b' => 'intro32',
        'combined 32b' => 'intro32',
        'oldskool 16b' => 'intro16',
        'combined 64b' => 'intro64',
        'oldskool 64b' => 'intro64',
        'zx 256b' => 'intro256',
        'zx 512b' => 'intro512',
        'oldskool 512b' => 'intro512',
        'oldskool 1k' => 'intro1k',
        'oldskool 4k' => 'intro4k',
        '8bit 1k' => 'intro1k',
        '8bit 4k' => 'intro4k',
        'pc 4k' => 'intro4k',
        'combined 1k' => 'intro1k',
        'zx 1k' => 'intro1k',
        'zx 4k' => 'intro4k',
        'combined 4k' => 'intro4k',
        'wild demo' => 'wild',
        'gravedigger' => 'gravedigger',
        'fast demo' => 'realtime_coding',
        'none' => 'out',
        'invit' => 'invitation',
        'gamedev' => 'game',
        'BASIC demo' => 'basic',
        'useless utility' => 'utility',
        '32k game' => 'game',
        'combined intro' => 'intro',
        'crazy demo' => 'demo',
        'musicdisk' => 'musicdisk',
        'coding' => 'realtime_coding',
        '2d demo' => '2d_demo',
        'one scene' => 'onescene',
        'amiga demo' => 'amigademo',
    ];


    protected $urls = [];

    /**
     * @var ProdsService
     */
    protected $prodsService;
    /**
     * @var AuthorsService
     */
    protected $authorsManager;
    /**
     * @var GroupsService
     */
    protected $groupsService;
    /**
     * @var CountriesManager
     */
    protected $countriesManager;
    private QueueService $queueService;
    protected $origin = 'pouet';

    public function __construct()
    {
        $this->categories = [
            'demo' => CategoryIds::DEMOS->value,
            '64k' => CategoryIds::DEMOS->value,
            '64b' => 262452,
            '128b' => 262453,
            '256b' => 92169,
            '512b' => 92168,
            '1k' => 92167,
            '4k' => 92166,
            '8k' => 92165,
            '16k' => 92164,
            '32k' => 315119,
            '40k' => 92163,
            '128k' => 315120,
            'artpack' => 315121,
            'cracktro' => 92171,
            '32b' => 262451,
            'demopack' => 315126,
            'demotool' => 92183,
            'dentro' => 92162,
            'diskmag' => 92179,
            'fastdemo' => 315136,
            'game' => 92177,
            'intro' => 92163,
            'invitation' => 92173,
            'musicdisk' => 92175,
            'procedural graphics' => 315137,
            'report' => CategoryIds::DEMOS->value,
            'slideshow' => 315121,
            'votedisk' => 315121,
            'wild' => CategoryIds::DEMOS->value,
            'bbstro' => 364978,
        ];

    }

    public function setQueueService(QueueService $queueService): void
    {
        $this->queueService = $queueService;
    }

    /**
     * @param \Illuminate\Database\Connection $db
     */
    public function setDb($db): void
    {
        $this->db = $db;
    }

    /**
     * @param AuthorsService $authorsManager
     */
    public function setAuthorsManager($authorsManager): void
    {
        $this->authorsManager = $authorsManager;
        $authorsManager->setForceUpdateCountry(false);
        $authorsManager->setForceUpdateCity(false);
    }

    /**
     * @param GroupsService $groupsService
     */
    public function setGroupsService($groupsService): void
    {
        $this->groupsService = $groupsService;
    }

    /**
     * @param CountriesManager $countriesManager
     */
    public function setCountriesManager($countriesManager): void
    {
        $this->countriesManager = $countriesManager;
    }

    public function setProdsService(ProdsService $prodsService): void
    {
        $this->prodsService = $prodsService;
        $this->prodsService->setForceUpdateCategories(false);
        $this->prodsService->setForceUpdateYoutube(true);
        $this->prodsService->setForceUpdateGroups(false);
        $this->prodsService->setForceUpdateAuthors(false);
        $this->prodsService->setAddImages(false);
    }

    /**
     * @return void
     */
    public function importAll()
    {
        $this->maxTime = time() + $this->timeLimit;

        if (!empty($this->debugEntry)) {
            $id = $this->debugEntry;
            $this->maxCounter = 1;
        } else {
            $id = $this->loadMaxImportedId() + 1;
            $this->maxId = $this->loadMaxPossibleId();
        }
        while (
            (!$this->maxCounter || ($this->counter < $this->maxCounter)) &&
            (time() <= $this->maxTime) &&
            ($id <= $this->maxId || $this->debugEntry)
        ) {
            $this->maxCounter--;
            if ($prodData = $this->download($id)) {
                $platformSupported = false;
                foreach ($prodData['platforms'] as $platform) {
                    if (isset($this->platforms[$platform['name']])) {
                        $platformSupported = true;
                    }
                }
                if ($platformSupported) {
                    $this->counter++;

                    $prodInfo = [
                        'title' => $prodData['name'],
                        'year' => '',
                        'compo' => '',
                        'legalStatus' => '',
                        'id' => $prodData['id'],
                        'language' => [],
                        'categories' => [],
                        'images' => [],
                        'labels' => [],
                        'authors' => [],
                        'publishers' => [],
                        'groups' => [],
                        'releases' => [],
                        'importIds' => [],
                    ];
                    foreach ($prodData['types'] as $type) {
                        if (!empty($this->categories[$type])) {
                            $prodInfo['directCategories'][] = $this->categories[$type];
                        } elseif (!isset($this->categories[$type])) {
                            $this->logError('Pouet: missing category ' . $type . ' ' . $prodData['name'] . ' ' . $prodData['id']);
                            exit;
                        }
                    }
                    foreach ($prodData['groups'] as $group) {
                        $label = [
                            'id' => $group['id'],
                            'title' => $group['name'],
                            'countryId' => false,
                            'abbreviation' => false,
                            'isGroup' => false,
                            'isPerson' => false,
                            'isAlias' => false,
                        ];
                        if (isset($group['acronym'])) {
                            $label['abbreviation'] = $group['acronym'];
                        }
                        if (isset($group['web'])) {
                            $label['website'] = $group['web'];
                        }
                        $prodInfo['labels'][] = $label;
                        $prodInfo['groupsIds'][] = $label['id'];
                    }
                    if (!empty($prodData['releaseDate'])) {
                        $prodInfo['year'] = date('Y', strtotime($prodData['releaseDate']));
                    }
                    if (!empty($prodData['demozoo'])) {
                        $prodInfo['importIds']['dzoo'] = $prodData['demozoo'];
                        $prodInfo['importIds']['zxd'] = $prodData['demozoo'];
                    }

                    foreach ($prodData['credits'] as $credit) {
                        $label = [
                            'id' => '',
                            'title' => '',
                            'countryId' => false,
                            'abbreviation' => false,
                            'isGroup' => false,
                            'isPerson' => true,
                            'isAlias' => false,
                        ];
                        if (!empty($credit['user'])) {
                            $label['id'] = $credit['user']['id'];
                            $label['title'] = $credit['user']['nickname'];
                        }
                        $prodInfo['labels'][] = $label;
                        if (empty($credit['role'])) {
                            $prodInfo['authors'][$label['id']] = [];
                        } else {
                            $roles = explode(',', strtolower($credit['role']));
                            $prodInfo['authors'][$label['id']] = [];
                            foreach ($roles as $role) {
                                $roleParts = explode('&', strtolower($role));
                                foreach ($roleParts as $rolePart) {
                                    $rolePart = trim($rolePart);
                                    if (isset($this->roles[$rolePart])) {
                                        $prodInfo['authors'][$label['id']][] = $this->roles[$rolePart];
                                    } else {
                                        $this->logError('Pouet: missing role ' . $rolePart . ' ' . $id);
                                        exit;
                                    }
                                }
                            }
                        }
                    }
                    foreach ($prodData['placings'] as $placing) {
                        if (isset($placing['party'])) {
                            $prodInfo['party'] = [
                                'title' => $placing['party']['name'],
                                'year' => $placing['year'],
                                'website' => $placing['party']['web'],
                                'place' => $placing['ranking'],
                            ];
                        } else {
                            $this->logError('Pouet: no party for placing ' . $id . ' ' . $placing['compo_name']);
                            exit;
                        }
                        if (!empty($placing['compo_name'])) {
                            if (isset($this->compos[$placing['compo_name']])) {
                                $prodInfo['compo'] = $this->compos[$placing['compo_name']];
                            } else {
                                $this->logError('Pouet: unknown compo ' . $id . ' ' . $placing['compo_name']);
                                exit;
                            }
                        }
                        break;
                    }
                    if (!empty($prodData['screenshot'])) {
                        $prodInfo['images'][] = $prodData['screenshot'];
                    }
                    foreach ($prodData['downloadLinks'] as $link) {
                        if ($link['type'] == 'youtube') {
                            if (stripos($link['link'], 'https://youtu.be/') === 0) {
                                $prodInfo['youtubeId'] = substr($link['link'], strlen('https://youtu.be/'));
                            }
                            if (stripos($link['link'], 'https://www.youtube.com/watch?v=') === 0) {
                                $prodInfo['youtubeId'] = substr($link['link'], strlen('https://www.youtube.com/watch?v='));
                            }
                            if (stripos($link['link'], 'http://www.youtube.com/watch?v=') === 0) {
                                $prodInfo['youtubeId'] = substr($link['link'], strlen('http://www.youtube.com/watch?v='));
                            }
                        }
                    }
                    $releaseInfo = [
                        'id' => $prodInfo['id'],
                        'title' => $prodInfo['title'],
                        'year' => false,
                        'releaseType' => 'original',
                        'language' => [],
                        'hardwareRequired' => [],
                        'images' => [],
                        'inlayImages' => [],
                        'infoFiles' => [],
                        'fileUrl' => false,
                        'version' => '',
                    ];
                    if (!empty($prodData['download'])) {
                        $url = $prodData['download'];
                        if (stripos($url, 'files.scene.org') !== false) {
                            $url = str_ireplace('/view/', '/get/', $url);
                        }
                        $releaseInfo['fileUrl'] = $url;
                    }
                    foreach ($prodData['platforms'] as $platform) {
                        if (!empty($this->platforms[$platform['name']])) {
                            $releaseInfo['hardwareRequired'][] = $this->platforms[$platform['name']];
                        }
                    }
                    $prodInfo['releases'][] = $releaseInfo;

                    if ($prodElement = $this->prodsService->importProdOld($prodInfo, $this->origin)) {
                        $this->queueService->updateStatus($prodElement->getId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

                        $this->updateReport($id, 'success');
                        $this->markProgress('prod ' . $id . ' imported ' . $this->counter . ' ' . $prodInfo['title'] . ' ' . $prodElement->id . ' ' . $prodElement->getUrl());
                    }
                } else {
                    $this->updateReport($id, 'skipped');
                    $this->markProgress('prod ' . $id . ' skipped');
                }
            }
            $id++;
        }
    }

    protected function download($id)
    {
        $prodData = false;
        $link = 'https://api.pouet.net/v1/prod/?id=' . $id;
        $string = $this->attemptDownload($link);
        if (!$string) {
            $this->markProgress('prod ' . $id . ' download attempt 2');

            sleep(3);
            $string = $this->attemptDownload($link);
        }
        if (!$string) {
            $this->markProgress('prod ' . $id . ' download attempt 3');

            sleep(20);
            $string = $this->attemptDownload($link);
        }
        if ($string) {
            if ($json = json_decode($string, true)) {
                if (!empty($json['success']) && !empty($json['prod'])) {
                    $prodData = $json['prod'];
                } else {
                    $this->markProgress('prod ' . $id . ' not exists');
                    $this->updateReport($id, 'notexists');
                }
            }
        } else {
            $this->markProgress('prod ' . $id . ' not downloaded');
            $this->updateReport($id, 'notdownloaded');
        }
        return $prodData;
    }

    protected function attemptDownload(string $link): bool|string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $link);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0',
            'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'Accept-Language: en-US,en;q=0.5',
            'Cache-Control: max-age=0',
        ]);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        $verbose = fopen('php://temp', 'w+');
        curl_setopt($ch, CURLOPT_STDERR, $verbose);

        $string = curl_exec($ch);
        curl_close($ch);

        if (!$string) {
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
        }
        return $string;
    }

    /**
     * @return void
     */
    protected function logError($message, $level = null): void
    {
        $this->markProgress($message);
        parent::logError($message, $level);
    }

    protected function markProgress(string $text): void
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo $text . ' ' . sprintf("%.2f", $endTime - $previousTime) . '<br/>';
        flush();
        $previousTime = $endTime;
    }

    protected function updateReport($id, string $status): void
    {
        $existingRecord = $this->db->table('import_pouet')->where('id', $id)->first();

        if ($existingRecord) {
            $this->db->table('import_pouet')->where('id', $id)->update(['status' => $status]);
        } else {
            $this->db->table('import_pouet')->insert([
                'id' => $id,
                'status' => $status,
            ]);
        }
    }

    protected function loadMaxImportedId()
    {
        if ($record = $this->db->table('import_pouet')
            ->whereIn('status', ['notexists', 'success', 'skipped'])
            ->orderBy('id', 'desc')
            ->limit(1)
            ->value('id')) {
            return $record;
        }
    }

    protected function loadMaxPossibleId()
    {
        $link = 'https://api.pouet.net/v1/front-page/latest-added/';
        $string = $this->attemptDownload($link);
        if (!$string) {
            $this->markProgress('Max possible id download attempt 2');

            sleep(3);
            $string = $this->attemptDownload($link);
        }
        if (!$string) {
            $this->markProgress('Max possible id download attempt 3');

            sleep(20);
            $string = $this->attemptDownload($link);
        }
        if ($string) {
            if ($json = json_decode($string, true)) {
                if (!empty($json['success']) && !empty($json['prods']) && !empty($json['prods'][0])) {
                    $prodData = $json['prods'][0];
                    return $prodData['id'];
                }
            }
        }
        return null;
    }
}