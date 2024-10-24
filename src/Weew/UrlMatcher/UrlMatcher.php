<?php

declare(strict_types=1);

namespace Weew\UrlMatcher;

use Weew\Collections\Dictionary;
use Weew\Collections\DictionaryInterface;

class UrlMatcher implements UrlMatcherInterface
{
    /**
     * @var MatchPatternInterface[]
     */
    private array $patterns = [];

    public function __construct()
    {
        $this->addDefaultPatterns();
    }

    /**
     * @param array<string, string> $patterns
     */
    public function match(string $pattern, string $path, array $patterns = []): bool
    {
        $patterns = $this->mergeWithLocalPatterns($patterns);

        $path = $this->addTrailingSlash($path);
        $pattern = $this->createRegexPattern($pattern, $patterns);
        $matches = [];

        if (preg_match_all($pattern, $path, $matches) === 1) {
            $matchedPath = $this->addTrailingSlash(array_get($matches, '0.0'));

            return $matchedPath == $path;
        }

        return false;
    }

    /**
     * @param array<string, string> $patterns
     */
    public function parse(string $pattern, string $path, array $patterns = []): DictionaryInterface
    {
        $patterns = $this->mergeWithLocalPatterns($patterns);
        $names = $this->extractParameterNames($pattern);
        $values = $this->extractParameterValues($pattern, $path, $patterns);
        $parameters = array_combine($names, array_pad($values, count($names), null));

        return new Dictionary($parameters);
    }

    public function replace(string $path, string $key, string $value): string
    {
        return str_replace(s('{%s}', $key), $value, $path);
    }

    public function replaceAll(string $path, array $replacements): string
    {
        foreach ($replacements as $key => $value) {
            $path = $this->replace($path, $key, $value);
        }

        return $path;
    }

    /**
     * @return MatchPatternInterface[]
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    /**
     * @param array<array-key, MatchPatternInterface> $patterns
 */
    public function setPatterns(array $patterns): void
    {
        $this->patterns = $patterns;
    }

    public function addPattern(string $name, string $pattern): void
    {
        array_unshift($this->patterns, $this->createPattern($name, $pattern));
        array_unshift($this->patterns, $this->createPattern($name, $pattern, true));
    }

    private function createRegexPattern(string $path, array $patterns = []): string
    {
        $pattern = $this->applyCustomRegexPatterns($path, $patterns);
        $pattern = $this->applyStandardRegexPatterns($pattern);

        return s('#%s#', $pattern);
    }

    private function applyCustomRegexPatterns(string $path, array $patterns): string
    {
        foreach ($patterns as $pattern) {
            $path = preg_replace([$pattern->getRegexName()], $pattern->getRegexPattern(), $path);
        }

        return $path;
    }

    private function applyStandardRegexPatterns(string $path): string
    {
        $pattern = preg_replace('#\{([a-zA-Z0-9_-]+)\?}#', '([^/]+)?', $path);
        return preg_replace('#\{([a-zA-Z0-9_-]+)}#', '([^/]+)', $pattern);
    }

    private function extractParameterNames(string $path): array
    {
        $names = [];
        $matches = [];
        preg_match_all('#\{([a-zA-Z0-9?]+)}#', $path, $matches);

        foreach (array_get($matches, 1, []) as $name) {
            $names[] = str_replace('?', '', $name);
        }

        return $names;
    }

    /**
     * @param MatchPatternInterface[] $patterns
     */
    private function extractParameterValues(string $pattern, string $path, array $patterns = []): array
    {
        $path = $this->addTrailingSlash($path);
        $matches = [];

        $pattern = $this->createRegexPattern($pattern, $patterns);
        preg_match_all($pattern, $path, $matches);
        array_shift($matches);

        return $this->processParameterValues($matches);
    }

    private function processParameterValues(array $matches): array
    {
        $values = [];

        foreach ($matches as $group) {
            if (is_array($group)) {
                foreach ($group as $value) {
                    if ($value == '') {
                        $value = null;
                    } else {
                        $value = $this->removeTrailingSlash($value);
                    }

                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    private function addTrailingSlash(string $string): string
    {
        if (!str_ends_with($string, '/')) {
            $string .= '/';
        }

        return $string;
    }

    private function removeTrailingSlash(string $string): string
    {
        if (str_ends_with($string, '/')) {
            $string = substr($string, 0, -1);
        }

        return $string;
    }

    /**
     * @return MatchPatternInterface[]
     */
    private function mergeWithLocalPatterns(array $patterns): array
    {
        $mergedPatterns = $this->patterns;

        foreach ($patterns as $name => $pattern) {
            array_unshift($mergedPatterns, $this->createPattern($name, $pattern));
            array_unshift($mergedPatterns, $this->createPattern($name, $pattern, true));
        }

        return $mergedPatterns;
    }

    private function createPattern(string $name, string $pattern, bool $optional = false): MatchPatternInterface
    {
        return new MatchPattern($name, $pattern, $optional);
    }

    /**
     * Register default patterns.
     */
    private function addDefaultPatterns(): void
    {
        $this->addPattern('any', '.+');
    }
}
