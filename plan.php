<?php declare(strict_types = 1);
namespace templado5;

use DOMDocument;
use DOMElement;
use DOMNode;
use Templado\Engine\CSRFProtection;
use Templado\Engine\FileName;
use Templado\Engine\FormData;
use Templado\Engine\Selector;
use Templado\Engine\Snippet;
use Templado\Engine\SnippetListCollection;
use Templado\Engine\Transformation;




class TempladoSnippet implements Snippet{
    use Engine;

    public static function fromFile(FileName $fileName): static {
    }

    public static function fromString(string $string): static {
    }

    public static function fromDomDocument(DOMDocument $dom): static {
    }

    public function toFile(FileName $name): void {
    }

    public function toString(): string {
    }

    public function getTargetId(): string {
    }

    public function applyTo(DOMElement $node): DOMNode {
    }

}

class TextSnippet implements Snippet {

    public static function fromFile(FileName $fileName): static {
    }

    public static function fromString(): static {
    }

    public function toString(): string {
    }

    public function getTargetId(): string {
    }

    public function applyTo(DOMElement $node): DOMNode {
    }

}
