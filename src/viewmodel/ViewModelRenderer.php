<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMAttr;
use DOMDocumentFragment;
use DOMElement;
use DOMNode;

class ViewModelRenderer {

    /** @var array */
    private $stack = [];

    /** @var string[] */
    private $stackNames = [];

    /** @var SnapshotDOMNodelist[] */
    private $listStack = [];

    /** @var object */
    private $resourceModel;

    /** @var array */
    private $prefixes = [];

    /**
     * @throws ViewModelRendererException
     */
    public function render(DOMNode $context, object $model): void {
        $this->resourceModel = $model;
        $this->stack         = [$model];
        $this->stackNames    = [];
        $this->listStack     = [];
        $this->prefixes      = [];
        $this->walk($context);
    }

    /**
     * @throws ViewModelRendererException
     */
    private function walk(DOMNode $context): void {
        if (!$context instanceof DOMElement) {
            return;
        }

        $stackAdded = 0;

        if ($context->hasAttribute('prefix')) {
            $this->resolvePrefixDefinition($context->getAttribute('prefix'));
        }

        if ($context->hasAttribute('resource')) {
            $this->addResourceToStack($context);
            $stackAdded++;
        }

        if ($context->hasAttribute('property')) {
            $this->addToStack($context);
            $stackAdded++;
            $context = $this->applyCurrent($context);
        }

        if ($context->hasChildNodes()) {
            $list              = new SnapshotDOMNodelist($context->childNodes);
            $this->listStack[] = $list;

            while ($list->hasNext()) {
                $childNode = $list->getNext();
                /* @var \DOMNode $childNode */
                $this->walk($childNode);
            }
            \array_pop($this->listStack);
        }

        while ($stackAdded > 0) {
            $this->dropFromStack();
            $stackAdded--;
        }
    }

    /**
     * @throws ViewModelRendererException
     */
    private function addToStack(DOMElement $context): void {
        $model    = $this->current();
        $property = $context->getAttribute('property');

        if (\substr_count($property, ':') === 1) {
            [$prefix, $property] = \explode(':', $property);

            if (!\array_key_exists($prefix, $this->prefixes)) {
                throw new ViewModelRendererException(\sprintf('Undefined prefix %s', $prefix));
            }

            $model = $this->prefixes[$prefix];

            if ($model === null) {
                return;
            }
        }

        $this->ensureIsObject($model, $property);

        $this->stackNames[] = $property;

        foreach ([$property, 'get' . \ucfirst($property)] as $method) {
            if (\method_exists($model, $method)) {
                $this->stack[] = $model->{$method}($context->nodeValue);

                return;
            }
        }

        if (\method_exists($model, '__call')) {
            $this->stack[] = $model->{$property}($context->nodeValue);

            return;
        }

        throw new ViewModelRendererException(
            \sprintf('Viewmodel method missing: $model->%s', \implode('()->', $this->stackNames) . '()')
        );
    }

    private function current() {
        return \end($this->stack);
    }

    private function dropFromStack(): void {
        \array_pop($this->stack);
        \array_pop($this->stackNames);
    }

    /**
     * @throws ViewModelRendererException
     */
    private function applyCurrent(DOMElement $context): DOMNode {
        /** @psalm-suppress MixedAssignment */
        $model = $this->current();

        switch (\gettype($model)) {
            case 'boolean': {
                /** @var bool $model */
                return $this->processBoolean($context, $model);
            }
            case 'string': {
                /** @var string $model */
                $this->processString($context, $model);

                return $context;
            }
            case 'object': {
                /** @var object $model */
                return $this->processObject($context, $model);
            }

            case 'array': {
                return $this->processArray($context, $model);
            }

            default: {
                throw new ViewModelRendererException(
                    \sprintf(
                        'Value returned by $model->%s must not be of type %s',
                        \implode('()->', $this->stackNames) . '()',
                        \gettype($model)
                    )
                );
            }
        }
    }

    /**
     * @throws ViewModelRendererException
     *
     * @return DOMDocumentFragment|DOMElement
     */
    private function processBoolean(DOMElement $context, bool $model) {
        if ($model === true) {
            return $context;
        }

        if ($context->isSameNode($context->ownerDocument->documentElement)) {
            throw new ViewModelRendererException('Cannot remove root element');
        }

        $this->removeNodeFromCurrentSnapshotList($context);
        $context->parentNode->removeChild($context);

        return $context->ownerDocument->createDocumentFragment();
    }

    private function processString(DOMElement $context, string $model): void {
        $context->nodeValue   = '';
        $context->textContent = $model;
    }

    /**
     * @throws ViewModelRendererException
     * @return \DOMDocumentFragment|\DOMElement
     */
    private function processObject(DOMElement $context, object $model) {
        if (\is_iterable($model)) {
            /** @var iterable $model */
            return $this->processArray($context, $model);
        }

        return $this->processObjectAsModel($context, $model);
    }

    /**
     * @throws ViewModelRendererException
     */
    private function processObjectAsModel(DOMElement $context, object $model): DOMElement {
        $container   = $this->moveToContainer($context);
        $workContext = $this->selectMatchingWorkContext($container->firstChild, $model);

        if (\method_exists($model, 'asString') ||
            \method_exists($model, '__call')
        ) {
            /** @psalm-suppress MixedAssignment */
            $value = $model->asString($workContext->nodeValue);

            if (!in_array(\gettype($value), ['null','string'])) {
                throw new ViewModelRendererException(
                    sprintf("Method \$model->%s must return 'null' or 'string', got '%s'",
                        \implode('()->', $this->stackNames) . '()->asString()',
                        \gettype($value)
                    )
                );
            }

            /** @psalm-var null|string $value */
            if ($value !== null) {
                $workContext->nodeValue   = '';
                $workContext->textContent = $value;
            }
        }

        foreach (new SnapshotAttributeList($workContext->attributes) as $attribute) {
            $this->processAttribute($attribute, $model);
        }

        $container->parentNode->insertBefore($workContext, $container);
        $container->parentNode->removeChild($container);

        return $workContext;
    }

    /**
     * @throws ViewModelRendererException
     */
    private function processArray(DOMElement $context, iterable $model): DOMDocumentFragment {
        $count = $this->getElementCount($model);

        if ($count > 1 && $context->isSameNode($context->ownerDocument->documentElement)) {
            throw new ViewModelRendererException(
                'Cannot render multiple copies of root element'
            );
        }

        if ($count === 0) {
            return $this->processBoolean($context, false);
        }

        $container = $this->moveToContainer($context);

        /**
         * @psalm-suppress MixedAssignment
         * @psalm-var int $pos
         */
        foreach ($model as $pos => $entry) {
            $subcontext = $container->cloneNode(true);
            $container->parentNode->insertBefore($subcontext, $container);

            $result = $this->processArrayEntry($subcontext->firstChild, $entry, $pos);

            $container->parentNode->insertBefore($result, $subcontext);
            $container->parentNode->removeChild($subcontext);
        }

        $fragment = $container->ownerDocument->createDocumentFragment();
        $container->parentNode->removeChild($container);

        return $fragment;
    }

    /**
     * @throws ViewModelRendererException
     * @psalm-suppress MissingParamType
     */
    private function processArrayEntry(DOMElement $context, $entry, int $pos): DOMElement {
        $workContext = $this->selectMatchingWorkContext($context, $entry);
        /* @var DOMElement $clone */
        $this->stack[]      = $entry;
        $this->stackNames[] = (string)$pos;

        $this->applyCurrent($workContext);

        if ($workContext->hasChildNodes()) {
            $list              = new SnapshotDOMNodelist($workContext->childNodes);
            $this->listStack[] = $list;

            while ($list->hasNext()) {
                $this->walk($list->getNext());
            }
            \array_pop($this->listStack);
        }
        $this->dropFromStack();

        return $workContext;
    }

    /**
     * @throws ViewModelRendererException
     */
    private function processAttribute(DOMAttr $attribute, object $model): void {
        $attributeName = $attribute->nodeName;

        if (\strpos($attributeName, '-') !== false) {
            $parts = \explode('-', $attributeName);
            \array_walk(
                $parts,
                static function (string &$value, int $pos): void {
                    $value = \ucfirst($value);
                }
            );
            $attributeName = \implode('', $parts);
        }

        foreach ([$attributeName, 'get' . \ucfirst($attributeName), '__call'] as $method) {
            if (!\method_exists($model, $method)) {
                continue;
            }

            if ($method === '__call') {
                $method = $attribute->name;
            }

            /** @psalm-var null|bool|string $value */
            $value = $model->{$method}($attribute->value);

            if ($value === null) {
                return;
            }

            /** @var DOMElement $parent */
            $parent = $attribute->parentNode;

            if ($value === false) {
                $parent->removeAttribute($attribute->name);

                return;
            }

            if (!\is_string($value)) {
                throw new ViewModelRendererException(
                    \sprintf(
                        'Attribute value must be string or boolean false - type %s received from $model->%s',
                        \gettype($value),
                        \implode('()->', $this->stackNames) . '()'
                    )
                );
            }

            $parent->setAttribute($attribute->name, $value);

            return;
        }
    }

    /**
     * @throws ViewModelRendererException
     * @psalm-assert object $mode
     * @psalm-suppress MissingParamType
     */
    private function ensureIsObject($model, string $property): void {
        if (!\is_object($model)) {
            throw new ViewModelRendererException(
                \sprintf(
                    'Trying to add "%s" failed - Non object (%s) on stack: $%s',
                    $property,
                    \gettype($model),
                    \implode('()->', $this->stackNames) . '() '
                )
            );
        }
    }

    /**
     * @throws ViewModelRendererException
     * @psalm-suppress MissingParamType
     */
    private function selectMatchingWorkContext(DOMElement $context, $entry): DOMElement {
        if (!$context->hasAttribute('typeof')) {
            return $context;
        }

        if (!is_object($entry)) {
            throw new ViewModelRendererException(
                \sprintf(
                    "Cannot call 'typeOf' on none object type '%s' returned from \$model->%s()",
                    \gettype($entry),
                    \implode('()->', $this->stackNames)
                )
            );
        }

        if (!\method_exists($entry, 'typeOf')) {
            throw new ViewModelRendererException(
                \sprintf(
                    "No 'typeOf' method in model returned from \$model->%s() but current context is conditional",
                    \implode('()->', $this->stackNames)
                )
            );
        }

        /** @psalm-suppress MixedAssignment */
        $requestedTypeOf = $entry->typeOf();
        if (!is_string($requestedTypeOf)) {
            throw new ViewModelRendererException(
                \sprintf(
                    "Return value of \$model->%s()->typeOf() must be string, got '%s'",
                    \implode('()->', $this->stackNames),
                    \gettype($entry)
                )
            );
        }

        if ($context->getAttribute('typeof') === $requestedTypeOf) {
            return $context;
        }

        $xp   = new \DOMXPath($context->ownerDocument);
        $list = $xp->query(
            \sprintf(
                '(following-sibling::*)[@property="%s" and @typeof="%s"]',
                $context->getAttribute('property'),
                $requestedTypeOf
            ),
            $context
        );

        $newContext = $list->item(0);

        if (!$newContext instanceof DOMElement) {
            throw new ViewModelRendererException(
                \sprintf(
                    "Context for type '%s' not found.",
                    $requestedTypeOf
                )
            );
        }

        return $newContext;
    }

    private function moveToContainer(DOMElement $context): DOMElement {
        $container = $context->ownerDocument->createElement('container');
        $context->parentNode->insertBefore($container, $context);

        $xp   = new \DOMXPath($container->ownerDocument);
        $list = $xp->query(
            \sprintf('*[@property="%s"]', $context->getAttribute('property')),
            $context->parentNode
        );

        foreach ($list as $node) {
            $container->appendChild($node);
            $this->removeNodeFromCurrentSnapshotList($node);
        }

        return $container;
    }

    private function removeNodeFromCurrentSnapshotList(DOMElement $context): void {
        $stackList = \end($this->listStack);

        if ((!$stackList instanceof SnapshotDOMNodelist) || !$stackList->hasNode($context)) {
            return;
        }
        $stackList->removeNode($context);
    }

    private function getElementCount(iterable $model): int {
        if (\is_array($model) || $model instanceof \Countable) {
            return \count($model);
        }

        throw new ViewModelRendererException(
            \sprintf(
                'Class %s must implement \Countable to be used as array',
                \get_class($model)
            )
        );
    }

    private function addResourceToStack(DOMElement $context): void {
        $resource = $context->getAttribute('resource');

        $this->stackNames[] = $resource;

        foreach ([$resource, 'get' . \ucfirst($resource)] as $method) {
            if (\method_exists($this->resourceModel, $method)) {
                $this->stack[] = $this->resourceModel->{$method}();

                return;
            }
        }

        if (\method_exists($this->resourceModel, '__call')) {
            $this->stack[] = $this->resourceModel->{$resource}();

            return;
        }

        throw new ViewModelRendererException(
            \sprintf('Resource Viewmodel method missing: $model->%s', \implode('()->', $this->stackNames) . '()')
        );
    }

    private function resolvePrefixDefinition(string $prefixDefinition): void {
        $parts = \explode(' ', $prefixDefinition);

        if (\count($parts) !== 2) {
            throw new ViewModelRendererException(
                \sprintf('Invalid prefix definition "%s" - must be of format "prefix resourcename"', $prefixDefinition)
            );
        }

        [$prefix, $resource] = $parts;
        $prefix              = \rtrim($prefix, ':');

        if (\strpos($resource, ':') !== false) {
            $this->prefixes[$prefix] = null;

            return;
        }

        foreach ([$resource, 'get' . \ucfirst($resource)] as $method) {
            if (\method_exists($this->resourceModel, $method)) {
                $this->prefixes[$prefix] = $this->resourceModel->{$method}();

                return;
            }
        }

        if (\method_exists($this->resourceModel, '__call')) {
            $this->prefixes[$prefix] = $this->resourceModel->{$resource}();

            return;
        }

        throw new ViewModelRendererException(
            \sprintf('No method %s to resolve prefix %s', $resource, $prefix)
        );
    }
}
