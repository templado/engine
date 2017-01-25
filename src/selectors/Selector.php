<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMNode;

interface Selector {

    public function select(DOMNode $context): Selection;

}
