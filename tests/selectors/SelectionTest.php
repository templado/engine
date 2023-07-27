<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\TestCase;

#[CoversClass(Selection::class)]
#[Small]
class SelectionTest extends TestCase {
    public function testCountReturnsCorrectNumberOfItemsInSelection(): void {
        $this->assertCount(1, $this->setupTestSelection());
    }

    public function testIsEmptyReturnsTrueOnEmptySelection(): void {
        $selection = new Selection(new \DOMNodeList());
        $this->assertCount(0, $selection);
        $this->assertTrue($selection->isEmpty());
    }

    public function testIsEmptyReturnsFalseOnNonEmptySelection(): void {
        $this->assertFalse($this->setupTestSelection()->isEmpty());
    }

    public function testIterationOverSelectionContentYielsExceptedNode(): void {
        foreach ($this->setupTestSelection() as $child) {
            $this->assertInstanceOf(\DOMElement::class, $child);
            $this->assertEquals('child', $child->localName);
        }
    }

    private function setupTestSelection(): Selection {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        return new Selection($dom->documentElement->childNodes);
    }
}
