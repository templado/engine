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
use function iterator_to_array;

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

    public function testUnsetOptionGetsUnselected(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<form id="test">
            <select name="a[]" multiple="multiple">
                <option value="a">a</option>
                <option value="b" selected="selected">b</option>
                <option value="c">c</option>
            </select>
        </form>');

        (new FormDataRenderer)->render($dom->documentElement, new FormData('test', ['a' => [1 => 'b', 2 => 'c']]));

        [$first, $second, $third] = $dom->getElementsByTagName('option');
        $this->assertFalse($first->hasAttribute('selected'));
        $this->assertTrue($second->hasAttribute('selected'));
        $this->assertTrue($third->hasAttribute('selected'));
    }

    public function testMultiLevelArrayOnSelectWorks(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<form id="test">
            <select name="a[][][]" multiple="multiple">
                <option value="a">a</option>
                <option value="b" selected="selected">b</option>
                <option value="c">c</option>
            </select>
        </form>');

        (new FormDataRenderer)->render($dom->documentElement, new FormData('test', ['a' => [ [ ['c'] ] ] ]));

        [$first, $second, $third] = $dom->getElementsByTagName('option');
        $this->assertFalse($first->hasAttribute('selected'));
        $this->assertFalse($second->hasAttribute('selected'));
        $this->assertTrue($third->hasAttribute('selected'));
    }

    public function testArraySyntaxGetsResolvedProperly(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<form id="test">
            <input type="text" name="a[]" />
            <input type="text" name="a[]" />
            <input type="text" name="b[][]" />
            <input type="text" name="c[][][]" />
            <input type="text" name="d[a][][]" />
            <input type="text" name="d[a][][b]" />
        </form>');

        (new FormDataRenderer)->render($dom->documentElement, new FormData('test', [
            'a' => ['a1','a2'],
            'b' => [['b1']],
            'c' => [[['c1']]],
            'd' => [
                'a' => [
                        0 => ['d-a1'],
                        1 => [ 'b' => 'd-a-0-b1']]
                ]
        ]));

        $expected = new DOMDocument;
        $expected->loadXML('  <form id="test">
              <input type="text" name="a[]" value="a1"/>
              <input type="text" name="a[]" value="a2"/>
              <input type="text" name="b[][]" value="b1"/>
              <input type="text" name="c[][][]" value="c1"/>
              <input type="text" name="d[a][][]" value="d-a1"/>
              <input type="text" name="d[a][][b]" value="d-a-0-b1"/>
          </form>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testNameToLookupConversionSelectsCorrectAncestors(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<form id="test">
            <input type="text" name="a1" />
            <input type="text" name="a2" />
            <input type="text" name="a[]" />
            <input type="text" name="a[]" />
        </form>');

        (new FormDataRenderer)->render($dom->documentElement, new FormData('test', [ 'a' => ['a-value-1', 'a-value-2']]));

        $expected = new DOMDocument;
        $expected->loadXML('<form id="test">
            <input type="text" name="a1" />
            <input type="text" name="a2" />
            <input type="text" name="a[]" value="a-value-1" />
            <input type="text" name="a[]" value="a-value-2" />
        </form>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }

    public function testNameToLookupConversionUsesIndexZeroByDefault(): void {
        $dom = new DOMDocument;
        $dom->loadXML('<form id="test">
            <input type="text" name="a[]" />
        </form>');

        (new FormDataRenderer)->render($dom->documentElement, new FormData('test', [ 'a' => ['a-value-1']]));

        $expected = new DOMDocument;
        $expected->loadXML('<form id="test">
            <input type="text" name="a[]" value="a-value-1" />
        </form>');

        $this->assertResultMatches($expected->documentElement, $dom->documentElement);
    }
}
