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

use const LIBXML_NOEMPTYTAG;
use function assert;
use DOMDocument;
use DOMElement;
use DOMNameSpaceNode;
use DOMNode;
use DOMXPath;
use XMLWriter;

class HTMLSerializer implements Serializer {
    private const HTMLNS        = 'http://www.w3.org/1999/xhtml';
    private bool $stripRDFaFlag = false;

    private bool $keepXMLHeaderFlag = false;

    private bool $namespaceCleaningFlag = true;

    private bool $withDoctypeFlag = true;

    private bool $isFirst = true;

    /** @psalm-var list<Filter> */
    private array $filters = [];

    /** @psalm-var list<Transformation> */
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
        if (!empty($this->transformations)) {
            (new TransformationProcessor())->process(
                $document->documentElement,
                ...$this->transformations
            );
        }

        $xmlString = $this->namespaceCleaningFlag ?
            $this->serializeToCleanedString($document) :
            $this->serializeToBasicString($document);

        $this->filters[] = new EmptyElementsFilter();

        foreach ($this->filters as $filter) {
            $xmlString = $filter->apply($xmlString);
        }

        return $xmlString;
    }

    private function serializeToCleanedString(DOMDocument $document): string {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->setIndent(true);
        $writer->setIndentString('  ');

        if ($this->keepXMLHeaderFlag) {
            $writer->startDocument(encoding: $document->encoding);
        }

        if ($this->withDoctypeFlag) {
            $writer->writeDtd('html');
        }

        $this->isFirst = true;

        $this->walk($writer, $document->documentElement, []);

        return $writer->outputMemory();
    }

    private function walk(XMLWriter $writer, DOMNode $node, array $knownPrefixes): void {
        $dom = $node->ownerDocument;

        if (!$node instanceof DOMElement) {
            $writer->writeRaw(
                $dom->saveXML($node)
            );

            return;
        }

        if ($node->namespaceURI === self::HTMLNS || $node->namespaceURI === '' || $node->namespaceURI === null) {
            $writer->startElement($node->localName);

            if ($this->isFirst) {
                $writer->writeAttribute('xmlns', self::HTMLNS);
                $this->isFirst = false;
            }
        } else {
            $writer->startElement($node->nodeName);

            if (empty($node->prefix)) {
                $writer->writeAttribute('xmlns', $node->namespaceURI);
            } elseif (!isset($knownPrefixes[$node->prefix])) {
                $writer->writeAttribute('xmlns:' . $node->prefix, $node->namespaceURI);
                $knownPrefixes[$node->prefix] = $node->namespaceURI;
            }
        }

        foreach ($node->attributes as $attribute) {
            if ($this->stripRDFaFlag && in_array($attribute->name, ['property', 'resource', 'prefix', 'typeof', 'vocab'], true)) {
                continue;
            }

            $prefix = $attribute->prefix;

            if (empty($prefix)) {
                $writer->writeAttribute($attribute->name, $attribute->value);

                continue;
            }

            if (!isset($knownPrefixes[$prefix])) {
                $nsUri = $node->lookupNamespaceURI($prefix);
                assert($nsUri !== null);

                $knownPrefixes[$prefix] = $nsUri;
                $writer->writeAttribute('xmlns:' . $prefix, $nsUri);
            }

            $writer->writeAttribute(
                $attribute->nodeName,
                $attribute->value
            );
        }

        foreach ((new DOMXPath($dom))->query('./namespace::*', $node) as $nsNode) {
            /** @psalm-suppress DocblockTypeContradiction */
            assert($nsNode instanceof DOMNameSpaceNode);

            if (empty($nsNode->prefix) || $nsNode->prefix === 'xml') {
                continue;
            }

            if ($nsNode->nodeValue === self::HTMLNS) {
                continue;
            }

            if (isset($knownPrefixes[$nsNode->prefix])) {
                continue;
            }

            assert($nsNode->nodeValue !== null);
            $writer->writeAttribute('xmlns:' . $nsNode->prefix, $nsNode->nodeValue);
            $knownPrefixes[$nsNode->prefix] = $nsNode->nodeValue;
        }

        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $childNode) {
                $this->walk($writer, $childNode, $knownPrefixes);
            }
        }

        $writer->fullEndElement();
    }

    private function serializeToBasicString(DOMDocument $document): string {
        $document->formatOutput = true;
        $xmlString              = $document->saveXML($document->documentElement, options: LIBXML_NOEMPTYTAG);

        if ($this->withDoctypeFlag) {
            $xmlString = "<!DOCTYPE html>\n" . $xmlString;
        }

        if ($this->keepXMLHeaderFlag) {
            $xmlString = sprintf(
                '<?xml version="1.0" encoding="%s" ?>',
                $document->encoding ?? 'utf-8'
            ) . "\n" . $xmlString;
        }

        return $xmlString . "\n";
    }
}
