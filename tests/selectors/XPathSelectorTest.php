<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Small;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(XPathSelector::class)]
#[UsesClass(Selection::class)]
#[Small]
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

    public function testLibxmlErrorHandlingGetsResetToPreviousState(): void {
            $dom = new DOMDocument();
            $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

            $org = libxml_use_internal_errors(false);

            $selector  = new XPathSelector('//child');
            $selector->select($dom->documentElement);

            $reset = \libxml_use_internal_errors($org);

            $this->assertFalse($reset);
    }
    public function testSelectReturnsExceptedNodeWhenDomDocuemtnIsUsed(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root><child /></root>');

        $selector  = new XPathSelector('//child');
        $selection = $selector->select($dom);

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

    public function testHtmlPrefixIsImplicitlyRegistered(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html xmlns="http://www.w3.org/1999/xhtml"><head /></html>');

        $selector = new XPathSelector('//html:head');
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach ($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }
    }

    public function testPHPFunctionsCanBeUsedInXPath(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html lang="en" xmlns="http://www.w3.org/1999/xhtml"><head /></html>');

        $selector = new XPathSelector('//html:html[php:functionString("substr", @lang, 0, 2) = "en"]/*');
        $selector->registerPrefix("php", "http://php.net/xpath");
        $selection = $selector->select($dom->documentElement);

        $this->assertInstanceOf(Selection::class, $selection);

        foreach ($selection as $node) {
            $this->assertSame(
                $dom->documentElement->firstChild,
                $node
            );
        }
    }

    #[DataProvider('invalidXPathQueryStringsProvider')]
    public function testUsingInvalidXPathQueryThrowsException(string $queryString): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><root xmlns="foo:ns"><child /></root>');

        $selector = new XPathSelector($queryString);

        try {
            $selector->select($dom->documentElement);
        } catch (XPathSelectorException $e) {
            $this->assertMatchesRegularExpression('/.*[a-zA-Z ]:.*/', $e->getMessage());
            $this->assertEmpty(\libxml_get_errors());

            return;
        }

        $this->fail('XPathSelectorException not thrown but expected');
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
