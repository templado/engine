<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

class FormDataRendererTest extends TestCase {

    /**
     * @param FormData    $formData
     * @param DOMDocument $contextDoc
     * @param DOMDocument $expectedDoc
     *
     * @dataProvider formdataProvider
     */
    public function testFormDataGetsRenderedAsExpected(FormData $formData, DOMDocument $contextDoc, DOMDocument $expectedDoc) {
        $renderer = new FormDataRenderer();
        $renderer->render($contextDoc->documentElement, $formData);
        $this->assertEqualXMLStructure(
            $contextDoc->documentElement,
            $expectedDoc->documentElement
        );
    }

    public function formdataProvider(): array {
        $result = [];

        foreach( glob(__DIR__ . '/../_data/formdata/*') as $entry) {
            $data = include $entry . '/formdata.php';
            $contextDOM = new DOMDocument();
            $contextDOM->load( $entry . '/form.html');

            $expectedDOM = new DOMDocument();
            $expectedDOM->load( $entry . '/expected.html');

            $result[basename($entry)] = [
                $data,
                $contextDOM,
                $expectedDOM
            ];
        }

        return $result;
    }

    public function testNoFormByGivenNameThrowsException() {
        $contextDOM = new DOMDocument();
        $contextDOM->load( __DIR__ . '/../_data/formdata/text/form.html');

        $formdata = new FormData('does-not-exist', []);
        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formdata);
    }

    public function testMultipleFormsByGivenNameThrowsException() {
        $contextDOM = new DOMDocument();
        $contextDOM->load( __DIR__ . '/../_data/formdata/text/form.html');

        $form = $contextDOM->getElementsByTagName('form')->item(0);
        $form->parentNode->insertBefore($form->cloneNode(TRUE), $form);

        $formdata = new FormData('test', []);
        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formdata);
    }

    public function testFormDataExceptionsGetsPassedOnAsFormDataRendererException() {
        $contextDOM = new DOMDocument();
        $contextDOM->load( __DIR__ . '/../_data/formdata/text/form.html');

        /** @var PHPUnit_Framework_MockObject_MockObject|FormData $formData */
        $formData = $this->createMock(FormData::class);
        $formData->method('getIdentifier')->willReturn('test');
        $formData->method('hasKey')->willReturn(true);
        $formData->method('getValue')->willThrowException(new FormDataException);

        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formData);
    }


}
