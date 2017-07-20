<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

class JsonMapperTest extends TestCase {

    public function testInvalidJsonThrowsException() {
        $this->expectException(JsonMapperException::class);
        (new JsonMapper())->fromString('not-json');
    }

    /**
     * @dataProvider jsonDataProvider
     */
    public function testJsonGetsMappedAsExcepted(string $jsonString, GenericViewModel $expected) {
        $result = (new JsonMapper())->fromString($jsonString);
        $this->assertEquals(
            $expected, $result
        );
    }

    public function jsonDataProvider(): array {
        return [
            'simple' => [
                '{"key":"value"}',
                new GenericViewModel(['key' => 'value'])
            ],
            'array'  => [
                '{"key": ["v1","v2"]}',
                new GenericViewModel(['key' => ["v1","v2"]])
            ],
            'array-of-array' => [
                '{"key": [["v1"],["v2"]]}',
                new GenericViewModel(['key' => [["v1"],["v2"]]])
            ],
            'object' => [
                '{"key": {"k":"v"}}',
                new GenericViewModel(['key' => new GenericViewModel(['k' => 'v'])])
            ],
            'array-of-object' => [
                '{"key": [{"k":"v1"},{"k":"v2"}]}',
                new GenericViewModel(['key' => [
                    new GenericViewModel(['k' => 'v1']),
                    new GenericViewModel(['k' => 'v2'])
                ]])
            ]
        ];
    }
}

