<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\EmptyElementsFilter
 */
class EmptyElementsFilterTest extends TestCase {

    /**
     * @dataProvider selfContainedElementsProvider
     *
     * @param string $expected
     * @param string $input
     */
    public function testSelfContainedElementsGetClosed(string $expected, string $input) {
        $this->assertEquals(
            $expected,
            (new EmptyElementsFilter())->apply($input)
        );
    }

    public function selfContainedElementsProvider(): array {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        $map = [];
        foreach($tagList as $tag) {
            $map[$tag] = [
                sprintf('<%s />', $tag),
                sprintf('<%1$s></%1$s>', $tag)
            ];
        }

        return $map;
    }

    /**
     * @expectedEx ception \Templado\Engine\EmptyElementsFilterException
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testRegexErrorsAreTurnedIntoException() {

        $oldL =ini_set('pcre.backtrack_limit', '100');
        //$this->expectException(EmptyElementsFilterException::class);
        (new EmptyElementsFilter())->apply(file_get_contents(__DIR__ . '/../_data/filter/regex_backtrack.html'));
        ini_set('pcre.backtrack_limit', '100000000');
    }

}
