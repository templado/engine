<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use function preg_last_error;
use function preg_replace;

class EmptyElementsFilter implements Filter {
    public function apply(string $content): string {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        foreach ($tagList as $tag) {
            $content = preg_replace(
                "=<{$tag}(.*[^>]?)></{$tag}>=U",
                "<{$tag}\$1 />",
                $content
            );

            if ($content === null) {
                throw new EmptyElementsFilterException(
                    'Error while processing regular expression',
                    preg_last_error()
                );
            }
        }

        return $content;
    }
}
