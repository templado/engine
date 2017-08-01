<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SnippetListCollection
 */
class SnippetListCollectionTest extends TestCase {

    /**
     * @var SnippetListCollection
     */
    private $collection;

    protected function setUp() {
        $this->collection = new SnippetListCollection();
    }

    public function testReturnsFalseWhenNoSnippetWithGivenIdExists() {
        $this->assertFalse(
            $this->collection->hasSnippetsForId('abc')
        );
    }

    /**
     * @uses \Templado\Engine\SnippetList
     */
    public function testReturnsTrueForExistingSnippet() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Snippet $snippet */
        $snippet = $this->createMock(SimpleSnippet::class);
        $snippet->method('getTargetId')->willReturn('abc');
        $this->collection->addSnippet($snippet);
        $this->assertTrue(
            $this->collection->hasSnippetsForId('abc')
        );
    }

    public function testThrowsExceptionWhenTryingToRetrieveNonExistingSnippet() {
        $this->expectException(SnippetCollectionException::class);
        $this->collection->getSnippetsForId('abc');
    }

    /**
     * @uses \Templado\Engine\SnippetList
     */
    public function testExistingSnippetCanBeRetrieved() {
        /** @var \PHPUnit_Framework_MockObject_MockObject|Snippet $snippet */
        $snippet = $this->createMock(SimpleSnippet::class);
        $snippet->method('getTargetId')->willReturn('abc');
        $this->collection->addSnippet($snippet);
        $result = $this->collection->getSnippetsForId('abc');
        $this->assertInstanceOf(SnippetList::class, $result);
        $this->assertCount(1, $result);
    }

}
