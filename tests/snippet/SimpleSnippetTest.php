<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SimpleSnippet
 */
class SimpleSnippetTest extends TestCase {

    /** @var  DOMElement */
    private $domElement;

    protected function setUp() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root id="abc" />');
        $this->domElement = $dom->documentElement;
    }

    public function testTargetIdCanBeRetrieved() {
        $snippet = new SimpleSnippet('foo', new DOMNode());
        $this->assertEquals('foo', $snippet->getTargetId());
    }

    public function testSnippetGetsAddedAsChild() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node/>');
        $snippet = new SimpleSnippet('abc', $dom->documentElement);

        $this->assertEquals(0, $this->domElement->childNodes->length);
        $snippet->applyTo($this->domElement);
        $this->assertEquals(1, $this->domElement->childNodes->length);
    }

    public function testSnippetReplacesContent() {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');
        $snippet = new SimpleSnippet('abc', $dom->documentElement);

        $snippet->applyTo($this->domElement);
        $this->assertEquals('node', $this->domElement->ownerDocument->documentElement->nodeName);
    }

    public function testSnippetWithNonElementContentDoesNotReplaceContent() {
        $fragment = (new \DOMDocument())->createDocumentFragment();
        $fragment->appendXML('<node1 /><node2 />');

        $snippet = new SimpleSnippet('abc', $fragment);
        $snippet->applyTo($this->domElement);
        $this->assertEquals(2, $this->domElement->childNodes->length);

    }

}

