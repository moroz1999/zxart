<?php

namespace ZxArt\Import\Services;

use errorLogger;
use Illuminate\Database\Connection;
use Override;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Import\Labels\Label;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Prods\Dto\PartyRefDTO;
use ZxArt\ZxProdCategories\CategoryIds;

class PouetImport extends errorLogger
{
    protected int $timeLimit = 60 * 29;
    protected int $maxCounter = 1000;
    protected int $maxId = 0;
    protected array $ignore = [];
    protected int $counter = 0;

    protected int $maxTime;
    protected $debugEntry;

    private readonly Connection $db;
    protected array $categories;

    /** @var array<string,string> */
    protected array $platforms = [
        'ZX Spectrum' => 'zx48',
        'SAM Coupé' => 'samcoupe',
        'ZX Enhanced' => '',
        'ZX-81' => 'zx811',
    ];

    /** @var array<string,string> */
    protected array $roles = [
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

    /** @var array<string,string> */
    protected array $compos = [
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

    protected array $urls = [];
    private readonly ProdsService $prodsService;
    private readonly QueueService $queueService;

    protected string $origin = 'pouet';

    public function __construct(
        Connection     $db,
        ProdsService   $prodsService,
        AuthorsService $authorsManager,
        QueueService   $queueService
    )
    {
        $this->db = $db;
        $this->prodsService = $prodsService;
        $this->queueService = $queueService;

        $authorsManager->setForceUpdateCountry(false);
        $authorsManager->setForceUpdateCity(false);

        $this->prodsService->setForceUpdateCategories(false);
        $this->prodsService->setForceUpdateYoutube(true);
        $this->prodsService->setForceUpdateGroups(false);
        $this->prodsService->setForceUpdateAuthors(false);
        $this->prodsService->setAddImages(false);

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

    public function importAll(): void
    {
        $this->maxTime = time() + $this->timeLimit;

        if (!empty($this->debugEntry)) {
            $id = (int)$this->debugEntry;
            $this->maxCounter = 1;
        } else {
            $id = ((int)$this->loadMaxImportedId()) + 1;
            $this->maxId = (int)$this->loadMaxPossibleId();
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

                    $labels = [];
                    $groupsIds = [];
                    if (!empty($prodData['groups'])) {
                        foreach ($prodData['groups'] as $group) {
                            $labels[] = new Label(
                                id: isset($group['id']) ? (string)$group['id'] : null,
                                name: isset($group['name']) ? (string)$group['name'] : null,
                                isAlias: false,
                                isPerson: false,
                                isGroup: true,
                                abbreviation: isset($group['acronym']) ? (string)$group['acronym'] : null,
                                website: isset($group['web']) ? (string)$group['web'] : null,
                            );
                            if (isset($group['id'])) {
                                $groupsIds[] = (string)$group['id'];
                            }
                        }
                    }

                    $authorRoles = [];
                    if (!empty($prodData['credits'])) {
                        foreach ($prodData['credits'] as $credit) {
                            $authorId = '';
                            $authorName = '';
                            if (!empty($credit['user'])) {
                                $authorId = (string)$credit['user']['id'];
                                $authorName = (string)$credit['user']['nickname'];
                            }
                            $labels[] = new Label(
                                id: $authorId !== '' ? $authorId : null,
                                name: $authorName !== '' ? $authorName : null,
                                isAlias: false,
                                isPerson: true,
                                isGroup: false,
                            );

                            if (empty($credit['role'])) {
                                $authorRoles[$authorId] = [];
                            } else {
                                $roles = explode(',', strtolower($credit['role']));
                                $authorRoles[$authorId] = [];
                                foreach ($roles as $role) {
                                    $roleParts = explode('&', strtolower($role));
                                    foreach ($roleParts as $rolePart) {
                                        $rolePart = trim($rolePart);
                                        if (isset($this->roles[$rolePart])) {
                                            $authorRoles[$authorId][] = $this->roles[$rolePart];
                                        } else {
                                            $this->logError('Pouet: missing role ' . $rolePart . ' ' . $id);
                                            exit;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $directCategories = [];
                    if (!empty($prodData['types'])) {
                        foreach ($prodData['types'] as $type) {
                            if (!empty($this->categories[$type])) {
                                $directCategories[] = (int)$this->categories[$type];
                            } elseif (!isset($this->categories[$type])) {
                                $this->logError('Pouet: missing category ' . $type . ' ' . $prodData['name'] . ' ' . $prodData['id']);
                                exit;
                            }
                        }
                    }

                    $partyDto = null;
                    $compo = '';
                    if (!empty($prodData['placings'])) {
                        foreach ($prodData['placings'] as $placing) {
                            if (isset($placing['party'])) {
                                $partyDto = new PartyRefDTO(
                                    title: (string)$placing['party']['name'],
                                    year: isset($placing['year']) ? (int)$placing['year'] : null,
                                    place: isset($placing['ranking']) ? (int)$placing['ranking'] : null,
                                );
                            } else {
                                $this->logError('Pouet: no party for placing ' . $id . ' ' . ($placing['compo_name'] ?? ''));
                                exit;
                            }
                            if (!empty($placing['compo_name'])) {
                                $compoName = $placing['compo_name'];
                                if (isset($this->compos[$compoName])) {
                                    $compo = $this->compos[$compoName];
                                } else {
                                    $this->logError('Pouet: unknown compo ' . $id . ' ' . $compoName);
                                    exit;
                                }
                            }
                            break;
                        }
                    }

                    $youtubeId = null;
                    if (!empty($prodData['downloadLinks'])) {
                        foreach ($prodData['downloadLinks'] as $link) {
                            if ($link['type'] === 'youtube' && !empty($link['link'])) {
                                $ll = $link['link'];
                                if (stripos($ll, 'https://youtu.be/') === 0) {
                                    $youtubeId = substr($ll, strlen('https://youtu.be/'));
                                }
                                if (stripos($ll, 'https://www.youtube.com/watch?v=') === 0) {
                                    $youtubeId = substr($ll, strlen('https://www.youtube.com/watch?v='));
                                }
                                if (stripos($ll, 'http://www.youtube.com/watch?v=') === 0) {
                                    $youtubeId = substr($ll, strlen('http://www.youtube.com/watch?v='));
                                }
                            }
                        }
                    }

                    $prodImages = [];
                    if (!empty($prodData['screenshot'])) {
                        $prodImages[] = (string)$prodData['screenshot'];
                    }

                    $releaseFileUrl = null;
                    if (!empty($prodData['download'])) {
                        $url = (string)$prodData['download'];
                        if (stripos($url, 'files.scene.org') !== false) {
                            $url = str_ireplace('/view/', '/get/', $url);
                        }
                        $releaseFileUrl = $url;
                    }

                    $hardwareRequired = [];
                    foreach ($prodData['platforms'] as $platform) {
                        $name = $platform['name'];
                        if (!empty($this->platforms[$name])) {
                            $hardwareRequired[] = $this->platforms[$name];
                        }
                    }

                    $releaseDto = new ReleaseImportDTO(
                        id: (string)$prodData['id'],
                        title: (string)$prodData['name'],
                        year: null,
                        language: [],
                        version: '',
                        releaseType: 'original',
                        filePath: null,
                        fileUrl: $releaseFileUrl,
                        fileName: null,
                        description: null,
                        hardwareRequired: $hardwareRequired ?: null,
                        labels: null,
                        authors: null,
                        publishers: null,
                        undetermined: null,
                        images: null,
                        inlayImages: null,
                        infoFiles: null,
                        adFiles: null,
                        md5: null,
                    );

                    $year = null;
                    if (!empty($prodData['releaseDate'])) {
                        $ts = strtotime((string)$prodData['releaseDate']);
                        if ($ts !== false) {
                            $year = (int)date('Y', $ts);
                        }
                    }

                    $importIds = [];
                    if (!empty($prodData['demozoo'])) {
                        $importIds['dzoo'] = (string)$prodData['demozoo'];
                        $importIds['zxd'] = (string)$prodData['demozoo'];
                    }

                    $prodDto = new ProdImportDTO(
                        id: (string)$prodData['id'],
                        title: (string)$prodData['name'],
                        altTitle: null,
                        description: null,
                        language: [],
                        legalStatus: '',
                        youtubeId: $youtubeId,
                        externalLink: null,
                        compo: $compo !== '' ? $compo : null,
                        year: $year,
                        ids: null,
                        importIds: $importIds ?: null,
                        labels: $labels ?: null,
                        authorRoles: $authorRoles ?: null,
                        groups: $groupsIds ?: null,
                        publishers: [],
                        undetermined: null,
                        party: $partyDto,
                        directCategories: $directCategories ?: null,
                        categories: null,
                        images: $prodImages ?: null,
                        maps: null,
                        inlayImages: null,
                        rzx: null,
                        compilationItems: null,
                        seriesProds: null,
                        articles: null,
                        releases: [$releaseDto],
                    );

                    if ($prodElement = $this->prodsService->importProd($prodDto, $this->origin)) {
                        $this->queueService->updateStatus($prodElement->getPersistedId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

                        $this->updateReport($id, 'success');
                        $this->markProgress('prod ' . $id . ' imported ' . $this->counter . ' ' . $prodDto->title . ' ' . $prodElement->id . ' ' . $prodElement->getUrl());
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

    #[Override]
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
        echo $text . ' ' . sprintf('%.2f', $endTime - $previousTime) . '<br/>';
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
        return 0;
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