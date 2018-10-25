<?php declare(strict_types = 1);
namespace Templado\Engine;

class EmptyElementsFilter implements Filter {

    public function apply(string $content): string {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        foreach($tagList as $tag) {
            $content = preg_replace(
                "=<{$tag}(.*[^>]?)></{$tag}>=U",
                "<{$tag}\$1 />",
                $content
            );
            if ($content === NULL) {
                $errorCode = preg_last_error();
                throw new EmptyElementsFilterException(
                    sprintf('Error while processing regular expression: %s (%d)',
                        array_flip(get_defined_constants(true)['pcre'])[$errorCode],
                        $errorCode
                    )
                );
            }
        }

        return $content;
    }

}
