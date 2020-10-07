<?php declare(strict_types = 1);
namespace Templado\Engine;

use ArrayIterator;
use DOMNode;
use DOMNodeList;
use IteratorAggregate;

class Selection implements IteratorAggregate {

    /** @var DOMNode[] */
    private $list = [];

    public function __construct(DOMNodeList $nodeList) {
        foreach ($nodeList as $node) {
            $this->list[] = $node;
        }
    }

    public function isEmpty(): bool {
        return \count($this->list) === 0;
    }

    public function getIterator(): \Iterator {
        return new ArrayIterator($this->list);
    }
}
