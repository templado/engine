<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;

interface Asset {

    /**
     * @return string
     */
    public function getTargetId(): string;

    /**
     * @param DOMElement $node
     *
     * @return DOMNode
     */
    public function applyTo(DOMElement $node): DOMNode;

}
