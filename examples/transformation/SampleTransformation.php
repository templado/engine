<?php declare(strict_types = 1);
namespace Templado\Engine\Example;

use DOMNode;
use Templado\Engine\Selector;
use Templado\Engine\Transformation;
use Templado\Engine\XPathSelector;

class SampleTransformation implements Transformation {

    public function getSelector(): Selector {
        return new XPathSelector('//*[@property]');
    }

    public function apply(DOMNode $context) {
        $context->parentNode->removeChild($context);
    }

}
