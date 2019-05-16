<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/viewmodel.php';

/**
 * @coversNothing
 */
class Issue5Test extends TestCase {
    public function testIssueIsNoLongerReproduceable(): void {
        $templadoFile = new FileName(__DIR__ . '/formTest.xhtml');
        $html         = Templado::loadHtmlFile($templadoFile);
        $html->applyViewModel(new Issue5_ViewData());

        $this->assertXmlStringEqualsXmlString(
            \file_get_contents(__DIR__ . '/expected.html'),
            $html->asString()
        );
    }
}
