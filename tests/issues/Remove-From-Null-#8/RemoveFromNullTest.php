<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/RemoveFromNullTestViewModel.php';

/**
 * @coversNothing
 */
class RemoveFromNullTest extends TestCase {

    public function testIssueIsNoLongerReproduceable() {

        $templadoFile = new FileName(__DIR__ . '/index.xhtml');
        $html = Templado::loadHtmlFile($templadoFile);
        $html->applyViewModel(new \RemoveFromNullTestViewModel());

        $this->assertXmlStringEqualsXmlString(
            file_get_contents(__DIR__ . '/expected.html'),
            $html->asString()
        );

    }
}

