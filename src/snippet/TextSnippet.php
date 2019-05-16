<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMText;

class TextSnippet extends SimpleSnippet {
    public function __construct($targetId, DOMText $content) {
        parent::__construct($targetId, $content);
    }
}
