<?php declare(strict_types = 1);
namespace Templado\Engine;

class EmptyElementsFilter implements Filter {
    public function apply(string $content): string {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        foreach ($tagList as $tag) {
            $content = \preg_replace(
                "=<{$tag}(.*[^>]?)></{$tag}>=U",
                "<{$tag}\$1 />",
                $content
            );

            if ($content === null) {
                throw new EmptyElementsFilterException(
                    'Error while processing regular expression',
                    \preg_last_error()
                );
            }
        }

        return $content;
    }
}
