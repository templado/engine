<?php declare(strict_types = 1);
namespace Templado\Engine;

use ArrayIterator;
use DOMDocument;
use RuntimeException;

class MergeList {

    private array $documents = [];

    public function isEmpty(): bool {
        return count($this->documents) === 0;
    }

    public function add(Id $id, DOMDocument $dom) {
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
