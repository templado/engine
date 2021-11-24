<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;

interface Transformation {
    public function getSelector(): Selector;

    /** @psalm-suppress MissingReturnType Adding void here would qualify as a BC break :-/ */
    public function apply(DOMNode $context);
}
