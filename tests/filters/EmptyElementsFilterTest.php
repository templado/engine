<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\TestCase;

/**
 * @covers \Templado\Engine\EmptyElementsFilter
 */
class EmptyElementsFilterTest extends TestCase {

    /**
     * @dataProvider selfContainedElementsProvider
     */
    public function testSelfContainedElementsGetClosed(string $expected, string $input): void {
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

        foreach ($tagList as $tag) {
            $map[$tag . '-plain'] = [
                \sprintf('<%s />', $tag),
                \sprintf('<%1$s></%1$s>', $tag)
            ];

            $map[$tag . '-with-attr'] = [
                \sprintf('<%s attr="value" />', $tag),
                \sprintf('<%1$s attr="value"></%1$s>', $tag)
            ];

        }

        return $map;
    }

    public function testRegexErrorsAreTurnedIntoException(): void {
        $this->iniSet('pcre.backtrack_limit', '2');
        $this->expectException(EmptyElementsFilterException::class);
        (new EmptyElementsFilter())->apply(\file_get_contents(__DIR__ . '/../_data/filter/regex_backtrack.html'));
    }

    /**
     * Regression Test for #26
     *
     * @dataProvider nestedElementsProvider
     */
    public function testNestedElementsWithRequiredSelfClosingWhenEmptyDoNotGetMangledWhenNotEmpty(string $input) {
        $this->assertEquals(
            $input,
            (new EmptyElementsFilter())->apply($input)
        );
    }

    public function nestedElementsProvider(): array {
        return [
            'issue#26' => [
                '<body><button id="foo">some text<span>bla</span></button></body>'
            ],
            'issue#26-plain' => [
                '<body><button>some text<span>bla</span></button></body>'
            ],
            'two-levels' => [
                '<button id="foo"><img src="about.gif" /></button>'
            ],
            'mixed-nesting' => [
                '<button id="foo"><img src="about.gif"><br />test<br/></button>'
            ],
            'span-deep-nesting' => [
                '<button id="foo"><img src="about.gif" /><br />test<span>test</span><br/></button>'
            ],
            'double-mixed-nesting' => [
                '<button id="foo"><img src="about.gif"><span>test</span></img></button><button id="bar"><img src="about2.gif"><span>test2</span></img></button>'
            ]
        ];
    }
}
