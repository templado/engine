<?php declare(strict_types = 1);
namespace Templado\Engine;

use PhpUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class InvalidDuplicateIdExceptionTest extends TestCase {
    public function testIssueIsNoLongerReproduceable(): void {
        $templado = Templado::loadHtmlFile(new FileName(__DIR__ . '/skeleton.xhtml'));

        $dom = new \DOMDocument;
        $dom->load(__DIR__ . '/login.xhtml');

        $collection = new SnippetListCollection();
        $collection->addSnippet(new TempladoSnippet('content', $dom));

        $templado->applySnippets($collection);

        $this->assertStringEqualsFile(
            __DIR__ . '/expected.xhtml',
            $templado->asString()
        );
    }
}
