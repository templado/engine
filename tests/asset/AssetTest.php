<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\Asset
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
        $asset = new Asset('abc', $this->domElement);
        $this->assertSame($this->domElement, $asset->getContent());
    }

    public function testIdCheckReturnsFalseForDomNode() {
        $asset = new Asset('abc', new DOMNode());
        $this->assertFalse($asset->hasContentWithId());
    }

    public function testIdCheckReturnsFalseWhenNoContentIdIsSet() {
        $asset = new Asset('abc', new DOMElement('test'));
        $this->assertFalse($asset->hasContentWithId());
    }

    public function testIdCheckReturnsTrueWhenContentIdIsSet() {
        $asset = new Asset('abc', $this->domElement);
        $this->assertTrue($asset->hasContentWithId());
    }

    public function testIdCanBeRetrieved() {
        $asset = new Asset('foo', $this->domElement);
        $this->assertEquals('abc', $asset->getContentId());
    }

    public function testTryingToGetNotExistingIdThrowsException() {
        $asset = new Asset('foo', new DOMNode());
        $this->expectException(AssetException::class);
        $asset->getContentId();
    }

    public function testRelationSelectorCanBeRetrieved() {
        $selector = $this->createMock(Selector::class);
        $asset = new Asset('foo',
            new DOMNode(),
            $selector
        );
        $this->assertSame($selector, $asset->getRelation());
    }

    public function testHasRelationReturnsTrueIfSelectorWasSet() {
        $asset = new Asset('foo',
            new DOMNode(),
            $this->createMock(Selector::class)
        );
        $this->assertTrue($asset->hasRelation());
    }

    public function testHasRelationReturnsFalseIfNoSelectorWasSet() {
        $asset = new Asset('foo',new DOMNode());
        $this->assertFalse($asset->hasRelation());
    }

    public function testTargetIdCanBeRetrieved() {
        $asset = new Asset('foo',new DOMNode());
        $this->assertEquals('foo', $asset->getTargetId());
    }

}

