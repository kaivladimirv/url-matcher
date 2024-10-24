<?php

declare(strict_types=1);

namespace Tests\Weew\UrlMatcher;

use PHPUnit\Framework\TestCase;
use Weew\UrlMatcher\MatchPattern;

class MatchPatternTest extends TestCase
{
    public function test_getters(): void
    {
        $pattern = new MatchPattern('foo', '[0-9]+');
        $this->assertEquals('foo', $pattern->getName());
        $this->assertEquals('[0-9]+', $pattern->getPattern());
    }
}
