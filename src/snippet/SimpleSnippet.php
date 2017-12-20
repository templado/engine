<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;

class SimpleSnippet implements Snippet {

    /** @var DOMNode */
    private $content;

    /** @var string */
    private $targetId;

    public function __construct(string $targetId, DOMNode $content) {
        $this->targetId = $targetId;
        $this->content = $content;
    }

    public function getTargetId(): string {
        return $this->targetId;
    }

    public function applyTo(DOMElement $node): DOMNode {
        $content = $node->ownerDocument->importNode($this->content, true);

        if ($this->shouldReplace($node, $content)) {
            $node->parentNode->replaceChild($content, $node);

            return $content;
        }

        $node->appendChild($content);

        return $node;
    }

    private function shouldReplace(DOMElement $node, DOMNode $content): bool {
        if (!$content instanceof DOMElement) {
            return false;
        }

        if (!$node->hasAttribute('id') || !$content->hasAttribute('id')) {
            return false;
        }

        return $node->getAttribute('id') === $content->getAttribute('id');
    }

}
