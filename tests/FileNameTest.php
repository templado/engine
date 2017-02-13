<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\FileName
 */
class FileNameTest extends TestCase {

    public function testFileNameCanBeRetrievedAsString() {
        $filename = new FileName('test');
        $this->assertEquals('test', $filename->asString());
    }

    public function testExitsReturnsTrueForExistingFile() {
        $this->assertTrue((new FileName(__FILE__))->exists());
    }

    public function testExitsReturnsFalseForNonExistingFile() {
        $this->assertFalse((new FileName('/not/existing'))->exists());
    }

    public function testTestingFileReturnsTrueForFile() {
        $this->assertTrue((new FileName(__FILE__))->isFile());
    }

    public function testTestingFileReturnsFalseForDirectory() {
        $this->assertFalse((new FileName(__DIR__))->isFile());
    }

    public function testReturnsFalseForNonreadableFiles() {
        $this->assertFalse((new FileName('/nope'))->isReadable());
    }

    public function testReturnsTrueForReadableFiles() {
        $this->assertTrue((new FileName(__FILE__))->isReadable());
    }

}
