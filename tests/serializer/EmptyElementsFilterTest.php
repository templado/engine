<?php declare(strict_types = 1);
namespace Templado\Engine;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use function ini_set;

#[CoversClass(EmptyElementsFilter::class)]
class EmptyElementsFilterTest extends TestCase {

    #[DataProvider('selfContainedElementsProvider')]
    public function testSelfContainedElementsGetClosed(string $expected, string $input): void {
        $this->assertEquals(
            $expected,
            (new EmptyElementsFilter())->apply($input)
        );
    }

    public static function selfContainedElementsProvider(): array {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        $map = [];

        foreach ($tagList as $tag) {
            $map[$tag] = [
                \sprintf('<%s />', $tag),
                \sprintf('<%1$s></%1$s>', $tag)
            ];
        }

        return $map;
    }

    public function testRegexErrorsAreTurnedIntoException(): void {
        $original = ini_set('pcre.backtrack_limit', '100');
        $this->expectException(EmptyElementsFilterException::class);
        (new EmptyElementsFilter())->apply(\file_get_contents(__DIR__ . '/../_data/filter/regex_backtrack.html'));
        ini_set('pcre.backtrack_limit', $original);
    }
}
