<?php
declare(strict_types=1);

namespace ZxArt\Parties\Services;

use ElementsManager;
use LanguagesManager;
use partyElement;
use structureManager;
use ZxArt\Parties\Repositories\PartiesRepository;

class PartiesService extends ElementsManager
{
    public const string TABLE = 'module_party';
    protected array $columnRelations = [];

    public function __construct(
        private readonly PartiesRepository $partiesRepository,
        protected structureManager         $structureManager,
        protected LanguagesManager         $languagesManager,
    )
    {
        $this->columnRelations = [
            'title' => ['title' => true],
            'date' => ['id' => true],
        ];
    }

    public function getPartyByTitleAndYear(string $title, int $year): ?partyElement
    {
        $id = $this->partiesRepository->findPartyIdByTitleAndYear($title, $year);
        if ($id === null) {
            return null;
        }
        /**
         * @var partyElement $partyElement
         */
        $partyElement = $this->structureManager->getElementById($id);
        return $partyElement;
    }
}