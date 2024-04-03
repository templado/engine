<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentCollection::class)]
#[UsesClass(Document::class)]
#[UsesClass(Id::class)]
#[Small]
class DocumentCollectionTest extends TestCase {

    public function testIsInitiallyEmpty(): void {
        $this->assertTrue(
            (new DocumentCollection())->isEmpty()
        );
    }

    public function testIteratesOverDocuments(): void {

        $documents = [];
        foreach(['A','B','C'] as $id) {
            $documents[] = Document::fromString(
                sprintf('<?xml version="1.0" ?><foo%s />', $id),
                new Id($id)
            );
        }

        $result = [];
        foreach((new DocumentCollection(...$documents)) as $document) {
            $result[] = $document;
        }

        $this->assertSame($documents, $result);
    }

    public function testCanAddDocument(): void {

        $document = Document::fromString(
            '<?xml version="1.0" ?><foo />',
            new Id('foo')
        );

        $collection = new DocumentCollection();
        $collection->add($document);
        $collection->add($document);

        $this->assertContains($document, $collection);
        $this->assertCount(2, $collection);
        $this->assertFalse($collection->isEmpty());
    }

    public function testAssocArrayLoosesKeyCorrectly(): void {

        $data = [ 'assocKey' => Document::fromString('<root />')];
        $collection = new DocumentCollection(...$data);

        foreach($collection as $key => $value) {
            $this->assertEquals(0, $key);
            $this->assertInstanceOf(Document::class, $value);
        }
    }

    public function testAddingAssocArrayLosesKeydCorrectly(): void {

        $data = [ 'assocKey' => Document::fromString('<root />')];
        $collection = new DocumentCollection();
        $collection->add(...$data);

        foreach($collection as $key => $value) {
            $this->assertEquals(0, $key);
            $this->assertInstanceOf(Document::class, $value);
        }
    }

}
