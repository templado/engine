<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;
use DOMNodeList;
use Iterator;

/**
 * Iterating over a DOMNodeList in PHP does not work when the list
 * changes during the iteration process. This Wrapper around NodeList
 * takes a snapshot of the list first and then turns that into an
 * iterator.
 */
class SnapshotDOMNodelist implements Iterator, \Countable {

    /** @var DOMNode[] */
    private $items = [];

    /** @var int */
    private $pos = 0;

    public function __construct(DOMNodeList $list) {
        $this->extractItemsFromNodeList($list);
    }

    public function count() {
        return \count($this->items);
    }

    public function hasNode(DOMNode $node) {
        foreach ($this->items as $pos => $item) {
            if ($item->isSameNode($node)) {
                return true;
            }
        }

        return false;
    }

    public function removeNode(DOMNode $node): void {
        foreach ($this->items as $pos => $item) {
            if ($item->isSameNode($node)) {
                \array_splice($this->items, $pos, 1);

                if ($pos <= $this->pos) {
                    $this->pos--;
                }

                return;
            }
        }

        throw new SnapshotDOMNodelistException('Node not found in list');
    }

    public function current(): DOMNode {
        if (!$this->valid()) {
            throw new SnapshotDOMNodelistException('No current node available');
        }

        return $this->items[$this->pos];
    }

    public function next(): void {
        $this->pos++;
    }

    public function key(): int {
        return $this->pos;
    }

    public function valid(): bool {
        $count = \count($this->items);

        return $count > 0 && $count > $this->pos;
    }

    public function rewind(): void {
        $this->pos = 0;
    }

    public function hasNext(): bool {
        $count = \count($this->items);

        return $count > 0 && $this->pos < $count;
    }

    public function getNext(): DOMNode {
        $node = $this->current();
        $this->next();

        return $node;
    }

    private function extractItemsFromNodeList(DOMNodeList $list): void {
        foreach ($list as $item) {
            $this->items[] = $item;
        }
    }
}
