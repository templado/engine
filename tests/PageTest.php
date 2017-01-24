<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;
use TheSeer\Templado\Example\ViewModel;

class PageTest extends TestCase {

    public function testAssetsCanBeApplied() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child id="a"/></root>');

        $asset = $this->createMock(Asset::class);
        $asset->method('getNode')->willReturn(
            new DOMElement('test')
        );

        /** @var AssetCollection|\PHPUnit_Framework_MockObject_MockObject $collection */
        $collection = $this->createMock(AssetCollection::class);
        $collection->method('hasAssetForId')->willReturn(true);
        $collection->method('getAssetForId')->willReturn($asset);

        $page = new Page($dom);

        $page->applyAssets($collection);

        $expected = new \DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><root><child id="a"><test/></child></root>');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

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

    public function testTransformationCanBeApplied() {

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selection = new Selection($dom->documentElement->childNodes);

        $selector = $this->createMock(Selector::class);
        $selector->method('select')->willReturn($selection);

        $transformation = $this->createMock(Transformation::class);
        $transformation->expects($this->once())->method('getSelector')->willReturn($selector);
        $transformation->expects($this->once())->method('apply')->with($dom->documentElement->firstChild);

        $page = new Page($dom);
        $page->applyTransformation($transformation);

    }

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

    public function testCanBeConvertedToString() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html><head><link rel="stylesheet"></link></head><body><p>test</p></body></html>');

        $expected = [
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '  <head>',
            '    <link rel="stylesheet"/>',
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

    public function testCanBeConvertedToStringWithDoctype() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><!DOCTYPE html><html><head><link rel="stylesheet"></link></head><body><p>test</p></body></html>');

        $expected = [
            '<!DOCTYPE html>',
            '<html xmlns="http://www.w3.org/1999/xhtml">',
            '  <head>',
            '    <link rel="stylesheet"/>',
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

}
