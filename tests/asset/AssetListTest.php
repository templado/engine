<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

class AssetListTest extends TestCase {

    public function testCanBeUsedAsIterator() {
        $expected = [
            $this->createMock(Asset::class),
            $this->createMock(Asset::class)
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
        $list->addAsset($this->createMock(Asset::class));
        $this->assertCount(1, $list);
    }

}
