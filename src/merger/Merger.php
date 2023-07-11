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
use DOMXPath;
use RuntimeException;

final class Merger {

    private MergeList $documents;

    /** @var array<string, DOMNode> */
    private array $seen;

    public function merge(DOMDocument $target, MergeList $toMerge): void {
        $this->documents = $toMerge;
        $this->seen = [];

        if ($target->documentElement === null) {
            throw new MergerException('Cannot merge into a document without a root element');
        }

        $this->processContext($target->documentElement);
    }

    private function processContext(DOMElement $context) {
        $owner = $context->ownerDocument;
        $nodes = new SnapshotDOMNodelist(
            (new DOMXPath($owner))->query('.//*[@id]', $context)
        );

        while($nodes->hasNext()) {
            $contextChild = $nodes->getNext();
            assert($contextChild instanceof DOMElement);

            if (!$this->isConnected($context, $contextChild)) {
                continue;
            }

            $id = $contextChild->getAttribute('id');
            if (!$this->documents->has($id)) {
                continue;
            }

            if (isset($this->seen[$id])) {
                throw new RuntimeException(
                    sprintf('Duplicate id "%s" in document detected - bailing out.', $id)
                );
            }
            $this->seen[$id] = true;

            foreach($this->documents->get($id) as $childDocument) {
                assert($childDocument instanceof DOMDocument);

                $import = $owner->importNode($childDocument->documentElement, true);
                assert($import instanceof DOMElement);

                $this->processContext($import);
                $this->mergeIn($contextChild, $import);
            }
        }

    }

    private function isConnected(DOMElement $context, DOMElement $contextChild) {
        $current = $contextChild;
        while ($current->parentNode !== null) {
            $current = $current->parentNode;
            if ($current->isSameNode($context)) {
                return true;
            }
        }

        return false;
    }

    private function mergeIn(DOMElement $contextChild, DOMElement $import): DOMElement {
        $workContext = [$import];
        if ($import->namespaceURI === Document::XMLNS) {
            $workContext = new SnapshotDOMNodelist($import->childNodes);
        }

        if ($this->shouldReplaceCurrent($import, $contextChild)) {
            $contextChild->after(...$workContext);
            $contextChild->remove();

            return $import;
        }

        $contextChild->append(...$workContext);

        return $import;
    }

    private function shouldReplaceCurrent(DOMElement $import, DOMElement $contextChild): bool {
        return $import->hasAttribute('id') && $import->getAttribute('id') === $contextChild->getAttribute('id');
    }

}
