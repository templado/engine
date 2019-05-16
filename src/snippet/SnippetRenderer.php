<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;

class SnippetRenderer {

    /** @var SnippetListCollection */
    private $snippetListCollection;

    /** @var DOMElement */
    private $currentContext;

    public function __construct(SnippetListCollection $snippetListCollection) {
        $this->snippetListCollection = $snippetListCollection;
    }

    public function render(DOMElement $context): void {
        $children = $context->childNodes;

        for ($i = 0; $i < $children->length; $i++) {
            $node = $children->item($i);

            if (!$node instanceof DOMElement) {
                continue;
            }
            $this->currentContext = $node;
            $this->processCurrent();
        }
    }

    /**
     * @throws SnippetCollectionException
     */
    private function processCurrent(): void {
        if ($this->currentContext->hasAttribute('id')) {
            $id = $this->currentContext->getAttribute('id');

            if ($this->snippetListCollection->hasSnippetsForId($id) && !$this->applySnippetsToElement($id)) {
                return;
            }
        }

        if ($this->currentContext->hasChildNodes()) {
            $this->render($this->currentContext);
        }
    }

    /**
     * @throws \Templado\Engine\SnippetCollectionException
     */
    private function applySnippetsToElement($id): bool {
        $snippets = $this->snippetListCollection->getSnippetsForId($id);

        foreach ($snippets as $snippet) {
            $result = $snippet->applyTo($this->currentContext);

            if (!$this->currentContext->isSameNode($result)) {
                if (!$result instanceof DOMElement) {
                    // Context $node was replaced by a non DOMElement,
                    // so we cannot apply further snippets
                    return false;
                }
                /* @var DOMElement $node */
                $this->currentContext = $result;
            }
        }

        return true;
    }
}
