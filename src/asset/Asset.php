<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;

class Asset {

    /** @var DOMNode */
    private $node;

    /**
     * @param DOMNode $node
     */
    public function __construct(DOMNode $node) {
        $this->node = $node;
    }

    public function getNode() {
        return $this->node;
    }

    public function hasId() {
        if (!$this->node instanceof DOMElement) {
            return false;
        }

        return $this->node->hasAttribute('id');
    }

    public function getId() {
        if (!$this->hasId()) {
            throw new AssetException('No ID set');
        }
        $node = $this->node;

        /** @var DOMElement $node */
        return $node->getAttribute('id');
    }

}
