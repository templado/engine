<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMXPath;

/** @psalm-suppress MissingConstructor */
class CSRFProtectionRenderer {

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
                $protection->getTokenValue()
            );
        }
    }

    private function getCSRFField(DOMElement $form): DOMElement {
        $nodeList = $this->xp->query(
            \sprintf('.//*[local-name() = "input" and @name="%s"]', $this->protection->getFieldName()),
            $form
        );

        if ($nodeList->length === 0) {
            return $this->createField($form);
        }

        /** @psalm-var \DOMElement */
        return $nodeList->item(0);
    }

    private function createField(DOMElement $form): DOMElement {
        if ($form->namespaceURI !== null) {
            $input = $form->ownerDocument->createElementNS($form->namespaceURI, 'input');
        } else {
            $input = $form->ownerDocument->createElement('input');
        }

        $form->insertBefore($input, $form->firstChild);
        $input->setAttribute('type', 'hidden');
        $input->setAttribute('name', $this->protection->getFieldName());

        return $input;
    }
}
