<?php

declare(strict_types=1);

namespace Weew\UrlMatcher;

use Weew\Collections\DictionaryInterface;

interface UrlMatcherInterface
{
    /**
     * @param array<string, string> $patterns
     */
    public function match(string $pattern, string $path, array $patterns = []): bool;

    /**
     * @param array<string, string> $patterns
     */
    public function parse(string $pattern, string $path, array $patterns = []): DictionaryInterface;

    public function replace(string $path, string $key, string $value): string;

    public function replaceAll(string $path, array $replacements): string;

    /**
     * @return MatchPatternInterface[]
     */
    public function getPatterns(): array;

    /**
     * @param array<array-key, MatchPatternInterface> $patterns
     */
    public function setPatterns(array $patterns): void;

    public function addPattern(string $name, string $pattern): void;
}
