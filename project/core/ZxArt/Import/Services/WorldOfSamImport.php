<?php

declare(strict_types=1);

namespace ZxArt\Import\Services;

use Dom\HTMLDocument;
use DOMElement;
use errorLogger;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use RuntimeException;
use Throwable;
use ZxArt\Authors\Services\AuthorsService;
use ZxArt\Import\Labels\Label;
use ZxArt\Import\Prods\Dto\ProdImportDTO;
use ZxArt\Import\Prods\Dto\ReleaseImportDTO;
use ZxArt\Prods\LegalStatus;
use ZxArt\Prods\Services\ProdsService;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueStatus;
use ZxArt\Queue\QueueType;
use ZxArt\ZxProdCategories\CategoryIds;

/**
 * Class responsible for importing products from WorldOfSAM.
 *
 * The World of SAM site lists products by letter with optional pagination. Each product page
 * contains a rich set of fields such as category, release year, authors, artists, musicians,
 * publishers, downloads, screenshots, packaging and instructions. This importer crawls the
 * listing pages to gather product URLs and then parses each product page to build
 * ProdImportDTO and ReleaseImportDTO instances which are handed off to the ProdsService for
 * persistence. Only the categories Game, Demo, Utility and Disk Magazine are imported; other
 * categories are ignored. Series with subproducts (e.g. disk magazines) are detected via
 * the “Subproducts” field and their child products are imported first before the parent.
 */
final class WorldOfSamImport extends errorLogger
{
    /**
     * Time limit in seconds for a single import run. Mirrors PouetImport defaults.
     */
    protected int $timeLimit = 60 * 29;

    /**
     * Maximum number of products to import per run. Set to a high value so we
     * effectively import everything in one go when possible.
     */
    protected int $maxCounter = 0;

    /**
     * Tracks how many products have been processed in the current run.
     */
    protected int $counter = 0;

    /**
     * Unix timestamp when the import should stop. Calculated at runtime.
     */
    protected ?int $maxTime = null;

    /**
     * Flag to optionally restrict importing to a single product for debugging.
     */
    protected ?string $debugSlug = null;

    /**
     * HTTP client used to fetch pages. Configured with a reasonable timeout
     * and custom User Agent.
     */
    private readonly Client $http;

    private QueueService $queueService;
    private ProdsService $prodsService;

    /**
     * Map of World of SAM categories to local CategoryIds. Only categories present in
     * this map will be imported. Others are ignored.
     *
     * @var array<string,int>
     */
    private array $categories;

    /**
     * Map of human‑readable role labels on the site to our internal role keys.
     * Only roles listed here are supported; any unknown role will trigger an exception.
     *
     * @var array<string,string|null>
     */
    private array $roleMap;

    private array $ignoreSlugs = ['ascd', 'sam-coupe-diskimage-manager', 'z88dk', 'games-werent'];

    /**
     * Identifier used when storing import provenance. This will be passed into
     * ProdsService::importProd().
     */
    private string $origin = 'worldofsam';
    private array $copyrightMap;
    private WorldOfSamLinksRewriter $worldOfSamLinksRewriter;

    public function __construct(
        ProdsService            $prodsService,
        AuthorsService          $authorsService,
        QueueService            $queueService,
        WorldOfSamLinksRewriter $worldOfSamLinksRewriter,
        array                   $config = []
    )
    {
        $this->worldOfSamLinksRewriter = $worldOfSamLinksRewriter;
        $this->prodsService = $prodsService;
        $this->queueService = $queueService;

        $this->http = new Client([
            'timeout' => 20,
            'connect_timeout' => 10,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:144.0) Gecko/20100101 Firefox/144.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Cache-Control' => 'max-age=0',
            ],
        ]);

        // Force update settings consistent with PouetImport
        $authorsService->setForceUpdateCountry(false);
        $authorsService->setForceUpdateCity(false);
        $this->prodsService->setForceUpdateCategories(false);
        $this->prodsService->setForceUpdateYoutube(true);
        $this->prodsService->setForceUpdateGroups(true);
        $this->prodsService->setForceUpdateAuthors(true);
        $this->prodsService->setAddImages(true);
        $this->prodsService->setForceUpdateImages(true);
        $this->prodsService->setUpdateExistingProds(true);

        // Category mapping: only import Game, Demo, Utility and Disk Magazine
        $this->categories = [
            'game' => CategoryIds::GAMES->value,
            'demo' => CategoryIds::DEMOS->value,
            'utility' => CategoryIds::SYSTEM_SOFTWARE->value,
            'disk magazine' => CategoryIds::PRESS_MAGAZINES->value,
        ];

        // Role mapping for authors/artists/musicians. Publisher handled separately.
        $this->roleMap = [
            'author' => 'code',
            'artist' => 'graphics',
            'musician' => 'music',
            'coder' => 'code',
            'code' => 'code',
        ];
        $this->copyrightMap = [
            'Copyrights Granted' => LegalStatus::allowed,
            'Copyrights Declined' => LegalStatus::forbidden,
            'Public Domain' => LegalStatus::allowed,
        ];
        // Apply overrides from config if provided
        if (isset($config['debugSlug'])) {
            $this->debugSlug = (string)$config['debugSlug'];
        }
    }

    /**
     * Entry point for importing all supported products. This method gathers URLs
     * from the listing pages and processes each product. It respects the time limit
     * and maximum counter to avoid excessively long runs.
     */
    public function importAll(): void
    {
        $this->maxTime = time() + $this->timeLimit;
        $this->counter = 0;

        $productUrls = $this->collectProductUrls();
        foreach ($productUrls as $slug => $url) {
            if (in_array($slug, $this->ignoreSlugs, true)) {
                continue;
            }
            if ($this->debugSlug !== null && $slug !== $this->debugSlug) {
                continue;
            }
            if ($this->maxCounter > 0 && $this->counter >= $this->maxCounter) {
                break;
            }
            if (time() > ($this->maxTime ?? 0)) {
                break;
            }
            try {
                $this->processProduct((string)$slug, $url);
            } catch (Throwable $e) {
                $this->markProgress('ERROR at product ' . $slug . ': ' . $e->getMessage());
                throw $e;
            }
            $this->counter++;
        }
    }

    /**
     * Collect all product URLs from the World of SAM listings. Iterates over letters
     * and paginated pages. Returns an associative array of slug => absolute URL.
     *
     * @return array<string,string>
     */
    private function collectProductUrls(): array
    {
        $letters = [
            '0', '1', '5', '9',
            'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
            'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z'
        ];
        $base = 'https://www.worldofsam.org';
        $urls = [];
        foreach ($letters as $letter) {
            $page = 0;
            $previousCount = -1;
            while (true) {
                $url = $base . '/products/' . $letter;
                if ($page > 0) {
                    $url .= '?page=' . $page;
                }
                $doc = $this->fetchDocument($url);
                if ($doc === null) {
                    break;
                }
                $links = $doc->querySelectorAll('.views-table a[href*="/products/"]');
                $countThisPage = 0;
                foreach ($links as $link) {
                    /** @var DOMElement $link */
                    $href = $link->getAttribute('href');
                    // Skip if not a product link (some anchors may point to /products but not product pages)
                    if (preg_match('#/(?:index\.php/)?products/([^/]+)#i', $href, $m)) {
                        $slug = $m[1];
                        if (!isset($urls[$slug])) {
                            $urls[$slug] = $base . $href;
                            $countThisPage++;
                        }
                    }
                }
                // If no new products found on this page, stop iterating pages for this letter
                if ($countThisPage === 0 || $countThisPage === $previousCount) {
                    break;
                }
                $previousCount = $countThisPage;
                $page++;
                // Avoid infinite loop if pagination structure breaks
                if ($page > 50) {
                    break;
                }
            }
        }
        return $urls;
    }

    /**
     * Process a single product page. Builds DTOs and hands off to the ProdsService.
     *
     * @param string $slug Unique identifier derived from the URL path
     * @param string $url Absolute URL to the product page
     */
    private function processProduct(string $slug, string $url): string|null
    {
        // Respect time limit
        if (time() > ($this->maxTime ?? 0)) {
            return null;
        }
        $doc = $this->fetchDocument($url);
        if ($doc === null) {
            $this->markProgress('Product ' . $slug . ' could not be downloaded');
            return null;
        }

        // Detect and import subproducts before processing parent
        $subproductLinks = $doc->querySelectorAll('div.field--name-field-subproducts a[href*="/products/"]');
        $seriesProdIds = [];
        foreach ($subproductLinks as $sublink) {
            /** @var DOMElement $sublink */
            $href = $sublink->getAttribute('href');
            if (preg_match('#^/(?:index\.php/)?products/([\w\-]+)$#', $href, $m)) {
                $subSlug = $m[1];
                $subUrl = 'https://www.worldofsam.org' . $href;
                $id = $this->processProduct($subSlug, $subUrl);
                if ($id !== null) {
                    $seriesProdIds[] = $id;
                }
            }
        }

        // Title
        $titleEl = $doc->querySelector('h1.node--title');
        $title = $titleEl ? trim($titleEl->textContent ?? '') : $slug;

        // Category
        $categoryEl = $doc->querySelector('div.field-node-field-category span.field__item-wrapper');
        $categoryText = $categoryEl ? trim($categoryEl->textContent ?? '') : '';
        $categoryId = null;
        if ($categoryText !== '') {
            $lower = strtolower($categoryText);
            if (isset($this->categories[$lower])) {
                $categoryId = (int)$this->categories[$lower];
            } else {
                // Unknown category – skip import
                $this->markProgress('Product ' . $slug . ' skipped due to unsupported category ' . $categoryText);
                return null;
            }
        }

        $infoFiles = [];
        $releaseDownloadLinks = [];

        $downloadAnchors = $doc->querySelectorAll('div.field-node--field-download a, div.field-node--field-download-public a');

        foreach ($downloadAnchors as $anchor) {
            /** @var DOMElement $anchor */
            $fileUrl = $anchor->getAttribute('href');
            if (!str_starts_with($fileUrl, 'http')) {
                $fileUrl = 'https://www.worldofsam.org' . $fileUrl;
            }
            $fileName = trim($anchor->textContent);

            // Decide by filename extension only
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION) ?: '');
            if ($extension === 'pdf' || $extension === 'txt') {
                $infoFiles[] = $fileUrl;
                continue;
            }

            $releaseDownloadLinks[] = [
                'url' => $fileUrl,
                'name' => $fileName,
            ];
        }

        // Release year
        $year = null;
        $yearEl = $doc->querySelector('div.field-node--field-release-year div.field__item');
        if ($yearEl) {
            $yearStr = trim($yearEl->textContent ?? '');
            if (is_numeric($yearStr)) {
                $year = (int)$yearStr;
            }
        }

        // Legal status
        $legalStatus = null;
        $copyrightEl = $doc->querySelector('div.field-node--field-copyrights div.field__item');
        if ($copyrightEl) {
            $legalStatusText = trim($copyrightEl->textContent ?? '');
            if (isset($this->copyrightMap[$legalStatusText])) {
                $legalStatus = $this->copyrightMap[$legalStatusText];
            } else {
                throw new RuntimeException('Unknown legal status ' . $legalStatusText . ' for ' . $slug);
            }
        }

        // Description
        $descEl = $doc->querySelector('div.field-node--body div.field__item');
        $description = $descEl ? trim($descEl->innerHTML ?? '') : null;
        if ($description !== null) {
            $description = $this->worldOfSamLinksRewriter->rewriteLinks($description);
        }

        // Instructions
        $instructionsEl = $doc->querySelector('div.field-node--field-instructions div.field__item');
        $instructions = $instructionsEl ? trim($instructionsEl->innerHTML ?? '') : null;
        if ($instructions !== null) {
            $instructions = $this->worldOfSamLinksRewriter->rewriteLinks($instructions);
        }

        // YouTube link
        $youtubeId = null;
        foreach ($doc->querySelectorAll('a') as $a) {
            /** @var DOMElement $a */
            $href = $a->getAttribute('href');
            if (stripos($href, 'youtube.com/watch') !== false) {
                $parts = parse_url($href);
                if (isset($parts['query'])) {
                    parse_str($parts['query'], $qs);
                    if (isset($qs['v'])) {
                        $youtubeId = $qs['v'];
                        break;
                    }
                }
            }
            if (stripos($href, 'youtu.be/') === 0) {
                $youtubeId = substr($href, strlen('https://youtu.be/'));
                break;
            }
        }

        // Extract roles and labels
        $labels = [];
        $authorRoles = [];
        $publishers = [];
        $roleFields = [
            'author' => 'div.field-node-field-author',
            'artist' => 'div.field-node-field-artist',
            'musician' => 'div.field-node-field-musician',
        ];
        foreach ($roleFields as $roleName => $selector) {
            $roleKey = $this->roleMap[$roleName] ?? null;
            if ($roleKey === null) {
                continue;
            }
            $people = $doc->querySelectorAll($selector . ' a[href*="/people/"]');
            foreach ($people as $person) {
                /** @var DOMElement $person */
                $href = $person->getAttribute('href');
                $name = trim($person->textContent);
                $importId = null;
                if (preg_match('#/(?:index\.php/)?people/([\w\-]+)$#', $href, $m)) {
                    $importId = $m[1];
                }
                // Create label
                $label = new Label(
                    id: $importId !== '' ? $importId : null,
                    name: $name !== '' ? $name : null,
                    isAlias: null,
                    isPerson: true,
                    isGroup: false,
                );
                $labels[] = $label;
                if ($importId !== null) {
                    $authorRoles[$importId] = $authorRoles[$importId] ?? [];
                    $authorRoles[$importId][] = $roleKey;
                }
            }
        }
        // Publisher
        $publisherEls = $doc->querySelectorAll('div.field-node-field-publisher a');
        foreach ($publisherEls as $pub) {
            /** @var DOMElement $pub */
            $href = $pub->getAttribute('href');
            $name = trim($pub->textContent);
            $pubId = null;
            if (preg_match('#/(?:index\.php/)?people/([\w\-]+)$#', $href, $m)) {
                $pubId = $m[1];
            }
            $publishers[] = $pubId ?: $name;
            // Add publisher as label as group
            $label = new Label(
                id: $pubId !== '' ? $pubId : null,
                name: $name !== '' ? $name : null,
                isAlias: null,
                isPerson: false,
                isGroup: true,
            );
            $labels[] = $label;
        }

        // Screenshots -> images
        $images = [];
        $titleImages = $doc->querySelectorAll('div.field-node--field-title-image img');
        foreach ($titleImages as $img) {
            /** @var DOMElement $img */
            $href = $img->getAttribute('src');
            if (!str_starts_with($href, 'http')) {
                $href = 'https://www.worldofsam.org' . $href;
            }
            $images[] = $href;
        }
        $ssEls = $doc->querySelectorAll('div.field-node--field-screenshots a');
        foreach ($ssEls as $a) {
            /** @var DOMElement $a */
            $href = $a->getAttribute('href');
            if (!str_starts_with($href, 'http')) {
                $href = 'https://www.worldofsam.org' . $href;
            }
            $images[] = $href;
        }

        // Packaging/inlay images -> inlayImages
        $inlayImages = [];
        $packEls = $doc->querySelectorAll('div.field-node--field-packaging a');
        foreach ($packEls as $a) {
            /** @var DOMElement $a */
            $href = $a->getAttribute('href');
            if (!str_starts_with($href, 'http')) {
                $href = 'https://www.worldofsam.org' . $href;
            }
            $inlayImages[] = $href;
        }
        // Some products may use the generic field name without double hyphen
        if (empty($inlayImages)) {
            $packEls = $doc->querySelectorAll('div.field-node-field-packaging a');
            foreach ($packEls as $a) {
                /** @var DOMElement $a */
                $href = $a->getAttribute('href');
                if (!str_starts_with($href, 'http')) {
                    $href = 'https://www.worldofsam.org' . $href;
                }
                $inlayImages[] = $href;
            }
        }
        $hardwareRequired = ['samcoupe'];

        // Build releases
        $releases = [];
        $releaseIndex = 0;
        foreach ($releaseDownloadLinks as $dl) {
            $releaseId = $slug;
            if ($releaseIndex > 0) {
                $releaseId .= '-' . $releaseIndex;
            }

            $releaseType = 'original';
            if ($categoryId !== $this->categories['demo'] && str_contains($title, 'demo')) {
                $releaseType = 'demo';
            }

            $releases[] = new ReleaseImportDTO(
                id: $releaseId,
                title: $title,
                year: $year,
                languages: null,
                version: null,
                releaseType: $releaseType,
                filePath: null,
                fileUrl: $dl['url'],
                fileName: $dl['name'],
                hardwareRequired: $hardwareRequired,
                labels: null,
                authors: null,
                publishers: null,
                undetermined: null,
                images: null,
                inlayImages: null,
                infoFiles: $releaseIndex === 0 && $infoFiles !== [] ? $infoFiles : null,
                adFiles: null,
                md5: null,
            );

            $releaseIndex++;
        }

        if ($releases === [] && $seriesProdIds === []) {
            if (str_contains('MIA', $description)) {
                $legalStatus = 'mia';
            }

            $releases[] = new ReleaseImportDTO(
                id: $slug . '-unknown',
                title: $title,
                releaseType: 'unknown',
                hardwareRequired: $hardwareRequired,
                infoFiles: $infoFiles !== [] ? $infoFiles : null,
            );
        }

        // Build product DTO
        $prodDto = new ProdImportDTO(
            id: $slug,
            title: $title,
            altTitle: null,
            description: $description,
            htmlDescription: true,
            instructions: $instructions ?: null,
            languages: ["en"],
            legalStatus: $legalStatus,
            youtubeId: $youtubeId,
            externalLink: null,
            compo: null,
            year: $year,
            ids: null,
            importIds: null,
            labels: $labels ?: null,
            authorRoles: $authorRoles ?: null,
            groups: null,
            publishers: $publishers ?: null,
            undetermined: null,
            party: null,
            directCategories: $categoryId !== null ? [$categoryId] : null,
            categories: null,
            images: $images ?: null,
            maps: null,
            inlayImages: $inlayImages ?: null,
            rzx: null,
            compilationItems: null,
            seriesProdIds: $seriesProdIds ?: null,
            articles: null,
            releases: $releases ?: null,
            origin: $this->origin
        );

        // Persist via prodsService
        if ($prod = $this->prodsService->importProd($prodDto, $this->origin)) {
            $prod->executeAction('resize');
            // Skip AI categories tagging in queue
            $this->queueService->updateStatus($prod->getPersistedId(), QueueType::AI_CATEGORIES_TAGS, QueueStatus::STATUS_SKIP);
            $this->markProgress('Product ' . $slug . ' imported');
        }

        return $prodDto->id;
    }

    /**
     * Fetch and parse a URL into a DOM\HTMLDocument using the querySelector API. Returns
     * null on error. Exceptions are caught and logged.
     *
     * @param string $url Absolute URL to fetch
     */
    private function fetchDocument(string $url): ?HTMLDocument
    {
        try {
            $response = $this->http->get($url);
            $html = (string)$response->getBody();
            // Use the DOM living standard Document class for CSS selectors
            // Suppress warnings from malformed HTML
            return HTMLDocument::createFromString($html);
        } catch (GuzzleException $e) {
            $this->markProgress('Failed to fetch ' . $url . ': ' . $e->getMessage());
            return null;
        }
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
}