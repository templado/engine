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

final class FormDataRenderer {
    /**
     * @throws FormDataRendererException
     */
    public function render(DOMElement $context, FormData $form): void {
        $formElement = $this->findFormElement($context, $form->getIdentifier());

        $this->processInputElements($form, $formElement);
        $this->processSelectElements($form, $formElement);
        $this->processTextareaElement($form, $formElement);
    }

    private function setInputValue(DOMElement $input, string $value): void {
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

    private function toggleInput(DOMElement $input, string $value): void {
        $actualValue = $input->getAttribute('value');

        if ($actualValue === $value) {
            $input->setAttribute('checked', 'checked');

            return;
        }
        $input->removeAttribute('checked');
    }

    private function setSelectValue(DOMElement $select, string $value): void {
        foreach ($select->getElementsByTagName('option') as $option) {
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
        if ($context->localName === 'form' &&
            ($context->getAttribute('id') === $identifier ||
             $context->getAttribute('name') === $identifier)) {
            return $context;
        }

        $dom = $context->ownerDocument;
        assert($dom instanceof DOMDocument);

        $xp     = new DOMXPath($dom);
        $result = $xp->query(
            sprintf('.//*[local-name() = "form" and (@id = "%1$s" or @name = "%1$s")]', $identifier),
            $context
        );

        switch ($result->length) {
            case 1: {
                $node = $result->item(0);
                assert($node instanceof DOMElement);

                return $node;
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
    private function processInputElements(FormData $form, DOMElement $formElement): void {
        foreach ($formElement->getElementsByTagName('input') as $input) {
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
    private function processSelectElements(FormData $form, DOMElement $formElement): void {
        foreach ($formElement->getElementsByTagName('select') as $select) {
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
    private function processTextareaElement(FormData $form, DOMElement $formElement): void {
        $owner = $formElement->ownerDocument;
        assert($owner instanceof DOMDocument);

        foreach ($formElement->getElementsByTagName('textarea') as $textarea) {
            $name = $textarea->getAttribute('name');

            if (!$form->hasKey($name)) {
                continue;
            }
            $textarea->nodeValue = '';
            $textarea->appendChild(
                $owner->createTextNode(
                    $form->getValue(
                        $textarea->getAttribute('name')
                    )
                )
            );
        }
    }
}
