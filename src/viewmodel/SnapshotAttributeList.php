<?php declare(strict_types = 1);
namespace Templado\Engine;

use Countable;
use DOMAttr;
use DOMNamedNodeMap;
use Iterator;

class SnapshotAttributeList implements Iterator, Countable {

    /** @var DOMAttr[] */
    private $attributes = [];

    /** @var int */
    private $pos = 0;

    public function __construct(DOMNamedNodeMap $map) {
        $this->extractAttributeNodes($map);
    }

    public function count(): int {
        return \count($this->attributes);
    }

    public function current(): DOMAttr {
        if (!$this->valid()) {
            throw new SnapshotAttributeListException('No current attribute available');
        }

        return $this->attributes[$this->pos];
    }

    public function next(): void {
        $this->pos++;
    }

    public function key(): int {
        return $this->pos;
    }

    public function valid(): bool {
        $count = \count($this->attributes);

        return $count > 0 && $count > $this->pos;
    }

    public function rewind(): void {
        $this->pos = 0;
    }

    private function extractAttributeNodes(DOMNamedNodeMap $map): void {
        foreach ($map as $attr) {
            if (!$attr instanceof DOMAttr) {
                throw new SnapshotAttributeListException(
                    \sprintf('%s is not an attribute node type (%s given)', $attr->localName, \get_class($attr))
                );
            }
            $this->attributes[] = $attr;
        }
    }
}
