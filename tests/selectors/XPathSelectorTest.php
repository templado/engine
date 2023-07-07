<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\TestCase;

class XPathSelectorTest extends TestCase {
    public function testSelectReturnsExceptedNode(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selector  = new XPathSelector('//child');
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach ($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }
    }

    public function testRegisteredNamespacePrefixIsUsed(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector('//foo:child');
        $selector->registerPrefix('foo', 'foo:ns');
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach ($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }
    }

    /**
     * @dataProvider invalidXPathQueryStringsProvider
     */
    public function testUsingInvalidXPathQueryThrowsException(string $queryString): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector($queryString);

        $this->expectException(XPathSelectorException::class);
        $selector->select($dom->documentElement);
    }

    public static function invalidXPathQueryStringsProvider(): array {
        return [
            'empty'          => [''],
            'syntax-error'   => ['//*['],
            'non-function'   => ['foo()'],
            'non-axis'       => ['f::axis'],
            'slash-crazy'    => ['/////'],
            'dots'           => ['....'],
            'unknown-prefix' => ['//not:known']
        ];
    }
}
