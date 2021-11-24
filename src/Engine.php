<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;

trait Engine {

    private DOMDOcument $dom;

    public function applySnippet(Snippet $snippet): static {
    }

    public function applySnippets(SnippetList $snippetList): static {
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
