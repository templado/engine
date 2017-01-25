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
     */
    private function applyAssetsToNode($id, DOMElement $node) {
        $assets = $this->assetCollection->getAssetsForId($id);
        foreach($assets as $asset) {
            if ($asset->hasContentWithId() && $asset->getContentId() === $id) {
                $node->parentNode->replaceChild(
                    $node->ownerDocument->importNode($asset->getContent(), true),
                    $node
                );

                continue;
            }
            $node->appendChild(
                $node->ownerDocument->importNode($asset->getContent(), true)
            );
        }
    }

}
