<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use Templado\Engine\Example\ViewModel;

/**
 * @covers \Templado\Engine\ViewModelRenderer
 * @uses \Templado\Engine\SnapshotDOMNodelist
 */
class ViewModelRendererTest extends TestCase {

    public function testViewModelGetsAppliedAsExcepted() {
        $viewModel = new ViewModel();
        $dom       = new DOMDocument();
        $dom->load(__DIR__ . '/../_data/viewmodel/source.html');

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->load(__DIR__ . '/../_data/viewmodel/expected.html');

        $this->assertEqualXMLStructure(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testIteratorReturnValueGetsApplied() {
        $dom       = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" /></body></html>');

        $viewModel = new class {
            public function a() {
                return new \ArrayIterator(['a','b']);
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a">a</p><p property="a">b</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );

    }

    public function testMagicCallMethodGetsCalledWhenDefinedAndNoExplicitMethodFits() {
        $dom       = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" /></body></html>');

        $viewModel = new class {
            public function __call($name, $args) {
                return 'text';
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a">text</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testMagicCallMethodGetsCalledForAttributesWhenDefinedAndNoExplicitMethodFits() {
        $dom       = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a" attr="b" /></body></html>');

        $viewModel = new class {
            public function __call($name, $args) {
                switch ($name) {
                    case 'a': return $this;
                    case 'property': return null;
                    default: return 'text';
                }
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a" attr="text">text</p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement
        );
    }

    public function testNestedViewModelWithMagicCallsGetsAppliedAsExpected() {
        $dom       = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><html><body><p property="a"><span property="b">original</span></p></body></html>');

        $viewModel = new class {
            public function __call($name, $args) {
                switch ($name) {
                    case 'a': return $this;
                    case 'b': return 'text';
                    case 'property': return null;
                    default: return ($args[0] ?? null);
                }
            }
        };

        $renderer = new ViewModelRenderer();
        $renderer->render($dom->documentElement, $viewModel);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0"?><html><body><p property="a"><span property="b">text</span></p></body></html>');

        $this->assertEquals(
            $expected->documentElement,
            $dom->documentElement,
            $dom->saveXML()
        );
    }

    public function testUseOfNonObjectModelThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, 'Non-Object');
    }

    public function testNoMethodForPropertyThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, new class {
        });
    }

    public function testUnsupportedVariableTypeThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" />');

        $model = new class {
            public function test() {
                return 1;
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $model);
    }

    public function testUnsupportedVariableTypeForAttributeThrowsException() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root property="test" attr="value" />');

        $model = new class {
            public function test() {
                return new class {
                    public function attr() {
                        return 1;
                    }
                };
            }
        };

        $renderer = new ViewModelRenderer();

        $this->expectException(ViewModelRendererException::class);
        $renderer->render($dom->documentElement, $model);
    }

}
