<?php declare(strict_types = 1);
namespace Templado\Engine;

use DOMElement;

class AssetRenderer {

    /**
     * @var AssetListCollection
     */
    private $assetCollection;

    /**
     * @var DOMElement
     */
    private $currentContext;

    /**
     * AssetRenderer constructor.
     *
     * @param AssetListCollection $assetCollection
     */
    public function __construct(AssetListCollection $assetCollection) {
        $this->assetCollection = $assetCollection;
    }

    public function render(DOMElement $context) {
        $children = $context->childNodes;
        for ($i = 0; $i < $children->length; $i++) {
            $node = $children->item($i);
            if (!$node instanceof DOMElement) {
                continue;
            }
            $this->currentContext = $node;
            $this->processCurrent();
        }
    }

    /**
     * @throws AssetCollectionException
     */
    private function processCurrent() {
        if ($this->currentContext->hasAttribute('id')) {
            $id = $this->currentContext->getAttribute('id');

            if ($this->assetCollection->hasAssetsForId($id) && !$this->applyAssetsToElement($id)) {
                return;
            }
        }

        if ($this->currentContext->hasChildNodes()) {
            $this->render($this->currentContext);
        }
    }

    /**
     * @param string $id
     *
     * @return bool
     *
     * @throws \Templado\Engine\AssetCollectionException
     */
    private function applyAssetsToElement($id): bool {
        $assets = $this->assetCollection->getAssetsForId($id);
        foreach ($assets as $asset) {
            $result = $asset->applyTo($this->currentContext);
            if (!$this->currentContext->isSameNode($result)) {
                if (!$result instanceof DOMElement) {
                    // Context $node was replaced by a non DOMElement,
                    // so we cannot apply further assets
                    return false;
                }
                /** @var DOMElement $node */
                $this->currentContext = $result;
            }
        }

        return true;
    }
}
