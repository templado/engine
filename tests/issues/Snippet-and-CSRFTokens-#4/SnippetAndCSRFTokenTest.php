<?php declare(strict_types = 1);
namespace Templado\Engine;

use PhpUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class SnippetAndCSRFTokenTest extends TestCase {

    public function testIssueIsNoLongerReproduceable() {
        $templadoFile = new FileName(__DIR__ . '/formTest.xhtml');
        $html = Templado::loadHtmlFile($templadoFile);

        $snippetFile = new FileName(__DIR__ . '/include.xhtml');
        $snippetLoader = new \Templado\Engine\SnippetLoader();
        $snippet = $snippetLoader->load($snippetFile);

        $snippetCollection = new \Templado\Engine\SnippetListCollection();
        $snippetCollection->addSnippet($snippet);

        $html->applySnippets($snippetCollection);
        $html->applyCSRFProtection(new \Templado\Engine\CSRFProtection('csrfToken','csrfValue'));

        file_put_contents(__DIR__ . '/expected.xhtml', $html->asString());
        $expected = file_get_contents(__DIR__ . '/expected.xhtml');

        $this->assertEquals(
            $expected,
            $html->asString()
        );
    }
}
