<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;
use DOMNodeList;

/**
 * Iterating over a DOMNodeList in PHP does not work when the list
 * changes during the iteration process. This Wrapper around NodeList
 * takes a snapshot of the list first and then allows iterating over it.
 */
class SnapshotDOMNodelist {

    /** @var DOMNode[] */
    private $items = [];

    /** @var int */
    private $pos = 0;

    public function __construct(DOMNodeList $list) {
        $this->extractItemsFromNodeList($list);
    }

    public function hasNode(DOMNode $node): bool {
        foreach ($this->items as $pos => $item) {
            if ($item->isSameNode($node)) {
                return true;
            }
        }

        return false;
    }

    public function hasNext(): bool {
        $count = \count($this->items);

        return $count > 0 && $this->pos < $count;
    }

    public function getNext(): DOMNode {
        $node = $this->current();
        $this->pos++;

        return $node;
    }

    public function removeNode(DOMNode $node): void {
        /** @psalm-var int $pos */
        foreach ($this->items as $pos => $item) {
            if ($item->isSameNode($node)) {
                \array_splice($this->items, $pos, 1);

                if ($this->pos > 0 && $pos <= $this->pos) {
                    $this->pos--;
                }

                return;
            }
        }

        throw new SnapshotDOMNodelistException('Node not found in list');
    }

    private function current(): DOMNode {
        if (!$this->valid()) {
            throw new SnapshotDOMNodelistException('No current node available');
        }

        return $this->items[$this->pos];
    }

    private function valid(): bool {
        $count = \count($this->items);

        return $count > 0 && $count > $this->pos;
    }

    private function extractItemsFromNodeList(DOMNodeList $list): void {
        foreach ($list as $item) {
            $this->items[] = $item;
        }
    }
}
