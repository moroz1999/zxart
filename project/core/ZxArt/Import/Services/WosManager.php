<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

use Config;
use ConfigManager;
use CountriesManager;
use errorLogger;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Connection;
use PDO;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdsService;

class WosManager extends errorLogger
{
    protected int|null $maxTime = null;
    protected int $counter = 0;
    protected int $maxCounter = 0;
    protected int $minCounter = 0;
    protected ?int $debugEntry = 43280;
    private const int FILETYPE_LOADING = 1;
    private const int FILETYPE_RUNNING = 2;
    private const int FILETYPE_OPENING = 3;
    private const int FILETYPE_RZX = 63;
    protected array $ignoreIds = [
        'tag3885',
        'tag3866',
        'tag3865',
        'tag3864',
        'tag3863',
        'tag3860',
        'tag3859',
        'tag3825',
        'tag3824',
        'tag3820',
        'tag3782',
        'tag3778',
        'tag3773',
        'tag3727',
        'tag3706',
        'tag3704',
        'tag3703',
        'tag3669',
        'tag3667',
        'tag3665',
        'tag3663',
        'tag3658',
        21734,
        21734,
        21734,
        32551,
        32702,
        32713,
        32644,
        32659,
        32631,
        32547,
        32727,
        32549,
        32548,
        32689,
        32520,
        32521,
        32522,
        32523, 32524, 32525, 32526, 32527, 32528, 32529, 32530, 32531, 32532, 32533, 32534, 32535, 32536, 32537, 32538, 32539, 32540, 32541, 32542, 32543, 32544, 32545, 32546, 32550, 32552, 32553, 32554, 32555, 32556, 32557, 32558, 32559, 32560, 32561, 32562, 32563, 32564, 32565, 32566, 32567, 32568, 32569, 32570, 32571, 32572, 32573, 32574, 32575, 32576, 32577, 32578, 32579, 32580, 32581, 32582, 32583, 32584, 32585, 32586, 32587, 32588, 32589, 32590, 32591, 32592, 32593, 32594, 32595, 32596, 32597, 32598, 32599, 32600, 32601, 32602, 32603, 32604, 32605, 32606, 32607, 32608, 32609, 32610, 32611, 32612, 32613, 32614, 32615, 32616, 32617, 32618, 32619, 32620, 32621, 32622, 32623, 32624, 32625, 32626, 32627, 32628, 32629, 32630, 32632, 32633, 32634, 32635, 32636, 32638, 32639, 32640, 32641, 32642, 32643, 32645, 32646, 32647, 32648, 32649, 32650, 32651, 32652, 32653, 32654, 32655, 32656, 32657, 32658, 32660, 32661, 32662, 32663, 32664, 32665, 32666, 32667, 32668, 32669, 32670, 32671, 32672, 32673, 32674, 32675, 32676, 32677, 32678, 32679, 32680, 32681, 32682, 32683, 32684, 32685, 32686, 32687, 32688, 32690, 32691, 32692, 32693, 32694, 32696, 32697, 32698, 32699, 32700, 32701, 32703, 32704, 32705, 32706, 32707, 32708, 32709, 32710, 32711, 32712, 32714, 32715, 32716, 32717, 32718, 32719, 32720, 32721, 32722, 32723, 32724, 32725, 32726, 32728, 32729, 32730, 32731, 32732, 32733, 32734, 32735, 32736, 32737, 32738, 32739, 32740, 32741, 32742, 32743, 32744, 32745, 32746, 32747, 32748, 32749, 32750, 32751, 32752, 32753, 32754, 32755, 32756, 32757, 32758, 32759, 32760, 32761, 32762, 32763, 32764, 32765, 32766, 32767, 32768, 32769, 32770, 32771, 32772, 32773, 32774, 32775, 32776, 32777, 32778, 32779, 32780, 32781, 32782, 32783, 32784, 32785, 32786, 32787, 32788, 32789, 32790, 32791, 32792, 32793, 32794, 32795, 32796, 32797, 32798, 32799, 32800, 32801, 32802, 32803, 32804, 32805, 32806, 32807, 32808, 32809, 32810, 32811, 32812, 32813, 32814, 32815, 32816, 32817, 32818, 32819, 32820, 32821, 32822, 32823, 32824, 32825, 32826, 32827, 32828, 32829, 32830, 32831, 32832, 32833, 32834, 32835, 32836, 32837, 32838, 32839, 32840, 32841, 32842, 32843, 32844, 32845, 32846, 32847, 32848, 32849, 32850, 32851, 32852, 32853, 32854, 32855, 32856, 32857, 32858, 32859, 32860, 32861, 32862, 32863, 32864, 32865, 32866, 32867, 32868, 32869, 32870, 32871, 32872, 32873, 32874, 32875, 32876, 32877, 32878, 32879, 32880, 32881, 32882, 32883, 32884, 32885, 32886, 32887, 32888, 32889, 32890, 32891, 32892, 32893, 32894, 32895, 32896, 32897, 32898, 32899, 32900, 32901, 32902, 32903, 32904, 32905, 32906, 32907, 32908, 32909, 32910, 32911, 32912, 32913, 32914, 32915, 32916, 32917, 32918, 32919, 32920, 32921, 32922, 32923, 32924, 32925, 32926, 32927, 32928, 32929, 32930, 32931, 32932, 32933, 32934, 32935, 32936, 32937, 32938, 32939, 32940, 32941, 32942, 32943, 32944, 32945, 32946, 32947, 32948, 32949, 32950, 32951, 32952, 32953, 32954, 32955, 32956, 32957, 32958, 32959, 32960, 32961, 32962, 32963, 32964, 32965, 32966, 32967, 32968, 32969, 32970, 32971, 32972, 32973, 32974, 32975, 32976, 32977, 32978, 32979, 32980, 32981, 32982, 32983, 32984, 32985, 32986, 32987, 32988, 32989, 32990, 32991, 32992, 32993, 32994, 32995, 32996, 32997, 32998, 32999, 33000, 33001, 33002, 33003, 33004, 33005, 33006, 33007, 33008, 33009, 33010, 33011, 33012, 33013, 33014, 33015, 33016, 33017, 33018, 33019, 33020, 33021, 33022, 33023, 33024, 33025, 33026, 33027, 33028, 33029, 33030, 33031, 33032, 33033, 33034, 33035, 33036, 33037, 33038, 33039, 33040, 33041, 33042, 33043, 33044, 33045, 33046, 33047, 33048, 33049, 33050, 33051, 33052, 33053, 33054, 33055, 33056, 33057, 33058, 33059, 33060, 33061, 33062, 33063, 33064, 33065, 33066, 33067, 33068, 33069, 33070, 33071, 33072, 33073, 33074, 33075, 33076, 33077, 33078, 33079, 33080, 33081, 33082, 33083, 33084, 33085, 33086, 33087, 33088, 33089, 33090, 33091, 33092, 33093, 33094, 33095, 33096, 33097, 33098, 33099, 33100, 33101, 33102, 33103, 33104, 33105, 33106, 33107, 33108, 33109, 33110, 33111, 33112, 33113, 33114, 33115, 33116, 33117, 33118, 33119, 33120, 33121, 33122, 33123, 33124, 33125, 33126, 33127, 33128, 33129, 33130, 33131, 33132, 33133, 33134, 33135, 33136, 33137, 33138, 33139, 33140, 33141, 33142, 33143, 33144, 33145, 33146, 33147, 33148, 33149, 33150, 33151, 33152, 33153, 33154, 33155, 33156, 33157, 33158, 33159, 33160, 33161, 33162, 33163, 33164, 33165, 33166, 33167, 33168, 33169, 33170, 33171, 33172, 33173, 33174, 33175, 33176, 33177, 33178, 33179, 33180, 33181, 33182, 33183, 33184, 33185, 33186, 33187, 33188, 33189, 33190, 33191, 33192, 33193, 33194, 33195, 33196, 33197, 33198, 33199, 33200, 33202, 33203, 33204, 33205, 33206, 33207, 33208, 33209, 33210, 33211, 33212, 33213, 33214, 33215, 33216, 33217, 33218, 33219, 33220, 33221, 33222, 33223, 33224, 33225, 33226, 33227, 33228, 33229, 33230, 33231, 33232, 33233, 33234, 33235, 33236, 33237, 33238, 33239, 33240, 33241, 33242, 33243, 33244, 33245, 33246, 33247, 33248, 33249, 33250, 33251, 33252, 33253, 33254, 33255, 33256, 33257, 33258, 33259, 33260, 33261, 33262, 33263, 33264, 33265, 33266, 33267, 33268, 33269, 33270, 33271, 33272, 33273, 33274, 33275, 33276, 33277, 33278, 33279, 33280, 33281, 33282, 33283, 33284, 33285, 33286, 33287, 33288, 33309, 33310, 33311, 33312, 33313, 33314, 33315, 33316, 33317, 33318, 33319, 33320, 33321, 33322, 33323, 33324, 33325, 33326, 33327, 33328, 33329, 33330, 33331, 33332, 33333, 33334, 33335, 33336, 33337, 33338, 33339, 33340, 33341, 33342, 33343, 33344, 33345, 33346, 33347, 33348, 33349, 32456, 38289, 32457, 32458, 32459, 32460, 32461, 32462, 32463, 32464, 32465, 32466, 32467, 32468, 32469, 32470, 32471, 32472, 32473, 32474, 32475, 32476, 32477, 32478, 32479, 32480, 32481, 32482, 32483, 32484, 32485, 32486, 32487, 32488, 32489, 32490, 32491, 35251, 32695];

    protected Config $zxdbConfig;
    protected Connection|null $zxdb = null;

    protected string $wosLink = 'https://spectrumcomputing.co.uk/pub/';
    protected string $archiveLink = 'https://archive.org/download/World_of_Spectrum_June_2017_Mirror/World%20of%20Spectrum%20June%202017%20Mirror.zip/World%20of%20Spectrum%20June%202017%20Mirror/';
    protected string $nvgLink = 'https://archive.org/download/mirror-ftp-nvg/Mirror_ftp_nvg.zip/';
    protected string $wosFilesPath;
    protected array $releaseFileTypes;
    protected array $releaseTypes;
    protected array $inlayFileTypes;
    protected array $mapFileTypes;
    protected array $infoFileTypes;
    protected array $adFileTypes;
    protected array $allowedCategoryIdsMap;
    protected string $origin = 'zxdb';
    protected array $releasesInfo = [];
    protected array $legalStatuses = [
        'D' => LegalStatus::forbidden,
        'S' => LegalStatus::insales,
        'A' => LegalStatus::unknown,
        '?' => LegalStatus::mia,
        'N' => LegalStatus::unreleased,
        'R' => LegalStatus::recovered,
    ];
    protected array $webRefIds = [
        36, //Modern ZX-Retro Gaming
        56, //SC rzx
        31, //itch.io
    ];
    protected array $minMachines = [
        24 => "atm",
        14 => "pentagon128",
        16 => "samcoupe",
        15 => "scorpion",
        17 => "sinclairql",
        11 => "timex2048",
        12 => "timex2068",
        25 => "zxevolution",
        7 => "zx128+2",
        10 => "zx128+2b",
        8 => "zx128+3",
        5 => "zx128",
        1 => "zx16",
        2 => "zx16",
        3 => "zx48",
        4 => "zx48",
        27 => "zxnext",
        26 => "zxuno",
        18 => "zx80",
        21 => "zx8116",
        19 => "zx811",
        20 => "zx812",
        22 => "zx8132",
        23 => "zx8164",
        32 => "lambda8300",
    ];
    protected array $optionalMachines = [
        9 => "zx128+3",
        13 => "timex2068",
        6 => "zx128",
        4 => "zx128",
        2 => "zx48",
    ];
    protected array $roles = [
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
    protected array $featureGroups = [
        9003 => "cursor",
        9001 => "int2_1",
        9002 => "int2_2",
        9004 => "kempston",
        9006 => "zxpand",
        1025 => "ay",
        1066 => "ay",
        101 => "cheetah",
        102 => "specdrum",
        1021 => "trdos",
        1023 => "ulaplus",
        1065 => "beeper",
        1026 => "beeper",
    ];
    protected array $languages = [
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
        "he" => ["he"],
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

    public function __construct(
        private readonly ProdsService     $prodsService,
        private readonly AuthorsService   $authorsService,
        private readonly CountriesManager $countriesManager,
        private readonly ConfigManager    $configManager,
    )
    {
        $zxdbConfig = $this->configManager->getConfig('zxdb');
        $this->zxdbConfig = $zxdbConfig;
        $this->makeZxdb();

        $this->prodsService->setForceUpdateYoutube(true);
        $this->prodsService->setUpdateExistingProds(true);
        $this->prodsService->setForceUpdateExternalLink(false);
        $this->prodsService->setForceUpdateReleaseType(true);
//        $this->prodsService->setForceUpdateAuthors(true);
//        $this->prodsService->setForceUpdateTitles(true);
//        $this->prodsService->setForceUpdateCategories(true);
//        $this->prodsService->setForceUpdatePublishers(true);
//        $this->prodsService->setForceUpdateGroups(true);
        $this->prodsService->setUpdateExistingReleases(true);
        $this->prodsService->setForceUpdateImages(true);
        $this->prodsService->setAddImages(true);

        $this->authorsService->setForceUpdateCountry(false);
        $this->authorsService->setForceUpdateCity(false);
        $this->authorsService->setForceUpdateGroups(false);

        $this->wosFilesPath = PUBLIC_PATH . 'wosfiles/';
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
        $this->allowedCategoryIdsMap = [
            1 => true,
            2 => true,
            3 => true,
            4 => true,
            5 => true,
            6 => true,
            7 => true,
            8 => true,
            9 => true,
            10 => true,
            11 => true,
            12 => true,
            13 => true,
            14 => true,
            15 => true,
            16 => true,
            17 => true,
            18 => true,
            19 => true,
            20 => true,
            21 => true,
            22 => true,
            23 => true,
            24 => true,
            25 => true,
            26 => true,
            27 => true,
            28 => true,
            29 => true,
            30 => true,
            31 => true,
            32 => true,
            33 => true,
            34 => true,
            35 => true,
            36 => true,
            37 => true,
            38 => true,
            39 => true,
            40 => true,
            41 => true,
            42 => true,
            43 => true,
            44 => true,
            45 => true,
            46 => true,
            47 => true,
            48 => true,
            49 => true,
            50 => true,
            51 => true,
            52 => true,
            53 => true,
            54 => true,
            55 => true,
            56 => true,
            57 => true,
            58 => true,
            59 => true,
            60 => true,
            61 => true,
            62 => true,
            63 => true,
            64 => true,
            65 => true,
            66 => true,
            67 => true,
            68 => true,
            69 => true,
            70 => true,
            71 => true,
            72 => true,
            73 => true,
            74 => true,
            75 => true,
            76 => true,
            77 => true,
            78 => true,
            79 => true,
            80 => true,
            82 => true,
            83 => true,
            110 => true,
            111 => true,
            112 => true,
            113 => true,
            114 => true,
        ];
    }

    private array $skipMachineTypes = [
        28, // jupiter ace
    ];

    public function importAll(): void
    {
        $this->maxTime = time() + 60 * 28;
        if (empty($this->debugEntry) && is_file($this->getStatusPath())) {
            $this->minCounter = (int)file_get_contents($this->getStatusPath());
        }
        $this->importCountries();
        if ($this->importZxdbProds()) {
            $this->importSeries();
        }
    }

    public function importCountries(): void
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

    public function importSeries(): void
    {
        if ($tags = $this->zxdb->table('tags')
            ->where('tagtype_id', 'like', 'S')
            ->select('*')->get()) {

            foreach ($tags as $tag) {
                $prodInfo = [
                    'title' => $tag['name'],
                    'id' => 'tag' . $tag['id'],
                    'categories' => [],
                    'seriesProds' => [],
                ];

                if (in_array($prodInfo['id'], $this->ignoreIds, true)) {
                    continue;
                }

                $records = $this->zxdb->table('members')
                    ->where('tag_id', '=', $tag['id'])
                    ->select('*')->get();

                $entryIds = array_column($records, 'entry_id');

                if ($entries = $this->zxdb->table('entries')
                    ->whereIn('id', $entryIds)
                    ->select('*')
                    ->get()) {
                    foreach ($entries as $entry) {
                        if (isset($this->allowedCategoryIdsMap[$entry['genretype_id']])) {
                            $prodInfo['categories'][] = $entry['genretype_id'];
                            $prodInfo['seriesProds'][] = $entry['id'];
                        }
                    }
                }

                $prodInfo['categories'] = array_unique($prodInfo['categories']);

                $dto = ProdImportDTO::fromArray($prodInfo);
                if ($this->prodsService->importProd($dto, $this->origin)) {
                    $this->counter++;
                    $this->markProgress(
                        'series ' . $this->counter . '/' . count($entries ?? []) .
                        ' imported ' . $prodInfo['id'] . ' ' . $prodInfo['title']
                    );
                } else {
                    $this->markProgress('series failed ' . $prodInfo['id'] . ' ' . $prodInfo['title']);
                }
            }
        }
    }

    public function importZxdbProds(): bool
    {
        if ($entries = $this->zxdb->table('entries')->select('*')->get()) {
            foreach ($entries as $entry) {
                $this->counter++;
                if ($this->counter < $this->minCounter) {
                    continue;
                }
                if (!empty($this->debugEntry) && $entry['id'] !== $this->debugEntry) {
                    continue;
                }
                if (in_array($entry['id'], $this->ignoreIds, true)) {
                    continue;
                }
                if (in_array($entry['machinetype_id'], $this->skipMachineTypes, true)) {
                    continue;
                }

                if (!isset($this->allowedCategoryIdsMap[$entry['genretype_id']])) {
                    file_put_contents($this->getStatusPath(), $this->counter);
                    if (($this->maxCounter && ($this->counter >= $this->maxCounter)) || time() >= $this->maxTime) {
                        return false;
                    }
                    continue;
                }

                $prodInfo = [
                    'title' => $entry['title'],
                    'altTitle' => null,
                    'year' => null,
                    'externalLink' => null,
                    'legalStatus' => null,
                    'id' => $entry['id'],
                    'language' => null,
                    'categories' => [$entry['genretype_id']],
                    'images' => [],
                    'labels' => [],
                    'authors' => [],
                    'publishers' => [],
                    'groups' => [],
                    'releases' => [],
                    'compilationItems' => [],
                    'rzx' => [],
                    'maps' => [],
                ];

                if ($entry['language_id'] && isset($this->languages[$entry['language_id']])) {
                    $prodInfo['language'] = $this->languages[$entry['language_id']] ?? null;
                }

                if (!empty($entry['availabletype_id']) && isset($this->legalStatuses[$entry['availabletype_id']])) {
                    $prodInfo['legalStatus'] = $this->legalStatuses[$entry['availabletype_id']]->name;
                }

                if ($record = $this->zxdb->table('aliases')
                    ->where('entry_id', '=', $entry['id'])
                    ->limit(1)
                    ->first()) {
                    $prodInfo['altTitle'] = $record['title'];
                }

                if ($records = $this->zxdb->table('contents')
                    ->select('entry_id')
                    ->where('container_id', '=', $entry['id'])
                    ->orderBy('prog_seq')
                    ->get()) {
                    foreach ($records as $record) {
                        $prodInfo['compilationItems'][] = $record['entry_id'];
                    }
                }

                if ($publishers = $this->zxdb->table('publishers')
                    ->select('*')
                    ->where('entry_id', '=', $entry['id'])
                    ->where('release_seq', '=', 0)
                    ->orderBy('publisher_seq')
                    ->get()) {
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
                    ->leftJoin('roles',
                        static function ($join) {
                            $join->on('authors.entry_id', '=', 'roles.entry_id')
                                ->on('authors.label_id', '=', 'roles.label_id');
                        }
                    )
                    ->where('authors.entry_id', '=', $entry['id'])
                    ->orderBy('author_seq');

                if ($authors = $query->get()) {
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
                    ->whereIn('website_id', $this->webRefIds)
                    ->get()) {
                    foreach ($rows as $row) {
                        if ($row['website_id'] === 36 || $row['website_id'] === 56) {
                            if ($row['website_id'] !== 56 && !empty($prodInfo['youtubeId'])) {
                                continue;
                            }
                            if (stripos($row['link'], 'https://youtu.be/') === 0) {
                                $prodInfo['youtubeId'] = substr($row['link'], strlen('https://youtu.be/'));
                            }
                        } elseif ($row['website_id'] === 31) {
                            $prodInfo['externalLink'] = $row['link'];
                        }
                    }
                }

                if ($prodInfo['externalLink'] && $prodInfo['legalStatus'] === LegalStatus::forbidden->name) {
                    $prodInfo['legalStatus'] = LegalStatus::insales->name;
                }

                $this->getReleasesInfo($entry);

                foreach ($this->releasesInfo[$entry['id']] as $releaseInfo) {
                    if (!empty($releaseInfo['year']) && (empty($prodInfo['year']) || $releaseInfo['year'] < $prodInfo['year'])) {
                        $prodInfo['year'] = $releaseInfo['year'];
                    }
                }
                //distribute all images across prod object and appropriate releases
                if ($downloads = $this->zxdb->table('downloads')
                    ->select('*')
                    ->where('entry_id', '=', $entry['id'])
                    ->orderBy('release_seq')
                    ->get()) {

                    foreach ($downloads as $download) {
                        $releaseInfo = $this->releasesInfo[$download['entry_id']][$download['release_seq']] ?? null;
                        if ($releaseInfo !== null) {
                            if (in_array($download['filetype_id'], $this->inlayFileTypes, true)) {
                                $releaseInfo['inlayImages'][] = $this->resolveDownloadUrl($download['file_link'], true);
                            } elseif (in_array($download['filetype_id'], $this->infoFileTypes, true)) {
                                $releaseInfo['infoFiles'][] = $this->resolveDownloadUrl($download['file_link']);
                            } elseif (in_array($download['filetype_id'], $this->adFileTypes, true)) {
                                $releaseInfo['adFiles'][] = $this->resolveDownloadUrl($download['file_link']);
                            }
                        }
                        unset($releaseInfo);

                        if ($download['filetype_id'] === self::FILETYPE_LOADING) {
                            $prodInfo['images'][] = $this->resolveDownloadUrl($download['file_link']);
                        } elseif ($download['filetype_id'] === self::FILETYPE_RUNNING) {
                            $isGif = str_contains($download['file_link'], '.gif');
                            if (!$isGif) {
                                $prodInfo['images'][] = $this->resolveDownloadUrl($download['file_link']);
                            }
                        } elseif ($download['filetype_id'] === self::FILETYPE_OPENING) {
                            $prodInfo['images'][] = $this->resolveDownloadUrl($download['file_link']);
                        } elseif ($download['filetype_id'] === self::FILETYPE_RZX) {
                            $prodInfo['rzx'][] = [
                                'url' => $this->resolveDownloadUrl($download['file_link']),
                                'author' => $download['comments'] ?? '',
                            ];
                        } elseif (in_array($download['filetype_id'], $this->mapFileTypes, true)) {
                            $prodInfo['maps'][] = [
                                'url' => $this->resolveDownloadUrl($download['file_link']),
                                'author' => $download['comments'] ?? '',
                            ];
                        }
                    }

                    $unusedReleases = $this->releasesInfo[$entry['id']];

                    foreach ($downloads as $download) {
                        if (!in_array($download['filetype_id'], $this->releaseFileTypes, true)) {
                            continue;
                        }
                        $releaseInfo = $this->releasesInfo[$download['entry_id']][$download['release_seq']] ?? null;
                        if (!$releaseInfo) {
                            continue;
                        }

                        if ($download['language_id'] !== $entry['language_id'] && isset($this->languages[$download['language_id']])) {
                            $releaseInfo['language'] = $this->languages[$download['language_id']] ?? null;
                        }

                        $releaseInfo['fileUrl'] = $this->resolveDownloadUrl($download['file_link'], true);
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
                            unset($releaseInfo['inlayImages'], $releaseInfo['adFiles'], $releaseInfo['infoFiles']);
                        }

                        if ($releaseInfo['releaseType'] === '' && $releaseInfo['release_seq'] === 0) {
                            $releaseInfo['releaseType'] = 'original';
                        }

                        $this->releasesInfo[$download['entry_id']][$download['release_seq']] = $releaseInfo;

                        $prodInfo['releases'][] = $releaseInfo;
                    }

                    foreach ($unusedReleases as $unusedRelease) {
                        $unusedRelease['id'] = md5($unusedRelease['id']);
                        $prodInfo['releases'][] = $unusedRelease;
                    }
                    //some releases dont have downloads, so release type should be determined by release_seq
                    foreach ($prodInfo['releases'] as &$release) {
                        if (empty($release['releaseType'])) {
                            $release['releaseType'] = ($release['release_seq'] === 0) ? 'original' : 'rerelease';
                        }
                        if (empty($release['hardwareRequired']) && isset($this->minMachines[$entry['machinetype_id']])) {
                            $release['hardwareRequired'] = [$this->minMachines[$entry['machinetype_id']]];
                        }
                    }
                    unset($release);
                }

                $dto = ProdImportDTO::fromArray($prodInfo);
                if ($this->prodsService->importProd($dto, $this->origin)) {
                    $this->markProgress(
                        'prod ' . $this->counter . '/' . count($entries) .
                        ' imported ' . $prodInfo['id'] . ' ' . $prodInfo['title']
                    );
                } else {
                    $this->markProgress('prod failed ' . $prodInfo['id'] . ' ' . $prodInfo['title']);
                }

                file_put_contents($this->getStatusPath(), $this->counter);
                if (($this->maxCounter && ($this->counter >= $this->maxCounter)) || time() >= $this->maxTime) {
                    return false;
                }
            }
        }
        return true;
    }

    protected function getReleasesInfo($entry): void
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
                    'releaseType' => null,
                    'year' => $release['release_year'] ?: null,
                    'language' => null,
                    'hardwareRequired' => [],
                    'images' => null,
                    'inlayImages' => [],
                    'infoFiles' => [],
                    'adFiles' => [],
                    'fileUrl' => null,
                    'version' => null,
                    'release_seq' => $release_seq,
                    'publishers' => [],
                    'labels' => [],
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
                    ->orderBy('publisher_seq')
                    ->get()) {
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

    protected function resolveDownloadUrl($url, bool $useArchiveOrg = false): string
    {
        $url = str_ireplace('+', '%2B', $url);
        if (stripos($url, 'archive.org') !== false) {
            return $url;
        }
        if (stripos($url, 'zxdb') !== false) {
            if (!str_starts_with($url, '/')) {
                $url = '/' . $url;
            }
            return 'https://spectrumcomputing.co.uk' . $url;
        }

        if (str_starts_with($url, '/nvg/')) {
            return $this->nvgLink . substr($url, 5);
        }
        if (str_starts_with($url, '/pub/')) {
            $url = substr($url, 5);
        }
        if ($useArchiveOrg) {
            return $this->archiveLink . $url;
        }

        return $this->wosLink . $url;
    }

    /**
     * @param false $isPerson
     */
    protected function gatherLabelsInfo(?array &$infoIndex, $labelId, bool $isGroup = false, bool $isPerson = false, $isAlias = false): array
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

                if (!$infoIndex[$labelId]['isGroup'] && (
                    $this->zxdb->table('authors')
                        ->select('*')
                        ->where('team_id', '=', $labelId)
                        ->limit(1)
                        ->first()
                    )
                ) {
                    $infoIndex[$labelId]['isGroup'] = true;
                }

                if (($label['from_id'] !== null) && ($label['from_id'] !== $label['owner_id'])) {
                    $fromInfo = $this->gatherLabelsInfo(
                        $infoIndex,
                        $label['from_id'],
                        $infoIndex[$labelId]['isGroup'],
                        $infoIndex[$labelId]['isPerson']
                    );
                    if ($fromInfo) {
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
                        if ($teamInfo = $this->gatherLabelsInfo($infoIndex, $row['team_id'], true)) {
                            $infoIndex[$labelId]['groupsData'][] = $teamInfo['id'];
                        }
                    }
                    $infoIndex[$labelId]['isPerson'] = true;
                }
            }
        }
        return $infoIndex[$labelId];
    }

    protected function makeZxdb(): void
    {
        if ($this->zxdb === null) {
            $manager = new Manager();
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

    protected function markProgress(string $text): void
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        $time = sprintf("%.2f", $endTime - $previousTime);
        echo $text . ' ' . $time . '<br/>';
        flush();
        file_put_contents(PUBLIC_PATH . 'import.log', date('H:i') . ' ' . $text . ' ' . $time . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }

    protected function getStatusPath(): string
    {
        return PUBLIC_PATH . 'wos.txt';
    }
}