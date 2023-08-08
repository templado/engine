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

use DOMDocument;

class HTMLSerializer implements Serializer {
    private bool $stripRDFaFlag         = false;
    private bool $keepXMLHeaderFlag     = false;
    private bool $namespaceCleaningFlag = true;
    private bool $withDoctypeFlag       = true;

    private array $filters = [];

    private array $transformations = [];

    public function stripRDFa(): self {
        $this->stripRDFaFlag = true;

        return $this;
    }

    public function noHtml5Doctype(): self {
        $this->withDoctypeFlag = false;

        return $this;
    }

    public function keepXMLHeader(): self {
        $this->keepXMLHeaderFlag = true;

        return $this;
    }

    public function disableNamespaceCleaning(): self {
        $this->namespaceCleaningFlag = false;

        return $this;
    }

    public function addTransformation(Transformation $transformation): self {
        $this->transformations[] = $transformation;

        return $this;
    }

    public function addFilter(Filter $filter): self {
        $this->filters[] = $filter;

        return $this;
    }

    public function serialize(DOMDocument $document): string {
        if ($this->namespaceCleaningFlag) {
            $this->transformations[] = new NamespaceCleaningTransformation();
        }

        if ($this->stripRDFaFlag) {
            $this->transformations[] = new StripRDFaAttributesTransformation;
        }

        if (!empty($this->transformations)) {
            (new TransformationProcessor())->process(
                $document->documentElement,
                ...$this->transformations
            );
        }

        if ($this->withDoctypeFlag) {
            $document = $this->enforceHTML5DocType($document);
        }

        $document->formatOutput = true;
        $xmlString              = $document->saveXML(options: LIBXML_NOEMPTYTAG);

        $this->filters[] = new EmptyElementsFilter();

        if (!$this->keepXMLHeaderFlag) {
            $this->filters[] = new XMLHeaderFilter();
        }

        foreach ($this->filters as $filter) {
            $xmlString = $filter->apply($xmlString);
        }

        return $xmlString;
    }

    private function enforceHTML5DocType(DOMDocument $document): DOMDocument {
        $tmp                     = new DOMDocument();
        $tmp->preserveWhiteSpace = false;
        $tmp->loadXML('<?xml version="1.0" ?><!DOCTYPE html><html />');
        $tmp->replaceChild(
            $tmp->importNode($document->documentElement, true),
            $tmp->documentElement
        );

        return $tmp;
    }
}
