<?php declare(strict_types = 1);
namespace Templado\Engine;

use ArrayIterator;
use DOMDocument;
use RuntimeException;

class MergeList {

    private array $documents = [];

    public function add(Id $id, DOMDocument $dom) {
        $idString = $id->asString();

        if (!isset($this->documents[$idString])) {
            $this->documents[$idString] = [];
        }

        $this->documents[$idString][] = $dom;
    }

    public function has(string $id): bool {
        return isset($this->documents[$id]);
    }

    public function get(string $id): ArrayIterator {
        if (!$this->has($id)) {
            throw new RuntimeException(
                sprintf('Empty List for id %s', $id)
            );
        }

        return new ArrayIterator($this->documents[$id]);
    }
}
