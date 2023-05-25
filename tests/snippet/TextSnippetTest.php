<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class TextSnippetTest extends TestCase {
    public function testCanBeConstructedWithTextNode(): void {
        $this->assertInstanceOf(
            TextSnippet::class,
            new TextSnippet('id', new \DOMText('test'))
        );
    }
}
