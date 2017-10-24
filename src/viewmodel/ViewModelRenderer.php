<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMAttr;
use DOMElement;
use DOMNode;

class ViewModelRenderer {

    /**
     * @var array
     */
    private $stack;

    /**
     * @var array
     */
    private $stackNames;

    /**
     * @param DOMNode $context
     *
     * @throws ViewModelRendererException
     */
    private function walk(DOMNode $context) {
        if ($context instanceof DOMElement && $context->hasAttribute('property')) {
            $this->addToStack($context);
            $this->applyCurrent($context);
        }

        if ($context->hasChildNodes()) {
            foreach(new SnapshotDOMNodelist($context->childNodes) as $childNode) {
                /** @var \DOMNode $childNode */
                if ($childNode->parentNode === null) {
                    continue;
                }
                $this->walk($childNode);
            }
        }

        if ($context instanceof DOMElement && $context->hasAttribute('property')) {
            $this->dropFromStack();
        }
    }

    /**
     * @param DOMNode $context
     * @param object  $model
     *
     * @throws ViewModelRendererException
     */
    public function render(DOMNode $context, $model) {
        $this->stack = [$model];
        $this->stackNames = [];
        $this->walk($context);
    }

    /**
     * @param DOMElement $context
     *
     * @throws ViewModelRendererException
     */
    private function addToStack(DOMElement $context) {
        $model = $this->current();
        $property = $context->getAttribute('property');

        $this->ensureIsObject($model, $property);

        $this->stackNames[] = $property;

        foreach([$property, 'get' . ucfirst($property)] as $method) {
            if (method_exists($model, $method)) {
                $this->stack[] = $model->{$method}($context->nodeValue);

                return;
            }
        }

        if (method_exists($model, '__call')) {
            $this->stack[] = $model->{$property}($context->nodeValue);

            return;
        }

        throw new ViewModelRendererException(
            sprintf('Viewmodel method missing: $model->%s', implode('()->', $this->stackNames) . '()')
        );
    }

    /**
     * @return mixed
     */
    private function current() {
        return end($this->stack);
    }

    private function dropFromStack() {
        array_pop($this->stack);
        array_pop($this->stackNames);
    }

    /**
     * @param DOMElement $context
     *
     * @throws ViewModelRendererException
     */
    private function applyCurrent(DOMElement $context) {
        $model = $this->current();
        switch (gettype($model)) {
            case 'boolean': {
                $this->processBoolean($context, $model);

                return;
            }
            case 'string': {
                $this->processString($context, $model);

                return;
            }

            case 'object': {
                $this->processObject($context, $model);

                return;
            }

            case 'array': {
                $this->processArray($context, $model);

                return;
            }

            default: {
                throw new ViewModelRendererException(
                    sprintf('Unsupported type %s', gettype($model))
                );
            }
        }
    }

    /**
     * @param DOMElement $context
     * @param bool       $model
     */
    private function processBoolean(DOMElement $context, bool $model) {
        if ($model === false) {
            while ($context->hasChildNodes()) {
                $context->removeChild($context->lastChild);
            }

            $context->parentNode->removeChild($context);
        }
    }

    /**
     * @param DOMElement $context
     * @param string     $model
     */
    private function processString(DOMElement $context, string $model) {
        $context->nodeValue = $model;
    }

    /**
     * @param DOMElement $context
     * @param object     $model
     *
     * @throws ViewModelRendererException
     */
    private function processObject(DOMElement $context, $model) {
        switch(true) {
            case $model instanceOf \Iterator: {
                $this->processObjectAsIterator($context, $model);
                break;
            }

            default: {
                $this->processObjectAsModel($context, $model);
            }
        }
    }

    private function processObjectAsIterator(DOMElement $context, \Iterator $model) {
        foreach($model as $pos => $entry) {
            $this->processArrayEntry($context, $entry, $pos);
        }
        $this->cleanupArrayLeftovers($context);
    }

    /**
     * @param DOMElement $context
     * @param Object     $model
     */
    private function processObjectAsModel(DOMElement $context, $model) {
        if (method_exists($model, 'asString') ||
            method_exists($model, '__call')
        ) {
            $value = $model->asString();
            if ($value !== null) {
                $context->nodeValue = $value;
            }
        }

        foreach($context->attributes as $attribute) {
            $this->processAttribute($attribute, $model);
        }
    }

    /**
     * @param DOMElement $context
     * @param array      $model
     *
     * @throws ViewModelRendererException
     */
    private function processArray(DOMElement $context, array $model) {
        foreach($model as $pos => $entry) {
            $this->processArrayEntry($context, $entry, $pos);
        }
        $this->cleanupArrayLeftovers($context);
    }

    /**
     * @param DOMElement $context
     * @param            $entry
     * @param int        $pos
     *
     * @throws ViewModelRendererException
     */
    private function processArrayEntry(DOMElement $context, $entry, $pos) {
        /** @var DOMElement $clone */
        $clone = $context->cloneNode(true);
        $context->parentNode->insertBefore($clone, $context);
        $this->stack[] = $entry;
        $this->stackNames[] = $pos;
        $this->applyCurrent($clone);
        if ($clone->hasChildNodes()) {
            foreach(new SnapshotDOMNodelist($clone->childNodes) as $childNode) {
                $this->walk($childNode);
            }
        }
        $this->dropFromStack();
    }

    /**
     * @param DOMElement $context
     */
    private function cleanupArrayLeftovers(DOMElement $context) {
        $next = $context;
        $remove = [$context];
        while ($next = $next->nextSibling) {
            if (!$next instanceof DOMElement) {
                continue;
            }
            if ($next->getAttribute('property') === $context->getAttribute('property')) {
                $remove[] = $next;
            }
        }

        $parent = $context->parentNode;
        while ($context->hasChildNodes()) {
            $context->removeChild($context->lastChild);
        }
        foreach($remove as $node) {
            $parent->removeChild($node);
        }
    }

    /**
     * @param DOMAttr $attribute
     * @param object  $model
     *
     * @throws ViewModelRendererException
     */
    private function processAttribute(DOMAttr $attribute, $model) {
        foreach([$attribute->name, 'get' . ucfirst($attribute->name), '__call'] as $method) {

            if (!method_exists($model, $method)) {
                continue;
            }

            if ($method === '__call') {
                $method = $attribute->name;
            }

            $value = $model->{$method}($attribute->value);
            if ($value === null) {
                return;
            }

            if ($value === false) {
                /** @var $parent DOMElement */
                $parent = $attribute->parentNode;
                $parent->removeAttribute($attribute->name);

                return;
            }

            if (!is_string($value)) {
                throw new ViewModelRendererException(
                    sprintf('Attribute value must be string or boolean false - type %s received from $model->%s',
                        gettype($value),
                        implode('()->', $this->stackNames) . '()'
                    )
                );
            }

            $attribute->value = $value;

            return;
        }
    }

    /**
     * @param mixed  $model
     * @param string $property
     *
     * @throws ViewModelRendererException
     */
    private function ensureIsObject($model, $property) {
        if (!is_object($model)) {
            throw new ViewModelRendererException(
                sprintf(
                    'Trying to add "%s" failed - Non object (%s) on stack: $%s',
                    $property,
                    gettype($model),
                    implode('()->', $this->stackNames) . '() '
                )
            );
        }
    }

}
