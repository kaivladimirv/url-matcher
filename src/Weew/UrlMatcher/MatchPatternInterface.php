<?php

declare(strict_types=1);

namespace Weew\UrlMatcher;

interface MatchPatternInterface
{
    public function getName(): string;

    public function getPattern(): string;

    public function getRegexName(): string;

    public function getRegexPattern(): string;
}
