<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\FileName
 */
class FileNameTest extends TestCase {
    public function testFileNameCanBeRetrievedAsString(): void {
        $filename = new FileName('test');
        $this->assertEquals('test', $filename->asString());
    }

    public function testExitsReturnsTrueForExistingFile(): void {
        $this->assertTrue((new FileName(__FILE__))->exists());
    }

    public function testExitsReturnsFalseForNonExistingFile(): void {
        $this->assertFalse((new FileName('/not/existing'))->exists());
    }

    public function testTestingFileReturnsTrueForFile(): void {
        $this->assertTrue((new FileName(__FILE__))->isFile());
    }

    public function testTestingFileReturnsFalseForDirectory(): void {
        $this->assertFalse((new FileName(__DIR__))->isFile());
    }

    public function testReturnsFalseForNonreadableFiles(): void {
        $this->assertFalse((new FileName('/nope'))->isReadable());
    }

    public function testReturnsTrueForReadableFiles(): void {
        $this->assertTrue((new FileName(__FILE__))->isReadable());
    }

    public function testGetMimeTypeReturnsExpectedMimeType(): void {
        $this->assertEquals('text/x-php', (new FileName(__FILE__))->getMimeType());
    }

    public function testGetNameReturnsBaseFileNameWithoutExtension(): void {
        $this->assertEquals('FileNameTest', (new FileName(__FILE__))->getName());
    }
}
