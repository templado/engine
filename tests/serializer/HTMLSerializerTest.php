<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use const LIBXML_NOEMPTYTAG;

#[CoversClass(HTMLSerializer::class)]
#[UsesClass(Document::class)]
#[UsesClass(EmptyElementsFilter::class)]
#[UsesClass(Selection::class)]
#[UsesClass(StaticNodeList::class)]
#[UsesClass(TransformationProcessor::class)]
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

    public function testXMLHeaderIsKeptWhenNotCleaning() {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" />');

        $this->assertSame(
            '<?xml version="1.0" encoding="utf-8" ?>' . "\n" . '<html xmlns="http://www.w3.org/1999/xhtml"></html>' . "\n",
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->disableNamespaceCleaning()->serialize($dom)
        );
    }

    public function testNamespacedAttributesGetSerializedCorrectly() {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" ?><html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a" a:attr="value" />');

        $this->assertSame(
            '<?xml version="1.0"?>' . "\n" . '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a" a:attr="value"></html>' . "\n",
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->serialize($dom)
        );
    }

    public function testNamespacedElementsGetSerializedCorrectly() {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" ?><html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a"><a:foo /><b:foo xmlns:b="urn:b" /><c xmlns="urn:c" /></html>');

        $this->assertSame(
            implode("\n", [
                '<?xml version="1.0"?>',
                '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a">',
                '  <a:foo></a:foo>',
                '  <b:foo xmlns:b="urn:b"></b:foo>',
                '  <c xmlns="urn:c"></c>',
                '</html>' . "\n"
            ]),
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->serialize($dom)
        );
    }



    private function createInputDocument(): Document {
        return Document::fromString(file_get_contents(__DIR__ . '/../_data/serializer/input.xml'));
    }

}
