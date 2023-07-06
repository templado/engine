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
use IteratorAggregate;
use Traversable;

/** @template-implements IteratorAggregate<int,Document> */
class DocumentCollection implements IteratorAggregate {
    /** @psalm-type list<int,Document> */
    private array $documents = [];

    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->documents);
    }

    public function has(string $id): bool {
        return false;
    }

    public function get(string $id): Traversable {
    }
}
