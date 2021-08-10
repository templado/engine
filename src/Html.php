<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMDocumentType;

class Html {

    /** @var DOMDocument */
    private $dom;

    public function __construct(DOMDocument $dom) {
        $this->dom = $dom;
    }

    public function applySnippets(SnippetListCollection $snippetListCollection): void {
    public function applySnippets(SnippetListCollection $snippetListCollection): self {
        (new SnippetRenderer($snippetListCollection))->render($this->dom->documentElement);

        return $this;
    }

    /**
     * @param object $model
     *
     * @throws ViewModelRendererException
     */
    public function applyViewModel($model): self {
        (new ViewModelRenderer())->render($this->dom->documentElement, $model);

        return $this;
    }

    /**
     * @throws FormDataRendererException
     */
    public function applyFormData(FormData $formData): self {
        (new FormDataRenderer())->render($this->dom->documentElement, $formData);

        return $this;
    }

    public function applyCSRFProtection(CSRFProtection $protection): self {
        (new CSRFProtectionRenderer())->render($this->dom->documentElement, $protection);

        return $this;
    }

    public function applyTransformation(Transformation $transformation): self {
        (new TransformationProcessor())->process($this->dom->documentElement, $transformation);

        return $this;
    }
    }

    public function asString(Filter $filter = null): string {
        $content = $this->serializeDomDocument();
        $content = (new EmptyElementsFilter())->apply($content);
        $content = (new ClearNamespaceDefinitionsFilter())->apply($content);

        if ($filter === null) {
            return $content;
        }

        return $filter->apply($content);
    }

    private function serializeDomDocument(): string {
        $this->dom->formatOutput       = true;
        $this->dom->preserveWhiteSpace = false;

        $this->dom->loadXML(
            $this->dom->saveXML()
        );

        /** @psalm-suppress RedundantCondition psalm believes this cannot be null, but it can ;) */
        if ($this->dom->doctype instanceof DOMDocumentType) {
            return $this->serializeWithoutXMLHeader();
        }

        return $this->dom->saveXML($this->dom->documentElement, \LIBXML_NOEMPTYTAG);
    }

    private function serializeWithoutXMLHeader(): string {
        return \implode(
            "\n",
            [
                $this->dom->saveXML($this->dom->doctype),
                $this->dom->saveXML($this->dom->documentElement, \LIBXML_NOEMPTYTAG)
            ]
        );
    }
}
