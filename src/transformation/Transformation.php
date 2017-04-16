<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;

interface Transformation {

    public function getSelector(): Selector;

    public function apply(DOMNode $context);

}
