<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use TheSeer\Templado\Example\ViewModel;

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
