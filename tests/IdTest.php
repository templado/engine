<?php declare(strict_types = 1);
namespace Templado\tests;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use Templado\Engine\Id;

class sIdTest extends \PHPUnit\Framework\TestCase {

    public function testCanBeCreatedForValidInput(): void {
        $this->assertInstanceOf(Id::class, new Id('abc'));
    }

    public function testCanBeConvertedToString(): void {
        $this->assertEquals('abc', (new Id('abc'))->asString());
    }

    #[DataProvider('invalidIdProvider')]
    public function testThrowsExceptionForInvalidInput(string $input): void {
        $this->expectException(InvalidArgumentException::class);

        (new Id($input));
    }

    public static function invalidIdProvider(): array {
        return [
            'empty' => [''],
            'space' => ["\u{0020}"],
            'tab' => ["\u{0009}"],
            'lf' => ["\u{000A}"],
            'ff' => ["\u{000C}"],
            'cr' => ["\u{000D}"]
        ];
    }
}
