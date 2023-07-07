<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\ClearRedundantHtmlNamespaceDefinitionsFilter
 */
class ClearRedundantHtmlNamespaceDefinitionsFilterTest extends TestCase {
    public function testNonHTMLNamespaceGetsIgnored(): void {
        $this->assertEquals(
            '<foo xmlns="urn:a:b" />',
            (new ClearRedundantHtmlNamespaceDefinitionsFilter())->apply('<foo xmlns="urn:a:b" />')
        );
    }

    public function testNamespaceForHTMLgetsMovedToRoot(): void {
        $this->assertEquals(
            '<html xmlns="http://www.w3.org/1999/xhtml"><meta /></html>',
            (new ClearRedundantHtmlNamespaceDefinitionsFilter())->apply('<html><meta xmlns="http://www.w3.org/1999/xhtml" /></html>',)
        );
    }

    public function testRegexErrorsAreTurnedIntoException(): void {
        $this->iniSet('pcre.backtrack_limit', '1');
        $this->expectException(ClearNamespaceDefinitionsFilterException::class);
        (new ClearRedundantHtmlNamespaceDefinitionsFilter())->apply(\file_get_contents(__DIR__ . '/../_data/filter/regex_backtrack.html'));
    }
}
