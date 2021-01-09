<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;

class SnippetRenderer {

    /** @var SnippetListCollection */
    private $snippetListCollection;

    /** @var DOMElement */
    private $currentContext;

    /** @var bool[] */
    private $seen;

    public function __construct(SnippetListCollection $snippetListCollection) {
        $this->snippetListCollection = $snippetListCollection;
    }

    public function render(DOMElement $context): void {
        $this->resetSeen();
        $this->process($context);
    }

    private function process(DOMElement $context): void {
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

            $this->ensureNotSeen($id);
            $this->markAsSeen($id);

            if ($this->snippetListCollection->hasSnippetsForId($id) && !$this->applySnippetsToElement($id)) {
                return;
            }
        }

        if ($this->currentContext->hasChildNodes()) {
            $this->process($this->currentContext);
        }
    }

    /**
     * @throws \Templado\Engine\SnippetCollectionException
     */
    private function applySnippetsToElement(string $id): bool {
        $snippets = $this->snippetListCollection->getSnippetsForId($id);

        foreach ($snippets as $snippet) {
            $result = $snippet->applyTo($this->currentContext);

            if (!$this->currentContext->isSameNode($result)) {
                if (!$result instanceof DOMElement) {
                    // Context $node was replaced by a non DOMElement,
                    // so we cannot apply further snippets
                    return false;
                }

                $this->currentContext = $result;
            }
        }

        return true;
    }

    private function resetSeen(): void {
        $this->seen = [];
    }

    private function ensureNotSeen(string $id): void {
        if (isset($this->seen[$id])) {
            throw new SnippetRendererException(
                \sprintf(
                    'Duplicate id "%s" in Document detected - bailing out.',
                    $id
                )
            );
        }
    }

    private function markAsSeen(string $id): void {
        $this->seen[$id] = true;
    }

}
