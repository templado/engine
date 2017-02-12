<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMNode;

class SimpleAsset implements Asset {

    /** @var DOMNode */
    private $content;

    /**
     * @var string
     */
    private $targetId;

    /**
     * @var bool
     */
    private $replace;

    /**
     * @param string  $targetId
     * @param DOMNode $content
     * @param bool    $replace
     *
     */
    public function __construct(string $targetId, DOMNode $content, bool $replace = false) {
        $this->content  = $content;
        $this->targetId = $targetId;
        $this->replace = $replace;
    }

    /**
     * @return string
     */
    public function getTargetId(): string {
        return $this->targetId;
    }

    /**
     * @return DOMNode
     */
    public function getContent(): DOMNode {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function replaceCurrent(): bool {
        return $this->replace;
    }

}
