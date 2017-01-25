<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;

class Asset {

    /** @var DOMNode */
    private $content;

    /**
     * @var string
     */
    private $targetId;

    /**
     * @var Selector
     */
    private $relation;

    /**
     * @param string   $targetId
     * @param DOMNode  $content
     * @param Selector $relation
     */
    public function __construct(string $targetId, DOMNode $content, Selector $relation = null) {
        $this->content  = $content;
        $this->targetId = $targetId;
        $this->relation = $relation;
    }

    /**
     * @return string
     */
    public function getTargetId(): string {
        return $this->targetId;
    }

    /**
     * @return bool
     */
    public function hasRelation(): bool {
        return $this->relation instanceof Selector;
    }

    /**
     * @return Selector
     */
    public function getRelation(): Selector {
        return $this->relation;
    }

    /**
     * @return DOMNode
     */
    public function getContent() {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasContentWithId(): bool {
        if (!$this->content instanceof DOMElement) {
            return false;
        }

        return $this->content->hasAttribute('id');
    }

    public function getContentId(): string {
        if (!$this->hasContentWithId()) {
            throw new AssetException('No Content ID set');
        }
        $node = $this->content;

        /** @var DOMElement $node */
        return $node->getAttribute('id');
    }

}
