<?php declare(strict_types = 1);
namespace TheSeer\Templado;

class AssetCollection {

    /** @var Asset[] */
    private $assets = [];

    public function addAsset(string $id, Asset $asset) {
        $this->assets[$id] = $asset;
    }

    public function hasAssetsFor(string $id): bool {
        return isset($this->assets[$id]);
    }

    public function getAssetForId(string $id): Asset {
        if (!$this->hasAssetsFor($id)) {
            throw new AssetCollectionException(
                sprintf("No Asset for Id '%s' in collection", $id)
            );
        }

        return $this->assets[$id];
    }

}
