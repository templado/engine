<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class XPathSelectorTest extends TestCase {

    public function testSelectReturnsExceptedNode() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selector  = new XPathSelector('//child');
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }
    }

    public function testRegisteredNamespacePrefixIsUsed() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector('//foo:child');
        $selector->registerPrefix('foo', 'foo:ns');
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }

    }
}
