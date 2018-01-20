<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

require __DIR__ . '/viewmodel.php';

class NegativeIndexTest extends TestCase {

    public function testIssueIsNoLongerReproduceable() {

        $templadoFile = new FileName(__DIR__ . '/input.xhtml');
        $html = Templado::loadHtmlFile($templadoFile);
        $html->applyViewModel(new NegativeIndexVM());

        $this->assertXmlStringEqualsXmlString(
            file_get_contents(__DIR__ . '/expected.html'),
            $html->asString()
        );

    }
}

