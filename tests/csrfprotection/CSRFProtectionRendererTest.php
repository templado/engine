<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\CSRFProtectionRenderer
 */
class CSRFProtectionRendererTest extends TestCase {
    use DomDocumentsEqualTrait;

    /** @var CSRFProtection */
    private $protection;

    /** @var CSRFProtectionRenderer */
    private $renderer;

    /** @var DOMDocument */
    private $expected;

    protected function setUp(): void {
        $protection = $this->createMock(CSRFProtection::class);
        $protection->method('fieldName')->willReturn('csrf');
        $protection->method('tokenValue')->willReturn('secure');

        $this->protection = $protection;
        $this->renderer   = new CSRFProtectionRenderer();

        $this->expected = new DOMDocument();
        $this->expected->loadXML(
            '<?xml version="1.0"?>
            <html><body><form><input type="hidden" name="csrf" value="secure"/></form></body></html>'
        );
    }

    public function testUsingContextElementWithoutOwnerDocumentThrowsException(): void {
        $this->expectException(CSRFProtectionRendererException::class);
        (new CSRFProtectionRenderer())->render(
            new DOMElement('dummmy'),
            $this->createMock(CSRFProtection::class)
        );
    }

    public function testCSRFTokenFieldGetsAddedWhenMissing(): void {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html><body><form></form></body></html>');

        $this->renderer->render($dom->documentElement, $this->protection);

        $this->assertResultMatches(
            $this->expected->documentElement,
            $dom->documentElement
        );
    }

    public function testCSRFTokenFieldGetsUpdatedWithTokenValue(): void {
        $dom = new DOMDocument();
        $dom->loadXML(
            '<?xml version="1.0"?>
            <html><body><form><input type="hidden" name="csrf" value=""/></form></body></html>'
        );

        $this->renderer->render($dom->documentElement, $this->protection);

        $this->assertResultMatches(
            $this->expected->documentElement,
            $dom->documentElement
        );

        $input = $dom->getElementsByTagName('input')->item(0);
        $this->assertEquals('secure', $input->getAttribute('value'));
    }

    public function testCSRFTokenFieldGetsAddedWithCorrectNamespaceWhenMissing(): void {
        $this->expected->documentElement->setAttribute('xmlns', 'a:b');

        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0" ?><html xmlns="a:b"><body><form></form></body></html>');

        $this->renderer->render($dom->documentElement, $this->protection);

        $this->assertResultMatches(
            $this->expected->documentElement,
            $dom->documentElement
        );

        $input = $dom->getElementsByTagName('input')->item(0);
        $this->assertEquals('a:b', $input->namespaceURI);
        $this->assertEquals('secure', $input->getAttribute('value'));
    }

    public function testCSRFTokenFieldWithNamespaceGetsUpdatedWithTokenValue(): void {
        $this->expected->documentElement->setAttribute('xmlns', 'a:b');

        $dom = new DOMDocument();
        $dom->loadXML(
            '<?xml version="1.0"?>
            <html xmlns="a:b"><body><form><input type="hidden" name="csrf" value=""/></form></body></html>'
        );

        $this->renderer->render($dom->documentElement, $this->protection);

        $this->assertResultMatches(
            $this->expected->documentElement,
            $dom->documentElement
        );

        $input = $dom->getElementsByTagName('input')->item(0);
        $this->assertEquals('secure', $input->getAttribute('value'));
    }
}
