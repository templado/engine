<?php declare(strict_types = 1);
namespace Templado\Engine;

class AssetList implements \Iterator, \Countable {

    /**
     * @var Asset[]
     */
    private $assets = [];

    /**
     * @param Asset $asset
     */
    public function addAsset(Asset $asset) {
        $this->assets[] = $asset;
    }

    /**
     * @return Asset
     */
    public function current(): Asset {
        return current($this->assets);
    }

    /**
     * @return mixed|Asset
     */
    public function next() {
        return next($this->assets);
    }

    /**
     * @return int
     */
    public function key(): int {
        return key($this->assets);
    }

    /**
     * @return bool
     */
    public function valid() {
        return key($this->assets) !== null;
    }

    /**
     * @return mixed|Asset
     */
    public function rewind() {
        return reset($this->assets);
    }

    public function count(): int {
        return count($this->assets);
    }

}
