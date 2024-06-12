<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use TypeError;

#[CoversClass(StringCollection::class)]
class StringCollectionTest extends TestCase {

    public function testCanBeCreatedFromStrings(): void {
        $this->assertInstanceOf(StringCollection::class, StringCollection::fromStrings('a', 'b'));
    }

    public function testCanBeCreatedFromArrayOfStrings(): void {
        $this->assertInstanceOf(StringCollection::class, StringCollection::fromArray(['a', 'b']));
    }

    public function testCreatingFromAssocArrayDropsAssocKey(): void {
        $this->assertSame(
            'a',
            (StringCollection::fromArray(['a' => 'a']))->itemAt(0)
        );
    }

    public function testThrowsErrorWhenArrayContainsNonStringValues(): void {
        $this->expectException(TypeError::class);
        $this->assertInstanceOf(StringCollection::class, StringCollection::fromArray([1,2,3]));
    }

    public function testCountReturnsCorrectNumber(): void {
        $this->assertCount(2,  (StringCollection::fromArray(['a', 'b'])));
    }

    public function testIndividualItemsCanBeRetrieved(): void {
        $collection = StringCollection::fromStrings('a', 'b');

        $this->assertSame('a', $collection->itemAt(0));
        $this->assertSame('b', $collection->itemAt(1));
    }

    public function testReturnsEmptyStringWhenNotFound(): void {
        $this->assertSame('', (StringCollection::fromStrings('a'))->itemAt(4));
    }
}
