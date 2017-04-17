<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\AssetLoader
 */
class AssetLoaderTest extends TestCase {

    public function testAttemptingToLoadNonExistingFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(false);

        $this->expectException(AssetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadSomethingThatIsNotAFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(false);

        $this->expectException(AssetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadANotReadableFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(false);

        $this->expectException(AssetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnInvalidFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('broken.txt');

        $this->expectException(AssetLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnUnknownFileTypeThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('undefined.xml');

        $this->expectException(AssetLoaderException::class);
        $loader->load($filename);
    }

    /**
     * @uses \Templado\Engine\SimpleAsset
     */
    public function testLoadingPlainHtmlReturnsValidAsset() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('simple.xhtml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('abc', $asset->getTargetId());
    }

    /**
     * @uses \Templado\Engine\SimpleAsset
     */
    public function testLoadingHtmlWithoutIdUsesFilename() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('noid.xhtml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('noid', $asset->getTargetId());
    }

    /**
     * @uses \Templado\Engine\SimpleAsset
     */
    public function testLoadingAssetFileReturnsValidAsset() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('asset.xml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('header', $asset->getTargetId());
    }

    public function testLoadingFileWithUnsupportedMimeTypeThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename('', 'unknown/binary');
        $this->expectException(AssetLoaderException::class);
        $asset = $loader->load($filename);
    }

    /**
     * @uses \Templado\Engine\TextAsset
     * @uses \Templado\Engine\SimpleAsset
     * @dataProvider textFileFilenameProvider
     */
    public function testLoadingATextFileCreatesATextnodeAsset($name, $mimetype) {
        $loader   = new AssetLoader();
        $filename = $this->createMockFilename($name, $mimetype);
        $filename->method('getName')->willReturn('simple');

        $asset = $loader->load($filename);

        $this->assertInstanceOf(TextAsset::class, $asset);
        $this->assertEquals('simple', $asset->getTargetId());
    }

    public function textFileFilenameProvider(): array {
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
    private function createMockFilename(string $name, string $mimetype='text/xml'): FileName {
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('getMimeType')->willReturn($mimetype);
        $filename->method('asString')->willReturn(
            __DIR__ . '/../_data/assets/' . $name
        );

        return $filename;
    }
}
