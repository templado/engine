<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\AssetListCollection
 */
class AssetListCollectionTest extends TestCase {

    /**
     * @var AssetListCollection
     */
    private $collection;

    protected function setUp() {
        $this->collection = new AssetListCollection();
    }

    public function testReturnsFalseWhenNoAssetWithGivenIdExists() {
        $this->assertFalse(
            $this->collection->hasAssetsForId('abc')
        );
    }

    /**
     * @uses \Templado\Engine\AssetList
     */
    public function testReturnsTrueForExistingAsset() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Asset $asset */
        $asset = $this->createMock(SimpleAsset::class);
        $asset->method('getTargetId')->willReturn('abc');
        $this->collection->addAsset($asset);
        $this->assertTrue(
            $this->collection->hasAssetsForId('abc')
        );
    }

    public function testThrowsExceptionWhenTryingToRetrieveNonExistingAsset() {
        $this->expectException(AssetCollectionException::class);
        $this->collection->getAssetsForId('abc');
    }

    /**
     * @uses \Templado\Engine\AssetList
     */
    public function testExistingAssetCanBeRetrieved() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Asset $asset */
        $asset = $this->createMock(SimpleAsset::class);
        $asset->method('getTargetId')->willReturn('abc');
        $this->collection->addAsset($asset);
        $result = $this->collection->getAssetsForId('abc');
        $this->assertInstanceOf(AssetList::class, $result);
        $this->assertCount(1, $result);
    }

}
