<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use PHPUnit\Framework\TestCase;

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
}
