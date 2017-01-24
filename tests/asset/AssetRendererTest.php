<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use PHPUnit\Framework\TestCase;

class AssetRendererTest extends TestCase {

    public function testSimpleElementGetsAdded() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $asset = $this->createMock(Asset::class);
        $asset->method('getNode')->willReturn(
            new DOMElement('test')
        );

        /** @var AssetCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetCollection::class);
        $collection->method('hasAssetForId')->willReturn(true);
        $collection->method('getAssetForId')->willReturn($asset);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><child id="a"><test/></child></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testElemenGetsReplacedIfAssetHasId() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $dom2    = new \DOMDocument();
        $element = $dom2->createElement('test');
        $element->setAttribute('id', 'a');

        $asset = $this->createMock(Asset::class);
        $asset->method('getNode')->willReturn(
            $element
        );
        $asset->method('hasId')->willReturn('true');
        $asset->method('getId')->willReturn('a');

        /** @var AssetCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetCollection::class);
        $collection->method('hasAssetForId')->willReturn(true);
        $collection->method('getAssetForId')->willReturn($asset);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><test id="a" /></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testMissingIdGetsIgnored() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        /** @var AssetCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetCollection::class);
        $collection->method('hasAssetForId')->willReturn(false);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testNonElementNodesGetIgnored() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        /** @var AssetCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetCollection::class);
        $collection->method('hasAssetForId')->willReturn(false);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

}
