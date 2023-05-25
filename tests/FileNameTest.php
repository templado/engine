<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\Filename
 */
class FileNameTest extends TestCase {
    public function testFileNameCanBeRetrievedAsString(): void {
        $filename = new Filename('test');
        $this->assertEquals('test', $filename->asString());
    }

    public function testExitsReturnsTrueForExistingFile(): void {
        $this->assertTrue((new Filename(__FILE__))->exists());
    }

    public function testExitsReturnsFalseForNonExistingFile(): void {
        $this->assertFalse((new Filename('/not/existing'))->exists());
    }

    public function testTestingFileReturnsTrueForFile(): void {
        $this->assertTrue((new Filename(__FILE__))->isFile());
    }

    public function testTestingFileReturnsFalseForDirectory(): void {
        $this->assertFalse((new Filename(__DIR__))->isFile());
    }

    public function testReturnsFalseForNonreadableFiles(): void {
        $this->assertFalse((new Filename('/nope'))->isReadable());
    }

    public function testReturnsTrueForReadableFiles(): void {
        $this->assertTrue((new Filename(__FILE__))->isReadable());
    }

    public function testGetMimeTypeReturnsExpectedMimeType(): void {
        $this->assertEquals('text/x-php', (new Filename(__FILE__))->getMimeType());
    }

    public function testGetNameReturnsBaseFileNameWithoutExtension(): void {
        $this->assertEquals('FileNameTest', (new Filename(__FILE__))->getName());
    }
}
