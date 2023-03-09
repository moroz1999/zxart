<?php

/**
 * Class zxMusicElement
 *
 * @property int $partyplace
 * @property int $denyPlaying
 * @property int $commentsAmount
 * @property string $title
 * @property string $mp3Name
 * @property string $file
 * @property string $fileName
 * @property float $votes
 * @property int $votesAmount
 */
class zxMusicElement extends ZxArtItem implements OpenGraphDataProviderInterface
{
    use MusicSettingsProvider;

    const MP3_STORAGE_PATH = 'https://converter.dev.artweb.ee/music/';
    public $dataResourceName = 'module_zxmusic';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $votesType = 'zxMusic';
    protected $authorLinkType = 'authorMusic';
    protected $partyLinkType = 'partyMusic';
    protected $sectionType = 'music';

    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['description'] = 'pre';

        $moduleStructure['party'] = 'text';
        $moduleStructure['partyplace'] = 'text';
        $moduleStructure['compo'] = 'text';
        $moduleStructure['game'] = 'text';
        $moduleStructure['author'] = 'numbersArray';
        $moduleStructure['type'] = 'text';
        $moduleStructure['year'] = 'text';
        $moduleStructure['votes'] = 'floatNumber';
        $moduleStructure['file'] = 'file';
        $moduleStructure['fileName'] = 'fileName';
        $moduleStructure['inspired'] = 'text';
        $moduleStructure['dateAdded'] = 'date';
        $moduleStructure['tagsText'] = 'text';
        $moduleStructure['tagsAmount'] = 'text';
        $moduleStructure['votesAmount'] = 'text';
        $moduleStructure['commentsAmount'] = 'text';
        $moduleStructure['userId'] = 'text';
        $moduleStructure['denyPlaying'] = 'checkbox';
        $moduleStructure['denyVoting'] = 'checkbox';
        $moduleStructure['denyComments'] = 'checkbox';
        $moduleStructure['time'] = 'text';
        $moduleStructure['channels'] = 'naturalNumber';
        $moduleStructure['container'] = 'text';
        $moduleStructure['program'] = 'text';
        $moduleStructure['internalTitle'] = 'text';
        $moduleStructure['internalAuthor'] = 'text';
        $moduleStructure['mp3Name'] = 'text';
        $moduleStructure['chipType'] = 'text';
        $moduleStructure['channelsType'] = 'text';
        $moduleStructure['frequency'] = 'text';
        $moduleStructure['intFrequency'] = 'text';
        $moduleStructure['zxTunesId'] = 'text';
        $moduleStructure['plays'] = 'text';
        $moduleStructure['embedCode'] = 'code';
        $moduleStructure['formatGroup'] = 'text';

        $moduleStructure['conversionChannelsType'] = 'text';
        $moduleStructure['conversionChipType'] = 'text';
        $moduleStructure['conversionFrequency'] = 'text';
        $moduleStructure['conversionIntFrequency'] = 'text';
        $moduleStructure['converterVersion'] = 'text';
        $moduleStructure['trackerFile'] = 'file';
        $moduleStructure['trackerFileName'] = 'fileName';
    }

    public function getElementData()
    {
        $data["id"] = $this->id;
        $data["author"] = $this->getAuthorNamesString();
        $data["title"] = $this->title;
        $data["link"] = $this->URL;
        $data["votes"] = $this->votes;
        $data["userVote"] = $this->getUserVote();
        $data["votePercent"] = $this->getVotePercent();
        $data["mp3FilePath"] = $this->getMp3FilePath();
        $data["url"] = $this->getUrl();
        return $data;
    }

    public function getMetaTitle()
    {
        $translationsManager = $this->getService('translationsManager');

        $authorNames = $this->getAuthorNames();

        $this->metaTitle = $translationsManager->getTranslationByName("titles.music");
        $this->metaTitle = str_ireplace('%t', $this->title, $this->metaTitle);

        if ($authorNames) {
            $this->metaTitle = str_ireplace('%a', implode(", ", $authorNames), $this->metaTitle);
        }
        return $this->metaTitle;
    }

    public function getTextContent()
    {
        $translationsManager = $this->getService('translationsManager');

        $textContent = $translationsManager->getTranslationByName("descriptions.music");
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
            $textContent = str_ireplace('%g ', '', $textContent);
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

    public function persistElementData()
    {
        parent::persistElementData();
        $this->checkIfReconversionNeeded();

        $structureManager = $this->getService('structureManager');
        if ($elements = $structureManager->getElementsByType('zxItemsList')) {
            foreach ($elements as $element) {
                if ($element->items = 'music') {
                    $structureManager->clearElementCache($element->id);
                }
            }
        }
    }

    public function checkIfReconversionNeeded()
    {
        if ($this->fileName && (!$this->mp3Name || ($this->getChannelsType() != $this->conversionChannelsType || $this->getChipType() != $this->conversionChipType || $this->getFrequency() != $this->conversionFrequency || $this->getIntFrequency() != $this->conversionIntFrequency) && $this->hasChipChannelsType())) {
            $linksManager = $this->getService('linksManager');
            if ($parentIds = $linksManager->getConnectedIdList($this->id, 'ayTrack', 'child')) {
                $parentId = reset($parentIds);
                $structureManager = $this->getService('structureManager');
                if ($mainTrackElement = $structureManager->getElementById($parentId)) {
                    $mainTrackElement->reconvertMp3();
                    return true;
                }
            }

            $this->reconvertMp3();
            return true;
        }
        return false;
    }

    public function reconvertMp3()
    {
        $mp3ConversionManager = $this->getService('mp3ConversionManager');
        $mp3ConversionManager->addToConversionQueue($this->id);
    }

    public function getOriginalFilePath()
    {
        $result = false;
        if ($this->fileName) {
            $result = $this->getService('PathsManager')->getPath('uploads') . $this->file;
        }
        return $result;
    }

    public function isPlayable()
    {
        return ($this->mp3Name && !$this->denyPlaying);
    }

    public function getMp3FilePath()
    {
        if ($this->mp3Name) {
            return self::MP3_STORAGE_PATH . $this->mp3Name;
        }
        return false;
    }

    public function generateConvertedBaseName($extraText = '')
    {
        $name = $this->id . ' ' . implode(",", $this->getAuthorNames()) . ' ' . $this->title;
        if ($extraText) {
            $name .= ' ' . $extraText;
        }
        $name = html_entity_decode($name, ENT_QUOTES);
        $name = preg_replace('/[^a-z\d]/i', '_', $name);
        return $name;
    }

    public function getFileExtension($extensionType)
    {
        $extension = '';
        if ($extensionType == 'original') {
            if ($info = pathinfo(html_entity_decode($this->fileName, ENT_QUOTES))) {
                if (isset($info['extension'])) {
                    $extension = "." . strtolower($info['extension']);
                }
            }
        } elseif ($extensionType == 'tracker') {
            if ($info = pathinfo(html_entity_decode($this->trackerFileName, ENT_QUOTES))) {
                if (isset($info['extension'])) {
                    $extension = "." . strtolower($info['extension']);
                }
            }
        }

        return $extension;
    }

    protected function fileExists($extensionType)
    {
        if ($extensionType == 'original') {
            if ($this->fileName) {
                return true;
            }
        } elseif ($extensionType == 'tracker') {
            if ($this->trackerFileName) {
                return true;
            }
        }

        return false;
    }

    public function getZxTunesUrl()
    {
        $result = false;
        if ($this->zxTunesId > 0) {
            if ($authors = $this->getRealAuthorsList()) {
                foreach ($authors as $author) {
                    if ($author->zxTunesId > 0) {
                        $result = 'http://zxtunes.com/author.php?id=' . $author->zxTunesId . '&play=' . $this->zxTunesId;
                    }
                }
            }
        }
        return $result;
    }

    public function logPlay()
    {
        $this->plays++;
        $this->getService('eventsLog')->logEvent($this->id, 'play');
        $collection = persistableCollection::getInstance($this->dataResourceName);
        $collection->updateData(['plays' => $this->plays], ['id' => $this->id]);
    }

    public function getIso8601Duration()
    {
        $duration = '';
        try {
            $duration = (new DateTime($this->time))->format('\P\TG\Mi\S');
        } catch (exception $e) {
        }
        return $duration;
    }

    public function getChartDataEventTypes($type = null)
    {
        return ['play'];
    }

    public function getChipType()
    {
        $chipType = false;
        if (!$this->chipType) {
            foreach ($this->getRealAuthorsList() as $author) {
                $chipType = $author->chipType;
                break;
            }
        } else {
            $chipType = $this->chipType;
        }
        if (!$chipType) {
            $chipType = 'ym';
        }
        return $chipType;
    }

    public function getFrequency()
    {
        $frequency = false;
        if (!$this->frequency) {
            foreach ($this->getRealAuthorsList() as $author) {
                $frequency = $author->frequency;
                break;
            }
        } else {
            $frequency = $this->frequency;
        }
        if (!$frequency) {
            $frequency = 1770000;
        }
        return $frequency;
    }

    public function getIntFrequency()
    {
        $intFrequency = false;
        if (!$this->intFrequency) {
            foreach ($this->getRealAuthorsList() as $author) {
                $intFrequency = $author->intFrequency;
                break;
            }
        } else {
            $intFrequency = $this->intFrequency;
        }
        if (!$intFrequency) {
            $intFrequency = 50;
        }
        return $intFrequency;
    }

    public function getChannelsType()
    {
        $channelsType = false;
        if (!$this->channelsType) {
            foreach ($this->getRealAuthorsList() as $author) {
                $channelsType = $author->channelsType;
                break;
            }
        } else {
            $channelsType = $this->channelsType;
        }
        if (!$channelsType) {
            $channelsType = 'ACB';
        }
        return $channelsType;
    }

    public function hasChipChannelsType()
    {
        return in_array($this->formatGroup, ['ay', 'ts', 'tsfm', 'fm', 'aydigitalay', 'aycovox']);
    }

    public function getOriginalPath()
    {
        if ($this->file) {
            return $this->getService('PathsManager')->getPath('uploads') . $this->file;
        }
        return false;
    }

    public function getLdJsonScriptData()
    {
        $data = [
            "@context" => "http://schema.org/",
            "@type" => "MusicRecording",
            "encodingFormat" => 'audio/mpeg',
            "name" => $this->title,
            "url" => $this->URL,
        ];

        $data["description"] = $this->getTextContent();
        $data["commentCount"] = $this->commentsAmount;
        $data["author"] = [
            "@type" => 'Person',
            "name" => $this->getAuthorNamesString(),
        ];
        $data["byArtist"] = [
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
        if ($path = $this->getMp3FilePath()) {
            $data['audio'] = $path;
            $data['duration'] = $this->getIso8601Duration();
        }
        if ($this->year) {
            $data['datePublished'] = $this->year;
        }
        if ($tags = $this->generateTagsText()) {
            $data['keywords'] = $tags;
        }
        return $data;
    }

    public function getOpenGraphData()
    {
        $languagesManager = $this->getService('LanguagesManager');
        $data = [
            'title' => $this->getMetaTitle(),
            'url' => $this->getUrl(),
            'type' => 'music:song',
            'og:audio' => $this->getMp3FilePath(),
            'image' => '/project/images/public/logo_og.png',
            'description' => $this->getMetaDescription(),
            'locale' => $languagesManager->getCurrentLanguage()->iso6391,
        ];
        return $data;
    }
}