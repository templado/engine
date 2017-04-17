<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class TextAssetTest extends TestCase {

    public function testCanBeConstructedWithTextNode() {
        $this->assertInstanceOf(
            TextAsset::class,
            new TextAsset('id', new \DOMText('test'))
        );
    }

}
