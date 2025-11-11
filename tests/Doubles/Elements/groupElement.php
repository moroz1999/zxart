<?php
declare(strict_types=1);

final class groupElement
{
    private string $title;
    private ?string $countryTitle = null;
    private ?string $cityTitle = null;
    /** @var array<int, array{authorElement: authorElement}> */
    private array $authorsInfo = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setCountryTitle(?string $countryTitle): void
    {
        $this->countryTitle = $countryTitle;
    }

    public function setCityTitle(?string $cityTitle): void
    {
        $this->cityTitle = $cityTitle;
    }

    public function getCountryTitle(): ?string
    {
        return $this->countryTitle;
    }

    public function matchesCountry(string $countryTitle): bool
    {
        if ($countryTitle === '') {
            return false;
        }
        return $this->countryTitle !== null && strcasecmp($this->countryTitle, $countryTitle) === 0;
    }

    public function matchesCity(string $cityTitle): bool
    {
        if ($cityTitle === '') {
            return false;
        }
        return $this->cityTitle !== null && strcasecmp($this->cityTitle, $cityTitle) === 0;
    }

    /**
     * @return array<int, array{authorElement: authorElement}>
     */
    public function getAuthorsInfo(string $context): array
    {
        return $this->authorsInfo;
    }

    public function addAuthor(authorElement $author): void
    {
        $this->authorsInfo[] = ['authorElement' => $author];
    }
}
