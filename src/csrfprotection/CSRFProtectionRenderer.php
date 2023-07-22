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
use DOMElement;
use DOMXPath;

/** @psalm-suppress MissingConstructor */
final class CSRFProtectionRenderer {
    /** @var CSRFProtection */
    private $protection;

    /** @var DOMXPath */
    private $xp;

    public function render(DOMElement $context, CSRFProtection $protection): void {
        $this->protection = $protection;
        $this->xp         = new DOMXPath($context->ownerDocument);

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
            $input = $form->ownerDocument->createElementNS($form->namespaceURI, 'input');
        } else {
            $input = $form->ownerDocument->createElement('input');
        }

        $form->insertBefore($input, $form->firstChild);
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', $this->protection->fieldName());

        return $input;
    }
}
