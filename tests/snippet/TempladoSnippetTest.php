<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\TempladoSnippet
 */
class TempladoSnippetTest extends TestCase {
    private $snippetDom;

    protected function setUp(): void {
        $this->snippetDom = new DOMDocument();
    }

    public function testTryingToLoadEmptyDocumentThrowsException(): void {
        $this->expectException(SnippetException::class);
        new TempladoSnippet('foo', $this->snippetDom);
    }

    public function testTargetIdCanBeRetrieved(): void {
        $this->snippetDom->loadXML('<?xml version="1.0" ?><p:snippet xmlns:p="https://templado.io/snippets/1.0" id="abc"><content /></p:snippet>');

        $snippet = new TempladoSnippet('foo', $this->snippetDom);
        $this->assertEquals('foo', $snippet->getTargetId());
    }

    public function testWrongNamespaceThrowsException(): void {
        $this->snippetDom->loadXML('<?xml version="1.0" ?><foo xmlns="a:b" />');

        $this->expectException(SnippetException::class);
        new TempladoSnippet('foo', $this->snippetDom);
    }

    public function testSnippetGetsAddedAsChild(): void {
        $this->snippetDom->loadXML('<?xml version="1.0" ?><p:snippet xmlns:p="https://templado.io/snippets/1.0" id="abc"><content /></p:snippet>');

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="foo" />');

        $snippet = new TempladoSnippet('foo', $this->snippetDom);
        $snippet->applyTo($dom->documentElement);

        $this->assertEquals('content', $dom->documentElement->firstChild->localName);
    }

    public function testSnippetWithSameIdReplacesContextNode(): void {
        $this->snippetDom->loadXML('<?xml version="1.0" ?><p:snippet xmlns:p="https://templado.io/snippets/1.0" id="abc"><content /></p:snippet>');

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');

        $snippet = new TempladoSnippet('abc', $this->snippetDom);
        $snippet->applyTo($dom->documentElement);

        $this->assertEquals('content', $dom->documentElement->localName);
        $this->assertFalse($dom->documentElement->hasChildNodes());
    }

    public function testSnippetMarkupWithoutIdDoesNotGetReplaced(): void {
        $this->snippetDom->loadXML('<?xml version="1.0" ?><p:snippet xmlns:p="https://templado.io/snippets/1.0"><content /></p:snippet>');

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');

        $snippet = new TempladoSnippet('abc', $this->snippetDom);
        $snippet->applyTo($dom->documentElement);

        $this->assertEquals('<node id="abc"><content/></node>', $dom->saveXML($dom->documentElement));
    }
}
