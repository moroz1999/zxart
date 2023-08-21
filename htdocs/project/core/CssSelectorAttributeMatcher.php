<?php

declare(strict_types=1);

namespace Sobak\Scrawler\Block\Matcher;

use Sobak\Scrawler\Support\Utils;
use Symfony\Component\CssSelector\CssSelectorConverter;

class CssSelectorAttributeMatcher extends AbstractMatcher implements SingleMatcherInterface
{
    private static $cache;

    protected $converter;
    protected $attribute;

    /**
     * @param string $attribute
     */
    public function attribute(string $attribute): CssSelectorAttributeMatcher
    {
        $this->attribute = $attribute;
        return $this;
    }

    public function __construct(string $matchBy)
    {
        parent::__construct($matchBy);
        $this->converter = new CssSelectorConverter();
    }

    public function match(): ?string
    {
        if (isset(self::$cache[$this->getMatchBy()]) === false) {
            self::$cache[$this->getMatchBy()] = $this->converter->toXPath($this->getMatchBy());
        }

        $result = $this->getCrawler()->filterXPath(self::$cache[$this->getMatchBy()]);

        return $result->count() === 0 ? null : Utils::trimWhitespace($result->attr($this->attribute));
    }
}
