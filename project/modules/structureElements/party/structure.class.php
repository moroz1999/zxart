<?php

use ZxArt\LinkTypes;

/**
 * @property string $title
 * @property string $abbreviation
 * @property string $image
 * @property string $originalName
 * @property int $country
 * @property int $city
 * @property int $picturesQuantity
 * @property int $tunesQuantity
 */
class partyElement extends structureElement implements CommentsHolderInterface, Recalculable, LocationProvider
{
    use LocationProviderTrait;
    use CommentsTrait;

    public $dataResourceName = 'module_party';
    public $allowedTypes = [];
    public $defaultActionName = 'show';
    public $role = 'content';
    protected $viewName = 'all';

    /**
     * @var yearElement
     */
    protected $yearElement;
    protected $prodsCompos;
    protected $picturesCompos;
    protected $tunesCompos;

    /**
     * @return void
     */
    protected function setModuleStructure(&$moduleStructure)
    {
        $moduleStructure['title'] = 'text';
        $moduleStructure['abbreviation'] = 'text';
        $moduleStructure['country'] = 'text';
        $moduleStructure['city'] = 'text';
        $moduleStructure['picturesQuantity'] = 'text';
        $moduleStructure['tunesQuantity'] = 'text';
        $moduleStructure['image'] = 'image';
        $moduleStructure['originalName'] = 'text';
        $moduleStructure['website'] = 'url';
        $moduleStructure['mentions'] = [
            'ConnectedElements',
            [
                'linkType' => LinkTypes::PRESS_PARTIES->value,
                'role' => 'parent',
            ],
        ];
    }

    /**
     * @return int
     */
    protected function getCityId()
    {
        return $this->city;
    }

    /**
     * @return int
     */
    protected function getCountryId()
    {
        return $this->country;
    }

    public function getYear(): string
    {
        if (is_null($this->yearElement)) {
            $structureManager = $this->getService('structureManager');
            $this->yearElement = $structureManager->getElementsFirstParent($this->id);
        }
        return $this->yearElement->title;
    }

    /**
     * @return void
     */
    public function recalculate()
    {
        if ($pictureIds = $this->getService('linksManager')->getConnectedIdList($this->id, 'partyPicture', 'parent')) {
            $this->picturesQuantity = count($pictureIds);
        } else {
            $this->picturesQuantity = 0;
        }
        if ($tuneIds = $this->getService('linksManager')->getConnectedIdList($this->id, 'partyMusic', 'parent')) {
            $this->tunesQuantity = count($tuneIds);
        } else {
            $this->tunesQuantity = 0;
        }
        $this->checkCountry();
        $this->persistElementData();
    }


    public function getPicturesCompos()
    {
        if ($this->picturesCompos === null) {
            $this->picturesCompos = $this->getCompos(['partyPicture']);
        }
        return $this->picturesCompos;
    }

    public function getProdsCompos()
    {
        if ($this->prodsCompos === null) {
            $this->prodsCompos = $this->getCompos(['partyProd']);
        }
        return $this->prodsCompos;
    }

    /**
     * @return (false|string)[]
     *
     * @psalm-return array<false|string>
     */
    public function getProdsComposJson(): array
    {
        $json = [];
        foreach ($this->getProdsCompos() as $compo => $prods) {
            $compoData = [
                'prods' => [],
                'prodsAmount' => count($prods)
            ];
            foreach ($prods as $prod) {
                $compoData['prods'][] = $prod->getElementData('list');
            }
            $json[$compo] = json_encode($compoData);
        }
        return $json;
    }

    public function getTunesCompos()
    {
        if ($this->tunesCompos === null) {
            $this->tunesCompos = $this->getCompos(['partyMusic']);
        }
        return $this->tunesCompos;
    }

    /**
     * @param string[] $linkTypes
     *
     * @psalm-param list{'partyMusic'|'partyPicture'|'partyProd'} $linkTypes
     *
     * @return array[]
     *
     * @psalm-return array<non-empty-list<non-empty-mixed>>
     */
    protected function getCompos(array $linkTypes): array
    {
        $compos = [];
        $idList = $this->getService('linksManager')->getConnectedIdList($this->id, $linkTypes, 'parent');
        $entries = [];
        foreach ($idList as $id) {
            if ($entry = $this->getService('structureManager')->getElementById($id)) {
                $entries[] = $entry;
            }
        }
        $sort = [];
        foreach ($entries as $entry) {
            $sort[] = $entry->partyplace . '_' . $entry->id;
        }
        array_multisort($sort, SORT_ASC, SORT_NUMERIC, $entries);

        foreach ($entries as $entry) {
            if (!empty($entry->compo)) {
                $compos[$entry->compo][] = $entry;
            } else {
                $compos['none'][] = $entry;
            }
        }
        krsort($compos);

        return $compos;
    }

    public function getSaveUrl(): string
    {
        $controller = controller::getInstance();
        $url = $controller->baseURL . 'zipItems/';
        $url .= 'language:' . $this->getService('LanguagesManager')->getCurrentLanguageCode() . '/';
        $url .= 'filter:partyId=' . $this->id . '/';
        $url .= 'structure:parties/';

        return $url;
    }

    public function getText($textType): string
    {
        $text = '';
        $translationsManager = $this->getService('translationsManager');

        if ($textType == 'bb') {
            foreach ($this->getPicturesCompos() as $compo => $items) {
                $items = array_reverse($items);

                $text .= "\n\n\n";
                $text .= '[b]' . $translationsManager->getTranslationByName('label.compo_' . $compo) . "[/b]\n\n";
                $length = count($items);
                foreach ($items as $key => $item) {
                    $text .= '[url=' . $item->getUrl() . '][img]' . $item->getImageUrl(1) . 'image.png[/img][/url] ';
                    if (($key == $length - 1) || (($key + 1) % 3 == 0)) {
                        $text .= "\n";
                    }
                }
            }
            foreach ($this->getTunesCompos() as $compo => $items) {
                $items = array_reverse($items);
                $text .= "\n\n\n";
                $text .= '[b]' . $translationsManager->getTranslationByName('musiccompo.compo_' . $compo) . "[/b]\n\n";
                foreach ($items as $key => $item) {
                    $text .= '[url=' . $item->getUrl() . ']' . $item->title . '[/url] by ' . $item->getAuthorNamesString();
                    if ($path = $item->getMp3FilePath()) {
                        $text .= ' - [url=' . $path . ']mp3[/url] ';
                    }
                    $text .= "\n";
                }
            }
        } elseif ($textType == 'text') {
            foreach ($this->getPicturesCompos() as $compo => $items) {
                $items = array_reverse($items);
                $text .= "\n\n\n";
                $text .= $translationsManager->getTranslationByName('label.compo_' . $compo) . "\n\n";

                foreach ($items as $key => $item) {
                    $text .= $item->title . "\n";
                    $text .= $item->getAuthorNamesString() . "\n";
                    $text .= $item->getUrl() . "\n";
                    $text .= $item->getImageUrl(1) . 'image.png' . "\n";
                    $text .= "\n";
                }
            }
            foreach ($this->getTunesCompos() as $compo => $items) {
                $items = array_reverse($items);
                $text .= "\n\n\n";
                $text .= $translationsManager->getTranslationByName('musiccompo.compo_' . $compo) . "\n\n";
                foreach ($items as $key => $item) {
                    $text .= $item->title . "\n";
                    $text .= $item->getAuthorNamesString() . "\n";
                    $text .= $item->getUrl() . "\n";
                    if ($path = $item->getMp3FilePath()) {
                        $text .= $path . "\n";
                    }
                    $text .= "\n";
                }
            }
        } elseif ($textType == 'html') {
            foreach ($this->getPicturesCompos() as $compo => $items) {
                $items = array_reverse($items);

                $text .= '<h4>' . $translationsManager->getTranslationByName('label.compo_' . $compo) . '</h4>' . "\n";
                foreach ($items as $item) {
                    $text .= '<h5>' . $item->title . ' by ' . $item->getAuthorNamesString() . '</h5>' . "\n";
                    $text .= '<a target="_blank" href="' . $item->URL . '"><img src="' . $item->getImageUrl(
                            2
                        ) . '"  alt="" /></a>' . "\n";
                    $text .= "\n";
                    $text .= "\n";
                    $text .= "\n";
                }
            }
            foreach ($this->getTunesCompos() as $compo => $items) {
                $items = array_reverse($items);

                $text .= '<h4>' . $translationsManager->getTranslationByName(
                        'musiccompo.compo_' . $compo
                    ) . '</h4>' . "\n";
                foreach ($items as $item) {
                    $text .= '<h5>' . $item->title . ' by ' . $item->getAuthorNamesString() . '</h5>' . "\n";
                    $text .= '<audio controls>' . "\n";
                    $text .= '    <source src="' . $item->getMp3FilePath() . '" type="audio/mp3"></source>' . "\n";
                    $text .= '</audio>' . "\n";
                    $text .= "\n";
                    $text .= "\n";
                    $text .= "\n";
                }
            }
        }
        return htmlentities($text, ENT_QUOTES);
    }

    public function getImageUrl(string $preset = 'partyFull'): string
    {
        if ($this->image) {
            return controller::getInstance()->baseURL . 'image/type:' . $preset . '/id:' . $this->image . '/filename:' . $this->originalName;
        }
        return controller::getInstance()->baseURL . 'images/zxprod_default.png';
    }

    public function getSearchTitle(): string
    {
        return $this->title;
    }
}