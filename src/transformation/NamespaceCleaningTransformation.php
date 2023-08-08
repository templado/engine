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

use DOMElement;
use DOMNode;

class NamespaceCleaningTransformation implements Transformation {
    private const HTMLNS = 'http://www.w3.org/1999/xhtml';

    public function selector(): Selector {
        return new XPathSelector('//*');
    }

    public function apply(DOMNode $context): void {
        assert($context instanceof DOMElement);

        if ($this->hasEmptyNamespace($context) ||
            $this->isPrefixedHTML($context) ||
            $this->isChildWithHtmlXMLNS($context)
        ) {
            $context = $this->enforceProperNamespace($context);
        }

        if ($context->isSameNode($context->ownerDocument->documentElement)) {
            $context->setAttribute('xmlns', self::HTMLNS);
        }
    }
    private function enforceProperNamespace(DOMElement $context): DOMElement {
        $replacement = $context->ownerDocument->createElementNS(
            self::HTMLNS,
            $context->localName
        );

        if ($context->hasAttributes()) {
            foreach (StaticNodeList::fromNamedNodeMap($context->attributes) as $attribute) {
                if ($attribute->localName === 'xmlns') {
                    continue;
                }
                $replacement->setAttributeNodeNS($attribute);
            }
        }

        if ($context->hasChildNodes()) {
            $replacement->append(...$context->childNodes);
        }

        $context->parentNode->replaceChild($replacement, $context);

        return $replacement;
    }

    private function hasEmptyNamespace(DOMElement $context): bool {
        return $context->namespaceURI === '' || $context->namespaceURI === null;
    }

    private function isPrefixedHTML(DOMElement $context): bool {
        return $context->namespaceURI === self::HTMLNS && $context->prefix !== '';
    }

    private function isChildWithHtmlXMLNS(DOMElement $context): bool {
        return empty($context->prefix) && ($context->getAttribute('xmlns') === self::HTMLNS);
    }
}
