<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use function file_get_contents;
use function implode;
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

    public function testStrippingRDFaKeepsFollowingAttributes() {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" encoding="ISO-8859-1" ?><html xmlns="http://www.w3.org/1999/xhtml" property="a" some="attr" />');

        $expected = implode("\n", [
            '<?xml version="1.0" encoding="ISO-8859-1"?>',
            '<html xmlns="http://www.w3.org/1999/xhtml" some="attr"></html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->stripRDFa()->serialize($dom)
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

    public function testEncodingGetSerializedCorrectly() {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<?xml version="1.0" encoding="ISO-8859-1" ?><html xmlns="http://www.w3.org/1999/xhtml" />');

        $expected = implode("\n", [
            '<?xml version="1.0" encoding="ISO-8859-1"?>',
            '<html xmlns="http://www.w3.org/1999/xhtml"></html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->serialize($dom),
            'Using XMLWriter'
        );

        $expected = implode("\n", [
            '<?xml version="1.0" encoding="ISO-8859-1" ?>',
            '<html xmlns="http://www.w3.org/1999/xhtml"></html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->keepXMLHeader()->noHtml5Doctype()->disableNamespaceCleaning()->serialize($dom),
            'Using DOM SaveXML'
        );

    }

    public function testNoRedundantNamespaceDeclarationsAreCreated(): void {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a" xmlns:c="urn:c">
            <p xmlns="http://www.w3.org/1999/xhtml" xmlns:a="urn:a" a:attr="value" xmlns:b="urn:b">
                <a:a />
                <b:b />
            </p>
            <h:p xmlns:h="http://www.w3.org/1999/xhtml" xmlns:c="urn:c" c:attr="value">
                <a:a />
            </h:p>
            </html>
        ');

        $expected = implode("\n", [
            '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:c="urn:c" xmlns:a="urn:a">',
            '  <p a:attr="value" xmlns:b="urn:b">',
            '    <a:a></a:a>',
            '    <b:b></b:b>',
            '  </p>',
            '  <p c:attr="value">',
            '    <a:a></a:a>',
            '  </p>',
            '</html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->noHtml5Doctype()->serialize($dom)
        );
    }

    public function testNoRedundantNamespacesAreCreated(): void {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->loadXML(
            '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:a="a:a" xmlns:b="http://www.w3.org/1999/xhtml" xmlns:c="c:c">
                <b:p xmlns:a="a:a" xmlns:b="http://www.w3.org/1999/xhtml" xmlns:c="c:c"/>
            </html>
        ');

        $expected = implode("\n", [
            '<html xmlns="http://www.w3.org/1999/xhtml" xmlns:c="c:c" xmlns:a="a:a">',
            '  <p></p>',
            '</html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->noHtml5Doctype()->serialize($dom)
        );
    }

    public function testRedundantWhitespaceOnTextNodesGetsTrimmed(): void {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->loadXML(
            '<html xmlns="http://www.w3.org/1999/xhtml">
                
                
                <p />
                
                                
            </html>
        ');

        $expected = implode("\n", [
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '  <p></p>',
            '</html>' . "\n"
        ]);

        $this->assertSame(
            $expected,
            (new HTMLSerializer())->noHtml5Doctype()->serialize($dom)
        );

    }


    private function createInputDocument(): Document {
        return Document::fromString(file_get_contents(__DIR__ . '/../_data/serializer/input.xml'));
    }

}
