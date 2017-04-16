<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\Selection
 */
class SelectionTest extends TestCase {

    public function testIsEmptyReturnsTrueOnEmptySelection() {
        $selection = new Selection(new \DOMNodeList());
        $this->assertTrue($selection->isEmpty());
    }

    public function testIsEmptyReturnsFalseOnNonEmptySelection() {
        $this->assertFalse($this->setupTestSelection()->isEmpty());
    }

    public function testIterationOverSelectionContentYielsExceptedNode() {
        foreach($this->setupTestSelection() as $child) {
            $this->assertInstanceOf(\DOMElement::class, $child);
            $this->assertEquals('child', $child->localName);
        }
    }

    /**
     * @return Selection
     */
    private function setupTestSelection(): Selection {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        return new Selection($dom->documentElement->childNodes);
    }

}
