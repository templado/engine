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

use Countable;

final readonly class StringCollection implements Countable {
    /**
     * @var string[]
     */
    private array $texts;

    /** @param string[] $text */
    public static function fromArray(array $text): self {
        return new self(...array_values($text));
    }

    public static function fromStrings(string ...$text): self {
        return new self(...$text);
    }

    private function __construct(string ...$text) {
        $this->texts = $text;
    }

    public function count(): int {
        return count($this->texts);
    }

    public function itemAt(int $pos): string {
        return $this->texts[$pos] ?? '';
    }
}
