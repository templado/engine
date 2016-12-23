<?php declare(strict_types = 1);
namespace TheSeer\Templado;

class FileName {

    private $path;

    /**
     * FileName constructor.
     *
     * @param $path
     */
    public function __construct(string $path) {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function asString(): string {
        return $this->path;
    }

}
