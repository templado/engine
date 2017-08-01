<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMDocument;
use DOMDocumentType;

class Html {

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * @param DOMDocument $dom
     */
    public function __construct(DOMDocument $dom) {
        $this->dom = $dom;
    }

    /**
     * @param SnippetListCollection $snippetListCollection
     */
    public function applySnippets(SnippetListCollection $snippetListCollection) {
        (new SnippetRenderer($snippetListCollection))->render($this->dom->documentElement);
    }

    /**
     * @param object $model
     *
     * @throws ViewModelRendererException
     */
    public function applyViewModel($model) {
        (new ViewModelRenderer())->render($this->dom->documentElement, $model);
    }

    /**
     * @param FormData $formData
     *
     * @throws FormDataRendererException
     */
    public function applyFormData(FormData $formData) {
        (new FormDataRenderer())->render($this->dom->documentElement, $formData);
    }

    /**
     * @param CSRFProtection $protection
     */
    public function applyCSRFProtection(CSRFProtection $protection) {
        (new CSRFProtectionRenderer())->render($this->dom->documentElement, $protection);
    }

    /**
     * @param Transformation $transformation
     */
    public function applyTransformation(Transformation $transformation) {
        (new TransformationProcessor())->process($this->dom->documentElement, $transformation);
    }

    /**
     * @param Filter $filter
     *
     * @return string
     */
    public function asString(Filter $filter = null): string {
        $content = $this->serializeDomDocument();
        $content = (new EmptyElementsFilter())->apply($content);
        $content = (new ClearNamespaceDefinitionsFilter())->apply($content);

        if ($filter === null) {
            return $content;
        }

        return $filter->apply($content);
    }

    /**
     * @return string
     */
    private function serializeDomDocument(): string {
        $this->dom->formatOutput = true;
        $this->dom->preserveWhiteSpace = false;

        $this->dom->loadXML(
            $this->dom->saveXML()
        );

        if ($this->dom->doctype instanceof DOMDocumentType) {
            return $this->serializeWithoutXMLHeader();
        }

        return $this->dom->saveXML($this->dom->documentElement, LIBXML_NOEMPTYTAG);
    }

    /**
     * @return string
     */
    private function serializeWithoutXMLHeader(): string {
        return implode(
            "\n",
            [
                $this->dom->saveXML($this->dom->doctype),
                $this->dom->saveXML($this->dom->documentElement, LIBXML_NOEMPTYTAG)
            ]
        );
    }

}
