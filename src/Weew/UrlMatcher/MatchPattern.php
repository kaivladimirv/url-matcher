<?php

declare(strict_types=1);

namespace Weew\UrlMatcher;

class MatchPattern implements MatchPatternInterface
{
    private string $name;
    private string $pattern;
    private string $regexName;
    private string $regexPattern;

    public function __construct(string $name, string $pattern, bool $optional = false)
    {
        $this->name = $name;
        $this->pattern = $pattern;
        $this->regexName = $this->createRegexName($name, $optional);
        $this->regexPattern = $this->createRegexPattern($pattern, $optional);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPattern(): string
    {
        return $this->pattern;
    }

    public function getRegexName(): string
    {
        return $this->regexName;
    }

    public function getRegexPattern(): string
    {
        return $this->regexPattern;
    }

    private function createRegexName(string $name, bool $optional): string
    {
        return $optional
            ? '#\{' . preg_quote($name) . '\?\}#'
            : '#\{' . preg_quote($name) . '\}#';
    }

    private function createRegexPattern(string $pattern, bool $optional): string
    {
        return $optional
            ? '(' . $pattern . ')?'
            : '(' . $pattern . ')';
    }
}
