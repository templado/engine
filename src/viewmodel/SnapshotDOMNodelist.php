<?php declare(strict_types = 1);
namespace Templado\Engine;

use ArrayIterator;
use DOMNode;
use DOMNodeList;
use Iterator;

/**
 * Iterating over a DOMNodeList in PHP does not work when the list
 * changes during the iteration process. This Wrapper around NodeList
 * takes a snapshot of the list first and then turns that into an
 * iterator.
 */
class SnapshotDOMNodelist implements \IteratorAggregate {

    /**
     * @var DOMNode[]
     */
    private $items = [];

    public function __construct(DOMNodeList $list) {
        $this->extractItemsFromNodeList($list);
    }

    public function getIterator(): Iterator {
        return new ArrayIterator($this->items);
    }

    private function extractItemsFromNodeList(DOMNodeList $list) {
        foreach($list as $item) {
            $this->items[] = $item;
        }
    }
}
