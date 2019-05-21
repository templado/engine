<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\SimpleSnippet
 */
class SimpleSnippetTest extends TestCase {

    /** @var DOMElement */
    private $domElement;

    protected function setUp(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root id="abc" />');
        $this->domElement = $dom->documentElement;
    }

    public function testTargetIdCanBeRetrieved(): void {
        $snippet = new SimpleSnippet('foo', new DOMNode());
        $this->assertEquals('foo', $snippet->getTargetId());
    }

    public function testSnippetGetsAddedAsChild(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node/>');
        $snippet = new SimpleSnippet('abc', $dom->documentElement);

        $this->assertEquals(0, $this->domElement->childNodes->length);
        $snippet->applyTo($this->domElement);
        $this->assertEquals(1, $this->domElement->childNodes->length);
    }

    public function testTargetIdCanBeModified(): void
    {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');
        $snippet = new SimpleSnippet('abc', $dom->documentElement);
        $id = 'xyz';
        $modifiedSnippet =$snippet->withTargetId($id)->getTargetId();

        $this->assertEquals($id, $modifiedSnippet);
        $this->assertNotSame($snippet, $modifiedSnippet);
    }

    public function testSnippetReplacesContent(): void {
        $dom = new \DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><node id="abc" />');
        $snippet = new SimpleSnippet('abc', $dom->documentElement);

        $snippet->applyTo($this->domElement);
        $this->assertEquals('node', $this->domElement->ownerDocument->documentElement->nodeName);
    }

    public function testSnippetWithNonElementContentDoesNotReplaceContent(): void {
        $fragment = (new \DOMDocument())->createDocumentFragment();
        $fragment->appendXML('<node1 /><node2 />');

        $snippet = new SimpleSnippet('abc', $fragment);
        $snippet->applyTo($this->domElement);
        $this->assertEquals(2, $this->domElement->childNodes->length);
    }
}
