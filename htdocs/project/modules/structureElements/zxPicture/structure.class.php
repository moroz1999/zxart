<?php

/**
 * Class zxPictureElement
 *
 * @property string $title
 * @property float $votes
 * @property int $votesAmount
 * @property int $commentsAmount
 * @property int $year
 * @property string $palette
 */
class zxPictureElement extends ZxArtItem
{
    use PaletteTypesProvider;
    use GraphicsCompoProvider;

    const TAG_LOADINGSCREEN = "Loading Screen";
    const TAG_GAMEGRAPHICS = "Game graphics";

    public $dataResourceName = 'module_zxpicture';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $viewName = 'short';
    protected $votesType = 'zxPicture';
    protected $authorLinkType = 'authorPicture';
    protected $partyLinkType = 'partyPicture';
    protected $sectionType = 'graphics';
    protected $imageName;
    protected $bestAuthorsPictures;

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
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';
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
        $moduleStructure['md5'] = 'text';
        $moduleStructure['palette'] = 'text';
    }

    public function getElementData()
    {
        // generic
        $data["id"] = $this->id;
        $data["title"] = $this->title;
        $data["link"] = $this->URL;
        $data["votes"] = $this->votes;
        $data["userVote"] = $this->getUserVote();
        $data["votePercent"] = $this->getVotePercent();
        return $data;
    }

    public function getImageUrl($zoom = 1, $download = false, $border = null)
    {
        $controller = controller::getInstance();
        if ($this->image) {
            /**
             * @var PicturesModesManager $picturesModesManager
             */
            $picturesModesManager = $this->getService('PicturesModesManager');

            $imageUrl = $controller->baseURL . 'zxscreen/';
            if ($this->rotation > 0) {
                $imageUrl .= 'rotation:' . $this->rotation . '/';
            }
            if ($border === null || $border) {
                if ($picturesModesManager->getBorder()) {
                    $imageUrl .= 'border:' . $this->border . '/';
                }
            }

            if ($download) {
                $imageUrl .= 'download:' . $download . '/';
            }
            $imageUrl .= 'mode:' . $picturesModesManager->getMode() . '/';
            $imageUrl .= 'pal:' . $this->getPalette() . '/';
            if ($this->type == 'standard' && $picturesModesManager->getHidden()) {
                $imageUrl .= 'type:hidden/';
            } else {
                $imageUrl .= 'type:' . $this->type . '/';
            }
            $imageUrl .= 'zoom:' . $zoom . '/';
            $imageUrl .= 'id:' . $this->image . '/';
        } else {
            $imageUrl = $controller->baseURL . 'project/images/public/zxprod_default.png';
        }
        return $imageUrl;
    }

    public function getPalette()
    {
        $palette = false;
        if (!$this->palette) {
            foreach ($this->getRealAuthorsList() as $author) {
                $palette = $author->getPalette();
                break;
            }
        } else {
            $palette = $this->palette;
        }
        if (!$palette) {
            $palette = 'srgb';
        }
        return $palette;
    }

    public function getImageContents()
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

    public function getOriginalPath()
    {
        if ($this->image) {
            return $this->getService('PathsManager')->getPath('uploads') . $this->image;
        }
        return false;
    }

    public function getFileExtension($extensionType)
    {
        $extension = '';
        if ($extensionType == 'original') {
            $type = $this->type;
            if ($type == 'standard' || $type == 'monochrome') {
                $extension = '.scr';
            } elseif ($type == 'gigascreen') {
                $extension = '.img';
            } elseif ($type == 'flash') {
                $extension = '.flash.scr';
            } elseif ($type == 'tricolor') {
                $extension = '.3';
            } elseif ($type == 'mg1') {
                $extension = '.mg1';
            } elseif ($type == 'mg2') {
                $extension = '.mg2';
            } elseif ($type == 'mg4') {
                $extension = '.mg4';
            } elseif ($type == 'mg8') {
                $extension = '.mg8';
            } elseif ($type == 'multicolor') {
                $extension = '.ifl';
            } elseif ($type == 'multicolor4') {
                $extension = '.ifl';
            } elseif ($type == 'mc') {
                $extension = '.mc';
            } elseif ($type == 'mlt') {
                $extension = '.mlt';
            } elseif ($type == 'attributes') {
                $extension = '.atr';
            } elseif ($type == 'lowresgs') {
                $extension = '.hlr';
            } elseif ($type == 'chr$') {
                $extension = '.ch$';
            } elseif ($type == 'timex81') {
                $extension = '.scr';
            } elseif ($type == 'timexhr') {
                $extension = '.scr';
            } elseif ($type == 'timexhrg') {
                $extension = '.hrg';
            } elseif ($type == 'sam4') {
                $extension = '.ss4';
            } elseif ($type == 'bsc') {
                $extension = '.bsc';
            } elseif ($type == 'bsp') {
                $extension = '.bsp';
            } elseif ($type == 'bmc4') {
                $extension = '.bmc4';
            } elseif ($type == 'ulaplus') {
                $extension = '.ulaplus.scr';
            } elseif ($type == 'zxevo') {
                $extension = '.bmp';
            } elseif ($type == 'sxg') {
                $extension = '.sxg';
            } elseif ($type == 'stellar') {
                $extension = '.stl';
            } elseif ($type == 'nxi') {
                $extension = '.nxi';
            }
        } elseif ($extensionType == 'exe') {
            if ($this->exeFileName) {
                $info = pathinfo($this->exeFileName);
                $extension = "." . strtolower($info['extension']);
            }
        } elseif ($extensionType == 'image') {
            $extension = '';
        }
        return $extension;
    }

    protected function fileExists($extensionType)
    {
        if ($extensionType == 'original') {
            if ($this->originalName) {
                return true;
            }
        } elseif ($extensionType == 'exe') {
            if ($this->exeFileName) {
                return true;
            }
        } elseif ($extensionType == 'image') {
            if ($this->image) {
                return true;
            }
        }
        return false;
    }

    public function getMetaTitle()
    {
        $translationsManager = $this->getService('translationsManager');

        $authorNames = $this->getAuthorNames();

        $this->metaTitle = $translationsManager->getTranslationByName("titles.picture");
        $this->metaTitle = str_ireplace('%t', $this->title, $this->metaTitle);

        if ($authorNames) {
            $this->metaTitle = str_ireplace('%a', implode(", ", $authorNames), $this->metaTitle);
        }
        return $this->metaTitle;
    }

    public function getTextContent()
    {
        $translationsManager = $this->getService('translationsManager');

        $textContent = $translationsManager->getTranslationByName("descriptions.picture");
        $textContent = str_ireplace('%t', $this->title, $textContent);

        if ($authorNames = $this->getAuthorNames()) {
            $textContent = str_ireplace('%a', implode(", ", $authorNames), $textContent);
        }

        if ($partyElement = $this->getPartyElement()) {
            $textContent = str_ireplace('%p', $partyElement->title, $textContent);
        } else {
            $textContent = str_ireplace('%p', "", $textContent);
        }

        if ($this->getGameElement()) {
            $textContent = str_ireplace('%g', $this->getGameElement()->title, $textContent);
        } else {
            $textContent = str_ireplace('%g', $this->release, $textContent);
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


    public function isFlickering()
    {
        return in_array(
            $this->type,
            [
                'gigascreen',
                'tricolor',
                'mg1',
                'mg2',
                'mg4',
                'mg8',
                'lowresgs',
                'stellar',
                'chr$',
                'bsp',
            ]
        );
    }

    public function logView()
    {
        if (!isset($_SERVER['HTTP_USER_AGENT']) || !preg_match(
                '/bot|crawl|slurp|spider/i',
                $_SERVER['HTTP_USER_AGENT']
            )) {
            //$text = date('d.m.Y H:i') . "\t" . $_SERVER['REMOTE_ADDR'] . "\t" . $_SERVER['REQUEST_URI'] . "\t" . $_SERVER['HTTP_USER_AGENT'] . "\n";
            //file_put_contents(ROOT_PATH . date('d.m.Y') . '-view.txt', $text, FILE_APPEND);


            $this->views++;
            $this->getService('eventsLog')->logEvent($this->id, 'view');
            $collection = persistableCollection::getInstance($this->dataResourceName);
            $collection->updateData(['views' => $this->views], ['id' => $this->id]);
        }
    }

    public function checkGameTag()
    {
        $gameTags = [13843, 46245, 13983, 47883, 46237];
        if ($tags = $this->getTagsList()) {
            foreach ($tags as $tagElement) {
                if (in_array($tagElement->id, $gameTags)) {
                    return;
                }
            }
        }
        if ($game = $this->getGameElement()) {
            if ($gameMaterials = $game->getMaterialsList()) {
                $number = 0;
                foreach ($gameMaterials as $element) {
                    if ($element->structureType == 'zxPicture') {
                        if ($element->id == $this->id) {
                            break;
                        }
                        $number++;
                    }
                }
                if ($number == 0) {
                    $this->getService('tagsManager')->addTag(self::TAG_LOADINGSCREEN, $this->id);
                } else {
                    $this->getService('tagsManager')->addTag(self::TAG_GAMEGRAPHICS, $this->id);
                }
            }
        }
    }

    public function getBestAuthorsPictures($limit = false)
    {
        if ($this->bestAuthorsPictures === null) {
            /**
             * @var ApiQueriesManager $queriesManager
             */
            $queriesManager = $this->getService('ApiQueriesManager');
            $authorsIdList = [];
            foreach ($this->getAuthorsList() as $author) {
                $authorsIdList[] = $author->id;
            }

            $sort = ['votes' => 'desc'];
            $parameters = [
                'authorId' => $authorsIdList,
                'zxPictureNotId' => $this->id,
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

    public function getChartDataEventTypes($type = null)
    {
        return ['view'];
    }

    public function getLdJsonScriptData()
    {
        $data = [
            "@context" => "http://schema.org/",
            "@type" => "MediaObject",
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
        if ($this->votesAmount) {
            $data["aggregateRating"] = [
                "@type" => 'AggregateRating',
                "ratingValue" => $this->votes,
                "ratingCount " => $this->votesAmount,
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
        return $data;
    }

    public function persistElementData()
    {
        parent::persistElementData();
        $structureManager = $this->getService('structureManager');
        if ($elements = $structureManager->getElementsByType('zxItemsList')) {
            foreach ($elements as $element) {
                if ($element->items = 'graphics') {
                    $structureManager->clearElementCache($element->id);
                }
            }
        }
    }

}