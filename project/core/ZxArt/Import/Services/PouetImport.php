<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

use errorLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Connection;
use Override;
use RuntimeException;
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

/**
 * @psalm-type PouetPlatform = array{
 *   name: string
 * }
 *
 * @psalm-type PouetGroup = array{
 *   id: int|string,
 *   name: string,
 *   acronym?: string,
 *   web?: string
 * }
 *
 * @psalm-type PouetUser = array{
 *   id: int|string,
 *   nickname: string
 * }
 *
 * @psalm-type PouetCredit = array{
 *   user?: PouetUser,
 *   role?: string
 * }
 *
 * @psalm-type PouetParty = array{
 *   name: string,
 *   web?: string
 * }
 *
 * @psalm-type PouetPlacing = array{
 *   party?: PouetParty,
 *   year?: int,
 *   ranking?: int,
 *   compo_name?: string
 * }
 *
 * @psalm-type PouetDownloadLink = array{
 *   type: string,
 *   link: string
 * }
 *
 * @psalm-type PouetProd = array{
 *   id: string,
 *   name: string,
 *   types?: list<string>,
 *   groups?: list<PouetGroup>,
 *   platforms: list<PouetPlatform>,
 *   credits?: list<PouetCredit>,
 *   placings?: list<PouetPlacing>,
 *   screenshot?: string,
 *   download?: string,
 *   downloadLinks?: list<PouetDownloadLink>,
 *   demozoo?: string,
 *   releaseDate?: string
 * }
 *
 * @psalm-type PouetApiResponse = array{
 *   success: bool|int,
 *   prod?: PouetProd
 * }
 */
class PouetImport extends errorLogger
{
    protected int $timeLimit = 60 * 29;
    protected int $maxCounter = 1000;
    protected int $maxId = 0;
    protected array $ignore = [];
    protected int $counter = 0;

    protected ?int $maxTime = null;
    protected ?int $debugEntry = null;

    private readonly Connection $db;
    private readonly ProdsService $prodsService;
    private readonly QueueService $queueService;
    private readonly Client $http;

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
        'some modelling' => '3dmodels',
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

        $this->http = new Client([
            'timeout' => 20,
            'connect_timeout' => 10,
            'verify' => false, // keep it exactly as requested
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:66.0) Gecko/20100101 Firefox/66.0',
                'Accept' => 'application/json,text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Cache-Control' => 'max-age=0',
            ],
        ]);

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

        $this->roles = $this->normalizeKeysToLower($this->roles);
        $this->compos = $this->normalizeKeysToLower($this->compos);
    }

    public function importAll(): void
    {
        $this->maxTime = time() + $this->timeLimit;

        if ($this->debugEntry !== null) {
            $id = $this->debugEntry;
            $this->maxCounter = 1;
        } else {
            $id = ($this->loadMaxImportedId()) + 1;
            $this->maxId = $this->loadMaxPossibleId() ?? 0;
        }
        while (
            (!$this->maxCounter || ($this->counter < $this->maxCounter)) &&
            (time() <= $this->maxTime) &&
            ($id <= $this->maxId || $this->debugEntry !== null)
        ) {
            // honor ignore list if populated
            if (in_array($id, $this->ignore, true)) {
                $this->updateReport($id, 'ignored');
                $this->markProgress('prod ' . $id . ' ignored');
                $id++;
                continue;
            }

            try {
                $this->processId($id);
            } catch (\Throwable $e) {
                $this->markProgress('ERROR at prod ' . $id . ': ' . $e->getMessage());
                throw $e;
            }

            $id++;
        }
    }

    protected function processId(int $id): void
    {
        $this->maxCounter--;

        $prodData = $this->download($id);
        if (!$prodData) {
            return;
        }

        $platformSupported = false;
        foreach ($prodData['platforms'] as $platform) {
            if (isset($this->platforms[$platform['name']])) {
                $platformSupported = true;
                break;
            }
        }

        if (!$platformSupported) {
            $this->updateReport($id, 'skipped');
            $this->markProgress('prod ' . $id . ' skipped');
            return;
        }

        $this->counter++;

        $labels = [];
        $groupsIds = [];
        $prodDataGroups = $prodData['groups'] ?? [];
        foreach ($prodDataGroups as $group) {
            $labels[] = new Label(
                id: isset($group['id']) ? (string)$group['id'] : null,
                name: $group['name'] ?? null,
                isAlias: null,
                isPerson: false,
                isGroup: true,
                abbreviation: $group['acronym'] ?? null,
                website: $group['web'] ?? null,
            );
            if (isset($group['id'])) {
                $groupsIds[] = (string)$group['id'];
            }
        }


        $authorRoles = [];
        $prodDataCredits = $prodData['credits'] ?? [];
        foreach ($prodDataCredits as $credit) {
            $authorId = '';
            $authorName = '';
            if (!empty($credit['user'])) {
                $authorId = (string)$credit['user']['id'];
                $authorName = $credit['user']['nickname'];
            }
            $labels[] = new Label(
                id: $authorId !== '' ? $authorId : null,
                name: $authorName !== '' ? $authorName : null,
                isAlias: null,
                isPerson: true,
                isGroup: false,
            );

            if (!isset($credit['role'])) {
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
                            $message = 'Pouet: missing role ' . $rolePart . ' ' . $id;
                            $this->logError($message);
                            throw new RuntimeException($message);
                        }
                    }
                }
            }
        }

        $directCategories = [];
        $prodDataTypes = $prodData['types'] ?? [];
        foreach ($prodDataTypes as $type) {
            $typeKey = $type;
            if (!empty($this->categories[$typeKey])) {
                $directCategories[] = (int)$this->categories[$typeKey];
            } elseif (!isset($this->categories[$typeKey])) {
                $message = 'Pouet: missing category ' . $typeKey . ' ' . $prodData['name'] . ' ' . $prodData['id'];
                $this->logError($message);
                throw new RuntimeException($message);
            }
        }

        $partyDto = null;
        $compo = '';
        $prodDataPlacings = $prodData['placings'] ?? [];
        foreach ($prodDataPlacings as $placing) {
            if (isset($placing['party'])) {
                $partyDto = new PartyRefDTO(
                    title: $placing['party']['name'],
                    year: $placing['year'] ? (int)$placing['year'] : null,
                    place: $placing['ranking'] ? (int)$placing['ranking'] : null,
                );
            } else {
                $message = 'Pouet: no party for placing ' . $id . ' ' . ($placing['compo_name'] ?? '');
                $this->logError($message);
                throw new RuntimeException($message);
            }
            if (isset($placing['compo_name'])) {
                $compoName = strtolower($placing['compo_name']);
                if (isset($this->compos[$compoName])) {
                    $compo = $this->compos[$compoName];
                } else {
                    $message = 'Pouet: unknown compo ' . $id . ' ' . $placing['compo_name'];
                    $this->logError($message);
                    throw new RuntimeException($message);
                }
            }
            break;
        }


        $youtubeId = null;
        $prodDataDownloadLinks = $prodData['downloadLinks'] ?? [];
        foreach ($prodDataDownloadLinks as $link) {
            if ($link['type'] === 'youtube' && isset($link['link'])) {
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


        $prodImages = [];
        if (isset($prodData['screenshot'])) {
            $prodImages[] = $prodData['screenshot'];
        }

        $releaseFileUrl = null;
        if (isset($prodData['download'])) {
            $url = $prodData['download'];
            if (stripos($url, 'files.scene.org') !== false) {
                $url = str_ireplace('/view/', '/get/', $url);
            }
            $releaseFileUrl = $url;
        }

        $hardwareRequired = [];
        foreach ($prodData['platforms'] as $platform) {
            $name = $platform['name'];
            if (isset($this->platforms[$name])) {
                $hardwareRequired[] = $this->platforms[$name];
            }
        }
        // оставляем пустые значения как ты и хотел
        $hardwareRequired = $hardwareRequired ?: null;

        $releaseDto = new ReleaseImportDTO(
            id: $prodData['id'],
            title: $prodData['name'],
            year: null,
            languages: [],
            version: '',
            releaseType: 'original',
            filePath: null,
            fileUrl: $releaseFileUrl,
            fileName: null,
            description: null,
            hardwareRequired: $hardwareRequired,
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
        if (isset($prodData['releaseDate'])) {
            $ts = strtotime($prodData['releaseDate']);
            if ($ts !== false) {
                $year = (int)date('Y', $ts);
            }
        }

        $importIds = [];
        if (isset($prodData['demozoo'])) {
            $importIds['dzoo'] = $prodData['demozoo'];
            $importIds['zxd'] = $prodData['demozoo'];
        }

        $prodDto = new ProdImportDTO(
            id: $prodData['id'],
            title: $prodData['name'],
            altTitle: null,
            description: null,
            languages: [],
            legalStatus: null,
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
            seriesProdIds: null,
            articles: null,
            releases: [$releaseDto],
        );

        if ($prodElement = $this->prodsService->importProd($prodDto, $this->origin)) {
            $this->queueService->updateStatus($prodElement->getPersistedId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);

            $this->updateReport($id, 'success');
            $this->markProgress('prod ' . $id . ' imported ' . $this->counter . ' ' . ($prodDto->title ?? '') . ' ' . $prodElement->id . ' ' . $prodElement->getUrl());
        }
    }

    /**
     * @return null|PouetProd
     */
    protected function download(int $id): ?array
    {
        $prodData = null;
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
            $json = json_decode($string, true);
            if (is_array($json)) {
                if (!empty($json['success']) && !empty($json['prod'])) {
                    /** @var PouetProd $prod */
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

        /** @var PouetProd|null $prod */
        return $prodData;
    }

    protected function attemptDownload(string $link): bool|string
    {
        try {
            $response = $this->http->get($link);
            $body = (string)$response->getBody();
            return $body !== '' ? $body : false;
        } catch (GuzzleException) {
            return false;
        }
    }

    #[Override]
    protected function logError($message, $level = null): void
    {
        $this->markProgress((string)$message);
        parent::logError($message, $level);
    }

    protected function markProgress(string $text): void
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo '<div style="font-family:monospace;">' . htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') .
            ' <span style="color:#888;">' . sprintf('%.2f', $endTime - $previousTime) . "s</span></div>\n";
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

    protected function loadMaxImportedId(): int
    {
        if ($record = $this->db->table('import_pouet')
            ->whereIn('status', ['notexists', 'success', 'skipped'])
            ->orderBy('id', 'desc')
            ->limit(1)
            ->value('id')) {
            return (int)$record;
        }
        return 0;
    }

    protected function loadMaxPossibleId(): ?int
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
            $json = json_decode($string, true);
            if (is_array($json) && !empty($json['success']) && !empty($json['prods']) && !empty($json['prods'][0])) {
                $prodData = $json['prods'][0];
                return isset($prodData['id']) ? (int)$prodData['id'] : null;
            }
        }
        return null;
    }

    /**
     * @param array<string,string> $map
     * @return array<string,string>
     */
    private function normalizeKeysToLower(array $map): array
    {
        $normalized = [];
        foreach ($map as $k => $v) {
            $normalized[strtolower($k)] = $v;
        }
        return $normalized;
    }
}
