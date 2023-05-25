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

use function iterator_to_array;
use function libxml_clear_errors;
use function libxml_get_errors;
use function libxml_get_last_error;
use function libxml_use_internal_errors;
use DOMDocument;

final readonly class Templado {
    public static function fromString(string $markup, ?Id $id = null): self {
        libxml_use_internal_errors(true);
        libxml_clear_errors();

        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $tmp                     = $dom->loadXML($markup);

        if (!$tmp || libxml_get_last_error()) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            throw new TempladoParsingException(...$errors);
        }

        return self::fromDomDocument($dom, $id);
    }

    public static function fromDomDocument(DOMDocument $dom, ?Id $id = null): self {
        return new self(
            $dom,
            $id
        );
    }

    private function __construct(
        private DOMDocument $dom,
        private ?Id $id
    ) {
    }

    public function id(): ?Id {
        return $this->id;
    }

    public function extract(Selector $selector, ?Id $id = null): self {
        $exportDom = new DOMDocument;
        $selection = $selector->select($this->dom->documentElement);

        if ($selection->isEmpty()) {
            throw new TempladoException('Selection cannot be empty');
        }

        if (count($selection) === 1) {
            $exportDom->appendChild(
                $exportDom->importNode(
                    $selection->getIterator()->current(),
                    true
                )
            );

            return new self($exportDom, $id);
        }

        $exportDom->loadXML('<templado:document xmlns:templado="https://templado.io/document/1.0" />');

        foreach ($selection as $node) {
            $exportDom->documentElement->appendChild(
                $exportDom->importNode($node, true)
            );
        }

        return new self($exportDom, $id);
    }

    public function asString(?Serializer $serializer = null): string {
        return $serializer->serialize($this->dom);
    }

    public function mergeIn(self|TempladoCollection ...$toMerge): self {
        $documents = [];

        foreach ($toMerge as $item) {
            if ($item instanceof self) {
                $documents[] = $item;

                continue;
            }

            $documents += iterator_to_array($item);
        }

        (new Merger())->merge($this, ...$documents);

        return $this;
    }

    public function applyViewModel(object $model, ?Selector $selector = null): self {
        $selection = $selector ? $selector->select($this->dom) : [$this->dom->documentElement];

        $renderer = new ViewModelRenderer();

        foreach ($selection as $ctx) {
            $renderer->render($ctx, $model);
        }

        return $this;
    }

    public function applyTransformation(Transformation $transformation, ?Selector $selector = null): self {
        $processor = new TransformationProcessor();
        $selection = $selector !== null ? $selector->select($this->dom) : [$this->dom->documentElement];

        foreach ($selection as $ctx) {
            $processor->process($ctx, $transformation);
        }

        return $this;
    }

    public function applyFormData(FormData $formData): self {
        (new FormDataRenderer())->render(
            $this->dom->documentElement,
            $formData
        );

        return $this;
    }

    public function applyCSRFProtection(CSRFProtection $protection): self {
        (new CSRFProtectionRenderer())->render(
            $this->dom->documentElement,
            $protection
        );

        return $this;
    }
}
