<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class NestedSnippetTest extends TestCase {

    use DomDocumentsEqualTrait;

    public function testReplacementWithMultipleSiblingBug(): void {
        $work = new DOMDocument();
        $work->loadXML('<?xml version="1.0" ?><test><some id="foo" /><other /></test>');

        $dom1 = new DOMDocument();
        $dom1->loadXML('<page:snippet xmlns:page="https://templado.io/snippets/1.0" id="foo"><p><a /></p><p><b id="bar" /></p></page:snippet>');
        $snippet1 = new TempladoSnippet('foo', $dom1);

        $dom2 = new DOMDocument();
        $dom2->loadXML('<page:snippet xmlns:page="https://templado.io/snippets/1.0" id="bar"><h4 id="bar">text</h4></page:snippet>');
        $snippet2 = new TempladoSnippet('bar', $dom2);

        $collection = new SnippetListCollection();
        $collection->addSnippet($snippet1);
        $collection->addSnippet($snippet2);

        (new SnippetRenderer($collection))->render($work->documentElement);

        $expected = new DOMDocument();
        $expected->loadXML('<?xml version="1.0" ?><test><p><a /></p><p><h4 id="bar">text</h4></p><other /></test>');

        $this->assertResultMatches($expected->documentElement, $work->documentElement);
    }
}
