<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMNode;

class StripRDFaAttributesTransformation implements Transformation {

    /** @var string[] */
    private $attributes = ['property', 'resource', 'prefix', 'typeof'];

    public function getSelector(): Selector {
        return new XPathSelector('//*[@' . implode(' or @', $this->attributes) . ']');
    }

    public function apply(DOMNode $context) {
        if (!$context instanceof \DOMElement) {
            return;
        }

        foreach($this->attributes as $attribute) {
            $context->removeAttribute($attribute);
        }
    }

}
