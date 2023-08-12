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

use function array_shift;
use function assert;
use function explode;
use function implode;
use function in_array;
use function sprintf;
use function str_contains;
use DOMDocument;
use DOMElement;
use DOMXPath;

final class FormDataRenderer {
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private DOMXPath $xp;
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private FormData $form;
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private DOMElement $originalContext;
    private string $elementsXPath = 'local-name() = "input" or local-name() = "select" or local-name() = "textarea"';

    public function render(DOMElement $context, FormData $form): void {
        if (!$context->ownerDocument instanceof DOMDocument) {
            throw new FormDataRendererException('Context must be connected to a DOMDocument');
        }

        $this->xp              = new DOMXPath($context->ownerDocument);
        $this->form            = $form;
        $this->originalContext = $context;

        $this->processElements(
            $this->findFormElements($context)
        );
    }

    private function processElements(StaticNodeList $elements): void {
        foreach ($elements as $element) {
            assert($element instanceof DOMElement);

            switch ($element->localName) {
                case 'select': {
                    $this->processSelect($element);

                    break;
                }
                case 'textarea': {
                    $this->processTextArea($element);

                    break;
                }

                default: {
                    $this->processInput($element);
                }
            }
        }
    }

    private function processInput(DOMElement $element): void {
        $name = $this->nameToLookupKey($element);

        if (!$this->form->has($name)) {
            return;
        }

        $value = $this->form->value($name);
        $type  = $element->getAttribute('type');

        switch ($type) {
            case 'file':
            case 'password':
                return;
            case 'radio':
            case 'checkbox':
                $required = $element->getAttribute('value');

                if ($required === $value) {
                    $element->setAttribute('checked', 'checked');

                    return;
                }

                $element->removeAttribute('checked');

                return;

            default:
                $element->setAttribute('value', $value);
        }
    }

    private function processTextArea(DOMElement $element): void {
        $name = $this->nameToLookupKey($element);

        if (!$this->form->has($name)) {
            return;
        }

        $value              = $this->form->value($name);
        $element->nodeValue = '';
        $element->appendChild(
            $this->originalContext->ownerDocument->createTextNode($value)
        );
    }
    private function processSelect(DOMElement $element): void {
        $name = $element->getAttribute('name');

        $options = $this->xp->query(
            sprintf(
                './/*[local-name() = "select" and starts-with(@name, "%2$s") and (
                        @form="%1$s" or ancestor::*[local-name()="form" and (@id = "%1$s" or @name = "%1$s")]
                    )]//*[local-name() = "option"]',
                $this->form->identifier(),
                $name
            ),
            $this->originalContext
        );

        $list      = StaticNodeList::fromNodeList($options);
        $values    = [];
        $fragments = explode('[]', $name);
        $rootName  = array_shift($fragments);

        foreach ($list as $pos => $option) {
            assert($option instanceof DOMElement);

            $option->removeAttribute('selected');

            $name = count($fragments) > 0 ? $rootName . '[' . $pos . ']' . implode('[0]', $fragments) : $rootName;

            if (!$this->form->has($name)) {
                continue;
            }

            $values[] = $this->form->value($name);
        }

        foreach ($list as $option) {
            assert($option instanceof DOMElement);

            if (in_array($option->getAttribute('value'), $values, true)) {
                $option->setAttribute('selected', 'selected');
            }
        }
    }

    private function findFormElements(DOMElement $context): StaticNodeList {
        $xpath = './/*[(%2$s) and (@form="%1$s" or ancestor::*[local-name()="form" and (@id = "%1$s" or @name = "%1$s")])]';

        $formIdentifier = $this->form->identifier();
        $result         = $this->xp->query(sprintf($xpath, $formIdentifier, $this->elementsXPath), $context);

        if ($result->count() === 0) {
            throw new FormDataRendererException(sprintf('No form or elements for form "%s" found', $formIdentifier));
        }

        return StaticNodeList::fromNodeList($result);
    }

    private function nameToLookupKey(DOMElement $element): string {
        $name = $element->getAttribute('name');

        if (!str_contains($name, '[]')) {
            return $name;
        }
        $fragments = explode('[]', $name);

        $xpath = sprintf(
            './/*[(%2$s) and starts-with(@name,"%3$s") and (@form="%1$s" or ancestor::*[local-name()="form" and (@id = "%1$s" or @name = "%1$s")])]',
            $this->form->identifier(),
            $this->elementsXPath,
            $fragments[0] . '[]'
        );

        $count = 0;
        foreach ($this->xp->query($xpath, $this->originalContext) as $match) {
            assert($match instanceof DOMElement);

            if ($match->isSameNode($element)) {
                break;
            }

            $count++;
        }
        $name = array_shift($fragments) . '[' . $count . ']';

        return $name . implode('[0]', $fragments);
    }
}
