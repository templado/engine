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

use InvalidArgumentException;

readonly class Id {
    private string $id;

    public function __construct(string $id) {
        $this->ensureNotEmpty($id);
        $this->ensureFollowsHtml5Rules($id);
        $this->id = $id;
    }

    public function asString(): string {
        return $this->id;
    }

    private function ensureNotEmpty(string $id): void {
        if ($id === '') {
            throw new InvalidArgumentException('ID must not be empty');
        }
    }

    private function ensureFollowsHtml5Rules(string $id): void {
        // https://www.w3.org/TR/html5-author/global-attributes.html#the-id-attribute
        // https://www.w3.org/TR/2012/WD-html5-20121025/single-page.html#space-character
        $invalid = "\u{0020}" . // SPACE
                   "\u{0009}" . // TAB
                   "\u{000A}" . // LF
                   "\u{000C}" . //"FF"
                   "\u{000D}"; // "CR"

        if (preg_match('/*[' . $invalid . ']/', $id)) {
            throw new InvalidArgumentException(
                'ID must not contain space type charectars (https://www.w3.org/TR/html5-author/global-attributes.html#the-id-attribute)'
            );
        }
    }
}
