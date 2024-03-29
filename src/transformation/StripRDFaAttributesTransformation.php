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

use function implode;
use DOMElement;
use DOMNode;

final class StripRDFaAttributesTransformation implements Transformation {
    /** @psalm-var list<string> */
    private array $attributes = ['property', 'resource', 'prefix', 'typeof', 'vocab'];

    public function selector(): Selector {
        return new XPathSelector('//*[@' . implode(' or @', $this->attributes) . ']');
    }

    public function apply(DOMNode $context): void {
        if (!$context instanceof DOMElement) {
            return;
        }

        foreach ($this->attributes as $attribute) {
            $context->removeAttribute($attribute);
        }
    }
}
