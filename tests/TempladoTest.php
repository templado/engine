<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\Templado
 */
class TempladoTest extends TestCase {

    /**
     * @uses \TheSeer\Templado\Page
     */
    public function testCanBeConstructedFromString() {
        $this->assertInstanceOf(
            Page::class,
            Templado::parseString('<?xml version="1.0" ?><root />')
        );
    }

    /**
     * @uses \TheSeer\Templado\TempladoException
     */
    public function testTryingToParseInvalidMarkupStringThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::parseString('<?xml version="1.0" ?><root>');
    }

    /**
     * @uses \TheSeer\Templado\TempladoException
     * @uses \TheSeer\Templado\FileName
     */
    public function testTryingToLoadBrokenFileThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::loadFile(new FileName(__DIR__ . '/_data/broken.txt'));
    }

    /**
     * @uses \TheSeer\Templado\FileName
     * @uses \TheSeer\Templado\Page
     */
    public function testCanBeConstructedFromFile() {
        $this->assertInstanceOf(
            Page::class,
            Templado::loadFile(new FileName(__DIR__ . '/_data/viewmodel/source.html'))
        );
    }

}
