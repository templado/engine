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

use function array_key_exists;
use function gettype;
use function is_iterable;
use function is_object;
use function is_string;
use function lcfirst;
use function method_exists;
use function property_exists;
use function str_contains;
use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

final class ViewModelRenderer {
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private object $rootModel;
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private DOMNode $pointer;
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private bool $supported;
    /**  @psalm-suppress PropertyNotSetInConstructor */
    private DOMXPath $xp;
    private array $prefixModels = [];

    public function render(DOMNode $context, object $model): void {
        $this->rootModel = $model;
        $document        = $context->ownerDocument;

        if ($document === null) {
            throw new ViewModelRendererException(
                'Given context node must be connected to a document',
                ViewModelRendererException::NotInDocument
            );
        }

        $this->pointer   = $document->createComment('templado pointer node');
        $this->xp        = new DOMXPath($document);
        $this->supported = true;

        $this->walk($context, $model);
    }

    private function walk(DOMNode $context, object $model): void {
        if (!$context instanceof DOMElement) {
            return;
        }

        $parent = $context->parentNode;
        assert($parent instanceof DOMNode);

        if ($context->hasAttribute('prefix')) {
            $this->registerPrefix($context->getAttribute('prefix'));
        }

        if ($context->hasAttribute('resource')) {
            $model = $this->resolveResource($context->getAttribute('resource'));
        }

        $supportedBackup = null;

        if ($context->hasAttribute('vocab')) {
            $supportedBackup = $this->supported;
            $this->modelSupportsVocab($model, $context->getAttribute('vocab'));
        }

        if ($this->supported && $context->hasAttribute('property')) {
            $model = $this->processProperty($context, $model);
        }

        if (!$this->isConnected($parent, $context)) {
            return;
        }

        if ($context->hasChildNodes()) {
            $children = StaticNodeList::fromNodeList($context->childNodes);

            foreach ($children as $child) {
                if (!$this->isConnected($context, $child)) {
                    continue;
                }
                $this->walk($child, $model);
            }
        }

        if ($supportedBackup !== null) {
            $this->supported = $supportedBackup;
        }
    }

    private function registerPrefix(string $prefixString): void {
        $parts = explode(': ', $prefixString);

        if (count($parts) !== 2) {
            throw new ViewModelRendererException(
                'Invalid prefix definition',
                ViewModelRendererException::InvalidPrefixDefinition
            );
        }

        [$prefix, $method] = $parts;

        if (str_contains($method, ':')) {
            $this->prefixModels[$prefix] = null;

            return;
        }

        $result = match (true) {
            // method variants
            method_exists($this->rootModel, $method)         => $this->rootModel->{$method}(),
            method_exists($this->rootModel, 'get' . $method) => $this->rootModel->{'get' . $method}(),
            method_exists($this->rootModel, '__call')        => $this->rootModel->{$method}(),

            // property variants
            property_exists($this->rootModel, $method) => $this->rootModel->{$method},
            method_exists($this->rootModel, '__get')   => $this->rootModel->{$method},

            default => throw new ViewModelRendererException(
                sprintf('Cannot resolve prefix request for "%s"', $method),
                ViewModelRendererException::PrefixResolvingFailed
            )
        };

        if (!is_object($result)) {
            throw new ViewModelRendererException(
                'Prefix type must be an object',
                ViewModelRendererException::WrongTypeForPrefix
            );
        }

        $this->prefixModels[$prefix] = $result;
    }

    private function resolveResource(string $resource): object {
        $model = $this->rootModel;

        if (str_contains($resource, ':')) {
            [$prefix, $resource] = explode(':', $resource);
            $model               = $this->modelForPrefix($prefix);

            if ($model === null) {
                throw new ViewModelRendererException(
                    sprintf('Cannot resolve resource request for "%s" using prefix "%s"', $resource, $prefix),
                    ViewModelRendererException::ResourceResolvingWithPrefixFailed
                );
            }
        }

        $result = match (true) {
            // method variants
            method_exists($model, $resource)         => $model->{$resource}(),
            method_exists($model, 'get' . $resource) => $model->{'get' . $resource}(),
            method_exists($model, '__call')          => $model->{$resource}(),

            // property variants
            property_exists($model, $resource) => $model->{$resource},
            method_exists($model, '__get')     => $model->{$resource},

            default => throw new ViewModelRendererException(
                sprintf('Cannot resolve resource request for "%s"', $resource),
                ViewModelRendererException::ResourceResolvingFailed
            )
        };

        if (!is_object($result)) {
            throw new ViewModelRendererException(
                'Resouce type must be a object',
                ViewModelRendererException::WrongTypeForResource
            );
        }

        return $result;
    }

    private function modelForPrefix(string $prefix): ?object {
        if (!array_key_exists($prefix, $this->prefixModels)) {
            throw new ViewModelRendererException(
                'No model set for prefix',
                ViewModelRendererException::NoModelForPrefix
            );
        }

        return $this->prefixModels[$prefix];
    }

    private function modelSupportsVocab(object $model, string $requiredVocab): void {
        $modelVocab = match (true) {
            // method variants
            method_exists($model, 'vocab')    => $model->vocab($requiredVocab),
            method_exists($model, 'getVocab') => $model->getVocab($requiredVocab),
            method_exists($model, '__call')   => $model->vocab($requiredVocab),

            // property variants
            property_exists($model, 'vocab') => $model->vocab,
            method_exists($model, '__get')   => $model->vocab,

            default => $requiredVocab
        };

        if (!is_string($modelVocab)) {
            throw new ViewModelRendererException(
                'Result of vocab query must be of type string',
                ViewModelRendererException::WrongTypeForVocab
            );
        }

        $this->supported = $modelVocab === $requiredVocab;
    }

    private function isConnected(DOMNode $context, DOMNode $contextChild): bool {
        $current = $contextChild;

        while ($current->parentNode !== null) {
            $current = $current->parentNode;

            if ($current->isSameNode($context)) {
                return true;
            }
        }

        return false;
    }

    private function processProperty(DOMElement $context, object $model): object {
        $property = $context->getAttribute('property');

        if (str_contains($property, ':')) {
            [$prefix, $property] = explode(':', $property);
            $prefixModel         = $this->modelForPrefix($prefix);

            if ($prefixModel === null) {
                return $model;
            }

            $model = $prefixModel;
        }

        $result = match (true) {
            // method variants
            method_exists($model, $property)         => $model->{$property}($context->textContent),
            method_exists($model, 'get' . $property) => $model->{'get' . $property}($context->textContent),
            method_exists($model, '__call')          => $model->{$property}($context->textContent),

            // property variants
            property_exists($model, $property) => $model->{$property},
            method_exists($model, '__get')     => $model->{$property},

            default => throw new ViewModelRendererException(
                sprintf('Cannot resolve property request for "%s"', $property),
                ViewModelRendererException::ResolvingPropertyFailed
            )
        };

        if ($context->hasAttribute('typeof')) {
            if (!is_iterable($result) && !is_object($result)) {
                throw new ViewModelRendererException(
                    'TypeOf handling requires object / list of objects',
                    ViewModelRendererException::WrongTypeForTypeOf
                );
            }

            $this->conditionalApply($context, $result);

            return $model;
        }

        if (is_iterable($result)) {
            $this->iterableApply($context, $result);

            return $model;
        }

        if (is_string($result)) {
            $context->nodeValue   = '';
            $context->textContent = $result;

            return $model;
        }

        if ($result instanceof Remove || $result === false) {
            $context->remove();

            return $model;
        }

        if ($result instanceof Ignore || $result === true || $result === null) {
            return $model;
        }

        if (is_object($result)) {
            $this->objectApply($context, $result);

            return $result;
        }

        throw new ViewModelRendererException(
            'Unsupported type',
            ViewModelRendererException::UnsupportedTypeForProperty
        );
    }

    private function conditionalApply(DOMElement $context, object|iterable $model): void {
        if (!is_iterable($model)) {
            $model = [$model];
        }

        $parent = $context->parentNode;
        assert($parent instanceof DOMNode);

        $myPointer = $parent->insertBefore($this->pointer->cloneNode(), $context);

        foreach ($model as $current) {
            if (!is_object($current)) {
                throw new ViewModelRendererException(
                    'Model must be an object when used for type of checks',
                    ViewModelRendererException::WrongTypeForTypeOf
                );
            }

            if (!method_exists($current, 'typeOf')) {
                throw new ViewModelRendererException(
                    'Model must provide method typeOf for type of checks',
                    ViewModelRendererException::TypeOfMethodRequired
                );
            }

            $matches = $this->xp->query(
                sprintf(
                    'following-sibling::*[@property="%s" and @typeof="%s"]',
                    $context->getAttribute('property'),
                    $current->typeOf()
                ),
                $myPointer
            );

            if ($matches->count() === 0) {
                throw new ViewModelRendererException(
                    'No matching types found',
                    ViewModelRendererException::NoMatch
                );
            }

            $clone = $matches->item(0)->cloneNode(true);
            $parent->insertBefore($clone, $myPointer);

            assert($clone instanceof DOMElement);
            $this->objectApply($clone, $current);

            if ($clone->hasChildNodes()) {
                $list = StaticNodeList::fromNodeList($clone->childNodes);

                foreach ($list as $child) {
                    if (!$this->isConnected($clone, $child)) {
                        continue;
                    }
                    $this->walk($child, $current);
                }
            }
        }

        $list = StaticNodeList::fromNodeList($this->xp->query(
            sprintf('following-sibling::*[@property="%s"]', $context->getAttribute('property')),
            $myPointer
        ));

        foreach ($list as $node) {
            assert($node instanceof DOMElement);

            $node->remove();
        }

        $parent->removeChild($myPointer);
    }

    private function iterableApply(DOMElement $context, iterable $list): void {
        $ownerDocument = $context->ownerDocument;
        assert($ownerDocument instanceof DOMDocument);

        if ($context->isSameNode($ownerDocument->documentElement)) {
            throw new ViewModelRendererException(
                'Cannot apply multiple on root element',
                ViewModelRendererException::MultipleRootElements
            );
        }

        $parent = $context->parentNode;
        assert($parent instanceof DOMNode);

        $myPointer = $parent->insertBefore($this->pointer->cloneNode(), $context);

        foreach ($list as $model) {
            $clone = $context->cloneNode(true);
            $parent->insertBefore($clone, $myPointer);

            if (is_string($model)) {
                $clone->nodeValue   = '';
                $clone->textContent = $model;

                continue;
            }

            if (is_object($model) && !$model instanceof Signal) {
                assert($clone instanceof DOMElement);
                $this->objectApply($clone, $model);

                if ($clone->hasChildNodes()) {
                    $list = StaticNodeList::fromNodeList($clone->childNodes);

                    foreach ($list as $child) {
                        if (!$this->isConnected($clone, $child)) {
                            continue;
                        }
                        $this->walk($child, $model);
                    }
                }

                continue;
            }

            throw new ViewModelRendererException(
                'Unsupported type of model in list',
                ViewModelRendererException::UnsupportedTypeForProperty
            );
        }

        $list = StaticNodeList::fromNodeList($this->xp->query(
            sprintf('following-sibling::*[@property="%s"]', $context->getAttribute('property')),
            $myPointer
        ));

        foreach ($list as $node) {
            assert($node instanceof DOMElement);

            $node->remove();
        }

        $parent->removeChild($myPointer);
    }

    private function objectApply(DOMElement $context, object $model): void {
        if ($model instanceof Document) {
            $this->documentApply($context, $model);

            return;
        }

        if (method_exists($model, 'asString') || method_exists($model, '__call')) {
            $context->nodeValue = '';
            $textContent        = $model->asString($context->textContent);

            if (!is_string($textContent)) {
                throw new ViewModelRendererException(
                    'Cannot use non string type for text content',
                    ViewModelRendererException::StringRequired
                );
            }

            $context->textContent = $textContent;
        } elseif (method_exists($model, '__toString')) {
            $context->nodeValue   = '';
            $context->textContent = (string)$model;
        }

        $attributes = StaticNodeList::fromNamedNodeMap($context->attributes);

        foreach ($attributes as $attribute) {
            $name = lcfirst(
                str_replace(['-', ':'], '', ucwords($attribute->nodeName, '-:'))
            );

            if (in_array($name, ['property', 'prefix', 'vocab', 'resource'], true)) {
                continue;
            }

            $result = match (true) {
                // method variants
                method_exists($model, $name)         => $model->{$name}($attribute->nodeValue),
                method_exists($model, 'get' . $name) => $model->{'get' . $name}($attribute->nodeValue),
                method_exists($model, '__call')      => $model->{$name}($attribute->nodeValue),

                // property variants
                property_exists($model, $name) => $model->{$name},
                method_exists($model, '__get') => $model->{$name},

                default => Signal::ignore()
            };

            if ($result instanceof Ignore || $result === true || $result === null) {
                continue;
            }

            if ($result instanceof Remove || $result === false) {
                $context->removeChild($attribute);

                continue;
            }

            if (is_string($result)) {
                $attribute->nodeValue   = '';
                $attribute->textContent = $result;

                continue;
            }

            throw new ViewModelRendererException(
                \sprintf('Unsupported type "%s" for attribute', gettype($result)),
                ViewModelRendererException::WrongTypeForAttribute
            );
        }
    }

    private function documentApply(DOMElement $context, Document $model): void {
        $context->nodeValue = '';

        $ownerDocument = $context->ownerDocument;
        assert($ownerDocument instanceof DOMDocument);

        $document = $model->asDomDocument();
        $nodes    = [$document->documentElement];

        if ($document->documentElement->namespaceURI === Document::XMLNS) {
            $nodes = $document->documentElement->childNodes;
        }

        foreach ($nodes as $node) {
            $context->appendChild(
                $ownerDocument->importNode($node, true)
            );
        }
    }
}
