<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMXPath;

class CSRFProtectionRenderer {

    /** @var CSRFProtection */
    private $protection;

    /** @var DOMXPath */
    private $xp;

    public function render(DOMElement $context, CSRFProtection $protection) {
        $this->protection = $protection;

        foreach($context->getElementsByTagName('form') as $form) {
            $this->getCSRFField($form)->setAttribute(
                'value',
                $protection->getTokenValue()
            );
        }
    }

    private function getCSRFField(DOMElement $form): DOMElement {
        if (!$this->xp instanceof DOMXPath) {
            $this->xp = new DOMXPath($form->ownerDocument);
        }
        $nodeList = $this->xp->query(
            sprintf('.//input[@name="%s"]', $this->protection->getFieldName()),
            $form
        );
        if ($nodeList->length === 0) {
            return $this->createField($form);
        }

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
