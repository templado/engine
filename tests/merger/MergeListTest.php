<?php declare(strict_types = 1);
namespace merger;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Id;
use Templado\Engine\MergeList;
use Templado\Engine\MergeListException;

#[CoversClass(MergeList::class)]
#[UsesClass(Id::class)]
#[Small]
class MergeListTest extends TestCase {

    public function testIsInitiallyEmpty(): void {
        $this->assertTrue(
            (new MergeList())->isEmpty()
        );
    }

    public function testCanAddDocument(): void {
        $list = new MergeList();
        $list->add(
            new Id('foo'),
            new DOMDocument()
        );

        $this->assertFalse($list->isEmpty());
    }

    public function testAddedDocumentCanBeFoundById(): void{
        $id = new Id('foo');
        $list = new MergeList();
        $list->add(
            $id,
            new DOMDocument()
        );

        $this->assertTrue($list->has($id));
    }

    public function testAddedDocumentsCanBeRetrievedById(): void {
        $id = new Id('foo');
        $docs = array_fill(0, 3, new DOMDocument());

        $list = new MergeList();

        foreach($docs as $doc) {
            $list->add($id, $doc);
        }

        foreach($list->get($id) as $pos => $found) {
            $this->assertSame($docs[$pos], $found);
        }
    }

    public function testCanAddMultipleDocumentsForSameId(): void {
        $list = new MergeList();
        $id = new Id('foo');
        $list->add($id, new DOMDocument());
        $list->add($id, new DOMDocument());

        $this->assertFalse($list->isEmpty());
        $this->assertCount(2, $list->get($id));
    }

    public function testRequestingNonExistingIdThrowsException(): void {
        $this->expectException(MergeListException::class);
        (new MergeList())->get(new Id('not-existing'));
    }
}
