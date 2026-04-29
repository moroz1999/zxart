<?php
declare(strict_types=1);


final readonly class LanguageLinksService
{
    public function __construct(
        private controller       $controller,
        private ConfigManager    $configManager,
        private LanguagesManager $languagesManager,
        private structureManager $structureManager,
        private linksManager     $linksManager,
    )
    {

    }

    public function getLinkForLanguage(structureElement $element, string $languageCode): ?string
    {
        $language = $this->languagesManager->getLanguageByCode($languageCode);
        if (!$language) {
            return null;
        }

        if ($language->id === $this->languagesManager->getCurrentLanguageId()) {
            return $element->getUrl();
        }

        if (!$element->hasActualStructureInfo()) {
            return null;
        }

        $path = $this->structureManager->findPath($element->id, (int)$language->id);
        if ($path !== null) {
            return $this->buildUrl($path);
        }

        return $this->findConnectedUrl($element->id, $language->id);
    }

    public function getLanguageLinks(structureElement $element): array
    {
        $links = [];
        foreach ($this->languagesManager->getLanguagesList($this->configManager->get('main.rootMarkerPublic')) as $language) {
            $url = $this->getLinkForLanguage($element, $language->iso6393);
            if ($url !== null) {
                $links[$language->iso6391] = $url;
            }
        }
        return $links;
    }

    private function buildUrl(array $path): string
    {
        $urlName = $this->controller->getApplication()->getUrlName();
        $languageUrl = $urlName !== '' ? $urlName . '/' : '';
        return $this->controller->baseURL . $languageUrl . implode('/', $path) . '/';
    }

    private function findConnectedUrl($id, $languageId): string|null
    {
        if ($connectedIds = $this->linksManager->getConnectedIdList($id, 'foreignRelative', 'parent')) {
            foreach ($connectedIds as $connectedId) {
                if ($element = $this->structureManager->getElementById($connectedId, $languageId)) {
                    return $element->getUrl();
                }
            }
        }
        return null;
    }

}