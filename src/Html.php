<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMDocumentType;
use DOMElement;
use DOMNode;
use DOMText;
use function assert;

class Html {

    /** @var DOMDocument */
    private $dom;

    public function __construct(DOMDocument $dom) {
        $this->dom = $dom;
    }

    public function applySnippet(Snippet $snippet): self {
        $list = new SnippetListCollection();
        $list->addSnippet($snippet);

        $this->applySnippets($list);

        return $this;
    }

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

    public function toSnippet(string $id): Snippet {
        $imported = (new DOMDocument())->importNode(
            $this->dom->documentElement,
            true
        );

        return new SimpleSnippet($id, $imported);
    }

    public function extractAsSnippets(Selector $selector, string $targetId): SnippetList {
        $selection = $selector->select($this->dom->documentElement);

        if ($selection->isEmpty()) {
            throw new TempladoException('Selection result is empty - cannot extract');
        }

        $list = new SnippetList();

        foreach ($selection as $item) {
            assert($item instanceof DOMNode);

            if ($item instanceof DOMText) {
                $list->addSnippet(
                    new TextSnippet($targetId, $item)
                );

                continue;
            }

            if ($item instanceof DOMElement) {
                $list->addSnippet(
                    new SimpleSnippet($targetId, $item)
                );

                continue;
            }

            throw new TempladoException('Unspported node type - cannot extract to snippet');
        }

        return $list;
    }

    public function asString(Filter $filter = null): string {
        $content = $this->serializeDomDocument();
        $content = (new EmptyElementsFilter())->apply($content);
        $content = (new ClearNamespaceDefinitionsFilter($this->dom->documentElement->nodeName))->apply($content);

        if ($filter === null) {
            return $content;
        }

        return $filter->apply($content);
    }

    private function serializeDomDocument(): string {
        $tmp = new DOMDocument();
        $tmp->formatOutput       = true;
        $tmp->preserveWhiteSpace = false;

        $tmp->loadXML(
            $this->dom->saveXML()
        );
        $tmp->formatOutput       = true;
        $tmp->preserveWhiteSpace = false;

        /** @psalm-suppress RedundantCondition psalm believes this cannot be null, but it can ;) */
        if ($tmp->doctype instanceof DOMDocumentType) {
            return $this->serializeWithoutXMLHeader($tmp);
        }

        return $tmp->saveXML($tmp->documentElement, \LIBXML_NOEMPTYTAG);
    }

    private function serializeWithoutXMLHeader(DOMDocument $dom): string {
        return \implode(
            "\n",
            [
                $dom->saveXML($dom->doctype),
                $dom->saveXML($dom->documentElement, \LIBXML_NOEMPTYTAG)
            ]
        );
    }
}
