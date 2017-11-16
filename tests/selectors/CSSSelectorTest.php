<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class CSSSelectorTest extends TestCase {

    public function testSelectReturnsExceptedNode() {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selector  = new CSSSelector('child');
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

        $selector = new CSSSelector('foo|child');
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

    /**
     * @dataProvider invalidXPathQueryStringsProvider
     */
    public function testUsingInvalidXPathQueryThrowsException($queryString) {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector($queryString);

        $this->expectException(XPathSelectorException::class);
        $selector->select($dom->documentElement);
    }

    public function invalidXPathQueryStringsProvider(): array {
        return [
            'empty' => [''],
            'syntax-error' => ['//*['],
            'non-function' => ['foo()'],
            'non-axis' => ['f::axis'],
            'slash-crazy' => ['/////'],
            'dots' => ['....'],
            'unknown-prefix' => ['//not:known']
        ];
    }
}
