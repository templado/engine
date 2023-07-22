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

use ArrayIterator;
use DOMDocument;

final class MergeList {
    /** @psalm-var array<string, list<DOMDocument>> */
    private array $documents = [];

    public function isEmpty(): bool {
        return count($this->documents) === 0;
    }

    public function add(Id $id, DOMDocument $dom): void {
        $idString = $id->asString();

        if (!isset($this->documents[$idString])) {
            $this->documents[$idString] = [];
        }

        $this->documents[$idString][] = $dom;
    }

    public function has(Id $id): bool {
        return isset($this->documents[$id->asString()]);
    }

    public function get(Id $id): ArrayIterator {
        if (!$this->has($id)) {
            throw new MergeListException(
                sprintf('Empty List for id %s', $id->asString())
            );
        }

        return new ArrayIterator($this->documents[$id->asString()]);
    }
}
