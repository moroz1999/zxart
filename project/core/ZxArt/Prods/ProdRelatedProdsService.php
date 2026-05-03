<?php

declare(strict_types=1);

namespace ZxArt\Prods;

use structureManager;
use translationsManager;
use ZxArt\Prods\Dto\ProdSummariesDto;
use ZxArt\Prods\Dto\ProdSummaryDto;
use ZxArt\Prods\Exception\ProdDetailsException;
use zxProdElement;

readonly class ProdRelatedProdsService
{
    private const string PROD_LIST_IMAGE_PRESET = 'prodImage';

    public function __construct(
        private structureManager $structureManager,
        private translationsManager $translationsManager,
    ) {
    }

    public function getCompilationItems(int $elementId): ProdSummariesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdSummariesDto(prods: $this->buildSummaries($prod->compilationItems));
    }

    public function getSeriesProds(int $elementId): ProdSummariesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdSummariesDto(prods: $this->buildSummaries($prod->seriesProds));
    }

    public function getCompilations(int $elementId): ProdSummariesDto
    {
        $prod = $this->getProd($elementId);
        return new ProdSummariesDto(prods: $this->buildSummaries($prod->compilations));
    }

    public function getSeries(int $elementId): ProdSummariesDto
    {
        $prod = $this->getProd($elementId);

        $prods = [];
        foreach ($prod->series as $seriesElement) {
            if (!$seriesElement instanceof zxProdElement) {
                continue;
            }
            foreach ($seriesElement->seriesProds as $seriesProd) {
                $prods[$seriesProd->getId()] = $seriesProd;
            }
        }

        if ($prods === [] && $prod->seriesProds) {
            foreach ($prod->seriesProds as $seriesProd) {
                $prods[$seriesProd->getId()] = $seriesProd;
            }
        }

        return new ProdSummariesDto(prods: $this->buildSummaries($prods));
    }

    private function getProd(int $elementId): zxProdElement
    {
        $element = $this->structureManager->getElementById($elementId);
        if (!$element instanceof zxProdElement) {
            throw new ProdDetailsException('Prod not found', 404);
        }
        return $element;
    }

    /**
     * @param iterable<zxProdElement> $prods
     * @return ProdSummaryDto[]
     */
    private function buildSummaries(iterable $prods): array
    {
        $summaries = [];
        foreach ($prods as $prod) {
            if (!$prod instanceof zxProdElement) {
                continue;
            }
            $legalStatus = $prod->getLegalStatus();
            $imageUrl = $this->resolveImageUrl($prod);

            $summaries[] = new ProdSummaryDto(
                id: $prod->getId(),
                title: $this->decodeText($prod->title),
                url: (string)$prod->getUrl(),
                year: $prod->year,
                legalStatus: $legalStatus,
                legalStatusLabel: $this->translate('legalstatus.' . $legalStatus),
                votes: $prod->getVotes(),
                votesAmount: $prod->getVotesAmount(),
                imageUrl: $imageUrl,
            );
        }
        return $summaries;
    }

    private function resolveImageUrl(zxProdElement $prod): ?string
    {
        /** @var mixed $rawImageUrl */
        $rawImageUrl = $prod->getImageUrl(0, self::PROD_LIST_IMAGE_PRESET);
        if (!is_string($rawImageUrl) || $rawImageUrl === '') {
            return null;
        }

        return $rawImageUrl;
    }

    private function translate(string $key): string
    {
        return (string)$this->translationsManager->getTranslationByName($key);
    }

    private function decodeText(string $value): string
    {
        return html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }
}
