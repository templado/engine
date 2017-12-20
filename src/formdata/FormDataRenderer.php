<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMXPath;

class FormDataRenderer {

    /**
     * @throws FormDataRendererException
     */
    public function render(DOMElement $context, FormData $form) {
        try {
            $formElement = $this->findFormElement($context, $form->getIdentifier());

            $this->processInputElements($form, $formElement);
            $this->processSelectElements($form, $formElement);
            $this->processTextareaElement($form, $formElement);
        } catch (FormDataException $e) {
            throw new FormDataRendererException(
                $e->getMessage()
            );
        }
    }

    private function setInputValue(DOMElement $input, string $value) {
        $type = $input->getAttribute('type');
        switch ($type) {
            case 'file':
            case 'password':
                return;
            case 'radio':
            case 'checkbox':
                $this->toggleInput($input, $value);

                return;
            default:
                $input->setAttribute('value', $value);
        }
    }

    private function toggleInput(DOMElement $input, string $value) {
        $actualValue = $input->getAttribute('value');
        if ($actualValue === $value) {
            $input->setAttribute('checked', 'checked');

            return;
        }
        $input->removeAttribute('checked');
    }

    private function setSelectValue(DOMElement $select, string $value) {
        foreach($select->getElementsByTagName('option') as $option) {
            /** @var DOMElement $option */
            if ($option->getAttribute('value') === $value) {
                $option->setAttribute('selected', 'selected');
                continue;
            }
            $option->removeAttribute('selected');
        }
    }

    /**
     * @throws FormDataRendererException
     */
    private function findFormElement(DOMElement $context, string $identifier): DOMElement {
        $xp = new DOMXPath($context->ownerDocument);
        $result = $xp->query(
            sprintf('.//*[local-name() = "form" and (@id = "%1$s" or @name = "%1$s")]', $identifier),
            $context
        );
        switch ($result->length) {
            case 1: {
                return $result->item(0);
            }
            case 0: {
                throw new FormDataRendererException(
                    sprintf('No form with name or id "%s" found', $identifier)
                );
            }
            default: {
                throw new FormDataRendererException(
                    sprintf('Multiple forms found with name or id "%s"', $identifier)
                );

            }
        }
    }

    /**
     * @throws FormDataException
     */
    private function processInputElements(FormData $form, DOMElement $formElement) {
        foreach($formElement->getElementsByTagName('input') as $input) {
            /** @var DOMElement $input */

            $name = $input->getAttribute('name');
            if (!$form->hasKey($name)) {
                continue;
            }
            $this->setInputValue(
                $input,
                $form->getValue(
                    $name
                )
            );
        }
    }

    /**
     * @throws FormDataException
     */
    private function processSelectElements(FormData $form, DOMElement $formElement) {
        foreach($formElement->getElementsByTagName('select') as $select) {
            /** @var DOMElement $select */
            $name = $select->getAttribute('name');
            if (!$form->hasKey($name)) {
                continue;
            }
            $this->setSelectValue(
                $select,
                $form->getValue(
                    $select->getAttribute('name')
                )
            );
        }
    }

    /**
     * @throws FormDataException
     */
    private function processTextareaElement(FormData $form, DOMElement $formElement) {
        foreach($formElement->getElementsByTagName('textarea') as $textarea) {
            /** @var DOMElement $textarea */
            $name = $textarea->getAttribute('name');
            if (!$form->hasKey($name)) {
                continue;
            }
            $textarea->nodeValue = '';
            $textarea->appendChild(
                $textarea->ownerDocument->createTextNode(
                    $form->getValue(
                        $textarea->getAttribute('name')
                    )
                )
            );
        }
    }

}
