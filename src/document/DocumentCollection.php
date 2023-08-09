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

use function array_push;
use function count;
use ArrayIterator;
use Countable;
use IteratorAggregate;

/** @template-implements IteratorAggregate<int,Document> */
final class DocumentCollection implements Countable, IteratorAggregate {
    /** @psalm-type list<int,Document> */
    private array $documents;

    public function __construct(Document ...$documents) {
        $this->documents = $documents;
    }

    public function count(): int {
        return count($this->documents);
    }

    public function add(Document ...$documents): void {
        array_push($this->documents, ...$documents);
    }
    public function getIterator(): ArrayIterator {
        return new ArrayIterator($this->documents);
    }
}
