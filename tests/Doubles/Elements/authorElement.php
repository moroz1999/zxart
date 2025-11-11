<?php
declare(strict_types=1);

// Global namespace doubles to match project class names

final class authorElement
{
    public ?string $realName = null;
    private string $title;
    private ?string $countryTitle = null;
    private ?string $cityTitle = null;
    /** @var groupElement[] */
    private array $groups = [];

    public function __construct(string $title)
    {
        $this->title = $title;
    }

    public function setRealName(?string $realName): void
    {
        $this->realName = $realName;
    }

    public function setCountryTitle(?string $countryTitle): void
    {
        $this->countryTitle = $countryTitle;
    }

    public function setCityTitle(?string $cityTitle): void
    {
        $this->cityTitle = $cityTitle;
    }

    public function addGroup(groupElement $group): void
    {
        $this->groups[] = $group;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getCountryTitle(): ?string
    {
        return $this->countryTitle;
    }

    public function getCityTitle(): ?string
    {
        return $this->cityTitle;
    }

    /**
     * @return groupElement[]
     */
    public function getGroupsList(): array
    {
        return $this->groups;
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
}
