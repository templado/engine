<?php declare(strict_types = 1);
namespace TheSeer\Templado\Example;

use DOMNode;
use TheSeer\Templado\Selector;
use TheSeer\Templado\Transformation;
use TheSeer\Templado\XPathSelector;

class SampleTransformation implements Transformation {

    public function getSelector(): Selector {
        return new XPathSelector('//*[@property]');
    }

    public function apply(DOMNode $context) {
        $context->parentNode->removeChild($context);
    }

}
