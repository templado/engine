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

trait Engine {

    private DOMDOcument $dom;

    public static function fromFile(FileName $fileName): static {
    }

    public static function fromString(string $string): static {
    }

    public static function fromDomDocument(DOMDocument $dom): static {
    }

    public function toFile(FileName $name): static {
    }

    public function applySnippet(Snippet $snippet): static {
    }

    public function applySnippets(SnippetListCollection $snippetList): static {
    }

    public function applyViewModel(object $model, ?Selector $selector = null): static {
    }

    public function applyFormData(FormData $formData): static {
    }

    public function applyCSRFProtection(CSRFProtection $protection): static {
    }

    public function applyTransformation(Transformation $transformation): static {
    }

}


class Html {
    use Engine;

    public function toSnippet(): Snippet {
    }

    public function toString(?Serializer $serializer = null): string {
    }

}

class TempladoSnippet implements Snippet{
    use Engine;

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
