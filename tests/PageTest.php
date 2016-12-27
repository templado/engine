<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

class PageTest extends TestCase {

    public function testCanBeConstructedFromString() {
        $this->assertInstanceOf(
            Page::class,
            Page::fromString('<?xml version="1.0" ?><root />')
        );
    }

    public function testCanBeConstructedFromFile() {
        $this->assertInstanceOf(
            Page::class,
            Page::fromFile(new FileName(__DIR__ . '/_data/viewmodel/source.html'))
        );
    }

}
