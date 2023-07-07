<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SnippetLoader
 */
class SnippetLoaderTest extends TestCase {
    public function testAttemptingToLoadNonExistingFileThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(false);

        $this->expectException(SnippetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadSomethingThatIsNotAFileThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(false);

        $this->expectException(SnippetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadANotReadableFileThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(false);

        $this->expectException(SnippetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnInvalidFileThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('broken.txt');

        $this->expectException(SnippetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnUnknownFileTypeThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('undefined.xml');

        $this->expectException(SnippetLoaderException::class);
        $loader->load($filename);
    }

    /**
     * @uses \Templado\Engine\SimpleSnippet
     */
    public function testLoadingPlainHtmlReturnsValidSnippet(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('simple.xhtml');
        $snippet  = $loader->load($filename);

        $this->assertInstanceOf(SimpleSnippet::class, $snippet);
        $this->assertEquals('abc', $snippet->getTargetId());
    }

    /**
     * @uses \Templado\Engine\SimpleSnippet
     */
    public function testLoadingHtmlWithoutIdUsesFilename(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('noid.xhtml');
        $snippet  = $loader->load($filename);

        $this->assertInstanceOf(SimpleSnippet::class, $snippet);
        $this->assertEquals('noid', $snippet->getTargetId());
    }

    /**
     * @uses \Templado\Engine\TempladoSnippet
     */
    public function testLoadingTempladoSnippetFileReturnsCorrectSnippetType(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('snippet.xml');
        $snippet  = $loader->load($filename);

        $this->assertInstanceOf(TempladoSnippet::class, $snippet);
        $this->assertEquals('header', $snippet->getTargetId());
    }

    public function testLoadingFileWithUnsupportedMimeTypeThrowsException(): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename('', 'unknown/binary');
        $this->expectException(SnippetLoaderException::class);
        $snippet = $loader->load($filename);
    }

    /**
     * @uses \Templado\Engine\TextSnippet
     * @uses \Templado\Engine\SimpleSnippet
     * @dataProvider textFileFilenameProvider
     */
    public function testLoadingATextFileCreatesATextnodeSnippet($name, $mimetype): void {
        $loader   = new SnippetLoader();
        $filename = $this->createMockFilename($name, $mimetype);
        $filename->method('getName')->willReturn('simple');

        $snippet = $loader->load($filename);

        $this->assertInstanceOf(TextSnippet::class, $snippet);
        $this->assertEquals('simple', $snippet->getTargetId());
    }

    public static function textFileFilenameProvider(): array {
        return [
            'text' => ['simple.txt', 'text/plain'],
            'php'  => ['simple.php', 'text/x-php']
        ];
    }

    /**
     * @param $name
     *
     * @return FileName|PHPUnit_Mock_Object_Object
     */
    private function createMockFilename(string $name, string $mimetype = 'text/xml'): FileName {
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('getMimeType')->willReturn($mimetype);
        $filename->method('asString')->willReturn(
            __DIR__ . '/../_data/snippets/' . $name
        );

        return $filename;
    }
}
