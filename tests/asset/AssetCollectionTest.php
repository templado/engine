<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

class AssetCollectionTest extends TestCase {

    /**
     * @var AssetCollection
     */
    private $collection;

    protected function setUp() {
        $this->collection = new AssetCollection();
    }

    public function testReturnsFalseWhenNoAssetWithGivenIdExists() {
        $this->assertFalse(
            $this->collection->hasAssetForId('abc')
        );
    }

    public function testReturnsTrueForExistingAsset() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Asset $asset */
        $asset = $this->createMock(Asset::class);
        $this->collection->addAsset('abc', $asset);
        $this->assertTrue(
            $this->collection->hasAssetForId('abc')
        );
    }

    public function testThrowsExceptionWhenTryingToRetrieveNonExistingAsset() {
        $this->expectException(AssetCollectionException::class);
        $this->collection->getAssetForId('abc');
    }

    public function testExistingAssetCanBeRetrieved() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Asset $asset */
        $asset = $this->createMock(Asset::class);
        $this->collection->addAsset('abc', $asset);
        $this->assertSame($asset, $this->collection->getAssetForId('abc'));
    }

}
