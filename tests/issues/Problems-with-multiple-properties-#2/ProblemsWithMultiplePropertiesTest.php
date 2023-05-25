<?php declare(strict_types = 1);

use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class ProblemsWithMultiplePropertiesTest extends TestCase {
    public function testIssueIsNoLongerReproduceable(): void {
        $html = \Templado\Engine\Templado::loadHtmlFile(
            new \Templado\Engine\Filename(__DIR__ . '/sample.html')
        );

        $html->applyViewModel(new class {
            public function entry1() {
                return false;
            }
            public function entry2() {
                return 'entry-1.2-value';
            }
        });

        $this->assertXmlStringEqualsXmlString(
            \file_get_contents(__DIR__ . '/expected.html'),
            $html->asString()
        );
    }
}
