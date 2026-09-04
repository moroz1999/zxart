<?php

use ZxArt\Authors\Repositories\AuthorshipRepository;
use ZxArt\Groups\GroupMemberRoles;
use ZxArt\Import\ImportOrigin;
use ZxArt\Import\ImportOriginsHolderTrait;
use ZxArt\Shared\EntityType;

trait Group
{
    use ImportOriginsHolderTrait;

    protected $linksInfo;

    public function getImportEntityType(): EntityType
    {
        return EntityType::Group;
    }

    /**
     * @return zxReleaseElement[]
     *
     * @psalm-return array<zxReleaseElement>
     */
    public function getReleases(): array
    {
        return $this->publishedReleases;
    }

    /**
     * A form draft has no roster: it is not persisted yet, and its transient
     * identifier is a structure path that casts to 0 - the id of no element.
     */
    public function getAuthorsInfo($type)
    {
        if (!$this->hasActualStructureInfo()) {
            return [];
        }
        $authorshipRepository = $this->getService(AuthorshipRepository::class);
        $entityType = $type instanceof EntityType ? $type : EntityType::from($type);
        $info = $authorshipRepository->getAuthorsInfo($this->id, $entityType);
        $sort = [];
        foreach ($info as $item) {
            $sort[] = $item['authorElement']->getTitle();
        }
        array_multisort($sort, SORT_ASC, $info);
        return $info;
    }

    public function getLinksInfo()
    {
        if ($this->linksInfo === null) {
            $this->linksInfo = [];
            /**
             * @var translationsManager $translationsManager
             */
            $translationsManager = $this->getService(translationsManager::class);

            $types = [ImportOrigin::Zxaaa, ImportOrigin::Zxdb, ImportOrigin::Spectrum4Ever, ImportOrigin::WorldOfSam];


            /**
             * @var \Illuminate\Database\Connection $db
             */
            $db = $this->getService('db');
            $query = $db->table('import_origin')
                ->select('importId', 'importOrigin')
                ->where('elementId', '=', $this->id)
                ->whereIn('importOrigin', array_map(static fn(ImportOrigin $type): string => $type->value, $types));
            if ($rows = $query->get()) {
                foreach ($rows as $row) {
                    $origin = ImportOrigin::tryFrom((string)$row['importOrigin']);
                    if ($origin === ImportOrigin::Zxdb) {
                        $this->linksInfo[] = [
                            'type' => 'sc',
                            'image' => 'icon_sc.png',
                            'name' => $translationsManager->getTranslationByName('links.link_sc'),
                            'url' => 'https://spectrumcomputing.co.uk/index.php?cat=999&label_id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($origin === ImportOrigin::Zxaaa) {
                        $this->linksInfo[] = [
                            'type' => '3a',
                            'image' => 'icon_3a.png',
                            'name' => $translationsManager->getTranslationByName('links.link_3a'),
                            'url' => 'https://zxaaa.net/view_demos.php?a=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($origin === ImportOrigin::Spectrum4Ever) {
                        $this->linksInfo[] = [
                            'type' => 's4e',
                            'image' => 'icon_s4e.png',
                            'name' => $translationsManager->getTranslationByName('links.link_s4e'),
                            'url' => 'https://spectrum4ever.org/fulltape.php?go=studio&id=' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    } elseif ($origin === ImportOrigin::WorldOfSam) {
                        $this->linksInfo[] = [
                            'type' => 'worldofsam',
                            'image' => 'icon_worldofsam.png',
                            'name' => $translationsManager->getTranslationByName('links.link_worldofsam'),
                            'url' => 'https://www.worldofsam.org/people/' . $row['importId'],
                            'id' => $row['importId'],
                        ];
                    }
                }
            }


            if ($this->wikiLink) {
                $this->linksInfo[] = [
                    'type' => 'swiki',
                    'image' => 'icon_swiki.png',
                    'name' => $translationsManager->getTranslationByName('links.link_swiki'),
                    'url' => 'https://speccy.info/' . $this->wikiLink,
                    'id' => $this->wikiLink,
                ];
            }
        }
        if ($this->structureType == 'group') {
            if ($aliasElements = $this->getAliasElements()) {
                /**
                 * @var authorAliasElement $aliasElement
                 */
                foreach ($aliasElements as $aliasElement) {
                    $this->linksInfo = array_merge($this->linksInfo, $aliasElement->getLinksInfo());
                }
            }
        }
        return $this->linksInfo;
    }

    /**
     * @return string[]
     */
    public function getAuthorRoles(): array
    {
        return GroupMemberRoles::LIST;
    }

    public function getProdsInfo(): array
    {
        $prodsInfo = [];
        foreach ($this->getGroupProds() as $prod) {
            $prodsInfo[] = $prod->getElementData('list');
        }
        return $prodsInfo;
    }

    public function getProdsAmount(): int
    {
        return count($this->getGroupProds());
    }
}
