<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;

interface Selector {
    public function select(DOMNode $context): Selection;
}
