<?php declare(strict_types = 1);
namespace Templado\Engine;

use TheSeer\CSS2XPath\Translator;

class CSSSelector extends XPathSelector {

    public function __construct(string $query) {
        parent::__construct(
            (new Translator())->translate($query)
        );
    }

}
