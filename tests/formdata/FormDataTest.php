<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;
use stdClass;

/**
 * @covers \Templado\Engine\FormData
 */
class FormDataTest extends TestCase {

    /**
     * @dataProvider validDataProvider
     */
    public function testCanBeInstantiatedWithUsableData(array $data): void {
        $formdata = new FormData('foo', $data);
        $this->assertInstanceOf(FormData::class, $formdata);
    }

    public function validDataProvider(): array {
        return [
            'string' => [['a' => 'a']],
            'array'  => [['a' => ['a', 'b']]]
        ];
    }

    /**
     * @dataProvider invalidDataProvider
     */
    public function testCanNotBeInstantiatedWithUnusableDataTypes(array $data): void {
        $this->expectException(FormDataException::class);
        new FormData('foo', $data);
    }

    public function invalidDataProvider(): array {
        return [
            'object' => [['a' => new StdClass]],
            'int'    => [['a' => 1]],
            'float'  => [['a' => 1.0]],
            'bool'   => [['a' => true]],
            'array'  => [['a' => [1, 2, 3]]]
        ];
    }

    public function testIndentifierCanBeRetrieved(): void {
        $formdata = new FormData('test', ['a' => 'a']);
        $this->assertEquals('test', $formdata->getIdentifier());
    }

    public function testReturnsTrueOnExistingKey(): void {
        $formdata = new FormData('test', ['a' => 'a']);
        $this->assertTrue($formdata->hasKey('a'));
    }

    public function testReturnsFalseOnNonExistingKey(): void {
        $formdata = new FormData('test', ['a' => 'a']);
        $this->assertFalse($formdata->hasKey('b'));
    }

    public function testThrowsExcpetionWhenNotExistingKeyIsRequested(): void {
        $this->expectException(FormDataException::class);
        $formdata = new FormData('test', ['a' => 'a']);
        $formdata->getValue('not-existing');
    }

    public function testValueOfExisingKeyCanBeRetrieved(): void {
        $formdata = new FormData('test', ['a' => 'value']);
        $this->assertEquals('value', $formdata->getValue('a'));
    }
}
