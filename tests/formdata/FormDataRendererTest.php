<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * @covers \Templado\Engine\FormDataRenderer
 *
 * @uses   \Templado\Engine\FormData
 */
class FormDataRendererTest extends TestCase {
    use DomDocumentsEqualTrait;

    /**
     * @dataProvider formdataProvider
     */
    public function testFormDataGetsRenderedAsExpected(FormData $formData, DOMDocument $contextDoc, DOMDocument $expectedDoc): void {
        $renderer = new FormDataRenderer();
        $renderer->render($contextDoc->documentElement, $formData);
        $this->assertResultMatches(
            $contextDoc->documentElement,
            $expectedDoc->documentElement
        );
    }

    public function formdataProvider(): array {
        $result = [];

        foreach (\glob(__DIR__ . '/../_data/formdata/*') as $entry) {
            $data       = include $entry . '/formdata.php';
            $contextDOM = new DOMDocument();
            $contextDOM->load($entry . '/form.html');

            $expectedDOM = new DOMDocument();
            $expectedDOM->load($entry . '/expected.html');

            $result[\basename($entry)] = [
                $data,
                $contextDOM,
                $expectedDOM
            ];
        }

        return $result;
    }

    public function testNoFormByGivenNameThrowsException(): void {
        $contextDOM = new DOMDocument();
        $contextDOM->load(__DIR__ . '/../_data/formdata/text/form.html');

        $formdata = new FormData('does-not-exist', []);
        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formdata);
    }

    public function testMultipleFormsByGivenNameThrowsException(): void {
        $contextDOM = new DOMDocument();
        $contextDOM->load(__DIR__ . '/../_data/formdata/text/form.html');

        $form = $contextDOM->getElementsByTagName('form')->item(0);
        $form->parentNode->insertBefore($form->cloneNode(true), $form);

        $formdata = new FormData('test', []);
        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formdata);
    }

    public function testFormDataExceptionsGetsPassedOnAsFormDataRendererException(): void {
        $contextDOM = new DOMDocument();
        $contextDOM->load(__DIR__ . '/../_data/formdata/text/form.html');

        /** @var FormData|PHPUnit_Framework_MockObject_MockObject $formData */
        $formData = $this->createMock(FormData::class);
        $formData->method('getIdentifier')->willReturn('test');
        $formData->method('hasKey')->willReturn(true);
        $formData->method('getValue')->willThrowException(new FormDataException);

        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formData);
    }
}
