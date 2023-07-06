<?php declare(strict_types=1);
/*
 * This file is part of Templado\Engine.
 *
 * Copyright (c) Arne Blankerts <arne@blankerts.de> and contributors
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Templado\Engine;

use DOMDocument;
use DOMElement;
use DOMNode;
use RuntimeException;

final class Merger {

    private DOMDocument $target;
    private MergeList $documents;
    private DOMElement $currentContext;

    /** @psalm-type list<bool> */
    private array $seen = [];

    public function merge(DOMDocument $target, MergeList $toMerge): void {
        $this->target = $target;
        $this->documents = $toMerge;

        $this->resetSeen();
        $this->process($target->documentElement);
    }

    private function process(DOMElement $context): void {
        $children = new SnapshotDOMNodelist($context->childNodes);

        while ($children->hasNext()) {
            $node = $children->getNext();

            if (!$node instanceof DOMElement) {
                continue;
            }
            $this->currentContext = $node;
            $this->processCurrent();
        }
    }

    private function processCurrent(): void {
        $nextSibling = $this->currentContext->nextSibling;
        if ($this->currentContext->hasAttribute('id')) {
            $id = $this->currentContext->getAttribute('id');

            $this->ensureNotSeen($id);
            $this->markAsSeen($id);

            if ($this->documents->has($id) && !$this->mergeIntoElement($id)) {
                return;
            }
        }

        $actualNext = $this->currentContext->nextSibling;
        if ($this->currentContext->hasChildNodes()) {
            $this->process($this->currentContext);
        }

        if ($nextSibling === null || $actualNext === null || $actualNext->isSameNode($nextSibling)) {
            return;
        }

        while (true) {
            if ($actualNext instanceof DOMElement) {
                $this->process($actualNext);
            }
            $actualNext = $actualNext->nextSibling;
            if ($actualNext === null || $actualNext->isSameNode($nextSibling)) {
                return;
            }
        }
    }

    private function mergeIntoElement(string $id): bool {
        foreach ($this->documents->get($id) as $toMerge) {
            assert($toMerge instanceof DOMDocument);

            $result = $this->mergeDocument($id, $toMerge);

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
            throw new \Exception(
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

    private function mergeDocument(string $id, DOMDocument $toMerge): ?DOMNode {
        $imported = $this->target->importNode($toMerge->documentElement, true);
        assert($imported instanceof DOMElement);

        $workNode = $imported;

        if ($imported->namespaceURI === 'https://templado.io/document/1.0' || $imported->namespaceURI === 'https://templado.io/snippet/1.0') {
            $workNode = $this->target->createDocumentFragment();
            $workNode->append(...$imported->childNodes);
        }

        if ($imported->hasAttribute('id') &&
            $imported->getAttribute('id') === $id) {
            $returnNode = $workNode->firstElementChild;

            $this->currentContext->replaceWith($workNode);

            return $returnNode;
        }

        $this->currentContext->append($workNode);

        return $this->currentContext;
    }

}
