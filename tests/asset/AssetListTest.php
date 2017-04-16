<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\AssetList
 */
class AssetListTest extends TestCase {

    public function testCanBeUsedAsIterator() {
        $expected = [
            $this->createMock(SimpleAsset::class),
            $this->createMock(SimpleAsset::class)
        ];
        $list     = new AssetList();
        $list->addAsset($expected[0]);
        $list->addAsset($expected[1]);

        foreach($list as $pos => $value) {
            $this->assertSame($expected[$pos], $value);
        }
    }

    public function testReturnsZeroForEmptyList() {
        $this->assertCount(0, new AssetList());
    }

    public function testReturnsCorrectCountForNonEmptyList() {
        $list = new AssetList();
        $list->addAsset($this->createMock(SimpleAsset::class));
        $this->assertCount(1, $list);
    }

}
