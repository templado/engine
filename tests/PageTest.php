<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;

/**
 * @covers \Templado\Engine\Page
 */
class PageTest extends TestCase {

    /**
     * @uses \Templado\Engine\AssetRenderer
     */
    public function testAssetsCanBeApplied() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $reference = $dom->documentElement->firstChild;

        $asset = $this->createMock(SimpleAsset::class);
        $asset->expects($this->once())->method('applyTo')->with($reference)->willReturn($reference);
        $assetList = $this->createMock(AssetList::class);
        $assetList->method('current')->willReturn($asset);
        $assetList->method('valid')->willReturn(true, false);

        /** @var AssetListCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetListCollection::class);
        $collection->method('hasAssetsForId')->willReturn(true);
        $collection->method('getAssetsForId')->willReturn($assetList);

        $page = new Page($dom);

        $page->applyAssets($collection);

    }

    /**
     * @uses \Templado\Engine\ViewModelRenderer
     */
    public function testViewModelCanBeApplied() {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/_data/viewmodel/source.html');

        $page = new Page($dom);
        $page->applyViewModel($viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/_data/viewmodel/expected.html');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\TransformationProcessor
     */
    public function testTransformationCanBeApplied() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = $this->createMock(Selection::class);
        $selection->method('getIterator')->willReturn($dom->documentElement->childNodes);

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->once())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        $page = new Page($dom);
        $page->applyTransformation($transformation);

    }

    /**
     * @uses \Templado\Engine\FormData
     * @uses \Templado\Engine\FormDataRenderer
     */
    public function testFormDataCanBeApplied() {
        $path = __DIR__ . '/_data/formdata/text';

        $formdata = include $path . '/formdata.php';

        $dom = new DOMDocument();
        $dom->load($path . '/form.html');

        $expected = new DOMDocument();
        $expected->load($path . '/expected.html');

        $page = new Page($dom);
        $page->applyFormData($formdata);

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\CSRFProtectionRenderer
     */
    public function testCSRFProtectionCanBeApplied() {
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

        $page = new Page($dom);
        $page->applyCSRFProtection($protection);

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testCanBeConvertedToString() {
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

        $page = new Page($dom);
        $this->assertEquals(
            implode("\n", $expected),
            $page->asString()
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testCanBeConvertedToStringWithDoctype() {
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

        $page = new Page($dom);
        $this->assertEquals(
            implode("\n", $expected),
            $page->asString()
        );
    }

    /**
     * @uses \Templado\Engine\ClearNamespaceDefinitionsFilter
     * @uses \Templado\Engine\EmptyElementsFilter
     */
    public function testPassedFilterGetsCalledAfterSerializing() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root />');
        $page = new Page($dom);

        $filter = $this->createMock(Filter::class);
        $filter->expects($this->once())->method('apply')->with('<root></root>');

        $page->asString($filter);
    }

}
