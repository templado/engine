<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SimpleAsset
 */
class SimpleAssetTest extends TestCase {

    /** @var  DOMElement */
    private $domElement;

    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root id="abc" />');
        $this->domElement = $dom->documentElement;
    }

    public function testTargetIdCanBeRetrieved() {
        $asset = new SimpleAsset('foo', new DOMNode());
        $this->assertEquals('foo', $asset->getTargetId());
    }

    public function testAssetGetsAddedAsChild() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node/>');
        $asset = new SimpleAsset('abc', $dom->documentElement);

        $this->assertEquals(0, $this->domElement->childNodes->length);
        $asset->applyTo($this->domElement);
        $this->assertEquals(1, $this->domElement->childNodes->length);
    }

    public function testAssetReplacesContent() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');
        $asset = new SimpleAsset('abc', $dom->documentElement);

        $asset->applyTo($this->domElement);
        $this->assertEquals('node', $this->domElement->ownerDocument->documentElement->nodeName);
    }

    public function testAssetWithNonElementContentDoesNotReplaceContent() {
        $fragment = (new \DOMDocument())->createDocumentFragment();
        $fragment->appendXML('<node1 /><node2 />');

        $asset = new SimpleAsset('abc', $fragment);
        $asset->applyTo($this->domElement);
        $this->assertEquals(2, $this->domElement->childNodes->length);

    }

}

