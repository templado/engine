<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\AssetRenderer
 */
class AssetRendererTest extends TestCase {

    public function testSimpleElementGetsAdded() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');
        $collection = $this->createMocksForDom($dom);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    public function testMissingIdGetsIgnored() {
        $xml = '<?xml version="1.0" ?><root><child id="a"/></root>';

        $dom = new DOMDocument();
        $dom->loadXML($xml);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(false);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new DOMDocument();
        $expected->loadXML($xml);

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testNonElementNodesGetIgnored() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(false);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><!-- comment --></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testRenderWorksRecursively() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"><subchild /></child></root>');

        $collection = $this->createMocksForDom($dom);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    public function testRenderingWorksRecursivelyOverAssetReplacedElements() {
        $page = new DOMDocument();
        $page->loadXML('<?xml version="1.0" ?><root><target id="a" /></root>');

        $dom1 = new DOMDocument();
        $dom1->loadXML('<?xml version="1.0" ?><child id="a"><subchild id="b" /></child>');
        $asset1 = $this->createMock(Asset::class);
        $asset1->expects($this->once())->method('applyTo')
            ->with($page->documentElement->firstChild)
            ->willReturn($dom1->documentElement);

        $dom2 = new DOMDocument();
        $dom2->loadXML('<?xml version="1.0" ?><replacement id="b"><Nested in="b" /></replacement>');
        $asset2 = $this->createMock(Asset::class);
        $asset2->expects($this->once())->method('applyTo')
            ->with($dom1->documentElement->firstChild)
            ->willReturn($dom2->documentElement);

        $assetList1 = $this->createAssetListMock($asset1);
        $assetList2 = $this->createAssetListMock($asset2);

        $collection = $this->createMock(AssetListCollection::class);
        $collection->expects($this->exactly(2))->method('hasAssetsForId')->withConsecutive(['a'],['b'])->willReturn(true);
        $collection->method('getAssetsForId')->willReturnOnConsecutiveCalls(
            $assetList1,
            $assetList2
        );

        $renderer = new AssetRenderer($collection);
        $renderer->render($page->documentElement);

    }

    public function testNonElementReplacementtGetsHandled() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $asset = $this->createMock(Asset::class);
        $asset->expects($this->once())->method('applyTo')
            ->with($dom->documentElement->firstChild)
            ->willReturn($dom->createTextNode('replacement-text'));

        $assetList = $this->createAssetListMock($asset);
        $collection = $this->createAssetCollectionMock($assetList);

        $renderer = new AssetRenderer($collection);
        $renderer->render($dom->documentElement);
    }

    /**
     * @param DOMDocument $dom
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAssetMock(DOMDocument $dom): \PHPUnit_Framework_MockObject_MockObject {
        $asset = $this->createMock(Asset::class);
        $asset->expects($this->once())->method('applyTo')
            ->with($dom->documentElement->firstChild)
            ->willReturn($dom->documentElement->firstChild);

        return $asset;
    }

    /**
     * @param $asset
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function createAssetListMock($asset): \PHPUnit_Framework_MockObject_MockObject {
        $assetList = $this->createMock(AssetList::class);
        $assetList->method('valid')->willReturn(true, false);
        $assetList->method('current')->willReturn($asset);

        return $assetList;
    }

    /**
     * @param $assetList
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AssetListCollection
     */
    private function createAssetCollectionMock($assetList) {
        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

        return $collection;
    }

    /**
     * @param $dom
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|AssetListCollection
     */
    private function createMocksForDom($dom) {
        $asset = $this->createAssetMock($dom);
        $assetList = $this->createAssetListMock($asset);
        $collection = $this->createAssetCollectionMock($assetList);

        return $collection;
    }

}
