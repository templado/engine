<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;
use DOMNode;

interface Snippet {

    public function getTargetId(): string;

    public function applyTo(DOMElement $node): DOMNode;

}
