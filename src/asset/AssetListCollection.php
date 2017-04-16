<?php declare(strict_types = 1);
namespace Templado\Engine;

class AssetListCollection {

    /** @var AssetList[] */
    private $assetLists = [];

    public function addAsset(Asset $asset) {
        $id = $asset->getTargetId();
        if (!$this->hasAssetsForId($id)) {
            $this->assetLists[$id] = new AssetList();
        }
        $this->assetLists[$id]->addAsset($asset);
    }

    public function hasAssetsForId(string $id): bool {
        return isset($this->assetLists[$id]);
    }

    /**
     * @param string $id
     *
     * @return AssetList
     * @throws AssetCollectionException
     */
    public function getAssetsForId(string $id): AssetList {
        if (!$this->hasAssetsForId($id)) {
            throw new AssetCollectionException(
                sprintf("No Assets for Id '%s' in collection", $id)
            );
        }

        return $this->assetLists[$id];
    }

}
