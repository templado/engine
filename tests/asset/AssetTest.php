<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\Asset
 */
class AssetTest extends TestCase {

    /** @var  DOMNode */
    private $domNode;

    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root id="abc" />');
        $this->domNode = $dom->documentElement;
    }

    public function testNodeCanBeRetrieved() {
        $asset = new Asset($this->domNode);
        $this->assertSame($this->domNode, $asset->getNode());
    }

    public function testIdCheckReturnsFalseForDomNode() {
        $asset = new Asset(new DOMNode());
        $this->assertFalse($asset->hasId());
    }

    public function testIdCheckReturnsFalseWhenNoIdIsSet() {
        $asset = new Asset(new DOMElement('test'));
        $this->assertFalse($asset->hasId());
    }

    public function testIdCheckReturnsTrueWhenIdIsSet() {
        $asset = new Asset($this->domNode);
        $this->assertTrue($asset->hasId());
    }

    public function testIdCanBeRetrieved() {
        $asset = new Asset($this->domNode);
        $this->assertEquals('abc', $asset->getId());
    }

    public function testTryingToGetNotExistingIdThrowsException() {
        $asset = new Asset(new DOMNode());
        $this->expectException(AssetException::class);
        $asset->getId();
    }

}

