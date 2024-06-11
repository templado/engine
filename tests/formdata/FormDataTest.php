<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(FormData::class)]
class FormDataTest extends TestCase {

    #[DataProvider('validDataProvider')]
    public function testCanBeInstantiatedWithUsableData(array $data, string $key): void {
        $formdata = new FormData('foo', $data);
        $this->assertInstanceOf(FormData::class, $formdata);

        $this->assertTrue($formdata->has($key));
    }

    public static function validDataProvider(): array {
        return [
            'string' => [['a' => 'a'], 'a'],
            'array'  => [['a' => ['b' => 'c']], 'a[b]']
        ];
    }

     #[DataProvider('invalidDataProvider')]
    public function testCanNotBeInstantiatedWithUnusableDataTypes(array $data): void {
        $this->expectException(FormDataException::class);
        new FormData('foo', $data);
    }

    public static function invalidDataProvider(): array {
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
        $this->assertEquals('test', $formdata->identifier());
    }

    public function testReturnsTrueOnExistingKey(): void {
        $formdata = new FormData('test', ['a' => 'a']);
        $this->assertTrue($formdata->has('a'));
    }

    public function testReturnsFalseOnNonExistingKey(): void {
        $formdata = new FormData('test', ['a' => 'a']);
        $this->assertFalse($formdata->has('b'));
    }

    public function testThrowsExcpetionWhenNotExistingKeyIsRequested(): void {
        $this->expectException(FormDataException::class);
        $formdata = new FormData('test', ['a' => 'a']);
        $formdata->value('not-existing');
    }

    public function testValueOfExisingKeyCanBeRetrieved(): void {
        $formdata = new FormData('test', ['a' => 'value']);
        $this->assertEquals('value', $formdata->value('a'));
    }

    public function testHandlesMixedNestedArrayTypes(): void {
        $formdata = new FormData('test', ['a' => 'a', 'b' => ['c','d'], 'e' => ['f' => ['g' => 'h']]]);
        $this->assertEquals('a', $formdata->value('a'));
        $this->assertEquals('c', $formdata->value('b[0]'));
        $this->assertEquals('d', $formdata->value('b[1]'));
        $this->assertEquals('h', $formdata->value('e[f][g]'));
    }

    #[DataProvider('invalidNameProvider')]
    public function testThrowsExceptionOnInvalidNameSyntax(string $input): void {
        $this->expectException(FormDataException::class);
        (new FormData('foo', []))->has($input);
    }

    public static function invalidNameProvider(): array {
        return [
            'foo[]0' => ['foo[]0'],
            'foo[0]a' => ['foo[0]a'],
            'foo[0]a[1]' => ['foo[0]a[1]'],
            'foo[' => ['foo['],
            'foo]' => ['foo]'],
            'foo[a[]]' => ['foo[a[]]'],
            '[]foo' => ['[]foo'],
        ];
    }

    public function testAcceptsValidNames(): void {
        $this->assertFalse(
            (new FormData('foo', []))->has("😀")
        );
    }

}
