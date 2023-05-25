<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;

/**
 * @covers \Templado\Engine\Html
 *
 * @uses \Templado\Engine\SnapshotDOMNodelist
 * @uses \Templado\Engine\SnapshotAttributeList
 */
class HTMLTest extends TestCase {
    use DomDocumentsEqualTrait;

    /**
     * @uses \Templado\Engine\SnippetRenderer
     * @uses \Templado\Engine\SnippetListCollection
     * @uses \Templado\Engine\SnippetList
     */
    public function testSingleSnippetCanBeApplied(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $reference = $dom->documentElement->firstChild;

        $snippet = $this->createMock(SimpleSnippet::class);
        $snippet->expects($this->once())->method('getTargetId')->willReturn('a');
        $snippet->expects($this->once())->method('applyTo')->with($reference)->willReturn($reference);

        $page = new Html($dom);
        $page->applySnippet($snippet);
    }

    /**
     * @uses \Templado\Engine\SnippetRenderer
     */
    public function testSnippetsCanBeApplied(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $reference = $dom->documentElement->firstChild;

        $snippet = $this->createMock(SimpleSnippet::class);
        $snippet->expects($this->once())->method('applyTo')->with($reference)->willReturn($reference);
        $snippetList = $this->createMock(SnippetList::class);
        $snippetList->method('current')->willReturn($snippet);
        $snippetList->method('valid')->willReturn(true, false);

        /** @var \PHPUnit_Framework_MockObject_MockObject|SnippetListCollection $collection */
        $collection = $this->createMock(SnippetListCollection::class);
        $collection->method('hasSnippetsForId')->willReturn(true);
        $collection->method('getSnippetsForId')->willReturn($snippetList);

        $page = new Html($dom);

        $page->applySnippets($collection);
    }

    /**
     * @uses \Templado\Engine\ViewModelRenderer
     */
    public function testViewModelCanBeApplied(): void {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/_data/viewmodel/source.html');

        $page = new Html($dom);
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
        $transformation->expects($this->once())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        $page = new Html($dom);
        $page->applyTransformation($transformation);
    }

    /**
     * @uses \Templado\Engine\FormData
     * @uses \Templado\Engine\FormDataRenderer
     */
    public function testFormDataCanBeApplied(): void {
        $path = __DIR__ . '/_data/formdata/text';

        $formdata = include $path . '/formdata.php';

        $dom = new DOMDocument();
        $dom->load($path . '/form.html');

        $expected = new DOMDocument();
        $expected->load($path . '/expected.html');

        $page = new Html($dom);
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

        $page = new Html($dom);
        $page->applyCSRFProtection($protection);

        $this->assertResultMatches(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testCanBeConvertedToString(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html><head><link rel="stylesheet"></link></head><body><p>test</p></body></html>');

        $expected = [
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '  <head>',
            '    <link rel="stylesheet" />',
            '  </head>',
            '  <body>',
            '    <p>test</p>',
            '  </body>',
            '</html>'
        ];

        $page = new Html($dom);
        $this->assertEquals(
            \implode("\n", $expected),
            $page->asString()
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testCanBeConvertedToStringWithDoctype(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><!DOCTYPE html><html><head><link rel="stylesheet"></link></head><body><p>test</p></body></html>');

        $expected = [
            '<!DOCTYPE html>',
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '  <head>',
            '    <link rel="stylesheet" />',
            '  </head>',
            '  <body>',
            '    <p>test</p>',
            '  </body>',
            '</html>'
        ];

        $page = new Html($dom);
        $this->assertEquals(
            \implode("\n", $expected),
            $page->asString()
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testPassedFilterGetsCalledAfterSerializing(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');
        $page = new Html($dom);

        $filter = $this->createMock(Filter::class);
        $filter->expects($this->once())->method('apply')->with('<root></root>');

        $page->asString($filter);
    }

    /**
     * @uses \Templado\Engine\SimpleSnippet
     */
    public function testCanBeConvertedToSnippet(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');
        $snippet = (new Html($dom))->toSnippet('test');

        $this->assertInstanceOf(Snippet::class, $snippet);
        $this->assertEquals('test', $snippet->getTargetId());
    }
}
