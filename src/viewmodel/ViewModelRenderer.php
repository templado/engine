<?php declare(strict_types = 1);
namespace TheSeer\Templado;

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
     * @param object  $model
     */
    public function render(DOMNode $context, $model) {
        $this->stack      = [$model];
        $this->stackNames = ['model'];
        $this->walk($context);
    }

    /**
     * @param DOMNode $context
     */
    private function walk(DOMNode $context) {
        if ($context instanceof DOMElement && $context->hasAttribute('property')) {
            $this->addToStack($context);
            $this->applyCurrent($context);
        }

        if ($context->hasChildNodes()) {
            foreach($context->childNodes as $childNode) {
                $this->walk($childNode);
            }
        }

        if ($context instanceof DOMElement && $context->hasAttribute('property')) {
            $this->dropFromStack();
        }
    }

    /**
     * @param DOMElement $context
     *
     * @throws ViewModelRendererException
     */
    private function addToStack(DOMElement $context) {
        $model    = $this->current();
        $property = $context->getAttribute('property');

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
        $this->stackNames[] = $property;

        foreach([$property, 'get' . ucfirst($property)] as $method) {
            if (method_exists($model, $method)) {
                $this->stack[] = $model->{$method}($context->nodeValue);
                return;
            }
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
        if (method_exists($model, 'asString')) {
            $context->nodeValue = $model->asString();
        }

        foreach($context->attributes as $attribute) {
            $this->processAttribute($attribute, $model);
        }
    }

    /**
     * @param DOMElement $context
     * @param array      $model
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
     */
    private function processArrayEntry(DOMElement $context, $entry, $pos) {
        /** @var DOMElement $clone */
        $clone = $context->cloneNode(true);
        $context->parentNode->insertBefore($clone, $context);
        $this->stack[]      = $entry;
        $this->stackNames[] = $pos;
        $this->applyCurrent($clone);
        if ($clone->hasChildNodes()) {
            foreach($clone->childNodes as $childNode) {
                $this->walk($childNode);
            }
        }
        $this->dropFromStack();
    }

    /**
     * @param DOMElement $context
     */
    private function cleanupArrayLeftovers(DOMElement $context) {
        $next   = $context;
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
     * @throws \TheSeer\Templado\ViewModelRendererException
     */
    private function processAttribute(DOMAttr $attribute, $model) {
        foreach([$attribute->name, 'get' . ucfirst($attribute->name)] as $method) {

            if (!method_exists($model, $method)) {
                continue;
            }

            $value = $model->{$method}($attribute->value);
            if ($value === false || $value === null) {
                /** @var $parent DOMElement */
                $parent = $attribute->parentNode;
                $parent->removeAttribute($attribute->name);

                return;
            }

            if (!is_string($value)) {
                throw new ViewModelRendererException(
                    sprintf('Attribute value must be string or boolean false - type %s received', gettype($value))
                );
            }

            $attribute->value = $value;
            return;
        }
    }

}
