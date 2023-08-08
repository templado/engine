<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(HTMLSerializer::class)]
#[UsesClass(Document::class)]
#[UsesClass(EmptyElementsFilter::class)]
#[UsesClass(NamespaceCleaningTransformation::class)]
#[UsesClass(Selection::class)]
#[UsesClass(StaticNodeList::class)]
#[UsesClass(TransformationProcessor::class)]
#[UsesClass(XMLHeaderFilter::class)]
#[UsesClass(XPathSelector::class)]
#[UsesClass(StripRDFaAttributesTransformation::class)]
class HTMLSerializerTest extends TestCase {

    public function testSerializesDocumentWithDefaultSettingsAsExpected() {
        $doc = $this->createInputDocument();

        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/default.html'),
            $doc->asString((new HTMLSerializer()))
        );
    }

    public function testSerializesDocumentWithoutDoctypeIfRequested() {
        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/nodoctype.html'),
            $this->createInputDocument()->asString((new HTMLSerializer())->noHtml5Doctype())
        );
    }

    public function testSerializesDocumentWithXMLHeaderIfRequested() {
        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/withxmlheader.html'),
            $this->createInputDocument()->asString((new HTMLSerializer())->keepXMLHeader())
        );
    }

    public function testStripsRDFaIfRequested() {
        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/nordfa.html'),
            $this->createInputDocument()->asString((new HTMLSerializer())->stripRDFa())
        );
    }

    public function testSerializesDocumentWithoutCleaningIfRequested() {
        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/nocleaning.html'),
            $this->createInputDocument()->asString((new HTMLSerializer())->disableNamespaceCleaning())
        );
    }

    public function testAddedFilterGetsApplied(): void {
        $this->assertSame(
            'replaced-by-filter',
            $this->createInputDocument()->asString(
                (new HTMLSerializer())->addFilter(new class implements Filter {
                    public function apply(string $content) : string {
                        return 'replaced-by-filter';
                    }
                })
            )
        );
    }

    public function testAddedTransformationGetsApplies(): void {
        $this->assertSame(
            file_get_contents(__DIR__ . '/../_data/serializer/nordfa.html'),
            $this->createInputDocument()->asString((new HTMLSerializer())->addTransformation(
                new StripRDFaAttributesTransformation()
            ))
        );
    }

    private function createInputDocument(): Document {
        return Document::fromString(file_get_contents(__DIR__ . '/../_data/serializer/input.xml'));
    }

}
