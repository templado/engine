<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class SnapshotDOMNodelistTest extends TestCase {

    /** @var DOMDocument */
    private $dom;

    protected function setUp(): void {
        $this->dom = new DOMDocument('1.0');
        $this->dom->loadXML('<?xml version="1.0" ?><root><a/><b/></root>');
    }

    public function testIteratesOverNodes(): void {
        $DOMNodeList = $this->dom->documentElement->childNodes;
        $list        = new SnapshotDOMNodelist($DOMNodeList);

        foreach ($list as $pos => $item) {
            $this->assertSame(
                $DOMNodeList->item($pos),
                $item
            );
        }
    }

    public function testKeepsListOfNodesEvenIfTheyGetRemovedFromTheDocument(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);

        $root->removeChild($root->firstChild);
        $root->removeChild($root->firstChild);

        $count = 0;

        foreach ($list as $pos => $item) {
            $count++;
            $this->assertNull($item->parentNode);
        }

        $this->assertEquals(2, $count);
        $this->assertFalse($root->hasChildNodes());
    }

    public function testExistingNodeCanBeRemoved(): void {
        $root = $this->dom->documentElement;

        $list = new SnapshotDOMNodelist($root->childNodes);
        $list->removeNode($root->getElementsByTagName('b')->item(0));

        $count=0;

        foreach ($list as $pos => $item) {
            $count++;
        }
        $this->assertEquals(1, $count);
    }

    public function testTryingToRemoveNonExistingNodeThrowsException(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);

        $this->expectException(SnapshotDOMNodelistException::class);
        $list->removeNode($this->dom->createElement('not-in-list'));
    }

    public function testTestingForExistenceReturnsFalseOnNonExistingNode(): void {
        $list = new SnapshotDOMNodelist(new \DOMNodeList());
        $this->assertFalse($list->hasNode(new \DOMNode()));
    }

    public function testTestingForExistenceReturnsTrueOnExistingNode(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $this->assertTrue($list->hasNode($root->firstChild));
    }

    public function testTryingToGetCurrentOnEmptyThrowsException(): void {
        $list = new SnapshotDOMNodelist(new \DOMNodeList());
        $this->expectException(SnapshotDOMNodelistException::class);
        $this->expectExceptionMessage('No current node available');
        $list->current();
    }

    public function testCountOfNodesCanBeRetrieved(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $this->assertEquals(2, $list->count());
    }

    public function testNodeCanBeRetrievedByNext(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $node = $list->getNext();
        $this->assertSame($root->firstChild, $node);
    }

    public function testHasNextReturnsTrueAtBeginning(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $this->assertTrue($list->hasNext());
    }

    public function testHasNextReturnsFalseWhenEndIsReached(): void {
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $list->next();
        $list->next();
        $this->assertFalse($list->hasNext());
    }

    public function testRemovingNodeBeforeCurrentAdjustsPosition(): void {
        $this->dom->loadXML('<?xml version="1.0" ?><root><a/><b/><c/><d/></root>');
        $root = $this->dom->documentElement;
        $list = new SnapshotDOMNodelist($root->childNodes);
        $list->next();
        $list->removeNode($root->firstChild);
        $this->assertSame($root->firstChild->nextSibling, $list->current());
    }
}
