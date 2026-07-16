<?php

declare(strict_types=1);

namespace ZxArt\Controllers;

use CmsHttpResponse;
use controller;
use LanguagesManager;
use Monolog\Logger;
use partyElement;
use structureManager;
use Throwable;
use ZxArt\Parties\PartiesTransformer;

/**
 * Parties list endpoint for the SPA `/parties` page (`/parties-data/`). Returns
 * every party as a card DTO, newest first. Named *PartiesData* so `/parties`
 * stays free for the SPA page.
 */
class PartiesData extends LoggedControllerApplication
{
    public $rendererName = 'json';

    public function __construct(
        controller $controller,
        Logger $logger,
        private readonly structureManager $structureManager,
        private readonly LanguagesManager $languagesManager,
        private readonly PartiesTransformer $partiesTransformer,
    ) {
        parent::__construct($controller, $logger);
    }

    public function initialize(): void
    {
        $this->startSession('public');
        $this->createRenderer();
    }

    public function execute($controller): void
    {
        try {
            $this->renderer->assign('body', ['parties' => $this->buildParties()]);
        } catch (Throwable $e) {
            $this->logThrowable('PartiesData::execute', $e);
            CmsHttpResponse::getInstance()->setStatusCode('500');
            $this->renderer->assign('body', ['errorMessage' => 'Internal server error']);
        }

        $this->renderer->display();
    }

    /** @return list<\ZxArt\Parties\Dto\PartyDto> */
    private function buildParties(): array
    {
        $languageId = $this->languagesManager->getCurrentLanguageId();
        $elements = $this->structureManager->getElementsByType('party', $languageId);
        $parties = [];
        foreach ($elements as $element) {
            if ($element instanceof partyElement) {
                $parties[] = $this->partiesTransformer->toDto($element);
            }
        }
        usort($parties, static fn($a, $b) => ((int)$b->year) <=> ((int)$a->year) ?: strcmp($a->title, $b->title));
        return $parties;
    }

    public function getUrlName(): string
    {
        return '';
    }
}
