<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;

class TransformationProcessor {
    public function process(DOMElement $context, Transformation $transformation): void {
        $selection = $transformation->getSelector()->select($context);

        if ($selection->isEmpty()) {
            return;
        }

        foreach ($selection as $node) {
            $transformation->apply($node);
        }
    }
}
