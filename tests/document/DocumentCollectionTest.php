<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DocumentCollection::class)]
#[UsesClass(Document::class)]
#[UsesClass(Id::class)]
#[Small]
class DocumentCollectionTest extends TestCase {

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
    }

}
