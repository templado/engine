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

use function sprintf;
use DOMDocument;
use DOMElement;
use DOMXPath;

/** @psalm-suppress MissingConstructor */
final class CSRFProtectionRenderer {
    private CSRFProtection $protection;

    private DOMDocument $dom;
    private DOMXPath$xp;

    public function render(DOMElement $context, CSRFProtection $protection): void {
        $this->protection = $protection;

        if ($context->ownerDocument === null) {
            throw new CSRFProtectionRendererException('Context element must be part of a document');
        }
        $this->dom = $context->ownerDocument;
        $this->xp  = new DOMXPath($this->dom);

        foreach ($context->getElementsByTagName('form') as $form) {
            $this->getCSRFField($form)->setAttribute(
                'value',
                $protection->tokenValue()
            );
        }
    }

    private function getCSRFField(DOMElement $form): DOMElement {
        $nodeList = $this->xp->query(
            sprintf('.//*[local-name() = "input" and @name="%s"]', $this->protection->fieldName()),
            $form
        );

        if ($nodeList->length === 0) {
            return $this->createField($form);
        }

        $fieldNode = $nodeList->item(0);
        assert($fieldNode instanceof DOMElement);

        return $fieldNode;
    }

    private function createField(DOMElement $form): DOMElement {
        if ($form->namespaceURI !== null) {
            $input = $this->dom->createElementNS($form->namespaceURI, 'input');
        } else {
            $input = $this->dom->createElement('input');
        }

        $form->insertBefore($input, $form->firstChild);
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', $this->protection->fieldName());

        return $input;
    }
}
