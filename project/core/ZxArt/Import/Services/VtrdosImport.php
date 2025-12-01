<?php
declare(strict_types=1);

namespace ZxArt\Import\Services;

use DOMDocument;
use DOMNode;
use DOMElement;
use DOMXPath;
use DOMNameSpaceNode;
use errorLogger;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\ZxProdCategories\CategoryIds;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Import\Labels\Label;

class VtrdosImport extends errorLogger
{
    protected int $counter = 0;
    protected int $maxCounter = 3;

    /** @var array<string, array<int, array<string, mixed>>> */
    protected array $urlsSettings = [];
    protected string $origin = 'vt';
    protected string $rootUrl = 'https://vtrd.in/';
    /** @var string[] */
    protected array $alphabet = [
        '123',
        'a',
        'b',
        'c',
        'd',
        'e',
        'f',
        'g',
        'h',
        'i',
        'j',
        'k',
        'l',
        'm',
        'n',
        'o',
        'p',
        'q',
        'r',
        's',
        't',
        'u',
        'v',
        'w',
        'x',
        'y',
        'z',
    ];
    /** @var array<string,string> */
    protected array $hwIndex = [
        '(DMA UltraSound Card)' => 'dmausc',
        '(km)' => 'kempstonmouse',
        '(kemp8bit)' => 'kempston8b',
        '(cvx)' => 'covoxfb',
        '(dma)' => 'dmausc',
        '(gs)' => 'gs',
        '(sd)' => 'soundrive',
        '(for 48k)' => 'zx48',
        '(48k only)' => 'zx48',
        '(128k only)' => 'zx128',
        '(1024k)' => 'pentagon1024',
        '(256k)' => 'scorpion',
        '(ts)' => 'ts',
        '48k' => 'zx48',
        'Pentagon 512k' => 'pentagon512',
        'Pentagon 1024k' => 'pentagon1024',
        'Pentagon 1024SL' => 'pentagon1024',
        'Scorpion ZS 256' => 'scorpion',
        'Byte' => 'byte',
        'smuc' => 'smuc',
        'SMUC' => 'smuc',
        'Sprinter' => 'sprinter',
        'Covox' => 'covoxfb',
        'General Sound' => 'gs',
        'Cache' => 'Cache',
        'SounDrive' => 'soundrive',
        'Turbo Sound' => 'ts',
        'TurboSound FM' => 'tsfm',
        'ZXM-MoonSound' => 'zxm',
        'AY' => 'ay',
        'DMA UltraSound Card' => 'dmausc',
        'DMA USC' => 'dmausc',
        'Beeper' => 'beeper',
        'AY Mouse' => 'aymouse',
        'CP/M' => 'cpm',
        '(for Profi)' => 'profi',
        '(Base Conf)' => 'baseconf',
        '(TS Conf)' => 'tsconf',
        'ATM Turbo 2' => 'atm2',
        'neoGS-SD' => 'sdneogs',
        'NEMO-HDD' => 'nemoide',
        'Nemo HDD' => 'nemoide',
        'ZSD' => 'zcontroller',
        'iS-DOS' => 'isdos',
        'TASiS' => 'tasis',
        'CD-ROM' => 'cd',
        'CD' => 'cd',
        'GMX' => 'gmx',
    ];

    /** @var array<string,string> */
    protected array $categoryHardware = [
        'Инфа и утилиты для TurboSound FM:' => 'tsfm',
        'Инфа, игры, утилиты для DMA Ultrasound Card:' => 'dmausc',
        'Софт для Profi компьютера:' => 'profi',
        'Дисковая операционная система CP/M:' => 'cpm',
        'Дисковая система iS-DOS от питерской IskraSoft:' => 'isdos',
        'Информационные сборники Евгения Илясова (под iS-DOS):' => 'isdos',
        'Анекдоты, юмор (под iS-DOS):' => 'isdos',
    ];
    /** @var array<string,int> */
    protected array $categories = [
        'Архиваторы:' => 244886,
        'Ассемблеры:' => 92552,
        'Буты:' => 204150,
        'Графические Редакторы:' => 244860,
        'Графические Утилиты:' => 92587,
        'Дебаггеры:' => 244863,
        'Дисковые утилиты:' => 244864,
        'Записные книжки:' => 244865,
        'Каталогизаторы:' => 92569,
        'Командеры:' => 92187,
        'Конвертеры Экранов:' => 244866,
        'Копир MS-DOS - TR-DOS:' => 244867,
        'Копировщики:' => 244869,
        'Копировщики Disk -> Tape:' => 244868,
        'Музыкальные Плейеры:' => 244870,
        'Музыкальные редакторы для AY:' => 244871,
        'Музыкальные редакторы для beeper:' => 244872,
        'Музыкальные Утилиты для работы с AY-звуком:' => 244873,
        'Музыкальные утилиты для работы с цифровым звуком:' => 244874,
        'Операционные системы:' => 244875,
        'Программаторы:' => 244876,
        'Просмотрщики графики:' => 244862,
        'Просмотрщики текстов:' => 244877,
        'Прошивки ПЗУ:' => 244878,
        'Работа с сетями:' => 202587,
        'Разное:' => 92590,
        'Редакторы звуков:' => 244881,
        'Редакторы игр:' => 202586,
        'Редакторы Шрифтов:' => 92573,
        'Системные тесты:' => 244882,
        'Спрайтовые Редакторы:' => 244883,
        'Текстовые редакторы:' => 244884,
        'Универсальные просмотрщики:' => 244885,
        'Упаковщики данных:' => 244887,
        'Упаковщики экранов:' => 244888,
        'Утилиты для защиты данных:' => 244890,
        'Утилиты для работы с текстом:' => 244889,
        'Утилиты для снятия защиты:' => 244891,
        'Цифровые музыкальные редакторы:' => 244892,
        'Языки программирования:' => 244893,
        'Различные тексты:' => 92181,
        'Статьи и правила конкурсов:' => 92181,
        'Сборники разной музыки:' => 92175,
        'Инфа и утилиты для TurboSound FM:' => 244871,
        'Инфа, игры, утилиты для DMA Ultrasound Card:' => 244871,
        'Схемы и девайсы:' => 92181,
        'Дисковая операционная система CP/M:' => 244875,
        'Софт для Profi компьютера:' => 92183,
        'Софт для клона ZX-Evolution:' => 92177,
        'Софт для ATM Turbo компьютера:' => 92177,
        'Дисковая система iS-DOS от питерской IskraSoft:' => 92183,
        'Информационные сборники Евгения Илясова (под iS-DOS):' => 92181,
        'Анекдоты, юмор (под iS-DOS):' => 92181,
        'Сборники игрушек, не вписывающиеся в мои таблицы:' => 202590,
        'Пакетное создание прессы на ZX:' => 418662,
        'Графика, гифты и поздравления:' => 92172,
        'Хиромантия, гадания и прочее:' => 92582,
        'Различные психологические тесты:' => 418663,
        'Разнообразный софт:' => CategoryIds::MISC->value,
        'Game // exUSSR' => 92177,
        'Game // English' => 92177,
        'Game // Demo version' => 92177,
        'Game // Translated' => 92177,
        'GS' => 92177,
        'Demo Party' => 315126,
    ];

    public function __construct(
        private readonly ProdsService $prodsService,
    )
    {
        $this->prodsService->setUpdateExistingReleases(true);

//        $this->urlsSettings['https://vtrd.in/press.php?l=1'] = [
//            [
//                'type' => 'press',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/press.php?l=2'] = [
//            [
//                'type' => 'press',
//            ],
//        ];
        $this->urlsSettings['https://vtrd.in/updates.php'] = [
            [
                'type' => 'updates',
            ],
        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=translat'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'language' => ['ru'],
//                    'releaseType' => 'localization',
//                    'authorRoles' => ['localization', 'release'],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=full_ver'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=demo_ver'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'demoversion',
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/system.php'] = [
//            [],
//            [
//                'type' => 'system',
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/sbor.php'] = [
//            [],
//            [
//                'type' => 'sbor',
//                'release' => [
//                    'hardwareRequired' => [],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/gs.php'] = [
//            [],
//            [
//                'type' => 'list',
//                'prod' => [
//                    'directCategories' => [92581],
//                ],
//                'release' => [
//                    'hardwareRequired' => ['gs'],
//                ],
//            ],
//            [],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'hardwareRequired' => ['gs'],
//                ],
//            ],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'hardwareRequired' => ['gs'],
//                    'authorRoles' => ['sfx', 'release'],
//                ],
//            ],
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'hardwareRequired' => ['gs'],
//                    'authorRoles' => ['intro_code', 'release'],
//                ],
//            ],
//        ];
//        $this->urlsSettings['https://vtrd.in/games.php?t=remix'] = [
//            [
//                'type' => 'table',
//                'prod' => [
//                    'directCategories' => [92177],
//                ],
//                'release' => [
//                    'releaseType' => 'mod',
//                    'authorRoles' => ['adaptation', 'release'],
//                ],
//            ],
//        ];
        foreach ($this->alphabet as $item) {
            $this->urlsSettings['https://vtrd.in/games.php?t=' . $item] = [
                [
                    'type' => 'table',
                    'prod' => [
                        'directCategories' => [92177],
                    ],
                    'release' => [
                        'releaseType' => 'adaptation',
                        'authorRoles' => ['adaptation', 'release'],
                    ],
                ],
            ];
        }
    }

    public function importAll(): void
    {
        foreach ($this->urlsSettings as $url => $settings) {
            $this->importUrlProds($url, $settings);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $allSettings
     */
    public function importUrlProds(string $pageUrl, array $allSettings): void
    {
        if ($html = $this->loadHtml($pageUrl)) {
            $xPath = new DOMXPath($html);
            $tableNodes = $xPath->query("//table");
            foreach ($tableNodes as $tableNode) {
                $settings = array_shift($allSettings);
                if (is_array($settings) && isset($settings['type']) && $settings['type'] !== '') {
                    if ($settings['type'] === 'list') {
                        $this->parseList($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'system') {
                        $this->parseSystem($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'sbor') {
                        $this->parseSbor($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'table') {
                        $this->parseTable($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'updates') {
                        $this->parseUpdates($tableNode, $xPath, $settings);
                    } elseif ($settings['type'] === 'press') {
                        $this->parsePress($tableNode, $xPath);
                    }
                }
            }
        }
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    /**
     * @param array<string, mixed> $settings
     */
    protected function parseTable(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath, array $settings): void
    {
        /** @var array<string, ProdImportDTO> $prodsIndex */
        $prodsIndex = [];
        $releaseNodes = $xPath->query(".//tr", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                $tdNodes = $releaseNode->getElementsByTagName('td');
                if ($td1 = $tdNodes->item(0)) {
                    // Initialize clear variables
                    $url = null;
                    $prodTitle = null;
                    $detailsUrl = null;
                    $aNode = null;

                    // Extract links from the first column
                    $aNodes = $td1->getElementsByTagName('a');
                    foreach ($aNodes as $aNodeItem) {
                        if (!$aNodeItem instanceof DOMElement) {
                            continue;
                        }
                        $classAttr = $aNodeItem->getAttribute('class');
                        $href = $aNodeItem->getAttribute('href');
                        if (!str_contains($classAttr, 'details')) {
                            // Main link with title
                            if ($href !== '') {
                                $url = $href;
                            }
                            $prodTitle = $this->processTitle((string)$aNodeItem->textContent);
                            $aNode = $aNodeItem;
                        } else if ($href !== '') {
                            $detailsUrl = $this->rootUrl . $href;
                        }
                    }

                    // Fallback: if details link not found in the first column, try column 5
                    if ($detailsUrl === null && $td5 = $tdNodes->item(4)) {
                        $aNodes5 = $td5->getElementsByTagName('a');
                        foreach ($aNodes5 as $aNodeItem) {
                            if ($aNodeItem instanceof DOMElement) {
                                $href = $aNodeItem->getAttribute('href');
                                if ($href !== '') {
                                    $detailsUrl = $this->rootUrl . $href;
                                    break;
                                }
                            }
                        }
                    }

                    if ($url !== null && $prodTitle !== null) {
                        // gather from columns
                        $prodYear = null;
                        $labelObjects = [];
                        $groupIds = [];
                        $publisherIds = [];
                        $undetermined = [];
                        $parsedReleaseType = null;
                        if ($td2 = $tdNodes->item(1)) {
                            $this->extractAuthorsFromPlainString((string)$td2->nodeValue, [], $prodYear, $labelObjects, $groupIds, $publisherIds, $undetermined, $parsedReleaseType);
                        }
                        $roles = [];
                        if (isset($settings['release']['authorRoles'])) {
                            $roles = (array)$settings['release']['authorRoles'];
                        }
                        $roles[] = 'release';
                        // release-level authors (publishers)
                        $releasePublishers = [];
                        $releaseUndetermined = [];
                        if ($td3 = $tdNodes->item(2)) {
                            $tmpLabels = [];
                            $tmpGroups = [];
                            $tmpPublishers = [];
                            $tmpUndet = [];
                            $this->extractAuthorsFromPlainString((string)$td3->nodeValue, $roles, $prodYear, $tmpLabels, $tmpGroups, $tmpPublishers, $tmpUndet, $releaseType);
                            $releasePublishers = $tmpPublishers;
                            $releaseUndetermined = $tmpUndet;
                            // also merge in product labels if new
                            foreach ($tmpLabels as $l) {
                                $ids = array_map(static fn(Label $x) => ($x->id ?? ''), $labelObjects);
                                if (!in_array($l->id ?? '', $ids, true)) {
                                    $labelObjects[] = $l;
                                }
                            }
                        }

                        // settings overrides
                        $languages = !empty($settings['release']['language']) ? (array)$settings['release']['language'] : null;
                        $releaseType = !empty($settings['release']['releaseType']) ? (string)$settings['release']['releaseType'] : null;
                        $hardwareRequired = !empty($settings['release']['hardwareRequired']) ? (array)$settings['release']['hardwareRequired'] : [];

                        // anchor info
                        if ($aNode instanceof DOMElement) {
                            $anchorTitle = $aNode->textContent ?? '';
                            // hardware and tags from anchor text
                            foreach ($this->hwIndex as $k => $v) {
                                if (stripos($anchorTitle, $k) !== false) {
                                    if (str_contains($k, '(')) {
                                        $anchorTitle = str_ireplace($k, '', $anchorTitle);
                                    }
                                    $hardwareRequired[] = $v;
                                }
                            }
                            if (stripos($anchorTitle, '(dsk)') !== false) {
                                $anchorTitle = str_ireplace('(dsk)', '', $anchorTitle);
                                $releaseType = $releaseType ?? 'adaptation';
                            }
                            if (stripos($anchorTitle, '(mod)') !== false) {
                                $anchorTitle = str_ireplace('(mod)', '', $anchorTitle);
                                $releaseType = $releaseType ?? 'mod';
                            }
                            if (stripos($anchorTitle, '(rus)') !== false) {
                                $anchorTitle = str_ireplace('(rus)', '', $anchorTitle);
                                $languages = $languages ?? ['ru'];
                            }
                            if (stripos($anchorTitle, '(ita)') !== false) {
                                $anchorTitle = str_ireplace('(ita)', '', $anchorTitle);
                                $languages = $languages ?? ['it'];
                            }
                            if (stripos($anchorTitle, '(pol)') !== false) {
                                $anchorTitle = str_ireplace('(pol)', '', $anchorTitle);
                                $languages = $languages ?? ['pl'];
                            }
                            if (stripos($anchorTitle, '(eng)') !== false) {
                                $anchorTitle = str_ireplace('(eng)', '', $anchorTitle);
                                $languages = $languages ?? ['en'];
                            }
                            if (stripos($anchorTitle, '(ukr)') !== false) {
                                $anchorTitle = str_ireplace('(ukr)', '', $anchorTitle);
                                $languages = $languages ?? ['ua'];
                            }
                        }

                        // Details page for fileUrl and description
                        $fileUrl = $this->rootUrl . $url;
                        $description = null;
                        if (is_string($detailsUrl)) {
                            [$detFileUrl, $detDescription, $detHw] = $this->getReleaseDetails($detailsUrl);
                            if ($detFileUrl !== null) {
                                $fileUrl = $detFileUrl;
                            }
                            if ($detDescription !== null) {
                                $description = $detDescription;
                            }
                            if (!empty($detHw)) {
                                foreach ($detHw as $hw) {
                                    if (!in_array($hw, $hardwareRequired, true)) {
                                        $hardwareRequired[] = $hw;
                                    }
                                }
                            }
                        }

                        $fileName = basename($fileUrl);
                        $releaseId = md5($fileName);

                        $releaseDto = new ReleaseImportDTO(
                            id: $releaseId,
                            title: $prodTitle,
                            year: $prodYear,
                            languages: $languages,
                            releaseType: $releaseType,
                            fileUrl: $fileUrl,
                            description: $description,
                            hardwareRequired: empty($hardwareRequired) ? null : array_values(array_unique($hardwareRequired)),
                            labels: empty($labelObjects) ? null : $labelObjects,
                            publishers: empty($releasePublishers) ? null : array_values(array_unique($releasePublishers)),
                            undetermined: empty($releaseUndetermined) ? null : $releaseUndetermined,
                        );

                        $prodId = md5($prodTitle . $prodYear);
                        $directCategories = isset($settings['prod']['directCategories']) ? (array)$settings['prod']['directCategories'] : null;

                        if (!isset($prodsIndex[$prodId])) {
                            $prodDto = new ProdImportDTO(
                                id: $prodId,
                                title: $prodTitle,
                                year: $prodYear,
                                labels: empty($labelObjects) ? null : $labelObjects,
                                groups: empty($groupIds) ? null : array_values(array_unique($groupIds)),
                                publishers: empty($publisherIds) ? null : array_values(array_unique($publisherIds)),
                                undetermined: empty($undetermined) ? null : $undetermined,
                                directCategories: $directCategories !== null ? array_map('intval', $directCategories) : null,
                                releases: [$releaseDto],
                            );
                            $prodsIndex[$prodId] = $prodDto;
                        } else {
                            $existing = $prodsIndex[$prodId];
                            $newReleases = $existing->releases ?? [];
                            $newReleases[] = $releaseDto;
                            $prodsIndex[$prodId] = new ProdImportDTO(
                                id: $existing->id,
                                title: $existing->title,
                                altTitle: $existing->altTitle,
                                description: $existing->description,
                                htmlDescription: $existing->htmlDescription,
                                instructions: $existing->instructions,
                                languages: $existing->languages,
                                legalStatus: $existing->legalStatus,
                                youtubeId: $existing->youtubeId,
                                externalLink: $existing->externalLink,
                                compo: $existing->compo,
                                year: $existing->year,
                                ids: $existing->ids,
                                importIds: $existing->importIds,
                                labels: $existing->labels,
                                authorRoles: $existing->authorRoles,
                                groups: $existing->groups,
                                publishers: $existing->publishers,
                                undetermined: $existing->undetermined,
                                party: $existing->party,
                                directCategories: $existing->directCategories,
                                categories: $existing->categories,
                                images: $existing->images,
                                maps: $existing->maps,
                                inlayImages: $existing->inlayImages,
                                rzx: $existing->rzx,
                                compilationItems: $existing->compilationItems,
                                seriesProdIds: $existing->seriesProdIds,
                                articles: $existing->articles,
                                releases: $newReleases,
                                origin: $existing->origin,
                            );
                        }
                    }
                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    protected function parsePress(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath): void
    {
        /** @var array<string, ProdImportDTO> $prodsIndex */
        $prodsIndex = [];
        $rowNodes = $xPath->query(".//tr", $node);
        if ($rowNodes->length > 0) {
            foreach ($rowNodes as $rowNode) {
                $tdNodes = $rowNode->getElementsByTagName('td');
                if ($td1 = $tdNodes->item(0)) {
                    $pressTitle = '';

                    if ($bNodes = $td1->getElementsByTagName('b')) {
                        foreach ($bNodes as $bNode) {
                            $pressTitle = trim($bNode->textContent);
                        }
                    }
                    if ($pressTitle !== '' && ($td2 = $tdNodes->item(1)) && $aNodes = $td2->getElementsByTagName('a')) {
                        $seriesProdsIds = [];
                        $directCategories = $aNodes->length > 20 ? [92182] : [92179];
                        foreach ($aNodes as $aNode) {
                            if (str_contains($aNode->getAttribute('class'), 'rpad')) {
                                continue;
                            }
                            $href = (string)$aNode->getAttribute('href');
                            if ($href === '') {
                                continue;
                            }
                            $issueNo = trim((string)$aNode->textContent);
                            $prodTitle = $this->processTitle($pressTitle . ' #' . $issueNo);
                            $prodId = md5($pressTitle . ' #' . $issueNo);

                            $fileUrl = $this->rootUrl . $href;
                            $releaseId = md5(basename($href));
                            $releaseDto = new ReleaseImportDTO(
                                id: $releaseId,
                                title: $prodTitle,
                                languages: ['ru'],
                                releaseType: 'original',
                                fileUrl: $fileUrl,
                                hardwareRequired: ['pentagon128'],
                            );

                            if (!isset($prodsIndex[$prodId])) {
                                $prodDto = new ProdImportDTO(
                                    id: $prodId,
                                    title: $prodTitle,
                                    directCategories: array_map('intval', $directCategories),
                                    releases: [$releaseDto],
                                );
                                $prodsIndex[$prodId] = $prodDto;
                            } else {
                                $existing = $prodsIndex[$prodId];
                                $newReleases = $existing->releases ?? [];
                                $newReleases[] = $releaseDto;
                                $prodsIndex[$prodId] = new ProdImportDTO(
                                    id: $existing->id,
                                    title: $existing->title,
                                    altTitle: $existing->altTitle,
                                    description: $existing->description,
                                    htmlDescription: $existing->htmlDescription,
                                    instructions: $existing->instructions,
                                    languages: $existing->languages,
                                    legalStatus: $existing->legalStatus,
                                    youtubeId: $existing->youtubeId,
                                    externalLink: $existing->externalLink,
                                    compo: $existing->compo,
                                    year: $existing->year,
                                    ids: $existing->ids,
                                    importIds: $existing->importIds,
                                    labels: $existing->labels,
                                    authorRoles: $existing->authorRoles,
                                    groups: $existing->groups,
                                    publishers: $existing->publishers,
                                    undetermined: $existing->undetermined,
                                    party: $existing->party,
                                    directCategories: $existing->directCategories,
                                    categories: $existing->categories,
                                    images: $existing->images,
                                    maps: $existing->maps,
                                    inlayImages: $existing->inlayImages,
                                    rzx: $existing->rzx,
                                    compilationItems: $existing->compilationItems,
                                    seriesProdIds: $existing->seriesProdIds,
                                    articles: $existing->articles,
                                    releases: $newReleases,
                                    origin: $existing->origin,
                                );
                            }
                            $seriesProdsIds[] = $prodId;
                        }
                        if (count($seriesProdsIds) > 1) {
                            $seriesProdId = md5($pressTitle);
                            $prodsIndex[$seriesProdId] = new ProdImportDTO(
                                id: $seriesProdId,
                                title: $pressTitle,
                                directCategories: array_map('intval', $directCategories),
                                seriesProdIds: array_values($seriesProdsIds),
                            );
                        }
                    }

                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    /**
     * @param array<string, mixed> $settings
     */
    protected function parseUpdates(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath, array $settings): void
    {
        /** @var array<string, ProdImportDTO> $prodsIndex */
        $prodsIndex = [];
        $releaseNodes = $xPath->query(".//tr", $node);
        if ($releaseNodes->length > 0) {
            foreach ($releaseNodes as $releaseNode) {
                if (count($prodsIndex) > $this->maxCounter) {
                    break;
                }
                $tdNodes = $releaseNode->getElementsByTagName('td');
                if ($td2 = $tdNodes->item(1)) {
                    $detailsUrl = false;
                    $prodTitle = false;
                    $currentCategory = null;

                    if ($aNodes = $td2->getElementsByTagName('a')) {
                        foreach ($aNodes as $aNode) {
                            if (!str_contains($aNode->getAttribute('class'), 'details')) {
                                $detailsUrl = $this->rootUrl . $aNode->getAttribute('href');
                                $prodTitle = $this->processTitle($aNode->textContent);
                                break;
                            }
                        }
                    }
                    if ($td3 = $tdNodes->item(2)) {
                        $categoryName = trim($td3->textContent);
                        if (isset($this->categories[$categoryName])) {
                            $currentCategory = [$this->categories[$categoryName]];
                        }
                        $nameVar = $categoryName . ':';
                        if (isset($this->categories[$nameVar])) {
                            $currentCategory = [$this->categories[$nameVar]];
                        }
                    }
                    if (is_string($detailsUrl) && $prodTitle !== false && $currentCategory === null) {
                        $this->markProgress('Category parsing failed: ' . $prodTitle . ' ' . $detailsUrl);
                        continue;
                    }
                    if (is_string($detailsUrl) && $prodTitle !== false && !empty($currentCategory)) {
                        $prodYear = null;
                        $labelObjects = [];
                        $groupIds = [];
                        $publisherIds = [];
                        $undetermined = [];
                        if ($td4 = $tdNodes->item(3)) {
                            $this->extractAuthorsFromPlainString((string)$td4->nodeValue, [], $prodYear, $labelObjects, $groupIds, $publisherIds, $undetermined, $releaseType);
                        }
                        $roles = [];
                        if (isset($settings['release']['authorRoles'])) {
                            $roles = (array)$settings['release']['authorRoles'];
                        }
                        $roles[] = 'release';

                        $releasePublishers = [];
                        $releaseUndetermined = [];
                        if ($td5 = $tdNodes->item(4)) {
                            $tmpLabels = [];
                            $tmpGroups = [];
                            $tmpPublishers = [];
                            $tmpUndet = [];
                            $this->extractAuthorsFromPlainString((string)$td5->nodeValue, $roles, $prodYear, $tmpLabels, $tmpGroups, $tmpPublishers, $tmpUndet, $releaseType);
                            $releasePublishers = $tmpPublishers;
                            $releaseUndetermined = $tmpUndet;
                            foreach ($tmpLabels as $l) {
                                $ids = array_map(static fn(Label $x) => ($x->id ?? ''), $labelObjects);
                                if (!in_array($l->id ?? '', $ids, true)) {
                                    $labelObjects[] = $l;
                                }
                            }
                        }

                        // Details
                        [$fileUrl, $description, $hw] = $this->getReleaseDetails($detailsUrl);
                        $hardwareRequired = $hw;

                        // apply overrides
                        $languages = !empty($settings['release']['language']) ? (array)$settings['release']['language'] : null;
                        $releaseType = !empty($settings['release']['releaseType']) ? (string)$settings['release']['releaseType'] : null;
                        if (!empty($settings['release']['hardwareRequired'])) {
                            foreach ((array)$settings['release']['hardwareRequired'] as $hw) {
                                if (!in_array($hw, $hardwareRequired, true)) {
                                    $hardwareRequired[] = $hw;
                                }
                            }
                        }

                        $releaseId = md5(basename($detailsUrl));

                        $releaseDto = new ReleaseImportDTO(
                            id: $releaseId,
                            title: $prodTitle,
                            year: $prodYear,
                            languages: $languages,
                            releaseType: $releaseType,
                            fileUrl: $fileUrl,
                            description: $description,
                            hardwareRequired: empty($hardwareRequired) ? null : array_values(array_unique($hardwareRequired)),
                            labels: empty($labelObjects) ? null : $labelObjects,
                            publishers: empty($releasePublishers) ? null : array_values(array_unique($releasePublishers)),
                            undetermined: empty($releaseUndetermined) ? null : $releaseUndetermined,
                        );

                        $prodId = md5(($prodTitle) . $prodYear);
                        $directCategories = $currentCategory;
                        if (isset($settings['prod']['directCategories'])) {
                            $directCategories = (array)$settings['prod']['directCategories'];
                        }

                        if (!isset($prodsIndex[$prodId])) {
                            $prodDto = new ProdImportDTO(
                                id: $prodId,
                                title: $prodTitle,
                                year: $prodYear,
                                labels: empty($labelObjects) ? null : $labelObjects,
                                groups: empty($groupIds) ? null : array_values(array_unique($groupIds)),
                                publishers: empty($publisherIds) ? null : array_values(array_unique($publisherIds)),
                                undetermined: empty($undetermined) ? null : $undetermined,
                                directCategories: empty($directCategories) ? null : array_map('intval', $directCategories),
                                releases: [$releaseDto],
                            );
                            $prodsIndex[$prodId] = $prodDto;
                        } else {
                            $existing = $prodsIndex[$prodId];
                            $newReleases = $existing->releases ?? [];
                            $newReleases[] = $releaseDto;
                            $prodsIndex[$prodId] = new ProdImportDTO(
                                id: $existing->id,
                                title: $existing->title,
                                altTitle: $existing->altTitle,
                                description: $existing->description,
                                htmlDescription: $existing->htmlDescription,
                                instructions: $existing->instructions,
                                languages: $existing->languages,
                                legalStatus: $existing->legalStatus,
                                youtubeId: $existing->youtubeId,
                                externalLink: $existing->externalLink,
                                compo: $existing->compo,
                                year: $existing->year,
                                ids: $existing->ids,
                                importIds: $existing->importIds,
                                labels: $existing->labels,
                                authorRoles: $existing->authorRoles,
                                groups: $existing->groups,
                                publishers: $existing->publishers,
                                undetermined: $existing->undetermined,
                                party: $existing->party,
                                directCategories: $existing->directCategories,
                                categories: $existing->categories,
                                images: $existing->images,
                                maps: $existing->maps,
                                inlayImages: $existing->inlayImages,
                                rzx: $existing->rzx,
                                compilationItems: $existing->compilationItems,
                                seriesProdIds: $existing->seriesProdIds,
                                articles: $existing->articles,
                                releases: $newReleases,
                                origin: $existing->origin,
                            );
                        }
                    }
                }
            }
            $this->importProdsIndex($prodsIndex);
        }
    }

    /**
     * Fetch release details and return structured data to avoid array-shapes.
     * @return array{0:?string,1:?string,2:string[]} [fileUrl, description, hardwareRequired]
     */
    private function getReleaseDetails(string $detailsUrl): array
    {
        $fileUrl = null;
        $description = null;
        $hardwareRequired = [];
        if ($html = $this->loadHtml($detailsUrl)) {
            $xPath = new DOMXPath($html);
            $aNodes = $xPath->query("//a[contains(@style, 'font-size:20px')]");
            if ($aNode = $aNodes->item(0)) {
                $fileUrl = $this->rootUrl . $aNode->getAttribute('href');
            }
            $divNodes = $xPath->query("//div[@class='details']");
            if ($divNode = $divNodes->item(0)) {
                $raw = (string)$html->saveHTML($divNode);
                // parse hardware markers
                foreach ($this->hwIndex as $key => $value) {
                    if (stripos($raw, $key) !== false) {
                        $hardwareRequired[] = $value;
                    }
                }
                $raw = trim(str_ireplace(['<div class="details">', '</div>'], ['', ''], $raw));
                if ($raw !== '') {
                    $description = $raw;
                }
            }
        }
        return [$fileUrl, $description, array_values(array_unique($hardwareRequired))];
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    /**
     * @param array<string, mixed> $settings
     */
    protected function parseSystem(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath, array $settings): void
    {
        $prodsIndex = [];
        $divNodes = $xPath->query(".//tr/td[@colspan='3']/div[@align='center']", $node);
        if ($divNodes->length === 1) {
            foreach ($divNodes as $divNode) {
                $currentCategory = $settings['prod']['directCategories'] ?? [];

                foreach ($divNode->childNodes as $childNode) {
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        if (strtolower($childNode->tagName) === 'p') {
                            $aNodes = $xPath->query(".//b/font/a", $childNode);
                            if ($aNodes->length > 0) {
                                $aNode = $aNodes->item(0);
                                $name = trim($aNode?->textContent);
                                if (isset($this->categories[$name])) {
                                    $currentCategory = [$this->categories[$name]];
                                }
                            }
                        }
                        if (!empty($currentCategory)) {
                            $liNodes = $xPath->query(".//li[@class='padding']", $childNode);
                            if ($liNodes->length > 0) {
                                foreach ($liNodes as $liNode) {
                                    $aNode = false;
                                    $textNode = false;
                                    foreach ($liNode->childNodes as $contentNode) {
                                        if ($contentNode->nodeType === XML_ELEMENT_NODE && strtolower($contentNode->tagName) === 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                                            $aNode = $contentNode;
                                        }
                                        if ($aNode !== false && substr(trim($contentNode->textContent), 0, 2) === 'by') {
                                            $textNode = $contentNode;
                                        }

                                        if ($aNode instanceof DOMElement && $textNode instanceof DOMNode) {
                                            $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                                            $aNode = false;
                                            $textNode = false;
                                        }
                                    }
                                }
                                $currentCategory = [];
                            }
                        }
                    }
                }
            }
        }
        $this->importProdsIndex($prodsIndex);
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    /**
     * @param array<string, mixed> $settings
     */
    protected function parseSbor(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath, array $settings): void
    {
        $prodsIndex = [];
        $divNodes = $xPath->query(".//tr/td[@colspan='3']/div[@align='center']", $node);
        if ($divNodes->length === 1) {
            foreach ($divNodes as $divNode) {
                $currentCategory = $settings['prod']['directCategories'] ?? [];

                foreach ($divNode->childNodes as $childNode) {
                    if ($childNode->nodeType === XML_ELEMENT_NODE) {
                        if (strtolower($childNode->tagName) === 'p') {
                            $aNodes = $xPath->query(".//b/font", $childNode);
                            if ($aNodes->length > 0) {
                                $aNode = $aNodes->item(0);
                                $name = trim($aNode?->textContent);
                                if (isset($this->categories[$name])) {
                                    $currentCategory = [$this->categories[$name]];
                                }
                                if (isset($this->categoryHardware[$name])) {
                                    $settings['release']['hardwareRequired'] = [$this->categoryHardware[$name]];
                                } else {
                                    $settings['release']['hardwareRequired'] = [];
                                }
                            }
                        }
                        if (!empty($currentCategory)) {
                            $liNodes = $xPath->query(".//li", $childNode);
                            if ($liNodes->length > 0) {
                                foreach ($liNodes as $liNode) {
                                    $aNode = false;
                                    $textNode = false;
                                    foreach ($liNode->childNodes as $contentNode) {
                                        if ($contentNode->nodeType === XML_ELEMENT_NODE && strtolower($contentNode->tagName) === 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                                            $aNode = $contentNode;
                                        }
                                        if ($aNode !== false && substr(trim($contentNode->textContent), 0, 2) === 'by') {
                                            $textNode = $contentNode;
                                        }

                                        if ($aNode instanceof DOMElement && $textNode instanceof DOMNode) {
                                            $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                                            $aNode = false;
                                            $textNode = false;
                                        }
                                    }
                                }
                                $currentCategory = [];
                            }
                        }
                    }
                }
            }
        }
        $this->importProdsIndex($prodsIndex);
    }

    /**
     * @param DOMNameSpaceNode|DOMNode $node
     * @param DOMXPath $xPath
     * @param $settings
     */
    /**
     * @param array<string, mixed> $settings
     */
    protected function parseList(DOMNode|DOMNameSpaceNode $node, DOMXPath $xPath, array $settings): void
    {
        /** @var array<string, ProdImportDTO> $prodsIndex */
        $prodsIndex = [];
        $currentCategory = $settings['prod']['directCategories'] ?? [];

        $liNodes = $xPath->query(".//li[@class='padding']", $node);
        if ($liNodes->length > 0) {
            foreach ($liNodes as $liNode) {
                $aNode = false;
                $textNode = false;
                foreach ($liNode->childNodes as $contentNode) {
                    if ($contentNode->nodeType === XML_ELEMENT_NODE && strtolower($contentNode->tagName) === 'a' && !str_contains($contentNode->getAttribute('class'), 'details')) {
                        $aNode = $contentNode;
                    }
                    if ($aNode !== false && substr(trim($contentNode->textContent), 0, 2) === 'by') {
                        $textNode = $contentNode;
                    }

                    if ($aNode instanceof DOMElement && $textNode instanceof DOMNode) {
                        $this->parseListItemRelease($aNode, $textNode, $currentCategory, $settings, $prodsIndex);

                        $aNode = false;
                        $textNode = false;
                    }
                }
            }
        }

        $this->importProdsIndex($prodsIndex);
    }

    /**
     * Build DTOs directly for list-like entries, avoiding intermediate array shapes.
     *
     * @param array<int> $currentCategory
     * @param array<string,mixed> $settings
     * @param array<string, ProdImportDTO> $prodsIndex
     */
    private function parseListItemRelease(DOMElement $aNode, DOMNode $textNode, array $currentCategory, array $settings, array &$prodsIndex): void
    {
        // Gather release data from anchor
        $fileUrl = $this->rootUrl . $aNode->getAttribute('href');
        $rawTitle = $aNode->textContent;

        $languages = null; // string[]|null
        $hardwareRequired = [];
        $releaseType = null;
        $version = null;

        $workTitle = $rawTitle;
        foreach ($this->hwIndex as $key => $value) {
            if (stripos($workTitle, $key) !== false) {
                if (str_contains($key, '(')) {
                    $workTitle = str_ireplace($key, '', $workTitle);
                }
                $hardwareRequired[] = $value;
            }
        }
        if (stripos($workTitle, '(dsk)') !== false) {
            $workTitle = str_ireplace('(dsk)', '', $workTitle);
            $releaseType = 'adaptation';
        }
        if (stripos($workTitle, '(mod)') !== false) {
            $workTitle = str_ireplace('(mod)', '', $workTitle);
            $releaseType = 'mod';
        }
        if (stripos($workTitle, '(rus)') !== false) {
            $workTitle = str_ireplace('(rus)', '', $workTitle);
            $languages = ['ru'];
        }
        if (stripos($workTitle, '(ita)') !== false) {
            $workTitle = str_ireplace('(ita)', '', $workTitle);
            $languages = ['it'];
        }
        if (stripos($workTitle, '(pol)') !== false) {
            $workTitle = str_ireplace('(pol)', '', $workTitle);
            $languages = ['pl'];
        }
        if (stripos($workTitle, '(eng)') !== false) {
            $workTitle = str_ireplace('(eng)', '', $workTitle);
            $languages = ['en'];
        }
        if (stripos($workTitle, '(ukr)') !== false) {
            $workTitle = str_ireplace('(ukr)', '', $workTitle);
            $languages = ['ua'];
        }
        if (preg_match('#(v[0-9]\.[0-9])#i', $workTitle, $matches, PREG_OFFSET_CAPTURE)) {
            $offset = (int)$matches[0][1];
            $version = substr($workTitle, $offset + 1);
            $workTitle = trim(substr($workTitle, 0, $offset));
        }
        $releaseTitle = $this->processTitle($workTitle);

        // Apply overrides from settings
        if (!empty($settings['release']['language'])) {
            $languages = (array)$settings['release']['language'];
        }
        if (!empty($settings['release']['releaseType'])) {
            $releaseType = (string)$settings['release']['releaseType'];
        }
        if (!empty($settings['release']['hardwareRequired'])) {
            $hardwareRequired = (array)$settings['release']['hardwareRequired'];
        }

        if ($fileUrl === '' || $releaseTitle === '') {
            return;
        }

        $releaseId = md5(basename($fileUrl));

        // Authors/groups/year extraction from text node
        $roles = [];
        if (!empty($settings['release']['authorRoles'])) {
            $roles = (array)$settings['release']['authorRoles'];
        }

        $prodYear = null; // int|null
        $labelObjects = [];
        $groupIds = [];
        $publisherIds = [];
        $undetermined = [];
        $parsedReleaseType = null;
        $this->extractAuthorsFromTextNode($textNode, $roles, $prodYear, $labelObjects, $groupIds, $publisherIds, $undetermined, $parsedReleaseType);

        // Product identity
        $prodTitle = $releaseTitle;
        if (strtolower(substr($prodTitle, -4)) === 'demo') {
            $prodTitle = trim(mb_substr($prodTitle, 0, -4));
        }
        $prodId = md5($prodTitle . $prodYear);

        // Categories
        $directCategories = $currentCategory;
        if (!empty($settings['prod']['directCategories'])) {
            $directCategories = (array)$settings['prod']['directCategories'];
        }

        // Build Release DTO
        $releaseDto = new ReleaseImportDTO(
            id: $releaseId,
            title: $releaseTitle,
            year: $prodYear,
            languages: $languages,
            version: $version,
            releaseType: $releaseType,
            fileUrl: $fileUrl,
            hardwareRequired: empty($hardwareRequired) ? null : array_values(array_unique($hardwareRequired)),
            labels: empty($labelObjects) ? null : $labelObjects,
            publishers: empty($publisherIds) ? null : array_values(array_unique($publisherIds)),
            undetermined: empty($undetermined) ? null : $undetermined,
        );

        // Upsert product DTO and append release
        if (!isset($prodsIndex[$prodId])) {
            $prodDto = new ProdImportDTO(
                id: $prodId,
                title: $prodTitle,
                year: $prodYear,
                labels: empty($labelObjects) ? null : $labelObjects,
                groups: empty($groupIds) ? null : array_values(array_unique($groupIds)),
                publishers: empty($publisherIds) ? null : array_values(array_unique($publisherIds)),
                undetermined: empty($undetermined) ? null : $undetermined,
                directCategories: empty($directCategories) ? null : array_map('intval', $directCategories),
                releases: [$releaseDto],
            );
            $prodsIndex[$prodId] = $prodDto;
        } else {
            $existing = $prodsIndex[$prodId];
            $newReleases = $existing->releases ?? [];
            $newReleases[] = $releaseDto;
            $prodsIndex[$prodId] = new ProdImportDTO(
                id: $existing->id,
                title: $existing->title,
                altTitle: $existing->altTitle,
                description: $existing->description,
                htmlDescription: $existing->htmlDescription,
                instructions: $existing->instructions,
                languages: $existing->languages,
                legalStatus: $existing->legalStatus,
                youtubeId: $existing->youtubeId,
                externalLink: $existing->externalLink,
                compo: $existing->compo,
                year: $existing->year,
                ids: $existing->ids,
                importIds: $existing->importIds,
                labels: $existing->labels,
                authorRoles: $existing->authorRoles,
                groups: $existing->groups,
                publishers: $existing->publishers,
                undetermined: $existing->undetermined,
                party: $existing->party,
                directCategories: $existing->directCategories,
                categories: $existing->categories,
                images: $existing->images,
                maps: $existing->maps,
                inlayImages: $existing->inlayImages,
                rzx: $existing->rzx,
                compilationItems: $existing->compilationItems,
                seriesProdIds: $existing->seriesProdIds,
                articles: $existing->articles,
                releases: $newReleases,
                origin: $existing->origin,
            );
        }
    }

    /**
     * Extract authors/groups/year from a text node into typed variables and value objects.
     *
     * @param string[] $roles
     * @param Label[] $labelsOut
     * @param string[] $groupsOut
     * @param string[] $publishersOut
     * @param array<string,string[]> $undeterminedOut
     */
    private function extractAuthorsFromTextNode(
        DOMNode $node,
        array   $roles,
        ?int    &$prodYearOut,
        array   &$labelsOut,
        array   &$groupsOut,
        array   &$publishersOut,
        array   &$undeterminedOut,
        ?string &$releaseTypeOut = null
    ): void
    {
        $prodYearOut = null;
        $labelsOut = [];
        $groupsOut = [];
        $publishersOut = [];
        $undeterminedOut = [];

        $text = trim($node->textContent);
        if (stripos($text, 'by ') === 0) {
            $text = substr($text, 3);
        }
        $authorsPart = $text;
        if (str_contains($text, ' - ')) {
            [$authorsPart] = explode(' - ', $text, 2);
        }

        if ($authorsPart === 'author') {
            $releaseTypeOut = 'original';
            return;
        }

        $authorsPart = trim(preg_replace('!\s+!', ' ', $authorsPart), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));
        if ($authorsPart === '' || $authorsPart === 'n/a') {
            return;
        }

        $parts = explode(',', $authorsPart);
        foreach ($parts as $part) {
            $name = trim($part);
            if (preg_match("#'([0-9]+)#", $name, $matches)) {
                $digits = trim($matches[1] ?? '');
                if ($digits !== '') {
                    if (strlen($digits) === 2) {
                        $yy = (int)$digits;
                        $prodYearOut = $yy > 50 ? (int)('19' . $digits) : (int)('20' . $digits);
                    } elseif (strlen($digits) === 4) {
                        $prodYearOut = (int)$digits;
                    }
                }
                $name = trim(preg_replace("#('[0-9]+)#", '', $name));
            }

            if (stripos($name, '/') !== false) {
                [$authorName, $groupName] = array_map('trim', explode('/', $name, 2));
                $name = $authorName;
                if ($groupName !== '') {
                    $groupsOut[] = $groupName;
                }
            }

            // Deduplicate by id
            $existingIds = array_map(static fn(Label $l) => ($l->id ?? ''), $labelsOut);
            if (!in_array($name, $existingIds, true)) {
                $labelsOut[] = new Label(id: $name, name: $name);
            }
        }

        // Heuristic: last label as publisher if not in groups
        if (count($labelsOut) > 1) {
            $last = $labelsOut[count($labelsOut) - 1];
            if (!in_array($last->id ?? '', $groupsOut, true)) {
                $publishersOut[] = $last->id ?? '';
            }
        }

        foreach ($labelsOut as $label) {
            if ($label->id === null) {
                continue;
            }
            $undeterminedOut[$label->id] = $roles;
        }
    }

    /**
     * Same as extractAuthorsFromTextNode but accepts a plain string instead of DOMNode.
     *
     * @param string[] $roles
     * @param Label[] $labelsOut
     * @param string[] $groupsOut
     * @param string[] $publishersOut
     * @param array<string,string[]> $undeterminedOut
     */
    private function extractAuthorsFromPlainString(
        string  $text,
        array   $roles,
        ?int    &$prodYearOut,
        array   &$labelsOut,
        array   &$groupsOut,
        array   &$publishersOut,
        array   &$undeterminedOut,
        ?string &$releaseTypeOut = null,
    ): void
    {
        $prodYearOut = null;
        $labelsOut = [];
        $groupsOut = [];
        $publishersOut = [];
        $undeterminedOut = [];

        $text = trim($text);
        if (strtolower(substr($text, 0, 3)) === 'by ') {
            $text = substr($text, 3);
        }
        $authorsPart = $text;
        if (str_contains($text, ' - ')) {
            [$authorsPart] = explode(' - ', $text, 2);
        }
        $authorsPart = trim(preg_replace('!\s+!', ' ', $authorsPart), " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0));

        if ($authorsPart === 'author') {
            $releaseTypeOut = 'original';
            return;
        }

        if ($authorsPart === '' || $authorsPart === 'n/a') {
            return;
        }
        $parts = explode(',', $authorsPart);
        foreach ($parts as $part) {
            $name = trim($part);
            if (preg_match("#'([0-9]+)#", $name, $matches)) {
                $digits = trim($matches[1] ?? '');
                if ($digits !== '') {
                    if (strlen($digits) === 2) {
                        $yy = (int)$digits;
                        $prodYearOut = $yy > 50 ? (int)('19' . $digits) : (int)('20' . $digits);
                    } elseif (strlen($digits) === 4) {
                        $prodYearOut = (int)$digits;
                    }
                }
                $name = trim(preg_replace("#('\d+)#", '', $name));
            }

            if (strpos($name, '/') !== false) {
                [$authorName, $groupName] = array_map('trim', explode('/', $name, 2));
                $name = $authorName;
                if ($groupName !== '') {
                    $groupsOut[] = $groupName;
                }
            }

            $existingIds = array_map(static fn(Label $l) => ($l->id ?? ''), $labelsOut);
            if (!in_array($name, $existingIds, true)) {
                $labelsOut[] = new Label(id: $name, name: $name);
            }
        }

        if (count($labelsOut) > 1) {
            $last = $labelsOut[count($labelsOut) - 1];
            if (!in_array($last->id ?? '', $groupsOut, true)) {
                $publishersOut[] = $last->id ?? '';
            }
        }

        foreach ($labelsOut as $label) {
            if ($label->id === null) {
                continue;
            }
            $undeterminedOut[$label->id] = $roles;
        }
    }

    private function processTitle(string $text): string
    {
        //remove (..)
        $text = preg_replace('#([(].*[)])*#', '', $text);
        //remove double spaces
        $text = trim(
            preg_replace('!\s+!', ' ', $text),
            " \t\n\r\0\x0B" . chr(0xC2) . chr(0xA0)
        );
        if (strtolower(substr($text, -4)) === 'demo') {
            $text = trim(mb_substr($text, 0, -4));
        }
        return $text;
    }

    protected function loadHtml(
        string $url
    ): DOMDocument|false
    {
        if ($contents = file_get_contents($url)) {
            $dom = new DOMDocument();
            $dom->encoding = 'UTF-8';
            $dom->recover = true;
            $dom->substituteEntities = true;
            $dom->strictErrorChecking = false;
            $dom->formatOutput = false;
            @$dom->loadHTML('<?xml version="1.0" encoding="UTF-8"?>' . $contents);
            $dom->normalizeDocument();

            $this->markProgress('html loaded ' . $url);

            return $dom;
        }
        return false;
    }

    protected function markProgress(
        string $text
    ): void
    {
        static $previousTime;

        if ($previousTime === null) {
            $previousTime = microtime(true);
        }
        $endTime = microtime(true);
        echo $text . ' ' . sprintf("%.2f", $endTime - $previousTime) . '<br/>';
        flush();
        file_put_contents(PUBLIC_PATH . 'import.log', date('H:i') . ' ' . $text . "\n", FILE_APPEND);
        $previousTime = $endTime;
    }

    /**
     * @param array<string, ProdImportDTO|array<string,mixed>> $prodsIndex
     */
    /**
     * @param array<string, ProdImportDTO> $prodsIndex
     */
    protected function importProdsIndex(array $prodsIndex): void
    {
        foreach ($prodsIndex as $key => $prodInfo) {
            $this->counter++;
            if ($this->counter > $this->maxCounter) {
                exit;
            }
            $dto = $prodInfo; // already DTO
            $title = $dto->title ?? '';
            if ($this->prodsService->importProd($dto, $this->origin)) {
                $this->markProgress('prod ' . $this->counter . '/' . $key . ' imported ' . $title);
            } else {
                $this->markProgress('prod failed ' . $title);
            }
        }
    }
}