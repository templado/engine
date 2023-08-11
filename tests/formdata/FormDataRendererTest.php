<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use DOMElement;
use PHPUnit\Framework\Attributes\DataProvider;
use function basename;
use function glob;
use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormDataRenderer::class)]
#[UsesClass(FormData::class)]
#[UsesClass(StaticNodeList::class)]
class FormDataRendererTest extends TestCase {
    use DomDocumentsEqualTrait;

    public function testThrowsExceptionWhenUsedWithDisconnectedElement(): void {
        $this->expectException(FormDataRendererException::class);
        (new FormDataRenderer())->render(new DOMElement('foo'), new FormData('foo', []));
    }


    public static function formdataProvider(): array {
        $result = [];

        foreach (glob(__DIR__ . '/../_data/formdata/*') as $entry) {
            $data       = include $entry . '/formdata.php';
            $contextDOM = new DOMDocument();
            $contextDOM->load($entry . '/form.html');

            $expectedDOM = new DOMDocument();
            $expectedDOM->load($entry . '/expected.html');

            $result[basename($entry)] = [
                $data,
                $contextDOM,
                $expectedDOM
            ];
        }

        return $result;
    }

    #[DataProvider('formDataProvider')]
    public function testFormDataGetsRenderedAsExpected(FormData $formData, DOMDocument $contextDoc, DOMDocument $expectedDoc): void {
        $renderer = new FormDataRenderer();
        $renderer->render($contextDoc->documentElement, $formData);
        $this->assertResultMatches(
            $contextDoc->documentElement,
            $expectedDoc->documentElement
        );
    }

    public function testFormElementFoundOnRootElementById(): void {
        $src = new DOMDocument();
        $src->loadXML('<?xml version="1.0" ?><form id="foo"><input type="text" name="bar" /></form>');

        $exp = new DOMDocument();
        $exp->loadXML('<?xml version="1.0" ?><form id="foo"><input type="text" name="bar" value="val" /></form>');

        $formData = new FormData('foo', ['bar' => 'val']);
        $renderer = new FormDataRenderer();

        $renderer->render($src->documentElement, $formData);
        $this->assertResultMatches(
            $src->documentElement,
            $exp->documentElement
        );
    }

    public function testFormElementFoundOnRootElementByName(): void {
        $src = new DOMDocument();
        $src->loadXML('<?xml version="1.0" ?><form name="foo"><input type="text" name="bar" /></form>');

        $exp = new DOMDocument();
        $exp->loadXML('<?xml version="1.0" ?><form name="foo"><input type="text" name="bar" value="val" /></form>');

        $formData = new FormData('foo', ['bar' => 'val']);
        $renderer = new FormDataRenderer();

        $renderer->render($src->documentElement, $formData);
        $this->assertResultMatches(
            $src->documentElement,
            $exp->documentElement
        );
    }

    public function testNoFormByGivenNameThrowsException(): void {
        $contextDOM = new DOMDocument();
        $contextDOM->load(__DIR__ . '/../_data/formdata/text/form.html');

        $formdata = new FormData('does-not-exist', []);
        $renderer = new FormDataRenderer();

        $this->expectException(FormDataRendererException::class);
        $renderer->render($contextDOM->documentElement, $formdata);
    }
}
