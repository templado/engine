<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\Templado
 */
class TempladoTest extends TestCase {

    /**
     * @uses \Templado\Engine\Html
     */
    public function testCanBeConstructedFromString(): void {
        $this->assertInstanceOf(
            Html::class,
            Templado::parseHtmlString('<?xml version="1.0" ?><root />')
        );
    }

    /**
     * @uses \Templado\Engine\TempladoException
     */
    public function testTryingToParseInvalidMarkupStringThrowsException(): void {
        $this->expectException(TempladoException::class);
        $this->expectExceptionMessageMatches('/^Parsing string failed:\R.*Line.*Column.*$/m');

        Templado::parseHtmlString('<?xml version="1.0" ?><root>');
    }

    /**
     * @uses \Templado\Engine\TempladoException
     * @uses \Templado\Engine\FileName
     */
    public function testTryingToLoadBrokenFileThrowsException(): void {
        $this->expectException(TempladoException::class);
        $this->expectExceptionMessageMatches('/^Loading file.*failed:\R.*Line.*Column.*$/m');

        Templado::loadHtmlFile(new FileName(__DIR__ . '/_data/broken.txt'));
    }

    /**
     * @uses \Templado\Engine\FileName
     * @uses \Templado\Engine\Html
     */
    public function testCanBeConstructedFromFile(): void {
        $this->assertInstanceOf(
            Html::class,
            Templado::loadHtmlFile(new FileName(__DIR__ . '/_data/viewmodel/source.html'))
        );
    }
}
