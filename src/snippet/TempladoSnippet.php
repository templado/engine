<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use DOMNode;

class TempladoSnippet implements Snippet {

    /** @var string */
    private $targetId;

    /** @var DOMDocument */
    private $content;

    public function __construct(string $targetId, DOMDocument $dom) {
        $this->ensureNotEmpty($dom);
        $this->ensureTempladoNamespacedContainer($dom);
        $this->targetId = $targetId;
        $this->content  = $dom;
    }

    public function getTargetId(): string {
        return $this->targetId;
    }

    public function applyTo(DOMElement $node): DOMNode {
        if (!$this->shouldReplace($node)) {
            return $this->appendToNode($node);
        }

        return $this->replaceNode($node);
    }

    private function ensureTempladoNamespacedContainer(DOMDocument $dom): void {
        if ($dom->documentElement->namespaceURI !== 'https://templado.io/snippets/1.0') {
            throw new SnippetException('Document must be in templado namespace (https://templado.io/snippets/1.0).');
        }
    }

    private function shouldReplace(DOMElement $node): bool {
        $root = $this->content->documentElement;

        if (!$node->hasAttribute('id') || !$root->hasAttribute('id')) {
            return false;
        }

        return $node->getAttribute('id') === $root->getAttribute('id');
    }

    private function appendToNode(DOMElement $node): DOMNode {
        foreach ($this->content->documentElement->childNodes as $child) {
            $node->appendChild($node->ownerDocument->importNode($child, true));
        }

        return $node;
    }

    private function replaceNode(DOMElement $node): DOMNode {
        $root  = $this->content->documentElement;
        $first = null;

        $parent = $node->parentNode;

        foreach ($root->childNodes as $child) {
            $imported = $node->ownerDocument->importNode($child, true);
            $parent->insertBefore($imported, $node);

            if ($first === null && $imported instanceof DOMElement) {
                $first = $imported;
            }
        }
        $parent->removeChild($node);

        return $first;
    }

    private function ensureNotEmpty(DOMDocument $dom): void {
        if ($dom->childNodes->length === 0) {
            throw new SnippetException('Document cannot be empty');
        }

        if ($dom->documentElement->childNodes->length === 0) {
            throw new SnippetException('Snippet content cannot be empty');
        }
    }
}
