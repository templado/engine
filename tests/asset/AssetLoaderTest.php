<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\AssetLoader
 */
class AssetLoaderTest extends TestCase {

    public function testAttemptingToLoadNonExistingFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(false);

        $this->expectException(AsseteLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadSomethingThatIsNotAFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(false);

        $this->expectException(AsseteLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadANotReadableFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(false);

        $this->expectException(AsseteLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnInvalidFileThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('asString')->willReturn(__DIR__ . '/../_data/broken.txt');

        $this->expectException(AsseteLoaderException::class);
        $loader->load($filename);
    }

    public function testAttemptingToLoadAnUnknownFileTypeThrowsException() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('asString')->willReturn(__DIR__ . '/../_data/assets/undefined.xml');

        $this->expectException(AsseteLoaderException::class);
        $loader->load($filename);
    }

    /**
     * @uses \TheSeer\Templado\SimpleAsset
     */
    public function testLoadingPlainHtmlReturnsValidAsset() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('asString')->willReturn(__DIR__ . '/../_data/assets/simple.xhtml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('abc', $asset->getTargetId());
    }

    /**
     * @uses \TheSeer\Templado\SimpleAsset
     */
    public function testLoadingHtmlWithoutIdUsesFilename() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('asString')->willReturn(__DIR__ . '/../_data/assets/noid.xhtml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('noid', $asset->getTargetId());
    }

    /**
     * @uses \TheSeer\Templado\SimpleAsset
     */
    public function testLoadingAssetFileReturnsValidAsset() {
        $loader   = new AssetLoader();
        $filename = $this->createMock(FileName::class);
        $filename->method('exists')->willReturn(true);
        $filename->method('isFile')->willReturn(true);
        $filename->method('isReadable')->willReturn(true);
        $filename->method('asString')->willReturn(__DIR__ . '/../_data/assets/asset.xml');
        $asset = $loader->load($filename);

        $this->assertInstanceOf(SimpleAsset::class, $asset);
        $this->assertEquals('header', $asset->getTargetId());
    }

}
