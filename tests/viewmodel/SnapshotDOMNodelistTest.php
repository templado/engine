<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class SnapshotDOMNodelistTest extends TestCase {

    /**
     * @var DOMDocument
     */
    private $dom;

    protected function setUp() {
        $this->dom = new DOMDocument('1.0');
        $this->dom->loadXML('<?xml version="1.0" ?><root><a/><b/></root>');
    }

    public function testIteratesOverNodes() {
        $DOMNodeList = $this->dom->documentElement->childNodes;
        $list = new SnapshotDOMNodelist($DOMNodeList);

        foreach($list as $pos => $item) {
            $this->assertSame(
                $DOMNodeList->item($pos),
                $item
            );
        }
    }

    public function testKeepsListOfNodesEvenIfTheyGetRemovedFromTheDocument() {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);

        $root->removeChild($root->firstChild);
        $root->removeChild($root->firstChild);

        $count = 0;
        foreach($list as $pos => $item) {
            $count++;
            $this->assertNull($item->parentNode);
        }

        $this->assertEquals(2, $count);
        $this->assertFalse($root->hasChildNodes());
    }

}
