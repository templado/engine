<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\Templado
 */
class TempladoTest extends TestCase {

    /**
     * @uses \Templado\Engine\Page
     */
    public function testCanBeConstructedFromString() {
        $this->assertInstanceOf(
            Page::class,
            Templado::parseString('<?xml version="1.0" ?><root />')
        );
    }

    /**
     * @uses \Templado\Engine\TempladoException
     */
    public function testTryingToParseInvalidMarkupStringThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::parseString('<?xml version="1.0" ?><root>');
    }

    /**
     * @uses \Templado\Engine\TempladoException
     * @uses \Templado\Engine\FileName
     */
    public function testTryingToLoadBrokenFileThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::loadFile(new FileName(__DIR__ . '/_data/broken.txt'));
    }

    /**
     * @uses \Templado\Engine\FileName
     * @uses \Templado\Engine\Page
     */
    public function testCanBeConstructedFromFile() {
        $this->assertInstanceOf(
            Page::class,
            Templado::loadFile(new FileName(__DIR__ . '/_data/viewmodel/source.html'))
        );
    }

}
