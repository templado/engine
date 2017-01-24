<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMDocument;
use DOMDocumentType;

class Page {

    /**
     * @var DOMDocument
     */
    private $dom;

    /**
     * Page constructor.
     *
     * @param DOMDocument $dom
     */
    public function __construct(DOMDocument $dom) {
        $this->dom = $dom;
    }

    /**
     * @param AssetCollection $assetCollection
     */
    public function applyAssets(AssetCollection $assetCollection) {
        (new AssetRenderer($assetCollection))->render($this->dom->documentElement);
    }

    /**
     * @param object $model
     */
    public function applyViewModel($model) {
        (new ViewModelRenderer())->render($this->dom->documentElement, $model);
    }

    /**
     * @param FormData $formData
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
     * @return string
     */
    public function asString(): string {
        return $this->clearNamespaceDefinitions(
            $this->fixEmptyElements(
                $this->serializeDomDocument()
            )
        );
    }

    /**
     * @param $xmlString
     *
     * @return mixed
     */
    private function fixEmptyElements($xmlString): string {
        $tagList = [
            'base', 'br', 'meta', 'link', 'img', 'input', 'button', 'hr', 'embed',
            'param', 'source', 'track', 'area', 'keygen',
        ];

        foreach($tagList as $tag) {
            $xmlString = preg_replace(
                "=<{$tag}(.*[^>])></{$tag}>=U",
                "<{$tag}\$1/>",
                $xmlString
            );
        }

        return $xmlString;
    }

    /**
     * @param $xmlString
     *
     * @return mixed
     */
    private function clearNamespaceDefinitions($xmlString): string {
        $xmlString = preg_replace('/ xmlns=".*[^"]"/U', '', $xmlString);
        $xmlString = str_replace('<html', '<html xmlns="http://www.w3.org/1999/xhtml"', $xmlString);

        return $xmlString;
    }

    /**
     * @return string
     */
    private function serializeDomDocument(): string {
        $this->dom->formatOutput       = true;
        $this->dom->preserveWhiteSpace = false;

        $this->dom->loadXML(
            $this->dom->saveXML()
        );

        if ($this->dom->doctype instanceof DOMDocumentType) {
            $xmlString = implode(
                "\n",
                [
                    $this->dom->saveXML($this->dom->doctype),
                    $this->dom->saveXML($this->dom->documentElement, LIBXML_NOEMPTYTAG)
                ]
            );

            return $xmlString;
        } else {
            $xmlString = $this->dom->saveXML($this->dom->documentElement, LIBXML_NOEMPTYTAG);

            return $xmlString;
        }
    }

}
