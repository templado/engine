<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\ClearNamespaceDefinitionsFilter
 */
class ClearNamespaceDefinitionFilterTest extends TestCase {

    public function testNamespaceWithoutPrefixGetsReplaced() {
        $this->assertEquals(
            '<foo />',
            (new ClearNamespaceDefinitionsFilter())->apply('<foo xmlns="a:ns" />')
        );
    }

    public function testNamespaceForHTMLgetsSet() {
        $this->assertEquals(
            '<html xmlns="http://www.w3.org/1999/xhtml" />',
            (new ClearNamespaceDefinitionsFilter())->apply('<html xmlns="a:ns" />')
        );
    }

}
