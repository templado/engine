<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNamedNodeMap;
use PHPUnit\Framework\TestCase;

class SnapshotAttributeListTest extends TestCase {

    /** @var DOMNamedNodeMap */
    private $map;

    /** @var SnapshotAttributeList */
    private $list;

    protected function setUp(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root a="va" b="vb" c="vc" />');
        $this->map  = $dom->documentElement->attributes;
        $this->list = new SnapshotAttributeList($this->map);
    }

    public function testIteratesOverAllAttributes(): void {
        foreach ($this->list as $pos => $attr) {
            $this->assertSame(
                $this->map->item($pos),
                $attr
            );
        }
    }

    public function testCountCanBeRetrieved(): void {
        $this->assertCount(3, $this->list);
    }

    public function testMapWithNonAttributesThrowsException(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><!DOCTYPE test [ <!ENTITY foo "Entity value"> ]><test />');

        $this->expectException(SnapshotAttributeListException::class);
        new SnapshotAttributeList($dom->doctype->entities);
    }

    public function testGettingCurrentAttributeWhenNoneIsAvailableThrowsException(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');
        $map  = $dom->documentElement->attributes;
        $list = new SnapshotAttributeList($map);
        $this->expectException(SnapshotAttributeListException::class);
        $list->current();
    }
}
