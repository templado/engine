<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;

interface Asset {

    /**
     * @return string
     */
    public function getTargetId(): string;

    /**
     * @param DOMElement $node
     */
    public function applyTo(DOMElement $node);

}
