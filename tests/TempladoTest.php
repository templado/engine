<?php declare(strict_types=1);
/*
 * This file is part of Document\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;

#[CoversClass(Document::class)]
#[UsesClass(Id::class)]
#[UsesClass(TempladoParsingException::class)]
#[UsesClass(XPathSelector::class)]
#[UsesClass(Selection::class)]
#[UsesClass(ViewModel::class)]
#[UsesClass(ViewModelRenderer::class)]
#[UsesClass(FormData::class)]
#[UsesClass(FormDataRenderer::class)]
#[UsesClass(SnapshotAttributeList::class)]
#[UsesClass(SnapshotDOMNodelist::class)]
class TempladoTest extends TestCase {
    use DomDocumentsEqualTrait;

    public function testCanBeConstructedFromString(): void {
        $this->assertInstanceOf(
            Document::class,
            Document::fromString('<?xml version="1.0" ?><root />')
        );
    }

    public function testCanBeConstructedFromStringWithId(): void {
        $id       = new Id('abc');
        $instance = Document::fromString('<?xml version="1.0" ?><root />', $id);
        $this->assertInstanceOf(
            Document::class,
            $instance
        );

        $this->assertSame($id, $instance->id());
    }

    public function testTryingToParseInvalidMarkupStringThrowsException(): void {
        $this->expectException(TempladoParsingException::class);
        Document::fromString('<?xml version="1.0" ?><root>');
    }

    public function testSelectionOfSingleNodeCanBeExtracted(): void {
        $id     = new Id('test');
        $result = (Document::fromString('<?xml version="1.0" ?><root><child /></root>'))->extract(
            new XPathSelector('//child'),
            $id
        );

        $this->assertInstanceOf(Document::class, $result);
        $this->assertEquals($id, $result->id());
    }

    public function testExtractingEmptySelectionThrowsException(): void {
        $this->expectException(TempladoException::class);
        (Document::fromString('<?xml version="1.0" ?><root><child /></root>'))->extract(
            new XPathSelector('//invalid')
        );
    }

    public function testSelectionOfMultiNodesCanBeExtracted(): void {
        $result = (Document::fromString('<?xml version="1.0" ?><root><child /><child /></root>'))->extract(
            new XPathSelector('//child')
        );

        $this->assertInstanceOf(Document::class, $result);

        $result->asString(new class($this) implements Serializer {

            public function __construct(
                private TestCase $testCase
            ){

            }
            public function serialize(DOMDocument $document): string {
                $this->testCase->assertCount(2, $document->getElementsByTagName('child'));

                return '';
            }
        });
    }

    public function testViewModelCanBeApplied(): void {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/_data/viewmodel/source.html');

        $page = Document::fromDomDocument($dom);
        $page->applyViewModel($viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/_data/viewmodel/expected.html');

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\TransformationProcessor
     */
    public function testTransformationCanBeApplied(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = $this->createMock(Selection::class);
        $selection->method('getIterator')->willReturn(new \ArrayIterator([$dom->documentElement->firstChild]));

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->once())->method('selector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        $page = Document::fromDomDocument($dom);
        $page->applyTransformation($transformation);
    }

    public function testFormDataCanBeApplied(): void {
        $path = __DIR__ . '/_data/formdata/text';

        $formdata = include $path . '/formdata.php';

        $dom = new DOMDocument();
        $dom->load($path . '/form.html');

        $expected = new DOMDocument();
        $expected->load($path . '/expected.html');

        $page = Document::fromDomDocument($dom);
        $page->applyFormData($formdata);

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\CSRFProtectionRenderer
     */
    public function testCSRFProtectionCanBeApplied(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html><body><form></form></body></html>');

        $protection = $this->createMock(CSRFProtection::class);
        $protection->method('getFieldName')->willReturn('csrf');
        $protection->method('getTokenValue')->willReturn('secure');

        $expected = new DOMDocument();
        $expected->loadXML(
            '<?xml version="1.0"?>
            <html><body><form><input type="hidden" name="csrf" value="secure"/></form></body></html>'
        );

        $page = Document::fromDomDocument($dom);
        $page->applyCSRFProtection($protection);

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

}
