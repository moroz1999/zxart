<?php

use App\Logging\EventsLog;
use App\Paths\PathsManager;
use PicturesModesManager;
use tagsManager;
use ZxArt\LinkTypes;
use ZxArt\Queue\QueueService;
use ZxArt\Queue\QueueType;
use ZxArt\ZxScreen\ZxPictureCacheManager;
use ZxArt\ZxScreen\ZxPictureFlickeringHelper;
use ZxArt\ZxScreen\ZxPictureParametersDto;
use ZxArt\ZxScreen\ZxPictureUrlHelper;

/**
 * Class zxPictureElement
 *
 * @property string $title
 * @property float $votes
 * @property int $votesAmount
 * @property int $commentsAmount
 * @property string $year
 * @property int $party
 * @property int $partyplace
 * @property string $compo
 * @property string $image
 * @property string $originalName
 * @property string $palette
 * @property string $type
 * @property int $rotation
 * @property int $border
 * @property pressArticleElement[] $mentions
 */
class zxPictureElement extends ZxArtItem implements OpenGraphDataProviderInterface
{
    use PaletteTypesProvider;
    use GraphicsCompoProvider;
    use ZxPictureTypesProvider;
    use CrawlerFilterTrait;

    const string TAG_LOADINGSCREEN = "Loading Screen";
    const string TAG_GAMEGRAPHICS = "Game graphics";

    public $dataResourceName = 'module_zxpicture';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    /**
     * @var int|mixed|null
     */
    protected $viewName = 'short';
    protected $votesType = 'zxPicture';
    protected $authorLinkType = 'authorPicture';
    protected $partyLinkType = 'partyPicture';
    protected $sectionType = 'graphics';
    protected $imageName;
    protected $bestAuthorsPictures;
    protected $metaTitle;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['description'] = 'pre';
        $moduleStructure['border'] = 'text';
        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['originalAuthor'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'naturalNumber';
        $moduleStructure['votes'] = 'floatNumber';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'fileName';
        $moduleStructure['inspired'] = 'image';
        $moduleStructure['inspiredName'] = 'fileName';
        $moduleStructure['inspired2'] = 'image';
        $moduleStructure['inspired2Name'] = 'fileName';
        $moduleStructure['sequence'] = 'image';
        $moduleStructure['sequenceName'] = 'fileName';
        $moduleStructure['exeFile'] = 'file';
        $moduleStructure['exeFileName'] = 'fileName';
        $moduleStructure['dateAdded'] = 'date';
        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['tagsAmount'] = 'text';
        $moduleStructure['votesAmount'] = 'text';
        $moduleStructure['commentsAmount'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['views'] = 'text';
        $moduleStructure['rotation'] = 'text';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';
        $moduleStructure['artCityId'] = 'text';
        $moduleStructure['palette'] = 'text';
        $moduleStructure['mentions'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PICTURES->value,
                'role' => 'parent',
            ],
        ];
    }

    /**
     * @return (float|int|mixed|string)[]
     *
     * @psalm-return array{id: int, title: string, link: mixed, votes: float, userVote: mixed, votePercent: mixed,...}
     */
    public function getElementData()
    {
        // generic
        $data["id"] = $this->getId();
        $data["title"] = $this->title;
        $data["link"] = $this->URL;
        $data["votes"] = $this->votes;
        $data["userVote"] = $this->getUserVote();
        $data["votePercent"] = $this->getVotePercent();
        return $data;
    }

    public function getImageUrl(int $zoom = 1, $border = null): string
    {
        return ZxPictureUrlHelper::getUrl(
            controller::getInstance()->baseURL,
            $this->getZxPictureParameters($zoom, $border)
        );
    }

    public function getDownloadUrl(int $zoom = 1): string
    {
        return ZxPictureUrlHelper::getUrl(
            controller::getInstance()->baseURL,
            $this->getZxPictureParameters($zoom, true, 'zximagesdownload')
        );
    }

    public function deleteCachedImage(): void
    {
        ZxPictureCacheManager::deleteCache((int)$this->id);
    }

    private function getZxPictureParameters(int $zoom = 1, $border = null, ?string $controller = null): ZxPictureParametersDto
    {
        $picturesModesManager = $this->getService(PicturesModesManager::class);
        return new ZxPictureParametersDto(
            type: $this->type === 'standard' && $picturesModesManager->getHidden() ? 'hidden' : $this->type,
            zoom: $zoom,
            id: (int)$this->image,
            border: ($border === null || $border) && $picturesModesManager->getBorder() ? $this->border : null,
            rotation: $this->rotation > 0 ? (int)$this->rotation : null,
            mode: $picturesModesManager->getMode(),
            palette: $this->getPalette(),
            hidden: $this->type === 'standard' && $picturesModesManager->getHidden(),
            controller: $controller,
        );
    }

    public function getPalette(): string
    {
        if ($this->palette !== '') {
            return $this->palette;
        }

        foreach ($this->getRealAuthorsList() as $author) {
            $authorPalette = $author->getPalette();
            if ($authorPalette !== null && $authorPalette !== '') {
                return $authorPalette;
            }
            break;
        }

        return 'srgb';
    }

    /**
     * @return false|string
     */
    public function getImageContents(): string|false
    {
        $zxImageConverter = new \ZxImage\Converter();
        $zxImageConverter->setType($this->type);
        $zxImageConverter->setPath($this->getOriginalPath());
        $zxImageConverter->setGigascreenMode('flicker');
        $zxImageConverter->setBorder($this->border);
        $zxImageConverter->setZoom(1);
        $zxImageConverter->setRotation($this->rotation);
        $contents = false;
        if ($zxImageConverter->generateCacheFile()) {
            $contents = file_get_contents($zxImageConverter->getCacheFileName());
        }
        return $contents;
    }

    /**
     * @return false|string
     */
    public function getOriginalPath(): string|false
    {
        if ($this->image) {
            return $this->getService(PathsManager::class)->getPath('uploads') . $this->image;
        }
        return false;
    }

    /**
     * @return string
     */
    public function getFileExtension($extensionType)
    {
        $extension = '';
        if ($extensionType === 'original') {
            $type = $this->type;
            if ($type === 'standard' || $type === 'monochrome') {
                $extension = '.scr';
            } elseif ($type === 'gigascreen') {
                $extension = '.img';
            } elseif ($type === 'flash') {
                $extension = '.flash.scr';
            } elseif ($type === 'tricolor') {
                $extension = '.3';
            } elseif ($type === 'multicolor') {
                $extension = '.ifl';
            } elseif ($type === 'multicolor4') {
                $extension = '.ifl';
            } elseif ($type === 'attributes') {
                $extension = '.atr';
            } elseif ($type === 'lowresgs') {
                $extension = '.hlr';
            } elseif ($type === 'chr$') {
                $extension = '.ch$';
            } elseif ($type === 'timex81') {
                $extension = '.scr';
            } elseif ($type === 'timexhr') {
                $extension = '.scr';
            } elseif ($type === 'timexhrg') {
                $extension = '.hrg';
            } elseif ($type === 'sam4') {
                $extension = '.ss4';
            } elseif ($type === 'ulaplus') {
                $extension = '.ulaplus.scr';
            } elseif ($type === 'zxevo') {
                $extension = '.bmp';
            } elseif ($type === 'stellar') {
                $extension = '.stl';
            } else {
                $extension = '.' . $type;
            }
        } elseif ($extensionType === 'exe') {
            if ($this->exeFileName) {
                $info = pathinfo($this->exeFileName);
                $extension = "." . strtolower($info['extension']);
            }
        } elseif ($extensionType === 'image') {
            $extension = '';
        }
        return $extension;
    }

    /**
     * @return bool
     */
    protected function fileExists($extensionType)
    {
        if ($extensionType === 'original') {
            if ($this->originalName) {
                return true;
            }
        } elseif ($extensionType === 'exe') {
            if ($this->exeFileName) {
                return true;
            }
        } elseif ($extensionType === 'image') {
            if ($this->image) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        $translationsManager = $this->getService('translationsManager');

        $authorNames = $this->getAuthorNames();

        $this->metaTitle = $translationsManager->getTranslationByName("titles.picture") ?? '';
        $this->metaTitle = str_ireplace('%t', $this->title, $this->metaTitle);

        if ($authorNames) {
            $this->metaTitle = str_ireplace('%a', implode(", ", $authorNames), $this->metaTitle);
        }
        return $this->metaTitle;
    }

    /**
     * @return string
     */
    public function getTextContent()
    {
        $translationsManager = $this->getService('translationsManager');

        $textContent = $translationsManager->getTranslationByName("descriptions.picture") ?? '';
        $textContent = str_ireplace('%t', $this->title, $textContent);

        if ($authorNames = $this->getAuthorNames()) {
            $textContent = str_ireplace('%a', implode(", ", $authorNames), $textContent);
        }

        if ($partyElement = $this->getPartyElement()) {
            $textContent = str_ireplace('%p', $partyElement->title, $textContent);
        } else {
            $textContent = str_ireplace('%p', "", $textContent);
        }

        if ($this->getReleaseElement()) {
            $textContent = str_ireplace('%g', $this->getReleaseElement()->title, $textContent);
        } else {
            $textContent = str_ireplace('%g', $this->release ?? '', $textContent);
        }

        if ($this->year) {
            $textContent = str_ireplace('%y', $this->year, $textContent);
        } else {
            $textContent = str_ireplace('%y', "", $textContent);
        }

        if ($tagsTexts = $this->getTagsTexts()) {
            $textContent .= ". " . implode(", ", $tagsTexts);
        }

        return $textContent;
    }


    public function isFlickering(): bool
    {
        return ZxPictureFlickeringHelper::isFlickering($this->type);
    }

    public function logView(): void
    {
        if (!$this->isCrawlerDetected()) {
            $this->views++;
            $this->getService(EventsLog::class)->logEvent($this->getId(), 'view');
            $collection = persistableCollection::getInstance($this->dataResourceName);
            $collection->updateData(['views' => $this->views], ['id' => $this->getId()]);

            $structureManager = $this->getService('structureManager');
            $structureManager->clearElementCache($this->getId());
        }
    }

    /**
     * @return void
     */
    public function checkGameTag()
    {
        $gameTags = [13843, 46245, 13983, 47883, 46237];
        if ($tags = $this->getTagsList()) {
            foreach ($tags as $tagElement) {
                if (in_array($tagElement->getId(), $gameTags)) {
                    return;
                }
            }
        }
        if ($game = $this->getReleaseElement()) {
            if ($gameMaterials = $game->getMaterialsList()) {
                $number = 0;
                foreach ($gameMaterials as $element) {
                    if ($element->structureType === 'zxPicture') {
                        if ($element->getId() == $this->getId()) {
                            break;
                        }
                        $number++;
                    }
                }
                if ($number == 0) {
                    $this->getService(tagsManager::class)->addTag(self::TAG_LOADINGSCREEN, $this->getId());
                } else {
                    $this->getService(tagsManager::class)->addTag(self::TAG_GAMEGRAPHICS, $this->getId());
                }
            }
        }
    }

    public function getBestAuthorsPictures($limit = false)
    {
        if ($this->bestAuthorsPictures === null) {
            $queriesManager = $this->getService('ApiQueriesManager');
            $authorsIdList = [];
            foreach ($this->getAuthorsList() as $author) {
                $authorsIdList[] = $author->id;
            }

            $sort = ['votes' => 'desc'];
            $parameters = [
                'authorId' => $authorsIdList,
                'zxPictureNotId' => $this->getId(),
                'zxPictureMinRating' => $this->getService('ConfigManager')->get('zx.averageVote'),
            ];

            $query = $queriesManager->getQuery();
            $query->setFiltrationParameters($parameters);
            $query->setExportType('zxPicture');
            $query->setOrder($sort);
            $query->setStart(0);
            $query->setLimit(10);

            if ($result = $query->getQueryResult()) {
                shuffle($result['zxPicture']);
                $this->bestAuthorsPictures = array_slice($result['zxPicture'], 0, $limit);
            }
        }
        return $this->bestAuthorsPictures;
    }

    /**
     * @return string[]
     *
     * @psalm-return list{'view'}
     */
    public function getChartDataEventTypes($type = null)
    {
        return ['view'];
    }


    public function getLdJsonScriptData()
    {
        $data = [
            "@context" => "http://schema.org/",
            "@type" => ["VisualArtwork", "MediaObject"],
            "encodingFormat" => 'image/png',
            "name" => $this->title,
            "url" => $this->URL,
        ];

        $data["description"] = $this->getTextContent();
        $data["commentCount"] = $this->commentsAmount;
        $data["author"] = [
            "@type" => 'Person',
            "name" => $this->getAuthorNamesString(),
        ];
        $data["artist"] = [
            "@type" => 'Person',
            "name" => $this->getAuthorNamesString(),
        ];
        if ($this->votesAmount) {
            $data["aggregateRating"] = [
                "@type" => 'AggregateRating',
                "ratingValue" => $this->votes,
                "ratingCount" => $this->votesAmount,
            ];
        }
        if ($imageUrl = $this->getImageUrl(1)) {
            $data['image'] = $imageUrl;
            $data['thumbnailUrl'] = $this->getImageUrl(1);
        }
        if ($this->year) {
            $data['datePublished'] = $this->year;
        }
        if ($tags = $this->generateTagsText()) {
            $data['keywords'] = $tags;
        }
        $data['artMedium'] = 'Digital';
        $data['artform'] = 'Drawing';
        return $data;
    }

    /**
     * @return void
     */
    #[Override]
    public function persistElementData()
    {
        parent::persistElementData();
        $structureManager = $this->getService('structureManager');
        if ($elements = $structureManager->getElementsByType('zxItemsList')) {
            foreach ($elements as $element) {
                if ($element->items = 'graphics') {
                    $structureManager->clearElementCache($element->getId());
                }
            }
        }
        if ($this->newlyCreated) {
            $queueService = $this->getService(QueueService::class);
            $queueService->checkElementInQueue($this->getPersistedId(), [QueueType::SOCIAL_POST]);
        }
    }

    /**
     * @return (mixed|string)[]
     *
     * @psalm-return array{title: mixed, url: mixed, type: 'article', image: mixed, description: mixed, locale: mixed}
     */
    public function getOpenGraphData()
    {
        $languagesManager = $this->getService('LanguagesManager');
        $data = [
            'title' => $this->getMetaTitle(),
            'url' => $this->getUrl(),
            'type' => 'article',
            'image' => $this->getImageUrl(2),
            'description' => $this->getMetaDescription(),
            'locale' => $languagesManager->getCurrentLanguage()->iso6391,
        ];
        return $data;
    }
}
