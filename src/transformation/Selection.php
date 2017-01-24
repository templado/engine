<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMNodeList;
use IteratorAggregate;

class Selection implements IteratorAggregate {

    /**
     * @var DOMNodeList
     */
    private $nodeList;

    /**
     * Selection constructor.
     *
     * @param DOMNodeList $nodeList
     */
    public function __construct(DOMNodeList $nodeList) {
        $this->nodeList = $nodeList;
    }

    public function isEmpty(): bool {
        return $this->nodeList->length === 0;
    }

    public function getIterator(): DOMNodeList {
        return $this->nodeList;
    }

}
