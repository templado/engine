<?php declare(strict_types = 1);
namespace Templado\Engine;

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

    /**
     * @dataProvider invalidXPathQueryStringsProvider
     */
    public function testUsingInvalidXPathQueryThrowsException(string $queryString, string $errorMessage) {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector($queryString);

        $this->expectException(XPathSelectorException::class);
        $this->expectExceptionMessage(
            sprintf($errorMessage, $queryString)
        );
        $selector->select($dom->documentElement);
    }

    public function invalidXPathQueryStringsProvider(): array {
        return [
            'empty' => ['', 'Invalid expression: "%s"'],
            'syntax-error' => ['//*[', 'Invalid expression: "%s"'],
            'non-function' => ['foo()', 'Unregistered function: "%s"'],
            'non-axis' => ['f::axis', 'Invalid expression: "%s"'],
            'slash-crazy' => ['/////', 'Invalid expression: "%s"'],
            'dots' => ['....', 'Invalid expression: "%s"'],
            'unknown-prefix' => ['//not:known', 'Undefined namespace prefix: "%s"']
        ];
    }
}
