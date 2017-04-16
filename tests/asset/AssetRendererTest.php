<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\AssetRenderer
 */
class AssetRendererTest extends TestCase {

    public function testSimpleElementGetsAdded() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $asset = $this->createMock(SimpleAsset::class);
        $asset->expects($this->once())->method('applyTo')->with($dom->documentElement->firstChild);

        $assetList = $this->createMock(AssetList::class);
        $assetList->method('valid')->willReturn(true, false);
        $assetList->method('current')->willReturn($asset);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);
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

    public function testRenderWorksRecursively() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"><subchild /></child></root>');

        $asset = $this->createMock(SimpleAsset::class);
        $asset->expects($this->once())->method('applyTo')->with($dom->documentElement->firstChild);

        $assetList = $this->createMock(AssetList::class);
        $assetList->method('valid')->willReturn(true, false);
        $assetList->method('current')->willReturn($asset);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

}
