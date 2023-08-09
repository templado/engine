<?php declare(strict_types = 1);
namespace Templado\Engine;

use AssertionError;
use DOMDocument;
use DOMNode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NamespaceCleaningTransformation::class)]
#[UsesClass(XPathSelector::class)]
#[UsesClass(StaticNodeList::class)]
class NamespaceCleaningTransformationTest extends TestCase {

    use DomDocumentsEqualTrait;

    public function testRequestForSelectorReturnsXPathSelector(): void {
        $this->assertInstanceOf(
            XPathSelector::class,
            (new NamespaceCleaningTransformation)->selector()
        );
    }

    public function testThrowsOnNonDomElementContext(): void {
        $this->expectException(AssertionError::class);
        (new NamespaceCleaningTransformation)->apply($this->createMock(DOMNode::class));
    }

    public function testCleansEmptyNamespaceAsExpected(): void {
        $input = new DOMDocument();
        $input->loadXML('<html />');

        $expected = new DOMDocument();
        $expected->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" />');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testCleansPrefixedNamespaceAsExpected(): void {
        $input = new DOMDocument();
        $input->loadXML('<f:html xmlns:f="http://www.w3.org/1999/xhtml" />');

        $expected = new DOMDocument();
        $expected->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" />');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testCleansRedundantXMLNSDeclarationAsExpected(): void {
        $input = new DOMDocument();
        $input->loadXML('<html xmlns="http://www.w3.org/1999/xhtml"><head xmlns="http://www.w3.org/1999/xhtml" /></html>');

        $expected = new DOMDocument();
        $expected->loadXML('<html xmlns="http://www.w3.org/1999/xhtml"><head /></html>');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testAdoptsAttributesToCleanedNodes(): void {
        $input = new DOMDocument();
        $input->loadXML('<html attr1="a" attr2="b" />');

        $expected = new DOMDocument();
        $expected->loadXML('<html xmlns="http://www.w3.org/1999/xhtml" attr1="a" attr2="b" />');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testOmitsRedundantXMLNSAttributesWhenCopying(): void {
        $input = new DOMDocument();
        $input->loadXML('<html><p id="a" xmlns="http://www.w3.org/1999/xhtml" xmlns:f="urn:other" /></html>');

        $expected = new DOMDocument();
        $expected->loadXML('<html xmlns="http://www.w3.org/1999/xhtml"><p id="a" xmlns:f="urn:other" /></html>');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testIgnoresOtherNamespaces(): void {
        $input = new DOMDocument();
        $input->loadXML('<other xmlns="urn:external" />');

        $expected = new DOMDocument();
        $expected->loadXML('<other xmlns="urn:external" />');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }

    public function testIgnoresOtherPrefixedNamespaces(): void {
        $input = new DOMDocument();
        $input->loadXML('<o:other xmlns:o="urn:external" />');

        $expected = new DOMDocument();
        $expected->loadXML('<o:other xmlns:o="urn:external" />');

        (new NamespaceCleaningTransformation)->apply($input->documentElement);

        $this->assertResultMatches(
            $expected->documentElement,
            $input->documentElement
        );
    }
}
