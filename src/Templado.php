<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

final readonly class Templado {

    private function __construct(
        private DOMDocument $dom,
        private ?Id $id
    ) {}

    public static function fromString(string $markup, ?Id $id = null): self {
        \libxml_use_internal_errors(true);
        \libxml_clear_errors();

        $dom                     = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $tmp                     = $dom->loadXML($markup);

        if (!$tmp || \libxml_get_last_error()) {
            $errors = \libxml_get_errors();
            \libxml_clear_errors();

            throw new TempladoParsingException(...$errors);
        }

    }

    public static function fromDomDocument(DOMDocument $dom, ?Id $id = null): self {
    }

    public function extract(Selector $selector, ?Id $id = null): self {
    }

    public function asString(?Serializer $serializer = null): string {
    }

    public function mergeIn(self|TempladoCollection ...$toImport): self {
    }

    public function applyViewModel(object $model, ?Selector $selector = null): self {
    }

    public function applyTransformation(Transformation $transformation, ?Selector $selector = null): self {
    }

    public function applyFormData(FormData $formData): self {
    }

    public function applyCSRFProtection(CSRFProtection $protection): self {
    }

}
