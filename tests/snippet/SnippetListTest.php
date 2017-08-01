<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SnippetList
 */
class SnippetListTest extends TestCase {

    public function testCanBeUsedAsIterator() {
        $expected = [
            $this->createMock(SimpleSnippet::class),
            $this->createMock(SimpleSnippet::class)
        ];
        $list     = new SnippetList();
        $list->addSnippet($expected[0]);
        $list->addSnippet($expected[1]);

        foreach($list as $pos => $value) {
            $this->assertSame($expected[$pos], $value);
        }
    }

    public function testReturnsZeroForEmptyList() {
        $this->assertCount(0, new SnippetList());
    }

    public function testReturnsCorrectCountForNonEmptyList() {
        $list = new SnippetList();
        $list->addSnippet($this->createMock(SimpleSnippet::class));
        $this->assertCount(1, $list);
    }

}
