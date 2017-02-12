<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\SimpleAsset
 */
class AssetTest extends TestCase {

    /** @var  DOMElement */
    private $domElement;

    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root id="abc" />');
        $this->domElement = $dom->documentElement;
    }

    public function testNodeCanBeRetrieved() {
        $asset = new SimpleAsset('abc', $this->domElement);
        $this->assertSame($this->domElement, $asset->getContent());
    }

    public function testTargetIdCanBeRetrieved() {
        $asset = new SimpleAsset('foo', new DOMNode());
        $this->assertEquals('foo', $asset->getTargetId());
    }

}

