<?php declare(strict_types = 1);
namespace TheSeer\Templado;

use DOMElement;
use DOMNode;

class AssetRenderer {

    /**
     * @var AssetListCollection
     */
    private $assetCollection;

    /**
     * AssetRenderer constructor.
     *
     * @param AssetListCollection $assetCollection
     */
    public function __construct(AssetListCollection $assetCollection) {
        $this->assetCollection = $assetCollection;
    }

    public function render(DOMNode $context) {
        foreach($context->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }
            $this->processNode($node);
        }
    }

    /**
     * @param DOMElement $node
     */
    private function processNode(DOMElement $node) {
        if ($node->hasAttribute('id')) {
            $id = $node->getAttribute('id');

            if ($this->assetCollection->hasAssetsForId($id)) {
                $this->applyAssetsToNode($id, $node);
            }
        }

        if ($node->hasChildNodes()) {
            $this->render($node);
        }
    }

    /**
     * @param string     $id
     * @param DOMElement $node
     *
     * @throws \TheSeer\Templado\AssetCollectionException
     */
    private function applyAssetsToNode($id, DOMElement $node) {
        $assets = $this->assetCollection->getAssetsForId($id);
        foreach($assets as $asset) {
            $asset->applyTo($node);
        }
    }

}
