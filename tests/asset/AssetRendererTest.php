<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\AssetRenderer
 */
class AssetRendererTest extends TestCase {

    public function testSimpleElementGetsAdded() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $asset = $this->createMock(SimpleAsset::class);
        $asset->method('getContent')->willReturn(
            new DOMElement('test')
        );

        $assetList = $this->createMock(AssetList::class);
        $assetList->method('valid')->willReturn(true, false);
        $assetList->method('current')->willReturn($asset);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><child id="a"><test/></child></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testElemenGetsReplacedIfConfiguredToDoSo() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $dom2    = new \DOMDocument();
        $element = $dom2->createElement('test');
        $element->setAttribute('id', 'a');

        $asset = $this->createMock(SimpleAsset::class);
        $asset->method('getContent')->willReturn(
            $element
        );
        $asset->method('replaceCurrent')->willReturn('true');

        $assetList = $this->createMock(AssetList::class);
        $assetList->method('valid')->willReturn(true, false);
        $assetList->method('current')->willReturn($asset);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

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

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(false);

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

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(false);

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
