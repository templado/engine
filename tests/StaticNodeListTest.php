<?php declare(strict_types = 1);
namespace templado\engine;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(StaticNodeList::class)]
class StaticNodeListTest extends TestCase {

    public function testCanBeCreatedFromNodeList(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<?xml version="1.0" ?><root><node1 /><node2 /><node3 /></root>');

        $expected= ['node1', 'node2', 'node3'];

        $list = StaticNodeList::fromNodeList($dom->documentElement->childNodes);

        $this->assertCount(3, $list);
        foreach($list as $pos => $node) {
            $this->assertEquals($expected[$pos], $node->localName);
        }
    }

    public function testCanBeCreatedFromNamedNodeMap(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<?xml version="1.0" ?><root attr1="" attr2="" attr3="" />');

        $expected= ['attr1', 'attr2', 'attr3'];

        $list = StaticNodeList::fromNamedNodeMap($dom->documentElement->attributes);

        $this->assertCount(3, $list);
        $pos = 0;
        foreach($list as $attr) {
            $this->assertEquals($expected[$pos], $attr->nodeName);
            $pos++;
        }

    }
}
