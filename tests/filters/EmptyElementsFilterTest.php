<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use PHPUnit\Framework\TestCase;

/**
 * @covers \TheSeer\Templado\EmptyElementsFilter
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
}
