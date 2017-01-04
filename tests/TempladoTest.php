<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

class TempladoTest extends TestCase {

    public function testCanBeConstructedFromString() {
        $this->assertInstanceOf(
            Page::class,
            Templado::parseString('<?xml version="1.0" ?><root />')
        );
    }

    public function testTryingToParseInvalidMarkupStringThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::parseString('<?xml version="1.0" ?><root>');
    }

    public function testTryingToLoadBrokenFileThrowsException() {
        $this->expectException(TempladoException::class);
        Templado::loadFile(new FileName(__DIR__ . '/_data/broken.txt'));
    }

    public function testCanBeConstructedFromFile() {
        $this->assertInstanceOf(
            Page::class,
            Templado::loadFile(new FileName(__DIR__ . '/_data/viewmodel/source.html'))
        );
    }

}
