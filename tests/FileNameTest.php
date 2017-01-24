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
}
