<?php declare(strict_types = 1);

namespace TheSeer\Templado;

use DOMElement;
use DOMNode;

class AssetRenderer {

    /**
     * @var AssetCollection
     */
    private $assetCollection;

    /**
     * AssetRenderer constructor.
     *
     * @param AssetCollection $assetCollection
     */
    public function __construct(AssetCollection $assetCollection) {
        $this->assetCollection = $assetCollection;
    }

    public function render(DOMNode $context) {
        foreach($context->childNodes as $node) {
            if (!$node instanceof DOMElement) {
                continue;
            }

            if ($node->hasAttribute('id')) {
                $id = $node->getAttribute('id');

                if (!$this->assetCollection->hasAssetsFor($id)) {
                    continue;
                }
                $asset = $this->assetCollection->getAssetForId($id);
                if ($asset->hasId() && $asset->getId() === $id) {
                    $node->parentNode->replaceChild(
                        $node->ownerDocument->importNode($asset->getNode(), true),
                        $node
                    );
                    continue;
                }
                $node->appendChild(
                    $node->ownerDocument->importNode($asset->getNode(), true)
                );
            }

            if ($node->hasChildNodes()) {
                $this->render($node);
            }
        }
    }

}
