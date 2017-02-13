<?php declare(strict_types=1);
namespace TheSeer\Templado;

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
