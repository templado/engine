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

use function assert;
use DOMElement;
use DOMNode;

final class TransformationProcessor {
    public function process(DOMElement $context, Transformation ...$transformations): void {
        foreach ($transformations as $transformation) {
            $selection = $transformation->selector()->select($context);

            if ($selection->isEmpty()) {
                return;
            }

            foreach ($selection as $node) {
                $transformation->apply($node);
            }
        }
    }
}
