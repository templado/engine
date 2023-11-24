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

use DOMDocumentFragment;
use XMLWriter;
use function assert;
use DOMAttr;
use DOMDocument;
use DOMElement;
use DOMNode;

class NamespaceCleaningTransformation implements Transformation {
    private const HTMLNS = 'http://www.w3.org/1999/xhtml';

    private bool $isFirst;

    public function selector(): Selector {
        return new XPathSelector('.');
    }

    public function apply(DOMNode $context): void {
        assert($context instanceof DOMElement);
        assert($context->parentNode instanceof DOMNode);

        $this->isFirst = true;

        $context->parentNode->replaceChild(
            $this->cleanup($context),
            $context
        );
    }

    private function cleanup(DOMElement $context): DOMNode {
        $writer = new XMLWriter();
        $writer->openMemory();
        $writer->startDocument();

        $this->walk($writer, $context, []);

        $writer->endDocument();

        /*
        assert($context->ownerDocument instanceof DOMDocument);
        $tmpContainer = $context->ownerDocument->createDocumentFragment();

        var_dump($writer->outputMemory());
        die;

        $tmpContainer->appendXML($writer->outputMemory());
*/
        $dom = new DOMDocument();
        $dom->loadXML($writer->outputMemory());

        assert($context->ownerDocument instanceof DOMDocument);
        return $context->ownerDocument->importNode(
            $dom->documentElement, true
        );

    }

    private function walk(XMLWriter $writer, DOMNode $node, array $knownPrefixes):void {
        assert($node->ownerDocument instanceof DOMDocument);

        if (!$node instanceof DOMElement) {
            $writer->writeRaw(
                $node->ownerDocument->saveXML($node)
            );

            return;
        }

        if ($node->namespaceURI === self::HTMLNS || empty($node->namespaceURI)) {
            $writer->startElement($node->localName);
            if ($this->isFirst) {
                $writer->writeAttribute('xmlns', self::HTMLNS);
                $this->isFirst = false;
            }
        } else {
            if (empty($node->prefix)) {
                $writer->startElement($node->nodeName);
                $writer->writeAttribute('xmlns', $node->namespaceURI);
            } else {
                $writer->startElementNs($node->prefix, $node->localName, $node->namespaceURI);
            }
        }

        foreach($node->attributes as $attribute) {
            assert($attribute instanceof DOMAttr);

            if (empty($attribute->prefix)) {
                $writer->writeAttribute($attribute->name, $attribute->value);
                continue;
            }

            if (!isset($knownPrefixes[$attribute->prefix])) {
                $knownPrefixes[$attribute->prefix] = $node->lookupNamespaceURI($attribute->prefix);
                $writer->writeAttribute('xmlns:' . $attribute->prefix, $node->lookupNamespaceURI($attribute->prefix));
            }
        }

        if ($node->hasChildNodes()) {
            foreach($node->childNodes as $childNode) {
                $this->walk($writer, $childNode, $knownPrefixes);
            }
        }

        $writer->endElement();
    }
}
